<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\ApiController;
use App\Models\Store\StoreTier;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StoreTierController extends ApiController
{
    public function index(Request $request): JsonResponse
    {
        $query = StoreTier::query();

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $tiers = $query->paginate(15);
        return $this->successResponse($tiers);
    }

    public function show(string $id): JsonResponse
    {
        $tier = StoreTier::find($id);
        if (!$tier) {
            return $this->errorResponse('Store tier not found', 404);
        }
        return $this->successResponse($tier);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'code' => 'required|string|max:100|unique:store_tier,code',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'level' => 'required|integer|min:1',
            'credit_limit' => 'nullable|numeric|min:0',
            'sort_order' => 'nullable|integer|min:0',
            'status' => 'boolean',
        ]);

        $validated['status'] = $request->boolean('status', true);
        $validated['credit_limit'] = $validated['credit_limit'] ?? 0;

        $tier = StoreTier::create($validated);
        return $this->successResponse($tier, 'Store tier created', 201);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $tier = StoreTier::find($id);
        if (!$tier) {
            return $this->errorResponse('Store tier not found', 404);
        }

        $validated = $request->validate([
            'code' => 'required|string|max:100|unique:store_tier,code,' . $id,
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'level' => 'required|integer|min:1',
            'credit_limit' => 'nullable|numeric|min:0',
            'sort_order' => 'nullable|integer|min:0',
            'status' => 'boolean',
        ]);

        $validated['status'] = $request->boolean('status', true);
        $validated['credit_limit'] = $validated['credit_limit'] ?? 0;

        $tier->update($validated);
        return $this->successResponse($tier, 'Store tier updated');
    }

    public function destroy(string $id): JsonResponse
    {
        $tier = StoreTier::find($id);
        if (!$tier) {
            return $this->errorResponse('Store tier not found', 404);
        }
        $tier->delete();
        return $this->successResponse(null, 'Store tier deleted', 204);
    }
}
