<?php

namespace App\Http\Controllers\Api;

use App\Models\Order\Order;
use Illuminate\Http\JsonResponse;

class OrderController extends ApiController
{
    public function index(): JsonResponse
    {
        $orders = Order::with('customer')->paginate(15);
        return $this->successResponse($orders);
    }

    public function show(string $order): JsonResponse
    {
        $order = Order::with('customer')->find($order);
        if (!$order) {
            return $this->errorResponse('Order not found', 404);
        }
        return $this->successResponse($order);
    }
}

   
