<?php

namespace App\Http\Controllers;

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
            'image' => 'nullable|string|max:255',
            'has_charge' => 'boolean',
            'charge_type' => 'nullable|integer|in:1,2',
            'charge_value' => 'nullable|numeric|min:0',
            'charge_bearer' => 'nullable|string|max:50',
            'minimum_amount' => 'nullable|numeric|min:0',
            'maximum_amount' => 'nullable|numeric|min:0',
            'sort_order' => 'nullable|integer|min:0',
            'banks' => 'nullable|array',
            'banks.*.bank_name' => 'nullable|string|max:100',
            'banks.*.account_number' => 'nullable|string|max:100',
            'banks.*.account_holder' => 'nullable|string|max:100',
        ]);

        $validated['id'] = (string) \Illuminate\Support\Str::uuid();
        $validated['creator'] = auth()->user()->name ?? 'admin';
        $validated['editor'] = auth()->user()->name ?? 'admin';
        $validated['status'] = 1;
        $validated['has_charge'] = $request->boolean('has_charge', false);
        $validated['deleted'] = false;
        $validated['sort_order'] = $validated['sort_order'] ?? 0;

        if (!$validated['has_charge']) {
            $validated['charge_type'] = null;
            $validated['charge_value'] = null;
            $validated['charge_bearer'] = null;
        }

        if ((int)$validated['type'] === 1 && !empty($request->input('banks'))) {
            $banks = [];
            foreach ($request->input('banks') as $bank) {
                if (!empty($bank['bank_name']) && !empty($bank['account_number']) && !empty($bank['account_holder'])) {
                    $banks[] = [
                        'bank_name' => $bank['bank_name'],
                        'account_number' => $bank['account_number'],
                        'account_holder' => $bank['account_holder'],
                    ];
                }
            }
            $validated['bank_info'] = $banks;
        } else {
            $validated['bank_info'] = null;
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
            'image' => 'nullable|string|max:255',
            'has_charge' => 'boolean',
            'charge_type' => 'nullable|integer|in:1,2',
            'charge_value' => 'nullable|numeric|min:0',
            'charge_bearer' => 'nullable|string|max:50',
            'minimum_amount' => 'nullable|numeric|min:0',
            'maximum_amount' => 'nullable|numeric|min:0',
            'sort_order' => 'nullable|integer|min:0',
            'banks' => 'nullable|array',
            'banks.*.bank_name' => 'nullable|string|max:100',
            'banks.*.account_number' => 'nullable|string|max:100',
            'banks.*.account_holder' => 'nullable|string|max:100',
        ]);

        $validated['editor'] = auth()->user()->name ?? 'admin';
        $validated['has_charge'] = $request->boolean('has_charge', false);
        $validated['sort_order'] = $validated['sort_order'] ?? 0;

        if (!$validated['has_charge']) {
            $validated['charge_type'] = null;
            $validated['charge_value'] = null;
            $validated['charge_bearer'] = null;
        }

        if ((int)$validated['type'] === 1 && !empty($request->input('banks'))) {
            $banks = [];
            foreach ($request->input('banks') as $bank) {
                if (!empty($bank['bank_name']) && !empty($bank['account_number']) && !empty($bank['account_holder'])) {
                    $banks[] = [
                        'bank_name' => $bank['bank_name'],
                        'account_number' => $bank['account_number'],
                        'account_holder' => $bank['account_holder'],
                    ];
                }
            }
            $validated['bank_info'] = $banks;
        } else {
            $validated['bank_info'] = null;
        }

        $paymentMethod->update($validated);

        return redirect()->route('payment-methods.index')->with('success', 'Payment method updated successfully');
    }

    public function destroy(string $id)
    {
        $paymentMethod = PaymentMethod::withoutGlobalScope('active')->findOrFail($id);
        $paymentMethod->update(['deleted' => true, 'status' => 0]);

        return redirect()->route('payment-methods.index')->with('success', 'Payment method deleted successfully');
    }
}
