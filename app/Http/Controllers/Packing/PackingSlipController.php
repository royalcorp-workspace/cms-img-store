<?php

namespace App\Http\Controllers\Packing;

use App\Http\Controllers\Controller;
use App\Models\Order\Order;
use App\Models\Packing\PackingSlip;
use App\Models\Packing\PackingOut;
use App\Models\Packing\Delivery;
use App\Models\Shipping\Courier;
use Illuminate\Http\Request;

class PackingSlipController extends Controller
{
    public function index(Request $request)
    {
        $query = PackingSlip::with(['order.customer', 'packer']);

        if ($request->has('status')) {
            $query->where('status', $request->query('status'));
        }

        $packingSlips = $query->latest()->paginate(10)->appends($request->query());

        return view('pages.packing-slip.index', compact('packingSlips'));
    }

    public function show(string $id)
    {
        $packingSlip = PackingSlip::with(['order.customer', 'order.items.product', 'packer', 'items'])->findOrFail($id);
        return view('pages.packing-slip.show', compact('packingSlip'));
    }

    public function create(string $order_id)
    {
        $order = Order::with(['items.product', 'items.variant'])->findOrFail($order_id);
        return view('pages.packing-slip.create', compact('order'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'order_id' => 'required|exists:orders,id',
            'items' => 'required|array',
        ]);

        $packingSlip = PackingSlip::create([
            'order_id' => $request->order_id,
            'status' => 'packing',
            'box_count' => $request->box_count ?? 1,
            'weight' => $request->weight ?? 0,
        ]);

        foreach ($request->items as $item) {
            \App\Models\Packing\PackingSlipItem::create([
                'packing_slip_id' => $packingSlip->id,
                'order_item_id' => $item['order_item_id'],
                'product_id' => \App\Models\Order\OrderItem::find($item['order_item_id'])->product_id,
                'quantity_ordered' => \App\Models\Order\OrderItem::find($item['order_item_id'])->quantity,
                'quantity_packed' => $item['quantity_packed'],
                'box_number' => $item['box_number'] ?? 1,
            ]);
        }

        return redirect()->route('packing-slip.show', $packingSlip->id)->with('success', 'Packing slip created');
    }
}