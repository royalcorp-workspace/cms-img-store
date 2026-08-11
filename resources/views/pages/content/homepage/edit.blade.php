@extends('layouts.app')

@section('title', 'Edit Homepage Section')

@section('content')
<div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
    <div>
        <h1 class="font-headline-lg text-headline-lg text-on-surface">Edit Homepage Section</h1>
        <nav class="flex items-center gap-2 text-body-md text-on-surface-variant mt-1">
            <a href="{{ route('dashboard') }}" class="text-primary hover:underline">Dashboard</a>
            <span class="material-symbols-outlined text-[16px]">chevron_right</span>
            <a href="{{ route('content.homepage.index') }}" class="text-primary hover:underline">Homepages</a>
            <span class="material-symbols-outlined text-[16px]">chevron_right</span>
            <span>Edit</span>
        </nav>
    </div>
    <a href="{{ route('content.homepage.index') }}" class="flex items-center gap-2 px-4 py-2 bg-surface-container-lowest border border-outline-variant text-secondary rounded-lg font-label-md hover:bg-surface-container transition-all">
        <span class="material-symbols-outlined text-[18px]">arrow_back</span> Back
    </a>
</div>

@include('layouts.partials.content-submenu')

<form method="POST" action="{{ route('content.homepage.update', $section->id) }}" class="bg-surface-container-lowest border border-outline-variant rounded-xl p-6" x-data="{ sectionKey: '{{ old('section_key', $section->section_key) }}' }">
    @csrf
    @method('PUT')
    
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="space-y-1.5">
            <label class="block text-label-sm font-medium text-on-surface-variant">Section Title <span class="text-danger">*</span></label>
            <input type="text" name="title" required value="{{ old('title', $section->title) }}" class="w-full px-3 py-2 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary/20 focus:outline-none">
            @error('title')<p class="text-danger text-sm">{{ $message }}</p>@enderror
        </div>
        
        <div class="space-y-1.5">
            <label class="block text-label-sm font-medium text-on-surface-variant">Section Key (Unique ID) <span class="text-danger">*</span></label>
            <input type="text" name="section_key" required x-model="sectionKey" class="w-full px-3 py-2 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary/20 focus:outline-none bg-gray-50" readonly>
            @error('section_key')<p class="text-danger text-sm">{{ $message }}</p>@enderror
        </div>

        <div class="space-y-1.5">
            <label class="block text-label-sm font-medium text-on-surface-variant">Sort Order <span class="text-danger">*</span></label>
            <input type="number" name="sort_order" required value="{{ old('sort_order', $section->sort_order) }}" class="w-full px-3 py-2 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary/20 focus:outline-none">
            <p class="text-xs text-on-surface-variant mt-1">Lower numbers appear first (e.g. 1, 2, 3...)</p>
            @error('sort_order')<p class="text-danger text-sm">{{ $message }}</p>@enderror
        </div>
        
        <div class="space-y-1.5 flex flex-col justify-center mt-6">
            <label class="flex items-center gap-2 cursor-pointer">
                <input type="checkbox" name="is_visible" value="1" {{ old('is_visible', $section->is_visible) ? 'checked' : '' }} class="w-5 h-5 text-primary border-outline-variant rounded focus:ring-primary/20">
                <span class="text-label-md font-medium text-on-surface-variant">Show this section on Homepage</span>
            </label>
            @error('is_visible')<p class="text-danger text-sm">{{ $message }}</p>@enderror
        </div>
    </div>
    
    <div class="mt-10 border-t border-outline-variant pt-8" x-show="sectionKey === 'pilihan_brand' || sectionKey === 'promo_brand'" style="display: none;">
        <h3 class="font-headline-sm text-on-surface mb-2">Pengaturan Gambar Brand (Khusus Section Ini)</h3>
        <p class="text-body-sm text-on-surface-variant mb-6">Masukkan URL gambar (Hotlink) jika Anda ingin menimpa gambar bawaan Brand khusus untuk bagian ini saja. Biarkan kosong jika ingin menggunakan gambar bawaan dari menu Brands.</p>
        
        @php
            $brands = \App\Models\Product\Brand::where('deleted', false)->where('status', true)->orderBy('sort_order', 'asc')->get();
            $meta = $section->meta ?? [];
        @endphp
        
        <div class="space-y-4">
            @foreach($brands as $brand)
                @php $safeName = \Illuminate\Support\Str::slug($brand->name); @endphp
                <div class="p-4 border border-outline-variant rounded-xl bg-surface-container-lowest grid grid-cols-1 md:grid-cols-12 gap-4 items-center">
                    <div class="md:col-span-3">
                        <h4 class="font-bold text-label-lg text-primary">{{ $brand->name }}</h4>
                    </div>
                    <div class="md:col-span-9 grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="space-y-2" x-data="{ logoUrl: '{{ old('meta.brands.' . $safeName . '.logo', $meta['brands'][$safeName]['logo'] ?? '') }}' }">
                            <label class="block text-label-xs font-medium text-on-surface-variant">Logo URL (Hotlink)</label>
                            <input type="url" name="meta[brands][{{ $safeName }}][logo]" x-model="logoUrl" class="w-full px-3 py-2 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary/20 focus:outline-none text-sm" placeholder="https://...">
                            <template x-if="logoUrl">
                                <div class="mt-2 p-2 bg-gray-50 border border-gray-200 rounded-lg inline-block">
                                    <img :src="logoUrl" class="h-10 object-contain" alt="Preview Logo">
                                </div>
                            </template>
                        </div>
                        <div class="space-y-2" x-data="{ bannerUrl: '{{ old('meta.brands.' . $safeName . '.banner', $meta['brands'][$safeName]['banner'] ?? '') }}' }">
                            <label class="block text-label-xs font-medium text-on-surface-variant">Banner / BG URL (Hotlink)</label>
                            <input type="url" name="meta[brands][{{ $safeName }}][banner]" x-model="bannerUrl" class="w-full px-3 py-2 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary/20 focus:outline-none text-sm" placeholder="https://...">
                            <template x-if="bannerUrl">
                                <div class="mt-2 p-2 bg-gray-50 border border-gray-200 rounded-lg inline-block w-full">
                                    <img :src="bannerUrl" class="h-20 w-full object-cover rounded" alt="Preview Banner">
                                </div>
                            </template>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
    
    <div class="mt-10 border-t border-outline-variant pt-8" x-show="sectionKey.includes('spesial') || sectionKey.includes('special')" style="display: none;">
        <h3 class="font-headline-sm text-on-surface mb-2">Produk Pilihan (Spesial Hari Ini)</h3>
        <p class="text-body-sm text-on-surface-variant mb-6">Pilih produk utama yang ingin Anda sorot (Featured) pada bagian ini.</p>
        
        @php
            $products = \App\Models\Product\Product::where('deleted', false)->where('status', true)->get();
            $featuredId = old('meta.featured_product_id', $meta['featured_product_id'] ?? '');
        @endphp
        <div class="space-y-1.5">
            <label class="block text-label-sm font-medium text-on-surface-variant">Pilih Produk</label>
            <select name="meta[featured_product_id]" class="w-full px-3 py-2 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary/20 focus:outline-none bg-white">
                <option value="">-- Pilih Produk (Atau biarkan sistem memilih otomatis) --</option>
                @foreach($products as $prod)
                    <option value="{{ $prod->id }}" {{ $featuredId == $prod->id ? 'selected' : '' }}>
                        {{ $prod->name }} ({{ $prod->brand->name ?? 'No Brand' }})
                    </option>
                @endforeach
            </select>
        </div>
    </div>
    
    <div class="mt-8 flex justify-end gap-4">
        <a href="{{ route('content.homepage.index') }}" class="px-8 py-3 border border-outline-variant text-primary font-bold rounded-lg hover:bg-surface-container transition-colors">Cancel</a>
        <button type="submit" class="px-10 py-3 bg-primary text-white font-label-md hover:opacity-90 transition-all">Update Section</button>
    </div>
</form>
@endsection
