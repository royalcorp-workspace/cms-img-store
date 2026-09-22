<?php

namespace App\Http\Controllers\Product;

use App\Http\Controllers\Controller;
use App\Models\Product\Product;
use App\Models\Product\ProductBundling;
use App\Models\Product\ProductBundlingItem;
use App\Models\Product\Category;
use App\Models\Product\Brand;
use App\Models\Product\Variant;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ProductBundlingController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::where('is_bundle', true)
            ->with(['bundleItems.product', 'bundleItems.variant', 'variants', 'brand', 'category']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('slug', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%");
            });
        }

        $bundlings = $query->orderBy('created_at', 'desc')->paginate(15)->withQueryString();
        return view('pages.bundling.index', compact('bundlings'));
    }

    public function create()
    {
        $products = Product::where('status', true)
            ->where('deleted', false)
            ->where('is_bundle', false)
            ->whereHas('variants', function ($q) {
                $q->where('deleted', false)->where('sell_price', '>', 0);
            })
            ->with(['variants' => function ($q) {
                $q->where('deleted', false)->where('sell_price', '>', 0);
            }, 'brand'])
            ->orderBy('name')
            ->get();

        $products->each(function ($p) {
            $valid = $p->variants->where('deleted', false)->where('sell_price', '>', 0);
            $min = (float)($valid->min('sell_price') ?: $p->variants->where('deleted', false)->min('price') ?: $p->base_price ?: 0);
            $max = (float)($valid->max('sell_price') ?: $p->variants->where('deleted', false)->max('price') ?: $min);
            $p->min_price = $min;
            $p->max_price = $max;
            $p->has_price_range = ($max > $min);
            $p->price_range_text = ($max > $min)
                ? 'Rp ' . number_format($min, 0, ',', '.') . ' - Rp ' . number_format($max, 0, ',', '.')
                : 'Rp ' . number_format($min, 0, ',', '.');
            $p->single_price = $min;
        });

        $bundleableProducts = $products->filter(function ($p) {
            return $p->variants->count() <= 1;
        })->values();

        $categories = Category::orderBy('name')->get();
        $brands = Brand::orderBy('name')->get();

        return view('pages.bundling.create', compact('products', 'bundleableProducts', 'categories', 'brands'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:products,slug',
            'category_id' => 'nullable|string|exists:product_category,id',
            'brand_id' => 'nullable|string|exists:brands,id',
            'description' => 'nullable|string',
            'price' => 'nullable|numeric|min:0',
            'image' => $request->hasFile('image')
                ? 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp,avif,ico|max:5120'
                : 'nullable|string|max:1000',
            'banner_image' => $request->hasFile('banner_image')
                ? 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp,avif,ico|max:5120'
                : 'nullable|string|max:1000',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|string|exists:products,id',
            'items.*.variant_id' => 'nullable|string|exists:product_variants,id',
            'items.*.quantity' => 'required|integer|min:1',
            'suggest_items' => 'nullable|array',
            'suggest_items.*.product_id' => 'nullable|string|exists:products,id',
            'suggest_items.*.variant_id' => 'nullable|string|exists:product_variants,id',
            'suggest_items.*.quantity' => 'nullable|integer|min:1',
            'suggest_items.*.bundle_price' => 'nullable|numeric|min:0',
            'suggest_items.*.discount_percent' => 'nullable|numeric|min:0|max:100',
            'suggest_items.*.discount_nominal' => 'nullable|numeric|min:0',
        ]);

        $firstItemProduct = Product::find($validated['items'][0]['product_id'] ?? null);
        $firstItemVariant = !empty($validated['items'][0]['variant_id']) ? Variant::find($validated['items'][0]['variant_id']) : null;
        $mainPrice = (float)($firstItemVariant?->sell_price ?? ($firstItemProduct?->variants->where('deleted', false)->where('sell_price', '>', 0)->min('sell_price') ?? $firstItemProduct?->base_price ?? 0));

        $totalSuggestBundlePrice = 0;
        if ($request->has('suggest_items') && is_array($request->input('suggest_items'))) {
            foreach ($request->input('suggest_items') as $s) {
                $totalSuggestBundlePrice += (float)($s['bundle_price'] ?? 0);
            }
        }
        $finalPrice = !empty($validated['price']) && (float)$validated['price'] > 0
            ? (float)$validated['price']
            : ($mainPrice + $totalSuggestBundlePrice);

        $categoryId = !empty($validated['category_id']) ? $validated['category_id'] : ($firstItemProduct?->category_id ?? Category::first()?->id);
        $brandId = !empty($validated['brand_id']) ? $validated['brand_id'] : ($firstItemProduct?->brand_id ?? Brand::first()?->id);

        $uploadDisk = config('filesystems.disks.s3.bucket') ? 's3' : 'public';
        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('bundlings', $uploadDisk);
        } elseif ($request->filled('image')) {
            $imagePath = $request->input('image');
        }

        $bannerPath = null;
        if ($request->hasFile('banner_image')) {
            $bannerPath = $request->file('banner_image')->store('bundlings', $uploadDisk);
        } elseif ($request->filled('banner_image')) {
            $bannerPath = $request->input('banner_image');
        }

        $slug = !empty($validated['slug']) ? Str::slug($validated['slug']) : Str::slug($validated['name']);

        // Create in products table
        $product = Product::create([
            'id' => (string) Str::uuid(),
            'name' => $validated['name'],
            'slug' => $slug,
            'category_id' => $categoryId,
            'brand_id' => $brandId,
            'description' => $validated['description'] ?? null,
            'base_price' => (string) $finalPrice,
            'thumbnail' => $imagePath,
            'is_bundle' => true,
            'status' => $request->has('is_active') ? 1 : 0,
            'show_on_web' => true,
            'courier_type' => 'keduanya',
            'shipping_scheme' => 'dimension',
            'shipping_cost' => 0,
        ]);

        // Default variant for standard pos/cart integration
        Variant::create([
            'id' => (string) Str::uuid(),
            'product_id' => $product->id,
            'sku' => 'BND-' . strtoupper(Str::random(6)),
            'variant_name' => 'Default',
            'price' => $finalPrice,
            'sell_price' => $finalPrice,
            'base_price' => $finalPrice,
            'status' => true,
        ]);

        // Mirror in products_bundling for backward compatibility
        try {
            \Illuminate\Support\Facades\DB::table('products_bundling')->updateOrInsert(
                ['id' => $product->id],
                [
                    'name' => $validated['name'],
                    'slug' => $slug,
                    'description' => $validated['description'] ?? null,
                    'price' => $finalPrice,
                    'is_active' => $request->has('is_active'),
                    'image_url' => $imagePath,
                    'banner_image' => $bannerPath,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        } catch (\Throwable $e) {
            // Ignore mirror error
        }

        // Fixed / Main Items
        foreach ($validated['items'] as $item) {
            ProductBundlingItem::create([
                'id' => (string) Str::uuid(),
                'product_bundling_id' => $product->id,
                'product_id' => $item['product_id'],
                'variant_id' => $item['variant_id'] ?? null,
                'quantity' => $item['quantity'],
                'is_suggest' => false,
                'bundle_price' => null,
                'discount_percent' => null,
                'discount_nominal' => null,
            ]);
        }

        // Suggest / Complement Items
        if ($request->has('suggest_items') && is_array($request->input('suggest_items'))) {
            foreach ($request->input('suggest_items') as $suggestItem) {
                if (!empty($suggestItem['product_id'])) {
                    $sProd = Product::find($suggestItem['product_id']);
                    $sVar = !empty($suggestItem['variant_id']) ? Variant::find($suggestItem['variant_id']) : $sProd?->variants->where('deleted', false)->first();
                    $sNormalPrice = (float)($sVar?->sell_price ?? $sVar?->price ?? $sProd?->base_price ?? 0);
                    $bPrice = isset($suggestItem['bundle_price']) && $suggestItem['bundle_price'] !== '' ? (float)$suggestItem['bundle_price'] : $sNormalPrice;
                    $discNominal = max(0, $sNormalPrice - $bPrice);
                    $discPercent = $sNormalPrice > 0 ? round(($discNominal / $sNormalPrice) * 100, 2) : 0;

                    ProductBundlingItem::create([
                        'id' => (string) Str::uuid(),
                        'product_bundling_id' => $product->id,
                        'product_id' => $suggestItem['product_id'],
                        'variant_id' => $suggestItem['variant_id'] ?? null,
                        'quantity' => $suggestItem['quantity'] ?? 1,
                        'is_suggest' => true,
                        'bundle_price' => $bPrice,
                        'discount_percent' => $discPercent,
                        'discount_nominal' => $discNominal,
                    ]);
                }
            }
        }

        return redirect()->route('bundlings.index')->with('success', 'Product bundle created successfully.');
    }

    public function edit($id)
    {
        $bundling = Product::where('is_bundle', true)
            ->with(['bundleItems.product.variants', 'bundleItems.variant', 'variants'])
            ->findOrFail($id);

        $products = Product::where('status', true)
            ->where('deleted', false)
            ->where('is_bundle', false)
            ->whereHas('variants', function ($q) {
                $q->where('deleted', false)->where('sell_price', '>', 0);
            })
            ->with(['variants' => function ($q) {
                $q->where('deleted', false)->where('sell_price', '>', 0);
            }, 'brand'])
            ->orderBy('name')
            ->get();

        // Include any products already in the bundling in case they weren't in $products
        $existingProductIds = $bundling->bundleItems->pluck('product_id')->filter()->unique();
        $missingProductIds = $existingProductIds->diff($products->pluck('id'));
        if ($missingProductIds->isNotEmpty()) {
            $missingProducts = Product::whereIn('id', $missingProductIds)
                ->with(['variants' => fn($q) => $q->where('deleted', false), 'brand'])
                ->get();
            $products = $products->concat($missingProducts)->sortBy('name')->values();
        }

        $products->each(function ($p) {
            $valid = $p->variants->where('deleted', false)->where('sell_price', '>', 0);
            $min = (float)($valid->min('sell_price') ?: $p->variants->where('deleted', false)->min('price') ?: $p->base_price ?: 0);
            $max = (float)($valid->max('sell_price') ?: $p->variants->where('deleted', false)->max('price') ?: $min);
            $p->min_price = $min;
            $p->max_price = $max;
            $p->has_price_range = ($max > $min);
            $p->price_range_text = ($max > $min)
                ? 'Rp ' . number_format($min, 0, ',', '.') . ' - Rp ' . number_format($max, 0, ',', '.')
                : 'Rp ' . number_format($min, 0, ',', '.');
            $p->single_price = $min;
        });

        $bundleableProducts = $products->filter(function ($p) {
            return $p->variants->count() <= 1;
        })->values();

        $categories = Category::orderBy('name')->get();
        $brands = Brand::orderBy('name')->get();

        return view('pages.bundling.edit', compact('bundling', 'products', 'bundleableProducts', 'categories', 'brands'));
    }

    public function update(Request $request, $id)
    {
        $product = Product::where('is_bundle', true)->findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:products,slug,' . $id,
            'category_id' => 'nullable|string|exists:product_category,id',
            'brand_id' => 'nullable|string|exists:brands,id',
            'description' => 'nullable|string',
            'price' => 'nullable|numeric|min:0',
            'image' => $request->hasFile('image')
                ? 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp,avif,ico|max:5120'
                : 'nullable|string|max:1000',
            'banner_image' => $request->hasFile('banner_image')
                ? 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp,avif,ico|max:5120'
                : 'nullable|string|max:1000',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|string|exists:products,id',
            'items.*.variant_id' => 'nullable|string|exists:product_variants,id',
            'items.*.quantity' => 'required|integer|min:1',
            'suggest_items' => 'nullable|array',
            'suggest_items.*.product_id' => 'nullable|string|exists:products,id',
            'suggest_items.*.variant_id' => 'nullable|string|exists:product_variants,id',
            'suggest_items.*.quantity' => 'nullable|integer|min:1',
            'suggest_items.*.bundle_price' => 'nullable|numeric|min:0',
            'suggest_items.*.discount_percent' => 'nullable|numeric|min:0|max:100',
            'suggest_items.*.discount_nominal' => 'nullable|numeric|min:0',
        ]);

        $firstItemProduct = Product::find($validated['items'][0]['product_id'] ?? null);
        $firstItemVariant = !empty($validated['items'][0]['variant_id']) ? Variant::find($validated['items'][0]['variant_id']) : null;
        $mainPrice = (float)($firstItemVariant?->sell_price ?? ($firstItemProduct?->variants->where('deleted', false)->where('sell_price', '>', 0)->min('sell_price') ?? $firstItemProduct?->base_price ?? 0));

        $totalSuggestBundlePrice = 0;
        if ($request->has('suggest_items') && is_array($request->input('suggest_items'))) {
            foreach ($request->input('suggest_items') as $s) {
                $totalSuggestBundlePrice += (float)($s['bundle_price'] ?? 0);
            }
        }
        $finalPrice = !empty($validated['price']) && (float)$validated['price'] > 0
            ? (float)$validated['price']
            : ($mainPrice + $totalSuggestBundlePrice);

        $uploadDisk = config('filesystems.disks.s3.bucket') ? 's3' : 'public';
        $imagePath = $product->thumbnail;
        if ($request->hasFile('image')) {
            if ($product->thumbnail) {
                unlink_media($product->thumbnail);
            }
            $imagePath = $request->file('image')->store('bundlings', $uploadDisk);
        } elseif ($request->filled('image')) {
            $imagePath = $request->input('image');
        }

        $slug = !empty($validated['slug']) ? Str::slug($validated['slug']) : Str::slug($validated['name']);

        $product->update([
            'name' => $validated['name'],
            'slug' => $slug,
            'category_id' => $validated['category_id'] ?? $product->category_id,
            'brand_id' => $validated['brand_id'] ?? $product->brand_id,
            'description' => $validated['description'] ?? null,
            'base_price' => (string) $finalPrice,
            'thumbnail' => $imagePath,
            'status' => $request->has('is_active') ? 1 : 0,
        ]);

        // Update default variant price
        $defaultVariant = $product->variants()->first();
        if ($defaultVariant) {
            $defaultVariant->update([
                'base_price' => $finalPrice,
                'sell_price' => $finalPrice,
            ]);
        } else {
            Variant::create([
                'id' => (string) Str::uuid(),
                'product_id' => $product->id,
                'sku' => 'BND-' . strtoupper(Str::random(6)),
                'variant_name' => 'Paket Bundling',
                'base_price' => $finalPrice,
                'sell_price' => $finalPrice,
                'stock_quantity' => 9999,
                'status' => 1,
            ]);
        }

        // Recreate bundle items
        ProductBundlingItem::where('product_bundling_id', $product->id)->delete();

        foreach ($request->input('items', []) as $item) {
            ProductBundlingItem::create([
                'id' => (string) Str::uuid(),
                'product_bundling_id' => $product->id,
                'product_id' => $item['product_id'],
                'variant_id' => !empty($item['variant_id']) ? $item['variant_id'] : null,
                'quantity' => $item['quantity'] ?? 1,
                'is_suggest' => false,
            ]);
        }

        if ($request->has('suggest_items') && is_array($request->input('suggest_items'))) {
            foreach ($request->input('suggest_items') as $sItem) {
                if (!empty($sItem['product_id'])) {
                    $sProd = Product::find($sItem['product_id']);
                    $sVar = !empty($sItem['variant_id']) ? Variant::find($sItem['variant_id']) : $sProd?->variants->where('deleted', false)->first();
                    $sNormalPrice = (float)($sVar?->sell_price ?? $sVar?->price ?? $sProd?->base_price ?? 0);
                    $bPrice = isset($sItem['bundle_price']) && $sItem['bundle_price'] !== '' ? (float)$sItem['bundle_price'] : $sNormalPrice;
                    $discNominal = max(0, $sNormalPrice - $bPrice);
                    $discPercent = $sNormalPrice > 0 ? round(($discNominal / $sNormalPrice) * 100, 2) : 0;

                    ProductBundlingItem::create([
                        'id' => (string) Str::uuid(),
                        'product_bundling_id' => $product->id,
                        'product_id' => $sItem['product_id'],
                        'variant_id' => !empty($sItem['variant_id']) ? $sItem['variant_id'] : null,
                        'quantity' => $sItem['quantity'] ?? 1,
                        'is_suggest' => true,
                        'bundle_price' => $bPrice,
                        'discount_percent' => $discPercent,
                        'discount_nominal' => $discNominal,
                    ]);
                }
            }
        }

        // Mirror update to products_bundling if exists
        try {
            \Illuminate\Support\Facades\DB::table('products_bundling')->updateOrInsert(
                ['id' => $product->id],
                [
                    'name' => $validated['name'],
                    'slug' => $slug,
                    'description' => $validated['description'] ?? null,
                    'price' => $finalPrice,
                    'is_active' => $request->has('is_active'),
                    'image_url' => $imagePath,
                    'updated_at' => now(),
                ]
            );
        } catch (\Throwable $e) {}

        return redirect()->route('bundlings.index')->with('success', 'Product bundle updated successfully.');
    }

    public function destroy($id)
    {
        $product = Product::where('is_bundle', true)->findOrFail($id);
        $product->update(['deleted' => true, 'status' => 0]);

        ProductBundlingItem::where('product_bundling_id', $product->id)->delete();
        ProductBundling::where('id', $product->id)->update(['deleted' => true, 'is_active' => false]);

        return redirect()->route('bundlings.index')->with('success', 'Product bundle deleted successfully.');
    }
}
