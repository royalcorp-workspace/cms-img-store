<?php

namespace App\Http\Controllers\Product;

use App\Http\Controllers\Controller;
use App\Models\Product\Product;
use App\Models\Product\Category;
use App\Models\Product\Brand;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductDisplayWebController extends Controller
{
    public function index(Request $request)
    {
        $tab = $request->query('tab', 'all'); // 'all', 'category', 'brand', 'suggest'
        $search = $request->query('search');

        $categories = Category::where('deleted', false)
            ->withCount(['products' => fn($q) => $q->where('deleted', false)])
            ->orderBy('name')
            ->get();

        $brands = Brand::where('deleted', false)
            ->withCount(['products' => fn($q) => $q->where('deleted', false)])
            ->orderBy('name')
            ->get();

        $categoryId = $request->query('category_id');
        $brandId = $request->query('brand_id');
        $productId = $request->query('product_id');

        // Default active category/brand if on those tabs and none selected
        if ($tab === 'category' && empty($categoryId) && $categories->isNotEmpty()) {
            $categoryId = $categories->first()->id;
        }
        if ($tab === 'brand' && empty($brandId) && $brands->isNotEmpty()) {
            $brandId = $brands->first()->id;
        }

        $activeCategory = $categories->firstWhere('id', $categoryId);
        $activeBrand = $brands->firstWhere('id', $brandId);

        // Products query base
        $query = Product::where('deleted', false)->with(['category', 'brand', 'images']);

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'ilike', "%{$search}%")
                  ->orWhere('code', 'ilike', "%{$search}%")
                  ->orWhere('slug', 'ilike', "%{$search}%");
            });
        }

        $suggestParentProducts = collect();
        $activeProduct = null;
        $availableSuggestions = collect();

        if ($tab === 'category') {
            if (!empty($categoryId)) {
                $query->where('category_id', $categoryId);
            }
            $products = $query->orderByRaw('CASE WHEN sort_order > 0 THEN sort_order ELSE 999999 END ASC')
                ->orderBy('sort_order', 'asc')
                ->orderBy('best_seller', 'desc')
                ->orderBy('created_at', 'desc')
                ->get();
        } elseif ($tab === 'brand') {
            if (!empty($brandId)) {
                $query->where('brand_id', $brandId);
            }
            $products = $query->orderByRaw('CASE WHEN sort_order > 0 THEN sort_order ELSE 999999 END ASC')
                ->orderBy('sort_order', 'asc')
                ->orderBy('best_seller', 'desc')
                ->orderBy('created_at', 'desc')
                ->get();
        } elseif ($tab === 'suggest') {
            // All products that can have suggestions
            $suggestParentProducts = Product::where('deleted', false)
                ->withCount('suggestedProducts')
                ->orderByDesc('suggested_products_count')
                ->orderBy('name')
                ->get();

            if (empty($productId) && $suggestParentProducts->isNotEmpty()) {
                $productId = $suggestParentProducts->first()->id;
            }

            $activeProduct = Product::where('deleted', false)
                ->with(['suggestedProducts' => function ($q) {
                    $q->orderByPivot('sort_order', 'asc');
                }, 'images'])
                ->find($productId);

            $products = $activeProduct ? $activeProduct->suggestedProducts : collect();

            if ($activeProduct) {
                $existingIds = $products->pluck('id')->push($activeProduct->id)->toArray();
                $availableSuggestions = Product::where('deleted', false)
                    ->whereNotIn('id', $existingIds)
                    ->orderBy('name')
                    ->get(['id', 'name', 'code', 'thumbnail']);
            }
        } else {
            // Tab 'all'
            $products = $query->orderByRaw('CASE WHEN sort_order > 0 THEN sort_order ELSE 999999 END ASC')
                ->orderBy('sort_order', 'asc')
                ->orderBy('best_seller', 'desc')
                ->orderBy('created_at', 'desc')
                ->get();
        }

        return view('pages.products.display-web.index', compact(
            'tab',
            'products',
            'categories',
            'brands',
            'categoryId',
            'brandId',
            'productId',
            'activeCategory',
            'activeBrand',
            'activeProduct',
            'suggestParentProducts',
            'availableSuggestions',
            'search'
        ));
    }

    public function reorder(Request $request)
    {
        $request->validate([
            'product_ids' => 'required|array',
            'product_ids.*' => 'required|uuid',
            'category_id' => 'nullable|uuid',
            'brand_id' => 'nullable|uuid',
        ]);

        $productIds = $request->input('product_ids');
        if (empty($productIds)) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak ada data produk yang diurutkan.'
            ], 400);
        }

        DB::beginTransaction();
        try {
            $categoryId = $request->input('category_id');
            $brandId = $request->input('brand_id');
            $isFiltered = !empty($categoryId) || !empty($brandId);

            if ($isFiltered) {
                $currentProducts = Product::whereIn('id', $productIds)
                    ->orderByRaw('CASE WHEN sort_order > 0 THEN sort_order ELSE 999999 END ASC')
                    ->orderBy('created_at', 'desc')
                    ->get(['id', 'sort_order']);

                $existingSlots = $currentProducts->pluck('sort_order')->values()->all();

                $hasValidSlots = count(array_filter($existingSlots, fn($s) => $s > 0)) === count($existingSlots)
                    && count(array_unique($existingSlots)) === count($existingSlots);

                if (!$hasValidSlots) {
                    $existingSlots = range(1, count($productIds));
                } else {
                    sort($existingSlots);
                }

                foreach ($productIds as $idx => $id) {
                    $slot = $existingSlots[$idx] ?? ($idx + 1);
                    Product::where('id', $id)->update(['sort_order' => $slot]);
                }
            } else {
                foreach ($productIds as $idx => $id) {
                    Product::where('id', $id)->update(['sort_order' => $idx + 1]);
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Urutan produk berhasil disimpan!'
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan urutan: ' . $e->getMessage()
            ], 500);
        }
    }

    public function reorderSuggestions(Request $request)
    {
        $request->validate([
            'product_id' => 'required|uuid|exists:products,id',
            'suggested_ids' => 'required|array',
            'suggested_ids.*' => 'required|uuid|exists:products,id',
        ]);

        $product = Product::findOrFail($request->product_id);
        $suggestedIds = $request->suggested_ids;

        DB::beginTransaction();
        try {
            $syncData = [];
            foreach ($suggestedIds as $index => $sId) {
                $syncData[$sId] = ['sort_order' => $index + 1];
            }

            $product->suggestedProducts()->sync($syncData);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Urutan produk saran berhasil disimpan!'
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan urutan saran: ' . $e->getMessage()
            ], 500);
        }
    }

    public function addSuggestion(Request $request)
    {
        $request->validate([
            'product_id' => 'required|uuid|exists:products,id',
            'suggested_product_id' => 'required|uuid|exists:products,id',
        ]);

        try {
            $product = Product::findOrFail($request->product_id);
            $maxOrder = DB::table('product_suggestions')
                ->where('product_id', $product->id)
                ->max('sort_order') ?? 0;

            $product->suggestedProducts()->syncWithoutDetaching([
                $request->suggested_product_id => ['sort_order' => $maxOrder + 1]
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Produk saran berhasil ditambahkan!'
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menambahkan produk saran: ' . $e->getMessage()
            ], 500);
        }
    }

    public function removeSuggestion(Request $request)
    {
        $request->validate([
            'product_id' => 'required|uuid|exists:products,id',
            'suggested_product_id' => 'required|uuid|exists:products,id',
        ]);

        try {
            $product = Product::findOrFail($request->product_id);
            $product->suggestedProducts()->detach($request->suggested_product_id);

            return response()->json([
                'success' => true,
                'message' => 'Produk saran berhasil dihapus!'
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus produk saran: ' . $e->getMessage()
            ], 500);
        }
    }

    public function toggleVisibility(Request $request)
    {
        $request->validate([
            'product_id' => 'required|uuid',
            'show_on_web' => 'required|boolean',
        ]);

        $product = Product::findOrFail($request->product_id);
        $product->update([
            'show_on_web' => (bool) $request->show_on_web,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Status tampilan web untuk produk "' . $product->name . '" berhasil diperbarui.'
        ]);
    }
}
