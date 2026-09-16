<?php

namespace App\Http\Controllers\Product;

use App\Http\Controllers\Controller;

use App\Models\Product\Category;
use App\Models\Product\Product;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        $query = Category::with('parent')->where('deleted', false);
        
        if ($request->has('search')) {
            $search = $request->get('search');
            $query->where('name', 'like', "%{$search}%");
        }
        
        $categories = $query->orderBy('name')->paginate(50);
        $allCategories = Category::where('deleted', false)->orderBy('name')->get(); // for the parent dropdown
        return view('pages.categories.index', compact('categories', 'allCategories'));
    }

    public function flat()
    {
        $categories = Category::all()->map(fn($c) => [
            'id' => $c->id,
            'text' => $c->name,
            'parent' => $c->parent_id ?: '#',
        ])->values();

        return response()->json([
            'success' => true,
            'status_code' => 200,
            'message' => 'Success',
            'data' => $categories,
        ]);
    }

    public function show($slug)
    {
        $category = Category::where('slug', $slug)
            ->where('is_active', true)
            ->where('deleted', false)
            ->with(['children' => function ($q) {
                $q->where('is_active', true)->orderBy('sort_order')->orderBy('name');
            }])
            ->firstOrFail();

        $childIds = $category->children->pluck('id')->push($category->id);

        $products = Product::whereIn('category_id', $childIds)
            ->where('status', true)
            ->where('deleted', false)
            ->with(['brand', 'variants', 'images', 'priceProductSettings', 'storePricings', 'category'])
            ->orderBy('category_id')
            ->orderBy('name')
            ->get();

        $grouped = $products->groupBy(function ($product) {
            return $product->category->name ?? 'Uncategorized';
        });

        return view('pages.categories.show', compact('category', 'products', 'grouped'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'tagline' => 'nullable|string|max:255',
            'slug' => 'nullable|string|max:255|unique:product_category,slug',
            'description' => 'nullable|string',
            'banner_web' => $request->hasFile('banner_web')
                ? 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp,avif,ico|max:5120'
                : 'nullable|string|max:1000',
            'banner_mobile' => $request->hasFile('banner_mobile')
                ? 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp,avif,ico|max:5120'
                : 'nullable|string|max:1000',
            'sort_order' => 'nullable|integer|min:0',
            'status' => 'boolean',
            'has_warranty' => 'boolean',
            'courier_setting_type' => 'nullable|string|in:global,detail',
            'courier_type' => 'nullable|string|in:toko,expedisi,keduanya',
            'shipping_scheme' => 'nullable|string|in:dimension,fixed',
            'shipping_cost' => 'nullable|numeric|min:0',
            'parent_id' => 'nullable|exists:product_category,id',
        ]);

        $validated['courier_setting_type'] = $validated['courier_setting_type'] ?? 'detail';
        $validated['courier_type'] = $validated['courier_type'] ?? 'keduanya';
        $validated['shipping_scheme'] = $validated['shipping_scheme'] ?? 'dimension';
        $validated['shipping_cost'] = (float)($validated['shipping_cost'] ?? 0);
        
        $uploadDisk = config('filesystems.disks.s3.bucket') ? 's3' : 'public';

        if ($request->hasFile('banner_web')) {
            $validated['banner_web'] = $request->file('banner_web')->store('categories', $uploadDisk);
        } elseif ($request->filled('banner_web')) {
            $validated['banner_web'] = $request->input('banner_web');
        }
        if ($request->hasFile('banner_mobile')) {
            $validated['banner_mobile'] = $request->file('banner_mobile')->store('categories', $uploadDisk);
        } elseif ($request->filled('banner_mobile')) {
            $validated['banner_mobile'] = $request->input('banner_mobile');
        }
        
        $category = Category::create($validated);
        return response()->json([
            'success' => true,
            'status_code' => 201,
            'message' => 'Category created',
            'data' => $category,
        ], 201);
    }

    public function edit($id)
    {
        $category = Category::findOrFail($id);
        return response()->json([
            'success' => true,
            'status_code' => 200,
            'message' => 'Success',
            'data' => $category,
        ]);
    }

    public function update(Request $request, $id)
    {
        $category = Category::findOrFail($id);
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'tagline' => 'nullable|string|max:255',
            'slug' => 'nullable|string|max:255|unique:product_category,slug,' . $id,
            'description' => 'nullable|string',
            'banner_web' => $request->hasFile('banner_web')
                ? 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp,avif,ico|max:5120'
                : 'nullable|string|max:1000',
            'banner_mobile' => $request->hasFile('banner_mobile')
                ? 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp,avif,ico|max:5120'
                : 'nullable|string|max:1000',
            'sort_order' => 'nullable|integer|min:0',
            'status' => 'boolean',
            'has_warranty' => 'boolean',
            'courier_setting_type' => 'nullable|string|in:global,detail',
            'courier_type' => 'nullable|string|in:toko,expedisi,keduanya',
            'shipping_scheme' => 'nullable|string|in:dimension,fixed',
            'shipping_cost' => 'nullable|numeric|min:0',
            'parent_id' => 'nullable|exists:product_category,id',
        ]);

        $validated['courier_setting_type'] = $validated['courier_setting_type'] ?? 'detail';
        $validated['courier_type'] = $validated['courier_type'] ?? 'keduanya';
        $validated['shipping_scheme'] = $validated['shipping_scheme'] ?? 'dimension';
        $validated['shipping_cost'] = (float)($validated['shipping_cost'] ?? 0);

        $uploadDisk = config('filesystems.disks.s3.bucket') ? 's3' : 'public';

        if ($request->hasFile('banner_web')) {
            if ($category->banner_web) {
                unlink_media($category->banner_web);
            }
            $validated['banner_web'] = $request->file('banner_web')->store('categories', $uploadDisk);
        } elseif ($request->filled('banner_web')) {
            $newBannerWeb = $request->input('banner_web');
            if ($category->banner_web && $category->banner_web !== $newBannerWeb) {
                unlink_media($category->banner_web);
            }
            $validated['banner_web'] = $newBannerWeb;
        } else {
            unset($validated['banner_web']);
        }

        if ($request->hasFile('banner_mobile')) {
            if ($category->banner_mobile) {
                unlink_media($category->banner_mobile);
            }
            $validated['banner_mobile'] = $request->file('banner_mobile')->store('categories', $uploadDisk);
        } elseif ($request->filled('banner_mobile')) {
            $newBannerMobile = $request->input('banner_mobile');
            if ($category->banner_mobile && $category->banner_mobile !== $newBannerMobile) {
                unlink_media($category->banner_mobile);
            }
            $validated['banner_mobile'] = $newBannerMobile;
        } else {
            unset($validated['banner_mobile']);
        }

        $category->update($validated);
        return response()->json([
            'success' => true,
            'status_code' => 200,
            'message' => 'Category updated',
            'data' => $category,
        ]);
    }

    public function destroy($id)
    {
        $category = Category::findOrFail($id);
        if ($category->banner_web) {
            unlink_media($category->banner_web);
        }
        if ($category->banner_mobile) {
            unlink_media($category->banner_mobile);
        }
        $category->delete();
        return response()->json([
            'success' => true,
            'status_code' => 204,
            'message' => 'Category deleted',
            'data' => null,
        ], 204);
    }
}
