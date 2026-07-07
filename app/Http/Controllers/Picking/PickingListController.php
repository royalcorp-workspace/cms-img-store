<?php

namespace App\Http\Controllers\Picking;

use App\Http\Controllers\Controller;
use App\Models\Order\Order;
use App\Models\Picking\PickingList;
use App\Models\Warehouse\Warehouse;
use Illuminate\Http\Request;

class PickingListController extends Controller
{
    public function index(Request $request)
    {
        $query = Order::with(['customer', 'items.product'])
            ->where('status', Order::STATUS_CONFIRMED);

        if ($search = $request->query('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('id', 'like', "%{$search}%")
                  ->orWhere('order_number', 'like', "%{$search}%");
            });
        }

        $orders = $query->latest()->paginate(10)->appends($request->query());

        return view('pages.picking-list.index', compact('orders'));
    }

    public function show(string $id)
    {
        $picklist = PickingList::with(['order.customer', 'items.product', 'items.variant', 'items.location', 'warehouse'])->findOrFail($id);
        return view('pages.picking-list.show', compact('picklist'));
    }

    public function create(?string $order_id)
    {
        $orders = Order::where('status', Order::STATUS_CONFIRMED)->get();
        $warehouses = Warehouse::where('status', true)->get();
        $warehouseLocations = \App\Models\Warehouse\WarehouseLocation::all();
        $order = $order_id ? Order::with(['items.product', 'items.variant'])->findOrFail($order_id) : null;
        return view('pages.picking-list.create', compact('order', 'orders', 'warehouses', 'warehouseLocations'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'order_id' => 'required|exists:orders,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'items' => 'required|array',
            'items.*.order_item_id' => 'required|exists:order_items,id',
            'items.*.warehouse_location_id' => 'nullable|exists:warehouse_locations,id',
        ]);

        $pickingList = PickingList::create([
            'order_id' => $request->order_id,
            'warehouse_id' => $request->warehouse_id,
            'status' => 'pending',
        ]);

        foreach ($request->items as $item) {
            \App\Models\Picking\PickingListItem::create([
                'picking_list_id' => $pickingList->id,
                'order_item_id' => $item['order_item_id'],
                'product_id' => \App\Models\Order\OrderItem::find($item['order_item_id'])->product_id,
                'product_variant_id' => $item['variant_id'] ?? null,
                'warehouse_location_id' => $item['warehouse_location_id'] ?? null,
                'quantity_ordered' => \App\Models\Order\OrderItem::find($item['order_item_id'])->quantity,
            ]);
        }

        return redirect()->route('picking-list.show', $pickingList->id)->with('success', 'Picking list created');
    }

    public function updateItem(Request $request, string $id)
    {
        $request->validate([
            'quantity_picked' => 'required|integer|min:0',
            'warehouse_location_id' => 'nullable|exists:warehouse_locations,id',
        ]);

        $item = \App\Models\Picking\PickingListItem::findOrFail($request->item_id);
        $item->update([
            'quantity_picked' => $request->quantity_picked,
            'warehouse_location_id' => $request->warehouse_location_id,
            'status' => $request->quantity_picked >= $item->quantity_ordered ? 'picked' : 'partial',
        ]);

        return back()->with('success', 'Item updated');
    }
}