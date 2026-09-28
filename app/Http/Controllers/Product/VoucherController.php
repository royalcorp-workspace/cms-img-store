<?php

namespace App\Http\Controllers\Product;

use App\Http\Controllers\Controller;
use App\Models\Customer\Customer;
use App\Models\Customer\CustomerGroup;
use App\Models\Product\Brand;
use App\Models\Product\Category;
use App\Models\Product\Product;
use App\Models\Promo\Voucher;
use App\Models\Store\Store;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class VoucherController extends Controller
{
    public function index(Request $request)
    {
        $query = Voucher::query()->withoutGlobalScope('active')->with(['store']);

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
        $stores = Store::orderBy('name')->get();
        $customerGroups = CustomerGroup::withCount('members')->where('deleted', false)->orderBy('name')->get();
        $products = Product::with('brand:id,name')->where('deleted', false)->orderBy('name')->get(['id', 'name', 'brand_id']);
        $brands = Brand::where('deleted', false)->orderBy('name')->get(['id', 'name']);
        $categories = Category::where('deleted', false)->orderBy('name')->get(['id', 'name']);
        $customers = Customer::orderBy('name')->get();

        return view('pages.vouchers.create', compact('stores', 'customerGroups', 'products', 'brands', 'categories', 'customers'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:255|unique:vouchers,code',
            'title' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|integer|in:1,2,3,4',
            'scope' => 'required|integer|in:1,2,3,4,5,6,7',
            'visibility' => 'required|string|in:public,claimable,hidden',
            'store_id' => 'nullable|integer|exists:stores,id',
            'require_follow' => 'boolean',
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
            'product_ids' => 'nullable|array',
            'product_ids.*' => 'exists:products,id',
            'brand_ids' => 'nullable|array',
            'brand_ids.*' => 'exists:brands,id',
            'customer_group_ids' => 'nullable|array',
            'customer_group_ids.*' => 'exists:customer_groups,id',
        ]);

        $validated['allow_stacking'] = ((int) $request->input('type') === 3) && $request->boolean('allow_stacking');
        $validated['show_on_web'] = $request->boolean('show_on_web');
        $validated['require_follow'] = $request->boolean('require_follow');
        $validated['valid_for_new_customer'] = $request->boolean('valid_for_new_customer');
        $validated['is_active'] = $request->boolean('is_active');

        unset(
            $validated['customer_ids'],
            $validated['category_ids'],
            $validated['product_ids'],
            $validated['brand_ids'],
            $validated['customer_group_ids']
        );

        $voucher = Voucher::create($validated);

        $this->syncRelations($voucher, $request);

        return redirect()->route('vouchers.index')->with('success', 'Voucher created successfully');
    }

    public function edit($id)
    {
        $voucher = Voucher::withoutGlobalScope('active')
            ->with(['customers', 'categories', 'products', 'brands', 'customerGroups', 'store'])
            ->findOrFail($id);

        $stores = Store::orderBy('name')->get();
        $customerGroups = CustomerGroup::withCount('members')->where('deleted', false)->orderBy('name')->get();
        $products = Product::with('brand:id,name')->where('deleted', false)->orderBy('name')->get(['id', 'name', 'brand_id']);
        $brands = Brand::where('deleted', false)->orderBy('name')->get(['id', 'name']);
        $categories = Category::where('deleted', false)->orderBy('name')->get(['id', 'name']);
        $customers = Customer::orderBy('name')->get();

        return view('pages.vouchers.edit', compact('voucher', 'stores', 'customerGroups', 'products', 'brands', 'categories', 'customers'));
    }

    public function update(Request $request, $id)
    {
        $voucher = Voucher::withoutGlobalScope('active')->findOrFail($id);

        $validated = $request->validate([
            'code' => 'required|string|max:255|unique:vouchers,code,' . $id,
            'title' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|integer|in:1,2,3,4',
            'scope' => 'required|integer|in:1,2,3,4,5,6,7',
            'visibility' => 'required|string|in:public,claimable,hidden',
            'store_id' => 'nullable|integer|exists:stores,id',
            'require_follow' => 'boolean',
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
            'product_ids' => 'nullable|array',
            'product_ids.*' => 'exists:products,id',
            'brand_ids' => 'nullable|array',
            'brand_ids.*' => 'exists:brands,id',
            'customer_group_ids' => 'nullable|array',
            'customer_group_ids.*' => 'exists:customer_groups,id',
        ]);

        $validated['allow_stacking'] = ((int) $request->input('type') === 3) && $request->boolean('allow_stacking');
        $validated['show_on_web'] = $request->boolean('show_on_web');
        $validated['require_follow'] = $request->boolean('require_follow');
        $validated['valid_for_new_customer'] = $request->boolean('valid_for_new_customer');
        $validated['is_active'] = $request->boolean('is_active');

        unset(
            $validated['customer_ids'],
            $validated['category_ids'],
            $validated['product_ids'],
            $validated['brand_ids'],
            $validated['customer_group_ids']
        );

        $voucher->update($validated);

        $this->syncRelations($voucher, $request);

        return redirect()->route('vouchers.index')->with('success', 'Voucher updated successfully');
    }

    public function destroy($id)
    {
        $voucher = Voucher::withoutGlobalScope('active')->findOrFail($id);
        $voucher->update(['deleted' => true]);
        return redirect()->route('vouchers.index')->with('success', 'Voucher deleted successfully');
    }

    protected function syncRelations(Voucher $voucher, Request $request): void
    {
        $scope = (int) $voucher->scope;

        $mapWithUuid = function (array $ids) {
            $data = [];
            foreach ($ids as $id) {
                $data[$id] = ['id' => (string) Str::uuid()];
            }
            return $data;
        };

        // Customer (scope 2)
        if ($scope === 2) {
            $voucher->customers()->sync($mapWithUuid($request->input('customer_ids', [])));
        } else {
            $voucher->customers()->detach();
        }

        // Category (scope 3)
        if ($scope === 3) {
            $voucher->categories()->sync($mapWithUuid($request->input('category_ids', [])));
        } else {
            $voucher->categories()->detach();
        }

        // Products (scope 4 or 6)
        if (in_array($scope, [4, 6], true)) {
            $voucher->products()->sync($mapWithUuid($request->input('product_ids', [])));
        } else {
            $voucher->products()->detach();
        }

        // Brands (scope 5 or 6)
        if (in_array($scope, [5, 6], true)) {
            $voucher->brands()->sync($mapWithUuid($request->input('brand_ids', [])));
        } else {
            $voucher->brands()->detach();
        }

        // Customer Groups (scope 7)
        if ($scope === 7) {
            $voucher->customerGroups()->sync($mapWithUuid($request->input('customer_group_ids', [])));
        } else {
            $voucher->customerGroups()->detach();
        }
    }
}
