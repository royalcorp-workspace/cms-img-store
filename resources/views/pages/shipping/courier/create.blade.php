@extends('layouts.app')

@section('title', 'Create Courier')

@section('content')
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="font-headline-lg text-headline-lg text-on-surface">Create Courier</h1>
            <nav class="flex items-center gap-2 text-body-md text-on-surface-variant mt-1">
                <a href="{{ route('dashboard') }}" class="text-primary hover:underline">Dashboard</a>
                <span class="material-symbols-outlined text-[16px]">chevron_right</span>
                <a href="{{ route('couriers.index') }}" class="text-primary hover:underline">Couriers</a>
                <span class="material-symbols-outlined text-[16px]">chevron_right</span>
                <span>Create</span>
            </nav>
        </div>
        <a href="{{ route('couriers.index') }}" class="flex items-center gap-2 px-4 py-2 bg-surface-container-lowest border border-outline-variant text-secondary rounded-lg font-label-md hover:bg-surface-container transition-all">
            <span class="material-symbols-outlined text-[18px]">arrow_back</span> Back
        </a>
    </div>

    @include('layouts.partials.shipping-payment-submenu')

    <form method="POST" action="{{ route('couriers.store') }}" class="space-y-6">
        @csrf
        <div class="bg-white rounded-xl shadow-sm border border-outline-variant/30 p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="space-y-1.5">
                    <label class="block text-label-sm font-medium text-on-surface-variant">Code <span class="text-danger">*</span></label>
                    <input type="text" name="code" class="w-full px-3 py-2 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary/20 focus:outline-none" placeholder="e.g., JNE" required value="{{ old('code') }}">
                    @error('code')<p class="text-danger text-sm">{{ $message }}</p>@enderror
                </div>
                <div class="space-y-1.5">
                    <label class="block text-label-sm font-medium text-on-surface-variant">Name <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="w-full px-3 py-2 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary/20 focus:outline-none" placeholder="e.g., JNE Express" required value="{{ old('name') }}">
                    @error('name')<p class="text-danger text-sm">{{ $message }}</p>@enderror
                </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                <div class="space-y-1.5">
                    <label class="block text-label-sm font-medium text-on-surface-variant">Type <span class="text-danger">*</span></label>
                    <select name="type" class="w-full px-3 py-2 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary/20 focus:outline-none bg-white select2-enable" required>
                        <option value="1" {{ old('type', '1') == '1' ? 'selected' : '' }}>Regular</option>
                        <option value="2" {{ old('type') == '2' ? 'selected' : '' }}>Express</option>
                        <option value="3" {{ old('type') == '3' ? 'selected' : '' }}>Same Day</option>
                        <option value="4" {{ old('type') == '4' ? 'selected' : '' }}>Instant</option>
                    </select>
                    @error('type')<p class="text-danger text-sm">{{ $message }}</p>@enderror
                </div>
                <div class="space-y-1.5">
                    <label class="block text-label-sm font-medium text-on-surface-variant">Sort Order</label>
                    <input type="number" name="sort_order" value="{{ old('sort_order', 0) }}" min="0" class="w-full px-3 py-2 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary/20 focus:outline-none">
                    @error('sort_order')<p class="text-danger text-sm">{{ $message }}</p>@enderror
                </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4 border-t border-outline-variant/30 pt-4">
                <div class="space-y-1.5 md:col-span-2">
                    <label class="block text-label-sm font-medium text-on-surface-variant">Batasi Jenis (Kategori)</label>
                    <p class="text-xs text-gray-500 mb-1">Pilih kategori yang diizinkan untuk kurir ini. Kosongkan jika berlaku untuk semua kategori.</p>
                    <select name="category_ids[]" multiple class="w-full px-3 py-2 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary/20 focus:outline-none bg-white select2-enable" data-placeholder="Pilih Kategori...">
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            
            <div class="mt-4 border-t border-outline-variant/30 pt-4" x-data="{ search: '' }">
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-end mb-3 gap-3">
                    <div class="space-y-1">
                        <label class="block text-label-sm font-medium text-on-surface-variant">Batasi Kode Barang (Produk)</label>
                        <p class="text-xs text-gray-500">Centang produk yang diizinkan untuk kurir ini. Kosongkan jika berlaku untuk semua barang.</p>
                    </div>
                    <div class="relative w-full sm:w-64">
                        <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-on-surface-variant text-[18px]">search</span>
                        <input type="text" x-model="search" placeholder="Cari kode atau nama produk..." class="w-full pl-9 pr-3 py-1.5 text-sm border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary/20 focus:outline-none bg-white">
                    </div>
                </div>
                
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3 max-h-[400px] overflow-y-auto p-3 border border-outline-variant/50 rounded-xl bg-surface-gray/30 custom-scrollbar">
                    @foreach($products as $prod)
                        <label class="bg-white rounded-lg border border-outline-variant/50 shadow-sm flex items-center p-2.5 cursor-pointer hover:border-primary/50 hover:shadow-md transition-all gap-3" x-show="search === '' || '{{ strtolower(addslashes($prod->code . ' ' . $prod->name)) }}'.includes(search.toLowerCase())">
                            <input type="checkbox" name="product_ids[]" value="{{ $prod->id }}" class="w-4 h-4 text-primary border-outline-variant rounded focus:ring-primary">
                            <div class="w-10 h-10 rounded overflow-hidden bg-surface-gray flex-shrink-0 border border-outline-variant/30">
                                @if($prod->images->isNotEmpty())
                                    <img src="{{ $prod->images->first()->url }}" alt="{{ $prod->name }}" class="w-full h-full object-cover">
                                @else
                                    <div class="w-full h-full flex items-center justify-center text-on-surface-variant">
                                        <span class="material-symbols-outlined text-[18px]">image</span>
                                    </div>
                                @endif
                            </div>
                            <div class="flex-1 min-w-0">
                                <h4 class="text-[12px] text-on-surface font-semibold truncate block">{{ $prod->code }}</h4>
                                <p class="text-[10px] text-on-surface-variant truncate">{{ $prod->name }}</p>
                            </div>
                        </label>
                    @endforeach
                </div>
            </div>
            <div class="mt-4 border-t border-outline-variant/30 pt-4">
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }} class="w-4 h-4 text-primary border-outline-variant rounded focus:ring-primary">
                    <span class="text-label-sm font-medium text-on-surface-variant">Active</span>
                </label>
                @error('is_active')<p class="text-danger text-sm">{{ $message }}</p>@enderror
            </div>
        </div>
        <div class="flex justify-end gap-4">
            <a href="{{ route('couriers.index') }}" class="px-8 py-3 border border-outline-variant text-primary font-bold rounded-lg hover:bg-surface-container transition-colors">Cancel</a>
            <button type="submit" class="px-10 py-3 bg-primary text-white rounded-lg font-label-md hover:opacity-90 transition-all shadow-sm">Save Courier</button>
        </div>
    </form>
@endsection
