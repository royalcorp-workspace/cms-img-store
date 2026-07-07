<?php

namespace App\Http\Controllers;

use App\Models\Product\Product;
use App\Models\Product\Variant;
use App\Models\Promo\StorePricing;
use App\Models\Store\Store;
use Illuminate\Http\Request;

class PriceProductSettingStoreController extends Controller
{
    public function index(Request $request)
    {
        $query = StorePricing::query()->with(['product', 'variant', 'store']);

        if ($search = $request->query('search')) {
            $query->where(function ($q) use ($search) {
                $q->whereHas('store', function ($sq) use ($search) {
                    $sq->where('name', 'like', "%{$search}%")->orWhere('code', 'like', "%{$search}%");
                })
                ->orWhereHas('product', function ($pq) use ($search) {
                    $pq->where('name', 'like', "%{$search}%");
                })
                ->orWhereHas('variant', function ($vq) use ($search) {
                    $vq->where('variant_name', 'like', "%{$search}%");
                });
            });
        }

        if ($request->filled('store_id')) {
            $query->where('store_id', $request->query('store_id'));
        }

        if ($request->filled('product_id')) {
            $query->where('product_id', $request->query('product_id'));
        }

        if ($request->filled('variant_id')) {
            $query->where('variant_id', $request->query('variant_id'));
        }

        $pricings = $query->orderByDesc('created_at')->paginate(15)->appends($request->query());

        $stats = [
            'total' => StorePricing::count(),
            'products' => StorePricing::whereNotNull('product_id')->count(),
            'variants' => StorePricing::whereNotNull('variant_id')->count(),
        ];

        $products = Product::where('deleted', false)->orderBy('name')->get(['id', 'name']);
        $variants = Variant::orderBy('variant_name')->get(['id', 'variant_name', 'product_id']);
        $stores = Store::where('status', true)->where('deleted', false)->orderBy('name')->get(['id', 'name', 'code']);

        return view('pages.promo.price-product-setting-store', compact('pricings', 'stats', 'products', 'variants', 'stores'));
    }

    public function create()
    {
        $products = Product::where('deleted', false)
            ->with(['variants' => function ($q) {
                $q->orderBy('sort_order')->orderBy('variant_name');
            }])
            ->orderBy('name')
            ->get();

        $stores = Store::where('status', true)->where('deleted', false)->orderBy('name')->get(['id', 'name', 'code']);

        return view('pages.promo.price-product-setting-store-create', compact('products', 'stores'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'store_id' => 'required|string|exists:store,id',
            'product_id' => 'nullable|string|exists:products,id',
            'variant_id' => 'nullable|string|exists:product_variants,id',
            'adjustments' => 'nullable|array',
            'adjustments.*.adjustment_name' => 'nullable|string|max:255',
            'adjustments.*.adjustment_name_desc' => 'nullable|string|max:255',
            'adjustments.*.adjustment_amount' => 'nullable|numeric',
        ]);

        if (empty($validated['product_id']) && empty($validated['variant_id'])) {
            return back()->withErrors(['product_id' => 'Pilih produk atau variant minimal satu.'])->withInput();
        }

        StorePricing::create($validated);

        return redirect()->route('price-product-setting-store.index')->with('success', 'Store pricing created successfully');
    }

    public function edit(string $id)
    {
        $pricing = StorePricing::findOrFail($id);
        $products = Product::where('deleted', false)
            ->with(['variants' => function ($q) {
                $q->orderBy('sort_order')->orderBy('variant_name');
            }])
            ->orderBy('name')
            ->get();

        $stores = Store::where('status', true)->where('deleted', false)->orderBy('name')->get(['id', 'name', 'code']);

        return view('pages.promo.price-product-setting-store-edit', compact('pricing', 'products', 'stores'));
    }

    public function update(Request $request, string $id)
    {
        $pricing = StorePricing::findOrFail($id);

        $validated = $request->validate([
            'store_id' => 'required|string|exists:store,id',
            'product_id' => 'nullable|string|exists:products,id',
            'variant_id' => 'nullable|string|exists:product_variants,id',
            'adjustments' => 'nullable|array',
            'adjustments.*.adjustment_name' => 'nullable|string|max:255',
            'adjustments.*.adjustment_name_desc' => 'nullable|string|max:255',
            'adjustments.*.adjustment_amount' => 'nullable|numeric',
        ]);

        if (empty($validated['product_id']) && empty($validated['variant_id'])) {
            return back()->withErrors(['product_id' => 'Pilih produk atau variant minimal satu.'])->withInput();
        }

        $pricing->update($validated);

        return redirect()->route('price-product-setting-store.index')->with('success', 'Store pricing updated successfully');
    }

    public function destroy(string $id)
    {
        $pricing = StorePricing::findOrFail($id);
        $pricing->delete();

        return redirect()->route('price-product-setting-store.index')->with('success', 'Store pricing deleted successfully');
    }
}
