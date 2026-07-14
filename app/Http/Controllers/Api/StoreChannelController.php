<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\ApiController;
use App\Models\Store\Store;
use App\Models\Store\StoreChannel;
use App\Models\Store\StoreChannelGroup;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StoreChannelController extends ApiController
{
    public function index(Request $request): JsonResponse
    {
        $query = StoreChannel::query()->with(['store', 'channelGroup']);

        if ($request->filled('search')) {
            $query->where('name', 'ilike', '%' . $request->search . '%');
        }

        if ($request->filled('store_id')) {
            $query->where('store_id', $request->store_id);
        }

        if ($request->filled('store_channel_group_id')) {
            $query->where('store_channel_group_id', $request->store_channel_group_id);
        }

        $channels = $query->paginate(15);
        return $this->successResponse($channels);
    }

    public function show(string $id): JsonResponse
    {
        $channel = StoreChannel::with(['store', 'channelGroup'])->find($id);
        if (!$channel) {
            return $this->errorResponse('Store channel not found', 404);
        }
        return $this->successResponse($channel);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'store_id' => 'required|string|exists:store,id',
            'store_channel_group_id' => 'required|string|exists:store_channel_group,id',
            'code' => 'required|string|max:100',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'sort_order' => 'nullable|integer|min:0',
            'status' => 'boolean',
        ]);

        $validated['status'] = $request->boolean('status', true);
        $channel = StoreChannel::create($validated);
        $channel->load(['store', 'channelGroup']);

        return $this->successResponse($channel, 'Store channel created', 201);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $channel = StoreChannel::find($id);
        if (!$channel) {
            return $this->errorResponse('Store channel not found', 404);
        }

        $validated = $request->validate([
            'store_id' => 'required|string|exists:store,id',
            'store_channel_group_id' => 'required|string|exists:store_channel_group,id',
            'code' => 'required|string|max:100',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'sort_order' => 'nullable|integer|min:0',
            'status' => 'boolean',
        ]);

        $validated['status'] = $request->boolean('status', true);
        $channel->update($validated);
        $channel->load(['store', 'channelGroup']);

        return $this->successResponse($channel, 'Store channel updated');
    }

    public function destroy(string $id): JsonResponse
    {
        $channel = StoreChannel::find($id);
        if (!$channel) {
            return $this->errorResponse('Store channel not found', 404);
        }
        $channel->delete();

        return $this->successResponse(null, 'Store channel deleted', 204);
    }
}
