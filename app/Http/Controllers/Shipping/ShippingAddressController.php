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
        $provinceId = $request->query('province_id');
        $cityId = $request->query('city_id');
        $subDistrictId = $request->query('sub_district_id');
        $courierId = $request->query('courier_id');
        $search = $request->query('search');
        $status = $request->query('status');

        $couriers = Courier::withoutGlobalScope('active')->where('deleted', false)->orderBy('name')->get();
        $tokoCouriers = Courier::withoutGlobalScope('active')->where('deleted', false)->where('courier_type', 'toko')->orderBy('name')->get();
        $expedisiCouriers = Courier::withoutGlobalScope('active')->where('deleted', false)->where('courier_type', 'expedisi')->orderBy('name')->get();

        $provinces = Province::where('deleted', false)->orderBy('name')->get(['id', 'name']);

        $citiesQuery = City::with('province')->orderBy('name');
        if ($provinceId) {
            $citiesQuery->where('province_id', $provinceId);
        }
        $cities = $citiesQuery->get();

        $subDistricts = collect();
        if ($cityId) {
            $subDistricts = SubDistrict::where('city_id', $cityId)->orderBy('sub_district')->get(['id', 'sub_district', 'district', 'postal_code']);
        }

        // 1. Tab Kurir Toko (Scope Kota)
        $cityRatesQuery = ShippingAddress::withoutGlobalScope('active')
            ->with(['courier', 'city.province'])
            ->whereNotNull('city_id')
            ->whereHas('courier', function ($q) {
                $q->where('courier_type', 'toko');
            })
            ->where('deleted', false);

        if ($activeTab === 'toko') {
            if ($provinceId) {
                $cityRatesQuery->whereHas('city', function ($q) use ($provinceId) {
                    $q->where('province_id', $provinceId);
                });
            }
            if ($cityId) {
                $cityRatesQuery->where('city_id', $cityId);
            }
            if ($courierId) {
                $cityRatesQuery->where('courier_id', $courierId);
            }
            if ($status !== null && $status !== '') {
                $cityRatesQuery->where('is_active', $status === '1');
            }
            if ($search) {
                $cityRatesQuery->where(function ($q) use ($search) {
                    $q->whereHas('city', function ($cq) use ($search) {
                        $cq->where('name', 'ilike', "%{$search}%")
                           ->orWhereHas('province', function ($pq) use ($search) {
                               $pq->where('name', 'ilike', "%{$search}%");
                           });
                    })->orWhereHas('courier', function ($cq) use ($search) {
                        $cq->where('name', 'ilike', "%{$search}%");
                    });
                });
            }
        }

        $cityRates = $cityRatesQuery->orderBy('created_at', 'desc')->paginate(20, ['*'], 'toko_page')->appends($request->query());

        // 2. Tab Tarif Ekspedisi (Scope Kota & Kelurahan) - Meliputi 1 Indonesia
        $subDistrictRatesQuery = ShippingAddress::withoutGlobalScope('active')
            ->with(['courier', 'subDistrict.city.province', 'city.province'])
            ->where(function ($q) {
                $q->whereNotNull('sub_district_id')->orWhereNotNull('city_id');
            })
            ->whereHas('courier', function ($q) {
                $q->where('courier_type', 'expedisi');
            })
            ->where('deleted', false);

        if ($activeTab === 'sub_district') {
            if ($provinceId) {
                $subDistrictRatesQuery->where(function ($q) use ($provinceId) {
                    $q->whereHas('subDistrict.city', function ($cq) use ($provinceId) {
                        $cq->where('province_id', $provinceId);
                    })->orWhereHas('city', function ($cq) use ($provinceId) {
                        $cq->where('province_id', $provinceId);
                    });
                });
            }
            if ($cityId) {
                $subDistrictRatesQuery->where(function ($q) use ($cityId) {
                    $q->whereHas('subDistrict', function ($sq) use ($cityId) {
                        $sq->where('city_id', $cityId);
                    })->orWhere('city_id', $cityId);
                });
            }
            if ($subDistrictId) {
                $subDistrictRatesQuery->where('sub_district_id', $subDistrictId);
            }
            if ($courierId) {
                $subDistrictRatesQuery->where('courier_id', $courierId);
            }
            if ($status !== null && $status !== '') {
                $subDistrictRatesQuery->where('is_active', $status === '1');
            }
            if ($search) {
                $subDistrictRatesQuery->where(function ($q) use ($search) {
                    $q->whereHas('subDistrict', function ($sq) use ($search) {
                        $sq->where('sub_district', 'ilike', "%{$search}%")
                           ->orWhere('district', 'ilike', "%{$search}%")
                           ->orWhere('postal_code', 'ilike', "%{$search}%");
                    })->orWhereHas('city', function ($cq) use ($search) {
                        $cq->where('name', 'ilike', "%{$search}%")
                           ->orWhereHas('province', function ($pq) use ($search) {
                               $pq->where('name', 'ilike', "%{$search}%");
                           });
                    })->orWhereHas('courier', function ($cq) use ($search) {
                        $cq->where('name', 'ilike', "%{$search}%");
                    });
                });
            }
        }

        $subDistrictRates = $subDistrictRatesQuery->orderBy('created_at', 'desc')->paginate(20, ['*'], 'expedisi_page')->appends($request->query());

        return view('pages.shipping.shipping-address.index', compact(
            'activeTab',
            'cityRates',
            'subDistrictRates',
            'provinces',
            'cities',
            'subDistricts',
            'tokoCouriers',
            'expedisiCouriers',
            'couriers',
            'provinceId',
            'cityId',
            'subDistrictId',
            'courierId',
            'search',
            'status'
        ));
    }

    public function getCitiesByProvince(Request $request)
    {
        $provinceId = $request->query('province_id');
        $query = City::query();
        if ($provinceId) {
            $query->where('province_id', $provinceId);
        }
        $cities = $query->orderBy('name')->get(['id', 'name']);
        return response()->json($cities);
    }

    public function getSubDistrictsByCity(Request $request)
    {
        $cityId = $request->query('city_id');
        $query = SubDistrict::query();
        if ($cityId) {
            $query->where('city_id', $cityId);
        }
        $subDistricts = $query->orderBy('sub_district')->get(['id', 'sub_district', 'district', 'postal_code']);
        return response()->json($subDistricts->map(fn($sd) => [
            'id' => $sd->id,
            'name' => $sd->sub_district . ($sd->district ? ', Kec. ' . $sd->district : '') . ($sd->postal_code ? ' (' . $sd->postal_code . ')' : ''),
        ]));
    }

    public function searchSubDistricts(Request $request)
    {
        $q = trim((string) $request->query('q', ''));
        if (strlen($q) < 2) {
            return response()->json(['results' => []]);
        }

        $results = SubDistrict::withoutGlobalScope('active')
            ->with('city.province')
            ->where('deleted', false)
            ->where(function ($query) use ($q) {
                $query->where('sub_district', 'ilike', "%{$q}%")
                      ->orWhere('district', 'ilike', "%{$q}%")
                      ->orWhere('postal_code', 'ilike', "%{$q}%")
                      ->orWhereHas('city', function ($cq) use ($q) {
                          $cq->where('name', 'ilike', "%{$q}%");
                      });
            })
            ->orderBy('sub_district')
            ->take(30)
            ->get();

        return response()->json([
            'results' => $results->map(function ($sd) {
                $cityName = $sd->city->name ?? $sd->district;
                $provinceName = $sd->city->province->name ?? $sd->province;
                $label = "{$sd->sub_district}, Kec. {$sd->district}, {$cityName}, {$provinceName}" . ($sd->postal_code ? " ({$sd->postal_code})" : "");
                return [
                    'id' => $sd->id,
                    'text' => $label,
                ];
            })
        ]);
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

        $tab = $request->input('tab');
        if (!$tab) {
            $courier = Courier::find($validated['courier_id']);
            $tab = ($courier && $courier->courier_type === 'expedisi') ? 'sub_district' : 'toko';
        }
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

        $tab = $request->input('tab');
        if (!$tab) {
            $courier = Courier::find($validated['courier_id']);
            $tab = ($courier && $courier->courier_type === 'expedisi') ? 'sub_district' : 'toko';
        }
        return redirect()->route('shipping-addresses.index', ['tab' => $tab])->with('success', 'Shipping rate updated successfully');
    }

    public function destroy(string $id)
    {
        $shippingAddress = ShippingAddress::withoutGlobalScope('active')->findOrFail($id);
        $shippingAddress->update(['deleted' => true, 'is_active' => false]);

        return redirect()->back()->with('success', 'Shipping address rate deleted successfully');
    }
}
