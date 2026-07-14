<?php

namespace App\Http\Controllers;

use App\Models\Shipping\ShippingAddress;
use App\Models\Shipping\Courier;
use App\Models\Location\SubDistrict;
use Illuminate\Http\Request;

class ShippingAddressController extends Controller
{
    public function index(Request $request)
    {
        $subDistrictId = $request->query('sub_district_id'); // nullable, default to null (Global)

        $couriers = Courier::all();
        $subDistricts = SubDistrict::orderBy('sub_district')->get();

        // Get all existing non-deleted rates for the selected sub_district_id
        $existingRates = ShippingAddress::withoutGlobalScope('active')
            ->where('sub_district_id', $subDistrictId)
            ->where('deleted', false)
            ->get()
            ->groupBy('courier_id');

        // Prepare the rates list for the view
        $courierRates = [];

        foreach ($couriers as $courier) {
            $rates = $existingRates->get($courier->id);

            if ($rates && $rates->count() > 0) {
                // If existing rates exist, add them all
                foreach ($rates as $rate) {
                    $courierRates[] = [
                        'id' => $rate->id,
                        'courier_id' => $courier->id,
                        'courier_name' => $courier->name,
                        'type' => $rate->type,
                        'price' => $rate->price,
                        'sort_order' => $rate->sort_order,
                        'is_active' => $rate->is_active,
                        'is_new' => false
                    ];
                }
            } else {
                // Otherwise, add a default empty rate row for this courier
                $courierRates[] = [
                    'id' => null, // no database record yet
                    'courier_id' => $courier->id,
                    'courier_name' => $courier->name,
                    'type' => 1, // default to Regular
                    'price' => '',
                    'sort_order' => 0,
                    'is_active' => true,
                    'is_new' => true
                ];
            }
        }

        return view('pages.shipping.shipping-address.index', compact('courierRates', 'subDistricts', 'couriers', 'subDistrictId'));
    }

    public function create()
    {
        $couriers = Courier::all();
        $subDistricts = SubDistrict::orderBy('sub_district')->take(100)->get();
        return view('pages.shipping.shipping-address.create', compact('couriers', 'subDistricts'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'courier_id' => 'required|exists:couriers,id',
            'sub_district_id' => 'nullable|exists:sub_districts,id',
            'type' => 'required|integer',
            'price' => 'required|numeric|min:0',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
        ]);

        $validated['creator'] = auth()->user()->name ?? 'admin';
        $validated['editor'] = auth()->user()->name ?? 'admin';
        $validated['is_active'] = $request->boolean('is_active', true);
        $validated['sort_order'] = $validated['sort_order'] ?? 0;

        ShippingAddress::withoutGlobalScope('active')->updateOrCreate(
            [
                'sub_district_id' => $validated['sub_district_id'],
                'courier_id' => $validated['courier_id'],
                'type' => $validated['type'],
            ],
            [
                'price' => $validated['price'],
                'sort_order' => $validated['sort_order'],
                'is_active' => $validated['is_active'],
                'deleted' => false,
                'creator' => $validated['creator'],
                'editor' => $validated['editor'],
            ]
        );

        return redirect()->route('shipping-addresses.index')->with('success', 'Shipping address rate created successfully');
    }

