<?php

namespace App\Http\Controllers;

use App\Models\Store\Store;
use App\Models\Store\StoreChannel;
use App\Models\Store\StoreChannelStock;
use App\Models\Store\StoreChannelGroup;
use Illuminate\Http\Request;

class StoreChannelStockController extends Controller
{
    public function index(Request $request)
    {
        $query = StoreChannelStock::query()->with(['product', 'variant', 'store', 'channel']);

        if ($request->filled('store_id')) {
            $query->where('store_id', $request->store_id);
        }

        if ($request->filled('store_channel_id')) {
            $query->where('store_channel_id', $request->store_channel_id);
        }

        if ($search = $request->query('search')) {
            $query->whereHas('product', function ($q) use ($search) {
                $q->where('name', 'ilike', "%{$search}%");
            })->orWhereHas('variant', function ($q) use ($search) {
                $q->where('variant_name', 'ilike', "%{$search}%");
            });
        }

        $stocks = $query->orderBy('created_at', 'desc')->paginate(15)->appends($request->query());

        $stores = Store::where('status', true)->where('deleted', false)->orderBy('name')->get(['id', 'name', 'code']);
        $channels = StoreChannel::where('status', true)->where('deleted', false)->orderBy('name')->get(['id', 'name', 'code']);

        return view('pages.stores.stock.index', compact('stocks', 'stores', 'channels'));
    }

    public function create()
    {
        $stores = Store::where('status', true)->where('deleted', false)->orderBy('name')->get(['id', 'name', 'code']);
        $channels = StoreChannel::where('status', true)->where('deleted', false)->orderBy('name')->get(['id', 'name', 'code', 'store_id']);

        return view('pages.stores.stock.create', compact('stores', 'channels'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'nullable|string|exists:products,id',
            'product_variant_id' => 'nullable|string|exists:product_variants,id',
            'store_id' => 'nullable|string|exists:store,id',
            'store_channel_id' => 'nullable|string|exists:store_channel,id',
            'incoming' => 'nullable|integer|min:0',
            'booked' => 'nullable|integer|min:0',
            'on_order' => 'nullable|integer|min:0',
            'outgoing' => 'nullable|integer|min:0',
            'quantity' => 'nullable|integer|min:0',
        ]);

        $validated['incoming'] = $validated['incoming'] ?? 0;
        $validated['booked'] = $validated['booked'] ?? 0;
        $validated['on_order'] = $validated['on_order'] ?? 0;
        $validated['outgoing'] = $validated['outgoing'] ?? 0;
        $validated['quantity'] = $validated['quantity'] ?? 0;
        $validated['creator'] = auth()->user()->name ?? 'admin';
        $validated['editor'] = auth()->user()->name ?? 'admin';

        StoreChannelStock::create($validated);

        return redirect()->route('store-channel-stocks.index')->with('success', 'Store channel stock created successfully');
    }

    public function edit(string $id)
    {
        $stock = StoreChannelStock::findOrFail($id);
        $stores = Store::where('status', true)->where('deleted', false)->orderBy('name')->get(['id', 'name', 'code']);
        $channels = StoreChannel::where('status', true)->where('deleted', false)->orderBy('name')->get(['id', 'name', 'code', 'store_id']);

        return view('pages.stores.stock.edit', compact('stock', 'stores', 'channels'));
    }

    public function update(Request $request, string $id)
    {
        $stock = StoreChannelStock::findOrFail($id);

        $validated = $request->validate([
            'product_id' => 'nullable|string|exists:products,id',
            'product_variant_id' => 'nullable|string|exists:product_variants,id',
            'store_id' => 'nullable|string|exists:store,id',
            'store_channel_id' => 'nullable|string|exists:store_channel,id',
            'incoming' => 'nullable|integer|min:0',
            'booked' => 'nullable|integer|min:0',
            'on_order' => 'nullable|integer|min:0',
            'outgoing' => 'nullable|integer|min:0',
            'quantity' => 'nullable|integer|min:0',
        ]);

        $validated['editor'] = auth()->user()->name ?? 'admin';
        $stock->update($validated);

        return redirect()->route('store-channel-stocks.index')->with('success', 'Store channel stock updated successfully');
    }

    public function destroy(string $id)
    {
        $stock = StoreChannelStock::findOrFail($id);
        $stock->delete();

        return redirect()->route('store-channel-stocks.index')->with('success', 'Store channel stock deleted successfully');
    }
}
