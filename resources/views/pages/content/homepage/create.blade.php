@extends('layouts.app')

@section('title', 'Add Homepage Section')

@section('content')
<div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
    <div>
        <h1 class="font-headline-lg text-headline-lg text-on-surface">Add Homepage Section</h1>
        <nav class="flex items-center gap-2 text-body-md text-on-surface-variant mt-1">
            <a href="{{ route('dashboard') }}" class="hover:text-primary transition-colors">Dashboard</a>
            <span class="material-symbols-outlined text-[16px]">chevron_right</span>
            <a href="{{ route('content.homepage.index') }}" class="text-primary hover:underline">Homepages</a>
            <span class="material-symbols-outlined text-[16px]">chevron_right</span>
            <span class="text-on-surface">Add Section</span>
        </nav>
    </div>
    <a href="{{ route('content.homepage.index') }}" class="flex items-center gap-2 px-4 py-2 bg-surface-container-lowest border border-outline-variant text-secondary rounded-lg font-label-md hover:bg-surface-container transition-all">
        <span class="material-symbols-outlined text-[18px]">arrow_back</span> Back
    </a>
</div>

@include('layouts.partials.content-submenu')

<form method="POST" action="{{ route('content.homepage.store') }}" class="bg-surface-container-lowest border border-outline-variant rounded-xl p-6 max-w-4xl" x-data="{
    title: '{{ old('title', '') }}',
    sectionKey: '{{ old('section_key', '') }}',
    contentType: '{{ old('content_type', 'product') }}',
    platform: '{{ old('platform', 'all') }}',
    productTab: 'regular',
    productSearch: '',
    bundleSearch: '',
    updateKey() {
        this.sectionKey = this.title
            .toLowerCase()
            .trim()
            .replace(/[\s\W-]+/g, '_')
            .replace(/^_+|_+$/g, '');
    }
}">
    @csrf

    <div class="space-y-6">
        {{-- Section 1: Basic Info --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="space-y-1.5">
                <label class="block text-label-sm font-medium text-on-surface-variant">Section Title <span class="text-danger">*</span></label>
                <input type="text" name="title" x-model="title" @input="updateKey()" required placeholder="e.g. Promo Brand Populer" class="w-full px-3 py-2 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary/20 focus:outline-none text-sm">
                @error('title')<p class="text-danger text-xs font-semibold mt-1">{{ $message }}</p>@enderror
            </div>
            
            <div class="space-y-1.5">
                <label class="block text-label-sm font-medium text-on-surface-variant">Section Key <span class="text-xs text-secondary font-normal">(Otomatis dari Judul)</span></label>
                <input type="text" name="section_key" x-model="sectionKey" readonly class="w-full px-3 py-2 border border-outline-variant rounded-lg bg-surface-container-low/60 cursor-not-allowed text-on-surface-variant focus:outline-none text-sm font-mono" placeholder="otomatis_terisi">
                @error('section_key')<p class="text-danger text-xs font-semibold mt-1">{{ $message }}</p>@enderror
            </div>
        </div>

        {{-- Section 2: Platform Version --}}
        <div class="space-y-2 pt-2 border-t border-outline-variant/30">
            <label class="block text-label-sm font-medium text-on-surface-variant">Platform Target <span class="text-danger">*</span></label>
            <p class="text-xs text-on-surface-variant">Pilih apakah section ini tampil di versi web, versi mobile, atau keduanya.</p>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 pt-1">
                <label class="flex items-center gap-3 p-3.5 rounded-xl border border-outline-variant/50 cursor-pointer transition-all hover:bg-surface-container/20" :class="platform === 'all' ? 'border-primary bg-primary/5 ring-1 ring-primary' : 'bg-white'">
                    <input type="radio" name="platform" value="all" x-model="platform" class="text-primary focus:ring-primary">
                    <div>
                        <span class="text-sm font-bold text-on-surface block">Semua Platform</span>
                        <span class="text-xs text-on-surface-variant">Tampil di Web dan Mobile</span>
                    </div>
                </label>
                <label class="flex items-center gap-3 p-3.5 rounded-xl border border-outline-variant/50 cursor-pointer transition-all hover:bg-surface-container/20" :class="platform === 'web' ? 'border-primary bg-primary/5 ring-1 ring-primary' : 'bg-white'">
                    <input type="radio" name="platform" value="web" x-model="platform" class="text-primary focus:ring-primary">
                    <div>
                        <span class="text-sm font-bold text-on-surface block">Versi Web</span>
                        <span class="text-xs text-on-surface-variant">Hanya tampil di Desktop Web</span>
                    </div>
                </label>
                <label class="flex items-center gap-3 p-3.5 rounded-xl border border-outline-variant/50 cursor-pointer transition-all hover:bg-surface-container/20" :class="platform === 'mobile' ? 'border-primary bg-primary/5 ring-1 ring-primary' : 'bg-white'">
                    <input type="radio" name="platform" value="mobile" x-model="platform" class="text-primary focus:ring-primary">
                    <div>
                        <span class="text-sm font-bold text-on-surface block">Versi Mobile</span>
                        <span class="text-xs text-on-surface-variant">Hanya tampil di Mobile Browser/App</span>
                    </div>
                </label>
            </div>
            @error('platform')<p class="text-danger text-xs font-semibold">{{ $message }}</p>@enderror
        </div>

        {{-- Section 3: Content Flag (Product, Category, Brand, Combination) --}}
        <div class="space-y-2 pt-2 border-t border-outline-variant/30">
            <label class="block text-label-sm font-medium text-on-surface-variant">Tipe Konten Section <span class="text-danger">*</span></label>
            <p class="text-xs text-on-surface-variant">Pilih jenis data yang akan menjadi konten utama di section ini.</p>
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 pt-1">
                <label class="flex items-center gap-2.5 p-3 rounded-xl border border-outline-variant/50 cursor-pointer transition-all hover:bg-surface-container/20" :class="contentType === 'product' ? 'border-primary bg-primary/5 ring-1 ring-primary' : 'bg-white'">
                    <input type="radio" name="content_type" value="product" x-model="contentType" class="text-primary focus:ring-primary">
                    <span class="text-sm font-bold text-on-surface">Produk</span>
                </label>
                <label class="flex items-center gap-2.5 p-3 rounded-xl border border-outline-variant/50 cursor-pointer transition-all hover:bg-surface-container/20" :class="contentType === 'category' ? 'border-primary bg-primary/5 ring-1 ring-primary' : 'bg-white'">
                    <input type="radio" name="content_type" value="category" x-model="contentType" class="text-primary focus:ring-primary">
                    <span class="text-sm font-bold text-on-surface">Kategori</span>
                </label>
                <label class="flex items-center gap-2.5 p-3 rounded-xl border border-outline-variant/50 cursor-pointer transition-all hover:bg-surface-container/20" :class="contentType === 'brand' ? 'border-primary bg-primary/5 ring-1 ring-primary' : 'bg-white'">
                    <input type="radio" name="content_type" value="brand" x-model="contentType" class="text-primary focus:ring-primary">
                    <span class="text-sm font-bold text-on-surface">Brand</span>
                </label>
                <label class="flex items-center gap-2.5 p-3 rounded-xl border border-outline-variant/50 cursor-pointer transition-all hover:bg-surface-container/20" :class="contentType === 'combination' ? 'border-primary bg-primary/5 ring-1 ring-primary' : 'bg-white'">
                    <input type="radio" name="content_type" value="combination" x-model="contentType" class="text-primary focus:ring-primary">
                    <span class="text-sm font-bold text-on-surface">Kombinasi</span>
                </label>
            </div>
            @error('content_type')<p class="text-danger text-xs font-semibold">{{ $message }}</p>@enderror
        </div>

        {{-- Dynamic options based on content type --}}
        <div x-show="contentType === 'product' || contentType === 'combination'" x-cloak class="space-y-3 p-4 bg-surface-container-low/40 rounded-xl border border-outline-variant/40">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-outline-variant/30 pb-2.5">
                <div>
                    <label class="block text-xs font-bold text-on-surface uppercase tracking-wider">Pilih Produk / Paket Terkait</label>
                    <span class="text-xs text-on-surface-variant font-normal">Centang produk reguler atau paket bundling yang ingin ditampilkan di section ini</span>
                </div>
                {{-- Tabs --}}
                <div class="inline-flex items-center p-1 bg-surface-container rounded-lg gap-1 border border-outline-variant/30 self-start sm:self-auto">
                    <button type="button" @click="productTab = 'regular'" :class="productTab === 'regular' ? 'bg-white text-primary font-bold shadow-sm' : 'text-on-surface-variant hover:text-on-surface'" class="px-3 py-1 text-xs rounded-md transition-all flex items-center gap-1.5">
                        <span class="material-symbols-outlined text-[15px]">inventory_2</span>
                        <span>Produk Reguler</span>
                    </button>
                    <button type="button" @click="productTab = 'bundling'" :class="productTab === 'bundling' ? 'bg-white text-primary font-bold shadow-sm' : 'text-on-surface-variant hover:text-on-surface'" class="px-3 py-1 text-xs rounded-md transition-all flex items-center gap-1.5">
                        <span class="material-symbols-outlined text-[15px]">all_in_one</span>
                        <span>Paket Bundling</span>
                    </button>
                </div>
            </div>

            {{-- Tab 1: Produk Reguler --}}
            <div x-show="productTab === 'regular'" class="space-y-2">
                <div class="flex items-center gap-2">
                    <input type="text" x-model="productSearch" placeholder="Cari produk reguler berdasarkan nama atau SKU..." class="w-full px-3 py-1.5 border border-outline-variant rounded-lg text-xs bg-white focus:outline-none focus:ring-1 focus:ring-primary">
                    <span class="text-[11px] text-on-surface-variant whitespace-nowrap">{{ count($products ?? []) }} Produk</span>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-2 max-h-60 overflow-y-auto p-2 bg-white rounded-lg border border-outline-variant/30">
                    @php $oldProducts = old('selected_products', []); @endphp
                    @forelse($products ?? [] as $prod)
                        <label x-show="!productSearch || '{{ strtolower(addslashes($prod->name . ' ' . ($prod->code ?? ''))) }}'.includes(productSearch.toLowerCase())" class="flex items-center gap-2.5 text-xs text-on-surface p-1.5 hover:bg-surface-container/30 rounded cursor-pointer border border-transparent hover:border-outline-variant/40 transition-colors">
                            <input type="checkbox" name="selected_products[]" value="{{ $prod->id }}" {{ in_array($prod->id, $oldProducts) ? 'checked' : '' }} class="rounded text-primary focus:ring-primary shrink-0">
                            @if(!empty($prod->thumbnail))
                                <img src="{{ Str::startsWith($prod->thumbnail, ['http://', 'https://']) ? $prod->thumbnail : asset('storage/' . $prod->thumbnail) }}" class="w-8 h-8 object-cover rounded shrink-0 border border-outline-variant/40" onerror="this.style.display='none'">
                            @else
                                <div class="w-8 h-8 rounded bg-surface-container flex items-center justify-center text-on-surface-variant shrink-0 border border-outline-variant/30">
                                    <span class="material-symbols-outlined text-[16px]">image</span>
                                </div>
                            @endif
                            <div class="min-w-0 flex-1">
                                <div class="font-medium truncate text-xs">{{ $prod->name }}</div>
                                @if($prod->code)<div class="text-[10px] text-on-surface-variant font-mono">{{ $prod->code }}</div>@endif
                            </div>
                        </label>
                    @empty
                        <p class="text-xs text-on-surface-variant p-2 col-span-3">Tidak ada produk ditemukan.</p>
                    @endforelse
                </div>
            </div>

            {{-- Tab 2: Paket Bundling --}}
            <div x-show="productTab === 'bundling'" class="space-y-2">
                <div class="flex items-center gap-2">
                    <input type="text" x-model="bundleSearch" placeholder="Cari paket bundling berdasarkan nama..." class="w-full px-3 py-1.5 border border-outline-variant rounded-lg text-xs bg-white focus:outline-none focus:ring-1 focus:ring-primary">
                    <span class="text-[11px] text-on-surface-variant whitespace-nowrap">{{ count($bundles ?? []) }} Paket</span>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-2 max-h-60 overflow-y-auto p-2 bg-white rounded-lg border border-outline-variant/30">
                    @php $oldBundles = old('selected_bundles', []); @endphp
                    @forelse($bundles ?? [] as $bundle)
                        <label x-show="!bundleSearch || '{{ strtolower(addslashes($bundle->name)) }}'.includes(bundleSearch.toLowerCase())" class="flex items-center gap-2.5 text-xs text-on-surface p-1.5 hover:bg-surface-container/30 rounded cursor-pointer border border-transparent hover:border-outline-variant/40 transition-colors">
                            <input type="checkbox" name="selected_bundles[]" value="{{ $bundle->id }}" {{ in_array($bundle->id, $oldBundles) ? 'checked' : '' }} class="rounded text-primary focus:ring-primary shrink-0">
                            @if(!empty($bundle->image_url))
                                <img src="{{ Str::startsWith($bundle->image_url, ['http://', 'https://']) ? $bundle->image_url : asset('storage/' . $bundle->image_url) }}" class="w-8 h-8 object-cover rounded shrink-0 border border-outline-variant/40" onerror="this.style.display='none'">
                            @else
                                <div class="w-8 h-8 rounded bg-surface-container flex items-center justify-center text-on-surface-variant shrink-0 border border-outline-variant/30">
                                    <span class="material-symbols-outlined text-[16px]">inventory_2</span>
                                </div>
                            @endif
                            <div class="min-w-0 flex-1">
                                <div class="font-medium truncate text-xs">{{ $bundle->name }}</div>
                                <div class="text-[10px] text-primary font-bold">Rp {{ number_format($bundle->price ?? 0, 0, ',', '.') }}</div>
                            </div>
                        </label>
                    @empty
                        <p class="text-xs text-on-surface-variant p-2 col-span-3">Tidak ada paket bundling ditemukan.</p>
                    @endforelse
                </div>
            </div>
        </div>

        <div x-show="contentType === 'brand' || contentType === 'combination'" x-cloak class="space-y-2 p-4 bg-surface-container-low/40 rounded-xl border border-outline-variant/40">
            <label class="block text-xs font-bold text-on-surface uppercase tracking-wider">Pilih Brand Terkait</label>
            <div class="grid grid-cols-2 sm:grid-cols-3 gap-2 max-h-48 overflow-y-auto p-2 bg-white rounded-lg border border-outline-variant/30">
                @foreach($brands ?? [] as $brand)
                    <label class="flex items-center gap-2 text-xs text-on-surface p-1 hover:bg-surface-container/30 rounded cursor-pointer">
                        <input type="checkbox" name="selected_brands[]" value="{{ $brand->id }}" class="rounded text-primary focus:ring-primary">
                        <span>{{ $brand->name }}</span>
                    </label>
                @endforeach
            </div>
        </div>

        <div x-show="contentType === 'category' || contentType === 'combination'" x-cloak class="space-y-2 p-4 bg-surface-container-low/40 rounded-xl border border-outline-variant/40">
            <label class="block text-xs font-bold text-on-surface uppercase tracking-wider">Pilih Kategori Terkait</label>
            <div class="grid grid-cols-2 sm:grid-cols-3 gap-2 max-h-48 overflow-y-auto p-2 bg-white rounded-lg border border-outline-variant/30">
                @foreach($categories ?? [] as $cat)
                    <label class="flex items-center gap-2 text-xs text-on-surface p-1 hover:bg-surface-container/30 rounded cursor-pointer">
                        <input type="checkbox" name="selected_categories[]" value="{{ $cat->id }}" class="rounded text-primary focus:ring-primary">
                        <span>{{ $cat->name }}</span>
                    </label>
                @endforeach
            </div>
        </div>

        {{-- Sort Order & Visibility --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 pt-2 border-t border-outline-variant/30">
            <div class="space-y-1.5">
                <label class="block text-label-sm font-medium text-on-surface-variant">Sort Order <span class="text-danger">*</span></label>
                <input type="number" name="sort_order" required value="{{ old('sort_order', 0) }}" class="w-full px-3 py-2 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary/20 focus:outline-none text-sm">
                <p class="text-xs text-on-surface-variant mt-1">Nomor lebih kecil muncul lebih awal (e.g. 1, 2, 3...)</p>
                @error('sort_order')<p class="text-danger text-xs font-semibold">{{ $message }}</p>@enderror
            </div>
            
            <div class="space-y-1.5 flex flex-col justify-center">
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="is_visible" value="1" {{ old('is_visible', true) ? 'checked' : '' }} class="w-5 h-5 text-primary border-outline-variant rounded focus:ring-primary/20">
                    <span class="text-label-md font-medium text-on-surface-variant">Tampilkan section ini di Homepage</span>
                </label>
                @error('is_visible')<p class="text-danger text-xs font-semibold">{{ $message }}</p>@enderror
            </div>
        </div>
    </div>
    
    <div class="mt-8 flex justify-end gap-3 border-t border-outline-variant/30 pt-4">
        <a href="{{ route('content.homepage.index') }}" class="px-6 py-2.5 border border-outline-variant text-on-surface-variant font-bold rounded-xl hover:bg-surface-container transition-colors text-sm">Batal</a>
        <button type="submit" class="px-8 py-2.5 bg-primary text-white font-bold rounded-xl hover:opacity-90 transition-all text-sm shadow-sm cursor-pointer">Simpan Section</button>
    </div>
</form>
@endsection
