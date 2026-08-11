@extends('layouts.app')

@section('title', 'Ubah Alamat - ' . $customer->name)

@section('content')
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="font-headline-lg text-headline-lg text-on-surface">Ubah Alamat</h1>
            <nav class="flex items-center gap-2 text-body-md text-on-surface-variant mt-1">
                <a href="{{ route('dashboard') }}" class="text-primary hover:underline">Dashboard</a>
                <span class="material-symbols-outlined text-[16px]">chevron_right</span>
                <a href="{{ route('customers.index') }}" class="text-primary hover:underline">Customers</a>
                <span class="material-symbols-outlined text-[16px]">chevron_right</span>
                <a href="{{ route('customers.addresses.index', $customer->id) }}" class="text-primary hover:underline">Buku Alamat</a>
                <span class="material-symbols-outlined text-[16px]">chevron_right</span>
                <span>Edit</span>
            </nav>
        </div>
    </div>

    @include('layouts.partials.customer-submenu')

    <div class="bg-surface rounded-2xl shadow-sm border border-outline-variant p-6">
        <form action="{{ route('customers.addresses.update', [$customer->id, $address->id]) }}" method="POST">
            @csrf
            @method('PUT')
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Label Alamat -->
                <div class="space-y-1.5">
                    <label class="block text-label-sm font-medium text-on-surface-variant">Label Alamat <span class="text-danger">*</span></label>
                    <input type="text" name="label" value="{{ old('label', $address->label) }}" class="w-full px-3 py-2 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary/20 focus:outline-none" placeholder="Rumah/Kantor" required>
                    @error('label')<p class="text-danger text-sm">{{ $message }}</p>@enderror
                </div>
                
                <div class="flex items-center h-full pt-6">
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" name="is_primary" value="1" {{ old('is_primary', $address->is_primary) ? 'checked' : '' }} class="sr-only peer">
                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-primary/20 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary"></div>
                        <span class="ml-3 text-label-md font-medium text-on-surface">Jadikan sebagai Alamat Utama</span>
                    </label>
                </div>

                <!-- Nama Penerima -->
                <div class="space-y-1.5">
                    <label class="block text-label-sm font-medium text-on-surface-variant">Nama Penerima <span class="text-danger">*</span></label>
                    <input type="text" name="recipient_name" value="{{ old('recipient_name', $address->recipient_name) }}" class="w-full px-3 py-2 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary/20 focus:outline-none" required>
                    @error('recipient_name')<p class="text-danger text-sm">{{ $message }}</p>@enderror
                </div>

                <!-- Nomor Telepon -->
                <div class="space-y-1.5">
                    <label class="block text-label-sm font-medium text-on-surface-variant">Nomor Telepon <span class="text-danger">*</span></label>
                    <input type="text" name="phone" value="{{ old('phone', $address->phone) }}" class="w-full px-3 py-2 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary/20 focus:outline-none" required>
                    @error('phone')<p class="text-danger text-sm">{{ $message }}</p>@enderror
                </div>

                <!-- Kecamatan -->
                <div class="space-y-1.5 md:col-span-2">
                    <label class="block text-label-sm font-medium text-on-surface-variant">Kecamatan (Sub-district) <span class="text-danger">*</span></label>
                    <select name="sub_district_id" class="w-full px-3 py-2 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary/20 focus:outline-none bg-white" required>
                        <option value="">Pilih Kecamatan</option>
                        @foreach($subDistricts as $subDistrict)
                            <option value="{{ $subDistrict->id }}" {{ old('sub_district_id', $address->sub_district_id) == $subDistrict->id ? 'selected' : '' }}>
                                {{ $subDistrict->sub_district }} - {{ $subDistrict->city ? $subDistrict->city->name : '' }}
                            </option>
                        @endforeach
                    </select>
                    @error('sub_district_id')<p class="text-danger text-sm">{{ $message }}</p>@enderror
                </div>

                <!-- Alamat Detail -->
                <div class="space-y-1.5 md:col-span-2">
                    <label class="block text-label-sm font-medium text-on-surface-variant">Alamat Lengkap <span class="text-danger">*</span></label>
                    <textarea name="address" rows="3" class="w-full px-3 py-2 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary/20 focus:outline-none" required>{{ old('address', $address->address) }}</textarea>
                    @error('address')<p class="text-danger text-sm">{{ $message }}</p>@enderror
                </div>
                
                <!-- Kode Pos -->
                <div class="space-y-1.5 md:col-span-1">
                    <label class="block text-label-sm font-medium text-on-surface-variant">Kode Pos</label>
                    <input type="text" name="postal_code" value="{{ old('postal_code', $address->postal_code) }}" class="w-full px-3 py-2 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary/20 focus:outline-none">
                    @error('postal_code')<p class="text-danger text-sm">{{ $message }}</p>@enderror
                </div>
            </div>

            <hr class="my-6 border-outline-variant">
            <div class="flex justify-end gap-3">
                <a href="{{ route('customers.addresses.index', $customer->id) }}" class="px-6 py-2.5 rounded-lg border border-outline-variant text-on-surface-variant hover:bg-surface-variant/50 font-label-lg transition-colors">Batal</a>
                <button type="submit" class="px-6 py-2.5 rounded-lg bg-primary text-white hover:opacity-90 font-label-lg transition-colors shadow-sm">Simpan Perubahan</button>
            </div>
        </form>
    </div>
@endsection
