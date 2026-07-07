<?php

namespace App\Http\Controllers;

use App\Models\Promo\Voucher;
use Illuminate\Http\Request;

class VoucherController extends Controller
{
    public function index(Request $request)
    {
        $query = Voucher::query()->withoutGlobalScope('active');

        if ($search = $request->query('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                  ->orWhere('title', 'like', "%{$search}%");
            });
        }

        if ($request->has('status')) {
            $status = $request->query('status');
            if ($status === 'active') {
                $query->where('is_active', true)->where('deleted', false);
            } elseif ($status === 'inactive') {
                $query->where(function ($q) {
                    $q->where('is_active', false)->orWhere('deleted', true);
                });
            } elseif ($status === 'expired') {
                $query->where('end_date', '<', now());
            }
        }

        $vouchers = $query->orderByDesc('created_at')->paginate(15)->appends($request->query());

        $stats = [
            'active' => Voucher::active()->count(),
            'total_redemptions' => Voucher::active()->sum('used_count'),
            'expiring_soon' => Voucher::active()->whereBetween('end_date', [now(), now()->addDays(7)])->count(),
            'total' => $vouchers->total(),
        ];

        return view('pages.vouchers.index', compact('vouchers', 'stats'));
    }

    public function create()
    {
        return view('pages.vouchers.create');
    }

    public function edit($id)
    {
        $voucher = Voucher::withoutGlobalScope('active')->findOrFail($id);
        return view('pages.vouchers.edit', compact('voucher'));
    }

    public function update(Request $request, $id)
    {
        $voucher = Voucher::withoutGlobalScope('active')->findOrFail($id);

        $validated = $request->validate([
            'code' => 'required|string|max:255|unique:vouchers,code,' . $id,
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

        $voucher->update($validated);

        if ((int)$voucher->type === 4) {
            $voucher->products()->sync($productIds);
            if ((int)$voucher->scope === 3) {
                $voucher->categories()->sync($categoryIds);
            } else {
                $voucher->categories()->detach();
            }
        } else {
            if ((int)$voucher->scope === 2) {
                $voucher->products()->sync($productIds);
                $voucher->categories()->detach();
            } elseif ((int)$voucher->scope === 3) {
                $voucher->categories()->sync($categoryIds);
                $voucher->products()->detach();
            } else {
                $voucher->products()->detach();
                $voucher->categories()->detach();
            }
        }

        return redirect()->route('vouchers.index')->with('success', 'Voucher updated successfully');
    }

    public function store(Request $request)
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

        return redirect()->route('vouchers.index')->with('success', 'Voucher created successfully');
    }

    public function destroy($id)
    {
        $voucher = Voucher::withoutGlobalScope('active')->findOrFail($id);
        $voucher->update(['deleted' => true]);
        return redirect()->route('vouchers.index')->with('success', 'Voucher deleted successfully');
    }
}
