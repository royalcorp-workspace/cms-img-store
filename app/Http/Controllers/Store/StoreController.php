<?php

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Controller;

use App\Models\Store\StoreGroup;
use App\Models\Store\Store;
use App\Models\Store\StoreTier;
use Illuminate\Http\Request;

class StoreController extends Controller
{
    public function index(Request $request)
    {
        $user = auth('admin')->user();
        $isSales = $user && method_exists($user, 'isSales') && $user->isSales();
        $isStore = $user && method_exists($user, 'isStore') && $user->isStore();

        $query = Store::query()->with(['group', 'tier', 'owner', 'salesCanvassers']);

        // 1. Jika sesi toko aktif
        if ($currentStore = session('current_store')) {
            $query->where('id', $currentStore['id']);
        } elseif ($isSales) {
            // 2. Jika Sales Canvasser, HANYA tampilkan toko binaan di store.sales_stores
            $assignedStoreIds = \App\Models\Store\SalesStore::where('sales_user_id', $user->id)->pluck('store_id');
            $query->whereIn('id', $assignedStoreIds);
        } elseif ($isStore) {
            // 3. Jika Toko, hanya tampilkan tokonya sendiri
            $storeId = $user->store_id ?? Store::where('owner_user_id', $user->id)->value('id');
            if ($storeId) {
                $query->where('id', $storeId);
            }
        }

        if ($search = $request->query('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'ilike', "%{$search}%")
                  ->orWhere('code', 'ilike', "%{$search}%");
                if (is_numeric($search)) {
                    $q->orWhere('address_number', (int) $search);
                }
            });
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

        return view('pages.stores.store.index', compact('stores', 'storeGroups', 'tiers', 'isSales'));
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
            'address_number' => 'nullable|integer|min:1',
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
            'address_number' => 'nullable|integer|min:1',
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

    /**
     * Beralih ke konteks toko spesifik (untuk Sales Canvasser atau Super Admin)
     */
    public function switchContext(Request $request, string $id)
    {
        $user = auth('admin')->user();
        $store = Store::where('deleted', false)->where('status', true)->findOrFail($id);

        if ($user && method_exists($user, 'isSales') && $user->isSales()) {
            $isAssigned = \App\Models\Store\SalesStore::where('sales_user_id', $user->id)
                ->where('store_id', $store->id)
                ->exists();

            if (!$isAssigned) {
                return redirect()->route('stores.index')->with('error', 'Anda tidak memiliki penugasan untuk toko ini.');
            }
        }

        $channel = $store->channels()->where('status', true)->where('deleted', false)->first();

        session()->put('current_store', [
            'id'                  => $store->id,
            'store_id'            => $store->id,
            'code'                => $store->code,
            'name'                => $store->name,
            'address_number'      => (int) $store->address_number,
            'tier_id'             => $store->tier_id,
            'tier_name'           => $store->tier?->name ?? 'Standard',
            'credit_limit'        => (float) $store->credit_limit,
            'outstanding_balance' => (float) $store->outstanding_balance,
            'available_credit'    => max(0, (float) $store->credit_limit - (float) $store->outstanding_balance),
            'payment_term'        => (int) $store->payment_term,
            'payment_term_days'   => (int) $store->payment_term,
            'store_group_id'      => $store->store_group_id,
            'email'               => $store->email,
            'phone'               => $store->phone,
            'address'             => $store->address,
            'is_hq'               => ($store->code === 'ONLINE_RETAIL'),
        ]);

        if ($channel) {
            session()->put('current_channel', [
                'id'         => $channel->id,
                'channel_id' => $channel->id,
                'code'       => $channel->code,
                'name'       => $channel->name,
                'group_id'   => $channel->store_channel_group_id,
            ]);
        }

        session()->put('assisted_by_sales', $user && method_exists($user, 'isSales') && $user->isSales());

        return redirect()->route('products.index')->with('success', "Berhasil masuk ke konteks toko {$store->name}. Menampilkan produk dan harga khusus toko ini.");
    }

    /**
     * Bersihkan konteks toko aktif (kembali ke multi-toko / overview sales)
     */
    public function clearContext(Request $request)
    {
        session()->forget(['current_store', 'current_channel', 'assisted_by_sales']);
        return redirect()->route('stores.index')->with('success', 'Kembali ke daftar toko binaan.');
    }
}
