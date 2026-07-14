<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\ApiController;
use App\Models\Store\Store;
use App\Models\Store\StoreGroup;
use App\Models\Store\StoreTier;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StoreController extends ApiController
{
    public function index(Request $request): JsonResponse
    {
        $query = Store::query()->with(['group', 'tier', 'owner']);

        if ($request->filled('search')) {
            $query->where('name', 'ilike', '%' . $request->search . '%');
        }

        if ($request->filled('store_group_id')) {
            $query->where('store_group_id', $request->store_group_id);
        }

        if ($request->filled('tier_id')) {
            $query->where('tier_id', $request->tier_id);
        }

        $stores = $query->paginate(15);
        return $this->successResponse($stores);
    }

    public function show(string $id): JsonResponse
    {
        $store = Store::with(['group', 'tier', 'owner', 'channels'])->find($id);
        if (!$store) {
            return $this->errorResponse('Store not found', 404);
        }
        return $this->successResponse($store);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'store_group_id' => 'required|string|exists:store_group,id',
            'tier_id' => 'nullable|string|exists:store_tier,id',
            'code' => 'required|string|max:100',
            'name' => 'required|string|max:255',
            'owner_user_id' => 'nullable|string|exists:user_admin,id',
            'credit_limit' => 'nullable|numeric|min:0',
            'outstanding_balance' => 'nullable|numeric|min:0',
            'address' => 'nullable|string',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'documents' => 'nullable|array',
            'payment_term' => 'nullable|integer|min:0',
            'sort_order' => 'nullable|integer|min:0',
            'status' => 'boolean',
        ]);

        $validated['status'] = $request->boolean('status', true);
        $validated['credit_limit'] = $validated['credit_limit'] ?? 0;
        $validated['outstanding_balance'] = $validated['outstanding_balance'] ?? 0;
        $validated['payment_term'] = $validated['payment_term'] ?? 0;

        $store = Store::create($validated);
        $store->load(['group', 'tier', 'owner']);

        return $this->successResponse($store, 'Store created', 201);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $store = Store::find($id);
        if (!$store) {
            return $this->errorResponse('Store not found', 404);
        }

        $validated = $request->validate([
            'store_group_id' => 'required|string|exists:store_group,id',
            'tier_id' => 'nullable|string|exists:store_tier,id',
            'code' => 'required|string|max:100',
            'name' => 'required|string|max:255',
            'owner_user_id' => 'nullable|string|exists:user_admin,id',
            'credit_limit' => 'nullable|numeric|min:0',
            'outstanding_balance' => 'nullable|numeric|min:0',
            'address' => 'nullable|string',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'documents' => 'nullable|array',
            'payment_term' => 'nullable|integer|min:0',
            'sort_order' => 'nullable|integer|min:0',
            'status' => 'boolean',
        ]);

        $validated['status'] = $request->boolean('status', true);
        $validated['credit_limit'] = $validated['credit_limit'] ?? 0;
        $validated['outstanding_balance'] = $validated['outstanding_balance'] ?? 0;
        $validated['payment_term'] = $validated['payment_term'] ?? 0;

        $store->update($validated);
        $store->load(['group', 'tier', 'owner']);

        return $this->successResponse($store, 'Store updated');
    }

    public function destroy(string $id): JsonResponse
    {
        $store = Store::find($id);
        if (!$store) {
            return $this->errorResponse('Store not found', 404);
        }
        $store->delete();
        return $this->successResponse(null, 'Store deleted', 204);
    }
}
