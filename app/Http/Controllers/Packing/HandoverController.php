<?php

namespace App\Http\Controllers\Packing;

use App\Http\Controllers\Controller;
use App\Models\Packing\Handover;
use App\Models\Packing\PackingOut;
use App\Models\Shipping\Courier;
use Illuminate\Http\Request;

class HandoverController extends Controller
{
    public function index(Request $request)
    {
        $query = Handover::with(['order.customer', 'warehouse', 'courier']);

        if ($request->has('status')) {
            $query->where('status', $request->query('status'));
        }

        $handovers = $query->latest()->paginate(10)->appends($request->query());

        return view('pages.handover.index', compact('handovers'));
    }

    public function show(string $id)
    {
        $handover = Handover::with(['order.customer', 'order.items.product', 'items.product', 'items.variant', 'items.location', 'warehouse', 'courier', 'packingOut'])->findOrFail($id);
        return view('pages.handover.show', compact('handover'));
    }

    public function create(string $packing_out_id)
    {
        $packingOut = PackingOut::with(['packingSlip.order', 'packingSlip.items.product', 'warehouse', 'packer'])->findOrFail($packing_out_id);
        $couriers = Courier::all();
        return view('pages.handover.create', compact('packingOut', 'couriers'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'packing_out_id' => 'required|exists:packing_outs,id',
            'courier_id' => 'nullable|exists:couriers,id',
            'driver_name' => 'nullable|string',
            'driver_phone' => 'nullable|string',
            'tracking_number' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        $packingOut = PackingOut::with(['packingSlip.order', 'packingSlip.items.product'])->findOrFail($request->packing_out_id);

        $handover = Handover::create([
            'packing_out_id' => $request->packing_out_id,
            'order_id' => $packingOut->packingSlip->order_id,
            'warehouse_id' => $packingOut->warehouse_id,
            'courier_id' => $request->courier_id,
            'driver_name' => $request->driver_name,
            'driver_phone' => $request->driver_phone,
            'tracking_number' => $request->tracking_number,
            'status' => 'pending',
            'notes' => $request->notes,
            'handover_at' => now(),
        ]);

        foreach ($packingOut->packingSlip->items as $item) {
            \App\Models\Packing\HandoverItem::create([
                'handover_id' => $handover->id,
                'order_item_id' => $item->order_item_id,
                'product_id' => $item->product_id,
                'product_variant_id' => $item->product_variant_id ?? null,
                'quantity_ordered' => $item->quantity_ordered,
                'quantity_handed_over' => $item->quantity_packed,
                'status' => 'handed_over',
                'notes' => $item->notes ?? null,
            ]);
        }

        return redirect()->route('handover.show', $handover->id)->with('success', 'Handover created');
    }
}
