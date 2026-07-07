<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\ApiController;
use App\Models\Store\StoreGroup;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StoreGroupController extends ApiController
{
    public function index(Request $request): JsonResponse
    {
        $query = StoreGroup::query();

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $storeGroups = $query->paginate(15);
        return $this->successResponse($storeGroups);
    }

    public function show(string $id): JsonResponse
    {
        $storeGroup = StoreGroup::find($id);
        if (!$storeGroup) {
            return $this->errorResponse('Store group not found', 404);
        }
        return $this->successResponse($storeGroup);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'code' => 'required|string|max:100|unique:store_group,code',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'sort_order' => 'nullable|integer|min:0',
            'status' => 'boolean',
        ]);

        $validated['status'] = $request->boolean('status', true);
        $storeGroup = StoreGroup::create($validated);
        return $this->successResponse($storeGroup, 'Store group created', 201);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $storeGroup = StoreGroup::find($id);
        if (!$storeGroup) {
            return $this->errorResponse('Store group not found', 404);
        }

        $validated = $request->validate([
            'code' => 'required|string|max:100|unique:store_group,code,' . $id,
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'sort_order' => 'nullable|integer|min:0',
            'status' => 'boolean',
        ]);

        $validated['status'] = $request->boolean('status', true);
        $storeGroup->update($validated);
        return $this->successResponse($storeGroup, 'Store group updated');
    }

    public function destroy(string $id): JsonResponse
    {
        $storeGroup = StoreGroup::find($id);
        if (!$storeGroup) {
            return $this->errorResponse('Store group not found', 404);
        }
        $storeGroup->delete();
        return $this->successResponse(null, 'Store group deleted', 204);
    }
}
