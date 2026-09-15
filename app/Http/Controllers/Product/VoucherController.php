<?php

namespace App\Http\Controllers\Product;

use App\Http\Controllers\Controller;

use App\Models\Promo\Voucher;
use Illuminate\Http\Request;

class VoucherController extends Controller
{
    public function index(Request $request)
    {
        $query = Voucher::query()->withoutGlobalScope('active');

        if ($search = $request->query('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('code', 'ilike', "%{$search}%")
                  ->orWhere('title', 'ilike', "%{$search}%");
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
        $voucher = Voucher::withoutGlobalScope('active')
            ->with(['customers', 'categories'])
            ->findOrFail($id);
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

        return redirect()->route('vouchers.index')->with('success', 'Voucher created successfully');
    }

    public function destroy($id)
    {
        $voucher = Voucher::withoutGlobalScope('active')->findOrFail($id);
        $voucher->update(['deleted' => true]);
        return redirect()->route('vouchers.index')->with('success', 'Voucher deleted successfully');
    }
}
