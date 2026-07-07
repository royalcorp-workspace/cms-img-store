<?php

namespace App\Http\Controllers;

use App\Models\Store\Store;
use App\Models\Store\StoreChannel;
use App\Models\Store\StoreChannelGroup;
use Illuminate\Http\Request;

class StoreChannelController extends Controller
{
    public function index(Request $request)
    {
        $query = StoreChannel::query()->with(['store', 'channelGroup']);

        if ($search = $request->query('search')) {
            $query->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%");
        }

        if ($request->filled('store_id')) {
            $query->where('store_id', $request->query('store_id'));
        }

        if ($request->filled('store_channel_group_id')) {
            $query->where('store_channel_group_id', $request->query('store_channel_group_id'));
        }

        $channels = $query->orderBy('sort_order')->orderBy('name')->paginate(15)->appends($request->query());

        $stores = Store::where('status', true)->where('deleted', false)->orderBy('name')->get(['id', 'name', 'code']);
        $channelGroups = StoreChannelGroup::where('status', true)->where('deleted', false)->orderBy('name')->get(['id', 'name', 'code']);

        return view('pages.stores.store-channel.index', compact('channels', 'stores', 'channelGroups'));
    }

    public function create()
    {
        $stores = Store::where('status', true)->where('deleted', false)->orderBy('name')->get(['id', 'name', 'code']);
        $channelGroups = StoreChannelGroup::where('status', true)->where('deleted', false)->orderBy('name')->get(['id', 'name', 'code']);

        return view('pages.stores.store-channel.create', compact('stores', 'channelGroups'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'store_id' => 'required|string|exists:store,id',
            'store_channel_group_id' => 'required|string|exists:store_channel_group,id',
            'code' => 'required|string|max:100',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'sort_order' => 'nullable|integer|min:0',
            'status' => 'boolean',
        ]);

        $validated['creator'] = auth()->id();
        $validated['editor'] = auth()->id();
        $validated['status'] = $request->boolean('status', true);

        StoreChannel::create($validated);

        return redirect()->route('store-channels.index')->with('success', 'Store channel created successfully');
    }

    public function edit(string $id)
    {
        $channel = StoreChannel::findOrFail($id);
        $stores = Store::where('status', true)->where('deleted', false)->orderBy('name')->get(['id', 'name', 'code']);
        $channelGroups = StoreChannelGroup::where('status', true)->where('deleted', false)->orderBy('name')->get(['id', 'name', 'code']);

        return view('pages.stores.store-channel.edit', compact('channel', 'stores', 'channelGroups'));
    }

    public function update(Request $request, string $id)
    {
        $channel = StoreChannel::findOrFail($id);

        $validated = $request->validate([
            'store_id' => 'required|string|exists:store,id',
            'store_channel_group_id' => 'required|string|exists:store_channel_group,id',
            'code' => 'required|string|max:100',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'sort_order' => 'nullable|integer|min:0',
            'status' => 'boolean',
        ]);

        $validated['editor'] = auth()->id();
        $validated['status'] = $request->boolean('status', true);

        $channel->update($validated);

        return redirect()->route('store-channels.index')->with('success', 'Store channel updated successfully');
    }

    public function destroy(string $id)
    {
        $channel = StoreChannel::findOrFail($id);
        $channel->delete();

        return redirect()->route('store-channels.index')->with('success', 'Store channel deleted successfully');
    }
}
