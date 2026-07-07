<?php

namespace App\Http\Controllers\Api;

use App\Models\Promo\Voucher;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class VoucherController extends ApiController
{
    public function index(): JsonResponse
    {
        $vouchers = Voucher::all();
        return $this->successResponse($vouchers);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'code' => 'required|string|max:255|unique:vouchers,code',
            'title' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|integer|in:1,2,3,4',
            'scope' => 'required|integer|in:1,2,3',
            'allow_stacking' => 'boolean',
            'value' => 'required|numeric|min:0',
            'min_purchase' => 'nullable|numeric|min:0',
            'max_discount' => 'nullable|numeric|min:0',
            'usage_limit' => 'nullable|integer|min:0',
            'usage_limit_per_user' => 'nullable|integer|min:0',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
            'valid_for_new_customer' => 'boolean',
            'is_active' => 'boolean',
            'product_ids' => 'nullable|array',
            'product_ids.*' => 'exists:products,id',
            'category_ids' => 'nullable|array',
            'category_ids.*' => 'exists:product_category,id',
        ]);

        $validated['allow_stacking'] = $request->boolean('allow_stacking');
        $validated['valid_for_new_customer'] = $request->boolean('valid_for_new_customer');
        $validated['is_active'] = $request->boolean('is_active');

        $productIds = $request->input('product_ids', []);
        $categoryIds = $request->input('category_ids', []);

        unset($validated['product_ids'], $validated['category_ids']);

        $voucher = Voucher::create($validated);

        if ((int)$voucher->type === 4) {
            if (!empty($productIds)) {
                $voucher->products()->attach($productIds);
            }
            if ((int)$voucher->scope === 3 && !empty($categoryIds)) {
                $voucher->categories()->attach($categoryIds);
            }
        } else {
            if ((int)$voucher->scope === 2 && !empty($productIds)) {
                $voucher->products()->attach($productIds);
            }
            if ((int)$voucher->scope === 3 && !empty($categoryIds)) {
                $voucher->categories()->attach($categoryIds);
            }
        }

        return $this->successResponse($voucher, 'Voucher created', 201);
    }

    public function show(string $id): JsonResponse
    {
        $voucher = Voucher::find($id);
        if (!$voucher) {
            return $this->errorResponse('Voucher not found', 404);
        }
        return $this->successResponse($voucher);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $voucher = Voucher::find($id);
        if (!$voucher) {
            return $this->errorResponse('Voucher not found', 404);
        }
        $voucher->update($request->all());
        return $this->successResponse($voucher, 'Voucher updated');
    }

    public function destroy(string $id): JsonResponse
    {
        $voucher = Voucher::find($id);
        if (!$voucher) {
            return $this->errorResponse('Voucher not found', 404);
        }
        $voucher->delete();
        return $this->successResponse(null, 'Voucher deleted', 204);
    }
}