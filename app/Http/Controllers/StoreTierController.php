<?php

namespace App\Http\Controllers;

use App\Models\Store\StoreTier;
use Illuminate\Http\Request;

class StoreTierController extends Controller
{
    public function index(Request $request)
    {
        $query = StoreTier::query();

        if ($search = $request->query('search')) {
            $query->where('name', 'ilike', "%{$search}%")
                  ->orWhere('code', 'ilike', "%{$search}%");
        }

        $tiers = $query->orderBy('level')->orderBy('sort_order')->paginate(15)->appends($request->query());
        return view('pages.stores.store-tier.index', compact('tiers'));
    }

    public function create()
    {
        return view('pages.stores.store-tier.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:100|unique:store_tier,code',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'level' => 'required|integer|min:1',
            'credit_limit' => 'nullable|numeric|min:0',
            'sort_order' => 'nullable|integer|min:0',
            'status' => 'boolean',
        ]);

        $validated['creator'] = auth()->user()->name ?? 'admin';
        $validated['editor'] = auth()->user()->name ?? 'admin';
        $validated['status'] = $request->boolean('status', true);
        $validated['credit_limit'] = $validated['credit_limit'] ?? 0;

        StoreTier::create($validated);

        return redirect()->route('store-tiers.index')->with('success', 'Store tier created successfully');
    }

    public function edit(string $id)
    {
        $tier = StoreTier::findOrFail($id);
        return view('pages.stores.store-tier.edit', compact('tier'));
    }

    public function update(Request $request, string $id)
    {
        $tier = StoreTier::findOrFail($id);

        $validated = $request->validate([
            'code' => 'required|string|max:100|unique:store_tier,code,' . $id,
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'level' => 'required|integer|min:1',
            'credit_limit' => 'nullable|numeric|min:0',
            'sort_order' => 'nullable|integer|min:0',
            'status' => 'boolean',
        ]);

        $validated['editor'] = auth()->user()->name ?? 'admin';
        $validated['status'] = $request->boolean('status', true);
        $validated['credit_limit'] = $validated['credit_limit'] ?? 0;

        $tier->update($validated);

        return redirect()->route('store-tiers.index')->with('success', 'Store tier updated successfully');
    }

    public function destroy(string $id)
    {
        $tier = StoreTier::findOrFail($id);
        $tier->delete();

        return redirect()->route('store-tiers.index')->with('success', 'Store tier deleted successfully');
    }
}
