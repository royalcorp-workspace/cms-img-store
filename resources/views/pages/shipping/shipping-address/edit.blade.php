@extends('layouts.app')

@section('title', 'Edit Shipping Address Rate')

@section('content')
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="font-headline-lg text-headline-lg text-on-surface">Edit Shipping Rate</h1>
            <nav class="flex items-center gap-2 text-body-md text-on-surface-variant mt-1">
                <a href="{{ route('dashboard') }}" class="text-primary hover:underline">Dashboard</a>
                <span class="material-symbols-outlined text-[16px]">chevron_right</span>
                <a href="{{ route('shipping-addresses.index') }}" class="text-primary hover:underline">Shipping Address Rates</a>
                <span class="material-symbols-outlined text-[16px]">chevron_right</span>
                <span>Edit</span>
            </nav>
        </div>
        <a href="{{ route('shipping-addresses.index') }}" class="flex items-center gap-2 px-4 py-2 bg-surface-container-lowest border border-outline-variant text-secondary rounded-lg font-label-md hover:bg-surface-container transition-all">
            <span class="material-symbols-outlined text-[18px]">arrow_back</span> Back
        </a>
    </div>

    @include('layouts.partials.shipping-payment-submenu')

    <form method="POST" action="{{ route('shipping-addresses.update', $shippingAddress->id) }}" class="space-y-6">
        @csrf
        @method('PUT')
        <div class="bg-white rounded-xl shadow-sm border border-outline-variant/30 p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div class="space-y-1.5">
                    <label class="block text-label-sm font-medium text-on-surface-variant">Wilayah Cakupan</label>
                    @php
                        $locationName = 'Semua Wilayah (Global / Default)';
                        if ($shippingAddress->city) {
                            $locationName = 'Kota: ' . $shippingAddress->city->name . ' (' . ($shippingAddress->city->province->name ?? '') . ')';
                        } elseif ($shippingAddress->subDistrict) {
                            $locationName = 'Kec/Kel: ' . $shippingAddress->subDistrict->sub_district . ' (' . $shippingAddress->subDistrict->district . ')';
                        }
                    @endphp
                    <input type="text" readonly disabled value="{{ $locationName }}" class="w-full px-3 py-2 border border-outline-variant rounded-lg bg-surface-gray text-on-surface-variant font-medium">
                </div>
                <div class="space-y-1.5">
                    <label class="block text-label-sm font-medium text-on-surface-variant">Service Type</label>
                    <select name="type" class="w-full px-3 py-2 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary/20 focus:outline-none bg-white">
                        <option value="1" {{ old('type', $shippingAddress->type) == 1 ? 'selected' : '' }}>Regular</option>
                        <option value="2" {{ old('type', $shippingAddress->type) == 2 ? 'selected' : '' }}>Express</option>
                        <option value="3" {{ old('type', $shippingAddress->type) == 3 ? 'selected' : '' }}>Same Day</option>
                        <option value="4" {{ old('type', $shippingAddress->type) == 4 ? 'selected' : '' }}>Instant</option>
                    </select>
                </div>
            </div>

            <input type="hidden" name="city_id" value="{{ $shippingAddress->city_id }}">
            <input type="hidden" name="sub_district_id" value="{{ $shippingAddress->sub_district_id }}">

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div class="space-y-1.5">
                    <label class="block text-label-sm font-medium text-on-surface-variant">Kurir <span class="text-danger">*</span></label>
                    <select name="courier_id" class="w-full px-3 py-2 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary/20 focus:outline-none bg-white select2-enable" required>
                        @foreach($couriers as $courier)
                            <option value="{{ $courier->id }}" {{ old('courier_id', $shippingAddress->courier_id) == $courier->id ? 'selected' : '' }}>
                                {{ $courier->name }} ({{ $courier->courier_type }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="space-y-1.5">
                    <label class="block text-label-sm font-medium text-on-surface-variant">Status</label>
                    <div class="pt-2">
                        <label class="inline-flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="is_active" value="1" {{ old('is_active', $shippingAddress->is_active) ? 'checked' : '' }} class="w-4 h-4 text-primary border-outline-variant rounded focus:ring-primary">
                            <span class="text-body-md font-medium text-on-surface">Aktif</span>
                        </label>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="space-y-1.5">
                    <label class="block text-label-sm font-medium text-on-surface-variant">Tarif Dasar / Flat (Rp) <span class="text-danger">*</span></label>
                    <input type="number" name="price" value="{{ old('price', $shippingAddress->price) }}" min="0" required placeholder="e.g. 25000" class="w-full px-3 py-2 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary/20 focus:outline-none">
                    <span class="text-[11px] text-on-surface-variant block">Tarif trip dasar / 1 kg pertama</span>
                </div>
                <div class="space-y-1.5">
                    <label class="block text-label-sm font-medium text-on-surface-variant">Biaya Tambahan / Kg (Rp)</label>
                    <input type="number" name="additional_price_per_kg" value="{{ old('additional_price_per_kg', $shippingAddress->additional_price_per_kg ?? 0) }}" min="0" placeholder="0" class="w-full px-3 py-2 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary/20 focus:outline-none">
                    <span class="text-[11px] text-on-surface-variant block">Biaya per kg berikutnya jika berat > 1 kg (isi 0 jika tarif flat)</span>
                </div>
            </div>
        </div>

        <div class="flex justify-end gap-4">
            <a href="{{ route('shipping-addresses.index') }}" class="px-8 py-3 border border-outline-variant text-primary font-bold rounded-lg hover:bg-surface-container transition-colors">Cancel</a>
            <button type="submit" class="px-10 py-3 bg-primary text-white rounded-lg font-label-md hover:opacity-90 transition-all shadow-sm">Simpan Perubahan</button>
        </div>
    </form>
@endsection
