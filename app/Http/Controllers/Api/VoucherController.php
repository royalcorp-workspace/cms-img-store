<?php

namespace App\Http\Controllers\Api;

use App\Models\Promo\Voucher;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class VoucherController extends ApiController
{
    public function index(Request $request): JsonResponse
    {
        $query = Voucher::with(['customers', 'categories']);
        if ($request->has('show_on_web')) {
            $query->where('show_on_web', $request->boolean('show_on_web'));
        }
        $vouchers = $query->get();
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
            'show_on_web' => 'boolean',
            'value' => 'required|numeric|min:0',
            'min_purchase' => 'nullable|numeric|min:0',
            'max_discount' => 'nullable|numeric|min:0',
            'usage_limit' => 'nullable|integer|min:0',
            'usage_limit_per_user' => 'nullable|integer|min:0',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
            'valid_for_new_customer' => 'boolean',
            'is_active' => 'boolean',
            'customer_ids' => 'nullable|array',
            'customer_ids.*' => 'exists:customers,id',
            'category_ids' => 'nullable|array',
            'category_ids.*' => 'exists:product_category,id',
        ]);

        $validated['allow_stacking'] = ((int) $request->input('type') === 3) && $request->boolean('allow_stacking');
        $validated['show_on_web'] = $request->boolean('show_on_web');
        $validated['valid_for_new_customer'] = $request->boolean('valid_for_new_customer');
        $validated['is_active'] = $request->boolean('is_active');

        $customerIds = $request->input('customer_ids', []);
        $categoryIds = $request->input('category_ids', []);

        unset($validated['customer_ids'], $validated['category_ids']);

        $voucher = Voucher::create($validated);

        if ((int)$voucher->scope === 2 && !empty($customerIds)) {
            $syncCustomers = [];
            foreach ($customerIds as $cId) {
                $syncCustomers[$cId] = ['id' => (string) \Illuminate\Support\Str::uuid()];
            }
            $voucher->customers()->attach($syncCustomers);
        } elseif ((int)$voucher->scope === 3 && !empty($categoryIds)) {
            $syncCats = [];
            foreach ($categoryIds as $catId) {
                $syncCats[$catId] = ['id' => (string) \Illuminate\Support\Str::uuid()];
            }
            $voucher->categories()->attach($syncCats);
        }

        return $this->successResponse($voucher->load(['customers', 'categories']), 'Voucher created', 201);
    }

    public function show(string $id): JsonResponse
    {
        $voucher = Voucher::with(['customers', 'categories'])->find($id);
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

        $validated = $request->validate([
            'code' => 'required|string|max:255|unique:vouchers,code,' . $id,
            'title' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|integer|in:1,2,3,4',
            'scope' => 'required|integer|in:1,2,3',
            'allow_stacking' => 'boolean',
            'show_on_web' => 'boolean',
            'value' => 'required|numeric|min:0',
            'min_purchase' => 'nullable|numeric|min:0',
            'max_discount' => 'nullable|numeric|min:0',
            'usage_limit' => 'nullable|integer|min:0',
            'usage_limit_per_user' => 'nullable|integer|min:0',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
            'valid_for_new_customer' => 'boolean',
            'is_active' => 'boolean',
            'customer_ids' => 'nullable|array',
            'customer_ids.*' => 'exists:customers,id',
            'category_ids' => 'nullable|array',
            'category_ids.*' => 'exists:product_category,id',
        ]);

        $validated['allow_stacking'] = ((int) $request->input('type') === 3) && $request->boolean('allow_stacking');
        $validated['show_on_web'] = $request->boolean('show_on_web');
        $validated['valid_for_new_customer'] = $request->boolean('valid_for_new_customer');
        $validated['is_active'] = $request->boolean('is_active');

        $customerIds = $request->input('customer_ids', []);
        $categoryIds = $request->input('category_ids', []);

        unset($validated['customer_ids'], $validated['category_ids']);

        $voucher->update($validated);

        if ((int)$voucher->scope === 2) {
            $syncCustomers = [];
            foreach ($customerIds as $cId) {
                $syncCustomers[$cId] = ['id' => (string) \Illuminate\Support\Str::uuid()];
            }
            $voucher->customers()->sync($syncCustomers);
            $voucher->categories()->detach();
        } elseif ((int)$voucher->scope === 3) {
            $syncCats = [];
            foreach ($categoryIds as $catId) {
                $syncCats[$catId] = ['id' => (string) \Illuminate\Support\Str::uuid()];
            }
            $voucher->categories()->sync($syncCats);
            $voucher->customers()->detach();
        } else {
            $voucher->customers()->detach();
            $voucher->categories()->detach();
        }

        return $this->successResponse($voucher->load(['customers', 'categories']), 'Voucher updated');
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