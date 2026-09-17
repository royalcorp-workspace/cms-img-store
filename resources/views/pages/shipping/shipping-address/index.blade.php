@extends('layouts.app')

@section('title', 'Shipping Address Rates')

@section('content')
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="font-headline-lg text-headline-lg text-on-surface">Shipping Address Rates</h1>
            <nav class="flex items-center gap-2 text-body-md text-on-surface-variant mt-1">
                <a href="{{ route('dashboard') }}" class="text-primary hover:underline">Dashboard</a>
                <span class="material-symbols-outlined text-[16px]">chevron_right</span>
                <span>Shipping & Payment</span>
                <span class="material-symbols-outlined text-[16px]">chevron_right</span>
                <span>Shipping Address Rates</span>
            </nav>
        </div>
    </div>

    @include('layouts.partials.shipping-payment-submenu')

    @if(session('success'))
        <div class="mb-6 p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl flex items-center gap-2 text-body-md">
            <span class="material-symbols-outlined text-emerald-600 text-[20px]">check_circle</span>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    <!-- Tabs Navigation -->
    <div class="flex items-center gap-2 mb-6 border-b border-outline-variant/40 pb-2">
        <a href="{{ route('shipping-addresses.index', ['tab' => 'toko']) }}" 
           class="flex items-center gap-2 px-4 py-2.5 rounded-lg text-label-md font-semibold transition-all {{ $activeTab === 'toko' ? 'bg-primary text-white shadow-sm' : 'bg-white text-on-surface-variant hover:bg-surface-container border border-outline-variant/30' }}">
            <span class="material-symbols-outlined text-[18px]">storefront</span>
            Kurir Toko (Scope Jangkauan Kota)
        </a>
        <a href="{{ route('shipping-addresses.index', ['tab' => 'sub_district']) }}" 
           class="flex items-center gap-2 px-4 py-2.5 rounded-lg text-label-md font-semibold transition-all {{ $activeTab === 'sub_district' ? 'bg-primary text-white shadow-sm' : 'bg-white text-on-surface-variant hover:bg-surface-container border border-outline-variant/30' }}">
            <span class="material-symbols-outlined text-[18px]">pin_drop</span>
            Tarif Kecamatan / Kelurahan & Global
        </a>
    </div>

    @if($activeTab === 'toko')
        <!-- Section: Kurir Toko City Scope -->
        <div class="bg-blue-50/70 border border-blue-200 rounded-xl p-4 mb-6 text-xs text-blue-900 flex items-start gap-3">
            <span class="material-symbols-outlined text-blue-600 text-[22px] mt-0.5 shrink-0">info</span>
            <div class="space-y-1">
                <p class="font-bold text-sm text-blue-950">Pengaturan Scope Wilayah & Tarif Kurir Toko :</p>
                <p>1. <strong>Scope Kota:</strong> Kurir Toko hanya menjangkau Kota/Kabupaten yang didaftarkan pada tabel di bawah ini. Kota di luar daftar otomatis berstatus <em>Di Luar Jangkauan</em> pada saat Checkout.</p>
                <p>2. <strong>Perhitungan Ongkir:</strong> Untuk produk dimensi/berat, ongkir dihitung dari <strong>Tarif Dasar Kota</strong> + <strong>Biaya Tambahan / Kg</strong> (jika berat > 1 kg). Jika Biaya Tambahan/Kg bernilai <code>0</code>, maka ongkir berlaku <strong>Flat Rate</strong> . Produk bertarif tetap (fixed) akan ditambahkan sesuai tarif tetapnya.</p>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-outline-variant/30 overflow-hidden mb-8">
            <div class="p-4 border-b border-outline-variant flex flex-col sm:flex-row gap-4 justify-between items-center bg-surface-gray">
                <form method="GET" class="flex flex-wrap items-center gap-3 w-full sm:w-auto">
                    <input type="hidden" name="tab" value="toko">
                    <div class="w-64">
                        <select name="city_id" class="w-full px-3 py-2 border border-outline-variant rounded-lg text-body-md bg-white select2-enable">
                            <option value="">Semua Kota / Kabupaten</option>
                            @foreach($cities as $c)
                                <option value="{{ $c->id }}" {{ request('city_id') == $c->id ? 'selected' : '' }}>
                                    {{ $c->name }} ({{ $c->province->name ?? '' }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="w-48">
                        <select name="courier_id" class="w-full px-3 py-2 border border-outline-variant rounded-lg text-body-md bg-white select2-enable">
                            <option value="">Semua Kurir</option>
                            @foreach($couriers as $cr)
                                <option value="{{ $cr->id }}" {{ request('courier_id') == $cr->id ? 'selected' : '' }}>
                                    {{ $cr->name }} ({{ $cr->courier_type }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" class="px-4 py-2 bg-primary text-white rounded-lg text-label-md font-semibold hover:opacity-90 transition-all shadow-sm">
                        Filter
                    </button>
                    @if(request('city_id') || request('courier_id'))
                        <a href="{{ route('shipping-addresses.index', ['tab' => 'toko']) }}" class="text-xs text-on-surface-variant hover:text-primary underline">
                            Reset Filter
                        </a>
                    @endif
                </form>

                <button type="button" onclick="openAddCityModal()" class="flex items-center gap-2 px-4 py-2 bg-primary text-white rounded-lg text-label-md font-semibold hover:opacity-90 transition-all shadow-sm shrink-0 border-0 cursor-pointer">
                    <span class="material-symbols-outlined text-[18px]">add_location_alt</span>
                    Tambah Jangkauan Kota
                </button>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-surface-gray border-b border-outline-variant/30">
                            <th class="px-gutter py-3.5 font-label-md text-label-md text-on-surface-variant uppercase tracking-wider">Kota / Kabupaten</th>
                            <th class="px-gutter py-3.5 font-label-md text-label-md text-on-surface-variant uppercase tracking-wider">Kurir</th>
                            <th class="px-gutter py-3.5 font-label-md text-label-md text-on-surface-variant uppercase tracking-wider">Tipe Layanan</th>
                            <th class="px-gutter py-3.5 font-label-md text-label-md text-on-surface-variant uppercase tracking-wider">Tarif Dasar (Rp)</th>
                            <th class="px-gutter py-3.5 font-label-md text-label-md text-on-surface-variant uppercase tracking-wider">Biaya Tambahan/Kg (Rp)</th>
                            <th class="px-gutter py-3.5 font-label-md text-label-md text-on-surface-variant uppercase tracking-wider text-center">Status</th>
                            <th class="px-gutter py-3.5 font-label-md text-label-md text-on-surface-variant uppercase tracking-wider text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-outline-variant/20">
                        @forelse($cityRates as $rate)
                            <tr class="hover:bg-surface-container/30 transition-colors {{ !$rate->is_active ? 'opacity-60' : '' }}" data-rate-id="{{ $rate->id }}">
                                <td class="px-gutter py-4 font-body-md text-on-surface font-semibold">
                                    {{ $rate->city->name ?? 'N/A' }}
                                    <span class="block text-xs text-on-surface-variant font-normal">{{ $rate->city->province->name ?? '' }}</span>
                                </td>
                                <td class="px-gutter py-4 font-body-md text-on-surface">
                                    <span class="font-medium">{{ $rate->courier->name ?? 'N/A' }}</span>
                                    @if(($rate->courier->courier_type ?? '') === 'toko')
                                        <span class="ml-1.5 px-2 py-0.5 rounded text-[10px] bg-amber-100 text-amber-800 font-semibold">Toko</span>
                                    @endif
                                </td>
                                <td class="px-gutter py-4 font-body-md text-on-surface">
                                    @php
                                        $typeMap = [1 => 'Regular', 2 => 'Express', 3 => 'Same Day', 4 => 'Instant'];
                                    @endphp
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-label-sm bg-primary/10 text-primary">
                                        {{ $typeMap[$rate->type] ?? 'Regular' }}
                                    </span>
                                </td>
                                <td class="px-gutter py-4 font-body-md text-on-surface font-semibold">
                                    Rp {{ number_format($rate->price, 0, ',', '.') }}
                                </td>
                                <td class="px-gutter py-4 font-body-md text-on-surface">
                                    @if(($rate->additional_price_per_kg ?? 0) > 0)
                                        + Rp {{ number_format($rate->additional_price_per_kg, 0, ',', '.') }} / kg
                                    @else
                                        <span class="text-xs text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded font-medium">Flat (Rp 0/kg)</span>
                                    @endif
                                </td>
                                <td class="px-gutter py-4 text-center">
                                    @if($rate->is_active)
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-label-sm bg-success/10 text-success">
                                            <span class="w-1.5 h-1.5 rounded-full bg-current"></span> Aktif
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-label-sm bg-neutral-100 text-neutral-500">
                                            Nonaktif
                                        </span>
                                    @endif
                                </td>
                                <td class="px-gutter py-4 text-center">
                                    <div class="flex items-center justify-center gap-2">
                                        <button type="button" onclick="openEditCityModal('{{ $rate->id }}', '{{ $rate->city_id }}', '{{ $rate->courier_id }}', '{{ $rate->type }}', '{{ $rate->price }}', '{{ $rate->additional_price_per_kg }}', {{ $rate->is_active ? 'true' : 'false' }})" class="p-1.5 text-on-surface-variant hover:text-primary rounded-lg hover:bg-primary/5 transition-all border-0 bg-transparent cursor-pointer" title="Edit">
                                            <span class="material-symbols-outlined text-[18px]">edit</span>
                                        </button>
                                        <form method="POST" action="{{ route('shipping-addresses.destroy', $rate->id) }}" class="inline-block" onsubmit="return confirm('Hapus jangkauan tarif kota ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="p-1.5 text-danger hover:bg-danger/5 rounded-lg transition-all border-0 bg-transparent cursor-pointer" title="Delete">
                                                <span class="material-symbols-outlined text-[18px]">delete</span>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-gutter py-12 text-center text-on-surface-variant">
                                    <span class="material-symbols-outlined text-[36px] text-on-surface-variant/40 block mb-2">location_off</span>
                                    Belum ada kota jangkauan yang didaftarkan untuk Kurir Toko.
                                    <div class="mt-3">
                                        <button type="button" onclick="openAddCityModal()" class="px-4 py-2 bg-primary text-white rounded-lg text-label-sm font-semibold hover:opacity-90 transition-all border-0 cursor-pointer">
                                            + Tambah Kota Sekarang
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($cityRates->hasPages())
                <div class="p-4 border-t border-outline-variant/30">
                    {{ $cityRates->links() }}
                </div>
            @endif
        </div>

    @else
        <!-- Section: Sub District & Global Rates Editor -->
        <div class="bg-white rounded-xl shadow-sm border border-outline-variant/30 p-6 mb-6">
            <div class="flex items-center gap-2 mb-4">
                <span class="material-symbols-outlined text-primary text-[24px]">pin_drop</span>
                <h2 class="font-headline-md text-headline-md text-on-surface font-semibold">Pilih Wilayah / Sub District</h2>
            </div>
            <div class="w-full md:w-1/2">
                <select id="select-sub-district" class="w-full px-3 py-2 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary/20 focus:outline-none bg-white select2-enable">
                    <option value="">Semua Wilayah (Tarif Global / Default Rate)</option>
                    @foreach($subDistricts as $subDistrict)
                        <option value="{{ $subDistrict->id }}" {{ $subDistrictId == $subDistrict->id ? 'selected' : '' }}>{{ $subDistrict->sub_district }} ({{ $subDistrict->district }} - {{ $subDistrict->province }})</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-outline-variant/30 overflow-hidden">
            <div class="p-4 border-b border-outline-variant flex flex-col sm:flex-row gap-4 justify-between items-center bg-surface-gray">
                <h2 class="font-headline-md text-headline-md text-on-surface font-semibold flex items-center gap-2">
                    <span class="material-symbols-outlined text-primary text-[22px]">edit_note</span> 
                    Tarif Wilayah: <span class="text-primary font-bold">{{ $subDistrictId ? 'Sub District Terpilih' : 'Global / Default' }}</span>
                </h2>
                <div class="flex items-center gap-3">
                    <span class="text-label-sm text-on-surface-variant bg-white px-3 py-1.5 rounded-lg border border-outline-variant/30 flex items-center gap-1">
                        <span class="w-2 h-2 rounded-full bg-success"></span> Tersimpan otomatis saat blur / change
                    </span>
                    <button type="button" onclick="openGuideModal()" class="flex items-center gap-1.5 px-3 py-1.5 bg-primary/10 hover:bg-primary text-primary hover:text-white rounded-lg text-label-sm font-semibold transition-all border-0 cursor-pointer">
                        <span class="material-symbols-outlined text-[16px]">help</span> Petunjuk
                    </button>
                </div>
            </div>
            
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse" id="subDistrictRatesTable">
                    <thead>
                        <tr class="bg-surface-gray border-b border-outline-variant/30">
                            <th class="px-gutter py-3 font-label-md text-label-md text-on-surface-variant uppercase tracking-wider w-1/4">Courier</th>
                            <th class="px-gutter py-3 font-label-md text-label-md text-on-surface-variant uppercase tracking-wider w-1/5">Service Type</th>
                            <th class="px-gutter py-3 font-label-md text-label-md text-on-surface-variant uppercase tracking-wider w-1/5">Tarif Dasar (Rp)</th>
                            <th class="px-gutter py-3 font-label-md text-label-md text-on-surface-variant uppercase tracking-wider w-1/5">Tambahan/Kg (Rp)</th>
                            <th class="px-gutter py-3 font-label-md text-label-md text-on-surface-variant uppercase tracking-wider w-16 text-center">Aktif</th>
                            <th class="px-4 py-3 font-label-md text-label-md text-on-surface-variant uppercase tracking-wider w-12 text-center"></th>
                            <th class="px-gutter py-3 font-label-md text-label-md text-on-surface-variant uppercase tracking-wider text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-outline-variant/20">
                        @foreach($courierRates as $rate)
                        <tr class="hover:bg-surface-container/30 transition-colors {{ !$rate['is_active'] || $rate['is_new'] ? 'opacity-60' : '' }}" 
                            data-rate-id="{{ $rate['id'] }}" 
                            data-courier-id="{{ $rate['courier_id'] }}">
                            
                            <td class="px-gutter py-4 font-body-md text-body-md text-on-surface font-semibold">
                                <div class="flex items-center justify-between gap-2 pr-4">
                                    <span>{{ $rate['courier_name'] }}</span>
                                    <button type="button" 
                                            onclick="addNewServiceRow(this, '{{ $rate['courier_id'] }}', '{{ $rate['courier_name'] }}')"
                                            class="px-2 py-1 bg-primary/10 hover:bg-primary text-primary hover:text-white rounded-lg text-[10px] font-semibold flex items-center gap-1 transition-all cursor-pointer border-0">
                                        <span class="material-symbols-outlined text-[12px] font-bold">add</span> Service
                                    </button>
                                </div>
                            </td>

                            <td class="px-gutter py-4">
                                <select class="input-type w-full px-2 py-1.5 border border-outline-variant rounded-lg text-body-md bg-white focus:ring-2 focus:ring-primary/20 focus:outline-none">
                                    <option value="1" {{ $rate['type'] == 1 ? 'selected' : '' }}>Regular</option>
                                    <option value="2" {{ $rate['type'] == 2 ? 'selected' : '' }}>Express</option>
                                    <option value="3" {{ $rate['type'] == 3 ? 'selected' : '' }}>Same Day</option>
                                    <option value="4" {{ $rate['type'] == 4 ? 'selected' : '' }}>Instant</option>
                                </select>
                            </td>

                            <td class="px-gutter py-4">
                                <input type="number" class="input-price w-full px-3 py-1.5 border border-outline-variant rounded-lg text-body-md bg-white focus:ring-2 focus:ring-primary/20 focus:outline-none" 
                                       value="{{ $rate['price'] }}" 
                                       min="0" 
                                       placeholder="enter fee (leave blank to delete)">
                            </td>

                            <td class="px-gutter py-4">
                                <input type="number" class="input-additional-price w-full px-3 py-1.5 border border-outline-variant rounded-lg text-body-md bg-white focus:ring-2 focus:ring-primary/20 focus:outline-none" 
                                       value="{{ $rate['additional_price_per_kg'] ?? 0 }}" 
                                       min="0" 
                                       placeholder="0 (flat rate)">
                            </td>

                            <td class="px-gutter py-4 text-center">
                                <input type="checkbox" class="input-is-active w-4 h-4 text-primary border-outline-variant rounded focus:ring-primary" {{ $rate['is_active'] ? 'checked' : '' }}>
                            </td>

                            <td class="px-4 py-4 text-center status-indicator h-[38px] flex items-center justify-center">
                                @if($rate['id'] && !$rate['is_active'])
                                    <span class="material-symbols-outlined text-neutral-400 text-[18px]" title="Inactive">block</span>
                                @endif
                            </td>

                            <td class="px-gutter py-4 text-center action-cell">
                                @if($rate['id'])
                                    <form method="POST" action="{{ route('shipping-addresses.destroy', $rate['id']) }}" class="inline-block" onsubmit="return confirm('Hapus tarif pengiriman ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-1 text-danger hover:bg-danger/5 rounded transition-all border-0 bg-transparent cursor-pointer">
                                            <span class="material-symbols-outlined text-[20px]">delete</span>
                                        </button>
                                    </form>
                                @else
                                    <span class="text-label-xs text-on-surface-variant opacity-60">Not Saved</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    <!-- Add/Edit City Scope Modal for Kurir Toko -->
    <div id="cityScopeModal" class="fixed inset-0 z-[9999] hidden flex items-center justify-center bg-black/50 backdrop-blur-sm transition-all duration-300 opacity-0">
        <div class="bg-white rounded-2xl max-w-lg w-full mx-4 shadow-xl border border-outline-variant/30 overflow-hidden transform scale-95 transition-all duration-300">
            <form id="cityScopeForm" method="POST" action="{{ route('shipping-addresses.store') }}">
                @csrf
                <input type="hidden" name="_method" id="cityModalMethod" value="POST">
                <input type="hidden" name="tab" value="toko">

                <div class="p-5 border-b border-outline-variant/30 flex justify-between items-center bg-surface-gray">
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-outlined text-primary text-[24px]">location_city</span>
                        <h3 id="cityModalTitle" class="font-headline-md text-headline-md text-on-surface font-bold">Tambah Jangkauan Kota Kurir Toko</h3>
                    </div>
                    <button type="button" onclick="closeCityModal()" class="text-on-surface-variant hover:text-on-surface p-1 rounded-full hover:bg-surface-container/50 transition-colors border-0 bg-transparent cursor-pointer">
                        <span class="material-symbols-outlined text-[20px]">close</span>
                    </button>
                </div>

                <div class="p-6 space-y-4">
                    <div class="space-y-1.5">
                        <label class="block text-label-sm font-medium text-on-surface-variant">Kota / Kabupaten Tujuan <span class="text-danger">*</span></label>
                        <select name="city_id" id="modalCityId" class="w-full px-3 py-2 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary/20 focus:outline-none bg-white select2-enable" required>
                            <option value="">-- Pilih Kota / Kabupaten --</option>
                            @foreach($cities as $c)
                                <option value="{{ $c->id }}">{{ $c->name }} ({{ $c->province->name ?? '' }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="space-y-1.5">
                        <label class="block text-label-sm font-medium text-on-surface-variant">Kurir <span class="text-danger">*</span></label>
                        <select name="courier_id" id="modalCourierId" class="w-full px-3 py-2 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary/20 focus:outline-none bg-white select2-enable" required>
                            @foreach($tokoCouriers as $cr)
                                <option value="{{ $cr->id }}">{{ $cr->name }} ({{ $cr->courier_type }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="space-y-1.5">
                        <label class="block text-label-sm font-medium text-on-surface-variant">Tipe Layanan <span class="text-danger">*</span></label>
                        <select name="type" id="modalType" class="w-full px-3 py-2 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary/20 focus:outline-none bg-white" required>
                            <option value="1">Regular</option>
                            <option value="2">Express</option>
                            <option value="3">Same Day</option>
                            <option value="4">Instant</option>
                        </select>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="space-y-1.5">
                            <label class="block text-label-sm font-medium text-on-surface-variant">Tarif Dasar / Flat (Rp) <span class="text-danger">*</span></label>
                            <input type="number" name="price" id="modalPrice" required min="0" placeholder="e.g. 25000" class="w-full px-3 py-2 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary/20 focus:outline-none">
                            <span class="text-[11px] text-on-surface-variant block">Tarif trip dasar / 1 kg pertama</span>
                        </div>

                        <div class="space-y-1.5">
                            <label class="block text-label-sm font-medium text-on-surface-variant">Biaya Tambahan / Kg (Rp)</label>
                            <input type="number" name="additional_price_per_kg" id="modalAdditionalPrice" min="0" value="0" placeholder="0" class="w-full px-3 py-2 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary/20 focus:outline-none">
                            <span class="text-[11px] text-on-surface-variant block">Diisi 0 jika tarif flat</span>
                        </div>
                    </div>

                    <div class="flex items-center gap-2 pt-2">
                        <input type="checkbox" name="is_active" id="modalIsActive" value="1" checked class="w-4 h-4 text-primary border-outline-variant rounded focus:ring-primary">
                        <label for="modalIsActive" class="text-label-sm font-medium text-on-surface">Aktifkan Jangkauan Kota Ini</label>
                    </div>
                </div>

                <div class="p-4 border-t border-outline-variant/30 flex justify-end gap-2 bg-surface-gray">
                    <button type="button" onclick="closeCityModal()" class="px-4 py-2 bg-surface-container-lowest border border-outline-variant text-secondary rounded-lg font-label-md hover:bg-surface-container transition-all cursor-pointer">
                        Batal
                    </button>
                    <button type="submit" class="px-5 py-2 bg-primary text-white rounded-lg font-label-md hover:opacity-90 transition-all cursor-pointer">
                        Simpan Jangkauan
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Guide Modal -->
    <div id="guideModal" class="fixed inset-0 z-[9999] hidden flex items-center justify-center bg-black/50 backdrop-blur-sm transition-all duration-300 opacity-0">
        <div class="bg-white rounded-2xl max-w-md w-full mx-4 shadow-xl border border-outline-variant/30 overflow-hidden transform scale-95 transition-all duration-300">
            <div class="p-6 border-b border-outline-variant/30 flex justify-between items-center bg-surface-gray">
                <div class="flex items-center gap-2">
                    <span class="material-symbols-outlined text-primary text-[24px]">help</span>
                    <h3 class="font-headline-md text-headline-md text-on-surface font-bold">Petunjuk Pengisian Tarif</h3>
                </div>
                <button type="button" onclick="closeGuideModal()" class="text-on-surface-variant hover:text-on-surface p-1 rounded-full hover:bg-surface-container/50 transition-colors border-0 bg-transparent cursor-pointer">
                    <span class="material-symbols-outlined text-[20px]">close</span>
                </button>
            </div>
            <div class="p-6 space-y-4 text-body-md text-on-surface-variant">
                <p><strong>1. Kurir Toko (Scope Kota):</strong> Digunakan untuk armada toko pengiriman lokal. Pengiriman hanya menjangkau Kota/Kabupaten yang telah Anda tambahkan.</p>
                <p><strong>2. Tarif Hybrid:</strong> Masukkan <em>Tarif Dasar</em> untuk pengiriman 1 kg pertama. Jika produk berat/dimensi lebih dari 1 kg, sistem menambahkan <em>Biaya Tambahan / Kg</em>. Jika tambahan adalah 0, tarif bersifat Flat.</p>
                <p><strong>3. Tarif Kecamatan / Kelurahan:</strong> Digunakan untuk penyesuaian tarif kurir ekspedisi atau custom per kelurahan tertentu.</p>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Location dropdown reload
        const subDistrictSelect = document.getElementById('select-sub-district');
        if (subDistrictSelect) {
            $(subDistrictSelect).on('change', function() {
                const subDistrictId = this.value;
                let url = '{{ route("shipping-addresses.index") }}?tab=sub_district';
                if (subDistrictId) {
                    url += '&sub_district_id=' + encodeURIComponent(subDistrictId);
                }
                window.location.href = url;
            });
        }

        const tableBody = document.querySelector('#subDistrictRatesTable tbody');
        if (tableBody) {
            tableBody.addEventListener('blur', function(e) {
                if (e.target.classList.contains('input-price') || e.target.classList.contains('input-additional-price')) {
                    saveRateInline(e.target);
                }
            }, true);

            tableBody.addEventListener('keydown', function(e) {
                if ((e.target.classList.contains('input-price') || e.target.classList.contains('input-additional-price')) && e.key === 'Enter') {
                    e.preventDefault();
                    e.target.blur();
                }
            });

            tableBody.addEventListener('change', function(e) {
                if (e.target.classList.contains('input-type') || e.target.classList.contains('input-is-active')) {
                    saveRateInline(e.target);
                }
            });

            tableBody.addEventListener('click', function(e) {
                const removeBtn = e.target.closest('.btn-remove-row');
                if (removeBtn) {
                    const row = removeBtn.closest('tr');
                    row.remove();
                }
            });
        }
    });

    function addNewServiceRow(button, courierId, courierName) {
        const clickedRow = button.closest('tr');
        const newRow = document.createElement('tr');
        newRow.className = 'hover:bg-surface-container/30 transition-colors opacity-60';
        newRow.setAttribute('data-courier-id', courierId);
        newRow.setAttribute('data-rate-id', '');
        
        newRow.innerHTML = `
            <td class="px-gutter py-4 font-body-md text-body-md text-on-surface font-semibold">
                <div class="flex items-center justify-between gap-2 pr-4">
                    <span class="opacity-50">${courierName}</span>
                    <span class="text-label-xs bg-primary/10 text-primary px-1.5 py-0.5 rounded font-normal">New</span>
                </div>
            </td>
            <td class="px-gutter py-4">
                <select class="input-type w-full px-2 py-1.5 border border-outline-variant rounded-lg text-body-md bg-white focus:ring-2 focus:ring-primary/20 focus:outline-none">
                    <option value="1">Regular</option>
                    <option value="2">Express</option>
                    <option value="3">Same Day</option>
                    <option value="4">Instant</option>
                </select>
            </td>
            <td class="px-gutter py-4">
                <input type="number" class="input-price w-full px-3 py-1.5 border border-outline-variant rounded-lg text-body-md bg-white focus:ring-2 focus:ring-primary/20 focus:outline-none" placeholder="Tarif dasar">
            </td>
            <td class="px-gutter py-4">
                <input type="number" class="input-additional-price w-full px-3 py-1.5 border border-outline-variant rounded-lg text-body-md bg-white focus:ring-2 focus:ring-primary/20 focus:outline-none" value="0" placeholder="0">
            </td>
            <td class="px-gutter py-4 text-center">
                <input type="checkbox" checked class="input-is-active w-4 h-4 text-primary border-outline-variant rounded focus:ring-primary">
            </td>
            <td class="px-4 py-4 text-center status-indicator h-[38px] flex items-center justify-center"></td>
            <td class="px-gutter py-4 text-center action-cell">
                <button type="button" class="btn-remove-row p-1 text-danger hover:bg-danger/5 rounded transition-all border-0 bg-transparent cursor-pointer">
                    <span class="material-symbols-outlined text-[20px]">delete</span>
                </button>
            </td>
        `;
        
        clickedRow.after(newRow);
        newRow.querySelector('.input-price').focus();
    }

    function saveRateInline(element) {
        const parentRow = element.closest('tr');
        let rateId = parentRow.getAttribute('data-rate-id') || null;
        const courierId = parentRow.getAttribute('data-courier-id');
        const subDistrictId = $('#select-sub-district').val() || null;

        const typeSelect = parentRow.querySelector('.input-type');
        const priceInput = parentRow.querySelector('.input-price');
        const additionalPriceInput = parentRow.querySelector('.input-additional-price');
        const isActiveCheckbox = parentRow.querySelector('.input-is-active');
        const statusIndicator = parentRow.querySelector('.status-indicator');

        const type = typeSelect ? typeSelect.value : 1;
        const price = priceInput ? priceInput.value : '';
        const additionalPrice = additionalPriceInput ? additionalPriceInput.value : 0;
        const sortOrder = 0;
        const isActive = isActiveCheckbox && isActiveCheckbox.checked ? 1 : 0;

        if (!rateId && (price === null || price === '')) {
            return;
        }

        statusIndicator.innerHTML = `
            <div class="w-4 h-4 border-2 border-primary border-t-transparent rounded-full animate-spin"></div>
        `;

        fetch('{{ route("shipping-addresses.save-inline") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                id: rateId,
                sub_district_id: subDistrictId,
                city_id: null,
                courier_id: courierId,
                type: type,
                price: price,
                additional_price_per_kg: additionalPrice,
                sort_order: sortOrder,
                is_active: isActive
            })
        })
        .then(response => {
            if (!response.ok) return response.json().then(err => { throw err; });
            return response.json();
        })
        .then(data => {
            if (data.success) {
                if (data.deleted) {
                    parentRow.style.opacity = '0.5';
                    statusIndicator.innerHTML = `<span class="material-symbols-outlined text-danger text-[18px]">delete_sweep</span>`;
                    parentRow.setAttribute('data-rate-id', '');
                } else {
                    parentRow.style.opacity = isActive ? '1' : '0.6';
                    parentRow.classList.remove('opacity-60');
                    if (data.data && data.data.id) {
                        parentRow.setAttribute('data-rate-id', data.data.id);
                        const actionCell = parentRow.querySelector('.action-cell');
                        if (actionCell) {
                            actionCell.innerHTML = `
                                <form method="POST" action="/shipping-addresses/${data.data.id}" class="inline-block" onsubmit="return confirm('Hapus tarif pengiriman ini?')">
                                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                    <input type="hidden" name="_method" value="DELETE">
                                    <button type="submit" class="p-1 text-danger hover:bg-danger/5 rounded transition-all border-0 bg-transparent cursor-pointer">
                                        <span class="material-symbols-outlined text-[20px]">delete</span>
                                    </button>
                                </form>
                            `;
                        }
                    }
                    statusIndicator.innerHTML = `<span class="material-symbols-outlined text-success text-[18px]">check_circle</span>`;
                    setTimeout(() => { statusIndicator.innerHTML = ''; }, 2000);
                }
            } else {
                statusIndicator.innerHTML = `<span class="material-symbols-outlined text-danger text-[18px]" title="Error: ${data.message}">error</span>`;
            }
        })
        .catch(error => {
            console.error('Error saving inline rate:', error);
            statusIndicator.innerHTML = `<span class="material-symbols-outlined text-danger text-[18px]" title="Error saving rate">error</span>`;
        });
    }

    // Modal Add / Edit City Scope
    function openAddCityModal() {
        document.getElementById('cityModalTitle').textContent = 'Tambah Jangkauan Kota Kurir Toko';
        document.getElementById('cityScopeForm').action = '{{ route("shipping-addresses.store") }}';
        document.getElementById('cityModalMethod').value = 'POST';
        $('#modalCityId').val('').trigger('change');
        $('#modalPrice').val('');
        $('#modalAdditionalPrice').val('0');
        $('#modalIsActive').prop('checked', true);

        const modal = document.getElementById('cityScopeModal');
        modal.classList.remove('hidden');
        setTimeout(() => {
            modal.classList.remove('opacity-0');
            modal.querySelector('.transform').classList.remove('scale-95');
        }, 10);
    }

    function openEditCityModal(id, cityId, courierId, type, price, additionalPrice, isActive) {
        document.getElementById('cityModalTitle').textContent = 'Edit Jangkauan Kota Kurir Toko';
        document.getElementById('cityScopeForm').action = '/shipping-addresses/' + id;
        document.getElementById('cityModalMethod').value = 'PUT';
        $('#modalCityId').val(cityId).trigger('change');
        $('#modalCourierId').val(courierId).trigger('change');
        $('#modalType').val(type);
        $('#modalPrice').val(price);
        $('#modalAdditionalPrice').val(additionalPrice || 0);
        $('#modalIsActive').prop('checked', isActive);

        const modal = document.getElementById('cityScopeModal');
        modal.classList.remove('hidden');
        setTimeout(() => {
            modal.classList.remove('opacity-0');
            modal.querySelector('.transform').classList.remove('scale-95');
        }, 10);
    }

    function closeCityModal() {
        const modal = document.getElementById('cityScopeModal');
        modal.classList.add('opacity-0');
        modal.querySelector('.transform').classList.add('scale-95');
        setTimeout(() => { modal.classList.add('hidden'); }, 300);
    }

    function openGuideModal() {
        const modal = document.getElementById('guideModal');
        modal.classList.remove('hidden');
        setTimeout(() => {
            modal.classList.remove('opacity-0');
            modal.querySelector('.transform').classList.remove('scale-95');
        }, 10);
    }

    function closeGuideModal() {
        const modal = document.getElementById('guideModal');
        modal.classList.add('opacity-0');
        modal.querySelector('.transform').classList.add('scale-95');
        setTimeout(() => { modal.classList.add('hidden'); }, 300);
    }
</script>
@endpush
