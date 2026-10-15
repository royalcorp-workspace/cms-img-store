<?php

namespace App\Http\Controllers\Order;

use App\Http\Controllers\Controller;

use App\Models\Order\Order;
use App\Models\Packing\DeliveryLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

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
            'store',
            'storeChannel',
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
        } elseif ($oldStatus !== $newStatus) {
            try {
                \App\Services\ShippingNotificationService::notifyShippingUpdate($order, (string) $newStatus);
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning("Failed to dispatch order status notification for #{$order->order_number}: " . $e->getMessage());
            }
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
            'estimated_delivery_at' => 'nullable|string',
            'estimated_delivery_duration' => 'nullable|string|max:100',
            'eta_notes' => 'nullable|string|max:500',
        ]);

        $trackingNumber = trim($validated['tracking_number']);
        $isKurirToko = $order->isKurirToko();
        $etaSource = $isKurirToko ? 'store' : 'manual';
        $estimatedAt = !empty($validated['estimated_delivery_at']) ? \Carbon\Carbon::parse($validated['estimated_delivery_at']) : null;
        $estimatedDuration = !empty($validated['estimated_delivery_duration']) ? trim((string)$validated['estimated_delivery_duration']) : null;
        $etaNotes = !empty($validated['eta_notes']) ? trim((string)$validated['eta_notes']) : null;

        if ($estimatedDuration && !$estimatedAt) {
            $calc = \App\Services\EtaService::calculateEta($estimatedDuration);
            $estimatedAt = $calc['estimated_at'];
        }

        $meta = $order->meta ?? [];
        $meta['tracking_number'] = $trackingNumber;
        $meta['resi'] = $trackingNumber;
        $meta['fulfillment_type'] = 'manual';
        $meta['resi_updated_at'] = now()->toDateTimeString();
        $meta['resi_updated_by'] = auth()->user()->name ?? 'admin';
        if ($estimatedAt) {
            $meta['estimated_delivery_at'] = $estimatedAt->toDateTimeString();
        }
        if ($estimatedDuration) {
            $meta['estimated_delivery_duration'] = $estimatedDuration;
        }
        if ($etaNotes) {
            $meta['eta_notes'] = $etaNotes;
        }

        $updateData = [
            'meta' => $meta,
            'editor' => auth()->user()->name ?? 'admin',
        ];

        // Kurir dikunci menggunakan bawaan dari checkout; fallback hanya jika order belum memiliki kurir
        $finalCourierId = $order->courier_id ?: (!empty($validated['courier_id']) ? $validated['courier_id'] : null);
        if (!empty($finalCourierId)) {
            $updateData['courier_id'] = $finalCourierId;
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
                'courier_id' => $finalCourierId ?? $order->courier_id,
                'target_status' => $targetStatus,
                'fulfillment_type' => 'manual',
                'estimated_delivery_at' => $estimatedAt,
                'estimated_delivery_duration' => $estimatedDuration,
                'eta_source' => $etaSource,
                'eta_notes' => $etaNotes,
            ]);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning("Gagal memperbarui dokumen alur gudang otomatis saat input resi manual #{$order->order_number}: " . $e->getMessage(), [
                'order_id' => $order->id,
            ]);
            try {
                \App\Services\ShippingNotificationService::notifyShippingUpdate(
                    $order,
                    'in_transit',
                    null,
                    null,
                    $trackingNumber,
                    $order->courier?->name ?? 'Kurir'
                );
            } catch (\Throwable $ne) {}
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

        if ($order->isKurirToko() || ($order->courier && $order->courier->courier_type === 'toko') || strtolower((string)$order->courier?->code) === 'kurir_toko') {
            return response()->json([
                'success' => false,
                'is_warning' => true,
                'message' => 'Pesanan ini dikirim menggunakan armada Kurir Toko. Layanan resi ekspedisi otomatis tidak dapat digunakan untuk kurir toko. Silakan gunakan tab Input Resi Manual.',
            ], 422);
        }

        $hasBiteshipResi = !empty($order->meta['biteship_order_id'])
            || (!empty($order->resi) && ($order->meta['fulfillment_type'] ?? '') === 'biteship');

        if ($hasBiteshipResi) {
            return response()->json([
                'success' => false,
                'is_warning' => true,
                'message' => 'Nomor resi untuk pesanan ini sudah berhasil diterbitkan (' . ($order->resi ?? $order->meta['biteship_order_id']) . '). Pengambilan resi otomatis tidak dapat diulang.',
            ], 422);
        }

        $biteshipService = app(\App\Services\BiteshipService::class);
        if (!$biteshipService->isConfigured()) {
            $biteshipService->logStructured('warning', 'CONFIG', "Layanan Ekspedisi Otomatis Belum Dikonfigurasi (#{$order->order_number})", [
                'Nomor Order' => $order->order_number,
                'Order ID' => $order->id,
                'Keterangan' => 'Layanan ekspedisi belum aktif.',
            ]);

            return response()->json([
                'success' => false,
                'is_warning' => true,
                'message' => 'Layanan pengambilan resi ekspedisi otomatis belum diaktifkan. Silakan gunakan tab Input Resi Manual untuk memasukkan nomor resi atau surat jalan.',
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

        // Kurir & tipe layanan dikunci menggunakan bawaan dari checkout
        $checkoutCourierCode = strtolower((string)($order->courier?->code ?? ''));
        $checkoutCourierName = strtolower((string)($order->courier?->name ?? ''));
        $courierCompany = $biteshipService->mapCourierCompany($checkoutCourierCode)
            ?: $biteshipService->mapCourierCompany($checkoutCourierName)
            ?: (!empty($validated['courier_company']) ? $biteshipService->mapCourierCompany($validated['courier_company']) : 'jne');

        $checkoutCourierType = strtolower(trim((string)(
            $order->meta['courier_service_type']
            ?? $order->meta['shipping_service_code']
            ?? $order->meta['shipping_address']['courier_service_code']
            ?? ''
        )));
        $courierType = $checkoutCourierType ?: (!empty($validated['courier_type']) ? $validated['courier_type'] : 'reg');
        $originNote = $validated['origin_note'] ?? null;
        $destinationNote = $validated['destination_note'] ?? null;
        $targetStatus = (int) ($validated['final_status'] ?? Order::STATUS_SHIPPED);

        $payload = $biteshipService->buildOrderPayload($order, $courierCompany, $courierType, $originNote, $destinationNote);
        if (!empty($validated['destination_postal_code'])) {
            $payload['destination_postal_code'] = (int) $validated['destination_postal_code'];
        }

        $biteshipRes = $biteshipService->createOrder($payload);

        if (!($biteshipRes['success'] ?? false)) {
            $rawErrMsg = $biteshipRes['message'] ?? 'Gagal membuat pesanan pengiriman di vendor ekspedisi.';
            $cleanErrMsg = str_ireplace('biteship', 'vendor ekspedisi', $rawErrMsg);
            return response()->json([
                'success' => false,
                'message' => $cleanErrMsg,
                'raw' => $biteshipRes['raw'] ?? null,
            ], 422);
        }

        $waybillId = $biteshipRes['waybill_id'] ?? null;
        $biteshipOrderId = $biteshipRes['order_id'] ?? null;
        $courierData = $biteshipRes['courier'] ?? [];
        $trackingUrl = $courierData['tracking_url'] ?? null;

        $rawBiteshipDuration = data_get($biteshipRes['raw'], 'shipment.duration')
            ?? data_get($biteshipRes['raw'], 'courier.duration')
            ?? data_get($biteshipRes['raw'], 'duration')
            ?? data_get($biteshipRes['raw'], 'shipment_duration_range')
            ?? ($order->meta['shipping_duration'] ?? '1-2 hari');

        $biteshipEta = \App\Services\EtaService::calculateEta((string) $rawBiteshipDuration);

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
        $meta['biteship_eta'] = $biteshipEta;
        $meta['estimated_delivery_at'] = $biteshipEta['estimated_at']->toDateTimeString();
        $meta['estimated_delivery_duration'] = $biteshipEta['duration'];
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
        if ($usedCompany && empty($order->courier_id)) {
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
                'estimated_delivery_at' => $biteshipEta['estimated_at'],
                'estimated_delivery_min' => $biteshipEta['min_date'],
                'estimated_delivery_max' => $biteshipEta['max_date'],
                'estimated_delivery_duration' => $biteshipEta['duration'],
                'eta_source' => 'biteship',
                'eta_notes' => $biteshipEta['formatted_label'],
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
                'note' => 'Nomor resi berhasil diterbitkan dari pihak ekspedisi - Menunggu penjemputan kurir',
                'payload' => $payload,
            ]);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning("Gagal menyimpan log order.created ke delivery_logs #{$order->order_number}: " . $e->getMessage());
        }

        $order->load(['courier', 'delivery.courier', 'handover.courier', 'pickingList', 'packingSlip', 'packingOut']);

        return response()->json([
            'success' => true,
            'message' => 'Nomor resi pengiriman berhasil diterbitkan otomatis dari vendor ekspedisi: ' . ($waybillId ?: ($order->resi ?: '-')),
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
        $order = \Illuminate\Support\Str::isUuid($id)
            ? Order::with(['courier', 'delivery.courier', 'pickingList', 'packingSlip', 'handover', 'payments'])->find($id)
            : Order::with(['courier', 'delivery.courier', 'pickingList', 'packingSlip', 'handover', 'payments'])->where('order_number', $id)->first();

        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Data pesanan tidak ditemukan.',
                'data' => null,
            ], 200);
        }

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
        ->orderBy('created_at', 'asc')
        ->get();

        $events = [];

        // 1. Pesanan Dibuat
        if ($order->created_at) {
            $events[] = [
                'stage' => 'created',
                'time' => $order->created_at->format('d M Y H:i'),
                'status' => 'Pesanan Dibuat',
                'raw_status' => 'order_created',
                'event' => 'order.created',
                'icon' => 'shopping_bag',
                'location' => 'Online Store',
                'description' => "Pesanan #{$order->order_number} berhasil dibuat oleh pelanggan.",
                'timestamp' => $order->created_at->timestamp,
                'weight' => -3,
            ];
        }

        // 2. Pembayaran Berhasil / Dikonfirmasi
        $isPaid = (int)$order->payment_status === Order::PAYMENT_PAID || (int)$order->status >= Order::STATUS_CONFIRMED;
        if ($isPaid) {
            $payment = $order->payments()->where('status', 'success')->latest()->first();
            $payTime = $payment?->created_at ?? ($order->created_at ? $order->created_at->copy()->addSeconds(30) : now());
            $payMethod = $order->payment_method ? ucwords(str_replace('_', ' ', $order->payment_method)) : 'Metode Pembayaran';
            $events[] = [
                'stage' => 'paid',
                'time' => $payTime->format('d M Y H:i'),
                'status' => 'Pembayaran Terverifikasi',
                'raw_status' => 'payment_verified',
                'event' => 'payment.verified',
                'icon' => 'paid',
                'location' => 'Sistem Pembayaran',
                'description' => "Pembayaran pesanan telah diverifikasi via {$payMethod}. Pesanan siap diproses.",
                'timestamp' => $payTime->timestamp,
                'weight' => -2,
            ];
        }

        // 3. Pesanan Diproses (Picking)
        $isProcessing = (int)$order->status >= Order::STATUS_PROCESSING || $order->pickingList()->exists();
        if ($isProcessing) {
            $procTime = $order->pickingList?->created_at ?? ($order->created_at ? $order->created_at->copy()->addMinutes(5) : now());
            $events[] = [
                'stage' => 'processing',
                'time' => $procTime->format('d M Y H:i'),
                'status' => 'Pesanan Diproses',
                'raw_status' => 'processing',
                'event' => 'order.processing',
                'icon' => 'inventory_2',
                'location' => 'Gudang Pengirim',
                'description' => 'Pesanan sedang diproses dan disiapkan oleh tim gudang.',
                'timestamp' => $procTime->timestamp,
                'weight' => -1,
            ];
        }

        $hasLogSedangDikemas = $logs->contains(fn($l) => in_array(strtolower($l->status), ['sedang_dikemas', 'packing']));
        $hasLogDikemas = $logs->contains(fn($l) => in_array(strtolower($l->status), ['dikemas', 'packed', 'siap_dikirim']));
        $hasLogHandover = $logs->contains(fn($l) => in_array(strtolower($l->status), ['diserahkan_ke_kurir', 'handover', 'handed_over']));
        $hasLogDelivered = $logs->contains(fn($l) => strtolower($l->status) === 'delivered');

        // 4. Dikemas (jika belum tercatat di DeliveryLog)
        $isPackingOrBeyond = $order->packingSlip()->exists() || (int)$order->status >= Order::STATUS_PROCESSING;
        if ($isPackingOrBeyond && !$hasLogSedangDikemas && !$hasLogDikemas) {
            $packTime = $order->packingSlip?->created_at ?? ($order->created_at ? $order->created_at->copy()->addMinutes(15) : now());
            $isPacked = $order->packingSlip?->status === 'packed' || (int)$order->status >= Order::STATUS_SHIPPED || !empty($resi);
            $events[] = [
                'stage' => 'packing',
                'time' => $packTime->format('d M Y H:i'),
                'status' => $isPacked ? 'Selesai Dikemas' : 'Sedang Dikemas',
                'raw_status' => $isPacked ? 'dikemas' : 'sedang_dikemas',
                'event' => $isPacked ? 'order.packed' : 'order.packing',
                'icon' => 'package',
                'location' => 'Bagian Packing Gudang',
                'description' => $isPacked
                    ? 'Pesanan telah selesai dikemas rapi dan siap diserahkan ke kurir pengiriman.'
                    : 'Pesanan sedang dikemas oleh tim gudang.',
                'timestamp' => $packTime->timestamp,
                'weight' => $isPacked ? 2 : 1,
            ];
        }

        // 5. Diserahkan ke Kurir (jika belum tercatat di DeliveryLog)
        $isHandoverOrBeyond = $order->handover()->exists() || (int)$order->status >= Order::STATUS_SHIPPED || !empty($resi);
        if ($isHandoverOrBeyond && !$hasLogHandover) {
            $handoverTime = $order->handover?->created_at
                ?? $order->delivery?->shipped_at
                ?? ($order->created_at ? $order->created_at->copy()->addMinutes(30) : now());
            $events[] = [
                'stage' => 'handover',
                'time' => $handoverTime->format('d M Y H:i'),
                'status' => 'Diserahkan ke Kurir',
                'raw_status' => 'diserahkan_ke_kurir',
                'event' => 'order.handover',
                'icon' => 'local_shipping',
                'location' => 'Gudang Pengirim',
                'description' => "Paket telah diserahkan kepada kurir {$courierName}" . (!empty($resi) ? " (No. Resi: {$resi})" : '') . '.',
                'timestamp' => $handoverTime->timestamp,
                'weight' => 3,
            ];
        }

        // 6. Checkpoint Log Pengiriman & Ekspedisi (Biteship webhook / kurir logs)
        foreach ($logs as $log) {
            $info = Order::deliveryStatusInfo($log->status, $log->event);
            $desc = $log->note;
            if (empty($desc)) {
                $desc = $info['label'] . ($log->location ? " di {$log->location}" : '');
            }

            $events[] = [
                'stage' => 'log_' . $log->id,
                'time' => $log->created_at ? $log->created_at->format('d M Y H:i') : '-',
                'status' => $info['label'] ?? ($log->note ?: ($log->status ?: 'Status Checkpoint')),
                'raw_status' => $log->status,
                'event' => $log->event,
                'icon' => $info['icon'] ?? 'local_shipping',
                'location' => $log->location ?: ($log->status === 'delivered' ? 'Alamat Tujuan' : 'Ekspedisi ' . $courierName),
                'description' => $desc,
                'timestamp' => $log->created_at?->timestamp ?? 0,
                'weight' => Order::statusStageWeight($log->status),
            ];
        }

        // 7. Paket Diterima (jika status Delivered tapi belum ada di DeliveryLog)
        $isDelivered = (int)$order->status === Order::STATUS_DELIVERED;
        if ($isDelivered && !$hasLogDelivered) {
            $delivTime = $order->delivery?->delivered_at ?? $order->updated_at ?? now();
            $events[] = [
                'stage' => 'delivered',
                'time' => $delivTime->format('d M Y H:i'),
                'status' => 'Paket Diterima',
                'raw_status' => 'delivered',
                'event' => 'order.delivered',
                'icon' => 'check_circle',
                'location' => 'Alamat Penerima',
                'description' => 'Paket telah berhasil diterima di alamat tujuan. Pesanan selesai.',
                'timestamp' => $delivTime->timestamp,
                'weight' => 11,
            ];
        }

        // Sort events descending: status checkpoint paling mutakhir di urutan teratas (index 0)
        usort($events, function ($a, $b) {
            $diff = ($b['timestamp'] ?? 0) <=> ($a['timestamp'] ?? 0);
            if ($diff !== 0) {
                return $diff;
            }
            return ($b['weight'] ?? 0) <=> ($a['weight'] ?? 0);
        });

        // Tentukan status terkini yang ditampilkan di header modal
        $latestEvent = $events[0] ?? null;
        $currentStatusLabel = $latestEvent['status'] ?? 'Menunggu Pengiriman';
        $currentStatusRaw = $latestEvent['raw_status'] ?? ($logs->last()?->status ?? $order->delivery_status);
        $currentStatusInfo = Order::deliveryStatusInfo($currentStatusRaw);

        $trackingUrl = $order->meta['biteship_tracking_url']
            ?? data_get($logs->last()?->payload, 'courier_link')
            ?? data_get($logs->last()?->payload, 'courier.link');

        return response()->json([
            'success' => true,
            'source' => 'order_tracking_flow',
            'data' => [
                'resi' => $resi,
                'tracking_number' => $resi,
                'courier' => $courierCode,
                'courier_name' => $courierName,
                'status' => $currentStatusLabel,
                'status_raw' => $currentStatusRaw,
                'status_badge_class' => $currentStatusInfo['badge_class'],
                'status_icon' => $latestEvent['icon'] ?? $currentStatusInfo['icon'],
                'link' => $trackingUrl,
                'last_updated' => $latestEvent['time'] ?? ($order->updated_at ? $order->updated_at->format('d M Y H:i') : '-'),
                'events' => $events,
            ],
        ], 200);
    }

    public function pushToJde(Request $request, string $id)
    {
        $order = Order::with(['items.variant', 'items.product', 'customer', 'store', 'storeChannel'])->findOrFail($id);

        if (empty($order->resi)) {
            return response()->json([
                'success' => false,
                'message' => 'Pesanan belum memiliki nomor resi. Tombol push ke JDE hanya tersedia setelah nomor resi diisi manual atau didapatkan otomatis.',
            ], 422);
        }

        if ($order->items->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Pesanan tidak memiliki item untuk di-push ke JDE.',
            ], 422);
        }

        $businessUnit = null;
        $addressNumber = null;

        // 1. Ambil dari kolom langsung di tabel orders jika sudah ada
        if (!empty($order->business_unit)) {
            $businessUnit = trim((string) $order->business_unit);
        }
        if (!empty($order->address_number)) {
            $addressNumber = (int) $order->address_number;
        }

        // 2. Ambil dari meta order jika belum ada
        if (!$businessUnit && !empty($order->meta['business_unit'])) {
            $businessUnit = trim((string) $order->meta['business_unit']);
        }
        if (!$addressNumber && !empty($order->meta['address_number'])) {
            $addressNumber = (int) $order->meta['address_number'];
        }

        // 3. Ambil dari relasi Channel
        $channel = $order->storeChannel;
        if (!$channel && !empty($order->store_channel_id)) {
            $channel = \App\Models\Store\StoreChannel::with('store.group')->find($order->store_channel_id);
        }
        if (!$channel && !empty($order->meta['store_channel_id'])) {
            $channel = \App\Models\Store\StoreChannel::with('store.group')->find($order->meta['store_channel_id']);
        }

        if ($channel) {
            $rawCode = trim((string) $channel->code);
            $cleanCode = preg_replace('/-[A-Za-z0-9]+$/', '', $rawCode);
            $businessUnit = $businessUnit ?: ($cleanCode ?: ($channel->store?->code ?? $channel->store?->group?->code));

            if (!$addressNumber && !empty($channel->address_number)) {
                $addressNumber = (int) $channel->address_number;
            }
            if (!$addressNumber && !empty($channel->store?->address_number)) {
                $addressNumber = (int) $channel->store->address_number;
            }
        }

        // 4. Ambil dari relasi Store
        $store = $order->store;
        if (!$store && !empty($order->store_id)) {
            $store = \App\Models\Store\Store::with('group')->find($order->store_id);
        }
        if (!$store && !empty($order->meta['store_id'])) {
            $store = \App\Models\Store\Store::with('group')->find($order->meta['store_id']);
        }

        if ($store) {
            $businessUnit = $businessUnit ?: ($store->code ?? $store->group?->code);
            if (!$addressNumber && !empty($store->address_number)) {
                $addressNumber = (int) $store->address_number;
            }
        }

        // 5. Default fallback dari konfigurasi jika store belum memiliki address_number
        $businessUnit = (string) ($businessUnit ?: config('services.jde.business_unit', env('JDE_BUSINESS_UNIT', '18011')));
        $addressNumber = (int) ($addressNumber ?: config('services.jde.address_number', env('JDE_ADDRESS_NUMBER', 100228)));

        // Persist ke database orders agar tercatat permanen
        if ($order->business_unit !== $businessUnit || (int)$order->address_number !== $addressNumber) {
            $order->business_unit = $businessUnit;
            $order->address_number = $addressNumber;
            if ($channel && !$order->store_channel_id) {
                $order->store_channel_id = $channel->id;
            }
            if ($store && !$order->store_id) {
                $order->store_id = $store->id;
            }
            $order->save();
        }

        $cleanOrderNumber = ltrim((string) $order->order_number, '#');
        $prefix = (string) config('services.jde.keterangan_prefix', env('JDE_KETERANGAN_PREFIX', 'WEB-IMG '));
        if (str_ends_with($prefix, '-') || str_ends_with($prefix, '_')) {
            $keterangan = $prefix . $cleanOrderNumber;
        } else {
            $keterangan = rtrim($prefix) . ' ' . $cleanOrderNumber;
        }

        $orderLines = [];
        foreach ($order->items as $item) {
            $secondItemNumber = $item->getJdeItemNumber();

            $orderLines[] = [
                '2nd_item_number' => (string) $secondItemNumber,
                'qty' => (int) $item->quantity,
            ];
        }

        $username = config('services.jde.username', env('JDE_USERNAME', 'UDO'));
        $password = config('services.jde.password', env('JDE_PASSWORD', 'udoroyal'));
        $environment = config('services.jde.environment', env('JDE_ENVIRONMENT', 'JPY920'));
        $role = config('services.jde.role', env('JDE_ROLE', '*ALL'));

        $payload = [
            'username' => (string) $username,
            'password' => (string) $password,
            'environment' => (string) $environment,
            'role' => (string) $role,
            'business_unit' => $businessUnit,
            'address_number' => $addressNumber,
            'keterangan' => $keterangan,
            'orderLines' => $orderLines,
        ];

        // Format payload langsung ke raw JSON string untuk pengiriman
        $jsonPayload = json_encode($payload, JSON_UNESCAPED_SLASHES);

        // Payload bertopeng untuk logging & meta (agar password tidak terekspos di log/db)
        $logPayload = $payload;
        if (!empty($logPayload['password'])) {
            $logPayload['password'] = '********';
        }

        $endpoint = config('services.jde.endpoint', env('JDE_ENDPOINT'));
        $timeout = (int) config('services.jde.timeout', 30);

        if (empty($endpoint)) {
            $this->logJde('warning', 'Push Order Aborted', [
                'order_number' => $order->order_number,
                'order_id' => $order->id,
                'reason' => 'JDE_ENDPOINT not configured in .env',
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Konfigurasi JDE_ENDPOINT belum diset pada environment (.env).',
            ], 422);
        }

        $this->logJde('info', 'Push Order Initiated', [
            'order_number' => $order->order_number,
            'order_id' => $order->id,
            'admin' => auth()->user()->name ?? 'admin',
            'endpoint' => $endpoint,
            'payload' => $logPayload,
        ]);

        try {
            $httpClient = Http::withoutVerifying()
                ->timeout($timeout)
                ->withHeaders([
                    'Accept' => '*/*',
                    'Content-Type' => 'application/json',
                    'User-Agent' => 'PostmanRuntime/7.39.0',
                    'Cache-Control' => 'no-cache',
                ]);

            // Mengirim langsung dalam format JSON string
            $response = $httpClient->withBody($jsonPayload, 'application/json')->post($endpoint);

            $responseData = $response->json();
            $isOk = $response->successful();

            if ($isOk && is_array($responseData) && isset($responseData['hasErrors']) && $responseData['hasErrors'] === true) {
                $isOk = false;
            }

            $meta = $order->meta ?? [];
            $meta['jde_push_payload'] = $logPayload;
            $meta['jde_push_response'] = $responseData ?? $response->body();
            $meta['jde_pushed_at'] = now()->toDateTimeString();
            $meta['jde_pushed_by'] = auth()->user()->name ?? 'admin';

            if ($isOk) {
                $order->jde_push_status = Order::JDE_STATUS_SUCCESS;
                $order->jde_push_date = now()->format('Y-m-d');
                unset($meta['jde_push_error']);

                $this->logJde('info', 'Push Order Success', [
                    'order_number' => $order->order_number,
                    'order_id' => $order->id,
                    'status_code' => $response->status(),
                    'response' => $responseData ?? $response->body(),
                ]);
            } else {
                $order->jde_push_status = Order::JDE_STATUS_FAILED;
                $meta['jde_push_error'] = is_array($responseData) ? json_encode($responseData) : ($response->body() ?: 'HTTP status ' . $response->status());

                $this->logJde('warning', 'Push Order Error Response', [
                    'order_number' => $order->order_number,
                    'order_id' => $order->id,
                    'status_code' => $response->status(),
                    'error' => $meta['jde_push_error'],
                    'payload' => $logPayload,
                ]);
            }

            $order->meta = $meta;
            $order->save();

            if ($isOk) {
                return response()->json([
                    'success' => true,
                    'message' => 'Pesanan #' . $order->order_number . ' berhasil di-push ke JDE.',
                    'jde_push_status' => Order::JDE_STATUS_SUCCESS,
                    'jde_push_status_label' => $order->jdePushStatusLabel(),
                    'jde_push_date' => $order->jde_push_date?->format('d M Y') ?? now()->format('d M Y'),
                    'response' => $responseData,
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'JDE mengembalikan respon error: ' . ($meta['jde_push_error'] ?? 'Terjadi kesalahan pada Orchestrator JDE'),
                'jde_push_status' => Order::JDE_STATUS_FAILED,
                'jde_push_status_label' => $order->jdePushStatusLabel(),
                'response' => $responseData,
            ], 400);

        } catch (\Throwable $e) {
            $meta = $order->meta ?? [];
            $meta['jde_push_payload'] = $payload;
            $meta['jde_push_error'] = $e->getMessage();
            $meta['jde_push_failed_at'] = now()->toDateTimeString();

            $order->jde_push_status = Order::JDE_STATUS_FAILED;
            $order->meta = $meta;
            $order->save();

            $this->logJde('error', 'Push Order Exception', [
                'order_number' => $order->order_number,
                'order_id' => $order->id,
                'exception' => $e->getMessage(),
                'payload' => $payload,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal menghubungi service JDE: ' . $e->getMessage(),
                'jde_push_status' => Order::JDE_STATUS_FAILED,
                'jde_push_status_label' => $order->jdePushStatusLabel(),
            ], 500);
        }
    }

    /**
     * Helper untuk mencatat log JDE harian dalam format banner terstruktur dan rapi.
     */
    protected function logJde(string $level, string $title, array $details): void
    {
        $separator = str_repeat('=', 80);
        $subSep    = str_repeat('-', 80);
        $now       = now()->format('Y-m-d H:i:s');

        $lines = [];
        $lines[] = $separator;
        $lines[] = sprintf("[%s] JDE INTEGRATION: %s", $now, strtoupper($title));
        $lines[] = $subSep;

        foreach ($details as $key => $val) {
            $label = ucwords(str_replace('_', ' ', (string) $key));
            if (is_array($val) || is_object($val)) {
                $pretty = json_encode($val, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
                $lines[] = sprintf("%-18s:\n%s", $label, $pretty);
            } elseif (is_string($val) && (str_starts_with(trim($val), '{') || str_starts_with(trim($val), '['))) {
                $decoded = json_decode($val, true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    $pretty = json_encode($decoded, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
                    $lines[] = sprintf("%-18s:\n%s", $label, $pretty);
                } else {
                    $lines[] = sprintf("%-18s: %s", $label, $val);
                }
            } else {
                if (is_bool($val)) {
                    $val = $val ? 'true' : 'false';
                } elseif ($val === null || $val === '') {
                    $val = '-';
                }
                $lines[] = sprintf("%-18s: %s", $label, (string) $val);
            }
        }

        $lines[] = $separator;
        $banner = implode("\n", $lines);

        try {
            Log::channel('jde')->$level($banner);
        } catch (\Throwable) {
            Log::$level($banner);
        }
    }
}
