<?php

namespace App\Http\Controllers;

use App\Models\Store\StoreGroup;
use App\Models\Store\Store;
use App\Models\Store\StoreTier;
use Illuminate\Http\Request;

class StoreController extends Controller
{
    public function index(Request $request)
    {
        $query = Store::query()->with(['group', 'tier', 'owner']);

        if ($search = $request->query('search')) {
            $query->where('name', 'ilike', "%{$search}%")
                  ->orWhere('code', 'ilike', "%{$search}%");
        }

        if ($request->filled('store_group_id')) {
            $query->where('store_group_id', $request->query('store_group_id'));
        }

        if ($request->filled('tier_id')) {
            $query->where('tier_id', $request->query('tier_id'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->boolean('status'));
        }

        $stores = $query->orderBy('sort_order')->orderBy('name')->paginate(15)->appends($request->query());

        $storeGroups = StoreGroup::where('status', true)->where('deleted', false)->orderBy('name')->get(['id', 'name', 'code']);
        $tiers = StoreTier::where('status', true)->where('deleted', false)->orderBy('level')->get(['id', 'name', 'code', 'level']);

        return view('pages.stores.store.index', compact('stores', 'storeGroups', 'tiers'));
    }

    public function create()
    {
        $storeGroups = StoreGroup::where('status', true)->where('deleted', false)->orderBy('name')->get(['id', 'name', 'code']);
        $tiers = StoreTier::where('status', true)->where('deleted', false)->orderBy('level')->get(['id', 'name', 'code', 'level']);

        return view('pages.stores.store.create', compact('storeGroups', 'tiers'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'store_group_id' => 'required|string|exists:store_group,id',
            'tier_id' => 'nullable|string|exists:store_tier,id',
            'code' => 'required|string|max:100',
            'name' => 'required|string|max:255',
            'owner_user_id' => 'nullable|string|exists:user_admin,id',
            'credit_limit' => 'nullable|numeric|min:0',
            'outstanding_balance' => 'nullable|numeric|min:0',
            'address' => 'nullable|string',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'documents' => 'nullable|array',
            'payment_term' => 'nullable|integer|min:0',
            'sort_order' => 'nullable|integer|min:0',
            'status' => 'boolean',
        ]);

        $validated['creator'] = auth()->user()->name ?? 'admin';
        $validated['editor'] = auth()->user()->name ?? 'admin';
        $validated['status'] = $request->boolean('status', true);
        $validated['credit_limit'] = $validated['credit_limit'] ?? 0;
        $validated['outstanding_balance'] = $validated['outstanding_balance'] ?? 0;
        $validated['payment_term'] = $validated['payment_term'] ?? 0;

        Store::create($validated);

        return redirect()->route('stores.index')->with('success', 'Store created successfully');
    }

    public function edit(string $id)
    {
        $store = Store::findOrFail($id);
        $storeGroups = StoreGroup::where('status', true)->where('deleted', false)->orderBy('name')->get(['id', 'name', 'code']);
        $tiers = StoreTier::where('status', true)->where('deleted', false)->orderBy('level')->get(['id', 'name', 'code', 'level']);

        return view('pages.stores.store.edit', compact('store', 'storeGroups', 'tiers'));
    }

    public function update(Request $request, string $id)
    {
        $store = Store::findOrFail($id);

        $validated = $request->validate([
            'store_group_id' => 'required|string|exists:store_group,id',
            'tier_id' => 'nullable|string|exists:store_tier,id',
            'code' => 'required|string|max:100',
            'name' => 'required|string|max:255',
            'owner_user_id' => 'nullable|string|exists:user_admin,id',
            'credit_limit' => 'nullable|numeric|min:0',
            'outstanding_balance' => 'nullable|numeric|min:0',
            'address' => 'nullable|string',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'documents' => 'nullable|array',
            'payment_term' => 'nullable|integer|min:0',
            'sort_order' => 'nullable|integer|min:0',
            'status' => 'boolean',
        ]);

        $validated['editor'] = auth()->user()->name ?? 'admin';
        $validated['status'] = $request->boolean('status', true);
        $validated['credit_limit'] = $validated['credit_limit'] ?? 0;
        $validated['outstanding_balance'] = $validated['outstanding_balance'] ?? 0;
        $validated['payment_term'] = $validated['payment_term'] ?? 0;

        $store->update($validated);

        return redirect()->route('stores.index')->with('success', 'Store updated successfully');
    }

    public function destroy(string $id)
    {
        $store = Store::findOrFail($id);
        $store->delete();

        return redirect()->route('stores.index')->with('success', 'Store deleted successfully');
    }
}
