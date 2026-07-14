<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\ApiController;
use App\Models\Store\Store;
use App\Models\Store\StoreChannel;
use App\Models\Store\StoreChannelStock;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StoreChannelStockController extends ApiController
{
    public function index(Request $request): JsonResponse
    {
        $query = StoreChannelStock::query()->with(['product', 'variant', 'store', 'channel']);

        if ($request->filled('store_id')) {
            $query->where('store_id', $request->store_id);
        }

        if ($request->filled('store_channel_id')) {
            $query->where('store_channel_id', $request->store_channel_id);
        }

        if ($request->filled('product_id')) {
            $query->where('product_id', $request->product_id);
        }

        if ($request->filled('product_variant_id')) {
            $query->where('product_variant_id', $request->product_variant_id);
        }

        $stocks = $query->paginate(15);
        return $this->successResponse($stocks);
    }

    public function show(string $id): JsonResponse
    {
        $stock = StoreChannelStock::with(['product', 'variant', 'store', 'channel'])->find($id);
        if (!$stock) {
            return $this->errorResponse('Store channel stock not found', 404);
        }
        return $this->successResponse($stock);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'product_id' => 'nullable|string|exists:products,id',
            'product_variant_id' => 'nullable|string|exists:product_variants,id',
            'store_id' => 'nullable|string|exists:store,id',
            'store_channel_id' => 'nullable|string|exists:store_channel,id',
            'incoming' => 'nullable|integer|min:0',
            'booked' => 'nullable|integer|min:0',
            'on_order' => 'nullable|integer|min:0',
            'outgoing' => 'nullable|integer|min:0',
            'quantity' => 'nullable|integer|min:0',
        ]);

        $validated['incoming'] = $validated['incoming'] ?? 0;
        $validated['booked'] = $validated['booked'] ?? 0;
        $validated['on_order'] = $validated['on_order'] ?? 0;
        $validated['outgoing'] = $validated['outgoing'] ?? 0;
        $validated['quantity'] = $validated['quantity'] ?? 0;
        $validated['creator'] = $this->getAuthenticatedUser()?->name ?? 'admin';
        $validated['editor'] = $this->getAuthenticatedUser()?->name ?? 'admin';

        $stock = StoreChannelStock::create($validated);
        $stock->load(['product', 'variant', 'store', 'channel']);

        return $this->successResponse($stock, 'Store channel stock created', 201);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $stock = StoreChannelStock::find($id);
        if (!$stock) {
            return $this->errorResponse('Store channel stock not found', 404);
        }

        $validated = $request->validate([
            'product_id' => 'nullable|string|exists:products,id',
            'product_variant_id' => 'nullable|string|exists:product_variants,id',
            'store_id' => 'nullable|string|exists:store,id',
            'store_channel_id' => 'nullable|string|exists:store_channel,id',
            'incoming' => 'nullable|integer|min:0',
            'booked' => 'nullable|integer|min:0',
            'on_order' => 'nullable|integer|min:0',
            'outgoing' => 'nullable|integer|min:0',
            'quantity' => 'nullable|integer|min:0',
        ]);

        $validated['editor'] = $this->getAuthenticatedUser()?->name ?? 'admin';
        $stock->update($validated);
        $stock->load(['product', 'variant', 'store', 'channel']);

        return $this->successResponse($stock, 'Store channel stock updated');
    }

    public function destroy(string $id): JsonResponse
    {
        $stock = StoreChannelStock::find($id);
        if (!$stock) {
            return $this->errorResponse('Store channel stock not found', 404);
        }
        $stock->delete();

        return $this->successResponse(null, 'Store channel stock deleted', 204);
    }
}
