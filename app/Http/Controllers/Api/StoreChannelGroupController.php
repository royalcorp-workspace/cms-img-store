<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\ApiController;
use App\Models\Store\StoreChannelGroup;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StoreChannelGroupController extends ApiController
{
    public function index(Request $request): JsonResponse
    {
        $query = StoreChannelGroup::query();

        if ($request->filled('search')) {
            $query->where('name', 'ilike', '%' . $request->search . '%');
        }

        $channelGroups = $query->paginate(15);
        return $this->successResponse($channelGroups);
    }

    public function show(string $id): JsonResponse
    {
        $channelGroup = StoreChannelGroup::find($id);
        if (!$channelGroup) {
            return $this->errorResponse('Store channel group not found', 404);
        }
        return $this->successResponse($channelGroup);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'code' => 'required|string|max:100|unique:store_channel_group,code',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'sort_order' => 'nullable|integer|min:0',
            'status' => 'boolean',
        ]);

        $validated['status'] = $request->boolean('status', true);
        $channelGroup = StoreChannelGroup::create($validated);

        return $this->successResponse($channelGroup, 'Store channel group created', 201);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $channelGroup = StoreChannelGroup::find($id);
        if (!$channelGroup) {
            return $this->errorResponse('Store channel group not found', 404);
        }

        $validated = $request->validate([
            'code' => 'required|string|max:100|unique:store_channel_group,code,' . $id,
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'sort_order' => 'nullable|integer|min:0',
            'status' => 'boolean',
        ]);

        $validated['status'] = $request->boolean('status', true);
        $channelGroup->update($validated);

        return $this->successResponse($channelGroup, 'Store channel group updated');
    }

    public function destroy(string $id): JsonResponse
    {
        $channelGroup = StoreChannelGroup::find($id);
        if (!$channelGroup) {
            return $this->errorResponse('Store channel group not found', 404);
        }
        $channelGroup->delete();

        return $this->successResponse(null, 'Store channel group deleted', 204);
    }
}
