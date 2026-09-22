<?php

namespace App\Http\Controllers\Packing;

use App\Http\Controllers\Controller;
use App\Models\Packing\Delivery;
use App\Models\Shipping\Courier;
use Illuminate\Http\Request;

class DeliveryController extends Controller
{
    public function index(Request $request)
    {
        $query = Delivery::with(['order.customer', 'courier', 'packingOut']);

        if ($request->filled('status')) {
            $query->where('status', $request->query('status'));
        }

        if ($request->filled('search')) {
            $search = $request->query('search');
            $query->where(function($q) use ($search) {
                $q->where('tracking_number', 'like', "%{$search}%")
                  ->orWhere('driver_name', 'like', "%{$search}%")
                  ->orWhere('id', 'like', "%{$search}%")
                  ->orWhereHas('order', function($oq) use ($search) {
                      $oq->where('order_number', 'like', "%{$search}%");
                  });
            });
        }

        $deliveries = $query->latest()->paginate(10)->withQueryString();

        return view('pages.delivery.index', compact('deliveries'));
    }

    public function show(string $id)
    {
        $delivery = Delivery::with(['order.customer', 'order.items.product', 'order.courier', 'courier', 'packingOut'])->findOrFail($id);
        
        $deliveryLogs = \App\Models\Packing\DeliveryLog::where('delivery_id', $delivery->id)
            ->orWhere('order_id', $delivery->order_id)
            ->when(!empty($delivery->tracking_number), function ($q) use ($delivery) {
                $q->orWhere('waybill_id', $delivery->tracking_number);
            })
            ->orderBy('created_at', 'desc')
            ->get();

        return view('pages.delivery.show', compact('delivery', 'deliveryLogs'));
    }

    public function create(string $packing_out_id)
    {
        $couriers = Courier::all();
        $packingOut = \App\Models\Packing\PackingOut::with('packingSlip.order.courier')->findOrFail($packing_out_id);
        return view('pages.delivery.create', compact('couriers', 'packingOut'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'packing_out_id' => 'required|exists:packing_outs,id',
            'courier_id' => 'nullable|exists:couriers,id',
            'tracking_number' => 'nullable|string',
            'driver_name' => 'nullable|string',
            'driver_phone' => 'nullable|string',
            'estimated_delivery_at' => 'nullable|string',
            'estimated_delivery_duration' => 'nullable|string|max:100',
            'eta_notes' => 'nullable|string|max:500',
        ]);

        $packingOut = \App\Models\Packing\PackingOut::with('packingSlip.order')->findOrFail($request->packing_out_id);
        $order = $packingOut->packingSlip?->order;
        $finalCourierId = $order?->courier_id ?: $request->courier_id;

        $courier = Courier::find($finalCourierId);
        $isToko = ($courier?->courier_type === 'toko' || $courier?->code === 'kurir_toko');
        $estimatedAt = !empty($request->estimated_delivery_at) ? \Carbon\Carbon::parse($request->estimated_delivery_at) : null;
        $estimatedDuration = !empty($request->estimated_delivery_duration) ? trim((string)$request->estimated_delivery_duration) : null;

        if ($estimatedDuration && !$estimatedAt) {
            $calc = \App\Services\EtaService::calculateEta($estimatedDuration);
            $estimatedAt = $calc['estimated_at'];
        }

        $delivery = Delivery::create([
            'packing_out_id' => $request->packing_out_id,
            'order_id' => $order?->id ?? $packingOut->packingSlip->order_id,
            'courier_id' => $finalCourierId,
            'tracking_number' => $request->tracking_number,
            'driver_name' => $request->driver_name,
            'driver_phone' => $request->driver_phone,
            'status' => 'in_transit',
            'shipped_at' => now(),
            'estimated_delivery_at' => $estimatedAt,
            'estimated_delivery_duration' => $estimatedDuration,
            'eta_source' => $isToko ? 'store' : 'manual',
            'eta_notes' => $request->eta_notes,
        ]);

        \App\Models\Packing\PackingOut::find($request->packing_out_id)->update(['status' => 'out']);

        // Record delivery log: Selesai Dikemas (ketika buat data delivery)
        try {
            $courier = Courier::find($request->courier_id);
            \App\Models\Packing\DeliveryLog::create([
                'order_id' => $delivery->order_id,
                'delivery_id' => $delivery->id,
                'waybill_id' => $request->tracking_number,
                'courier_code' => $courier?->code,
                'event' => 'delivery.created',
                'status' => 'dikemas',
                'location' => 'Gudang Pengirim',
                'note' => 'Pesanan selesai dikemas dan siap dikirim (Data pengiriman dibuat)',
                'payload' => [
                    'delivery_id' => $delivery->id,
                    'tracking_number' => $request->tracking_number,
                    'courier_id' => $request->courier_id,
                    'driver_name' => $request->driver_name,
                ],
            ]);
        } catch (\Throwable $e) {}

        return redirect()->route('delivery.show', $delivery->id)->with('success', 'Delivery created');
    }

    public function updateStatus(string $id)
    {
        $delivery = Delivery::with('order')->findOrFail($id);
        $delivery->update([
            'status' => request('status'),
            'delivered_at' => request('status') === 'delivered' ? now() : $delivery->delivered_at,
        ]);

        if (request('status') === 'delivered') {
            $delivery->order->update(['status' => \App\Models\Order\Order::STATUS_DELIVERED]);
        }

        return back()->with('success', 'Delivery status updated');
    }
}