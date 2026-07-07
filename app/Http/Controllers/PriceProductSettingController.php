<?php

namespace App\Http\Controllers;

use App\Models\Product\Product;
use App\Models\Product\Variant;
use App\Models\Promo\PriceProductSetting;
use App\Models\Store\Store;
use App\Models\Store\StoreChannelGroup;
use App\Models\Store\StoreTier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PriceProductSettingController extends Controller
{
    public function index(Request $request)
    {
        $query = PriceProductSetting::query()->withoutGlobalScope('active');

        if ($search = $request->query('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                  ->orWhere('title', 'like', "%{$search}%");
            });
        }

        if ($request->has('status')) {
            $status = $request->query('status');
            if ($status === 'active') {
                $query->where('is_active', true)->where('deleted', false);
            } elseif ($status === 'inactive') {
                $query->where(function ($q) {
                    $q->where('is_active', false)->orWhere('deleted', true);
                });
            } elseif ($status === 'expired') {
                $query->where('end_date', '<', now());
            }
        }

        if ($request->has('type')) {
            $query->where('type', $request->query('type'));
        }

        $settings = $query->orderByDesc('sort_order')->paginate(9)->appends($request->query());

        $stats = [
            'total' => PriceProductSetting::withoutGlobalScope('active')->count(),
            'active' => PriceProductSetting::active()->count(),
            'featured' => PriceProductSetting::featured()->count(),
            'volume' => PriceProductSetting::where('type', 2)->active()->count(),
        ];

        return view('pages.promo.price-product-setting', compact('settings', 'stats'));
    }

    public function create()
    {
        $productsQuery = \App\Models\Product\Product::where('deleted', false)
            ->select(['id', 'name', 'category_id', 'base_price'])
            ->with(['variants' => function ($q) {
                $q->select(['id', 'product_id', 'variant_name', 'sku', 'price', 'stock_qty', 'sort_order'])
                  ->orderBy('sort_order')->orderBy('variant_name');
            }, 'category' => function ($q) {
                $q->select(['id', 'name', 'slug']);
            }, 'images' => function ($q) {
                $q->select(['id', 'product_id', 'url']);
            }])
            ->orderBy('name');

        if (request()->has('search') && request('search') != '') {
            $productsQuery->where('name', 'like', '%' . request('search') . '%');
        }

        if (request()->has('category') && request('category') != 'all') {
            $productsQuery->whereHas('category', function($q) {
                $q->where('slug', request('category'))
                  ->orWhereIn('parent_id', function($q2) {
                      $q2->select('id')->from('product_category')->where('slug', request('category'));
                  });
            });
        }

        $products = $productsQuery->paginate(12);

        if (request()->ajax()) {
            return response()->json([
                'html' => view('pages.promo.partials.product-cards', compact('products'))->render(),
                'hasMore' => $products->hasMorePages()
            ]);
        }

        $categories = \App\Models\Product\Category::where('parent_id', null)
            ->where('status', true)
            ->with(['children' => function ($q) {
                $q->where('status', true)->orderBy('sort_order')->orderBy('name');
            }])
            ->orderBy('sort_order')->orderBy('name')
            ->get();

        $stores = Store::where('status', true)->where('deleted', false)->orderBy('name')->get(['id', 'name', 'code']);
        $tiers = StoreTier::where('status', true)->where('deleted', false)->orderBy('level')->get(['id', 'name', 'code', 'level']);
        $channelGroups = StoreChannelGroup::where('status', true)->where('deleted', false)->orderBy('name')->get(['id', 'name', 'code']);

        return view('pages.promo.price-product-setting-create', compact('products', 'categories', 'stores', 'tiers', 'channelGroups'));
    }

    public function edit($id)
    {
        $setting = PriceProductSetting::withoutGlobalScope('active')->findOrFail($id);
        
        $productsQuery = \App\Models\Product\Product::where('deleted', false)
            ->select(['id', 'name', 'category_id', 'base_price'])
            ->with(['variants' => function ($q) {
                $q->select(['id', 'product_id', 'variant_name', 'sku', 'price', 'stock_qty', 'sort_order'])
                  ->orderBy('sort_order')->orderBy('variant_name');
            }, 'category' => function ($q) {
                $q->select(['id', 'name', 'slug']);
            }, 'images' => function ($q) {
                $q->select(['id', 'product_id', 'url']);
            }])
            ->orderBy('name');

        if (request()->has('search') && request('search') != '') {
            $productsQuery->where('name', 'like', '%' . request('search') . '%');
        }

        if (request()->has('category') && request('category') != 'all') {
            $productsQuery->whereHas('category', function($q) {
                $q->where('slug', request('category'))
                  ->orWhereIn('parent_id', function($q2) {
                      $q2->select('id')->from('product_category')->where('slug', request('category'));
                  });
            });
        }

        $products = $productsQuery->paginate(12);

        $selectedVariantIds = $setting->variants()->pluck('product_variants.id')->toArray();
        $variantPricesFromPivot = $setting->variants()->pluck('discount_value', 'product_variants.id')->toArray();

        if (request()->ajax()) {
            return response()->json([
                'html' => view('pages.promo.partials.product-cards', compact('products', 'selectedVariantIds', 'variantPricesFromPivot'))->render(),
                'hasMore' => $products->hasMorePages()
            ]);
        }

        $categories = \App\Models\Product\Category::where('parent_id', null)
            ->where('status', true)
            ->with(['children' => function ($q) {
                $q->where('status', true)->orderBy('sort_order')->orderBy('name');
            }])
            ->orderBy('sort_order')->orderBy('name')
            ->get();

        $stores = Store::where('status', true)->where('deleted', false)->orderBy('name')->get(['id', 'name', 'code']);
        $tiers = StoreTier::where('status', true)->where('deleted', false)->orderBy('level')->get(['id', 'name', 'code', 'level']);
        $channelGroups = StoreChannelGroup::where('status', true)->where('deleted', false)->orderBy('name')->get(['id', 'name', 'code']);

        $volumeTiers = $setting->volumeTiers()->orderBy('sort_order')->get();

        return view('pages.promo.price-product-setting-edit', compact('setting', 'products', 'categories', 'volumeTiers', 'selectedVariantIds', 'variantPricesFromPivot', 'stores', 'tiers', 'channelGroups'));
    }

    public function update(Request $request, $id)
    {
        $setting = PriceProductSetting::withoutGlobalScope('active')->findOrFail($id);

        $code = $request->input('code');
        if (empty($code)) {
            $code = 'PROMO-' . strtoupper(bin2hex(random_bytes(4)));
        }

        $request->merge([
            'scope_store_id' => $request->input('scope_store_id') ?: null,
            'scope_tier_id' => $request->input('scope_tier_id') ?: null,
            'scope_channel_group_id' => $request->input('scope_channel_group_id') ?: null,
            'code' => $code,
        ]);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'code' => 'required|string|max:255|unique:price_product_settings,code,' . $id,
            'description' => 'nullable|string',
            'type' => 'required|integer|in:1,2',
            'discount_type' => 'required|integer|in:1,2',
            'discount_value' => 'required|numeric|min:0',
            'min_purchase' => 'nullable|numeric|min:0',
            'max_discount' => 'nullable|numeric|min:0',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
            'scope' => 'required|integer|in:1,2,3',
            'scope_store_type' => 'nullable|integer|in:0,1,2,3',
            'scope_store_id' => 'nullable|uuid|exists:store,id',
            'scope_tier_id' => 'nullable|uuid|exists:store_tier,id',
            'scope_channel_group_id' => 'nullable|uuid|exists:store_channel_group,id',
            'variant_ids' => 'nullable|array',
            'variant_ids.*' => 'uuid|exists:product_variants,id',
            'variant_prices' => 'nullable|array',
            'variant_prices.*' => 'nullable|numeric|min:0',
            'volume_tiers' => 'nullable|array',
            'volume_tiers.*.min_purchase' => 'nullable|integer|min:0',
            'volume_tiers.*.discount_type' => 'nullable|integer|in:1,2',
            'volume_tiers.*.discount_value' => 'nullable|numeric|min:0',
        ]);

        $validated['is_active'] = $request->boolean('is_active');
        $validated['is_featured'] = $request->boolean('is_featured');

        $scopeStoreType = (int) $request->input('scope_store_type', 0);
        $scopeStoreId = null;

        if ($scopeStoreType === 1) {
            $scopeStoreId = $request->input('scope_tier_id');
        } elseif ($scopeStoreType === 2) {
            $scopeStoreId = $request->input('scope_store_id');
        } elseif ($scopeStoreType === 3) {
            $scopeStoreId = $request->input('scope_channel_group_id');
        }

        $validated['scope_store_type'] = $scopeStoreType;
        $validated['scope_store_id'] = $scopeStoreId;

        $variantIds = $request->input('variant_ids', []);

        try {
            DB::beginTransaction();

            unset($validated['volume_tiers']);
            $setting->update($validated);

            if (!empty($variantIds)) {
                $pivotData = [];
                $variantPrices = $request->input('variant_prices', []);
                // Fetch product_id for each variant to prevent Not-Null constraint errors
                $variants = Variant::whereIn('id', $variantIds)->get(['id', 'product_id'])->keyBy('id');

                foreach ($variantIds as $variantId) {
                    $variant = $variants->get($variantId);
                    $productId = $variant ? $variant->product_id : null;

                    $pivotData[$variantId] = [
                        'product_id' => $productId,
                        'discount_type' => $validated['discount_type'],
                        'discount_value' => $variantPrices[$variantId] ?? $validated['discount_value'],
                    ];
                }
                $setting->variants()->sync($pivotData);
            } else {
                $setting->variants()->detach();
            }

            if ($validated['type'] == 2 && $request->has('volume_tiers')) {
                $this->saveVolumeTiers($setting, $request->input('volume_tiers'));
            } elseif ($validated['type'] != 2) {
                $setting->volumeTiers()->delete();
            }

            DB::commit();
            return redirect()->route('price-settings.index')->with('success', 'Price setting updated successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()->with('error', 'Gagal memperbarui price setting: ' . $e->getMessage());
        }
    }

    private function saveVolumeTiers(PriceProductSetting $setting, array $tiers): void
    {
        $setting->volumeTiers()->delete();
        foreach ($tiers as $index => $tier) {
            if (empty($tier['min_purchase']) && empty($tier['discount_value'])) continue;
            $setting->volumeTiers()->create([
                'id' => \Illuminate\Support\Str::uuid(),
                'min_purchase' => $tier['min_purchase'] ?? 0,
                'discount_type' => $tier['discount_type'] ?? 1,
                'discount_value' => $tier['discount_value'] ?? 0,
                'sort_order' => $index,
            ]);
        }
    }

    public function bulk()
    {
        $categories = \App\Models\Product\Category::where('parent_id', null)
            ->where('status', true)
            ->with(['children' => function ($q) {
                $q->where('status', true)->orderBy('sort_order')->orderBy('name');
            }])
            ->orderBy('sort_order')->orderBy('name')
            ->get();

        $products = Product::query()
            ->where('deleted', false)
            ->with(['variants' => function ($q) {
                $q->orderBy('sort_order')->orderBy('variant_name');
            }, 'category'])
            ->orderBy('name')
            ->get();

        return view('pages.promo.bulk-price-setting', compact('products', 'categories'));
    }

    public function bulkStore(Request $request)
    {
        $request->validate([
            'variants' => 'required|array',
            'variants.*.id' => 'required|uuid|exists:product_variants,id',
            'variants.*.price' => 'required|numeric|min:0',
            'products' => 'nullable|array',
            'products.*.base_price' => 'nullable|numeric|min:0',
        ]);

        $updatedProductIds = [];

        foreach ($request->input('variants') as $variantData) {
            $variant = Variant::findOrFail($variantData['id']);
            $variant->update([
                'price' => $variantData['price'],
                'editor' => Auth::id(),
            ]);
            $updatedProductIds[] = $variant->product_id;
        }

        if ($request->has('products')) {
            foreach ($request->input('products') as $productId => $productData) {
                $product = Product::findOrFail($productId);
                $product->update([
                    'base_price' => $productData['base_price'],
                    'editor' => Auth::id(),
                ]);
            }
        }

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'Variant prices updated successfully']);
        }

        return redirect()->route('price-settings.bulk')->with('success', 'Variant prices updated successfully');
    }

    public function store(Request $request)
    {
        $code = $request->input('code');
        if (empty($code)) {
            $code = 'PROMO-' . strtoupper(bin2hex(random_bytes(4)));
        }

        $request->merge([
            'scope_store_id' => $request->input('scope_store_id') ?: null,
            'scope_tier_id' => $request->input('scope_tier_id') ?: null,
            'scope_channel_group_id' => $request->input('scope_channel_group_id') ?: null,
            'code' => $code,
        ]);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'code' => 'required|string|max:255|unique:price_product_settings,code',
            'description' => 'nullable|string',
            'type' => 'required|integer|in:1,2',
            'discount_type' => 'required|integer|in:1,2',
            'discount_value' => 'required|numeric|min:0',
            'min_purchase' => 'nullable|numeric|min:0',
            'max_discount' => 'nullable|numeric|min:0',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
            'scope' => 'required|integer|in:1,2,3',
            'scope_store_type' => 'nullable|integer|in:0,1,2,3',
            'scope_store_id' => 'nullable|uuid|exists:store,id',
            'scope_tier_id' => 'nullable|uuid|exists:store_tier,id',
            'scope_channel_group_id' => 'nullable|uuid|exists:store_channel_group,id',
            'variant_ids' => 'nullable|array',
            'variant_ids.*' => 'uuid|exists:product_variants,id',
            'variant_prices' => 'nullable|array',
            'variant_prices.*' => 'nullable|numeric|min:0',
            'volume_tiers' => 'nullable|array',
            'volume_tiers.*.min_purchase' => 'nullable|integer|min:0',
            'volume_tiers.*.discount_type' => 'nullable|integer|in:1,2',
            'volume_tiers.*.discount_value' => 'nullable|numeric|min:0',
        ]);

        $validated['is_active'] = $request->boolean('is_active');
        $validated['is_featured'] = $request->boolean('is_featured');

        $scopeStoreType = (int) $request->input('scope_store_type', 0);
        $scopeStoreId = null;

        if ($scopeStoreType === 1) {
            $scopeStoreId = $request->input('scope_tier_id');
        } elseif ($scopeStoreType === 2) {
            $scopeStoreId = $request->input('scope_store_id');
        } elseif ($scopeStoreType === 3) {
            $scopeStoreId = $request->input('scope_channel_group_id');
        }

        $validated['scope_store_type'] = $scopeStoreType;
        $validated['scope_store_id'] = $scopeStoreId;

        $variantIds = $request->input('variant_ids', []);

        try {
            DB::beginTransaction();

            unset($validated['volume_tiers']);
            $setting = PriceProductSetting::create($validated);

            if (!empty($variantIds)) {
                $pivotData = [];
                $variantPrices = $request->input('variant_prices', []);
                // Fetch product_id for each variant to prevent Not-Null constraint errors
                $variants = Variant::whereIn('id', $variantIds)->get(['id', 'product_id'])->keyBy('id');

                foreach ($variantIds as $variantId) {
                    $variant = $variants->get($variantId);
                    $productId = $variant ? $variant->product_id : null;

                    $pivotData[$variantId] = [
                        'product_id' => $productId,
                        'discount_type' => $validated['discount_type'],
                        'discount_value' => $variantPrices[$variantId] ?? $validated['discount_value'],
                    ];
                }
                $setting->variants()->attach($pivotData);
            }

            if ($validated['type'] == 2 && $request->has('volume_tiers')) {
                $this->saveVolumeTiers($setting, $request->input('volume_tiers'));
            }

            DB::commit();
            return redirect()->route('price-settings.index')->with('success', 'Price setting created successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()->with('error', 'Gagal membuat price setting: ' . $e->getMessage());
        }
    }
}
