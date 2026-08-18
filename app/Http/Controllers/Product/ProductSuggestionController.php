<?php

namespace App\Http\Controllers\Product;

use App\Http\Controllers\Controller;
use App\Models\Product\Product;
use Illuminate\Http\Request;

class ProductSuggestionController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::withCount('suggestedProducts');

        if ($search = $request->query('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'ilike', "%{$search}%")
                  ->orWhere('code', 'ilike', "%{$search}%");
            });
        }

        $products = $query->orderBy('name')->paginate(15)->appends($request->query());

        return view('pages.products.suggestions.index', compact('products'));
    }

    public function edit($id)
    {
        $product = Product::with('suggestedProducts')->findOrFail($id);
        $allProducts = Product::where('id', '!=', $id)->orderBy('name')->get();

        return view('pages.products.suggestions.edit', compact('product', 'allProducts'));
    }

    public function update(Request $request, $id)
    {
        $product = Product::findOrFail($id);

        $validated = $request->validate([
            'suggested_products' => 'nullable|array',
            'suggested_products.*' => 'exists:products,id',
        ]);

        if (isset($validated['suggested_products']) && is_array($validated['suggested_products'])) {
            $syncData = [];
            foreach ($validated['suggested_products'] as $index => $sId) {
                $syncData[$sId] = ['sort_order' => $index + 1];
            }
            $product->suggestedProducts()->sync($syncData);
        } else {
            $product->suggestedProducts()->sync([]);
        }

        return redirect()->route('product-suggestions.index')->with('success', 'Product suggestions updated successfully');
    }
}
