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

    public function pushJde(string $order, \App\Services\JdeService $jdeService): JsonResponse
    {
        $orderModel = \Illuminate\Support\Str::isUuid($order) ? Order::find($order) : null;
        if (!$orderModel) {
            $orderModel = Order::where('order_number', $order)->first();
        }

        if (!$orderModel) {
            return $this->errorResponse('Order not found', 404);
        }

        if (!$jdeService->isOrderEligible($orderModel)) {
            return $this->errorResponse('Pesanan belum memenuhi syarat push JDE (harus lunas, status >= 3, dan memiliki item).', 422);
        }

        $result = $jdeService->pushOrder($orderModel, 'webhook_payment_realtime');

        return response()->json($result, $result['success'] ? 200 : 422);
    }
}
