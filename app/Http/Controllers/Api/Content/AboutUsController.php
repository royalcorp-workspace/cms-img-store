<?php

namespace App\Http\Controllers\Api\Content;

use App\Http\Controllers\Api\ApiController;
use App\Models\Content\AboutUs;
use Illuminate\Http\JsonResponse;

class AboutUsController extends ApiController
{
    public function index(): JsonResponse
    {
        $about = AboutUs::where('is_active', true)->first();
        if (!$about) {
            return $this->errorResponse('About Us not found', 404);
        }
        return $this->successResponse($about);
    }
}
