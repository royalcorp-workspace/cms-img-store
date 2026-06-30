<?php

namespace App\Http\Controllers;

use App\Models\Product\Product;
use App\Models\Product\Color;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::paginate(15);
        return view('pages.products.index', compact('products'));
    }

    public function create()
    {
        $product = new Product();
        return view('pages.products.create', compact('product'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:products,slug',
            'thumbnail' => 'nullable|string|max:255',
            'alt_text' => 'nullable|string|max:255',
            'short_description' => 'nullable|string|max:500',
            'description' => 'nullable|string',
            'base_price' => 'nullable|numeric|min:0',
            'segments' => 'nullable|array',
            'segments.*' => 'nullable|string|max:255',
            'best_seller' => 'boolean',
            'is_new' => 'boolean',
            'sort_order' => 'nullable|integer|min:0',
            'status' => 'boolean',
            'category_id' => 'nullable|string|exists:categories,id',
            'brand_id' => 'nullable|string|exists:brands,id',
            'colors' => 'nullable|array',
            'colors.*.color_name' => 'nullable|string|max:255',
            'colors.*.color_code' => 'nullable|string|max:255',
            'colors.*.status' => 'boolean',
            'variants' => 'nullable|array',
            'variants.*.sku' => 'nullable|string|max:255',
            'variants.*.variant_name' => 'nullable|string|max:255',
            'variants.*.width' => 'nullable|numeric|min:0',
            'variants.*.length' => 'nullable|numeric|min:0',
            'variants.*.height' => 'nullable|numeric|min:0',
            'variants.*.weight' => 'nullable|numeric|min:0',
            'variants.*.price' => 'nullable|numeric|min:0',
            'variants.*.stock_qty' => 'nullable|integer|min:0',
            'variants.*.min_order_qty' => 'nullable|integer|min:0',
            'variants.*.sort_order' => 'nullable|integer|min:0',
            'variants.*.status' => 'boolean',
        ]);

        $product = Product::create($validated);

        if (isset($validated['colors']) && is_array($validated['colors'])) {
            foreach ($validated['colors'] as $colorData) {
                Color::create(array_merge(['product_id' => $product->id], $colorData));
            }
        }

        if (isset($validated['variants']) && is_array($validated['variants'])) {
            foreach ($validated['variants'] as $variantData) {
                \App\Models\Product\Variant::create(array_merge(['product_id' => $product->id], $variantData));
            }
        }

        return redirect()->route('products.index')->with('success', 'Product created successfully');
    }

    public function show($id)
    {
        $product = Product::with('variants', 'colors')->findOrFail($id);
        return view('pages.products.show', compact('product'));
    }

    public function edit($id)
    {
        $product = Product::with('colors')->findOrFail($id);
        return view('pages.products.create', compact('product'));
    }

    public function update(Request $request, $id)
    {
        $product = Product::findOrFail($id);

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'slug' => 'nullable|string|max:255|unique:products,slug,' . $product->id,
            'thumbnail' => 'nullable|string|max:255',
            'alt_text' => 'nullable|string|max:255',
            'short_description' => 'nullable|string|max:500',
            'description' => 'nullable|string',
            'base_price' => 'nullable|numeric|min:0',
            'segments' => 'nullable|array',
            'segments.*' => 'nullable|string|max:255',
            'best_seller' => 'boolean',
            'is_new' => 'boolean',
            'sort_order' => 'nullable|integer|min:0',
            'status' => 'boolean',
            'category_id' => 'nullable|string|exists:categories,id',
            'brand_id' => 'nullable|string|exists:brands,id',
            'colors' => 'nullable|array',
            'colors.*.id' => 'nullable|string|exists:product_colors,id',
            'colors.*.color_name' => 'nullable|string|max:255',
            'colors.*.color_code' => 'nullable|string|max:255',
            'colors.*.status' => 'boolean',
            'variants' => 'nullable|array',
            'variants.*.id' => 'nullable|string|exists:product_variants,id',
            'variants.*.sku' => 'nullable|string|max:255',
            'variants.*.variant_name' => 'nullable|string|max:255',
            'variants.*.width' => 'nullable|numeric|min:0',
            'variants.*.length' => 'nullable|numeric|min:0',
            'variants.*.height' => 'nullable|numeric|min:0',
            'variants.*.weight' => 'nullable|numeric|min:0',
            'variants.*.price' => 'nullable|numeric|min:0',
            'variants.*.stock_qty' => 'nullable|integer|min:0',
            'variants.*.min_order_qty' => 'nullable|integer|min:0',
            'variants.*.sort_order' => 'nullable|integer|min:0',
            'variants.*.status' => 'boolean',
        ]);

        $product->update($validated);

        if (isset($validated['colors']) && is_array($validated['colors'])) {
            $submittedColorIds = [];
            foreach ($validated['colors'] as $colorData) {
                if (isset($colorData['id'])) {
                    $submittedColorIds[] = $colorData['id'];
                    $color = Color::find($colorData['id']);
                    if ($color) {
                        $color->update($colorData);
                    }
                } else {
                    Color::create(array_merge(['product_id' => $product->id], $colorData));
                }
            }

            if (!empty($submittedColorIds)) {
                $product->colors()->whereNotIn('id', $submittedColorIds)->delete();
            } else {
                $product->colors()->delete();
            }
        } else {
            $product->colors()->delete();
        }

        if (isset($validated['variants']) && is_array($validated['variants'])) {
            $submittedIds = [];
            foreach ($validated['variants'] as $variantData) {
                if (isset($variantData['id'])) {
                    $submittedIds[] = $variantData['id'];
                    $variant = \App\Models\Product\Variant::find($variantData['id']);
                    if ($variant) {
                        $variant->update($variantData);
                    }
                } else {
                    \App\Models\Product\Variant::create(array_merge(['product_id' => $product->id], $variantData));
                }
            }

            if (!empty($submittedIds)) {
                $product->variants()->whereNotIn('id', $submittedIds)->delete();
            } else {
                $product->variants()->delete();
            }
        } else {
            $product->variants()->delete();
        }

        return redirect()->route('products.index')->with('success', 'Product updated successfully');
    }

    public function destroy($id)
    {
        $product = Product::findOrFail($id);
        $product->delete();
        return redirect()->route('products.index');
    }
}
