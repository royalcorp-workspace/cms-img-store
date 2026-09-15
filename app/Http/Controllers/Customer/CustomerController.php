<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;

use App\Models\Customer\Customer;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $query = Customer::with(['user', 'addresses']);

        if ($search = $request->query('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'ilike', "%{$search}%")
                  ->orWhere('email', 'ilike', "%{$search}%")
                  ->orWhere('phone', 'ilike', "%{$search}%");
            });
        }

        if ($request->has('status')) {
            $status = $request->query('status');
            if ($status === 'active') {
                $query->where('deleted', false);
            } elseif ($status === 'inactive') {
                $query->where('deleted', true);
            }
        }

        $customers = $query->paginate(15)->appends($request->query());
        return view('pages.customers.index', compact('customers'));
    }

    public function create()
    {
        $subDistricts = \App\Models\Location\SubDistrict::with('city')->orderBy('sub_district')->get();
        return view('pages.customers.create', compact('subDistricts'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:customers,email',
            'phone' => 'nullable|string|max:50',
            'user_id' => 'nullable|exists:users,id',
            'customer_type' => 'nullable|integer',
            'label' => 'nullable|string|max:50',
            'sub_district_id' => 'nullable|exists:sub_districts,id',
            'address' => 'nullable|string',
            'postal_code' => 'nullable|string|max:10',
            'is_primary' => 'nullable|boolean',
        ]);

        $customer = Customer::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'user_id' => $validated['user_id'] ?? null,
            'customer_type' => $validated['customer_type'] ?? 1,
        ]);

        // Create initial address if provided
        if ($request->filled('sub_district_id') || $request->filled('address')) {
            $subDistrict = \App\Models\Location\SubDistrict::find($request->sub_district_id);
            \App\Models\Customer\Address::create([
                'id' => \Illuminate\Support\Str::uuid(),
                'customer_id' => $customer->id,
                'user_id' => $customer->user_id,
                'sub_district_id' => $request->sub_district_id,
                'city_id' => $subDistrict ? $subDistrict->city_id : null,
                'label' => $request->label ?? 'Utama',
                'recipient_name' => $customer->name,
                'phone' => $customer->phone ?? '-',
                'address' => $request->address ?? '-',
                'postal_code' => $request->postal_code,
                'is_primary' => $request->has('is_primary') ? true : false,
            ]);
        }

        return redirect()->route('customers.index')->with('success', 'Customer created successfully');
    }

    public function show($id)
    {
        $customer = Customer::with(['user', 'addresses', 'orders.deliveryLogs', 'orders.courier', 'orders.delivery.courier'])->findOrFail($id);
        return view('pages.customers.show', compact('customer'));
    }

    public function edit($id)
    {
        $customer = Customer::findOrFail($id);
        $addresses = $customer->addresses()->with(['city', 'subDistrict'])->get();
        $subDistricts = \App\Models\Location\SubDistrict::with('city')->orderBy('sub_district')->get();
        
        return view('pages.customers.edit', compact('customer', 'addresses', 'subDistricts'));
    }

    public function update(Request $request, $id)
    {
        $customer = Customer::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:customers,email,' . $id,
            'phone' => 'nullable|string|max:50',
            'user_id' => 'nullable|exists:users,id',
            'customer_type' => 'nullable|integer',
        ]);

        $customer->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'user_id' => $validated['user_id'] ?? null,
            'customer_type' => $validated['customer_type'] ?? 1,
        ]);

        return redirect()->route('customers.index')->with('success', 'Customer updated successfully');
    }

    public function destroy($id)
    {
        $customer = Customer::findOrFail($id);
        $customer->update(['deleted' => true]);
        return redirect()->route('customers.index')->with('success', 'Customer deleted successfully');
    }
}
