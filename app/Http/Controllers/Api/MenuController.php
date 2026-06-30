<?php

namespace App\Http\Controllers\Api;

use App\Models\Menu;
use Illuminate\Http\JsonResponse;

class MenuController extends ApiController
{
    public function index(): JsonResponse
    {
        $menus = Menu::with('children')
            ->parents()
            ->orderBy('order')
            ->get();

        return $this->successResponse($menus);
    }

    public function show(string $id): JsonResponse
    {
        $menu = Menu::with('children')->find($id);

        if (!$menu) {
            return $this->errorResponse('Menu not found', 404);
        }

        return $this->successResponse($menu);
    }
}