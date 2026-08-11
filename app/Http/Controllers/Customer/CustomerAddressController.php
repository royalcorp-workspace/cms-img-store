<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Customer\Customer;
use App\Models\Customer\Address;
use App\Models\Location\SubDistrict;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CustomerAddressController extends Controller
{
    public function index($customerId)
    {
        $customer = Customer::findOrFail($customerId);
        $addresses = $customer->addresses()->with(['city', 'subDistrict'])->get();
        return view('pages.customers.addresses.index', compact('customer', 'addresses'));
    }

    public function create($customerId)
    {
        $customer = Customer::findOrFail($customerId);
        $subDistricts = SubDistrict::with('city')->orderBy('sub_district')->get();
        return view('pages.customers.addresses.create', compact('customer', 'subDistricts'));
    }

    public function store(Request $request, $customerId)
    {
        $customer = Customer::findOrFail($customerId);

        $validated = $request->validate([
            'label' => 'required|string|max:50',
            'recipient_name' => 'required|string|max:100',
            'phone' => 'required|string|max:20',
            'sub_district_id' => 'required|exists:sub_districts,id',
            'address' => 'required|string',
            'postal_code' => 'nullable|string|max:10',
        ]);

        $subDistrict = SubDistrict::find($validated['sub_district_id']);

        if ($request->has('is_primary')) {
            $customer->addresses()->update(['is_primary' => false]);
        }

        Address::create([
            'id' => Str::uuid(),
            'customer_id' => $customer->id,
            'user_id' => $customer->user_id,
            'sub_district_id' => $validated['sub_district_id'],
            'city_id' => $subDistrict->city_id,
            'label' => $validated['label'],
            'recipient_name' => $validated['recipient_name'],
            'phone' => $validated['phone'],
            'address' => $validated['address'],
            'postal_code' => $validated['postal_code'],
            'is_primary' => $request->has('is_primary') || $customer->addresses()->count() === 0,
        ]);

        return redirect()->route('customers.edit', $customer->id)->with('success', 'Alamat berhasil ditambahkan.');
    }

    public function edit($customerId, $id)
    {
        $customer = Customer::findOrFail($customerId);
        $address = Address::where('customer_id', $customerId)->findOrFail($id);
        $subDistricts = SubDistrict::with('city')->orderBy('sub_district')->get();
        return view('pages.customers.addresses.edit', compact('customer', 'address', 'subDistricts'));
    }

    public function update(Request $request, $customerId, $id)
    {
        $customer = Customer::findOrFail($customerId);
        $address = Address::where('customer_id', $customerId)->findOrFail($id);

        $validated = $request->validate([
            'label' => 'required|string|max:50',
            'recipient_name' => 'required|string|max:100',
            'phone' => 'required|string|max:20',
            'sub_district_id' => 'required|exists:sub_districts,id',
            'address' => 'required|string',
            'postal_code' => 'nullable|string|max:10',
        ]);

        $subDistrict = SubDistrict::find($validated['sub_district_id']);

        if ($request->has('is_primary') && !$address->is_primary) {
            $customer->addresses()->update(['is_primary' => false]);
        }

        $address->update([
            'sub_district_id' => $validated['sub_district_id'],
            'city_id' => $subDistrict->city_id,
            'label' => $validated['label'],
            'recipient_name' => $validated['recipient_name'],
            'phone' => $validated['phone'],
            'address' => $validated['address'],
            'postal_code' => $validated['postal_code'],
            'is_primary' => $request->has('is_primary') ? true : $address->is_primary,
        ]);

        return redirect()->route('customers.edit', $customer->id)->with('success', 'Alamat berhasil diperbarui.');
    }

    public function destroy($customerId, $id)
    {
        $address = Address::where('customer_id', $customerId)->findOrFail($id);
        $address->delete();

        return redirect()->route('customers.edit', $customerId)->with('success', 'Alamat berhasil dihapus.');
    }
}