    public function saveInline(Request $request)
    {
        $validated = $request->validate([
            'id' => 'nullable|exists:shipping_addresses,id',
            'sub_district_id' => 'nullable|exists:sub_districts,id',
            'courier_id' => 'required|exists:couriers,id',
            'type' => 'required|integer',
            'price' => 'nullable|numeric|min:0',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'required|boolean',
        ]);

        $id = $validated['id'] ?? null;
        $subDistrictId = $validated['sub_district_id'];
        $courierId = $validated['courier_id'];
        $type = $validated['type'];
        $price = $validated['price'];

        // If ID is provided, update or delete it
        if ($id) {
            $rate = ShippingAddress::withoutGlobalScope('active')->findOrFail($id);

            if ($price === null || $price === '') {
                $rate->update([
                    'deleted' => true,
                    'is_active' => false,
                    'editor' => auth()->user()->name ?? 'admin'
                ]);

                return response()->json([
                    'success' => true,
                    'deleted' => true,
                    'message' => 'Rate deleted successfully'
                ]);
            }

            // Check if changing type causes duplicate unique combination
            $duplicate = ShippingAddress::withoutGlobalScope('active')
                ->where('sub_district_id', $rate->sub_district_id)
                ->where('courier_id', $rate->courier_id)
                ->where('type', $type)
                ->where('id', '!=', $id)
                ->where('deleted', false)
                ->first();

            if ($duplicate) {
                return response()->json([
                    'success' => false,
                    'message' => 'A rate for this courier and service type already exists.'
                ], 422);
            }

            $rate->update([
                'type' => $type,
                'price' => $price,
                'sort_order' => $validated['sort_order'] ?? 0,
                'is_active' => (bool)$validated['is_active'],
                'editor' => auth()->user()->name ?? 'admin',
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Rate updated successfully',
                'data' => $rate
            ]);
        }

        // If ID is not provided, it's a new rate
        if ($price === null || $price === '') {
            return response()->json([
                'success' => true,
                'message' => 'No action taken (empty price)'
            ]);
        }

        // Check if there is already a rate (active or deleted) for this combination
        $rate = ShippingAddress::withoutGlobalScope('active')
            ->where('sub_district_id', $subDistrictId)
            ->where('courier_id', $courierId)
            ->where('type', $type)
            ->first();

        if ($rate) {
            $rate->update([
                'price' => $price,
                'sort_order' => $validated['sort_order'] ?? 0,
                'is_active' => (bool)$validated['is_active'],
                'deleted' => false,
                'editor' => auth()->user()->name ?? 'admin',
            ]);
        } else {
            $rate = ShippingAddress::create([
                'sub_district_id' => $subDistrictId,
                'courier_id' => $courierId,
                'type' => $type,
                'price' => $price,
                'sort_order' => $validated['sort_order'] ?? 0,
                'is_active' => (bool)$validated['is_active'],
                'deleted' => false,
                'creator' => auth()->user()->name ?? 'admin',
                'editor' => auth()->user()->name ?? 'admin',
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Rate created successfully',
            'data' => $rate
        ]);
    }

    public function edit(string $id)
    {
        $shippingAddress = ShippingAddress::withoutGlobalScope('active')->findOrFail($id);
        $couriers = Courier::all();
        $subDistricts = SubDistrict::orderBy('sub_district')->take(100)->get();

        // Get all existing rates for this SubDistrict and Service Type
        $existingRates = ShippingAddress::withoutGlobalScope('active')
            ->where('sub_district_id', $shippingAddress->sub_district_id)
            ->where('type', $shippingAddress->type)
            ->where('deleted', false)
            ->get()
            ->keyBy('courier_id');

        return view('pages.shipping.shipping-address.edit', compact('shippingAddress', 'couriers', 'subDistricts', 'existingRates'));
    }

    public function update(Request $request, string $id)
    {
        $shippingAddress = ShippingAddress::withoutGlobalScope('active')->findOrFail($id);

        $request->validate([
            'rates' => 'required|array',
            'rates.*.courier_id' => 'required|exists:couriers,id',
            'rates.*.price' => 'nullable|numeric|min:0',
            'rates.*.sort_order' => 'nullable|integer|min:0',
            'rates.*.is_active' => 'boolean',
        ]);

        $subDistrictId = $shippingAddress->sub_district_id;
        $type = $shippingAddress->type;

        foreach ($request->input('rates', []) as $rateData) {
            $courierId = $rateData['courier_id'];
            $price = $rateData['price'];

            if ($price !== null && $price !== '') {
                ShippingAddress::withoutGlobalScope('active')->updateOrCreate(
                    [
                        'sub_district_id' => $subDistrictId,
                        'courier_id' => $courierId,
                        'type' => $type,
                    ],
                    [
                        'price' => $price,
                        'sort_order' => $rateData['sort_order'] ?? 0,
                        'is_active' => isset($rateData['is_active']) ? (bool)$rateData['is_active'] : false,
                        'deleted' => false,
                        'editor' => auth()->user()->name ?? 'admin',
                    ]
                );
            } else {
                ShippingAddress::withoutGlobalScope('active')
                    ->where('sub_district_id', $subDistrictId)
                    ->where('courier_id', $courierId)
                    ->where('type', $type)
                    ->update([
                        'deleted' => true,
                        'is_active' => false,
                        'editor' => auth()->user()->name ?? 'admin'
                    ]);
            }
        }

        return redirect()->route('shipping-addresses.index')->with('success', 'Shipping rates updated successfully');
    }

    public function destroy(string $id)
    {
        $shippingAddress = ShippingAddress::withoutGlobalScope('active')->findOrFail($id);
        $shippingAddress->update(['deleted' => true, 'is_active' => false]);

        return redirect()->route('shipping-addresses.index')->with('success', 'Shipping address rate deleted successfully');
    }
}
