<?php

namespace App\Http\Controllers\Api\Content;

use App\Http\Controllers\Api\ApiController;
use App\Models\Content\Faq;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FaqController extends ApiController
{
    public function index(Request $request): JsonResponse
    {
        $query = Faq::query()->where('is_published', true)->orderBy('sort_order');

        if ($request->filled('search')) {
            $query->where('question', 'like', '%' . $request->search . '%');
        }

        $faqs = $query->get();
        return $this->successResponse($faqs);
    }

    public function show(string $id): JsonResponse
    {
        $faq = Faq::where('is_published', true)->find($id);
        if (!$faq) {
            return $this->errorResponse('FAQ not found', 404);
        }
        return $this->successResponse($faq);
    }
}
