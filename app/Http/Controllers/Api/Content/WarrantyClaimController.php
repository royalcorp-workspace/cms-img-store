<?php

namespace App\Http\Controllers\Api\Content;

use App\Http\Controllers\Api\ApiController;
use App\Models\Content\WarrantyClaim;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WarrantyClaimController extends ApiController
{
    public function index(Request $request): JsonResponse
    {
        $query = WarrantyClaim::query()->where('is_published', true)->orderBy('sort_order');

        if ($request->filled('search')) {
            $query->where('title', 'ilike', '%' . $request->search . '%');
        }

        $items = $query->get();
        return $this->successResponse($items);
    }

    public function show(string $id): JsonResponse
    {
        $item = WarrantyClaim::where('is_published', true)->find($id);
        if (!$item) {
            return $this->errorResponse('Warranty Claim not found', 404);
        }
        return $this->successResponse($item);
    }
}
