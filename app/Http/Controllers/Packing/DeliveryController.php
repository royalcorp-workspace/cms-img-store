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

        if ($request->has('status')) {
            $query->where('status', $request->query('status'));
        }

        $deliveries = $query->latest()->paginate(10)->appends($request->query());

        return view('pages.delivery.index', compact('deliveries'));
    }

    public function show(string $id)
    {
        $delivery = Delivery::with(['order.customer', 'order.items.product', 'courier', 'packingOut'])->findOrFail($id);
        return view('pages.delivery.show', compact('delivery'));
    }

    public function create(string $packing_out_id)
    {
        $couriers = Courier::all();
        $packingOut = \App\Models\Packing\PackingOut::with('packingSlip.order')->findOrFail($packing_out_id);
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
        ]);

        $delivery = Delivery::create([
            'packing_out_id' => $request->packing_out_id,
            'order_id' => \App\Models\Packing\PackingOut::find($request->packing_out_id)->packingSlip->order_id,
            'courier_id' => $request->courier_id,
            'tracking_number' => $request->tracking_number,
            'driver_name' => $request->driver_name,
            'driver_phone' => $request->driver_phone,
            'status' => 'in_transit',
            'shipped_at' => now(),
        ]);

        \App\Models\Packing\PackingOut::find($request->packing_out_id)->update(['status' => 'out']);

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