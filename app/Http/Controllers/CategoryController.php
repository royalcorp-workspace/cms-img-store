<?php

namespace App\Http\Controllers;

use App\Models\Product\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index()
    {
        return view('pages.categories.index');
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
        return response()->json($category, 201);
    }

    public function edit($id)
    {
        $category = Category::findOrFail($id);
        return response()->json($category);
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
        return response()->json($category);
    }

    public function destroy($id)
    {
        $category = Category::findOrFail($id);
        $category->delete();
        return response()->json(null, 204);
    }
}
