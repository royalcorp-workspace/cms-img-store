<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\ApiController;
use App\Models\Promo\StorePricing;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PriceProductSettingStoreController extends ApiController
{
    public function index(Request $request): JsonResponse
    {
        $query = StorePricing::query()->with(['product', 'variant', 'store']);

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->whereHas('store', function ($sq) use ($request) {
                    $sq->where('name', 'like', '%' . $request->search . '%')->orWhere('code', 'like', '%' . $request->search . '%');
                })
                ->orWhereHas('product', function ($pq) use ($request) {
                    $pq->where('name', 'like', '%' . $request->search . '%');
                })
                ->orWhereHas('variant', function ($vq) use ($request) {
                    $vq->where('variant_name', 'like', '%' . $request->search . '%');
                });
            });
        }

        if ($request->filled('store_id')) {
            $query->where('store_id', $request->store_id);
        }

        if ($request->filled('product_id')) {
            $query->where('product_id', $request->product_id);
        }

        if ($request->filled('variant_id')) {
            $query->where('variant_id', $request->variant_id);
        }

        $pricings = $query->paginate(15);
        return $this->successResponse($pricings);
    }

    public function show(string $id): JsonResponse
    {
        $pricing = StorePricing::with(['product', 'variant', 'store'])->find($id);
        if (!$pricing) {
            return $this->errorResponse('Store pricing not found', 404);
        }
        return $this->successResponse($pricing);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'store_id' => 'required|string|exists:store,id',
            'product_id' => 'nullable|string|exists:products,id',
            'variant_id' => 'nullable|string|exists:product_variants,id',
            'adjustments' => 'nullable|array',
            'adjustments.*.adjustment_name' => 'nullable|string|max:255',
            'adjustments.*.adjustment_name_desc' => 'nullable|string|max:255',
            'adjustments.*.adjustment_amount' => 'nullable|numeric',
        ]);

        if (empty($validated['product_id']) && empty($validated['variant_id'])) {
            return $this->errorResponse('Product or variant is required', 422);
        }

        $pricing = StorePricing::create($validated);
        $pricing->load(['product', 'variant', 'store']);

        return $this->successResponse($pricing, 'Store pricing created', 201);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $pricing = StorePricing::find($id);
        if (!$pricing) {
            return $this->errorResponse('Store pricing not found', 404);
        }

        $validated = $request->validate([
            'store_id' => 'required|string|exists:store,id',
            'product_id' => 'nullable|string|exists:products,id',
            'variant_id' => 'nullable|string|exists:product_variants,id',
            'adjustments' => 'nullable|array',
            'adjustments.*.adjustment_name' => 'nullable|string|max:255',
            'adjustments.*.adjustment_name_desc' => 'nullable|string|max:255',
            'adjustments.*.adjustment_amount' => 'nullable|numeric',
        ]);

        if (empty($validated['product_id']) && empty($validated['variant_id'])) {
            return $this->errorResponse('Product or variant is required', 422);
        }

        $pricing->update($validated);
        $pricing->load(['product', 'variant', 'store']);

        return $this->successResponse($pricing, 'Store pricing updated');
    }

    public function destroy(string $id): JsonResponse
    {
        $pricing = StorePricing::find($id);
        if (!$pricing) {
            return $this->errorResponse('Store pricing not found', 404);
        }
        $pricing->delete();

        return $this->successResponse(null, 'Store pricing deleted', 204);
    }
}
