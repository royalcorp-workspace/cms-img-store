<?php

namespace App\Http\Controllers\Api;

use App\Models\Product\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InventoryController extends ApiController
{
    public function index(): JsonResponse
    {
        $inventory = Product::select('id', 'name', 'created_at')
            ->with(['variants' => function ($q) {
                $q->select('id', 'product_id', 'variant_name', 'sku', 'stock_quantity');
            }])
            ->orderBy('created_at', 'desc')
            ->paginate(request('per_page', 15));
        return $this->successResponse($inventory);
    }

    public function store(Request $request): JsonResponse
    {
        $product = Product::create(array_merge($request->all(), ['price' => $request->price ?? 0]));
        return $this->successResponse($product, 'Inventory item created', 201);
    }
}