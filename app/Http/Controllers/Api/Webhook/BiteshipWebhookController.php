<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Webhook;

use App\Http\Controllers\Controller;
use App\Models\Order\Order;
use App\Models\Packing\Delivery;
use App\Models\Packing\DeliveryLog;
use App\Services\BiteshipService;
use App\Services\InventoryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BiteshipWebhookController extends Controller
{
    protected BiteshipService $biteshipService;

    public function __construct(BiteshipService $biteshipService)
    {
        $this->biteshipService = $biteshipService;
    }

    /**
     * Handle incoming Biteship webhooks for order.status, order.price, and order.waybill_id.
     */
    public function handle(Request $request): JsonResponse
    {
        $payload = $request->all();
        $event = (string) ($request->input('event') ?? 'unknown');
        $biteshipOrderId = $request->input('order_id') ?? data_get($payload, 'data.id');
        $waybillId = $request->input('courier_waybill_id')
            ?? $request->input('courier_tracking_id')
            ?? $request->input('waybill_id')
            ?? data_get($payload, 'courier.waybill_id');
        $courierCode = $request->input('courier_company') ?? data_get($payload, 'courier.company');

        // 1. Locate Order
        $order = $this->resolveOrder($request, $biteshipOrderId, $waybillId);
        $delivery = $order ? Delivery::where('order_id', $order->id)->first() : null;
        if (!$delivery && $waybillId) {
            $delivery = Delivery::where('tracking_number', $waybillId)->first();
        }

        $orderNumber = $order?->order_number ?? $request->input('reference_id') ?? data_get($payload, 'metadata.order_number') ?? '-';

        // 2. Process Event Specific Logic
        $result = match ($event) {
            'order.status' => $this->handleOrderStatus($order, $delivery, $request, $payload),
            'order.price' => $this->handleOrderPrice($order, $delivery, $request, $payload),
            'order.waybill_id' => $this->handleOrderWaybillId($order, $delivery, $request, $payload),
            default => $this->handleGenericEvent($order, $delivery, $request, $payload),
        };

        // 3. Save to delivery_logs table
        try {
            DeliveryLog::create([
                'order_id' => $order?->id,
                'delivery_id' => $delivery?->id,
                'waybill_id' => $waybillId ?: ($order?->resi),
                'biteship_order_id' => $biteshipOrderId ?: ($order?->meta['biteship_order_id'] ?? null),
                'courier_code' => $courierCode ?: ($order?->courier?->code ?? null),
                'event' => $event,
                'status' => $request->input('status') ?? ($result['status'] ?? null),
                'price' => $request->filled('price') ? (float) $request->input('price') : null,
                'cash_on_delivery' => $request->filled('cash_on_delivery.amount') ? (float) $request->input('cash_on_delivery.amount') : null,
                'location' => $request->input('location') ?? ($request->input('destination.address') ?? null),
                'note' => $request->input('note') ?? ($result['summary'] ?? null),
                'payload' => $payload,
            ]);
        } catch (\Throwable $e) {
            Log::warning("Gagal menyimpan webhook ke delivery_logs: " . $e->getMessage());
        }

        // 4. Log structured readable banner
        $this->biteshipService->logStructured(
            'info',
            'WEBHOOK',
            sprintf("Webhook Diterima: %s (#%s)", strtoupper($event), $orderNumber),
            [
                'Event' => $event,
                'Nomor Order' => $orderNumber,
                'Order ID CMS' => $order?->id ?? 'Tidak Ditemukan',
                'Nomor Resi' => $waybillId ?: ($order?->resi ?? '-'),
                'Biteship Order ID' => $biteshipOrderId ?: '-',
                'Status / Aksi' => $result['summary'] ?? 'Diproses',
                'Ekspedisi' => strtoupper((string) $courierCode),
                'Payload Request' => $payload,
            ]
        );

        return response()->json([
            'success' => true,
            'message' => "Webhook {$event} berhasil diproses.",
            'data' => [
                'event' => $event,
                'order_number' => $orderNumber,
                'processed' => true,
                'summary' => $result['summary'] ?? 'OK',
            ],
        ]);
    }

    /**
     * Handle event: order.status
     * Fired every time there is a status update in Biteship courier tracking.
     */
    protected function handleOrderStatus(?Order $order, ?Delivery $delivery, Request $request, array $payload): array
    {
        $biteshipStatus = strtolower(trim((string) ($request->input('status') ?? '')));
        $note = $request->input('note') ?? $request->input('description') ?? '';
        $location = $request->input('location') ?? '';
        $waybillId = $request->input('courier_waybill_id') ?? $request->input('courier_tracking_id');

        if (!$order) {
            return [
                'status' => $biteshipStatus,
                'summary' => "Status {$biteshipStatus} dicatat (Order tidak terhubung di CMS)",
            ];
        }

        $meta = $order->meta ?? [];
        $meta['biteship_status'] = $biteshipStatus;
        if (!empty($note)) {
            $meta['biteship_last_note'] = $note;
        }
        if (!empty($location)) {
            $meta['biteship_last_location'] = $location;
        }
        $meta['biteship_status_updated_at'] = now()->toDateTimeString();

        $oldStatus = (int) $order->status;
        $summary = "Status {$biteshipStatus}";

        // Map Biteship status to CMS Order status
        if ($biteshipStatus === 'delivered') {
            $order->status = Order::STATUS_DELIVERED;
            $meta['delivered_at'] = now()->toDateTimeString();
            $summary = "Pesanan Diterima Pelanggan (DELIVERED)";

            if ($delivery) {
                $delivery->status = 'delivered';
                $delivery->delivered_at = now();
                $delivery->save();
            }

            // Deduct inventory to delivered if not done yet
            if ($oldStatus !== Order::STATUS_DELIVERED) {
                foreach ($order->items as $item) {
                    if ($item->product_variant_id) {
                        InventoryService::recordWebOrderDelivered($item->product_variant_id, (int) $item->quantity);
                    }
                }
            }
        } elseif (in_array($biteshipStatus, ['dropping_off', 'picking_up', 'allocated', 'picked', 'on_process', 'in_transit'], true)) {
            if ($oldStatus < Order::STATUS_SHIPPED) {
                $order->status = Order::STATUS_SHIPPED;
                $summary = "Pesanan Dalam Pengiriman Kurir ({$biteshipStatus})";

                foreach ($order->items as $item) {
                    if ($item->product_variant_id) {
                        InventoryService::recordWebOrderShipped($item->product_variant_id, (int) $item->quantity);
                    }
                }
            }
            if ($delivery) {
                $delivery->status = 'in_transit';
                if (empty($delivery->shipped_at)) {
                    $delivery->shipped_at = now();
                }
                $delivery->save();
            }
        } elseif (in_array($biteshipStatus, ['returned', 'return_in_transit'], true)) {
            $summary = "Paket Retur / Dikembalikan ke Asal ({$biteshipStatus})";
            if ($delivery) {
                $delivery->status = 'returned';
                $delivery->save();
            }
        } elseif (in_array($biteshipStatus, ['cancelled', 'rejected'], true)) {
            $summary = "Pengiriman Dibatalkan oleh Kurir ({$biteshipStatus})";
        }

        if (!empty($waybillId) && empty($order->resi)) {
            $meta['resi'] = $waybillId;
            $meta['tracking_number'] = $waybillId;
        }

        $order->meta = $meta;
        $order->save();

        return [
            'status' => $biteshipStatus,
            'summary' => $summary,
        ];
    }

    /**
     * Handle event: order.price
     * Fired every time there is a change in price (shipping rates different from actual weight).
     */
    protected function handleOrderPrice(?Order $order, ?Delivery $delivery, Request $request, array $payload): array
    {
        $newPrice = (float) ($request->input('price') ?? 0);
        $summary = "Perubahan ongkir aktual: Rp " . number_format($newPrice, 0, ',', '.');

        if ($order) {
            $meta = $order->meta ?? [];
            $meta['biteship_actual_shipping_cost'] = $newPrice;
            $meta['biteship_price_updated_at'] = now()->toDateTimeString();

            if ($request->filled('cash_on_delivery')) {
                $meta['biteship_cod_info'] = $request->input('cash_on_delivery');
            }

            $order->meta = $meta;
            $order->save();
        }

        return [
            'status' => 'price_updated',
            'summary' => $summary,
        ];
    }

    /**
     * Handle event: order.waybill_id
     * Fired every time there is an update or newly issued waybill number.
     */
    protected function handleOrderWaybillId(?Order $order, ?Delivery $delivery, Request $request, array $payload): array
    {
        $waybillId = $request->input('courier_waybill_id')
            ?? $request->input('courier_tracking_id')
            ?? $request->input('waybill_id');
        $trackingUrl = $request->input('courier_link') ?? data_get($payload, 'courier.link');

        $summary = "Waybill terbit / diperbarui: {$waybillId}";

        if ($order && $waybillId) {
            $meta = $order->meta ?? [];
            $meta['resi'] = $waybillId;
            $meta['tracking_number'] = $waybillId;
            if ($trackingUrl) {
                $meta['biteship_tracking_url'] = $trackingUrl;
            }
            $meta['resi_updated_at'] = now()->toDateTimeString();
            $meta['resi_updated_by'] = 'Biteship Webhook';
            $meta['fulfillment_type'] = 'biteship';

            $order->meta = $meta;
            if ($order->status < Order::STATUS_SHIPPED) {
                $order->status = Order::STATUS_SHIPPED;
            }
            $order->save();

            if ($delivery) {
                $delivery->tracking_number = $waybillId;
                if ($order->status >= Order::STATUS_SHIPPED && $delivery->status === 'pending') {
                    $delivery->status = 'in_transit';
                    $delivery->shipped_at = now();
                }
                $delivery->save();
            }
        }

        return [
            'status' => 'waybill_updated',
            'summary' => $summary,
        ];
    }

    /**
     * Fallback for unknown or generic events.
     */
    protected function handleGenericEvent(?Order $order, ?Delivery $delivery, Request $request, array $payload): array
    {
        return [
            'status' => $request->input('status') ?? 'received',
            'summary' => 'Event diproses dan dicatat ke log',
        ];
    }

    /**
     * Find matching CMS Order by metadata, reference_id, biteship_order_id, or waybill number.
     */
    protected function resolveOrder(Request $request, ?string $biteshipOrderId, ?string $waybillId): ?Order
    {
        // 1. By metadata.order_id
        $cmsOrderId = $request->input('metadata.order_id');
        if ($cmsOrderId) {
            $order = Order::find($cmsOrderId);
            if ($order) return $order;
        }

        // 2. By metadata.order_number or reference_id
        $orderNumber = $request->input('metadata.order_number') ?? $request->input('reference_id');
        if ($orderNumber) {
            $order = Order::where('order_number', $orderNumber)->first();
            if ($order) return $order;
        }

        // 3. By biteship_order_id in meta JSON
        if ($biteshipOrderId) {
            $order = Order::whereRaw("meta->>'biteship_order_id' = ?", [$biteshipOrderId])->first();
            if ($order) return $order;
        }

        // 4. By tracking number / resi
        if ($waybillId) {
            $order = Order::whereRaw("meta->>'resi' = ?", [$waybillId])
                ->orWhereRaw("meta->>'tracking_number' = ?", [$waybillId])
                ->first();
            if ($order) return $order;
        }

        return null;
    }
}
