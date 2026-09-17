<?php

namespace App\Http\Controllers\Order;

use App\Http\Controllers\Controller;

use App\Models\Order\Order;
use App\Models\Packing\DeliveryLog;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $query = Order::query()->with(['customer', 'courier', 'delivery.courier', 'handover.courier']);

        if ($search = $request->query('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('id', 'ilike', "%{$search}%")
                  ->orWhereHas('customer', function ($cq) use ($search) {
                      $cq->where('name', 'ilike', "%{$search}%");
                  });
            });
        }

        if ($request->has('status') && $request->query('status') !== '') {
            $query->where('status', $request->query('status'));
        }

        if ($request->has('payment_status')) {
            $query->where('payment_status', $request->query('payment_status'));
        }

        $startDate = $request->query('start_date', now()->toDateString());
        $endDate = $request->query('end_date', now()->toDateString());

        $query->whereDate('created_at', '>=', $startDate)
              ->whereDate('created_at', '<=', $endDate);

        $orders = $query->orderByDesc('created_at')->paginate(10)->appends($request->query());

        $stats = [
            'total' => Order::whereDate('created_at', '>=', $startDate)
                           ->whereDate('created_at', '<=', $endDate)
                           ->count(),
            Order::STATUS_DRAFT => Order::where('status', Order::STATUS_DRAFT)
                ->whereDate('created_at', '>=', $startDate)
                ->whereDate('created_at', '<=', $endDate)
                ->count(),
            Order::STATUS_PENDING_APPROVAL => Order::where('status', Order::STATUS_PENDING_APPROVAL)
                ->whereDate('created_at', '>=', $startDate)
                ->whereDate('created_at', '<=', $endDate)
                ->count(),
            Order::STATUS_CONFIRMED => Order::where('status', Order::STATUS_CONFIRMED)
                ->whereDate('created_at', '>=', $startDate)
                ->whereDate('created_at', '<=', $endDate)
                ->count(),
            Order::STATUS_PROCESSING => Order::where('status', Order::STATUS_PROCESSING)
                ->whereDate('created_at', '>=', $startDate)
                ->whereDate('created_at', '<=', $endDate)
                ->count(),
            Order::STATUS_SHIPPED => Order::where('status', Order::STATUS_SHIPPED)
                ->whereDate('created_at', '>=', $startDate)
                ->whereDate('created_at', '<=', $endDate)
                ->count(),
            Order::STATUS_DELIVERED => Order::where('status', Order::STATUS_DELIVERED)
                ->whereDate('created_at', '>=', $startDate)
                ->whereDate('created_at', '<=', $endDate)
                ->count(),
            Order::STATUS_CANCELLED => Order::where('status', Order::STATUS_CANCELLED)
                ->whereDate('created_at', '>=', $startDate)
                ->whereDate('created_at', '<=', $endDate)
                ->count(),
            Order::STATUS_RETURNED => Order::where('status', Order::STATUS_RETURNED)
                ->whereDate('created_at', '>=', $startDate)
                ->whereDate('created_at', '<=', $endDate)
                ->count(),
        ];

        $couriers = \App\Models\Shipping\Courier::orderBy('name')->get();

        return view('pages.orders.index', compact('orders', 'stats', 'startDate', 'endDate', 'couriers'));
    }

    public function show(string $id)
    {
        $order = Order::with([
            'customer',
            'items.product.images',
            'items.variant',
            'courier',
            'pickingList',
            'packingSlip.items',
            'packingOut',
            'delivery.courier',
            'handover.courier',
            'invoice'
        ])->findOrFail($id);
        $couriers = \App\Models\Shipping\Courier::orderBy('name')->get();
        $biteshipService = app(\App\Services\BiteshipService::class);
        $biteshipConfigured = $biteshipService->isConfigured();
        $biteshipOrigin = $biteshipService->getOriginConfig();

        return view('pages.orders.show', compact('order', 'couriers', 'biteshipConfigured', 'biteshipOrigin'));
    }

    public function verifyPayment(Request $request, string $id)
    {
        $order = Order::findOrFail($id);

        if ((int)$order->payment_status === Order::PAYMENT_PAID) {
            return redirect()->route('orders.show', $id)->with('error', 'Pembayaran order ini sudah lunas (Paid) dan tidak dapat direkonsiliasi kembali.');
        }

        $validated = $request->validate([
            'payment_status' => 'required|integer|in:0,1,2,3,4',
            'status' => 'required|integer|in:0,1,2,3,4,5,6,7',
            'reconciliation_notes' => 'nullable|string|max:1000',
        ]);

        $meta = $order->meta ?? [];
        $meta['reconciliation_notes'] = $validated['reconciliation_notes'];
        $meta['reconciled_at'] = now()->toDateTimeString();
        $meta['reconciled_by'] = auth()->user()->name ?? 'admin';

        $oldStatus = (int)$order->status;
        $order->update([
            'payment_status' => $validated['payment_status'],
            'status' => $validated['status'],
            'meta' => $meta,
            'editor' => auth()->user()->name ?? 'admin',
        ]);

        if ((int)$validated['status'] === Order::STATUS_CANCELLED && $oldStatus !== Order::STATUS_CANCELLED) {
            foreach ($order->items as $item) {
                if ($item->product_variant_id) {
                    \App\Services\InventoryService::recordWebOrderCancelled($item->product_variant_id, (int)$item->quantity);
                }
            }
        }

        return redirect()->route('orders.show', $id)->with('success', 'Order payment reconciliation completed successfully');
    }

    public function updateStatus(Request $request, string $id)
    {
        $order = Order::with(['items', 'delivery', 'handover'])->findOrFail($id);

        $validated = $request->validate([
            'status' => 'required|integer|in:0,1,2,3,4,5,6,7',
            'notes' => 'nullable|string|max:500',
            'tracking_number' => 'nullable|string|max:100',
            'courier_id' => 'nullable|string|exists:couriers,id',
        ]);

        $newStatus = (int) $validated['status'];
        $oldStatus = (int) $order->status;

        // Cegah perubahan status jika order sudah Shipped atau Delivered
        if (in_array($oldStatus, [Order::STATUS_SHIPPED, Order::STATUS_DELIVERED])) {
            return response()->json([
                'success' => false,
                'message' => 'Status pesanan ' . $order->statusLabel() . ' sudah terkunci dan tidak dapat diubah lagi.',
            ], 422);
        }

        $meta = $order->meta ?? [];

        if (!empty($validated['notes'])) {
            $meta['status_change_notes'] = $validated['notes'];
        }

        if (!empty($validated['tracking_number'])) {
            $trackingNumber = trim($validated['tracking_number']);
            $meta['tracking_number'] = $trackingNumber;
            $meta['resi'] = $trackingNumber;
            $meta['resi_updated_at'] = now()->toDateTimeString();
            $meta['resi_updated_by'] = auth()->user()->name ?? 'admin';
        }

        $updateData = [
            'status' => $newStatus,
            'meta' => $meta,
            'editor' => auth()->user()->name ?? 'admin',
        ];

        if (!empty($validated['courier_id'])) {
            $updateData['courier_id'] = $validated['courier_id'];
        }

        $order->update($updateData);

        // Inventory movements
        if ($newStatus === Order::STATUS_CANCELLED && $oldStatus !== Order::STATUS_CANCELLED) {
            foreach ($order->items as $item) {
                if ($item->product_variant_id) {
                    \App\Services\InventoryService::recordWebOrderCancelled($item->product_variant_id, (int) $item->quantity);
                }
            }
        } elseif ($newStatus === Order::STATUS_SHIPPED && $oldStatus !== Order::STATUS_SHIPPED && $oldStatus !== Order::STATUS_DELIVERED) {
            foreach ($order->items as $item) {
                if ($item->product_variant_id) {
                    \App\Services\InventoryService::recordWebOrderShipped($item->product_variant_id, (int) $item->quantity);
                }
            }
        } elseif ($newStatus === Order::STATUS_DELIVERED && $oldStatus !== Order::STATUS_DELIVERED) {
            if ($oldStatus < Order::STATUS_SHIPPED) {
                foreach ($order->items as $item) {
                    if ($item->product_variant_id) {
                        \App\Services\InventoryService::recordWebOrderShipped($item->product_variant_id, (int) $item->quantity);
                    }
                }
            }
            foreach ($order->items as $item) {
                if ($item->product_variant_id) {
                    \App\Services\InventoryService::recordWebOrderDelivered($item->product_variant_id, (int) $item->quantity);
                }
            }
        }

        // Auto-populate fulfillment pipeline from Picking List to Handover
        if ($newStatus === Order::STATUS_SHIPPED || $newStatus === Order::STATUS_DELIVERED) {
            \App\Services\OrderFulfillmentService::completeFulfillmentPipeline($order, [
                'tracking_number' => $trackingNumber ?? $order->resi,
                'courier_id' => $validated['courier_id'] ?? $order->courier_id,
                'target_status' => $newStatus,
                'fulfillment_type' => 'status_override',
            ]);
        }

        $order->load(['courier', 'delivery.courier', 'handover.courier', 'pickingList', 'packingSlip', 'packingOut']);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Status pesanan berhasil diperbarui menjadi ' . $order->statusLabel() . '.',
                'status' => $order->status,
                'status_label' => $order->statusLabel(),
                'status_badge_class' => $order->statusBadgeClass,
            ]);
        }

        return redirect()->route('orders.show', $id)->with('success', 'Status pesanan berhasil diperbarui menjadi ' . $order->statusLabel() . '.');
    }

    public function updateResi(Request $request, string $id)
    {
        $order = Order::with(['delivery', 'handover', 'courier', 'items'])->findOrFail($id);

        // Lock if order is already Shipped (4) or Delivered (5)
        if ((int) $order->status >= Order::STATUS_SHIPPED) {
            $statusName = $order->statusLabel();
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => "Pesanan sudah dalam status {$statusName}. Nomor resi sudah terkunci dan tidak dapat diubah atau ditambah lagi.",
                ], 422);
            }
            return redirect()->route('orders.show', $id)
                ->with('error', "Pesanan sudah dalam status {$statusName}. Nomor resi sudah terkunci dan tidak dapat diubah atau ditambah lagi.");
        }

        $validated = $request->validate([
            'tracking_number' => 'required|string|max:100',
            'courier_id' => 'nullable|string|exists:couriers,id',
            'status' => 'nullable|integer|in:0,1,2,3,4,5,6,7',
        ]);

        $trackingNumber = trim($validated['tracking_number']);
        $meta = $order->meta ?? [];
        $meta['tracking_number'] = $trackingNumber;
        $meta['resi'] = $trackingNumber;
        $meta['fulfillment_type'] = 'manual';
        $meta['resi_updated_at'] = now()->toDateTimeString();
        $meta['resi_updated_by'] = auth()->user()->name ?? 'admin';

        $updateData = [
            'meta' => $meta,
            'editor' => auth()->user()->name ?? 'admin',
        ];

        if (!empty($validated['courier_id'])) {
            $updateData['courier_id'] = $validated['courier_id'];
        }

        $targetStatus = isset($validated['status'])
            ? (int) $validated['status']
            : ((int) $order->status < Order::STATUS_SHIPPED ? Order::STATUS_SHIPPED : (int) $order->status);

        $updateData['status'] = $targetStatus;

        $oldStatus = (int) $order->status;
        $order->update($updateData);

        // Inventory movements
        if ($targetStatus === Order::STATUS_SHIPPED && $oldStatus < Order::STATUS_SHIPPED) {
            foreach ($order->items as $item) {
                if ($item->product_variant_id) {
                    \App\Services\InventoryService::recordWebOrderShipped($item->product_variant_id, (int) $item->quantity);
                }
            }
        } elseif ($targetStatus === Order::STATUS_DELIVERED && $oldStatus !== Order::STATUS_DELIVERED) {
            if ($oldStatus < Order::STATUS_SHIPPED) {
                foreach ($order->items as $item) {
                    if ($item->product_variant_id) {
                        \App\Services\InventoryService::recordWebOrderShipped($item->product_variant_id, (int) $item->quantity);
                    }
                }
            }
            foreach ($order->items as $item) {
                if ($item->product_variant_id) {
                    \App\Services\InventoryService::recordWebOrderDelivered($item->product_variant_id, (int) $item->quantity);
                }
            }
        }

        // Auto-populate fulfillment pipeline from Picking List to Handover
        try {
            \App\Services\OrderFulfillmentService::completeFulfillmentPipeline($order, [
                'tracking_number' => $trackingNumber,
                'courier_id' => $validated['courier_id'] ?? $order->courier_id,
                'target_status' => $targetStatus,
                'fulfillment_type' => 'manual',
            ]);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning("Gagal memperbarui dokumen alur gudang otomatis saat input resi manual #{$order->order_number}: " . $e->getMessage(), [
                'order_id' => $order->id,
            ]);
        }

        $order->load(['courier', 'delivery.courier', 'handover.courier', 'pickingList', 'packingSlip', 'packingOut']);

        // Log manual resi update activity
        app(\App\Services\BiteshipService::class)->logStructured(
            'info',
            'ACTIVITY',
            "Input Resi Manual Pesanan #{$order->order_number}",
            [
                'Nomor Order' => $order->order_number,
                'Order ID' => $order->id,
                'Nomor Resi' => $trackingNumber,
                'Ekspedisi' => $order->courier?->name ?? 'Kurir',
                'Status Target' => $targetStatus,
                'Alur Gudang WMS' => 'Picking List, Packing Slip, Packing Out, Handover, Invoice (Selesai Otomatis)',
            ]
        );

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Nomor resi berhasil diperbarui.',
                'order_id' => $order->id,
                'tracking_number' => $order->resi,
                'courier_name' => $order->courier_name ?? 'Kurir',
                'status' => $order->status,
                'status_label' => $order->statusLabel(),
                'status_badge_class' => $order->statusBadgeClass,
            ]);
        }

        return redirect()->back()->with('success', 'Nomor resi berhasil diperbarui.');
    }

    public function hitBiteship(Request $request, string $id)
    {
        $order = Order::with(['customer', 'items.product', 'items.variant', 'courier', 'delivery', 'handover'])->findOrFail($id);

        $hasBiteshipResi = !empty($order->meta['biteship_order_id'])
            || (!empty($order->resi) && ($order->meta['fulfillment_type'] ?? '') === 'biteship');

        if ($hasBiteshipResi) {
            return response()->json([
                'success' => false,
                'message' => 'Pesanan ini sudah berhasil dibuat di Biteship dengan Nomor Resi ' . ($order->resi ?? $order->meta['biteship_order_id']) . '. Pengiriman tidak dapat di-hit ulang.',
            ], 422);
        }

        $biteshipService = app(\App\Services\BiteshipService::class);
        if (!$biteshipService->isConfigured()) {
            $biteshipService->logStructured('warning', 'CONFIG', "Biteship API Key Belum Dikonfigurasi di .env (#{$order->order_number})", [
                'Nomor Order' => $order->order_number,
                'Order ID' => $order->id,
                'Keterangan' => 'Admin mencoba hit Biteship tetapi BITESHIP_API_KEY kosong di .env',
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Biteship API Key belum dikonfigurasi di .env (BITESHIP_API_KEY). Silakan isi Key terlebih dahulu atau gunakan Tab Input Resi Manual.',
            ], 422);
        }

        $validated = $request->validate([
            'courier_company' => 'nullable|string|max:50',
            'courier_type' => 'nullable|string|max:50',
            'destination_postal_code' => 'nullable|integer|digits:5',
            'destination_note' => 'nullable|string|max:200',
            'origin_note' => 'nullable|string|max:200',
            'final_status' => 'nullable|integer|in:4,5',
        ]);

        $shippingMeta = $order->meta['shipping_address'] ?? [];
        $postalCode = !empty($validated['destination_postal_code'])
            ? (int) $validated['destination_postal_code']
            : (int) ($shippingMeta['postal_code'] ?? 0);

        if ($postalCode <= 0) {
            $biteshipService->logStructured('warning', 'VALIDASI', "Pengiriman Biteship Gagal - Kode Pos Tujuan Kosong (#{$order->order_number})", [
                'Nomor Order' => $order->order_number,
                'Order ID' => $order->id,
                'Keterangan' => 'Kode pos tujuan 5-digit belum terisi pada alamat pengiriman.',
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Kode pos tujuan belum diisi. Silakan isi kode pos tujuan 5 digit pada form pengiriman.',
            ], 422);
        }

        $courierCompany = !empty($validated['courier_company']) ? $validated['courier_company'] : null;
        $courierType = !empty($validated['courier_type']) ? $validated['courier_type'] : null;
        $originNote = $validated['origin_note'] ?? null;
        $destinationNote = $validated['destination_note'] ?? null;
        $targetStatus = (int) ($validated['final_status'] ?? Order::STATUS_SHIPPED);

        $payload = $biteshipService->buildOrderPayload($order, $courierCompany, $courierType, $originNote, $destinationNote);
        if (!empty($validated['destination_postal_code'])) {
            $payload['destination_postal_code'] = (int) $validated['destination_postal_code'];
        }

        $biteshipRes = $biteshipService->createOrder($payload);

        if (!($biteshipRes['success'] ?? false)) {
            return response()->json([
                'success' => false,
                'message' => 'Biteship Error: ' . ($biteshipRes['message'] ?? 'Gagal membuat pesanan pengiriman di Biteship.'),
                'raw' => $biteshipRes['raw'] ?? null,
            ], 422);
        }

        $waybillId = $biteshipRes['waybill_id'] ?? null;
        $biteshipOrderId = $biteshipRes['order_id'] ?? null;
        $courierData = $biteshipRes['courier'] ?? [];
        $trackingUrl = $courierData['tracking_url'] ?? null;

        $meta = $order->meta ?? [];
        if ($waybillId) {
            $meta['tracking_number'] = $waybillId;
            $meta['resi'] = $waybillId;
        }
        $meta['biteship_order_id'] = $biteshipOrderId;
        $meta['biteship_shipment'] = $biteshipRes['raw'] ?? [];
        $meta['biteship_payload'] = $payload;
        $meta['biteship_request_payload'] = $payload;
        $meta['biteship_tracking_url'] = $trackingUrl;
        $meta['fulfillment_type'] = 'biteship';
        $meta['resi_updated_at'] = now()->toDateTimeString();
        $meta['resi_updated_by'] = auth()->user()->name ?? 'admin';

        $oldStatus = (int) $order->status;

        $updateData = [
            'meta' => $meta,
            'status' => $targetStatus,
            'editor' => auth()->user()->name ?? 'admin',
        ];

        $usedCompany = $courierData['company'] ?? ($payload['courier_company'] ?? '');
        if ($usedCompany) {
            $cleanCompany = strtolower(trim((string) $usedCompany));
            $mappedCompany = $biteshipService->mapCourierCompany($cleanCompany);
            $matchedCourier = \App\Models\Shipping\Courier::where(function ($q) use ($cleanCompany, $mappedCompany) {
                $q->whereRaw('LOWER(code) = ?', [$cleanCompany])
                  ->orWhereRaw('LOWER(code) = ?', [$mappedCompany]);
            })->first();
            if ($matchedCourier) {
                $updateData['courier_id'] = $matchedCourier->id;
            }
        }

        $order->update($updateData);

        // Deduct inventory
        if ($targetStatus === Order::STATUS_SHIPPED && $oldStatus < Order::STATUS_SHIPPED) {
            foreach ($order->items as $item) {
                if ($item->product_variant_id) {
                    \App\Services\InventoryService::recordWebOrderShipped($item->product_variant_id, (int) $item->quantity);
                }
            }
        } elseif ($targetStatus === Order::STATUS_DELIVERED && $oldStatus !== Order::STATUS_DELIVERED) {
            if ($oldStatus < Order::STATUS_SHIPPED) {
                foreach ($order->items as $item) {
                    if ($item->product_variant_id) {
                        \App\Services\InventoryService::recordWebOrderShipped($item->product_variant_id, (int) $item->quantity);
                    }
                }
            }
            foreach ($order->items as $item) {
                if ($item->product_variant_id) {
                    \App\Services\InventoryService::recordWebOrderDelivered($item->product_variant_id, (int) $item->quantity);
                }
            }
        }

        // Auto-populate fulfillment pipeline from Picking List to Handover
        try {
            \App\Services\OrderFulfillmentService::completeFulfillmentPipeline($order, [
                'tracking_number' => $waybillId,
                'courier_id' => $updateData['courier_id'] ?? $order->courier_id,
                'driver_name' => $courierData['company'] ?? (!empty($usedCompany) ? strtoupper($usedCompany) : 'Biteship Courier'),
                'target_status' => $targetStatus,
                'fulfillment_type' => 'biteship',
            ]);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning("Gagal memperbarui dokumen alur gudang otomatis saat hit biteship #{$order->order_number}: " . $e->getMessage(), [
                'order_id' => $order->id,
            ]);
        }

        // Record initial outgoing order.created log in delivery_logs
        try {
            DeliveryLog::create([
                'order_id' => $order->id,
                'delivery_id' => $order->delivery?->id,
                'waybill_id' => $waybillId ?: ($order->resi),
                'biteship_order_id' => $biteshipOrderId,
                'courier_code' => $usedCompany ?: ($order->courier?->code),
                'event' => 'order.created',
                'status' => 'allocated',
                'location' => 'Gudang Pengirim',
                'note' => 'Pesanan berhasil dibuat di Biteship (Order Created) - Menunggu penjemputan kurir',
                'payload' => $payload,
            ]);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning("Gagal menyimpan log order.created ke delivery_logs #{$order->order_number}: " . $e->getMessage());
        }

        $order->load(['courier', 'delivery.courier', 'handover.courier', 'pickingList', 'packingSlip', 'packingOut']);

        return response()->json([
            'success' => true,
            'message' => 'Pesanan berhasil dikirim ke Biteship! Nomor Resi: ' . ($waybillId ?: 'Terbit di Biteship'),
            'data' => [
                'order_id' => $order->id,
                'tracking_number' => $order->resi,
                'biteship_order_id' => $biteshipOrderId,
                'courier_name' => $order->courier_name ?? strtoupper($usedCompany),
                'tracking_url' => $trackingUrl,
                'status' => $order->status,
                'status_label' => $order->statusLabel(),
                'status_badge_class' => $order->statusBadgeClass,
            ],
        ]);
    }

    public function trackBiteship(Request $request, string $id)
    {
        $order = Order::with(['courier', 'delivery.courier'])->findOrFail($id);

        $resi = $order->resi;
        $courierName = $order->courier_name ?? $order->courier?->name ?? 'Kurir';
        $courierCode = $order->courier?->code ?? strtolower(explode(' ', (string)$courierName)[0] ?? 'kurir');
        $biteshipOrderId = $order->meta['biteship_order_id'] ?? null;
        $deliveryId = $order->delivery?->id;

        // Retrieve delivery logs from DB (matching by order_id, waybill_id, biteship_order_id, or delivery_id)
        $logs = DeliveryLog::where(function ($query) use ($order, $resi, $biteshipOrderId, $deliveryId) {
            $query->where('order_id', $order->id);
            if (!empty($resi)) {
                $query->orWhere('waybill_id', $resi);
            }
            if (!empty($biteshipOrderId)) {
                $query->orWhere('biteship_order_id', $biteshipOrderId);
            }
            if (!empty($deliveryId)) {
                $query->orWhere('delivery_id', $deliveryId);
            }
        })
        ->orderBy('created_at', 'desc')
        ->get();

        if (empty($resi) && $logs->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Pesanan ini belum memiliki riwayat pengiriman atau nomor resi.',
            ], 404);
        }

        // Apply statusStageWeight tiebreaker for events recorded at identical second timestamps
        $logs = $logs->sort(function ($a, $b) {
            $timeCompare = ($b->created_at?->timestamp ?? 0) <=> ($a->created_at?->timestamp ?? 0);
            if ($timeCompare !== 0) {
                return $timeCompare;
            }
            return Order::statusStageWeight($b->status) <=> Order::statusStageWeight($a->status);
        })->values();

        $events = [];
        foreach ($logs as $log) {
            $info = Order::deliveryStatusInfo($log->status, $log->event);
            $events[] = [
                'time' => $log->created_at ? $log->created_at->format('d M Y H:i') : '-',
                'status' => $info['label'] ?? ($log->note ?: ($log->status ?: 'Status Checkpoint')),
                'raw_status' => $log->status,
                'event' => $log->event,
                'location' => $log->location ?: null,
                'description' => $log->note ?: ($info['label'] . ($log->location ? " di {$log->location}" : '')),
                'payload' => $log->payload,
            ];
        }

        // If no webhook events logged yet, provide initial baseline checkpoint
        if (empty($events)) {
            $events[] = [
                'time' => !empty($order->meta['resi_updated_at'])
                    ? \Carbon\Carbon::parse($order->meta['resi_updated_at'])->format('d M Y H:i')
                    : ($order->updated_at ? $order->updated_at->format('d M Y H:i') : '-'),
                'status' => 'Resi Diterbitkan',
                'raw_status' => 'waybill_issued',
                'event' => 'order.waybill_issued',
                'location' => 'Gudang Pengirim',
                'description' => 'Nomor resi ' . ($resi ?: '-') . ' telah diterbitkan. Menunggu pembaruan status log dari webhook kurir.',
                'payload' => $order->meta['biteship_payload'] ?? null,
            ];
        }

        $latestLog = $logs->first();
        $latestStatusInfo = Order::deliveryStatusInfo($latestLog?->status ?? $order->delivery_status);

        $trackingUrl = $order->meta['biteship_tracking_url']
            ?? data_get($latestLog?->payload, 'courier_link')
            ?? data_get($latestLog?->payload, 'courier.link');

        return response()->json([
            'success' => true,
            'source' => 'webhook_logs',
            'data' => [
                'resi' => $resi,
                'tracking_number' => $resi,
                'courier' => $courierCode,
                'courier_name' => $courierName,
                'status' => $latestStatusInfo['label'],
                'status_raw' => $latestLog?->status ?? $order->delivery_status,
                'status_badge_class' => $latestStatusInfo['badge_class'],
                'status_icon' => $latestStatusInfo['icon'],
                'link' => $trackingUrl,
                'last_updated' => $latestLog?->created_at?->format('d M Y H:i') ?? $order->updated_at?->format('d M Y H:i'),
                'events' => $events,
            ],
        ]);
    }
}
