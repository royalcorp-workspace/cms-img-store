<?php

namespace App\Http\Controllers\Api\Content;

use App\Http\Controllers\Api\ApiController;
use App\Models\Content\PrivacyPolicy;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PrivacyPolicyController extends ApiController
{
    public function index(Request $request): JsonResponse
    {
        $query = PrivacyPolicy::query()->where('is_published', true)->orderBy('sort_order');

        if ($request->filled('search')) {
            $query->where('title', 'like', '%' . $request->search . '%');
        }

        $items = $query->get();
        return $this->successResponse($items);
    }

    public function show(string $id): JsonResponse
    {
        $item = PrivacyPolicy::where('is_published', true)->find($id);
        if (!$item) {
            return $this->errorResponse('Privacy Policy not found', 404);
        }
        return $this->successResponse($item);
    }
}
