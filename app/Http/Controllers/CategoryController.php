<?php

namespace App\Http\Controllers;

use App\Models\Product\Category;
use App\Models\Product\Product;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index()
    {
        return view('pages.categories.index');
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
            ->where('status', true)
            ->where('deleted', false)
            ->with(['children' => function ($q) {
                $q->where('status', true)->orderBy('sort_order')->orderBy('name');
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
            'sort_order' => 'nullable|integer|min:0',
            'status' => 'boolean',
            'parent_id' => 'nullable|exists:product_category,id',
        ]);
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
            'sort_order' => 'nullable|integer|min:0',
            'status' => 'boolean',
            'parent_id' => 'nullable|exists:product_category,id',
        ]);
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
