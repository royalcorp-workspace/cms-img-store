<?php

namespace App\Http\Controllers\Api;

use App\Models\Buffer\Buffer;
use App\Models\Buffer\BufferItem;
use App\Models\Customer\Customer;
use App\Models\Order\Order;
use App\Models\Order\OrderItem;
use App\Models\Product\Product;
use App\Models\Product\Variant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BufferController extends ApiController
{
    public function index(Request $request): JsonResponse
    {
        $userId = $request->get('auth_user_id');
        $sessionId = $request->header('X-Session-ID');

        $query = Buffer::with(['items.product', 'items.variant', 'customer']);

        if ($userId) {
            $query->where(function ($q) use ($userId, $sessionId) {
                $q->where('customer_id', $userId)
                  ->orWhere('session_id', $sessionId);
            });
        } elseif ($sessionId) {
            $query->where('session_id', $sessionId);
        }

        $buffers = $query->get();
        return $this->successResponse($buffers);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'customer_id' => 'nullable|exists:customers,id',
            'session_id' => 'nullable|string|max:100',
            'customer_name' => 'nullable|string|max:255',
            'customer_email' => 'nullable|email|max:255',
            'customer_phone' => 'nullable|string|max:50',
            'meta' => 'nullable|array',
        ]);

        $validated['customer_id'] = $validated['customer_id'] ?? null;
        $validated['session_id'] = $validated['session_id'] ?? $request->header('X-Session-ID');
        $validated['creator'] = $request->get('auth_user_id');
        $validated['editor'] = $request->get('auth_user_id');

        $buffer = Buffer::create($validated);
        return $this->successResponse($buffer, 'Buffer created', 201);
    }

    public function show(string $id): JsonResponse
    {
        $buffer = Buffer::with(['items.product', 'items.variant', 'customer'])->find($id);
        if (!$buffer) {
            return $this->errorResponse('Buffer not found', 404);
        }
        return $this->successResponse($buffer);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $buffer = Buffer::find($id);
        if (!$buffer) {
            return $this->errorResponse('Buffer not found', 404);
        }

        $validated = $request->validate([
            'customer_id' => 'nullable|exists:customers,id',
            'customer_name' => 'nullable|string|max:255',
            'customer_email' => 'nullable|email|max:255',
            'customer_phone' => 'nullable|string|max:50',
            'subtotal' => 'nullable|numeric|min:0',
            'tax' => 'nullable|numeric|min:0',
            'discount' => 'nullable|numeric|min:0',
            'total' => 'nullable|numeric|min:0',
            'meta' => 'nullable|array',
        ]);

        $validated['editor'] = $request->get('auth_user_id');
        $buffer->update($validated);

        return $this->successResponse($buffer, 'Buffer updated');
    }

    public function destroy(string $id): JsonResponse
    {
        $buffer = Buffer::find($id);
        if (!$buffer) {
            return $this->errorResponse('Buffer not found', 404);
        }

        $buffer->delete();

        return $this->successResponse(null, 'Buffer deleted', 204);
    }

    public function addItem(Request $request, string $id): JsonResponse
    {
        $buffer = Buffer::find($id);
        if (!$buffer) {
            return $this->errorResponse('Buffer not found', 404);
        }

        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'product_variant_id' => 'nullable|exists:product_variants,id',
            'name' => 'required|string|max:255',
            'quantity' => 'required|integer|min:1',
            'unit_price' => 'required|numeric|min:0',
            'total' => 'required|numeric|min:0',
            'discount_nominal' => 'nullable|numeric|min:0',
            'discount_percent' => 'nullable|numeric|min:0|max:100',
            'item_notes' => 'nullable|string',
            'meta' => 'nullable|array',
        ]);

        $validated['buffer_id'] = $id;

        $item = BufferItem::create($validated);

        $this->recalculateBuffer($buffer);

        return $this->successResponse($item->load('product', 'variant'), 'Item added to buffer', 201);
    }

    public function updateItem(Request $request, string $id, string $itemId): JsonResponse
    {
        $buffer = Buffer::find($id);
        if (!$buffer) {
            return $this->errorResponse('Buffer not found', 404);
        }

        $item = BufferItem::where('buffer_id', $id)->find($itemId);
        if (!$item) {
            return $this->errorResponse('Buffer item not found', 404);
        }

        $validated = $request->validate([
            'quantity' => 'nullable|integer|min:1',
            'unit_price' => 'nullable|numeric|min:0',
            'total' => 'nullable|numeric|min:0',
            'discount_nominal' => 'nullable|numeric|min:0',
            'discount_percent' => 'nullable|numeric|min:0|max:100',
            'item_notes' => 'nullable|string',
            'meta' => 'nullable|array',
        ]);

        $item->update($validated);

        $this->recalculateBuffer($buffer);

        return $this->successResponse($item->load('product', 'variant'), 'Buffer item updated');
    }

    public function destroyItem(string $id, string $itemId): JsonResponse
    {
        $buffer = Buffer::find($id);
        if (!$buffer) {
            return $this->errorResponse('Buffer not found', 404);
        }

        $item = BufferItem::where('buffer_id', $id)->find($itemId);
        if (!$item) {
            return $this->errorResponse('Buffer item not found', 404);
        }

        $item->delete();

        $this->recalculateBuffer($buffer);

        return $this->successResponse(null, 'Buffer item deleted', 204);
    }

    public function checkout(Request $request, string $id): JsonResponse
    {
        $buffer = Buffer::with(['items.product', 'items.variant', 'customer'])->find($id);
        if (!$buffer) {
            return $this->errorResponse('Buffer not found', 404);
        }

        if ($buffer->items->isEmpty()) {
            return $this->errorResponse('Buffer is empty', 400);
        }

        $validated = $request->validate([
            'payment_method' => 'nullable|string|max:100',
            'payment_status' => 'nullable|integer|min:0|max:4',
            'notes' => 'nullable|string',
            'meta' => 'nullable|array',
        ]);

        $order = Order::create([
            'customer_id' => $buffer->customer_id,
            'status' => Order::STATUS_DRAFT,
            'payment_method' => $validated['payment_method'] ?? null,
            'payment_status' => $validated['payment_status'] ?? Order::PAYMENT_UNPAID,
            'subtotal' => $buffer->subtotal,
            'tax' => $buffer->tax,
            'discount' => $buffer->discount,
            'total' => $buffer->total,
            'notes' => $validated['notes'] ?? null,
            'meta' => $validated['meta'] ?? $buffer->meta,
            'creator' => $request->get('auth_user_id'),
            'editor' => $request->get('auth_user_id'),
            'voucher_id' => null,
            'transaction_fee' => 0,
        ]);

        foreach ($buffer->items as $item) {
            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $item->product_id,
                'product_variant_id' => $item->product_variant_id,
                'name' => $item->name,
                'quantity' => $item->quantity,
                'unit_price' => $item->unit_price,
                'total' => $item->total,
                'discount_nominal' => $item->discount_nominal,
                'discount_percent' => $item->discount_percent,
                'item_notes' => $item->item_notes,
                'meta' => $item->meta,
            ]);
        }

        $buffer->delete();

        return $this->successResponse($order->load('items'), 'Order created from buffer', 201);
    }

    private function recalculateBuffer(Buffer $buffer): void
    {
        $items = $buffer->items()->get();
        $subtotal = $items->sum('total');
        $discount = $items->sum(function ($item) {
            return $item->discount_nominal + ($item->unit_price * $item->quantity * $item->discount_percent / 100);
        });
        $tax = 0;
        $total = $subtotal - $discount + $tax;

        $buffer->update([
            'subtotal' => $subtotal,
            'discount' => $discount,
            'tax' => $tax,
            'total' => $total,
        ]);
    }
}
