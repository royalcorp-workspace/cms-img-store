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
            'slug' => 'nullable|string|max:255|unique:product_category,slug',
            'description' => 'nullable|string',
            'banner_web' => 'nullable|image|max:2048',
            'banner_mobile' => 'nullable|image|max:2048',
            'sort_order' => 'nullable|integer|min:0',
            'status' => 'boolean',
            'has_warranty' => 'boolean',
            'parent_id' => 'nullable|exists:product_category,id',
        ]);
        
        if ($request->hasFile('banner_web')) {
            $validated['banner_web'] = $request->file('banner_web')->store('categories', 'public');
        }
        if ($request->hasFile('banner_mobile')) {
            $validated['banner_mobile'] = $request->file('banner_mobile')->store('categories', 'public');
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
            'slug' => 'nullable|string|max:255|unique:product_category,slug,' . $id,
            'description' => 'nullable|string',
            'banner_web' => 'nullable|image|max:2048',
            'banner_mobile' => 'nullable|image|max:2048',
            'sort_order' => 'nullable|integer|min:0',
            'status' => 'boolean',
            'has_warranty' => 'boolean',
            'parent_id' => 'nullable|exists:product_category,id',
        ]);

        if ($request->hasFile('banner_web')) {
            if ($category->banner_web) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($category->banner_web);
            }
            $validated['banner_web'] = $request->file('banner_web')->store('categories', 'public');
        }
        if ($request->hasFile('banner_mobile')) {
            if ($category->banner_mobile) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($category->banner_mobile);
            }
            $validated['banner_mobile'] = $request->file('banner_mobile')->store('categories', 'public');
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
        $category->delete();
        return response()->json([
            'success' => true,
            'status_code' => 204,
            'message' => 'Category deleted',
            'data' => null,
        ], 204);
    }
}
