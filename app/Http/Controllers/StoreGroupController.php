<?php

namespace App\Http\Controllers;

use App\Models\Store\StoreGroup;
use App\Models\Store\Store;
use Illuminate\Http\Request;

class StoreGroupController extends Controller
{
    public function index(Request $request)
    {
        $query = StoreGroup::query();

        if ($search = $request->query('search')) {
            $query->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%");
        }

        $storeGroups = $query->orderBy('sort_order')->orderBy('name')->paginate(15)->appends($request->query());
        return view('pages.stores.store-group.index', compact('storeGroups'));
    }

    public function create()
    {
        return view('pages.stores.store-group.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:100|unique:store_group,code',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'sort_order' => 'nullable|integer|min:0',
            'status' => 'boolean',
        ]);

        $validated['creator'] = auth()->id();
        $validated['editor'] = auth()->id();
        $validated['status'] = $request->boolean('status', true);

        StoreGroup::create($validated);

        return redirect()->route('store-groups.index')->with('success', 'Store group created successfully');
    }

    public function edit(string $id)
    {
        $storeGroup = StoreGroup::findOrFail($id);
        return view('pages.stores.store-group.edit', compact('storeGroup'));
    }

    public function update(Request $request, string $id)
    {
        $storeGroup = StoreGroup::findOrFail($id);

        $validated = $request->validate([
            'code' => 'required|string|max:100|unique:store_group,code,' . $id,
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'sort_order' => 'nullable|integer|min:0',
            'status' => 'boolean',
        ]);

        $validated['editor'] = auth()->id();
        $validated['status'] = $request->boolean('status', true);

        $storeGroup->update($validated);

        return redirect()->route('store-groups.index')->with('success', 'Store group updated successfully');
    }

    public function destroy(string $id)
    {
        $storeGroup = StoreGroup::findOrFail($id);
        $storeGroup->delete();

        return redirect()->route('store-groups.index')->with('success', 'Store group deleted successfully');
    }
}
