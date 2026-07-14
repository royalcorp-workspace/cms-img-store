<?php

namespace App\Http\Controllers\Api\Content;

use App\Http\Controllers\Api\ApiController;
use App\Models\Content\TermsAndCondition;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TermsAndConditionController extends ApiController
{
    public function index(Request $request): JsonResponse
    {
        $query = TermsAndCondition::query()->where('is_published', true)->orderBy('sort_order');

        if ($request->filled('search')) {
            $query->where('title', 'ilike', '%' . $request->search . '%');
        }

        $items = $query->get();
        return $this->successResponse($items);
    }

    public function show(string $id): JsonResponse
    {
        $item = TermsAndCondition::where('is_published', true)->find($id);
        if (!$item) {
            return $this->errorResponse('Terms & Conditions not found', 404);
        }
        return $this->successResponse($item);
    }
}
