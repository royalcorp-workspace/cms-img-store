<?php

namespace App\Http\Controllers\Shipping;

use App\Http\Controllers\Controller;

use App\Models\Shipping\ShippingAddress;
use App\Models\Shipping\Courier;
use App\Models\Location\SubDistrict;
use App\Models\Location\City;
use App\Models\Location\Province;
use Illuminate\Http\Request;

class ShippingAddressController extends Controller
{
    public function index(Request $request)
    {
        $activeTab = $request->query('tab', 'toko'); // 'toko' or 'sub_district'
        $subDistrictId = $request->query('sub_district_id'); // nullable, default to null (Global)
        $cityId = $request->query('city_id'); // nullable filter for toko

        $couriers = Courier::withoutGlobalScope('active')->where('deleted', false)->get();
        $tokoCouriers = Courier::withoutGlobalScope('active')->where('deleted', false)->where('courier_type', 'toko')->get();
        if ($tokoCouriers->isEmpty()) {
            $tokoCouriers = $couriers;
        }

        $subDistricts = SubDistrict::orderBy('sub_district')->take(200)->get();
        $cities = City::with('province')->orderBy('name')->get();

        // City rates (Scope Kota - Kurir Toko)
        $cityRatesQuery = ShippingAddress::withoutGlobalScope('active')
            ->with(['courier', 'city.province'])
            ->whereNotNull('city_id')
            ->where('deleted', false);

        if ($cityId) {
            $cityRatesQuery->where('city_id', $cityId);
        }
        if ($request->filled('courier_id')) {
            $cityRatesQuery->where('courier_id', $request->query('courier_id'));
        }

        $cityRates = $cityRatesQuery->orderBy('created_at', 'desc')->paginate(20)->appends($request->query());

        // Get all existing non-deleted rates for the selected sub_district_id
        $existingRates = ShippingAddress::withoutGlobalScope('active')
            ->where('sub_district_id', $subDistrictId)
            ->whereNull('city_id')
            ->where('deleted', false)
            ->get()
            ->groupBy('courier_id');

        // Prepare the rates list for the sub-district view
        $courierRates = [];
        foreach ($couriers as $courier) {
            $rates = $existingRates->get($courier->id);

            if ($rates && $rates->count() > 0) {
                foreach ($rates as $rate) {
                    $courierRates[] = [
                        'id' => $rate->id,
                        'courier_id' => $courier->id,
                        'courier_name' => $courier->name,
                        'type' => $rate->type,
                        'price' => $rate->price,
                        'additional_price_per_kg' => $rate->additional_price_per_kg,
                        'sort_order' => $rate->sort_order,
                        'is_active' => $rate->is_active,
                        'is_new' => false
                    ];
                }
            } else {
                $courierRates[] = [
                    'id' => null,
                    'courier_id' => $courier->id,
                    'courier_name' => $courier->name,
                    'type' => 1,
                    'price' => '',
                    'additional_price_per_kg' => 0,
                    'sort_order' => 0,
                    'is_active' => true,
                    'is_new' => true
                ];
            }
        }

        return view('pages.shipping.shipping-address.index', compact(
            'activeTab',
            'cityRates',
            'cities',
            'tokoCouriers',
            'courierRates',
            'subDistricts',
            'couriers',
            'subDistrictId',
            'cityId'
        ));
    }

    public function create()
    {
        $couriers = Courier::withoutGlobalScope('active')->where('deleted', false)->get();
        $cities = City::with('province')->orderBy('name')->get();
        $subDistricts = SubDistrict::orderBy('sub_district')->take(200)->get();
        return view('pages.shipping.shipping-address.create', compact('couriers', 'cities', 'subDistricts'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'courier_id' => 'required|exists:couriers,id',
            'city_id' => 'nullable|exists:cities,id',
            'sub_district_id' => 'nullable|exists:sub_districts,id',
            'type' => 'required|integer',
            'price' => 'required|numeric|min:0',
            'additional_price_per_kg' => 'nullable|numeric|min:0',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
        ]);

        $validated['creator'] = auth()->user()->name ?? 'admin';
        $validated['editor'] = auth()->user()->name ?? 'admin';
        $validated['is_active'] = $request->boolean('is_active', true);
        $validated['sort_order'] = $validated['sort_order'] ?? 0;
        $validated['additional_price_per_kg'] = $validated['additional_price_per_kg'] ?? 0;

        ShippingAddress::withoutGlobalScope('active')->updateOrCreate(
            [
                'city_id' => $validated['city_id'] ?? null,
                'sub_district_id' => $validated['sub_district_id'] ?? null,
                'courier_id' => $validated['courier_id'],
                'type' => $validated['type'],
            ],
            [
                'price' => $validated['price'],
                'additional_price_per_kg' => $validated['additional_price_per_kg'],
                'sort_order' => $validated['sort_order'],
                'is_active' => $validated['is_active'],
                'deleted' => false,
                'creator' => $validated['creator'],
                'editor' => $validated['editor'],
            ]
        );

        $tab = !empty($validated['city_id']) ? 'toko' : 'sub_district';
        return redirect()->route('shipping-addresses.index', ['tab' => $tab])->with('success', 'Shipping address rate created successfully');
    }

