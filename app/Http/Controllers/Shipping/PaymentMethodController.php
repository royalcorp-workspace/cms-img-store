<?php

namespace App\Http\Controllers\Shipping;

use App\Http\Controllers\Controller;

use App\Models\PaymentMethod;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PaymentMethodController extends Controller
{
    public function index(Request $request)
    {
        $query = PaymentMethod::query()->withoutGlobalScope('active');

        if ($search = $request->query('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('code', 'ilike', "%{$search}%")
                  ->orWhere('name', 'ilike', "%{$search}%");
            });
        }

        if ($request->filled('type')) {
            $query->where('type', $request->query('type'));
        }

        if ($request->filled('status')) {
            $status = $request->query('status');
            if ($status === 'active') {
                $query->where('status', 1)->where('deleted', false);
            } elseif ($status === 'inactive') {
                $query->where(function ($q) {
                    $q->where('status', 0)->orWhere('deleted', true);
                });
            }
        }

        $paymentMethods = $query->orderBy('sort_order')->orderBy('name')->paginate(15)->appends($request->query());

        return view('pages.payment-methods.index', compact('paymentMethods'));
    }

    public function create()
    {
        return view('pages.payment-methods.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:50|unique:payment_methods,code',
            'name' => 'required|string|max:150',
            'type' => 'required|integer|in:1,2,3,4,5,6,7,8',
            'provider' => 'nullable|string|max:100',
            'image' => $request->hasFile('image')
                ? 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp,avif,ico|max:5120'
                : 'nullable|string|max:1000',
            'has_charge' => 'boolean',
            'charge_type' => 'nullable|integer|in:1,2',
            'charge_value' => 'nullable|numeric|min:0',
            'charge_bearer' => 'nullable|string|max:50',
            'minimum_amount' => 'nullable|numeric|min:0',
            'maximum_amount' => 'nullable|numeric|min:0',
            'sort_order' => 'nullable|integer|min:0',
            'status' => 'nullable|integer|in:0,1',
            'product_code' => 'nullable|string|max:50',
            'bank_code' => 'nullable|string|max:50',
            'banks' => 'nullable|array',
            'banks.*.bank_name' => 'nullable|string|max:100',
            'banks.*.account_number' => 'nullable|string|max:100',
            'banks.*.account_holder' => 'nullable|string|max:100',
        ]);

        $validated['id'] = (string) \Illuminate\Support\Str::uuid();
        $validated['creator'] = auth()->user()->name ?? 'admin';
        $validated['editor'] = auth()->user()->name ?? 'admin';
        

        $validated['status'] = $request->has('status') ? (int)$request->input('status') : 1;
        $validated['has_charge'] = $request->boolean('has_charge', false);
        $validated['deleted'] = false;
        $validated['sort_order'] = $validated['sort_order'] ?? 0;

        $uploadDisk = config('filesystems.disks.s3.bucket') ? 's3' : 'public';
        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('payment-methods', $uploadDisk);
        } elseif ($request->filled('image')) {
            $validated['image'] = $request->input('image');
        }

        if (!$validated['has_charge']) {
            $validated['charge_type'] = null;
            $validated['charge_value'] = null;
            $validated['charge_bearer'] = null;
        }

        if ((int)$validated['type'] === 1) {
            $banks = [];
            if (!empty($request->input('banks'))) {
                foreach ($request->input('banks') as $bank) {
                    if (!empty($bank['bank_name']) && !empty($bank['account_number']) && !empty($bank['account_holder'])) {
                        $banks[] = [
                            'bank_name' => $bank['bank_name'],
                            'account_number' => $bank['account_number'],
                            'account_holder' => $bank['account_holder'],
                        ];
                    }
                }
            }
            $validated['bank_info'] = !empty($banks) ? $banks : null;
        } else {
            $productCode = trim((string)$request->input('product_code', ''));
            $bankCode = trim((string)$request->input('bank_code', ''));
            $validated['bank_info'] = [
                'product_code' => !empty($productCode) ? $productCode : $validated['code'],
                'bank_code' => !empty($bankCode) ? $bankCode : PaymentMethod::resolveEspayProductCode($validated['code'], (int)$validated['type']),
                'bank_name' => $validated['name'],
            ];
        }

        PaymentMethod::create($validated);

        return redirect()->route('payment-methods.index')->with('success', 'Payment method created successfully');
    }

    public function edit(string $id)
    {
        $paymentMethod = PaymentMethod::withoutGlobalScope('active')->findOrFail($id);

        return view('pages.payment-methods.edit', compact('paymentMethod'));
    }

    public function update(Request $request, string $id)
    {
        $paymentMethod = PaymentMethod::withoutGlobalScope('active')->findOrFail($id);

        $validated = $request->validate([
            'code' => 'required|string|max:50|unique:payment_methods,code,' . $id,
            'name' => 'required|string|max:150',
            'type' => 'required|integer|in:1,2,3,4,5,6,7,8',
            'provider' => 'nullable|string|max:100',
            'image' => $request->hasFile('image')
                ? 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp,avif,ico|max:5120'
                : 'nullable|string|max:1000',
            'has_charge' => 'boolean',
            'charge_type' => 'nullable|integer|in:1,2',
            'charge_value' => 'nullable|numeric|min:0',
            'charge_bearer' => 'nullable|string|max:50',
            'minimum_amount' => 'nullable|numeric|min:0',
            'maximum_amount' => 'nullable|numeric|min:0',
            'sort_order' => 'nullable|integer|min:0',
            'status' => 'required|integer|in:0,1',
            'product_code' => 'nullable|string|max:50',
            'bank_code' => 'nullable|string|max:50',
            'banks' => 'nullable|array',
            'banks.*.bank_name' => 'nullable|string|max:100',
            'banks.*.account_number' => 'nullable|string|max:100',
            'banks.*.account_holder' => 'nullable|string|max:100',
        ]);

        $validated['editor'] = auth()->user()->name ?? 'admin';
        $validated['status'] = (int)$validated['status'];
        if ($validated['status'] === 1) {
            $validated['deleted'] = false;
        }

        $validated['has_charge'] = $request->boolean('has_charge', false);
        $validated['sort_order'] = $validated['sort_order'] ?? 0;

        $uploadDisk = config('filesystems.disks.s3.bucket') ? 's3' : 'public';
        if ($request->hasFile('image')) {
            if ($paymentMethod->image) {
                unlink_media($paymentMethod->image);
            }
            $validated['image'] = $request->file('image')->store('payment-methods', $uploadDisk);
        } elseif ($request->filled('image')) {
            $newImage = $request->input('image');
            if ($paymentMethod->image && $paymentMethod->image !== $newImage) {
                unlink_media($paymentMethod->image);
            }
            $validated['image'] = $newImage;
        } else {
            unset($validated['image']);
        }

        if (!$validated['has_charge']) {
            $validated['charge_type'] = null;
            $validated['charge_value'] = null;
            $validated['charge_bearer'] = null;
        }

        if ((int)$validated['type'] === 1) {
            $banks = [];
            if (!empty($request->input('banks'))) {
                foreach ($request->input('banks') as $bank) {
                    if (!empty($bank['bank_name']) && !empty($bank['account_number']) && !empty($bank['account_holder'])) {
                        $banks[] = [
                            'bank_name' => $bank['bank_name'],
                            'account_number' => $bank['account_number'],
                            'account_holder' => $bank['account_holder'],
                        ];
                    }
                }
            }
            $validated['bank_info'] = !empty($banks) ? $banks : null;
        } else {
            $productCode = trim((string)$request->input('product_code', ''));
            $bankCode = trim((string)$request->input('bank_code', ''));
            if (!empty($productCode) || !empty($bankCode)) {
                $validated['bank_info'] = [
                    'product_code' => !empty($productCode) ? $productCode : ($paymentMethod->bank_info['product_code'] ?? $validated['code']),
                    'bank_code' => !empty($bankCode) ? $bankCode : ($paymentMethod->bank_info['bank_code'] ?? PaymentMethod::resolveEspayProductCode($validated['code'], (int)$validated['type'])),
                    'bank_name' => $validated['name'],
                ];
            } elseif (is_array($paymentMethod->bank_info) && isset($paymentMethod->bank_info['product_code'])) {
                // Preserve existing online bank_info if not explicitly altered
                $validated['bank_info'] = $paymentMethod->bank_info;
            } else {
                $validated['bank_info'] = [
                    'product_code' => $validated['code'],
                    'bank_code' => PaymentMethod::resolveEspayProductCode($validated['code'], (int)$validated['type']),
                    'bank_name' => $validated['name'],
                ];
            }
        }

        $paymentMethod->update($validated);

        return redirect()->route('payment-methods.index')->with('success', 'Payment method updated successfully');
    }

    public function toggleStatus(string $id)
    {
        $paymentMethod = PaymentMethod::withoutGlobalScope('active')->findOrFail($id);
        $newStatus = $paymentMethod->status == 1 ? 0 : 1;
        $paymentMethod->update([
            'status' => $newStatus,
            'deleted' => $newStatus == 1 ? false : $paymentMethod->deleted,
            'editor' => auth()->user()->name ?? 'admin',
        ]);

        $statusLabel = $newStatus == 1 ? 'diaktifkan' : 'dinonaktifkan';
        return redirect()->back()->with('success', "Status metode pembayaran {$paymentMethod->name} berhasil {$statusLabel}");
    }

    public function destroy(string $id)
    {
        $paymentMethod = PaymentMethod::withoutGlobalScope('active')->findOrFail($id);
        $paymentMethod->update(['deleted' => true, 'status' => 0]);

        return redirect()->route('payment-methods.index')->with('success', 'Payment method deleted successfully');
    }
}
