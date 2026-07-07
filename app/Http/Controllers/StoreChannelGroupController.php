<?php

namespace App\Http\Controllers;

use App\Models\Store\StoreChannelGroup;
use Illuminate\Http\Request;

class StoreChannelGroupController extends Controller
{
    public function index(Request $request)
    {
        $query = StoreChannelGroup::query();

        if ($search = $request->query('search')) {
            $query->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%");
        }

        $channelGroups = $query->orderBy('sort_order')->orderBy('name')->paginate(15)->appends($request->query());
        return view('pages.stores.store-channel-group.index', compact('channelGroups'));
    }

    public function create()
    {
        return view('pages.stores.store-channel-group.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:100|unique:store_channel_group,code',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'sort_order' => 'nullable|integer|min:0',
            'status' => 'boolean',
        ]);

        $validated['creator'] = auth()->id();
        $validated['editor'] = auth()->id();
        $validated['status'] = $request->boolean('status', true);

        StoreChannelGroup::create($validated);

        return redirect()->route('store-channel-groups.index')->with('success', 'Store channel group created successfully');
    }

    public function edit(string $id)
    {
        $channelGroup = StoreChannelGroup::findOrFail($id);
        return view('pages.stores.store-channel-group.edit', compact('channelGroup'));
    }

    public function update(Request $request, string $id)
    {
        $channelGroup = StoreChannelGroup::findOrFail($id);

        $validated = $request->validate([
            'code' => 'required|string|max:100|unique:store_channel_group,code,' . $id,
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'sort_order' => 'nullable|integer|min:0',
            'status' => 'boolean',
        ]);

        $validated['editor'] = auth()->id();
        $validated['status'] = $request->boolean('status', true);

        $channelGroup->update($validated);

        return redirect()->route('store-channel-groups.index')->with('success', 'Store channel group updated successfully');
    }

    public function destroy(string $id)
    {
        $channelGroup = StoreChannelGroup::findOrFail($id);
        $channelGroup->delete();

        return redirect()->route('store-channel-groups.index')->with('success', 'Store channel group deleted successfully');
    }
}