    public function saveInline(Request $request)
    {
        $validated = $request->validate([
            'id' => 'nullable|exists:shipping_addresses,id',
            'city_id' => 'nullable|exists:cities,id',
            'sub_district_id' => 'nullable|exists:sub_districts,id',
            'courier_id' => 'required|exists:couriers,id',
            'type' => 'required|integer',
            'price' => 'nullable|numeric|min:0',
            'additional_price_per_kg' => 'nullable|numeric|min:0',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'required|boolean',
        ]);

        $id = $validated['id'] ?? null;
        $cityId = $validated['city_id'] ?? null;
        $subDistrictId = $validated['sub_district_id'] ?? null;
        $courierId = $validated['courier_id'];
        $type = $validated['type'];
        $price = $validated['price'];
        $additionalPricePerKg = $validated['additional_price_per_kg'] ?? 0;

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
                ->where('city_id', $rate->city_id)
                ->where('courier_id', $rate->courier_id)
                ->where('type', $type)
                ->where('id', '!=', $id)
                ->where('deleted', false)
                ->first();

            if ($duplicate) {
                return response()->json([
                    'success' => false,
                    'message' => 'A rate for this courier, location and service type already exists.'
                ], 422);
            }

            $rate->update([
                'type' => $type,
                'price' => $price,
                'additional_price_per_kg' => $additionalPricePerKg,
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
            ->where('city_id', $cityId)
            ->where('courier_id', $courierId)
            ->where('type', $type)
            ->first();

        if ($rate) {
            $rate->update([
                'price' => $price,
                'additional_price_per_kg' => $additionalPricePerKg,
                'sort_order' => $validated['sort_order'] ?? 0,
                'is_active' => (bool)$validated['is_active'],
                'deleted' => false,
                'editor' => auth()->user()->name ?? 'admin',
            ]);
        } else {
            $rate = ShippingAddress::create([
                'city_id' => $cityId,
                'sub_district_id' => $subDistrictId,
                'courier_id' => $courierId,
                'type' => $type,
                'price' => $price,
                'additional_price_per_kg' => $additionalPricePerKg,
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
        $couriers = Courier::withoutGlobalScope('active')->where('deleted', false)->get();
        $cities = City::with('province')->orderBy('name')->get();
        $subDistricts = SubDistrict::orderBy('sub_district')->take(200)->get();

        return view('pages.shipping.shipping-address.edit', compact('shippingAddress', 'couriers', 'cities', 'subDistricts'));
    }

    public function update(Request $request, string $id)
    {
        $shippingAddress = ShippingAddress::withoutGlobalScope('active')->findOrFail($id);

        $validated = $request->validate([
            'courier_id' => 'required|exists:couriers,id',
            'city_id' => 'nullable|exists:cities,id',
            'sub_district_id' => 'nullable|exists:sub_districts,id',
            'type' => 'required|integer',
            'price' => 'required|numeric|min:0',
            'additional_price_per_kg' => 'nullable|numeric|min:0',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
        ]);

        $shippingAddress->update([
            'courier_id' => $validated['courier_id'],
            'city_id' => $validated['city_id'] ?? null,
            'sub_district_id' => $validated['sub_district_id'] ?? null,
            'type' => $validated['type'],
            'price' => $validated['price'],
            'additional_price_per_kg' => $validated['additional_price_per_kg'] ?? 0,
            'sort_order' => $validated['sort_order'] ?? 0,
            'is_active' => $request->boolean('is_active', true),
            'editor' => auth()->user()->name ?? 'admin',
        ]);

        $tab = !empty($validated['city_id']) ? 'toko' : 'sub_district';
        return redirect()->route('shipping-addresses.index', ['tab' => $tab])->with('success', 'Shipping rate updated successfully');
    }

    public function destroy(string $id)
    {
        $shippingAddress = ShippingAddress::withoutGlobalScope('active')->findOrFail($id);
        $shippingAddress->update(['deleted' => true, 'is_active' => false]);

        return redirect()->back()->with('success', 'Shipping address rate deleted successfully');
    }
}
