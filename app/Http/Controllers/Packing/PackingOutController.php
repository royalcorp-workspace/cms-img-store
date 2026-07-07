<?php

namespace App\Http\Controllers\Packing;

use App\Http\Controllers\Controller;
use App\Models\Packing\PackingOut;
use Illuminate\Http\Request;

class PackingOutController extends Controller
{
    public function index(Request $request)
    {
        $query = PackingOut::with(['packingSlip.order.customer', 'warehouse', 'packer']);

        if ($request->has('status')) {
            $query->where('status', $request->query('status'));
        }

        $packingOuts = $query->latest()->paginate(10)->appends($request->query());

        return view('pages.packing-out.index', compact('packingOuts'));
    }

    public function show(string $id)
    {
        $packingOut = PackingOut::with(['packingSlip.order.customer', 'packingSlip.items', 'warehouse', 'handover'])->findOrFail($id);
        return view('pages.packing-out.show', compact('packingOut'));
    }

    public function create(string $packing_slip_id)
    {
        $packingSlip = \App\Models\Packing\PackingSlip::with(['order'])->findOrFail($packing_slip_id);
        return view('pages.packing-out.create', compact('packingSlip'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'packing_slip_id' => 'required|exists:packing_slips,id',
        ]);

        $packingSlip = \App\Models\Packing\PackingSlip::with('order')->findOrFail($request->packing_slip_id);

        $packingOut = PackingOut::create([
            'packing_slip_id' => $request->packing_slip_id,
            'warehouse_id' => $request->warehouse_id,
            'status' => 'ready',
        ]);

        $packingSlip->update(['status' => 'packed']);

        return redirect()->route('packing-out.show', $packingOut->id)->with('success', 'Packing out created');
    }

    public function confirmOut(string $id)
    {
        $packingOut = PackingOut::findOrFail($id);
        $packingOut->update(['status' => 'out']);
        $packingOut->packingSlip->order->update(['status' => \App\Models\Order\Order::STATUS_SHIPPED]);

        return back()->with('success', 'Order marked as shipped');
    }
}