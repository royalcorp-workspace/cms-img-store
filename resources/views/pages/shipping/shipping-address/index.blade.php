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
            <span class="material-symbols-outlined text-[18px]">local_shipping</span>
            Tarif Ekspedisi (Scope Kecamatan / Kelurahan)
        </a>
    </div>

    @if($activeTab === 'toko')
        <!-- Section: Kurir Toko City Scope -->
        <div class="bg-blue-50/70 border border-blue-200 rounded-xl p-4 mb-6 text-xs text-blue-900 flex items-start gap-3">
            <span class="material-symbols-outlined text-blue-600 text-[22px] mt-0.5 shrink-0">info</span>
            <div class="space-y-1">
                <p class="font-bold text-sm text-blue-950">Pengaturan Scope Wilayah & Tarif Kurir Toko :</p>
                <p>1. <strong>Scope Kota:</strong> Kurir Toko hanya menjangkau Kota/Kabupaten yang didaftarkan pada tabel di bawah ini. Kota di luar daftar otomatis berstatus <em>Di Luar Jangkauan</em> pada saat Checkout.</p>
                <p>2. <strong>Perhitungan Ongkir:</strong> Untuk produk dimensi/berat, ongkir dihitung dari <strong>Tarif Dasar Kota</strong> + <strong>Biaya Tambahan / Kg</strong> (jika berat > 1 kg). Jika Biaya Tambahan/Kg bernilai <code>0</code>, maka ongkir berlaku <strong>Flat Rate</strong>. Produk bertarif tetap (fixed) akan ditambahkan sesuai tarif tetapnya.</p>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-outline-variant/30 overflow-hidden mb-8">
            <div class="p-4 border-b border-outline-variant flex flex-col sm:flex-row gap-4 justify-between items-center bg-surface-gray">
                <form method="GET" class="flex flex-wrap items-center gap-3 w-full sm:w-auto">
                    <input type="hidden" name="tab" value="toko">
                    <div class="w-48">
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari kota / provinsi..." class="w-full px-3 py-2 border border-outline-variant rounded-lg text-body-md bg-white">
                    </div>
                    <div class="w-60">
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
                            <option value="">Semua Kurir Toko</option>
                            @foreach($tokoCouriers as $cr)
                                <option value="{{ $cr->id }}" {{ request('courier_id') == $cr->id ? 'selected' : '' }}>
                                    {{ $cr->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="w-36">
                        <select name="status" class="w-full px-3 py-2 border border-outline-variant rounded-lg text-body-md bg-white">
                            <option value="">Semua Status</option>
                            <option value="1" {{ request('status') === '1' ? 'selected' : '' }}>Aktif</option>
                            <option value="0" {{ request('status') === '0' ? 'selected' : '' }}>Nonaktif</option>
                        </select>
                    </div>
                    <button type="submit" class="px-4 py-2 bg-primary text-white rounded-lg text-label-md font-semibold hover:opacity-90 transition-all shadow-sm">
                        Filter
                    </button>
                    @if(request('city_id') || request('courier_id') || request('search') || request('status') !== null)
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
                                    <span class="ml-1.5 px-2 py-0.5 rounded text-[10px] bg-amber-100 text-amber-800 font-semibold">Toko</span>
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
        <!-- Section: Tarif Ekspedisi (Scope Kecamatan / Kelurahan) - Comprehensive List -->
        <div class="bg-indigo-50/70 border border-indigo-200 rounded-xl p-4 mb-6 text-xs text-indigo-900 flex items-start gap-3">
            <span class="material-symbols-outlined text-indigo-600 text-[22px] mt-0.5 shrink-0">local_shipping</span>
            <div class="space-y-1">
                <p class="font-bold text-sm text-indigo-950">Daftar Tarif Kurir Ekspedisi (Kecamatan / Kelurahan) :</p>
                <p>1. <strong>Scope Kecamatan/Kelurahan:</strong> Kurir Ekspedisi (JNE, SiCepat, J&T, TIKI, Pos Indonesia) dihitung berdasarkan data alamat tujuan pembeli per Kelurahan / Kecamatan.</p>
                <p>2. <strong>Perhitungan Ongkir:</strong> Dihitung dari <strong>Tarif Dasar</strong> + <strong>Biaya Tambahan / Kg</strong> bila berat pesanan melebihi 1 kg. Jika tarif kelurahan tidak ditemukan, sistem akan menggunakan tarif kecamatan atau estimasi default.</p>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-outline-variant/30 overflow-hidden mb-8">
            <div class="p-4 border-b border-outline-variant flex flex-col lg:flex-row gap-4 justify-between items-center bg-surface-gray">
                <form method="GET" class="flex flex-wrap items-center gap-3 w-full lg:w-auto">
                    <input type="hidden" name="tab" value="sub_district">
                    
                    <!-- 1. Filter Provinsi -->
                    <div class="w-48">
                        <select name="province_id" id="filterExpeditionProvince" class="w-full px-3 py-2 border border-outline-variant rounded-lg text-body-md bg-white select2-enable">
                            <option value="">Semua Provinsi</option>
                            @foreach($provinces as $prov)
                                <option value="{{ $prov->id }}" {{ (string)request('province_id') === (string)$prov->id ? 'selected' : '' }}>
                                    {{ $prov->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- 2. Filter Kota / Kab -->
                    <div class="w-52">
                        <select name="city_id" id="filterExpeditionCity" class="w-full px-3 py-2 border border-outline-variant rounded-lg text-body-md bg-white select2-enable">
                            <option value="">Semua Kota / Kabupaten</option>
                            @foreach($cities as $c)
                                <option value="{{ $c->id }}" {{ (string)request('city_id') === (string)$c->id ? 'selected' : '' }}>
                                    {{ $c->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- 3. Filter Kelurahan -->
                    <div class="w-56">
                        <select name="sub_district_id" id="filterExpeditionSubDistrict" class="w-full px-3 py-2 border border-outline-variant rounded-lg text-body-md bg-white select2-enable">
                            <option value="">Semua Kelurahan / Kec.</option>
                            @foreach($subDistricts as $sd)
                                <option value="{{ $sd->id }}" {{ (string)request('sub_district_id') === (string)$sd->id ? 'selected' : '' }}>
                                    {{ $sd->sub_district }}{{ $sd->district ? ', Kec. ' . $sd->district : '' }}{{ $sd->postal_code ? ' (' . $sd->postal_code . ')' : '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- 4. Filter Kurir Ekspedisi -->
                    <div class="w-44">
                        <select name="courier_id" class="w-full px-3 py-2 border border-outline-variant rounded-lg text-body-md bg-white select2-enable">
                            <option value="">Semua Kurir Ekspedisi</option>
                            @foreach($expedisiCouriers as $cr)
                                <option value="{{ $cr->id }}" {{ (string)request('courier_id') === (string)$cr->id ? 'selected' : '' }}>
                                    {{ $cr->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- 5. Filter Status -->
                    <div class="w-32">
                        <select name="status" class="w-full px-3 py-2 border border-outline-variant rounded-lg text-body-md bg-white">
                            <option value="">Semua Status</option>
                            <option value="1" {{ request('status') === '1' ? 'selected' : '' }}>Aktif</option>
                            <option value="0" {{ request('status') === '0' ? 'selected' : '' }}>Nonaktif</option>
                        </select>
                    </div>

                    <!-- 6. Cari kata kunci -->
                    <div class="w-48">
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari kelurahan, kec, kode pos..." class="w-full px-3 py-2 border border-outline-variant rounded-lg text-body-md bg-white">
                    </div>

                    <button type="submit" class="px-4 py-2 bg-primary text-white rounded-lg text-label-md font-semibold hover:opacity-90 transition-all shadow-sm">
                        Filter
                    </button>
                    @if(request('province_id') || request('city_id') || request('sub_district_id') || request('courier_id') || request('search') || request('status') !== null)
                        <a href="{{ route('shipping-addresses.index', ['tab' => 'sub_district']) }}" class="text-xs text-on-surface-variant hover:text-primary underline">
                            Reset Filter
                        </a>
                    @endif
                </form>

                <button type="button" onclick="openAddExpeditionModal()" class="flex items-center gap-2 px-4 py-2 bg-primary text-white rounded-lg text-label-md font-semibold hover:opacity-90 transition-all shadow-sm shrink-0 border-0 cursor-pointer">
                    <span class="material-symbols-outlined text-[18px]">add_location_alt</span>
                    Tambah Tarif Ekspedisi
                </button>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-surface-gray border-b border-outline-variant/30">
                            <th class="px-gutter py-3.5 font-label-md text-label-md text-on-surface-variant uppercase tracking-wider">Wilayah Tujuan</th>
                            <th class="px-gutter py-3.5 font-label-md text-label-md text-on-surface-variant uppercase tracking-wider">Kota / Kabupaten</th>
                            <th class="px-gutter py-3.5 font-label-md text-label-md text-on-surface-variant uppercase tracking-wider">Kurir Ekspedisi</th>
                            <th class="px-gutter py-3.5 font-label-md text-label-md text-on-surface-variant uppercase tracking-wider">Layanan</th>
                            <th class="px-gutter py-3.5 font-label-md text-label-md text-on-surface-variant uppercase tracking-wider">Tarif Dasar (Rp)</th>
                            <th class="px-gutter py-3.5 font-label-md text-label-md text-on-surface-variant uppercase tracking-wider">Biaya Tambahan/Kg</th>
                            <th class="px-gutter py-3.5 font-label-md text-label-md text-on-surface-variant uppercase tracking-wider text-center">Status</th>
                            <th class="px-gutter py-3.5 font-label-md text-label-md text-on-surface-variant uppercase tracking-wider text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-outline-variant/20">
                        @forelse($subDistrictRates as $rate)
                            <tr class="hover:bg-surface-container/30 transition-colors {{ !$rate->is_active ? 'opacity-60' : '' }}" data-rate-id="{{ $rate->id }}">
                                <td class="px-gutter py-4 font-body-md text-on-surface font-semibold">
                                    @if($rate->subDistrict)
                                        {{ $rate->subDistrict->sub_district }}
                                        <span class="block text-xs text-on-surface-variant font-normal">Kec. {{ $rate->subDistrict->district ?? '-' }}</span>
                                        @if(!empty($rate->subDistrict->postal_code))
                                            <span class="block text-[11px] text-on-surface-variant/70">Kode Pos: {{ $rate->subDistrict->postal_code }}</span>
                                        @endif
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[11px] bg-indigo-50 text-indigo-700 font-semibold">
                                            <span class="material-symbols-outlined text-[13px]">public</span> Seluruh Wilayah Kota / Kab
                                        </span>
                                        <span class="block text-[11px] text-on-surface-variant font-normal mt-0.5">Tarif Standar (1 Indonesia)</span>
                                    @endif
                                </td>
                                <td class="px-gutter py-4 font-body-md text-on-surface">
                                    @if($rate->subDistrict)
                                        {{ $rate->subDistrict->city->name ?? $rate->subDistrict->district ?? '-' }}
                                        <span class="block text-xs text-on-surface-variant font-normal">{{ $rate->subDistrict->city->province->name ?? $rate->subDistrict->province ?? '' }}</span>
                                    @else
                                        <span class="font-semibold">{{ $rate->city->name ?? '-' }}</span>
                                        <span class="block text-xs text-on-surface-variant font-normal">{{ $rate->city->province->name ?? '' }}</span>
                                    @endif
                                </td>
                                <td class="px-gutter py-4 font-body-md text-on-surface">
                                    <span class="font-medium">{{ $rate->courier->name ?? 'N/A' }}</span>
                                    <span class="ml-1.5 px-2 py-0.5 rounded text-[10px] bg-blue-100 text-blue-800 font-semibold">Ekspedisi</span>
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
                                        @php
                                            $subDistrictLabel = $rate->subDistrict 
                                                ? (($rate->subDistrict->sub_district ?? '') . ', Kec. ' . ($rate->subDistrict->district ?? '') . ' (' . ($rate->subDistrict->postal_code ?? '') . ')')
                                                : (($rate->city->name ?? '') . ' (Tingkat Kota)');
                                        @endphp
                                        <button type="button" onclick="openEditExpeditionModal('{{ $rate->id }}', '{{ $rate->sub_district_id }}', '{{ addslashes($subDistrictLabel) }}', '{{ $rate->courier_id }}', '{{ $rate->type }}', '{{ $rate->price }}', '{{ $rate->additional_price_per_kg }}', {{ $rate->is_active ? 'true' : 'false' }})" class="p-1.5 text-on-surface-variant hover:text-primary rounded-lg hover:bg-primary/5 transition-all border-0 bg-transparent cursor-pointer" title="Edit">
                                            <span class="material-symbols-outlined text-[18px]">edit</span>
                                        </button>
                                        <form method="POST" action="{{ route('shipping-addresses.destroy', $rate->id) }}" class="inline-block" onsubmit="return confirm('Hapus tarif ekspedisi untuk wilayah ini?')">
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
                                <td colspan="8" class="px-gutter py-12 text-center text-on-surface-variant">
                                    <span class="material-symbols-outlined text-[36px] text-on-surface-variant/40 block mb-2">local_shipping</span>
                                    Belum ada tarif ekspedisi yang didaftarkan.
                                    <div class="mt-3">
                                        <button type="button" onclick="openAddExpeditionModal()" class="px-4 py-2 bg-primary text-white rounded-lg text-label-sm font-semibold hover:opacity-90 transition-all border-0 cursor-pointer">
                                            + Tambah Tarif Ekspedisi
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($subDistrictRates->hasPages())
                <div class="p-4 border-t border-outline-variant/30">
                    {{ $subDistrictRates->links() }}
                </div>
            @endif
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
                        <label class="block text-label-sm font-medium text-on-surface-variant">Kurir Toko <span class="text-danger">*</span></label>
                        <select name="courier_id" id="modalCourierId" class="w-full px-3 py-2 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary/20 focus:outline-none bg-white select2-enable" required>
                            @foreach($tokoCouriers as $cr)
                                <option value="{{ $cr->id }}">{{ $cr->name }}</option>
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

    <!-- Add/Edit Expedition Rate Modal for Kecamatan / Kelurahan -->
    <div id="expeditionRateModal" class="fixed inset-0 z-[9999] hidden flex items-center justify-center bg-black/50 backdrop-blur-sm transition-all duration-300 opacity-0">
        <div class="bg-white rounded-2xl max-w-lg w-full mx-4 shadow-xl border border-outline-variant/30 overflow-hidden transform scale-95 transition-all duration-300">
            <form id="expeditionRateForm" method="POST" action="{{ route('shipping-addresses.store') }}">
                @csrf
                <input type="hidden" name="_method" id="expeditionModalMethod" value="POST">
                <input type="hidden" name="tab" value="sub_district">
                <input type="hidden" name="city_id" id="modalExpeditionCityId" value="">

                <div class="p-5 border-b border-outline-variant/30 flex justify-between items-center bg-surface-gray">
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-outlined text-primary text-[24px]">local_shipping</span>
                        <h3 id="expeditionModalTitle" class="font-headline-md text-headline-md text-on-surface font-bold">Tambah Tarif Ekspedisi</h3>
                    </div>
                    <button type="button" onclick="closeExpeditionModal()" class="text-on-surface-variant hover:text-on-surface p-1 rounded-full hover:bg-surface-container/50 transition-colors border-0 bg-transparent cursor-pointer">
                        <span class="material-symbols-outlined text-[20px]">close</span>
                    </button>
                </div>

                <div class="p-6 space-y-4">
                    <div class="space-y-1.5">
                        <label class="block text-label-sm font-medium text-on-surface-variant">Wilayah Kecamatan / Kelurahan <span class="text-danger">*</span></label>
                        <select name="sub_district_id" id="modalExpeditionSubDistrictId" class="w-full px-3 py-2 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary/20 focus:outline-none bg-white" required>
                            <option value="">-- Ketik nama kelurahan / kecamatan --</option>
                        </select>
                        <span class="text-[11px] text-on-surface-variant block">Ketik minimal 2 huruf untuk mencari wilayah (Kelurahan, Kecamatan, Kota, Kode Pos).</span>
                    </div>

                    <div class="space-y-1.5">
                        <label class="block text-label-sm font-medium text-on-surface-variant">Kurir Ekspedisi <span class="text-danger">*</span></label>
                        <select name="courier_id" id="modalExpeditionCourierId" class="w-full px-3 py-2 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary/20 focus:outline-none bg-white select2-enable" required>
                            <option value="">-- Pilih Kurir Ekspedisi --</option>
                            @foreach($expedisiCouriers as $cr)
                                <option value="{{ $cr->id }}">{{ $cr->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="space-y-1.5">
                        <label class="block text-label-sm font-medium text-on-surface-variant">Tipe Layanan <span class="text-danger">*</span></label>
                        <select name="type" id="modalExpeditionType" class="w-full px-3 py-2 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary/20 focus:outline-none bg-white" required>
                            <option value="1">Regular</option>
                            <option value="2">Express</option>
                            <option value="3">Same Day</option>
                            <option value="4">Instant</option>
                        </select>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="space-y-1.5">
                            <label class="block text-label-sm font-medium text-on-surface-variant">Tarif Dasar / Flat (Rp) <span class="text-danger">*</span></label>
                            <input type="number" name="price" id="modalExpeditionPrice" required min="0" placeholder="e.g. 10000" class="w-full px-3 py-2 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary/20 focus:outline-none">
                            <span class="text-[11px] text-on-surface-variant block">Tarif per 1 kg pertama</span>
                        </div>

                        <div class="space-y-1.5">
                            <label class="block text-label-sm font-medium text-on-surface-variant">Biaya Tambahan / Kg (Rp)</label>
                            <input type="number" name="additional_price_per_kg" id="modalExpeditionAdditionalPrice" min="0" value="0" placeholder="0" class="w-full px-3 py-2 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary/20 focus:outline-none">
                            <span class="text-[11px] text-on-surface-variant block">Diisi 0 jika tarif flat</span>
                        </div>
                    </div>

                    <div class="flex items-center gap-2 pt-2">
                        <input type="checkbox" name="is_active" id="modalExpeditionIsActive" value="1" checked class="w-4 h-4 text-primary border-outline-variant rounded focus:ring-primary">
                        <label for="modalExpeditionIsActive" class="text-label-sm font-medium text-on-surface">Aktifkan Tarif Ini</label>
                    </div>
                </div>

                <div class="p-4 border-t border-outline-variant/30 flex justify-end gap-2 bg-surface-gray">
                    <button type="button" onclick="closeExpeditionModal()" class="px-4 py-2 bg-surface-container-lowest border border-outline-variant text-secondary rounded-lg font-label-md hover:bg-surface-container transition-all cursor-pointer">
                        Batal
                    </button>
                    <button type="submit" class="px-5 py-2 bg-primary text-white rounded-lg font-label-md hover:opacity-90 transition-all cursor-pointer">
                        Simpan Tarif Ekspedisi
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize AJAX Select2 for SubDistrict in modal
        if (typeof $ !== 'undefined' && $.fn.select2) {
            $('#modalExpeditionSubDistrictId').select2({
                placeholder: 'Ketik minimal 2 huruf nama kelurahan/kecamatan...',
                allowClear: true,
                dropdownParent: $('#expeditionRateModal'),
                ajax: {
                    url: '{{ route("shipping-addresses.search-sub-districts") }}',
                    dataType: 'json',
                    delay: 250,
                    data: function(params) {
                        return { q: params.term };
                    },
                    processResults: function(data) {
                        return { results: data.results };
                    },
                    cache: true
                },
                minimumInputLength: 2
            });

            // -------------------------------------------------------------
            // Cascading Filter Ekspedisi: Provinsi -> Kota/Kab -> Kelurahan
            // -------------------------------------------------------------
            $('#filterExpeditionProvince').on('change', function() {
                var provId = $(this).val();
                var $citySelect = $('#filterExpeditionCity');
                var $subDistrictSelect = $('#filterExpeditionSubDistrict');

                $citySelect.empty().append('<option value="">Semua Kota / Kabupaten</option>');
                $subDistrictSelect.empty().append('<option value="">Semua Kelurahan / Kec.</option>');

                $.ajax({
                    url: '{{ route("shipping-addresses.cities-by-province") }}',
                    data: provId ? { province_id: provId } : {},
                    dataType: 'json',
                    success: function(cities) {
                        $.each(cities, function(idx, item) {
                            $citySelect.append(new Option(item.name, item.id));
                        });
                        $citySelect.val('').trigger('change.select2');
                        $subDistrictSelect.val('').trigger('change.select2');
                    }
                });
            });

            $('#filterExpeditionCity').on('change', function() {
                var cityId = $(this).val();
                var $subDistrictSelect = $('#filterExpeditionSubDistrict');

                $subDistrictSelect.empty().append('<option value="">Semua Kelurahan / Kec.</option>');

                if (cityId) {
                    $.ajax({
                        url: '{{ route("shipping-addresses.subdistricts-by-city") }}',
                        data: { city_id: cityId },
                        dataType: 'json',
                        success: function(subDistricts) {
                            $.each(subDistricts, function(idx, item) {
                                $subDistrictSelect.append(new Option(item.name, item.id));
                            });
                            $subDistrictSelect.val('').trigger('change.select2');
                        }
                    });
                } else {
                    $subDistrictSelect.val('').trigger('change.select2');
                }
            });
        }
    });

    // Modal Add / Edit City Scope for Kurir Toko
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

    // Modal Add / Edit Expedition Rate for Kecamatan / Kelurahan
    function openAddExpeditionModal() {
        document.getElementById('expeditionModalTitle').textContent = 'Tambah Tarif Ekspedisi (Kecamatan)';
        document.getElementById('expeditionRateForm').action = '{{ route("shipping-addresses.store") }}';
        document.getElementById('expeditionModalMethod').value = 'POST';
        
        // Reset inputs
        $('#modalExpeditionCityId').val('');
        $('#modalExpeditionSubDistrictId').prop('required', true).val(null).trigger('change');
        $('#modalExpeditionCourierId').val('').trigger('change');
        $('#modalExpeditionType').val('1');
        $('#modalExpeditionPrice').val('');
        $('#modalExpeditionAdditionalPrice').val('0');
        $('#modalExpeditionIsActive').prop('checked', true);

        const modal = document.getElementById('expeditionRateModal');
        modal.classList.remove('hidden');
        setTimeout(() => {
            modal.classList.remove('opacity-0');
            modal.querySelector('.transform').classList.remove('scale-95');
        }, 10);
    }

    function openEditExpeditionModal(id, subDistrictId, cityId, subDistrictLabel, courierId, type, price, additionalPrice, isActive) {
        document.getElementById('expeditionModalTitle').textContent = 'Edit Tarif Ekspedisi';
        document.getElementById('expeditionRateForm').action = '/shipping-addresses/' + id;
        document.getElementById('expeditionModalMethod').value = 'PUT';

        $('#modalExpeditionCityId').val(cityId || '');

        if (subDistrictId) {
            $('#modalExpeditionSubDistrictId').prop('required', true);
            if ($('#modalExpeditionSubDistrictId').find("option[value='" + subDistrictId + "']").length) {
                $('#modalExpeditionSubDistrictId').val(subDistrictId).trigger('change');
            } else {
                var newOption = new Option(subDistrictLabel, subDistrictId, true, true);
                $('#modalExpeditionSubDistrictId').append(newOption).trigger('change');
            }
        } else {
            // Tingkat Kota (1 Indonesia)
            $('#modalExpeditionSubDistrictId').prop('required', false);
            var newOption = new Option(subDistrictLabel, '', true, true);
            $('#modalExpeditionSubDistrictId').empty().append(newOption).trigger('change');
        }

        $('#modalExpeditionCourierId').val(courierId).trigger('change');
        $('#modalExpeditionType').val(type);
        $('#modalExpeditionPrice').val(price);
        $('#modalExpeditionAdditionalPrice').val(additionalPrice || 0);
        $('#modalExpeditionIsActive').prop('checked', isActive);

        const modal = document.getElementById('expeditionRateModal');
        modal.classList.remove('hidden');
        setTimeout(() => {
            modal.classList.remove('opacity-0');
            modal.querySelector('.transform').classList.remove('scale-95');
        }, 10);
    }

    function closeExpeditionModal() {
        const modal = document.getElementById('expeditionRateModal');
        modal.classList.add('opacity-0');
        modal.querySelector('.transform').classList.add('scale-95');
        setTimeout(() => { modal.classList.add('hidden'); }, 300);
    }
</script>
@endpush
