<?php

namespace App\Http\Controllers\Api;

use App\Models\Product\Product;
use App\Models\Product\Image;
use App\Models\Product\Variant;
use App\Models\Product\Color;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProductController extends ApiController
{
    public function index(Request $request): JsonResponse
    {
        $query = Product::query()->with(['images', 'variants.priceProductSettings', 'colors', 'priceProductSettings']);

        if ($request->filled('search')) {
            $query->where('name', 'ilike', '%' . $request->search . '%');
        }

        if ($request->filled('status')) {
            $query->where('status', $request->boolean('status'));
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        $products = $query->paginate(15);
        return $this->successResponse($products);
    }

    public function store(Request $request): JsonResponse
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
            'images' => 'nullable|array',
            'images.*.alt_text' => 'nullable|string|max:255',
            'images.*.sort_order' => 'nullable|integer|min:0',
            'images.*.status' => 'boolean',
        ]);

        $product = Product::create($validated);

        if (isset($validated['colors']) && is_array($validated['colors'])) {
            foreach ($validated['colors'] as $colorData) {
                Color::create(array_merge(['product_id' => $product->id], $colorData));
            }
        }

        if (isset($validated['variants']) && is_array($validated['variants'])) {
            foreach ($validated['variants'] as $variantData) {
                Variant::create(array_merge(['product_id' => $product->id], $variantData));
            }
        }

        $product->load('images', 'variants.priceProductSettings', 'colors', 'priceProductSettings');

        return $this->successResponse($product, 'Product created', 201);
    }

    public function show(string $id): JsonResponse
    {
        $product = Product::where('slug', $id)->orWhere('id', $id)->first();
        if (!$product) {
            return $this->errorResponse('Product not found', 404);
        }
        $product->load('images', 'variants.priceProductSettings', 'colors', 'priceProductSettings');
        return $this->successResponse($product);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $product = Product::where('slug', $id)->orWhere('id', $id)->first();
        if (!$product) {
            return $this->errorResponse('Product not found', 404);
        }

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
                    $variant = Variant::find($variantData['id']);
                    if ($variant) {
                        $variant->update($variantData);
                    }
                } else {
                    Variant::create(array_merge(['product_id' => $product->id], $variantData));
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

        $product->load('images', 'variants.priceProductSettings', 'colors', 'priceProductSettings');

        return $this->successResponse($product, 'Product updated');
    }

    public function destroy(string $id): JsonResponse
    {
        $product = Product::where('slug', $id)->orWhere('id', $id)->first();
        if (!$product) {
            return $this->errorResponse('Product not found', 404);
        }
        $product->delete();
        return $this->successResponse(null, 'Product deleted', 204);
    }

    public function storeImage(Request $request, string $id): JsonResponse
    {
        $product = Product::where('slug', $id)->orWhere('id', $id)->first();
        if (!$product) {
            return $this->errorResponse('Product not found', 404);
        }
        $request->validate([
            'image' => 'required|file|image|max:2048',
            'alt_text' => 'nullable|string|max:255',
            'sort_order' => 'nullable|integer|min:0',
            'status' => 'boolean',
        ]);
        $path = $request->file('image')->store('product_images', 'public');
        $image = Image::create([
            'product_id' => $product->id,
            'image' => $path,
            'alt_text' => $request->alt_text,
            'sort_order' => $request->sort_order ?? 0,
            'status' => $request->status ?? true,
        ]);
        return $this->successResponse($image, 'Image uploaded', 201);
    }

    public function destroyImage(string $id): JsonResponse
    {
        $image = Image::find($id);
        if (!$image) {
            return $this->errorResponse('Image not found', 404);
        }
        if ($image->image && Storage::disk('public')->exists($image->image)) {
            Storage::disk('public')->delete($image->image);
        }
        $image->delete();
        return $this->successResponse(null, 'Image deleted', 204);
    }

    public function storeVariant(Request $request, string $id): JsonResponse
    {
        $product = Product::where('slug', $id)->orWhere('id', $id)->first();
        if (!$product) {
            return $this->errorResponse('Product not found', 404);
        }
        $request->validate([
            'sku' => 'nullable|string|max:255',
            'variant_name' => 'nullable|string|max:255',
            'width' => 'nullable|numeric|min:0',
            'length' => 'nullable|numeric|min:0',
            'height' => 'nullable|numeric|min:0',
            'weight' => 'nullable|numeric|min:0',
            'price' => 'nullable|numeric|min:0',
            'stock_qty' => 'nullable|integer|min:0',
            'min_order_qty' => 'nullable|integer|min:0',
            'sort_order' => 'nullable|integer|min:0',
            'status' => 'boolean',
        ]);
        $variant = Variant::create(array_merge(
            $request->only(['sku', 'variant_name', 'width', 'length', 'height', 'weight', 'price', 'stock_qty', 'min_order_qty', 'sort_order', 'status']),
            ['product_id' => $product->id]
        ));
        return $this->successResponse($variant, 'Variant created', 201);
    }

    public function updateVariant(Request $request, string $id): JsonResponse
    {
        $variant = Variant::find($id);
        if (!$variant) {
            return $this->errorResponse('Variant not found', 404);
        }
        $request->validate([
            'sku' => 'nullable|string|max:255',
            'variant_name' => 'nullable|string|max:255',
            'width' => 'nullable|numeric|min:0',
            'length' => 'nullable|numeric|min:0',
            'height' => 'nullable|numeric|min:0',
            'weight' => 'nullable|numeric|min:0',
            'price' => 'nullable|numeric|min:0',
            'stock_qty' => 'nullable|integer|min:0',
            'min_order_qty' => 'nullable|integer|min:0',
            'sort_order' => 'nullable|integer|min:0',
            'status' => 'boolean',
        ]);
        $variant->update($request->only(['sku', 'variant_name', 'width', 'length', 'height', 'weight', 'price', 'stock_qty', 'min_order_qty', 'sort_order', 'status']));
        return $this->successResponse($variant, 'Variant updated');
    }

    public function destroyVariant(string $id): JsonResponse
    {
        $variant = Variant::find($id);
        if (!$variant) {
            return $this->errorResponse('Variant not found', 404);
        }
        $variant->delete();
        return $this->successResponse(null, 'Variant deleted', 204);
    }

    public function storeColor(Request $request, string $id): JsonResponse
    {
        $product = Product::where('slug', $id)->orWhere('id', $id)->first();
        if (!$product) {
            return $this->errorResponse('Product not found', 404);
        }
        $request->validate([
            'color_name' => 'nullable|string|max:255',
            'color_code' => 'nullable|string|max:255',
            'status' => 'boolean',
        ]);
        $color = Color::create(array_merge(
            $request->only(['color_name', 'color_code', 'status']),
            ['product_id' => $product->id]
        ));
        return $this->successResponse($color, 'Color created', 201);
    }

    public function updateColor(Request $request, string $id): JsonResponse
    {
        $color = Color::find($id);
        if (!$color) {
            return $this->errorResponse('Color not found', 404);
        }
        $request->validate([
            'color_name' => 'nullable|string|max:255',
            'color_code' => 'nullable|string|max:255',
            'status' => 'boolean',
        ]);
        $color->update($request->only(['color_name', 'color_code', 'status']));
        return $this->successResponse($color, 'Color updated');
    }

    public function destroyColor(string $id): JsonResponse
    {
        $color = Color::find($id);
        if (!$color) {
            return $this->errorResponse('Color not found', 404);
        }
        $color->delete();
        return $this->successResponse(null, 'Color deleted', 204);
    }
}
