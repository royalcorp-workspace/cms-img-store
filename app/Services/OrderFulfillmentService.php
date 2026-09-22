<?php

namespace App\Services;

use App\Models\CreditMemo;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Order\Order;
use App\Models\Packing\Delivery;
use App\Models\Packing\DeliveryLog;
use App\Models\Packing\Handover;
use App\Models\Packing\HandoverItem;
use App\Models\Packing\PackingOut;
use App\Models\Packing\PackingSlip;
use App\Models\Packing\PackingSlipItem;
use App\Models\Payment;
use App\Models\Picking\PickingList;
use App\Models\Picking\PickingListItem;
use App\Models\Shipping\Courier;
use App\Models\Warehouse\Warehouse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OrderFulfillmentService
{
    /**
     * Completes the entire fulfillment data pipeline from Picking List to Handover
     * when the manual pick & pack steps are overridden/bypassed.
     *
     * @param Order $order
     * @param array $params [
     *    'tracking_number' => string|null,
     *    'courier_id' => string|null,
     *    'driver_name' => string|null,
     *    'driver_phone' => string|null,
     *    'target_status' => int|null,
     *    'fulfillment_type' => string|null ('manual', 'biteship', 'quick_status'),
     *    'notes' => string|null,
     * ]
     * @return array
     */
    public static function completeFulfillmentPipeline(Order $order, array $params = []): array
    {
        return DB::transaction(function () use ($order, $params) {
            // Ensure order items are loaded with products & variants
            if (!$order->relationLoaded('items')) {
                $order->load(['items.product', 'items.variant']);
            }

            $rawUser = auth()->user();
            $userId = null;
            if ($rawUser && !empty($rawUser->id) && Str::isUuid((string) $rawUser->id)) {
                if (\App\Models\User::where('id', (string) $rawUser->id)->exists()) {
                    $userId = (string) $rawUser->id;
                }
            }
            if (!$userId && !empty($order->creator)) {
                $matchedUser = \App\Models\User::where('username', $order->creator)
                    ->orWhere('name', $order->creator)
                    ->first();
                $userId = $matchedUser?->id;
            }
            if (!$userId) {
                $userId = \App\Models\User::first()?->id;
            }
            if ($userId && !\App\Models\User::where('id', $userId)->exists()) {
                $userId = null;
            }

            $fulfillmentType = $params['fulfillment_type'] ?? 'override';
            $trackingNumber = !empty($params['tracking_number']) ? trim($params['tracking_number']) : ($order->resi ?? null);
            $targetStatus = isset($params['target_status']) ? (int) $params['target_status'] : (int) $order->status;
            $isDelivered = ($targetStatus === Order::STATUS_DELIVERED);

            // 1. Resolve Warehouse
            $warehouseId = null;
            if (!empty($order->warehouse_id) && Warehouse::where('id', $order->warehouse_id)->exists()) {
                $warehouseId = $order->warehouse_id;
            } else {
                $activeWarehouse = Warehouse::where('status', true)->first();
                $warehouseId = $activeWarehouse?->id ?? Warehouse::first()?->id;
            }

            // 2. Resolve Courier (Strictly prioritize checkout courier)
            $courierId = $order->courier_id ?: (!empty($params['courier_id']) ? $params['courier_id'] : null);
            if (!$courierId) {
                $courierName = $order->meta['shipping_address']['courier_name'] ?? null;
                if ($courierName) {
                    $matched = Courier::whereRaw('LOWER(name) LIKE ?', ['%' . strtolower($courierName) . '%'])->first();
                    $courierId = $matched?->id;
                }
            }
            if ($courierId && !Courier::where('id', $courierId)->exists()) {
                $courierId = null;
            }
            $courier = $courierId ? Courier::find($courierId) : null;
            $driverName = $params['driver_name'] ?? ($courier?->name ?? 'Kurir Ekspedisi');
            $driverPhone = $params['driver_phone'] ?? null;

            // 3. Picking List & Items
            $pickingList = PickingList::where('order_id', $order->id)->first();
            if (!$pickingList) {
                $pickingList = PickingList::create([
                    'order_id' => $order->id,
                    'warehouse_id' => $warehouseId,
                    'picker_id' => $userId,
                    'status' => 'picked',
                    'priority' => 1,
                    'notes' => 'Otomatis dibuat via alur bypass ' . $fulfillmentType,
                ]);

                foreach ($order->items as $item) {
                    PickingListItem::create([
                        'picking_list_id' => $pickingList->id,
                        'order_item_id' => $item->id,
                        'product_id' => $item->product_id,
                        'product_variant_id' => $item->product_variant_id ?? null,
                        'quantity_ordered' => (int) $item->quantity,
                        'quantity_picked' => (int) $item->quantity,
                        'status' => 'picked',
                        'notes' => null,
                    ]);
                }
            } else {
                $pickingList->update([
                    'status' => 'picked',
                    'warehouse_id' => $warehouseId ?: $pickingList->warehouse_id,
                ]);
                foreach ($order->items as $item) {
                    PickingListItem::updateOrCreate(
                        [
                            'picking_list_id' => $pickingList->id,
                            'order_item_id' => $item->id,
                        ],
                        [
                            'product_id' => $item->product_id,
                            'product_variant_id' => $item->product_variant_id ?? null,
                            'quantity_ordered' => (int) $item->quantity,
                            'quantity_picked' => (int) $item->quantity,
                            'status' => 'picked',
                        ]
                    );
                }
            }

            // 4. Packing Slip & Items
            $packingSlip = PackingSlip::where('order_id', $order->id)->first();
            $totalWeight = 0;
            foreach ($order->items as $item) {
                $totalWeight += ((float) ($item->weight ?? 0)) * ((int) $item->quantity);
            }
            if ($totalWeight <= 0) {
                $totalWeight = 1.0;
            }

            if (!$packingSlip) {
                $packingSlip = PackingSlip::create([
                    'picking_list_id' => $pickingList->id,
                    'order_id' => $order->id,
                    'packer_id' => $userId,
                    'status' => 'packed',
                    'box_count' => 1,
                    'weight' => $totalWeight,
                    'notes' => 'Otomatis dibuat via alur bypass ' . $fulfillmentType,
                ]);

                foreach ($order->items as $item) {
                    PackingSlipItem::create([
                        'packing_slip_id' => $packingSlip->id,
                        'order_item_id' => $item->id,
                        'product_id' => $item->product_id,
                        'product_variant_id' => $item->product_variant_id ?? null,
                        'quantity_ordered' => (int) $item->quantity,
                        'quantity_packed' => (int) $item->quantity,
                        'box_number' => 1,
                    ]);
                }
            } else {
                $packingSlip->update([
                    'picking_list_id' => $pickingList->id,
                    'status' => 'packed',
                    'weight' => $totalWeight > 0 ? $totalWeight : $packingSlip->weight,
                ]);
                foreach ($order->items as $item) {
                    PackingSlipItem::updateOrCreate(
                        [
                            'packing_slip_id' => $packingSlip->id,
                            'order_item_id' => $item->id,
                        ],
                        [
                            'product_id' => $item->product_id,
                            'product_variant_id' => $item->product_variant_id ?? null,
                            'quantity_ordered' => (int) $item->quantity,
                            'quantity_packed' => (int) $item->quantity,
                            'box_number' => 1,
                        ]
                    );
                }
            }

            // Record delivery log: Sedang Dikemas
            if (DeliveryLog::where('order_id', $order->id)->where('event', 'packing_slip.created')->doesntExist()) {
                DeliveryLog::create([
                    'order_id' => $order->id,
                    'waybill_id' => $trackingNumber,
                    'courier_code' => $courier?->code,
                    'event' => 'packing_slip.created',
                    'status' => 'sedang_dikemas',
                    'location' => 'Gudang Pengirim',
                    'note' => 'Pesanan sedang dikemas di gudang (Packing Slip diterbitkan)',
                    'payload' => [
                        'packing_slip_id' => $packingSlip->id,
                        'weight' => $packingSlip->weight,
                        'box_count' => $packingSlip->box_count,
                    ],
                ]);
            }

            // 5. Packing Out
            $packingOut = PackingOut::where('packing_slip_id', $packingSlip->id)->first();
            if (!$packingOut) {
                $packingOut = PackingOut::create([
                    'packing_slip_id' => $packingSlip->id,
                    'warehouse_id' => $warehouseId,
                    'packer_id' => $userId,
                    'status' => 'out',
                    'notes' => 'Otomatis dibuat via alur bypass ' . $fulfillmentType,
                ]);
            } else {
                $packingOut->update([
                    'status' => 'out',
                    'warehouse_id' => $warehouseId ?: $packingOut->warehouse_id,
                ]);
            }

            // 6. Invoice & Invoice Items
            $invoice = Invoice::where('order_id', $order->id)->first();
            if (!$invoice) {
                $invoice = Invoice::create([
                    'id' => Str::uuid()->toString(),
                    'invoice_number' => 'INV-' . ($order->order_number ?: substr($order->id, 0, 8)) . '-' . rand(100, 999),
                    'order_id' => $order->id,
                    'customer_id' => $order->customer_id,
                    'courier_id' => $courierId,
                    'shipping_addresses_id' => $order->shipping_addresses_id,
                    'status' => 0,
                    'deleted' => false,
                    'payment_method' => $order->payment_method,
                    'payment_status' => (int) ($order->payment_status ?? 0),
                    'settlement_id' => $order->settlement_id,
                    'subtotal' => (float) ($order->subtotal ?? 0),
                    'tax' => (float) ($order->tax ?? 0),
                    'discount' => (float) ($order->discount ?? 0),
                    'total' => (float) ($order->total ?? 0),
                    'shipping_cost' => (float) ($order->shipping_cost ?? 0),
                    'shipping_cost_subsidy' => (float) ($order->shipping_cost_subsidy ?? 0),
                    'transaction_fee' => (float) ($order->transaction_fee ?? 0),
                    'voucher_id' => $order->voucher_id,
                    'voucher_nominal' => (float) ($order->voucher_nominal ?? 0),
                    'notes' => $order->notes,
                    'meta' => $order->meta,
                    'creator' => $order->creator,
                    'editor' => $order->editor,
                    'due_date' => now()->addDays(1),
                ]);

                foreach ($order->items as $item) {
                    InvoiceItem::create([
                        'id' => Str::uuid()->toString(),
                        'invoice_id' => $invoice->id,
                        'product_id' => $item->product_id,
                        'product_variant_id' => $item->product_variant_id,
                        'product_color_id' => $item->product_color_id ?? null,
                        'name' => $item->name ?? 'Produk',
                        'quantity' => (int) ($item->quantity ?? 1),
                        'unit_price' => (float) ($item->unit_price ?? 0),
                        'discount_nominal' => (float) ($item->discount_nominal ?? 0),
                        'discount_percent' => (float) ($item->discount_percent ?? 0),
                        'total' => (float) ($item->total ?? 0),
                        'weight' => (float) ($item->weight ?? 0),
                        'item_notes' => $item->item_notes ?? '',
                        'meta' => $item->meta,
                    ]);
                }

                $creditMemo = CreditMemo::where('order_id', $order->id)->first();
                if ($creditMemo && Payment::where('order_id', $order->id)->doesntExist()) {
                    Payment::create([
                        'id' => Str::uuid()->toString(),
                        'payment_number' => 'PAY-' . ($order->order_number ?: substr($order->id, 0, 8)) . '-' . rand(100, 999),
                        'order_id' => $creditMemo->order_id,
                        'gateway' => $creditMemo->gateway,
                        'transaction_id' => $creditMemo->transaction_id,
                        'amount' => $creditMemo->amount,
                        'status' => $creditMemo->status,
                        'payload' => $creditMemo->payload,
                        'paid_at' => $creditMemo->paid_at,
                    ]);
                }
            }

            // 7. Delivery
            $delivery = Delivery::where('order_id', $order->id)->first();
            $deliveryStatus = $isDelivered ? 'delivered' : 'in_transit';

            // Resolve ETA
            $estimatedAt = !empty($params['estimated_delivery_at']) ? \Carbon\Carbon::parse($params['estimated_delivery_at']) : null;
            $estimatedMin = !empty($params['estimated_delivery_min']) ? \Carbon\Carbon::parse($params['estimated_delivery_min']) : null;
            $estimatedMax = !empty($params['estimated_delivery_max']) ? \Carbon\Carbon::parse($params['estimated_delivery_max']) : null;
            $estimatedDuration = !empty($params['estimated_delivery_duration']) ? trim((string) $params['estimated_delivery_duration']) : null;
            $etaSource = !empty($params['eta_source']) ? trim((string) $params['eta_source']) : ($order->isKurirToko() ? 'store' : ($fulfillmentType === 'biteship' ? 'biteship' : 'manual'));
            $etaNotes = !empty($params['eta_notes']) ? trim((string) $params['eta_notes']) : null;

            // If Biteship and no ETA passed explicitly, calculate from Biteship duration or rates
            if ($fulfillmentType === 'biteship' && !$estimatedAt && !$estimatedMin) {
                $rawDuration = $params['biteship_duration'] ?? ($order->meta['biteship_duration'] ?? ($order->meta['shipping_duration'] ?? null));
                if ($rawDuration) {
                    $eta = \App\Services\EtaService::calculateEta($rawDuration);
                    $estimatedMin = $eta['min_date'];
                    $estimatedMax = $eta['max_date'];
                    $estimatedAt = $eta['estimated_at'];
                    $estimatedDuration = $eta['duration'];
                    $etaSource = 'biteship';
                    $etaNotes = $eta['formatted_label'];
                }
            }

            if (!$delivery) {
                $delivery = Delivery::create([
                    'packing_out_id' => $packingOut->id,
                    'order_id' => $order->id,
                    'courier_id' => $courierId,
                    'tracking_number' => $trackingNumber,
                    'driver_name' => $driverName,
                    'driver_phone' => $driverPhone,
                    'status' => $deliveryStatus,
                    'shipped_at' => now(),
                    'delivered_at' => $isDelivered ? now() : null,
                    'notes' => 'Pengiriman via ' . $fulfillmentType,
                    'estimated_delivery_at' => $estimatedAt,
                    'estimated_delivery_min' => $estimatedMin,
                    'estimated_delivery_max' => $estimatedMax,
                    'estimated_delivery_duration' => $estimatedDuration,
                    'eta_source' => $etaSource,
                    'eta_notes' => $etaNotes,
                ]);
            } else {
                $deliveryUpdate = [
                    'packing_out_id' => $packingOut->id,
                    'status' => $deliveryStatus,
                ];
                if ($trackingNumber) {
                    $deliveryUpdate['tracking_number'] = $trackingNumber;
                }
                if ($courierId) {
                    $deliveryUpdate['courier_id'] = $courierId;
                }
                if ($driverName) {
                    $deliveryUpdate['driver_name'] = $driverName;
                }
                if ($driverPhone) {
                    $deliveryUpdate['driver_phone'] = $driverPhone;
                }
                if ($isDelivered && empty($delivery->delivered_at)) {
                    $deliveryUpdate['delivered_at'] = now();
                }
                if (empty($delivery->shipped_at)) {
                    $deliveryUpdate['shipped_at'] = now();
                }
                if ($estimatedAt) {
                    $deliveryUpdate['estimated_delivery_at'] = $estimatedAt;
                }
                if ($estimatedMin) {
                    $deliveryUpdate['estimated_delivery_min'] = $estimatedMin;
                }
                if ($estimatedMax) {
                    $deliveryUpdate['estimated_delivery_max'] = $estimatedMax;
                }
                if ($estimatedDuration) {
                    $deliveryUpdate['estimated_delivery_duration'] = $estimatedDuration;
                }
                if ($etaSource) {
                    $deliveryUpdate['eta_source'] = $etaSource;
                }
                if ($etaNotes) {
                    $deliveryUpdate['eta_notes'] = $etaNotes;
                }
                $delivery->update($deliveryUpdate);
            }

            // Record delivery log: Selesai Dikemas (ketika buat data delivery)
            if (DeliveryLog::where('order_id', $order->id)->where('event', 'delivery.created')->doesntExist()) {
                $logNote = 'Pesanan selesai dikemas dan siap dikirim (Data pengiriman dibuat)';
                if ($delivery->eta_label) {
                    $logNote .= ' - ' . ($delivery->eta_source_label ?? 'ETA') . ': ' . $delivery->eta_label;
                }
                DeliveryLog::create([
                    'order_id' => $order->id,
                    'delivery_id' => $delivery->id,
                    'waybill_id' => $trackingNumber,
                    'courier_code' => $courier?->code,
                    'event' => 'delivery.created',
                    'status' => 'dikemas',
                    'location' => 'Gudang Pengirim',
                    'note' => $logNote,
                    'payload' => [
                        'delivery_id' => $delivery->id,
                        'tracking_number' => $trackingNumber,
                        'courier_id' => $courierId,
                        'driver_name' => $driverName,
                        'eta' => $delivery->eta_label,
                        'eta_source' => $delivery->eta_source,
                    ],
                ]);
            }

            // 8. Handover & Handover Items
            $handover = Handover::where('order_id', $order->id)->first();
            $handoverStatus = $isDelivered ? 'completed' : 'in_transit';

            if (!$handover) {
                $handover = Handover::create([
                    'packing_out_id' => $packingOut->id,
                    'order_id' => $order->id,
                    'warehouse_id' => $warehouseId,
                    'courier_id' => $courierId,
                    'driver_name' => $driverName,
                    'driver_phone' => $driverPhone,
                    'tracking_number' => $trackingNumber,
                    'status' => $handoverStatus,
                    'notes' => 'Handover otomatis via alur bypass ' . $fulfillmentType,
                    'handover_at' => now(),
                ]);

                foreach ($order->items as $item) {
                    HandoverItem::create([
                        'handover_id' => $handover->id,
                        'order_item_id' => $item->id,
                        'product_id' => $item->product_id,
                        'product_variant_id' => $item->product_variant_id ?? null,
                        'quantity_ordered' => (int) $item->quantity,
                        'quantity_handed_over' => (int) $item->quantity,
                        'status' => 'handed_over',
                        'notes' => null,
                    ]);
                }
            } else {
                $handoverUpdate = [
                    'packing_out_id' => $packingOut->id,
                    'warehouse_id' => $warehouseId ?: $handover->warehouse_id,
                    'status' => $handoverStatus,
                ];
                if ($trackingNumber) {
                    $handoverUpdate['tracking_number'] = $trackingNumber;
                }
                if ($courierId) {
                    $handoverUpdate['courier_id'] = $courierId;
                }
                if ($driverName) {
                    $handoverUpdate['driver_name'] = $driverName;
                }
                if ($driverPhone) {
                    $handoverUpdate['driver_phone'] = $driverPhone;
                }
                if (empty($handover->handover_at)) {
                    $handoverUpdate['handover_at'] = now();
                }
                $handover->update($handoverUpdate);

                foreach ($order->items as $item) {
                    HandoverItem::updateOrCreate(
                        [
                            'handover_id' => $handover->id,
                            'order_item_id' => $item->id,
                        ],
                        [
                            'product_id' => $item->product_id,
                            'product_variant_id' => $item->product_variant_id ?? null,
                            'quantity_ordered' => (int) $item->quantity,
                            'quantity_handed_over' => (int) $item->quantity,
                            'status' => 'handed_over',
                        ]
                    );
                }
            }

            // Record delivery log: Diserahkan ke Kurir (ketika handover)
            if (DeliveryLog::where('order_id', $order->id)->where('event', 'handover.created')->doesntExist()) {
                $courierTitle = $courier?->name ?? ($driverName ?: 'Kurir');
                DeliveryLog::create([
                    'order_id' => $order->id,
                    'delivery_id' => $delivery?->id,
                    'waybill_id' => $trackingNumber,
                    'courier_code' => $courier?->code,
                    'event' => 'handover.created',
                    'status' => 'diserahkan_ke_kurir',
                    'location' => 'Gudang Pengirim',
                    'note' => "Paket telah diserahkan ke kurir ({$courierTitle})",
                    'payload' => [
                        'handover_id' => $handover->id,
                        'driver_name' => $driverName,
                        'courier_name' => $courierTitle,
                        'tracking_number' => $trackingNumber,
                    ],
                ]);
            }

            return [
                'picking_list' => $pickingList,
                'packing_slip' => $packingSlip,
                'packing_out' => $packingOut,
                'invoice' => $invoice,
                'delivery' => $delivery,
                'handover' => $handover,
            ];
        });
    }
}
