@extends('layouts.app')

@section('title', 'Edit Gudang')

@section('content')
    <!-- Header Section -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="font-headline-lg text-headline-lg text-on-surface">Edit Gudang</h1>
            <nav class="flex items-center gap-2 text-label-sm text-on-surface-variant mt-1 font-medium">
                <a href="{{ route('dashboard') }}" class="hover:text-primary transition-colors">eCommerce</a>
                <span class="material-symbols-outlined text-[14px]">chevron_right</span>
                <a href="{{ route('warehouses.index') }}" class="hover:text-primary transition-colors">Warehouse</a>
                <span class="material-symbols-outlined text-[14px]">chevron_right</span>
                <span class="text-on-surface">Edit Gudang</span>
            </nav>
        </div>
        <div>
            <a href="{{ route('warehouses.index') }}" class="flex items-center gap-2 px-4 py-2 border border-outline-variant bg-white text-on-surface hover:bg-surface-container-high rounded-lg text-sm font-medium transition-all">
                <span class="material-symbols-outlined text-[18px]">arrow_back</span>
                Kembali ke Warehouse
            </a>
        </div>
    </div>

    <!-- Standard Submenu Tabs -->
    @include('layouts.partials.inventory-submenu')

    <!-- Validation Errors -->
    @if($errors->any())
        <div class="mb-6 p-4 rounded-lg bg-danger/10 border border-danger/20 text-danger">
            <div class="font-medium text-sm mb-1">Terdapat kesalahan pengisian data:</div>
            <ul class="list-disc list-inside text-xs space-y-1">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="bg-white rounded-xl shadow-sm border border-outline-variant/30 overflow-hidden max-w-2xl">
        <div class="p-6 border-b border-outline-variant/30">
            <h2 class="text-base font-semibold text-on-surface">Edit Informasi Gudang</h2>
            <p class="text-xs text-on-surface-variant mt-0.5">Perbarui informasi data gudang.</p>
        </div>

        <form action="{{ route('warehouses.update', $warehouse->id) }}" method="POST" class="p-6 space-y-5">
            @csrf
            @method('PUT')

            <div>
                <label class="block text-xs font-semibold text-on-surface mb-1.5">Kode Gudang <span class="text-danger">*</span></label>
                <input type="text" name="code" value="{{ old('code', $warehouse->code) }}" required placeholder="Contoh: GD-BDG01" class="w-full h-11 px-3 border border-outline-variant rounded-lg text-sm bg-white font-mono focus:ring-2 focus:ring-primary/20 focus:border-primary focus:outline-none uppercase">
                <p class="text-[11px] text-on-surface-variant mt-1">Kode unik untuk identifikasi gudang.</p>
            </div>

            <div>
                <label class="block text-xs font-semibold text-on-surface mb-1.5">Nama Gudang <span class="text-danger">*</span></label>
                <input type="text" name="name" value="{{ old('name', $warehouse->name) }}" required placeholder="Contoh: Gudang Cabang Bandung" class="w-full h-11 px-3 border border-outline-variant rounded-lg text-sm bg-white focus:ring-2 focus:ring-primary/20 focus:border-primary focus:outline-none">
            </div>

            <div>
                <label class="block text-xs font-semibold text-on-surface mb-1.5">Kota</label>
                <input type="text" name="city" value="{{ old('city', $warehouse->city) }}" placeholder="Contoh: Kota Bandung" class="w-full h-11 px-3 border border-outline-variant rounded-lg text-sm bg-white focus:ring-2 focus:ring-primary/20 focus:border-primary focus:outline-none">
            </div>

            <div>
                <label class="block text-xs font-semibold text-on-surface mb-1.5">Alamat Lengkap</label>
                <textarea name="address" rows="3" placeholder="Alamat jalan, nomor, RT/RW, dsb." class="w-full p-3 border border-outline-variant rounded-lg text-sm bg-white focus:ring-2 focus:ring-primary/20 focus:border-primary focus:outline-none">{{ old('address', $warehouse->address) }}</textarea>
            </div>

            <div class="flex items-center gap-3 pt-2">
                <input type="checkbox" name="status" id="whStatus" value="1" {{ old('status', $warehouse->status) ? 'checked' : '' }} class="w-4 h-4 text-primary border-outline-variant rounded focus:ring-primary/20">
                <label for="whStatus" class="text-sm font-medium text-on-surface cursor-pointer">Gudang Aktif dan Siap Digunakan</label>
            </div>

            <div class="border-t border-outline-variant/30 pt-5 flex items-center justify-end gap-3">
                <a href="{{ route('warehouses.index') }}" class="px-5 py-2.5 border border-outline-variant text-on-surface hover:bg-surface-container-high rounded-lg text-sm font-medium transition-colors">
                    Batal
                </a>
                <button type="submit" class="px-6 py-2.5 bg-primary text-white hover:bg-primary/90 rounded-lg text-sm font-medium shadow-sm transition-colors flex items-center gap-2">
                    <span class="material-symbols-outlined text-[18px]">save</span>
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
@endsection
