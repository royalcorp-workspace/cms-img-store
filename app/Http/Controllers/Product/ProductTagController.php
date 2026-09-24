<?php

namespace App\Http\Controllers\Product;

use App\Http\Controllers\Controller;
use App\Models\Product\ProductTag;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ProductTagController extends Controller
{
    public function index(Request $request)
    {
        $query = ProductTag::where('deleted', false)->withCount('products');

        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'ilike', "%{$search}%")
                  ->orWhere('slug', 'ilike', "%{$search}%");
            });
        }

        $tags = $query->orderBy('sort_order', 'asc')->orderBy('name', 'asc')->paginate(20);

        return view('pages.product-tags.index', compact('tags'));
    }

    public function create()
    {
        return view('pages.product-tags.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100|unique:product_tags,name,NULL,id,deleted,false',
            'sort_order' => 'nullable|integer',
        ]);

        $slug = Str::slug($request->name);
        // Ensure slug uniqueness
        $originalSlug = $slug;
        $counter = 1;
        while (ProductTag::where('slug', $slug)->where('deleted', false)->exists()) {
            $slug = "{$originalSlug}-{$counter}";
            $counter++;
        }

        ProductTag::create([
            'name' => $request->name,
            'slug' => $slug,
            'sort_order' => $request->input('sort_order', 0) ?? 0,
            'creator' => auth()->user()->name ?? 'Admin',
            'deleted' => false,
        ]);

        return redirect()->route('tags.index')->with('success', 'Product Tag berhasil ditambahkan.');
    }

    public function edit($id)
    {
        $tag = ProductTag::where('deleted', false)->findOrFail($id);
        return view('pages.product-tags.edit', compact('tag'));
    }

    public function update(Request $request, $id)
    {
        $tag = ProductTag::where('deleted', false)->findOrFail($id);

        $request->validate([
            'name' => "required|string|max:100|unique:product_tags,name,{$id},id,deleted,false",
            'sort_order' => 'nullable|integer',
        ]);

        $slug = Str::slug($request->name);
        $originalSlug = $slug;
        $counter = 1;
        while (ProductTag::where('slug', $slug)->where('id', '!=', $id)->where('deleted', false)->exists()) {
            $slug = "{$originalSlug}-{$counter}";
            $counter++;
        }

        $tag->update([
            'name' => $request->name,
            'slug' => $slug,
            'sort_order' => $request->input('sort_order', 0) ?? 0,
            'editor' => auth()->user()->name ?? 'Admin',
        ]);

        return redirect()->route('tags.index')->with('success', 'Product Tag berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $tag = ProductTag::where('deleted', false)->findOrFail($id);
        $tag->update(['deleted' => true]);

        return redirect()->route('tags.index')->with('success', 'Product Tag berhasil dihapus.');
    }
}
