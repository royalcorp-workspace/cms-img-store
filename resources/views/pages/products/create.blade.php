@extends('layouts.app')

@section('title', isset($product) && $product->id ? 'Edit Product' : 'Create Product')

@push('styles')
<link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
<style>
    .ql-editor {
        min-height: 200px;
        font-family: inherit;
        font-size: 0.95rem;
        line-height: 1.6;
    }
    .ql-toolbar.ql-snow {
        border-color: #e2e8f0;
        border-top-left-radius: 0.75rem;
        border-top-right-radius: 0.75rem;
        background-color: #f8fafc;
    }
    .ql-container.ql-snow {
        border-color: #e2e8f0;
        border-bottom-left-radius: 0.75rem;
        border-bottom-right-radius: 0.75rem;
        font-size: 0.95rem;
    }
</style>
@endpush

@section('content')
@php
    $isEdit = isset($product) && $product->id;
    $productId = $product->id ?? null;
    $variants = $product->variants ?? collect([]);
    $colors = $product->colors ?? collect([]);

    if (!function_exists('buildCategoryOptions')) {
        function buildCategoryOptions($categories, $parentId = null, $prefix = '', $selectedId = null)
        {
            $html = '';
            foreach ($categories->where('parent_id', $parentId) as $cat) {
                $selected = ($selectedId == $cat->id) ? 'selected' : '';
                $settingType = e($cat->courier_setting_type ?? 'detail');
                $courierType = e($cat->courier_type ?? 'keduanya');
                $html .= '<option value="' . $cat->id . '" data-setting-type="' . $settingType . '" data-courier-type="' . $courierType . '" ' . $selected . '>' . $prefix . e($cat->name) . '</option>';
                $html .= buildCategoryOptions($categories, $cat->id, $prefix . '&nbsp;&nbsp;&nbsp;&nbsp;', $selectedId);
            }
            return $html;
        }
    }

    $variantData = $variants->map(function ($v) {
        $img = \App\Models\Product\Image::where('variant_id', $v->id)->first();
        $imgPath = $img ? $img->image : ($v->attributes['image'] ?? null);
        $imgUrl = $img ? $img->url : ($imgPath ? media_url($imgPath) : null);

        return [
            'id' => $v->id,
            'sku' => $v->sku ?? '',
            'variant_name' => $v->variant_name ?? '',
            'base_price' => $v->base_price !== null ? (float)$v->base_price : 0,
            'sell_price' => $v->sell_price !== null ? (float)$v->sell_price : ($v->base_price !== null ? (float)$v->base_price : 0),
            'shipping_cost' => $v->shipping_cost !== null ? (float)$v->shipping_cost : 0,
            'stock_qty' => $v->stock_quantity ?? 0,
            'length' => $v->length !== null && $v->length !== '' ? (float)$v->length : (isset($v->attributes['length']) ? (float)$v->attributes['length'] : null),
            'width' => $v->width !== null && $v->width !== '' ? (float)$v->width : (isset($v->attributes['width']) ? (float)$v->attributes['width'] : null),
            'height' => $v->height !== null && $v->height !== '' ? (float)$v->height : (isset($v->attributes['height']) ? (float)$v->attributes['height'] : null),
            'weight' => $v->weight !== null && $v->weight !== '' ? (float)$v->weight : (isset($v->attributes['weight']) ? (float)$v->attributes['weight'] : null),
            'status' => $v->status ?? 1,
            'attributes' => $v->attributes ?? null,
            'image' => $imgPath,
            'image_url' => $imgUrl,
        ];
    })->values()->all();

    $colorData = $colors->map(function ($c) {
        return [
            'id' => $c->id,
            'color_name' => $c->color_name ?? '',
            'color_code' => $c->color_code ?? '#FF0000',
        ];
    })->values()->all();

    $existingGalleryImages = ($product->images ?? collect([]))->whereNull('variant_id')->sortBy('sort_order')->map(function($img) {
        return [
            'id' => $img->id,
            'image' => $img->image,
            'url' => $img->url,
            'sort_order' => $img->sort_order ?? 0,
        ];
    })->values()->all();

    $productCode = $product->code ?? '';
    if (empty($productCode)) {
        $datePrefix = 'PRD' . date('dmy');
        $lastProduct = \App\Models\Product\Product::withoutGlobalScope('not-deleted')
            ->where('code', 'like', $datePrefix . '%')
            ->orderBy('code', 'desc')
            ->first();
            
        if ($lastProduct && preg_match('/(\d{5})$/', $lastProduct->code, $matches)) {
            $lastNumber = (int) $matches[1];
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }
        
        $productCode = $datePrefix . str_pad((string)$newNumber, 5, '0', STR_PAD_LEFT);
    }
@endphp

<div class="w-full space-y-6 pb-16">
    <!-- Header Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-outline-variant/40 pb-4">
        <div class="space-y-1">
            <div class="flex items-center gap-3">
                <h1 class="font-headline-lg text-2xl font-bold text-on-surface">
                    {{ $isEdit ? 'Edit Produk' : 'Tambah Produk Baru' }}
                </h1>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-mono font-bold bg-primary/10 text-primary border border-primary/20">
                    {{ $productCode }}
                </span>
                @if($isEdit)
                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold {{ ($product->status ?? 1) ? 'bg-success/10 text-success' : 'bg-danger/10 text-danger' }}">
                        <span class="w-1.5 h-1.5 rounded-full bg-current"></span> {{ ($product->status ?? 1) ? 'Aktif' : 'Nonaktif' }}
                    </span>
                @endif
            </div>
            <nav class="flex items-center gap-2 text-xs text-on-surface-variant">
                <a href="{{ route('dashboard') }}" class="text-primary hover:underline">Dashboard</a>
                <span class="material-symbols-outlined text-[14px]">chevron_right</span>
                <a href="{{ route('products.index') }}" class="text-primary hover:underline">Produk</a>
                <span class="material-symbols-outlined text-[14px]">chevron_right</span>
                <span class="text-on-surface">{{ $isEdit ? ($product->name ?? 'Edit') : 'Tambah Baru' }}</span>
            </nav>
        </div>

        <div class="flex items-center gap-3">
            <a href="{{ route('products.index') }}" class="inline-flex items-center gap-1.5 px-4 py-2 border border-outline-variant text-on-surface-variant hover:bg-surface-container rounded-xl text-xs font-semibold transition-colors">
                <span class="material-symbols-outlined text-[16px]">arrow_back</span>
                <span>Batal</span>
            </a>
            @if($isEdit)
                <a href="{{ route('products.show', $product->id) }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-surface-container-high hover:bg-surface-container-highest text-on-surface rounded-xl text-xs font-semibold transition-colors">
                    <span class="material-symbols-outlined text-[16px]">visibility</span>
                    <span>Lihat Detail</span>
                </a>
            @endif
            <button type="button" onclick="submitProductForm()" class="btn-save inline-flex items-center gap-2 px-5 py-2 bg-primary text-white hover:opacity-90 rounded-xl text-xs font-bold transition-all shadow-sm active:scale-95">
                <span class="material-symbols-outlined text-[18px]">save</span>
                <span>{{ $isEdit ? 'Simpan Perubahan' : 'Simpan Produk' }}</span>
            </button>
        </div>
    </div>

    @include('layouts.partials.product-submenu')

    <!-- Main Form -->
    <form id="productForm" method="POST" action="{{ $isEdit ? route('products.update', $product->id) : route('products.store') }}" enctype="multipart/form-data" class="w-full space-y-6">
        @csrf
        @if($isEdit) @method('PUT') @endif
        <input type="hidden" name="code" id="productCodeHidden" value="{{ $productCode }}">
        <input type="hidden" name="variants" id="variantsInput" value="">
        <input type="hidden" name="colors" id="colorsInput" value="{{ json_encode($colorData) }}">

        <!-- 1. INFORMASI DASAR PRODUK & FOTO UTAMA -->
        <div class="w-full bg-white rounded-2xl shadow-sm border border-outline-variant/30 p-6 space-y-6">
            <div class="border-b border-outline-variant/20 pb-3">
                <h2 class="text-base font-bold text-on-surface flex items-center gap-2">
                    <span class="material-symbols-outlined text-primary text-[20px]">description</span>
                    Informasi Produk & Foto Utama
                </h2>
                <p class="text-xs text-on-surface-variant mt-0.5">Nama produk, URL slug, kategori, brand, garansi, dan foto utama katalog.</p>
            </div>

            <!-- Top Section: Data Fields & Foto Utama Thumbnail -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Data Fields (2 Cols on lg) -->
                <div class="lg:col-span-2 grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Kode Produk -->
                    <div class="space-y-1.5">
                        <label class="block text-xs font-bold text-on-surface-variant uppercase tracking-wider">
                            Kode Produk <span class="text-[11px] font-normal text-on-surface-variant">(Otomatis)</span>
                        </label>
                        <input type="text" value="{{ $product->code ?? 'Auto Generated' }}" class="w-full px-3.5 py-2 bg-surface-container-low border border-outline-variant rounded-xl text-xs font-mono text-on-surface-variant cursor-not-allowed" readonly>
                    </div>

                    <!-- Nama Produk -->
                    <div class="space-y-1.5">
                        <label class="block text-xs font-bold text-on-surface-variant uppercase tracking-wider">
                            Nama Produk <span class="text-danger">*</span>
                        </label>
                        <input type="text" name="name" id="productNameInput" value="{{ old('name', $product->name ?? '') }}" required placeholder="Contoh: Royal Foam Exclusive / Nama Produk" class="w-full px-3.5 py-2 border border-outline-variant rounded-xl text-sm font-medium focus:ring-2 focus:ring-primary/20 focus:border-primary focus:outline-none transition-all">
                    </div>

                    <!-- Slug Produk (Disabled / Otomatis) -->
                    <div class="space-y-1.5">
                        <label class="block text-xs font-bold text-on-surface-variant uppercase tracking-wider">
                            Product Slug (URL)
                        </label>
                        <div class="flex items-center">
                            <span class="px-3 py-2 bg-surface-container border border-r-0 border-outline-variant rounded-l-xl text-xs text-on-surface-variant font-mono select-none">/products/</span>
                            <input type="text" name="slug" id="productSlug" value="{{ old('slug', $product->slug ?? '') }}" class="w-full px-3.5 py-2 border border-outline-variant rounded-r-xl text-xs font-mono bg-surface-container text-on-surface-variant cursor-not-allowed select-none focus:outline-none" placeholder="auto-slug" readonly tabindex="-1">
                        </div>
                        <p class="text-[10px] text-on-surface-variant">Slug otomatis dibuat dari nama produk.</p>
                    </div>

                    <!-- Kategori -->
                    <div class="space-y-1.5">
                        <label class="block text-xs font-bold text-on-surface-variant uppercase tracking-wider">
                            Kategori Produk <span class="text-danger">*</span>
                        </label>
                        <select name="category_id" id="categorySelect" class="w-full px-3.5 py-2 border border-outline-variant rounded-xl text-sm focus:ring-2 focus:ring-primary/20 focus:outline-none bg-white select2-enable">
                            <option value="">Pilih Kategori</option>
                            {!! buildCategoryOptions(\App\Models\Product\Category::all(), null, '', old('category_id', $product->category_id ?? '')) !!}
                        </select>
                    </div>

                    <!-- Brand -->
                    <div class="space-y-1.5">
                        <label class="block text-xs font-bold text-on-surface-variant uppercase tracking-wider">
                            Brand / Merek
                        </label>
                        <select name="brand_id" id="brandSelect" class="w-full px-3.5 py-2 border border-outline-variant rounded-xl text-sm focus:ring-2 focus:ring-primary/20 focus:outline-none bg-white select2-enable">
                            <option value="">Pilih Brand</option>
                            @foreach(\App\Models\Product\Brand::all() as $brand)
                                <option value="{{ $brand->id }}" {{ old('brand_id', $product->brand_id ?? '') == $brand->id ? 'selected' : '' }}>
                                    {{ $brand->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Garansi -->
                    <div class="space-y-1.5">
                        <label class="block text-xs font-bold text-on-surface-variant uppercase tracking-wider">
                            Durasi Garansi
                        </label>
                        <div class="flex gap-2">
                            <input type="text" name="warranty_duration" id="warrantyInput" value="{{ old('warranty_duration', $product->warranty_duration ?? '') }}" class="w-full px-3.5 py-2 border border-outline-variant rounded-xl text-xs focus:ring-2 focus:ring-primary/20 focus:outline-none" placeholder="Contoh: 15 Tahun">
                            <div class="flex items-center gap-1 flex-shrink-0">
                                <button type="button" onclick="setWarranty('5 Tahun')" class="px-2.5 py-1.5 bg-surface-container hover:bg-surface-container-high rounded-lg text-[11px] font-semibold text-on-surface-variant">5 Th</button>
                                <button type="button" onclick="setWarranty('10 Tahun')" class="px-2.5 py-1.5 bg-surface-container hover:bg-surface-container-high rounded-lg text-[11px] font-semibold text-on-surface-variant">10 Th</button>
                                <button type="button" onclick="setWarranty('15 Tahun')" class="px-2.5 py-1.5 bg-surface-container hover:bg-surface-container-high rounded-lg text-[11px] font-semibold text-on-surface-variant">15 Th</button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Foto Utama (Thumbnail Produk) -->
                <div class="space-y-2 flex flex-col">
                    <label class="block text-xs font-bold text-on-surface-variant uppercase tracking-wider">
                        Foto Utama (Thumbnail Katalog)
                    </label>

                    <div class="flex-1 border-2 border-dashed border-outline-variant/60 hover:border-primary/50 rounded-2xl p-4 flex flex-col items-center justify-center bg-surface-container-lowest transition-all min-h-[190px]">
                        <input type="file" name="thumbnail_file" id="thumbnailInput" accept="image/*" class="hidden" onchange="previewThumbnail(this)">

                        <div id="thumbnailPreviewContainer" class="{{ ($product->thumbnail ?? false) ? '' : 'hidden' }} space-y-3 text-center w-full">
                            <img id="thumbnailPreview" src="{{ ($product->thumbnail_url ?? false) ? $product->thumbnail_url : '' }}" alt="Thumbnail" class="max-h-36 mx-auto rounded-xl object-contain shadow-xs border border-outline-variant/30">
                            <div class="flex items-center justify-center gap-2">
                                <button type="button" onclick="document.getElementById('thumbnailInput').click()" class="px-3 py-1 bg-primary text-white rounded-lg text-xs font-semibold hover:opacity-90">
                                    Ganti Foto
                                </button>
                                <button type="button" onclick="clearThumbnail()" class="px-3 py-1 border border-outline-variant text-danger hover:bg-danger/5 rounded-lg text-xs font-semibold">
                                    Hapus
                                </button>
                            </div>
                        </div>

                        <div id="thumbnailEmptyPlaceholder" class="{{ ($product->thumbnail ?? false) ? 'hidden' : '' }} py-4 text-center space-y-2">
                            <span class="material-symbols-outlined text-[40px] text-primary/60">add_photo_alternate</span>
                            <div>
                                <p class="text-xs font-bold text-on-surface">Pilih Foto Utama Produk</p>
                                <p class="text-[11px] text-on-surface-variant">Foto tampilan di etalase/katalog utama</p>
                            </div>
                            <button type="button" onclick="document.getElementById('thumbnailInput').click()" class="px-4 py-1.5 bg-primary text-white rounded-xl text-xs font-bold hover:opacity-90 transition-all shadow-xs inline-flex items-center gap-1.5">
                                <span class="material-symbols-outlined text-[16px]">upload</span>
                                <span>Upload Thumbnail</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Galeri Foto Produk (Bisa Upload Banyak Foto) -->
            <div class="space-y-3 pt-4 border-t border-outline-variant/20">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                    <div>
                        <label class="block text-xs font-bold text-on-surface-variant uppercase tracking-wider">
                            Galeri Foto Produk 
                        </label>
                        <p class="text-[11px] text-on-surface-variant">Upload banyak foto produk untuk slider/galeri katalog utama. Urutan foto dapat digeser (drag & drop atau tombol panah).</p>
                    </div>
                    <button type="button" onclick="document.getElementById('galleryImagesInput').click()" class="px-3.5 py-1.5 bg-primary/10 hover:bg-primary/20 border border-primary/30 text-primary rounded-xl text-xs font-bold inline-flex items-center gap-1.5 transition-all shadow-2xs self-start sm:self-auto">
                        <span class="material-symbols-outlined text-[16px]">add_photo_alternate</span>
                        <span>+ Tambah Foto Galeri</span>
                    </button>
                </div>

                <input type="file" id="galleryImagesInput" multiple accept="image/*" class="hidden" onchange="handleGalleryImagesPicked(this)">

                <div id="galleryImagesContainer" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-3 min-h-[70px] p-3 rounded-2xl bg-surface-container-lowest border border-dashed border-outline-variant/60">
                    <!-- Populated by renderGalleryImages() -->
                </div>
            </div>

            <!-- Descriptions -->
            <div class="space-y-4 pt-2 border-t border-outline-variant/20">
                <!-- Short Description -->
                <div class="space-y-1.5">
                    <label class="block text-xs font-bold text-on-surface-variant uppercase tracking-wider">
                        Ringkasan Singkat (Short Description)
                    </label>
                    <textarea name="short_description" rows="2" class="w-full px-3.5 py-2 border border-outline-variant rounded-xl text-xs focus:ring-2 focus:ring-primary/20 focus:outline-none leading-relaxed" placeholder="Ringkasan singkat produk untuk preview etalase...">{{ old('short_description', $product->short_description ?? '') }}</textarea>
                </div>

                <!-- Full Description (Quill) -->
                <div class="space-y-1.5">
                    <label class="block text-xs font-bold text-on-surface-variant uppercase tracking-wider">
                        Deskripsi Lengkap Produk
                    </label>
                    <div class="border border-outline-variant rounded-xl overflow-hidden focus-within:ring-2 focus-within:ring-primary/20 transition-all">
                        <div id="quill-editor">{!! old('description', $product->description ?? '') !!}</div>
                    </div>
                    <input type="hidden" name="description" id="description-input" value="{{ old('description', $product->description ?? '') }}">
                </div>
            </div>
        </div>

        <!-- 3. PENGATURAN KURIR & SKEMA ONGKOS KIRIM -->
        <div class="w-full bg-white rounded-2xl shadow-sm border border-outline-variant/30 p-6 space-y-5" id="shippingSectionContainer">
            <div class="border-b border-outline-variant/20 pb-3 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div class="space-y-1">
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-outlined text-primary text-[22px]">local_shipping</span>
                        <h2 class="text-base font-bold text-on-surface">
                            Pengaturan Kurir & Skema Ongkos Kirim
                        </h2>
                    </div>
                    <p class="text-xs text-on-surface-variant">
                        Atur tipe kurir pengiriman (Toko / Expedisi / Keduanya) dan skema kalkulasi ongkos kirim (Fixed Rate atau dari Dimensi & Berat Produk).
                    </p>
                </div>
                <div id="categoryShippingBadge" class="hidden shrink-0">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-primary/10 text-primary border border-primary/20 text-xs font-semibold rounded-xl">
                        <span class="material-symbols-outlined text-[15px]">category</span>
                        <span id="categoryShippingBadgeText">Pengaturan Kategori</span>
                    </span>
                </div>
            </div>

            <!-- Banner Notifikasi Pengaturan Global Kategori (Jika Kategori = Global) -->
            <div id="categoryGlobalNotice" class="hidden p-4 rounded-xl bg-blue-50/80 border border-blue-200 text-blue-900 space-y-2">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <div class="flex items-start gap-2.5">
                        <span class="material-symbols-outlined text-primary text-[20px] mt-0.5 shrink-0">info</span>
                        <div>
                            <p class="text-xs font-bold text-blue-950">Kategori Menggunakan Pengaturan Kurir Global</p>
                            <p class="text-xs text-blue-800 mt-0.5" id="categoryGlobalDesc">
                                Produk ini secara default mengikuti pengaturan global kategori.
                            </p>
                        </div>
                    </div>
                    <button type="button" onclick="enableShippingOverride()" id="btnOverrideShipping" class="px-3 py-1.5 bg-white border border-blue-300 hover:bg-blue-100 text-blue-900 rounded-xl text-xs font-bold transition-all shadow-sm shrink-0 self-start sm:self-auto">
                        Kustomisasi Khusus Produk Ini
                    </button>
                </div>
            </div>

            <!-- Pilihan Tipe Kurir -->
            <div class="space-y-2.5">
                <label class="block text-xs font-bold text-on-surface-variant uppercase tracking-wider flex items-center gap-1.5">
                    <span>Pilihan Tipe Kurir</span>
                    <span class="text-danger">*</span>
                </label>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3" id="courierTypeCardsContainer">
                    <!-- Kurir Toko -->
                    <label class="courier-card relative flex items-start p-3.5 border rounded-xl cursor-pointer transition-all hover:bg-surface-container-lowest {{ old('courier_type', $product->courier_type ?? 'keduanya') === 'toko' ? 'border-primary bg-primary/5 ring-1 ring-primary/30' : 'border-outline-variant/60' }}">
                        <input type="radio" name="courier_type" value="toko" {{ old('courier_type', $product->courier_type ?? 'keduanya') === 'toko' ? 'checked' : '' }} onchange="onCourierTypeChanged()" class="mt-0.5 mr-3 text-primary focus:ring-primary">
                        <div class="space-y-0.5">
                            <div class="flex items-center gap-1.5 font-bold text-xs text-on-surface">
                                <span class="material-symbols-outlined text-primary text-[18px]">store</span>
                                <span>Pengiriman by Toko</span>
                            </div>
                            <p class="text-[11px] text-on-surface-variant">Armada / kurir internal toko</p>
                        </div>
                    </label>

                    <!-- Kurir Expedisi -->
                    <label class="courier-card relative flex items-start p-3.5 border rounded-xl cursor-pointer transition-all hover:bg-surface-container-lowest {{ old('courier_type', $product->courier_type ?? 'keduanya') === 'expedisi' ? 'border-primary bg-primary/5 ring-1 ring-primary/30' : 'border-outline-variant/60' }}">
                        <input type="radio" name="courier_type" value="expedisi" {{ old('courier_type', $product->courier_type ?? 'keduanya') === 'expedisi' ? 'checked' : '' }} onchange="onCourierTypeChanged()" class="mt-0.5 mr-3 text-primary focus:ring-primary">
                        <div class="space-y-0.5">
                            <div class="flex items-center gap-1.5 font-bold text-xs text-on-surface">
                                <span class="material-symbols-outlined text-primary text-[18px]">local_shipping</span>
                                <span>Pengiriman by Expedisi</span>
                            </div>
                            <p class="text-[11px] text-on-surface-variant">Logistik pihak ketiga (JNE, J&T, Kargo)</p>
                        </div>
                    </label>

                    <!-- Keduanya -->
                    <label class="courier-card relative flex items-start p-3.5 border rounded-xl cursor-pointer transition-all hover:bg-surface-container-lowest {{ old('courier_type', $product->courier_type ?? 'keduanya') === 'keduanya' ? 'border-primary bg-primary/5 ring-1 ring-primary/30' : 'border-outline-variant/60' }}">
                        <input type="radio" name="courier_type" value="keduanya" {{ old('courier_type', $product->courier_type ?? 'keduanya') === 'keduanya' ? 'checked' : '' }} onchange="onCourierTypeChanged()" class="mt-0.5 mr-3 text-primary focus:ring-primary">
                        <div class="space-y-0.5">
                            <div class="flex items-center gap-1.5 font-bold text-xs text-on-surface">
                                <span class="material-symbols-outlined text-primary text-[18px]">sync_alt</span>
                                <span>Keduanya (Toko & Expedisi)</span>
                            </div>
                            <p class="text-[11px] text-on-surface-variant">Pembeli bebas memilih metode kurir</p>
                        </div>
                    </label>
                </div>
            </div>

            <!-- Skema Ongkos Kirim -->
            <div class="space-y-2.5 pt-3 border-t border-outline-variant/20">
                <label class="block text-xs font-bold text-on-surface-variant uppercase tracking-wider flex items-center gap-1.5">
                    <span>Skema Ongkos Kirim</span>
                    <span class="text-danger">*</span>
                </label>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3" id="shippingSchemeCardsContainer">
                    <!-- Kalkulasi Dimensi & Berat -->
                    <label class="scheme-card relative flex items-start p-3.5 border rounded-xl cursor-pointer transition-all hover:bg-surface-container-lowest {{ old('shipping_scheme', $product->shipping_scheme ?? 'dimension') === 'dimension' ? 'border-primary bg-primary/5 ring-1 ring-primary/30' : 'border-outline-variant/60' }}">
                        <input type="radio" name="shipping_scheme" value="dimension" {{ old('shipping_scheme', $product->shipping_scheme ?? 'dimension') === 'dimension' ? 'checked' : '' }} onchange="onShippingSchemeChanged()" class="mt-0.5 mr-3 text-primary focus:ring-primary">
                        <div class="space-y-0.5">
                            <div class="flex items-center gap-1.5 font-bold text-xs text-on-surface">
                                <span class="material-symbols-outlined text-primary text-[18px]">straighten</span>
                                <span>Hitung dari Dimensi & Berat Produk</span>
                            </div>
                            <p class="text-[11px] text-on-surface-variant">Dihitung otomatis dari Panjang × Lebar × Tinggi dan Berat aktual sesuai tarif ekspedisi/logistik.</p>
                        </div>
                    </label>

                    <!-- Fixed Flat Rate -->
                    <label class="scheme-card relative flex items-start p-3.5 border rounded-xl cursor-pointer transition-all hover:bg-surface-container-lowest {{ old('shipping_scheme', $product->shipping_scheme ?? 'dimension') === 'fixed' ? 'border-primary bg-primary/5 ring-1 ring-primary/30' : 'border-outline-variant/60' }}">
                        <input type="radio" name="shipping_scheme" value="fixed" {{ old('shipping_scheme', $product->shipping_scheme ?? 'dimension') === 'fixed' ? 'checked' : '' }} onchange="onShippingSchemeChanged()" class="mt-0.5 mr-3 text-primary focus:ring-primary">
                        <div class="space-y-0.5">
                            <div class="flex items-center gap-1.5 font-bold text-xs text-on-surface">
                                <span class="material-symbols-outlined text-primary text-[18px]">payments</span>
                                <span>Ongkos Kirim Tetap (Fixed Rate)</span>
                            </div>
                            <p class="text-[11px] text-on-surface-variant">Langsung di-set harga ongkir tetap (misal: Rp 500.000 untuk unit produk).</p>
                        </div>
                    </label>
                </div>
            </div>

            <!-- Input Ongkir Tetap (Hanya muncul jika Fixed Rate) -->
            <div id="fixedShippingCostContainer" class="{{ old('shipping_scheme', $product->shipping_scheme ?? 'dimension') === 'fixed' ? '' : 'hidden' }} p-4 bg-amber-50/70 border border-amber-200 rounded-xl space-y-3 transition-all">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                    <div class="space-y-0.5">
                        <label class="block text-xs font-bold text-amber-950 uppercase tracking-wider flex items-center gap-1.5">
                            <span class="material-symbols-outlined text-amber-700 text-[18px]">payments</span>
                            <span>Tarif Ongkos Kirim Tetap Default (Rp)</span>
                        </label>
                        <p class="text-[11px] text-amber-900">
                            Tarif dasar/default pengiriman. <strong>Ongkos kirim tetap dapat diset berbeda per varian produk</strong> (misal: Satuan Rp 150rb, Fullset Rp 500rb) pada tabel variasi di bawah.
                        </p>
                    </div>
                </div>
                <div class="flex items-center gap-3 flex-wrap">
                    <div class="relative w-full sm:w-64">
                        <span class="absolute left-3.5 top-2 text-xs text-on-surface-variant font-bold">Rp</span>
                        <input type="number" step="1000" min="0" name="shipping_cost" id="productShippingCost" value="{{ old('shipping_cost', $product->shipping_cost ?? 0) }}" placeholder="Contoh: 500000" class="w-full pl-10 pr-3.5 py-2 border border-outline-variant rounded-xl text-xs font-bold text-on-surface focus:ring-2 focus:ring-primary/20 focus:outline-none bg-white">
                    </div>
                    <button type="button" onclick="applyDefaultShippingToAllVariants()" class="px-3.5 py-2 bg-amber-700 hover:bg-amber-800 text-white rounded-xl text-xs font-bold transition-all shadow-sm flex items-center gap-1.5 active:scale-95">
                        <span class="material-symbols-outlined text-[16px]">sync_alt</span>
                        <span>Terapkan ke Semua Varian</span>
                    </button>
                </div>
            </div>

            <!-- Dimensi & Berat Fisik Produk -->
            <div class="space-y-3 pt-3 border-t border-outline-variant/20" id="dimensionInputsContainer">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                    <div>
                        <h3 class="text-xs font-bold text-on-surface-variant uppercase tracking-wider flex items-center gap-1.5">
                            <span class="material-symbols-outlined text-primary text-[16px]">straighten</span>
                            <span>Dimensi & Berat Fisik Produk</span>
                        </h3>
                        <p class="text-[11px] text-on-surface-variant mt-0.5">
                            Digunakan untuk kalkulasi tarif ongkir volumetrik maupun packing logistik ekspedisi.
                        </p>
                    </div>
                    <div id="volumetricPreviewBadge" class="hidden text-[11px] px-3 py-1 bg-surface-container-highest text-on-surface font-mono font-medium rounded-lg shrink-0 border border-outline-variant/40">
                        Volumetrik: <span id="volumetricWeightText" class="font-bold text-primary">0 kg</span>
                    </div>
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                    <!-- Panjang -->
                    <div class="space-y-1.5">
                        <label class="block text-xs font-bold text-on-surface-variant uppercase tracking-wider">
                            Panjang (cm)
                        </label>
                        <div class="relative">
                            <input type="number" step="0.1" min="0" name="length" id="productLength" value="{{ old('length', $product->length ?? '') }}" placeholder="0" oninput="calculateVolumetricWeight()" class="w-full px-3.5 py-2 border border-outline-variant rounded-xl text-xs focus:ring-2 focus:ring-primary/20 focus:outline-none pr-10">
                            <span class="absolute right-3 top-2 text-[11px] text-on-surface-variant font-medium">cm</span>
                        </div>
                    </div>

                    <!-- Lebar -->
                    <div class="space-y-1.5">
                        <label class="block text-xs font-bold text-on-surface-variant uppercase tracking-wider">
                            Lebar (cm)
                        </label>
                        <div class="relative">
                            <input type="number" step="0.1" min="0" name="width" id="productWidth" value="{{ old('width', $product->width ?? '') }}" placeholder="0" oninput="calculateVolumetricWeight()" class="w-full px-3.5 py-2 border border-outline-variant rounded-xl text-xs focus:ring-2 focus:ring-primary/20 focus:outline-none pr-10">
                            <span class="absolute right-3 top-2 text-[11px] text-on-surface-variant font-medium">cm</span>
                        </div>
                    </div>

                    <!-- Tinggi -->
                    <div class="space-y-1.5">
                        <label class="block text-xs font-bold text-on-surface-variant uppercase tracking-wider">
                            Tinggi (cm)
                        </label>
                        <div class="relative">
                            <input type="number" step="0.1" min="0" name="height" id="productHeight" value="{{ old('height', $product->height ?? '') }}" placeholder="0" oninput="calculateVolumetricWeight()" class="w-full px-3.5 py-2 border border-outline-variant rounded-xl text-xs focus:ring-2 focus:ring-primary/20 focus:outline-none pr-10">
                            <span class="absolute right-3 top-2 text-[11px] text-on-surface-variant font-medium">cm</span>
                        </div>
                    </div>

                    <!-- Berat -->
                    <div class="space-y-1.5">
                        <label class="block text-xs font-bold text-on-surface-variant uppercase tracking-wider">
                            Berat (kg)
                        </label>
                        <div class="relative">
                            <input type="number" step="0.01" min="0" name="weight" id="productWeight" value="{{ old('weight', $product->weight ?? '') }}" placeholder="0" oninput="calculateVolumetricWeight()" class="w-full px-3.5 py-2 border border-outline-variant rounded-xl text-xs focus:ring-2 focus:ring-primary/20 focus:outline-none pr-10">
                            <span class="absolute right-3 top-2 text-[11px] text-on-surface-variant font-medium">kg</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 2. VARIASI PRODUK, HARGA & WARNA -->
        <div class="w-full bg-white rounded-2xl shadow-sm border border-outline-variant/30 p-6 space-y-6">
            <!-- Header with ERP & POS Guidance -->
            <div class="border-b border-outline-variant/20 pb-4 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div class="space-y-1">
                    <h2 class="text-base font-bold text-on-surface flex items-center gap-2">
                        <span class="material-symbols-outlined text-primary text-[22px]">layers</span>
                        Variasi Produk, Harga & Warna
                    </h2>
                    <p class="text-xs text-on-surface-variant">
                        Atur variasi penentu harga (Ukuran, Kelengkapan, Tebal) dan pilihan warna katalog tanpa membuat SKU berantakan.
                    </p>
                </div>
                <div class="flex items-center gap-2 shrink-0">
                    <button type="button" onclick="resetAllVariations()" class="text-xs text-on-surface-variant hover:text-danger flex items-center gap-1.5 px-3 py-1.5 rounded-xl border border-outline-variant/40 hover:border-danger/30 hover:bg-danger/5 transition-all" title="Reset ke data awal">
                        <span class="material-symbols-outlined text-[15px]">refresh</span>
                        <span>Reset Variasi</span>
                    </button>
                </div>
            </div>

            <!-- Guidance Info Banner: Anti-Pusing Guide -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-3 p-4 bg-surface-container-lowest border border-outline-variant/50 rounded-2xl text-xs">
                <div class="flex items-start gap-2.5">
                    <span class="material-symbols-outlined text-primary text-[20px] shrink-0 mt-0.5">price_check</span>
                    <div>
                        <span class="font-bold text-primary block">1. Variasi Penentu Harga (Ada di Tabel Bawah):</span>
                        <p class="text-on-surface-variant text-[11px] leading-relaxed mt-0.5">
                            <strong>Ukuran</strong>, <strong>Kelengkapan</strong> (Mattress Only vs Fullset), dan <strong>Ketebalan</strong> yang memiliki selisih harga jual masing-masing memiliki baris harga & SKU unik di ERP.
                        </p>
                    </div>
                </div>
                <div class="flex items-start gap-2.5">
                    <span class="material-symbols-outlined text-emerald-600 text-[20px] shrink-0 mt-0.5">palette</span>
                    <div>
                        <span class="font-bold text-emerald-700 block">2. Pilihan Warna Produk (Tidak Mengubah Harga):</span>
                        <p class="text-on-surface-variant text-[11px] leading-relaxed mt-0.5">
                            Warna kain/matras (Hitam, Putih, Navy, dll) dipilih oleh pembeli sebagai opsi swatch di katalog/POS tanpa melipatgandakan baris harga dan tanpa mengubah SKU.
                        </p>
                    </div>
                </div>
            </div>

            <!-- BAGIAN 1: PILIHAN WARNA PRODUK (PRODUCT COLORS) -->
            <div class="bg-surface-container-lowest/70 border border-outline-variant/50 rounded-2xl p-4 space-y-3.5">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-1 border-b border-outline-variant/20 pb-2.5">
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-outlined text-emerald-600 text-[20px]">palette</span>
                        <div>
                            <h3 class="text-xs font-bold text-on-surface uppercase tracking-wider">
                                Pilihan Warna Produk (Kain / Matras)
                            </h3>
                            <p class="text-[11px] text-on-surface-variant">
                                Opsi warna katalog di POS/Web. Warna tidak mengubah harga dan tidak membludak di tabel SKU.
                            </p>
                        </div>
                    </div>
                    <span id="colorCountBadge" class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200/60 self-start sm:self-auto">0 Warna</span>
                </div>

                <!-- Active Colors Container -->
                <div id="productColorsContainer" class="flex flex-wrap items-center gap-2 min-h-[46px] p-2.5 bg-white border border-outline-variant/70 rounded-xl">
                    <!-- Dynamic color chips injected by JS -->
                </div>

                <!-- Add Custom Color Input -->
                <div class="grid grid-cols-1 sm:grid-cols-12 gap-2 items-center">
                    <div class="sm:col-span-4 flex items-center gap-2">
                        <div class="relative flex items-center">
                            <input type="color" id="customColorPicker" value="#1e293b" class="w-9 h-9 p-0.5 rounded-lg border border-outline-variant cursor-pointer bg-white" onchange="document.getElementById('customColorHex').value = this.value">
                        </div>
                        <input type="text" id="customColorHex" value="#1e293b" placeholder="#HEX" class="w-24 px-2.5 py-1.5 bg-white border border-outline-variant rounded-xl text-xs font-mono uppercase focus:ring-2 focus:ring-primary/20 focus:outline-none" oninput="if(/^#[0-9A-F]{6}$/i.test(this.value)){ document.getElementById('customColorPicker').value = this.value; }">
                    </div>
                    <div class="sm:col-span-5">
                        <input type="text" id="customColorName" placeholder="Nama warna (misal: Hitam, Navy, Sage, Putih...)" class="w-full px-3 py-1.5 bg-white border border-outline-variant rounded-xl text-xs focus:ring-2 focus:ring-primary/20 focus:outline-none" onkeydown="if(event.key==='Enter'){ event.preventDefault(); addCustomColor(); }">
                    </div>
                    <div class="sm:col-span-3 flex justify-end">
                        <button type="button" onclick="addCustomColor()" class="w-full sm:w-auto px-4 py-2 bg-primary/10 hover:bg-primary/20 text-primary rounded-xl text-xs font-bold flex items-center justify-center gap-1 transition-all">
                            <span class="material-symbols-outlined text-[16px]">add</span>
                            <span>Tambah Warna</span>
                        </button>
                    </div>
                </div>

                <!-- Quick Presets -->
                <div class="flex items-center gap-1.5 flex-wrap pt-1 text-[11px] text-on-surface-variant">
                    <span class="font-medium mr-1">⚡ Preset Cepat:</span>
                    <button type="button" onclick="addColorOption('Hitam', '#1e293b')" class="px-2.5 py-1 bg-white hover:bg-surface-container-high border border-outline-variant/60 rounded-lg font-medium flex items-center gap-1.5 shadow-2xs transition-colors">
                        <span class="w-3 h-3 rounded-full bg-[#1e293b] border border-black/20"></span> Hitam
                    </button>
                    <button type="button" onclick="addColorOption('Navy', '#1e3a8a')" class="px-2.5 py-1 bg-white hover:bg-surface-container-high border border-outline-variant/60 rounded-lg font-medium flex items-center gap-1.5 shadow-2xs transition-colors">
                        <span class="w-3 h-3 rounded-full bg-[#1e3a8a] border border-black/20"></span> Navy
                    </button>
                    <button type="button" onclick="addColorOption('Putih', '#ffffff')" class="px-2.5 py-1 bg-white hover:bg-surface-container-high border border-outline-variant/60 rounded-lg font-medium flex items-center gap-1.5 shadow-2xs transition-colors">
                        <span class="w-3 h-3 rounded-full bg-[#ffffff] border border-black/20"></span> Putih
                    </button>
                    <button type="button" onclick="addColorOption('Abu-abu', '#64748b')" class="px-2.5 py-1 bg-white hover:bg-surface-container-high border border-outline-variant/60 rounded-lg font-medium flex items-center gap-1.5 shadow-2xs transition-colors">
                        <span class="w-3 h-3 rounded-full bg-[#64748b] border border-black/20"></span> Abu-abu
                    </button>
                    <button type="button" onclick="addColorOption('Cokelat', '#78350f')" class="px-2.5 py-1 bg-white hover:bg-surface-container-high border border-outline-variant/60 rounded-lg font-medium flex items-center gap-1.5 shadow-2xs transition-colors">
                        <span class="w-3 h-3 rounded-full bg-[#78350f] border border-black/20"></span> Cokelat
                    </button>
                    <button type="button" onclick="addColorOption('Cream', '#fef3c7')" class="px-2.5 py-1 bg-white hover:bg-surface-container-high border border-outline-variant/60 rounded-lg font-medium flex items-center gap-1.5 shadow-2xs transition-colors">
                        <span class="w-3 h-3 rounded-full bg-[#fef3c7] border border-black/20"></span> Cream
                    </button>
                    <button type="button" onclick="addColorOption('Sage', '#84a98c')" class="px-2.5 py-1 bg-white hover:bg-surface-container-high border border-outline-variant/60 rounded-lg font-medium flex items-center gap-1.5 shadow-2xs transition-colors">
                        <span class="w-3 h-3 rounded-full bg-[#84a98c] border border-black/20"></span> Sage
                    </button>
                </div>
            </div>

            <!-- BAGIAN 2: KONFIGURASI PENENTU VARIASI HARGA (UKURAN, KELENGKAPAN, KETEBALAN) -->
            <div class="space-y-4">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-outline-variant/20 pb-2.5">
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-outlined text-primary text-[20px]">tune</span>
                        <div>
                            <h3 class="text-xs font-bold text-on-surface uppercase tracking-wider">
                                Konfigurasi Penentu Harga Produk
                            </h3>
                            <p class="text-[11px] text-on-surface-variant">
                                Tentukan faktor variasi yang memiliki perbedaan harga jual. Tabel di bawah akan otomatis menyesuaikan kombinasinya.
                            </p>
                        </div>
                    </div>
                    <div id="combinationSummaryBadge" class="px-3 py-1 rounded-full text-xs font-bold bg-primary/10 text-primary border border-primary/20 self-start sm:self-auto">
                        0 Kombinasi
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
                    <!-- 1. UKURAN PRODUK (WAJIB) -->
                    <div class="bg-surface-container-lowest/70 border border-outline-variant/50 rounded-2xl p-4 flex flex-col justify-between space-y-3">
                        <div class="space-y-2.5">
                            <div class="flex items-center justify-between">
                                <label class="text-xs font-bold text-on-surface uppercase tracking-wider flex items-center gap-1.5">
                                    <span class="w-5 h-5 rounded-full bg-primary text-white text-[11px] flex items-center justify-center font-bold">1</span>
                                    Ukuran Produk <span class="text-danger">*</span>
                                </label>
                                <span id="sizeCountBadge" class="text-[11px] font-bold text-primary">0 Ukuran</span>
                            </div>
                            <p class="text-[11px] text-on-surface-variant">Klik untuk mengaktifkan / menonaktifkan ukuran produk:</p>

                            <!-- Size toggle chips container -->
                            <div id="standardSizesContainer" class="flex flex-wrap gap-1.5 pt-0.5">
                                <!-- Populated dynamically by JS -->
                            </div>

                            <!-- Custom Size input -->
                            <div class="pt-2 border-t border-outline-variant/20">
                                <div class="flex items-center gap-1.5">
                                    <input type="text" id="customSizeInput" placeholder="Ukuran lain (misal: 150 X 190)" class="flex-1 px-2.5 py-1.5 bg-white border border-outline-variant rounded-lg text-xs focus:ring-2 focus:ring-primary/20 focus:outline-none" onkeydown="if(event.key==='Enter'){ event.preventDefault(); addCustomSize(); }">
                                    <button type="button" onclick="addCustomSize()" class="px-2.5 py-1.5 bg-surface-container hover:bg-surface-container-high text-on-surface rounded-lg text-xs font-bold shrink-0 border border-outline-variant/60 transition-colors">
                                        + Tambah
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="pt-2">
                            <button type="button" onclick="toggleAllStandardSizes()" class="w-full py-1.5 bg-primary/5 hover:bg-primary/10 text-primary border border-primary/20 rounded-xl text-xs font-bold transition-colors text-center">
                                ⚡ Pilih Semua Standar (90-200)
                            </button>
                        </div>
                    </div>

                    <!-- 2. KELENGKAPAN PAKET (OPSIONAL) -->
                    <div class="bg-surface-container-lowest/70 border border-outline-variant/50 rounded-2xl p-4 flex flex-col justify-between space-y-3">
                        <div class="space-y-2.5">
                            <div class="flex items-center justify-between">
                                <label class="text-xs font-bold text-on-surface uppercase tracking-wider flex items-center gap-1.5">
                                    <span class="w-5 h-5 rounded-full bg-primary text-white text-[11px] flex items-center justify-center font-bold">2</span>
                                    Kelengkapan / Opsi Tambahan
                                </label>
                                <!-- Switch toggle -->
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" id="completenessToggle" class="sr-only peer" onchange="toggleCompletenessMode(this.checked)">
                                    <div class="w-9 h-5 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-primary"></div>
                                </label>
                            </div>

                            <p class="text-[11px] text-on-surface-variant">
                                Aktifkan jika produk memiliki opsi kelengkapan/tipe (misal: Satuan vs Paket Lengkap, atau Feel: Plush vs Firm).
                            </p>

                            <div id="completenessDisabledNotice" class="p-3 bg-surface-container/40 rounded-xl text-[11px] text-on-surface-variant italic text-center">
                                Opsi dinonaktifkan. Seluruh ukuran diasumsikan sebagai <strong>Satuan/Standar</strong> (1 paket produk tunggal).
                            </div>

                            <div id="completenessOptionsContainer" class="space-y-2 hidden">
                                <!-- Dynamic Title for Web -->
                                <div class="mb-2.5 pb-2 border-b border-outline-variant/30">
                                    <label class="block text-[10px] font-bold text-on-surface-variant uppercase mb-1">Judul Atribut di Web:</label>
                                    <div class="relative">
                                        <input type="text" id="completenessTitleInput" value="Kelengkapan" placeholder="Contoh: Kelengkapan, Feel, Tipe..." class="w-full px-2.5 py-1.5 bg-white border border-outline-variant rounded-lg text-xs font-bold text-primary focus:ring-2 focus:ring-primary/20 focus:outline-none" oninput="onCompletenessTitleChanged(this.value)">
                                    </div>
                                    <p class="text-[10px] text-on-surface-variant mt-1">Dinamis disesuaikan di toko online (misal: <em>Kelengkapan</em> atau <em>Feel</em>).</p>
                                </div>

                                <div class="space-y-1.5" id="completenessCheckboxesList">
                                    <!-- Populated dynamically by JS -->
                                </div>

                                <!-- Add custom completeness -->
                                <div class="flex items-center gap-1.5 pt-2 border-t border-outline-variant/20">
                                    <input type="text" id="customCompletenessInput" placeholder="Kelengkapan / opsi lain..." class="flex-1 px-2.5 py-1.5 bg-white border border-outline-variant rounded-lg text-xs focus:ring-2 focus:ring-primary/20 focus:outline-none" onkeydown="if(event.key==='Enter'){ event.preventDefault(); addCustomCompleteness(); }">
                                    <button type="button" onclick="addCustomCompleteness()" class="px-2.5 py-1.5 bg-surface-container hover:bg-surface-container-high text-on-surface rounded-lg text-xs font-bold shrink-0 border border-outline-variant/60 transition-colors">
                                        + Tambah
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="text-[10px] text-on-surface-variant flex items-center gap-1 pt-2 border-t border-outline-variant/10">
                            <span class="material-symbols-outlined text-[14px] text-primary">sell</span>
                            <span>Tiap opsi memiliki harga & SKU tersendiri</span>
                        </div>
                    </div>

                    <!-- 3. KETEBALAN / TINGGI PRODUK (OPSIONAL) -->
                    <div class="bg-surface-container-lowest/70 border border-outline-variant/50 rounded-2xl p-4 flex flex-col justify-between space-y-3">
                        <div class="space-y-2.5">
                            <div class="flex items-center justify-between">
                                <label class="text-xs font-bold text-on-surface uppercase tracking-wider flex items-center gap-1.5">
                                    <span class="w-5 h-5 rounded-full bg-primary text-white text-[11px] flex items-center justify-center font-bold">3</span>
                                    Ketebalan / Tinggi Produk (T)
                                </label>
                                <!-- Switch toggle -->
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" id="thicknessToggle" class="sr-only peer" checked onchange="toggleThicknessSwitch(this.checked)">
                                    <div class="w-9 h-5 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-primary"></div>
                                </label>
                            </div>

                            <p class="text-[11px] text-on-surface-variant">
                                Aktifkan jika produk memiliki spesifikasi atau variasi ketebalan / tinggi (T).
                            </p>

                            <div id="thicknessDisabledNotice" class="p-3 bg-surface-container/40 rounded-xl text-[11px] text-on-surface-variant italic text-center hidden">
                                Opsi dinonaktifkan. Produk tidak memiliki variasi ketebalan/tinggi.
                            </div>

                            <div id="thicknessOptionsContainer" class="space-y-2.5">
                                <div class="flex items-center justify-between">
                                    <span class="text-[11px] font-semibold text-on-surface">Pilihan Mode:</span>
                                    <!-- Mode toggle pills -->
                                    <div class="inline-flex rounded-lg border border-outline-variant/60 p-0.5 bg-surface-container-low text-[10px]">
                                        <button type="button" id="thModeSingleBtn" onclick="setThicknessMode('single')" class="px-2 py-0.5 rounded-md font-bold transition-all bg-white text-primary shadow-2xs">1 Tebal</button>
                                        <button type="button" id="thModeMultiBtn" onclick="setThicknessMode('multi')" class="px-2 py-0.5 rounded-md font-medium text-on-surface-variant hover:text-on-surface transition-all">Multi-Tebal</button>
                                    </div>
                                </div>

                                <!-- Single thickness panel -->
                                <div id="singleThicknessPanel" class="space-y-2">
                                    <p class="text-[11px] text-on-surface-variant">Satu ketebalan / tinggi standar untuk semua ukuran produk:</p>
                                    <div class="relative">
                                        <input type="number" step="0.1" min="0" id="singleThicknessInput" value="25" placeholder="Misal: 25" class="w-full px-3 py-2 bg-white border border-outline-variant rounded-xl text-xs font-semibold focus:ring-2 focus:ring-primary/20 focus:outline-none pr-10" oninput="onSingleThicknessChange(this.value)">
                                        <span class="absolute right-3 top-2 text-[11px] text-on-surface-variant font-medium">cm</span>
                                    </div>
                                    <div class="flex items-center gap-1 flex-wrap pt-0.5">
                                        <span class="text-[10px] text-on-surface-variant">Pilihan tebal/tinggi:</span>
                                        <button type="button" onclick="setSingleThicknessQuick(20)" class="text-[10px] px-2 py-0.5 bg-white border border-outline-variant rounded-md hover:border-primary font-medium">20 cm</button>
                                        <button type="button" onclick="setSingleThicknessQuick(25)" class="text-[10px] px-2 py-0.5 bg-white border border-outline-variant rounded-md hover:border-primary font-medium">25 cm</button>
                                        <button type="button" onclick="setSingleThicknessQuick(30)" class="text-[10px] px-2 py-0.5 bg-white border border-outline-variant rounded-md hover:border-primary font-medium">30 cm</button>
                                        <button type="button" onclick="setSingleThicknessQuick(35)" class="text-[10px] px-2 py-0.5 bg-white border border-outline-variant rounded-md hover:border-primary font-medium">35 cm</button>
                                    </div>
                                </div>

                                <!-- Multi thickness panel -->
                                <div id="multiThicknessPanel" class="space-y-2 hidden">
                                    <p class="text-[11px] text-on-surface-variant">Pilih beberapa ketebalan/tinggi dengan harga berbeda:</p>
                                    <div id="multiThicknessChipsContainer" class="flex flex-wrap gap-1.5 pt-0.5">
                                        <!-- Populated dynamically by JS -->
                                    </div>
                                    <div class="flex items-center gap-1.5 pt-1">
                                        <input type="number" step="0.1" min="0" id="customThicknessInput" placeholder="Tebal lain (cm)..." class="flex-1 px-2.5 py-1.5 bg-white border border-outline-variant rounded-lg text-xs focus:ring-2 focus:ring-primary/20 focus:outline-none" onkeydown="if(event.key==='Enter'){ event.preventDefault(); addCustomThickness(); }">
                                        <button type="button" onclick="addCustomThickness()" class="px-2.5 py-1.5 bg-surface-container hover:bg-surface-container-high text-on-surface rounded-lg text-xs font-bold shrink-0 border border-outline-variant/60 transition-colors">
                                            + Tambah
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="text-[10px] text-on-surface-variant flex items-center gap-1 pt-2 border-t border-outline-variant/10">
                            <span class="material-symbols-outlined text-[14px] text-primary">height</span>
                            <span id="thicknessFooterNote">Tinggi / tebal produk otomatis tersinkron ke kolom T (cm)</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- BAGIAN 3: ATUR SEKALIGUS (SMART BATCH APPLY BAR) -->
            <div class="bg-surface-container-lowest border border-outline-variant/50 rounded-2xl p-4 space-y-3">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-outline-variant/20 pb-2.5">
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-outlined text-primary text-[20px]">bolt</span>
                        <div>
                            <span class="text-xs font-bold text-on-surface">Ubah Sekaligus (Terapkan Cepat ke Baris)</span>
                            <p class="text-[11px] text-on-surface-variant">Isi harga & spesifikasi serentak tanpa perlu mengetik manual satu per satu.</p>
                        </div>
                    </div>
                    <!-- Target Selector -->
                    <div class="flex items-center gap-2">
                        <span class="text-xs text-on-surface-variant font-medium shrink-0">Terapkan ke:</span>
                        <select id="batchTargetSelector" class="px-3 py-1.5 border border-outline-variant rounded-xl text-xs font-bold text-primary bg-white focus:ring-2 focus:ring-primary/20 focus:outline-none cursor-pointer">
                            <option value="all">Semua Baris Kombinasi</option>
                            <!-- Populated dynamically with kelengkapan & tebal filters -->
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-8 gap-2.5 items-end">
                    <div>
                        <label class="block text-[10px] font-bold text-on-surface-variant uppercase mb-1">Harga Modal (Rp)</label>
                        <input type="number" id="batchBasePrice" placeholder="0" class="w-full px-2.5 py-1.5 border border-outline-variant rounded-lg text-xs focus:ring-2 focus:ring-primary/20 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-on-surface-variant uppercase mb-1">Harga Jual (Rp) *</label>
                        <input type="number" id="batchSellPrice" placeholder="0" class="w-full px-2.5 py-1.5 border border-outline-variant rounded-lg text-xs font-bold text-primary focus:ring-2 focus:ring-primary/20 focus:outline-none">
                    </div>
                    <div id="batchShippingCostWrapper" class="{{ old('shipping_scheme', $product->shipping_scheme ?? 'dimension') === 'fixed' ? '' : 'hidden' }}">
                        <label class="block text-[10px] font-bold text-amber-900 uppercase mb-1">Ongkir Tetap (Rp)</label>
                        <input type="number" step="1000" min="0" id="batchShippingCost" placeholder="0" class="w-full px-2.5 py-1.5 border border-amber-300 rounded-lg text-xs font-bold text-amber-950 bg-amber-50/50 focus:ring-2 focus:ring-amber-400/20 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-on-surface-variant uppercase mb-1">Kode SKU</label>
                        <input type="text" id="batchSkuPrefix" value="{{ $productCode }}" readonly disabled class="w-full px-2.5 py-1.5 border border-outline-variant/60 rounded-lg text-xs font-mono font-bold bg-surface-container/60 text-on-surface-variant cursor-not-allowed" title="Prefix SKU otomatis dari kode produk">
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-on-surface-variant uppercase mb-1">P (cm)</label>
                        <input type="number" step="0.1" min="0" id="batchLength" placeholder="200" class="w-full px-2 py-1.5 border border-outline-variant rounded-lg text-xs focus:ring-2 focus:ring-primary/20 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-on-surface-variant uppercase mb-1">L (cm)</label>
                        <input type="number" step="0.1" min="0" id="batchWidth" placeholder="0" class="w-full px-2 py-1.5 border border-outline-variant rounded-lg text-xs focus:ring-2 focus:ring-primary/20 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-on-surface-variant uppercase mb-1">T (cm)</label>
                        <input type="number" step="0.1" min="0" id="batchHeight" placeholder="25" class="w-full px-2 py-1.5 border border-outline-variant rounded-lg text-xs focus:ring-2 focus:ring-primary/20 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-on-surface-variant uppercase mb-1">Berat (kg)</label>
                        <div class="flex items-center gap-1.5">
                            <input type="number" step="0.01" min="0" id="batchWeight" placeholder="0" class="w-full px-2 py-1.5 border border-outline-variant rounded-lg text-xs focus:ring-2 focus:ring-primary/20 focus:outline-none">
                            <button type="button" onclick="applyBatchSettings()" class="py-1.5 px-3 bg-primary text-white hover:opacity-90 rounded-lg text-xs font-bold transition-all shadow-2xs flex items-center justify-center gap-1 active:scale-95 shrink-0" title="Terapkan ke baris yang dipilih">
                                <span class="material-symbols-outlined text-[16px]">done_all</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- BAGIAN 4: DAFTAR VARIASI TERKELOMPOK PER UKURAN PRODUK -->
            <div class="space-y-4">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-outline-variant/30 pb-3">
                    <div class="flex items-center gap-2.5">
                        <span class="material-symbols-outlined text-primary text-[22px]">view_agenda</span>
                        <div>
                            <div class="flex items-center gap-2">
                                <h3 class="text-xs font-bold text-on-surface uppercase tracking-wider">
                                    Daftar Variasi per Ukuran Produk
                                </h3>
                                <span id="variantRowCountBadge" class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-primary/10 text-primary">0 Ukuran</span>
                            </div>
                            <p class="text-[11px] text-on-surface-variant mt-0.5">Tampilan terkelompok rapi per ukuran produk. Setiap ukuran memuat pilihan ketebalan/tinggi, kelengkapan, dan harga tanpa duplikasi.</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2 self-start sm:self-auto">
                        <button type="button" onclick="toggleAllUkuranCards(true)" class="px-2.5 py-1 rounded-lg text-xs font-semibold bg-surface-container hover:bg-surface-container-high text-on-surface-variant transition-colors flex items-center gap-1">
                            <span class="material-symbols-outlined text-[15px]">unfold_more</span> Buka Semua
                        </button>
                        <button type="button" onclick="toggleAllUkuranCards(false)" class="px-2.5 py-1 rounded-lg text-xs font-semibold bg-surface-container hover:bg-surface-container-high text-on-surface-variant transition-colors flex items-center gap-1">
                            <span class="material-symbols-outlined text-[15px]">unfold_less</span> Tutup Semua
                        </button>
                    </div>
                </div>

                <!-- CONTAINER CARD PER UKURAN -->
                <div id="variantsGroupedContainer" class="space-y-4">
                    <!-- Dynamic grouped cards injected by JS -->
                </div>
                <p id="noVariantsWarning" class="text-xs text-danger font-medium hidden">Mohon tentukan minimal 1 kombinasi variasi produk.</p>
            </div>
        </div>

        <!-- 3. PENGIRIMAN & STATUS KATALOG -->
        <div class="w-full bg-white rounded-2xl shadow-sm border border-outline-variant/30 p-6 space-y-5">
            <div class="border-b border-outline-variant/20 pb-3">
                <h2 class="text-base font-bold text-on-surface flex items-center gap-2">
                    <span class="material-symbols-outlined text-primary text-[20px]">visibility</span>
                    Status Katalog
                </h2>
                <p class="text-xs text-on-surface-variant mt-0.5">Atur visibilitas dan status produk dalam katalog.</p>
            </div>

            <!-- Status & Visibilitas -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 pt-2">
                <!-- Status Produk -->
                <div class="space-y-1.5">
                    <label class="block text-xs font-bold text-on-surface-variant uppercase tracking-wider">
                        Status Produk
                    </label>
                    <select name="status" class="w-full px-3 py-2 border border-outline-variant rounded-xl text-xs bg-white focus:ring-2 focus:ring-primary/20 focus:outline-none">
                        <option value="1" {{ old('status', $product->status ?? 1) == 1 ? 'selected' : '' }}>Aktif (Tampil di Katalog)</option>
                        <option value="0" {{ old('status', $product->status ?? 1) == 0 ? 'selected' : '' }}>Nonaktif (Disembunyikan)</option>
                    </select>
                </div>

                <!-- Tampilkan di Web -->
                <div class="space-y-1.5">
                    <label class="block text-xs font-bold text-on-surface-variant uppercase tracking-wider">
                        Tampilkan di Web
                    </label>
                    <select name="show_on_web" class="w-full px-3 py-2 border border-outline-variant rounded-xl text-xs bg-white focus:ring-2 focus:ring-primary/20 focus:outline-none">
                        <option value="1" {{ old('show_on_web', (isset($product) && $product->exists ? ($product->show_on_web ? '1' : '0') : '1')) == '1' ? 'selected' : '' }}>Ya (Tampil di Web)</option>
                        <option value="0" {{ old('show_on_web', (isset($product) && $product->exists ? ($product->show_on_web ? '1' : '0') : '1')) == '0' ? 'selected' : '' }}>Tidak (Sembunyikan dari Web)</option>
                    </select>
                </div>

                <!-- Produk Baru -->
                <div class="space-y-1.5">
                    <label class="block text-xs font-bold text-on-surface-variant uppercase tracking-wider">
                        Badge Produk Baru (New)
                    </label>
                    <select name="is_new" class="w-full px-3 py-2 border border-outline-variant rounded-xl text-xs bg-white focus:ring-2 focus:ring-primary/20 focus:outline-none">
                        <option value="0" {{ old('is_new', $product->is_new ?? 0) == 0 ? 'selected' : '' }}>Tidak</option>
                        <option value="1" {{ old('is_new', $product->is_new ?? 0) == 1 ? 'selected' : '' }}>Ya (Label NEW)</option>
                    </select>
                </div>

                <!-- Best Seller -->
                <div class="space-y-1.5">
                    <label class="block text-xs font-bold text-on-surface-variant uppercase tracking-wider">
                        Badge Best Seller
                    </label>
                    <select name="best_seller" class="w-full px-3 py-2 border border-outline-variant rounded-xl text-xs bg-white focus:ring-2 focus:ring-primary/20 focus:outline-none">
                        <option value="0" {{ old('best_seller', $product->best_seller ?? 0) == 0 ? 'selected' : '' }}>Tidak</option>
                        <option value="1" {{ old('best_seller', $product->best_seller ?? 0) == 1 ? 'selected' : '' }}>Ya (Best Seller)</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- BOTTOM ACTIONS -->
        <div class="flex items-center justify-between pt-2">
            <a href="{{ route('products.index') }}" class="inline-flex items-center gap-1.5 px-4 py-2 border border-outline-variant text-on-surface-variant hover:bg-surface-container rounded-xl text-xs font-semibold transition-colors">
                <span class="material-symbols-outlined text-[16px]">arrow_back</span>
                <span>Batal</span>
            </a>
            <button type="button" onclick="submitProductForm()" class="btn-save inline-flex items-center gap-2 px-6 py-2.5 bg-primary text-white hover:opacity-90 rounded-xl text-xs font-bold transition-all shadow-sm active:scale-95">
                <span class="material-symbols-outlined text-[18px]">save</span>
                <span>{{ $isEdit ? 'Simpan Perubahan Produk' : 'Simpan Produk' }}</span>
            </button>
        </div>
    </form>
</div>

<!-- HIDDEN GLOBAL FILE INPUT FOR VARIANT IMAGES -->
<input type="file" id="globalVariantImagePicker" accept="image/*" class="hidden" onchange="handleVariantImageFilePicked(this)">

@endsection

@push('scripts')
<script src="https://cdn.quilljs.com/1.3.6/quill.js"></script>
<script>
// ==================== THUMBNAIL MANAGEMENT ====================
function previewThumbnail(input) {
    const container = document.getElementById('thumbnailPreviewContainer');
    const placeholder = document.getElementById('thumbnailEmptyPlaceholder');
    const preview = document.getElementById('thumbnailPreview');
    
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.src = e.target.result;
            container.classList.remove('hidden');
            placeholder.classList.add('hidden');
        };
        reader.readAsDataURL(input.files[0]);
    }
}

function clearThumbnail() {
    const input = document.getElementById('thumbnailInput');
    const container = document.getElementById('thumbnailPreviewContainer');
    const placeholder = document.getElementById('thumbnailEmptyPlaceholder');
    const preview = document.getElementById('thumbnailPreview');
    
    input.value = '';
    preview.src = '';
    container.classList.add('hidden');
    placeholder.classList.remove('hidden');
}

function setWarranty(val) {
    document.getElementById('warrantyInput').value = val;
}

// ==================== GLOBAL CONFIG & VARIATION STATE ====================
window.productCode = @json($productCode);

let variantRows = [];
let productColorsList = [];
let currentUploadContext = null;

// Standard Preset Sizes
const STANDARD_SIZES = ['080 X 200', '090 X 200', '100 X 200', '120 X 200', '140 X 200', '160 X 200', '180 X 200', '200 X 200'];
let allAvailableSizes = [...STANDARD_SIZES];
let selectedSizes = ['160 X 200', '180 X 200', '200 X 200'];

// Completeness Configuration
let hasCompleteness = false;
let completenessAttributeTitle = 'Kelengkapan';
const PRESET_COMPLETENESS = [
    { name: 'Kasur Saja', short: 'Kasur Saja', code: 'KS' },
    { name: 'Set Kasur + Divan', short: 'Set Kasur + Divan', code: 'SD' }
];
let completenessList = [...PRESET_COMPLETENESS];
let activeCompleteness = ['Kasur Saja', 'Set Kasur + Divan'];

// Thickness Configuration
let hasThickness = true;
let thicknessMode = 'single'; // 'single' or 'multi'
let singleThickness = 25;
const PRESET_THICKNESSES = [20, 25, 30, 35, 40];
let availableThicknesses = [...PRESET_THICKNESSES];
let activeThicknesses = [25];

// In-memory cache to preserve inputs across regenerations
let rowCache = {};

// ==================== HELPER FUNCTIONS ====================
function escapeHtml(string) {
    const entityMap = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#39;'
    };
    return String(string ?? '').replace(/[&<>"']/g, s => entityMap[s]);
}

function slugify(text) {
    return text.toString().toLowerCase()
        .replace(/[^a-z0-9]+/g, '-')
        .replace(/(^-|-$)+/g, '');
}

// Extract width and length from size string (e.g., "080 X 200", "160x200", "140 X 190")
function parseSizeDimensions(str) {
    if (!str) return { width: null, length: null };
    const numbers = (str.match(/\d+/g) || []).map(Number);
    if (numbers.length >= 2) {
        let n1 = numbers[0];
        let n2 = numbers[1];
        // In Indonesian mattresses, standard lengths are 200, 190, 180. Width is usually the other dimension.
        if (n1 > n2 && (n1 === 200 || n1 === 190 || n1 === 180)) {
            return { width: n2, length: n1 };
        } else {
            return { width: n1, length: n2 };
        }
    } else if (numbers.length === 1) {
        return { width: numbers[0], length: 200 };
    }
    return { width: null, length: null };
}

// Format standard size display (e.g., "80x200" -> "080 X 200")
function formatStandardSize(str) {
    if (!str) return '';
    const dims = parseSizeDimensions(str);
    if (dims.width && dims.length) {
        const wStr = dims.width < 100 ? '0' + dims.width : String(dims.width);
        return `${wStr} X ${dims.length}`;
    }
    return str.trim().toUpperCase();
}

// Auto-generate official ERP SKU from Product Code + Ukuran - Kelengkapan / Ketebalan
function generateAutoSku(sizeStr, compCode, tebalNum) {
    const dims = parseSizeDimensions(sizeStr);
    const prodCode = (window.productCode || document.getElementById('productCodeHidden')?.value || '').trim() || 'PRD';

    let suffix = '';
    if (compCode) {
        suffix += `-${compCode.toUpperCase()}`;
    }
    if (tebalNum) {
        const cleanT = String(tebalNum).replace(/\D+/g, '');
        if (cleanT) suffix += `-T${cleanT}`;
    }

    let sizePart = '';
    if (dims.width && dims.length) {
        sizePart = `${dims.width}X${dims.length}`;
    } else if (sizeStr) {
        sizePart = slugify(sizeStr).toUpperCase().replace(/-+/g, '');
    } else {
        sizePart = 'STD';
    }

    return `${prodCode}-${sizePart}${suffix}`.replace(/--+/g, '-');
}

function getCombinationKey(sizeStr, compName, thVal) {
    const s = (sizeStr || '').trim();
    const c = (compName || '').trim();
    const t = (hasThickness && thicknessMode === 'multi' && thVal) ? String(thVal).trim() : '';
    return `${s}__${c}__${t}`;
}

// ==================== PRODUCT COLORS MANAGEMENT (product_colors) ====================
function renderColorChips() {
    const container = document.getElementById('productColorsContainer');
    const badge = document.getElementById('colorCountBadge');
    if (!container) return;

    if (badge) badge.textContent = `${productColorsList.length} Warna`;

    if (productColorsList.length === 0) {
        container.innerHTML = `
            <span class="text-xs text-on-surface-variant italic py-1">
                Belum ada pilihan warna. Tambahkan dari preset cepat di bawah atau gunakan picker jika produk memiliki variasi warna kain/matras.
            </span>
        `;
        return;
    }

    container.innerHTML = '';
    productColorsList.forEach((c, idx) => {
        const chip = document.createElement('div');
        chip.className = 'inline-flex items-center gap-2 px-3 py-1.5 rounded-xl border border-outline-variant/80 bg-surface-container-lowest text-xs font-semibold text-on-surface shadow-2xs group hover:border-emerald-600 transition-all';
        chip.innerHTML = `
            <span class="w-3.5 h-3.5 rounded-full border border-black/25 shrink-0 shadow-2xs" style="background-color: ${escapeHtml(c.color_code)}"></span>
            <span class="font-bold">${escapeHtml(c.color_name)}</span>
            <span class="text-[10px] text-on-surface-variant font-mono font-normal uppercase">${escapeHtml(c.color_code)}</span>
            <button type="button" onclick="removeColor(${idx})" class="ml-1 text-on-surface-variant hover:text-danger flex items-center justify-center p-0.5 rounded-md hover:bg-danger/10 transition-colors" title="Hapus warna ini">
                <span class="material-symbols-outlined text-[14px]">close</span>
            </button>
        `;
        container.appendChild(chip);
    });
}

function addColorOption(name, hex) {
    const trimmed = (name || '').trim();
    if (!trimmed) return;

    const exists = productColorsList.some(c => c.color_name.toLowerCase() === trimmed.toLowerCase());
    if (exists) {
        showToast('info', `Warna "${trimmed}" sudah ditambahkan.`);
        return;
    }

    let code = (hex || '#1e293b').trim();
    if (!code.startsWith('#')) code = '#' + code;

    productColorsList.push({
        id: null,
        color_name: trimmed,
        color_code: code
    });

    renderColorChips();
    showToast('success', `Warna "${trimmed}" berhasil ditambahkan!`);
}

function addCustomColor() {
    const nameInput = document.getElementById('customColorName');
    const hexInput = document.getElementById('customColorHex');
    const name = nameInput.value.trim();
    const hex = hexInput.value.trim() || '#1e293b';

    if (!name) {
        alert('Mohon isi nama warna.');
        nameInput.focus();
        return;
    }

    addColorOption(name, hex);
    nameInput.value = '';
    nameInput.focus();
}

function removeColor(idx) {
    productColorsList.splice(idx, 1);
    renderColorChips();
}

// ==================== 1. SIZE CONTROLS ====================
function renderStandardSizes() {
    const container = document.getElementById('standardSizesContainer');
    const badge = document.getElementById('sizeCountBadge');
    if (!container) return;

    if (badge) badge.textContent = `${selectedSizes.length} Ukuran Aktif`;
    container.innerHTML = '';

    allAvailableSizes.forEach(sizeStr => {
        const isSelected = selectedSizes.includes(sizeStr);
        const btn = document.createElement('button');
        btn.type = 'button';
        btn.onclick = () => toggleSize(sizeStr);

        if (isSelected) {
            btn.className = 'px-2.5 py-1 rounded-lg text-xs font-bold border border-primary bg-primary/10 text-primary flex items-center gap-1 shadow-2xs transition-all';
            btn.innerHTML = `<span class="material-symbols-outlined text-[14px]">check</span><span>${escapeHtml(sizeStr)}</span>`;
        } else {
            btn.className = 'px-2.5 py-1 rounded-lg text-xs font-medium border border-outline-variant/70 bg-white text-on-surface hover:border-primary/60 hover:text-primary transition-all';
            btn.innerHTML = `<span>+ ${escapeHtml(sizeStr)}</span>`;
        }
        container.appendChild(btn);
    });
}

function toggleSize(sizeStr) {
    saveCurrentTableInputs();
    const idx = selectedSizes.indexOf(sizeStr);
    if (idx !== -1) {
        selectedSizes.splice(idx, 1);
    } else {
        selectedSizes.push(sizeStr);
        // Keep sizes sorted naturally
        selectedSizes.sort((a, b) => {
            const dA = parseSizeDimensions(a);
            const dB = parseSizeDimensions(b);
            return (dA.width || 0) - (dB.width || 0);
        });
    }
    renderStandardSizes();
    rebuildCombinations();
}

function toggleAllStandardSizes() {
    saveCurrentTableInputs();
    const allSelected = STANDARD_SIZES.every(s => selectedSizes.includes(s));
    if (allSelected) {
        selectedSizes = [];
    } else {
        selectedSizes = Array.from(new Set([...selectedSizes, ...STANDARD_SIZES]));
        selectedSizes.sort((a, b) => {
            const dA = parseSizeDimensions(a);
            const dB = parseSizeDimensions(b);
            return (dA.width || 0) - (dB.width || 0);
        });
    }
    renderStandardSizes();
    rebuildCombinations();
}

function addCustomSize() {
    const input = document.getElementById('customSizeInput');
    const val = input.value.trim();
    if (!val) return;

    const formatted = formatStandardSize(val);
    if (!allAvailableSizes.includes(formatted)) {
        allAvailableSizes.push(formatted);
    }
    if (!selectedSizes.includes(formatted)) {
        selectedSizes.push(formatted);
    }

    input.value = '';
    renderStandardSizes();
    rebuildCombinations();
    showToast('success', `Ukuran "${formatted}" berhasil ditambahkan!`);
}

// ==================== 2. COMPLETENESS CONTROLS ====================
function toggleCompletenessMode(isChecked) {
    saveCurrentTableInputs();
    hasCompleteness = !!isChecked;

    const notice = document.getElementById('completenessDisabledNotice');
    const container = document.getElementById('completenessOptionsContainer');
    const toggle = document.getElementById('completenessToggle');
    if (toggle) toggle.checked = hasCompleteness;

    if (hasCompleteness) {
        notice?.classList.add('hidden');
        container?.classList.remove('hidden');
        if (activeCompleteness.length === 0 && completenessList.length > 0) {
            activeCompleteness = [completenessList[0].name];
        }
    } else {
        notice?.classList.remove('hidden');
        container?.classList.add('hidden');
    }

    renderCompletenessCheckboxes();
    rebuildCombinations();
}

function renderCompletenessCheckboxes() {
    const container = document.getElementById('completenessCheckboxesList');
    if (!container) return;

    container.innerHTML = '';
    completenessList.forEach((c) => {
        const isChecked = activeCompleteness.includes(c.name);
        const item = document.createElement('div');
        item.className = 'flex items-center justify-between p-2 rounded-xl border ' + 
            (isChecked ? 'border-primary/40 bg-primary/5' : 'border-outline-variant/60 bg-white') + ' transition-colors';

        item.innerHTML = `
            <label class="flex items-center gap-2 cursor-pointer flex-1 select-none">
                <input type="checkbox" class="rounded border-outline-variant text-primary focus:ring-primary h-4 w-4 cursor-pointer" ${isChecked ? 'checked' : ''} onchange="toggleCompletenessOption('${escapeHtml(c.name)}')">
                <span class="text-xs font-semibold text-on-surface">${escapeHtml(c.name)}</span>
            </label>
            <span class="px-1.5 py-0.5 rounded bg-surface-container text-on-surface-variant font-mono font-bold text-[10px] uppercase ml-2">${escapeHtml(c.code || 'VAR')}</span>
        `;
        container.appendChild(item);
    });
}

function toggleCompletenessOption(compName) {
    saveCurrentTableInputs();
    const idx = activeCompleteness.indexOf(compName);
    if (idx !== -1) {
        if (activeCompleteness.length === 1) {
            alert('Minimal harus ada 1 kelengkapan paket yang aktif jika opsi ini dinyalakan.');
            renderCompletenessCheckboxes();
            return;
        }
        activeCompleteness.splice(idx, 1);
    } else {
        activeCompleteness.push(compName);
    }
    renderCompletenessCheckboxes();
    rebuildCombinations();
}

function addCustomCompleteness() {
    const input = document.getElementById('customCompletenessInput');
    const val = input.value.trim();
    if (!val) return;

    const exists = completenessList.some(c => c.name.toLowerCase() === val.toLowerCase());
    if (exists) {
        showToast('info', `Kelengkapan "${val}" sudah ada.`);
        return;
    }

    const initials = val.split(' ').map(w => w[0]).join('').toUpperCase().substring(0, 3) || 'CST';
    const newComp = { name: val, short: val, code: initials };
    completenessList.push(newComp);
    activeCompleteness.push(val);

    input.value = '';
    renderCompletenessCheckboxes();
    rebuildCombinations();
    showToast('success', `Kelengkapan "${val}" berhasil ditambahkan!`);
}

// ==================== 3. THICKNESS / HEIGHT CONTROLS ====================
function setThicknessMode(mode) {
    saveCurrentTableInputs();
    thicknessMode = mode;

    const sBtn = document.getElementById('thModeSingleBtn');
    const mBtn = document.getElementById('thModeMultiBtn');
    const sPanel = document.getElementById('singleThicknessPanel');
    const mPanel = document.getElementById('multiThicknessPanel');
    const note = document.getElementById('thicknessFooterNote');

    if (mode === 'multi') {
        sBtn?.classList.remove('bg-white', 'text-primary', 'shadow-2xs');
        sBtn?.classList.add('text-on-surface-variant');
        mBtn?.classList.add('bg-white', 'text-primary', 'shadow-2xs');
        mBtn?.classList.remove('text-on-surface-variant');
        sPanel?.classList.add('hidden');
        mPanel?.classList.remove('hidden');
        if (note) note.textContent = 'Setiap ketebalan menghasilkan variasi harga & SKU tersendiri';
        if (activeThicknesses.length === 0) activeThicknesses = [25];
        renderMultiThicknessChips();
    } else {
        mBtn?.classList.remove('bg-white', 'text-primary', 'shadow-2xs');
        mBtn?.classList.add('text-on-surface-variant');
        sBtn?.classList.add('bg-white', 'text-primary', 'shadow-2xs');
        sBtn?.classList.remove('text-on-surface-variant');
        mPanel?.classList.add('hidden');
        sPanel?.classList.remove('hidden');
        if (note) note.textContent = 'Tinggi / tebal produk otomatis tersinkron ke kolom T (cm)';
    }

    rebuildCombinations();
}

function onSingleThicknessChange(val) {
    singleThickness = parseFloat(val) || 25;
    const pHeight = document.getElementById('productHeight');
    if (pHeight) pHeight.value = singleThickness;

    if (thicknessMode === 'single') {
        variantRows.forEach(v => {
            v.height = singleThickness;
            v.tebal = singleThickness;
        });
        const rows = document.querySelectorAll('tr.variant-row');
        rows.forEach(r => {
            const hInput = r.querySelector('.v-height');
            if (hInput) hInput.value = singleThickness;
        });
    }
}

function setSingleThicknessQuick(val) {
    const input = document.getElementById('singleThicknessInput');
    if (input) input.value = val;
    onSingleThicknessChange(val);
}

function renderMultiThicknessChips() {
    const container = document.getElementById('multiThicknessChipsContainer');
    if (!container) return;

    container.innerHTML = '';
    availableThicknesses.forEach(th => {
        const isSelected = activeThicknesses.includes(th);
        const btn = document.createElement('button');
        btn.type = 'button';
        btn.onclick = () => toggleMultiThickness(th);

        if (isSelected) {
            btn.className = 'px-2.5 py-1 rounded-lg text-xs font-bold border border-emerald-600 bg-emerald-50 text-emerald-700 flex items-center gap-1 shadow-2xs transition-all';
            btn.innerHTML = `<span class="material-symbols-outlined text-[13px]">check</span><span>${th} cm</span>`;
        } else {
            btn.className = 'px-2.5 py-1 rounded-lg text-xs font-medium border border-outline-variant/70 bg-white text-on-surface hover:border-emerald-600/60 hover:text-emerald-700 transition-all';
            btn.innerHTML = `<span>+ ${th} cm</span>`;
        }
        container.appendChild(btn);
    });
}

function toggleMultiThickness(th) {
    saveCurrentTableInputs();
    const idx = activeThicknesses.indexOf(th);
    if (idx !== -1) {
        if (activeThicknesses.length === 1) {
            alert('Minimal harus ada 1 ketebalan yang aktif.');
            return;
        }
        activeThicknesses.splice(idx, 1);
    } else {
        activeThicknesses.push(th);
        activeThicknesses.sort((a, b) => a - b);
    }
    renderMultiThicknessChips();
    rebuildCombinations();
}

function addCustomThickness() {
    const input = document.getElementById('customThicknessInput');
    const val = parseFloat(input.value);
    if (!val || val <= 0) return;

    if (!availableThicknesses.includes(val)) {
        availableThicknesses.push(val);
        availableThicknesses.sort((a, b) => a - b);
    }
    if (!activeThicknesses.includes(val)) {
        activeThicknesses.push(val);
        activeThicknesses.sort((a, b) => a - b);
    }

    input.value = '';
    renderMultiThicknessChips();
    rebuildCombinations();
    showToast('success', `Tebal "${val} cm" berhasil ditambahkan!`);
}

// ==================== 3B. TOGGLES & DYNAMIC ATTRIBUTE LABELS ====================
function toggleThicknessSwitch(isChecked) {
    saveCurrentTableInputs();
    hasThickness = !!isChecked;

    const notice = document.getElementById('thicknessDisabledNotice');
    const container = document.getElementById('thicknessOptionsContainer');
    const toggle = document.getElementById('thicknessToggle');
    if (toggle) toggle.checked = hasThickness;

    if (hasThickness) {
        notice?.classList.add('hidden');
        container?.classList.remove('hidden');
        if (thicknessMode === 'multi' && activeThicknesses.length === 0 && availableThicknesses.length > 0) {
            activeThicknesses = [availableThicknesses[0]];
        }
    } else {
        notice?.classList.remove('hidden');
        container?.classList.add('hidden');
    }

    rebuildCombinations();
}

function onCompletenessTitleChanged(val) {
    completenessAttributeTitle = val.trim() || 'Kelengkapan';
    renderVariantsTable();
}

// ==================== 4. COMBINATION ENGINE & BATCH APPLY ====================
function saveCurrentTableInputs() {
    const rows = document.querySelectorAll('tr.variant-row');
    rows.forEach(row => {
        const key = row.dataset.key;
        if (!key) return;

        const vName = row.querySelector('.v-variant-name')?.value?.trim() || '';
        const vSku = row.querySelector('.v-sku')?.value?.trim() || '';
        const bPrice = row.querySelector('.v-base-price')?.value ?? '';
        const sPrice = row.querySelector('.v-sell-price')?.value ?? '';
        const sCost = row.querySelector('.v-shipping-cost')?.value ?? '';
        const len = row.querySelector('.v-length')?.value ?? '';
        const wid = row.querySelector('.v-width')?.value ?? '';
        const hei = row.querySelector('.v-height')?.value ?? '';
        const wei = row.querySelector('.v-weight')?.value ?? '';
        const stat = row.querySelector('.v-status')?.value ?? '1';
        const excl = row.dataset.excluded === '1';

        const parsedBPrice = (bPrice !== '' && !isNaN(parseFloat(bPrice))) ? parseFloat(bPrice) : 0;
        const parsedSPrice = (sPrice !== '' && !isNaN(parseFloat(sPrice))) ? parseFloat(sPrice) : 0;
        const parsedSCost = (sCost !== '' && !isNaN(parseFloat(sCost))) ? parseFloat(sCost) : 0;
        const parsedLen = (len !== '' && !isNaN(parseFloat(len))) ? parseFloat(len) : null;
        const parsedWid = (wid !== '' && !isNaN(parseFloat(wid))) ? parseFloat(wid) : null;
        const parsedHei = (hei !== '' && !isNaN(parseFloat(hei))) ? parseFloat(hei) : null;
        const parsedWei = (wei !== '' && !isNaN(parseFloat(wei))) ? parseFloat(wei) : null;

        rowCache[key] = {
            variant_name: vName,
            sku: vSku,
            base_price: parsedBPrice,
            sell_price: parsedSPrice,
            shipping_cost: parsedSCost,
            length: parsedLen,
            width: parsedWid,
            height: parsedHei,
            weight: parsedWei,
            status: stat,
            excluded: excl,
            has_db_sku: row.dataset.hasDbSku === '1',
            id: row.dataset.id || null
        };

        // CRITICAL FIX: Directly update matching variantRows item so individual edits are saved
        const gIdx = parseInt(row.dataset.index);
        let target = (!isNaN(gIdx) && variantRows[gIdx]) ? variantRows[gIdx] : variantRows.find(r => r.key === key);
        if (target) {
            target.variant_name = vName;
            target.sku = vSku;
            target.base_price = parsedBPrice;
            target.sell_price = parsedSPrice;
            target.shipping_cost = parsedSCost;
            target.length = parsedLen;
            target.width = parsedWid;
            target.height = parsedHei;
            target.weight = parsedWei;
            target.status = stat;
            target.excluded = excl;
        }
    });
}

function rebuildCombinations() {
    saveCurrentTableInputs();

    const sizes = selectedSizes.length > 0 ? selectedSizes : [];
    const comps = (hasCompleteness && activeCompleteness.length > 0) ? activeCompleteness : [null];
    const tebals = (hasThickness && thicknessMode === 'multi' && activeThicknesses.length > 0) 
        ? activeThicknesses 
        : (hasThickness ? [singleThickness || 25] : [null]);

    const totalCombinations = sizes.length * comps.length * (hasThickness && thicknessMode === 'multi' ? tebals.length : 1);

    // Update Badges
    const badge = document.getElementById('combinationSummaryBadge');
    if (badge) {
        let label = `${sizes.length} Ukuran`;
        if (hasCompleteness) label += ` × ${comps.length} ${completenessAttributeTitle || 'Kelengkapan'}`;
        if (hasThickness && thicknessMode === 'multi') label += ` × ${tebals.length} Tebal`;
        badge.textContent = `${label} = ${totalCombinations} Baris`;
    }

    const newRows = [];

    sizes.forEach(sizeStr => {
        const dims = parseSizeDimensions(sizeStr);

        comps.forEach(compName => {
            const compObj = compName ? completenessList.find(c => c.name === compName) : null;
            const compShort = compObj ? compObj.short : null;
            const compCode = compObj ? compObj.code : null;

            tebals.forEach(thVal => {
                const key = getCombinationKey(sizeStr, compName, thVal);
                const altKey1 = `${sizeStr}__${compName || ''}__${thVal || ''}`;
                const altKey2 = `${sizeStr}__${compName || ''}__`;
                const altKey3 = `${sizeStr}____`;

                const cached = rowCache[key] 
                    || rowCache[altKey1] 
                    || rowCache[altKey2] 
                    || rowCache[altKey3] 
                    || variantRows.find(r => r.key === key || (r.size === sizeStr && (r.kelengkapan || '') === (compShort || compName || '')))
                    || {};

                // Default Variant Name
                let defaultName = sizeStr;
                if (compShort && hasThickness && thicknessMode === 'multi' && thVal) {
                    defaultName = `${sizeStr} - ${compShort} - T.${thVal}cm`;
                } else if (compShort) {
                    defaultName = `${sizeStr} - ${compShort}`;
                } else if (hasThickness && thicknessMode === 'multi' && thVal) {
                    defaultName = `${sizeStr} - T.${thVal}cm`;
                }

                // Default SKU
                const defaultSku = generateAutoSku(sizeStr, compCode, (hasThickness && thicknessMode === 'multi') ? thVal : null);

                newRows.push({
                    key: key,
                    id: cached.id || null,
                    size: sizeStr,
                    kelengkapan: compShort || compName || null,
                    tebal: hasThickness ? (thicknessMode === 'multi' ? thVal : (singleThickness || 25)) : null,
                    variant_name: cached.variant_name || defaultName,
                    sku: cached.sku || defaultSku,
                    has_db_sku: cached.has_db_sku || false,
                    base_price: (cached.base_price !== undefined && cached.base_price !== '') ? cached.base_price : (document.getElementById('batchBasePrice')?.value || 0),
                    sell_price: (cached.sell_price !== undefined && cached.sell_price !== '') ? cached.sell_price : (document.getElementById('batchSellPrice')?.value || 0),
                    shipping_cost: cached.shipping_cost !== undefined ? cached.shipping_cost : (document.getElementById('batchShippingCost')?.value || document.getElementById('productShippingCost')?.value || 0),
                    length: cached.length !== undefined && cached.length !== '' ? cached.length : (dims.length || 200),
                    width: cached.width !== undefined && cached.width !== '' ? cached.width : (dims.width || ''),
                    height: cached.height !== undefined && cached.height !== '' ? cached.height : (hasThickness ? (thicknessMode === 'multi' ? thVal : (singleThickness || 25)) : (document.getElementById('productHeight')?.value || 25)),
                    weight: cached.weight !== undefined && cached.weight !== '' ? cached.weight : (document.getElementById('productWeight')?.value || ''),
                    status: cached.status !== undefined ? cached.status : 1,
                    image: cached.image || null,
                    image_url: cached.image_url || null,
                    image_file: cached.image_file || null,
                    excluded: cached.excluded || false
                });
            });
        });
    });

    variantRows = newRows;
    updateBatchTargetSelector();
    renderVariantsTable();
}

function updateBatchTargetSelector() {
    const selector = document.getElementById('batchTargetSelector');
    if (!selector) return;

    const currentVal = selector.value;
    selector.innerHTML = `<option value="all">Semua Baris Kombinasi (${variantRows.length} Baris)</option>`;

    if (hasCompleteness && activeCompleteness.length > 0) {
        const groupOpt = document.createElement('optgroup');
        groupOpt.label = `Filter ${completenessAttributeTitle || 'Kelengkapan Paket'}`;
        activeCompleteness.forEach(compName => {
            const compObj = completenessList.find(c => c.name === compName);
            const shortName = compObj ? compObj.short : compName;
            const count = variantRows.filter(r => r.kelengkapan === shortName || r.kelengkapan === compName).length;
            const opt = document.createElement('option');
            opt.value = `comp:${shortName}`;
            opt.textContent = `Khusus: ${shortName} (${count} Baris)`;
            groupOpt.appendChild(opt);
        });
        selector.appendChild(groupOpt);
    }

    if (hasThickness && thicknessMode === 'multi' && activeThicknesses.length > 0) {
        const thGroup = document.createElement('optgroup');
        thGroup.label = 'Filter Ketebalan';
        activeThicknesses.forEach(th => {
            const count = variantRows.filter(r => r.tebal == th).length;
            const opt = document.createElement('option');
            opt.value = `tebal:${th}`;
            opt.textContent = `Khusus Tebal: ${th} cm (${count} Baris)`;
            thGroup.appendChild(opt);
        });
        selector.appendChild(thGroup);
    }

    if (selector.options) {
        for (let i = 0; i < selector.options.length; i++) {
            if (selector.options[i].value === currentVal) {
                selector.value = currentVal;
                break;
            }
        }
    }
}

function applyBatchSettings() {
    saveCurrentTableInputs();

    const target = document.getElementById('batchTargetSelector')?.value || 'all';
    const bBase = document.getElementById('batchBasePrice')?.value.trim() || '';
    const bSell = document.getElementById('batchSellPrice')?.value.trim() || '';
    const bShipping = document.getElementById('batchShippingCost')?.value.trim() || '';
    const bPrefix = document.getElementById('batchSkuPrefix')?.value.trim() || '';
    const bLength = document.getElementById('batchLength')?.value.trim() || '';
    const bWidth = document.getElementById('batchWidth')?.value.trim() || '';
    const bHeight = document.getElementById('batchHeight')?.value.trim() || '';
    const bWeight = document.getElementById('batchWeight')?.value.trim() || '';

    if (!bBase && !bSell && !bShipping && !bPrefix && !bLength && !bWidth && !bHeight && !bWeight) {
        alert('Mohon isi minimal salah satu kolom pada Ubah Sekaligus.');
        return;
    }

    let matchedCount = 0;

    variantRows.forEach((v) => {
        let match = false;
        if (target === 'all') {
            match = true;
        } else if (target.startsWith('comp:')) {
            const targetComp = target.replace('comp:', '');
            match = (v.kelengkapan === targetComp);
        } else if (target.startsWith('tebal:')) {
            const targetTebal = target.replace('tebal:', '');
            match = (String(v.tebal) === targetTebal);
        }

        if (match) {
            matchedCount++;
            if (bBase !== '') v.base_price = bBase;
            if (bSell !== '') v.sell_price = bSell;
            if (bShipping !== '') v.shipping_cost = bShipping;
            if (bLength !== '') v.length = bLength;
            if (bWidth !== '') v.width = bWidth;
            if (bHeight !== '') v.height = bHeight;
            if (bWeight !== '') v.weight = bWeight;
            if (bPrefix !== '') {
                const compObj = completenessList.find(c => c.name === v.kelengkapan || c.short === v.kelengkapan);
                v.sku = generateAutoSku(v.size, compObj?.code, thicknessMode === 'multi' ? v.tebal : null);
                v.has_db_sku = false;
            }
        }
    });

    renderVariantsTable();
    showToast('success', `Pengaturan berhasil diterapkan ke ${matchedCount} baris varian!`);
}

// ==================== 5. GROUPED VARIANT CARDS RENDERING ====================
function renderVariantsTable() {
    const container = document.getElementById('variantsGroupedContainer');
    const warning = document.getElementById('noVariantsWarning');
    const badge = document.getElementById('variantRowCountBadge');

    if (!container) return;

    // Get unique sizes in current variantRows
    const uniqueSizes = [];
    variantRows.forEach(r => {
        if (r.size && !uniqueSizes.includes(r.size)) {
            uniqueSizes.push(r.size);
        }
    });

    const activeCount = variantRows.filter(r => !r.excluded).length;
    if (badge) {
        badge.textContent = `${uniqueSizes.length} Ukuran (${variantRows.length} Total Variasi)`;
    }

    if (variantRows.length === 0 || uniqueSizes.length === 0) {
        if (warning) warning.classList.remove('hidden');
        container.innerHTML = `
            <div class="p-8 text-center rounded-2xl border-2 border-dashed border-outline-variant/40 bg-white">
                <span class="material-symbols-outlined text-4xl text-on-surface-variant/40 mb-2">category</span>
                <p class="text-sm font-semibold text-on-surface">Belum ada kombinasi variasi produk yang aktif</p>
                <p class="text-xs text-on-surface-variant mt-1">Aktifkan ukuran produk pada panel konfigurasi di atas untuk menghasilkan daftar variasi.</p>
            </div>
        `;
        return;
    }
    if (warning) warning.classList.add('hidden');

    const isFixedShipping = $('input[name="shipping_scheme"]:checked').val() === 'fixed';
    const productDefaultShipping = parseFloat(document.getElementById('shippingCostInput')?.value) || 0;

    let colorsSummary = 'Standar';
    if (productColorsList.length > 0) {
        colorsSummary = productColorsList.map(c => c.color_name).join(', ');
    }

    container.innerHTML = '';

    uniqueSizes.forEach((sizeStr) => {
        const dims = parseSizeDimensions(sizeStr);
        const sizeRows = [];
        variantRows.forEach((r, idx) => {
            if (r.size === sizeStr) {
                sizeRows.push({ item: r, globalIdx: idx });
            }
        });

        const baseSku = generateAutoSku(sizeStr, null, null);
        const cardSlug = slugify(sizeStr);

        // Calculate common sell price for this size if all active rows share the same price
        const activeSizeRows = sizeRows.filter(sr => !sr.item.excluded);
        let commonSellPrice = '';
        if (activeSizeRows.length > 0) {
            const firstPrice = activeSizeRows[0].item.sell_price;
            if (firstPrice !== undefined && firstPrice !== null && firstPrice !== '' && parseFloat(firstPrice) > 0) {
                if (activeSizeRows.every(sr => parseFloat(sr.item.sell_price) === parseFloat(firstPrice))) {
                    commonSellPrice = firstPrice;
                }
            }
        }

        // Unique thicknesses and completeness in this size
        const sizeTebals = Array.from(new Set(sizeRows.map(sr => sr.item.tebal).filter(Boolean)));
        const sizeComps = Array.from(new Set(sizeRows.map(sr => sr.item.kelengkapan).filter(Boolean)));

        let tebalBadges = sizeTebals.map(t => `<span class="px-2 py-0.5 rounded-md bg-emerald-50 text-emerald-700 font-bold text-[10px] border border-emerald-200/60 font-mono">T.${escapeHtml(String(t))} cm</span>`).join(' ');
        let compBadges = sizeComps.map(c => `<span class="px-2 py-0.5 rounded-md bg-blue-50 text-blue-700 font-bold text-[10px] border border-blue-200/60">${escapeHtml(c)}</span>`).join(' ');

        const card = document.createElement('div');
        card.className = 'ukuran-card bg-white rounded-2xl border border-outline-variant/50 shadow-2xs overflow-hidden transition-all';
        card.dataset.size = sizeStr;

        // Card Header
        let headerHtml = `
            <div class="px-5 py-4 bg-surface-container-lowest/90 border-b border-outline-variant/30 flex flex-col md:flex-row md:items-center justify-between gap-3">
                <div class="flex items-start md:items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-primary/10 text-primary flex items-center justify-center font-bold text-sm shrink-0 border border-primary/20 shadow-2xs mt-0.5 md:mt-0">
                        <span class="material-symbols-outlined text-[22px]">bed</span>
                    </div>
                    <div>
                        <div class="flex items-center gap-2 flex-wrap">
                            <h4 class="text-sm font-bold text-on-surface">Ukuran ${escapeHtml(sizeStr)}</h4>
                            <span class="px-2 py-0.5 rounded-md bg-surface-container text-on-surface-variant font-mono text-[11px] font-semibold">
                                P: ${dims.length || 200} cm × L: ${dims.width || 0} cm
                            </span>
                            <span class="px-2 py-0.5 rounded-md bg-primary/10 text-primary font-bold text-[11px]">
                                ${sizeRows.length} Opsi Harga
                            </span>
                        </div>
                        <div class="text-[11px] text-on-surface-variant flex items-center gap-2 mt-1.5 flex-wrap">
                            <span>SKU Induk: <code class="font-mono font-bold text-on-surface">${escapeHtml(baseSku)}</code></span>
                            <span>•</span>
                            <span class="flex items-center gap-1">
                                <span class="material-symbols-outlined text-[13px] text-on-surface-variant">palette</span>
                                <span>Warna:</span>
                                <span class="font-semibold text-on-surface">${escapeHtml(colorsSummary)}</span>
                            </span>
                            ${tebalBadges ? `<span>•</span> <span>Tebal:</span> ${tebalBadges}` : ''}
                            ${compBadges ? `<span>•</span> <span>Paket:</span> ${compBadges}` : ''}
                        </div>
                    </div>
                </div>

                <div class="flex items-center gap-2 self-end md:self-auto flex-wrap">
                    <!-- Quick Price Setter for this specific size -->
                    <div class="flex items-center gap-1.5 bg-surface-container-low px-2.5 py-1 rounded-xl border border-outline-variant/40 shadow-2xs">
                        <span class="text-[10px] font-bold text-on-surface-variant uppercase">Harga:</span>
                        <input type="number" id="quickPrice_${cardSlug}" placeholder="Rp Jual" value="${commonSellPrice}" class="w-24 px-2 py-0.5 text-xs font-bold text-primary bg-white border border-outline-variant/60 rounded-lg focus:ring-1 focus:ring-primary focus:outline-none" onkeydown="if(event.key==='Enter'){ event.preventDefault(); applyPriceToSize('${escapeHtml(sizeStr)}'); }">
                        <button type="button" onclick="applyPriceToSize('${escapeHtml(sizeStr)}')" class="px-2.5 py-1 bg-primary text-white rounded-lg text-[10px] font-bold hover:opacity-90 shadow-2xs transition-all" title="Terapkan harga ini ke semua varian ukuran ${escapeHtml(sizeStr)}">
                            Set
                        </button>
                    </div>

                    ${isFixedShipping ? `
                    <!-- Quick Shipping Setter for this specific size -->
                    <div class="flex items-center gap-1.5 bg-amber-50/80 px-2.5 py-1 rounded-xl border border-amber-200/60 shadow-2xs">
                        <span class="text-[10px] font-bold text-amber-900 uppercase">Ongkir:</span>
                        <input type="number" step="1000" id="quickShipping_${cardSlug}" placeholder="Rp Ongkir" class="w-24 px-2 py-0.5 text-xs font-bold text-amber-900 bg-white border border-amber-300 rounded-lg focus:ring-1 focus:ring-amber-500 focus:outline-none" onkeydown="if(event.key==='Enter'){ event.preventDefault(); applyShippingToSize('${escapeHtml(sizeStr)}'); }">
                        <button type="button" onclick="applyShippingToSize('${escapeHtml(sizeStr)}')" class="px-2.5 py-1 bg-amber-600 text-white rounded-lg text-[10px] font-bold hover:bg-amber-700 shadow-2xs transition-all" title="Terapkan ongkir ini ke semua varian ukuran ${escapeHtml(sizeStr)}">
                            Set
                        </button>
                    </div>
                    ` : ''}

                    <!-- Collapse Toggle -->
                    <button type="button" onclick="toggleUkuranCard('${cardSlug}')" class="p-1.5 text-on-surface-variant hover:text-on-surface rounded-xl hover:bg-surface-container border border-outline-variant/30 transition-colors" title="Buka / Tutup Rincian Ukuran Ini">
                        <span class="material-symbols-outlined text-[20px] transition-transform duration-200" id="collapseIcon_${cardSlug}">expand_less</span>
                    </button>
                </div>
            </div>
        `;

        // Card Sub-table
        let rowsHtml = '';
        sizeRows.forEach(({ item: v, globalIdx }, subIdx) => {
            const rowNo = subIdx + 1;
            const imgUrl = v.image_url || (v.image ? (v.image.startsWith('http') ? v.image : '/storage/' + v.image) : null);
            let photoHtml = '';
            if (imgUrl) {
                photoHtml = `
                    <div class="relative group/photo w-8 h-8 mx-auto rounded-lg overflow-hidden border border-primary/50 shadow-2xs">
                        <img src="${imgUrl}" class="w-full h-full object-cover">
                        <div class="absolute inset-0 bg-black/60 opacity-0 group-hover/photo:opacity-100 flex items-center justify-center gap-1 transition-opacity">
                            <button type="button" onclick="openRowImagePicker(${globalIdx})" class="p-0.5 text-white hover:text-primary transition-colors" title="Ganti Foto">
                                <span class="material-symbols-outlined text-[13px]">edit</span>
                            </button>
                            <button type="button" onclick="removeRowImage(${globalIdx})" class="p-0.5 text-white hover:text-danger transition-colors" title="Hapus Foto">
                                <span class="material-symbols-outlined text-[13px]">close</span>
                            </button>
                        </div>
                    </div>
                `;
            } else {
                photoHtml = `
                    <button type="button" onclick="openRowImagePicker(${globalIdx})" class="w-8 h-8 mx-auto rounded-lg border-2 border-dashed border-outline-variant/60 hover:border-primary/60 hover:bg-primary/5 flex items-center justify-center text-on-surface-variant hover:text-primary transition-all shadow-2xs" title="Upload foto khusus kombinasi ini">
                        <span class="material-symbols-outlined text-[14px]">add_a_photo</span>
                    </button>
                `;
            }

            const lengthVal = (v.length !== undefined && v.length !== null && v.length !== '') ? v.length : (dims.length || 200);
            const widthVal = (v.width !== undefined && v.width !== null && v.width !== '') ? v.width : (dims.width || '');
            const heightVal = (v.height !== undefined && v.height !== null && v.height !== '') ? v.height : (v.tebal || singleThickness || 25);
            const weightVal = (v.weight !== undefined && v.weight !== null && v.weight !== '') ? v.weight : (document.getElementById('productWeight')?.value || '');
            const shippingCostVal = (v.shipping_cost !== undefined && v.shipping_cost !== null && v.shipping_cost !== '') ? v.shipping_cost : (productDefaultShipping || 0);

            rowsHtml += `
                <tr class="variant-row hover:bg-surface-container/20 transition-colors ${v.excluded ? ' opacity-40 bg-surface-container-low' : ''}"
                    data-index="${globalIdx}"
                    data-key="${v.key || getCombinationKey(v.size, v.kelengkapan, v.tebal)}"
                    data-id="${v.id || ''}"
                    data-has-db-sku="${v.has_db_sku ? '1' : '0'}"
                    data-excluded="${v.excluded ? '1' : '0'}">
                    <td class="px-3 py-2.5 text-center text-on-surface-variant font-mono text-[11px]">${rowNo}</td>
                    <td class="px-2 py-2.5 text-center">${photoHtml}</td>
                    ${hasCompleteness ? `
                        <td class="px-3 py-2.5">
                            <span class="px-2 py-1 rounded-lg bg-blue-50 text-blue-800 font-bold text-xs border border-blue-200/70 inline-block">
                                ${escapeHtml(v.kelengkapan || 'Standar')}
                            </span>
                        </td>
                    ` : ''}
                    ${hasThickness ? `
                    <td class="px-3 py-2.5">
                        <span class="px-2 py-1 rounded-lg bg-emerald-50 text-emerald-800 font-bold text-xs border border-emerald-200/70 inline-block font-mono">
                            ${escapeHtml(String(v.tebal || heightVal || ''))} cm
                        </span>
                    </td>
                    ` : ''}
                    <td class="px-3 py-2.5">
                        <input type="text" class="v-sku w-full min-w-[200px] px-2.5 py-1.5 border border-outline-variant/60 bg-surface-container/60 text-on-surface-variant rounded-lg text-xs font-mono font-bold cursor-not-allowed select-all focus:outline-none" value="${escapeHtml(v.sku || '')}" title="SKU otomatis terisi (Kode Produk + Ukuran - Kelengkapan/Tebal)" readonly tabindex="-1">
                    </td>
                    <td class="px-3 py-2.5">
                        <input type="text" class="v-variant-name w-full min-w-[170px] px-2.5 py-1.5 border border-outline-variant rounded-lg text-xs font-semibold text-on-surface bg-white focus:ring-2 focus:ring-primary/20 focus:border-primary focus:outline-none transition-all" value="${escapeHtml(v.variant_name || '')}" placeholder="Nama kombinasi" oninput="onRowNameChanged(this, ${globalIdx})">
                    </td>
                    <td class="px-3 py-2.5">
                        <input type="number" step="0.01" class="v-base-price w-full min-w-[110px] px-2.5 py-1.5 border border-outline-variant rounded-lg text-xs focus:ring-2 focus:ring-primary/20 focus:outline-none" placeholder="0" value="${v.base_price !== null && v.base_price !== undefined && v.base_price !== '' ? v.base_price : 0}">
                    </td>
                    <td class="px-3 py-2.5">
                        <input type="number" step="0.01" class="v-sell-price w-full min-w-[120px] px-2.5 py-1.5 border border-outline-variant rounded-lg text-xs font-bold text-primary focus:ring-2 focus:ring-primary/20 focus:outline-none" placeholder="0" value="${v.sell_price !== null && v.sell_price !== undefined && v.sell_price !== '' ? v.sell_price : 0}">
                    </td>
                    ${isFixedShipping ? `
                    <td class="px-3 py-2.5 bg-amber-50/30 border-l border-r border-amber-200/40">
                        <input type="number" step="1000" min="0" class="v-shipping-cost w-full min-w-[110px] px-2.5 py-1.5 border border-amber-300 rounded-lg text-xs font-bold text-amber-900 bg-white focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 focus:outline-none" placeholder="0" value="${shippingCostVal}">
                    </td>
                    ` : `
                    <input type="hidden" class="v-shipping-cost" value="${shippingCostVal}">
                    `}
                    <!-- Variant-Level Dimensions Inputs (P, L, T, Berat) -->
                    <td class="px-1.5 py-2.5 text-center">
                        <input type="number" step="1" min="0" class="v-length w-16 px-1.5 py-1.5 border border-outline-variant rounded-lg text-xs text-center font-medium bg-white focus:ring-2 focus:ring-primary/20 focus:border-primary focus:outline-none [appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none transition-all" placeholder="P" value="${escapeHtml(String(lengthVal))}" title="Panjang (cm)">
                    </td>
                    <td class="px-1.5 py-2.5 text-center">
                        <input type="number" step="1" min="0" class="v-width w-16 px-1.5 py-1.5 border border-outline-variant rounded-lg text-xs text-center font-medium bg-white focus:ring-2 focus:ring-primary/20 focus:border-primary focus:outline-none [appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none transition-all" placeholder="L" value="${escapeHtml(String(widthVal))}" title="Lebar (cm)">
                    </td>
                    <td class="px-1.5 py-2.5 text-center">
                        <input type="number" step="0.1" min="0" class="v-height w-16 px-1.5 py-1.5 border border-outline-variant rounded-lg text-xs text-center font-medium bg-white focus:ring-2 focus:ring-primary/20 focus:border-primary focus:outline-none [appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none transition-all" placeholder="T" value="${escapeHtml(String(heightVal))}" title="Tinggi (cm)">
                    </td>
                    <td class="px-1.5 py-2.5 text-center">
                        <input type="number" step="0.01" min="0" class="v-weight w-16 px-1.5 py-1.5 border border-outline-variant rounded-lg text-xs text-center font-medium bg-white focus:ring-2 focus:ring-primary/20 focus:border-primary focus:outline-none [appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none transition-all" placeholder="0" value="${escapeHtml(String(weightVal))}" title="Berat (kg)">
                    </td>
                    <td class="px-3 py-2.5">
                        <select class="v-status w-full px-2 py-1.5 border border-outline-variant rounded-lg text-xs bg-white focus:ring-2 focus:ring-primary/20 focus:outline-none">
                            <option value="1" ${v.status == 1 ? 'selected' : ''}>Aktif</option>
                            <option value="0" ${v.status == 0 ? 'selected' : ''}>Nonaktif</option>
                        </select>
                    </td>
                    <td class="px-2 py-2.5 text-center">
                        <div class="flex items-center justify-center gap-0.5">
                            <button type="button" onclick="toggleExcludeRow(${globalIdx})" class="p-1 text-on-surface-variant hover:text-primary hover:bg-surface-container rounded-lg transition-colors" title="${v.excluded ? 'Aktifkan Kembali' : 'Sembunyikan Baris Ini'}">
                                <span class="material-symbols-outlined text-[17px]">${v.excluded ? 'visibility' : 'visibility_off'}</span>
                            </button>
                            <button type="button" onclick="deleteVariantRow(${globalIdx})" class="p-1 text-on-surface-variant hover:text-danger hover:bg-danger/10 rounded-lg transition-colors" title="Hapus Baris Ini">
                                <span class="material-symbols-outlined text-[17px]">delete</span>
                            </button>
                        </div>
                    </td>
                </tr>
            `;
        });

        let bodyHtml = `
            <div id="ukuranBody_${cardSlug}" class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-surface-gray/50 border-b border-outline-variant/20 text-[10px] font-bold text-on-surface-variant uppercase tracking-wider">
                            <th class="px-3 py-2 text-center w-9">#</th>
                            <th class="px-2 py-2 w-12 text-center">Foto</th>
                            ${hasCompleteness ? `<th class="px-3 py-2 min-w-[130px]">${escapeHtml(completenessAttributeTitle || 'Kelengkapan')}</th>` : ''}
                            ${hasThickness ? '<th class="px-3 py-2 min-w-[85px]">Ketebalan</th>' : ''}
                            <th class="px-3 py-2 min-w-[210px]">SKU</th>
                            <th class="px-3 py-2 min-w-[180px]">Nama Kombinasi</th>
                            <th class="px-3 py-2 min-w-[120px]">Harga Modal (Rp)</th>
                            <th class="px-3 py-2 min-w-[130px]">Harga Jual (Rp) <span class="text-danger">*</span></th>
                            ${isFixedShipping ? '<th class="px-3 py-2 min-w-[130px] text-amber-900 bg-amber-50/50 border-l border-r border-amber-200/50">Ongkir Tetap (Rp)</th>' : ''}
                            <th class="px-1.5 py-2 min-w-[65px] text-center">P (cm)</th>
                            <th class="px-1.5 py-2 min-w-[65px] text-center">L (cm)</th>
                            <th class="px-1.5 py-2 min-w-[65px] text-center">T (cm)</th>
                            <th class="px-1.5 py-2 min-w-[65px] text-center">Berat (kg)</th>
                            <th class="px-3 py-2 min-w-[85px]">Status</th>
                            <th class="px-2 py-2 text-center w-10">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-outline-variant/15 text-xs bg-white">
                        ${rowsHtml}
                    </tbody>
                </table>
            </div>
        `;

        card.innerHTML = headerHtml + bodyHtml;
        container.appendChild(card);
    });
}

function applyPriceToSize(sizeStr) {
    const cardSlug = slugify(sizeStr);
    const input = document.getElementById(`quickPrice_${cardSlug}`);
    const val = input ? parseFloat(input.value) : 0;
    if (!val || val <= 0) {
        alert('Mohon masukkan nominal harga jual yang valid.');
        if (input) input.focus();
        return;
    }

    saveCurrentTableInputs();
    let count = 0;
    variantRows.forEach(v => {
        if (v.size === sizeStr) {
            v.sell_price = val;
            if (!v.base_price || v.base_price === '') v.base_price = val;
            count++;
        }
    });

    renderVariantsTable();
    showToast('success', `Harga Rp ${Number(val).toLocaleString('id-ID')} berhasil diterapkan ke semua varian ukuran ${sizeStr}!`);
}

function applyShippingToSize(sizeStr) {
    const cardSlug = slugify(sizeStr);
    const input = document.getElementById(`quickShipping_${cardSlug}`);
    const val = input ? parseFloat(input.value) : 0;
    if (isNaN(val) || val < 0) {
        alert('Mohon masukkan nominal ongkir yang valid (minimal Rp 0).');
        if (input) input.focus();
        return;
    }

    saveCurrentTableInputs();
    let count = 0;
    variantRows.forEach(v => {
        if (v.size === sizeStr) {
            v.shipping_cost = val;
            count++;
        }
    });

    renderVariantsTable();
    showToast('success', `Ongkir Rp ${Number(val).toLocaleString('id-ID')} berhasil diterapkan ke semua varian ukuran ${sizeStr}!`);
}

function applyDefaultShippingToAllVariants() {
    const defaultVal = parseFloat(document.getElementById('shippingCostInput')?.value) || 0;
    if (isNaN(defaultVal) || defaultVal < 0) {
        alert('Masukkan nominal ongkos kirim default yang valid.');
        return;
    }
    saveCurrentTableInputs();
    if (variantRows.length === 0) {
        alert('Belum ada varian produk.');
        return;
    }
    variantRows.forEach(v => {
        v.shipping_cost = defaultVal;
    });
    renderVariantsTable();
    showToast('success', `Ongkir default Rp ${Number(defaultVal).toLocaleString('id-ID')} berhasil diterapkan ke seluruh varian!`);
}

function toggleUkuranCard(cardSlug) {
    const body = document.getElementById(`ukuranBody_${cardSlug}`);
    const icon = document.getElementById(`collapseIcon_${cardSlug}`);
    if (!body || !icon) return;

    if (body.classList.contains('hidden')) {
        body.classList.remove('hidden');
        icon.textContent = 'expand_less';
    } else {
        body.classList.add('hidden');
        icon.textContent = 'expand_more';
    }
}

function toggleAllUkuranCards(expand) {
    const cards = document.querySelectorAll('#variantsGroupedContainer .ukuran-card');
    cards.forEach(card => {
        const sizeStr = card.dataset.size;
        if (!sizeStr) return;
        const cardSlug = slugify(sizeStr);
        const body = document.getElementById(`ukuranBody_${cardSlug}`);
        const icon = document.getElementById(`collapseIcon_${cardSlug}`);
        if (body && icon) {
            if (expand) {
                body.classList.remove('hidden');
                icon.textContent = 'expand_less';
            } else {
                body.classList.add('hidden');
                icon.textContent = 'expand_more';
            }
        }
    });
}

function onRowNameChanged(input, idx) {
    if (!variantRows[idx]) return;
    variantRows[idx].variant_name = input.value.trim();
}

function onRowSkuChanged(input, idx) {
    if (!variantRows[idx]) return;
    variantRows[idx].sku = input.value.trim();
    variantRows[idx].has_db_sku = true;
}

function deleteVariantRow(idx) {
    saveCurrentTableInputs();
    const v = variantRows[idx];
    if (!v) return;

    if (v.has_db_sku) {
        if (!confirm(`Kombinasi "${v.variant_name}" memiliki SKU ${v.sku} di database. Yakin ingin menghapus varian ini?`)) {
            return;
        }
    }
    variantRows.splice(idx, 1);
    renderVariantsTable();
}

function toggleExcludeRow(idx) {
    if (!variantRows[idx]) return;
    variantRows[idx].excluded = !variantRows[idx].excluded;
    renderVariantsTable();
}

function resetAllVariations() {
    if (!confirm('Yakin ingin mereset seluruh variasi ke konfigurasi awal?')) return;
    rowCache = {};
    initVariantsFromBackend();
}

// ==================== 6. VARIANT ROW IMAGE PICKER ====================
function openRowImagePicker(rowIdx) {
    currentUploadContext = { type: 'row', index: rowIdx };
    document.getElementById('globalVariantImagePicker').click();
}

function handleVariantImageFilePicked(input) {
    if (!input.files || !input.files[0] || !currentUploadContext) return;
    const file = input.files[0];
    const previewUrl = URL.createObjectURL(file);

    if (currentUploadContext.type === 'row') {
        const idx = currentUploadContext.index;
        if (variantRows[idx]) {
            variantRows[idx].image_file = file;
            variantRows[idx].image_url = previewUrl;
            variantRows[idx].image = null;
            renderVariantsTable();
            showToast('success', `Foto kombinasi "${variantRows[idx].variant_name}" berhasil dipilih!`);
        }
    }

    input.value = '';
    currentUploadContext = null;
}

function removeRowImage(rowIdx) {
    if (!variantRows[rowIdx]) return;
    variantRows[rowIdx].image_file = null;
    variantRows[rowIdx].image_url = null;
    variantRows[rowIdx].image = null;
    renderVariantsTable();
}

// ==================== 6b. PRODUCT HEADER GALLERY IMAGES ====================
let existingGalleryImages = @json($existingGalleryImages ?? []);
let newGalleryFiles = [];

// Unified galleryItems list to allow seamless reordering (drag & drop and arrow buttons)
let galleryItems = [];
if (Array.isArray(existingGalleryImages) && existingGalleryImages.length > 0) {
    galleryItems = existingGalleryImages.map(img => ({
        type: 'existing',
        id: img.id,
        url: img.url,
        sort_order: img.sort_order ?? 0
    }));
}

function handleGalleryImagesPicked(input) {
    if (!input.files || input.files.length === 0) return;
    for (let i = 0; i < input.files.length; i++) {
        const file = input.files[i];
        galleryItems.push({
            type: 'new',
            id: null,
            file: file,
            previewUrl: URL.createObjectURL(file)
        });
    }
    input.value = '';
    renderGalleryImages();
}

function removeGalleryItem(idx) {
    if (galleryItems[idx] && galleryItems[idx].type === 'new' && galleryItems[idx].previewUrl) {
        try { URL.revokeObjectURL(galleryItems[idx].previewUrl); } catch (e) {}
    }
    galleryItems.splice(idx, 1);
    renderGalleryImages();
}

function removeExistingGalleryImage(idx) {
    removeGalleryItem(idx);
}

function removeNewGalleryImage(idx) {
    removeGalleryItem(idx);
}

function moveGalleryItem(idx, direction) {
    const targetIdx = idx + direction;
    if (targetIdx < 0 || targetIdx >= galleryItems.length) return;
    const item = galleryItems.splice(idx, 1)[0];
    galleryItems.splice(targetIdx, 0, item);
    renderGalleryImages();
}

// HTML5 Drag & Drop handlers
let draggedGalleryIdx = null;

function handleGalleryDragStart(e, idx) {
    draggedGalleryIdx = idx;
    e.dataTransfer.effectAllowed = 'move';
    e.dataTransfer.setData('text/plain', String(idx));
    const target = e.currentTarget;
    setTimeout(() => {
        if (target) {
            target.classList.add('opacity-40', 'scale-95');
        }
    }, 0);
}

function handleGalleryDragEnd(e) {
    draggedGalleryIdx = null;
    const cards = document.querySelectorAll('.gallery-card');
    cards.forEach(c => {
        c.classList.remove('opacity-40', 'scale-95', 'ring-2', 'ring-primary', 'border-primary');
    });
}

function handleGalleryDragOver(e, idx) {
    e.preventDefault();
    e.dataTransfer.dropEffect = 'move';
}

function handleGalleryDragEnter(e, idx) {
    e.preventDefault();
    if (draggedGalleryIdx !== null && draggedGalleryIdx !== idx) {
        const target = document.getElementById(`gallery-item-${idx}`);
        if (target) {
            target.classList.add('ring-2', 'ring-primary', 'border-primary');
        }
    }
}

function handleGalleryDragLeave(e, idx) {
    const target = document.getElementById(`gallery-item-${idx}`);
    if (target) {
        target.classList.remove('ring-2', 'ring-primary', 'border-primary');
    }
}

function handleGalleryDrop(e, targetIdx) {
    e.preventDefault();
    const sourceIdx = draggedGalleryIdx !== null ? draggedGalleryIdx : parseInt(e.dataTransfer.getData('text/plain'), 10);
    if (sourceIdx !== null && !isNaN(sourceIdx) && sourceIdx !== targetIdx && sourceIdx >= 0 && sourceIdx < galleryItems.length) {
        const movedItem = galleryItems.splice(sourceIdx, 1)[0];
        galleryItems.splice(targetIdx, 0, movedItem);
        renderGalleryImages();
    }
    draggedGalleryIdx = null;
}

function renderGalleryImages() {
    const container = document.getElementById('galleryImagesContainer');
    if (!container) return;

    if (galleryItems.length === 0) {
        container.innerHTML = `<p class="col-span-full text-center py-6 text-xs text-on-surface-variant flex flex-col items-center justify-center gap-2">
            <span class="material-symbols-outlined text-[26px] text-outline">photo_library</span>
            <span>Belum ada foto galeri tambahan. Klik <b>"+ Tambah Foto Galeri"</b> untuk mengunggah banyak foto produk.</span>
        </p>`;
        return;
    }

    let html = '';
    galleryItems.forEach((item, idx) => {
        const imgSrc = item.previewUrl || item.url;
        const isNew = item.type === 'new';
        const isFirst = idx === 0;
        const isLast = idx === galleryItems.length - 1;

        html += `
            <div id="gallery-item-${idx}" 
                 class="gallery-card relative group/gallery aspect-square rounded-xl overflow-hidden border-2 ${isNew ? 'border-primary/60' : 'border-outline-variant/60'} bg-surface-container-lowest shadow-2xs cursor-grab active:cursor-grabbing transition-all select-none"
                 draggable="true"
                 ondragstart="handleGalleryDragStart(event, ${idx})"
                 ondragend="handleGalleryDragEnd(event)"
                 ondragover="handleGalleryDragOver(event, ${idx})"
                 ondragenter="handleGalleryDragEnter(event, ${idx})"
                 ondragleave="handleGalleryDragLeave(event, ${idx})"
                 ondrop="handleGalleryDrop(event, ${idx})">
                
                <img src="${imgSrc}" class="w-full h-full object-cover pointer-events-none" alt="Gallery #${idx + 1}">
                
                <!-- Order & Type Badge -->
                <div class="absolute top-1.5 left-1.5 flex items-center gap-1 z-10 pointer-events-none">
                    <span class="px-1.5 py-0.5 ${isFirst ? 'bg-primary text-white font-black' : 'bg-black/70 text-white font-bold'} rounded text-[10px] shadow-xs">
                        #${idx + 1}
                    </span>
                    ${isNew ? '<span class="px-1.5 py-0.5 bg-secondary-container text-on-secondary-container rounded text-[9px] font-bold shadow-xs">Baru</span>' : ''}
                </div>

                <!-- Drag indicator handle overlay -->
                <div class="absolute bottom-1.5 left-1/2 -translate-x-1/2 px-2 py-0.5 rounded-full bg-black/70 text-white text-[10px] flex items-center gap-0.5 opacity-0 group-hover/gallery:opacity-100 transition-opacity pointer-events-none shadow-xs">
                    <span class="material-symbols-outlined text-[13px]">drag_indicator</span>
                    <span>Geser</span>
                </div>

                <!-- Action Buttons: Move Left, Move Right, Delete -->
                <div class="absolute top-1.5 right-1.5 flex items-center gap-1 z-10">
                    ${!isFirst ? `
                        <button type="button" onclick="moveGalleryItem(${idx}, -1)" class="w-6 h-6 rounded-full bg-black/70 hover:bg-primary text-white flex items-center justify-center opacity-100 sm:opacity-0 group-hover/gallery:opacity-100 transition-all shadow-xs" title="Geser ke kiri">
                            <span class="material-symbols-outlined text-[14px]">arrow_back</span>
                        </button>
                    ` : ''}
                    ${!isLast ? `
                        <button type="button" onclick="moveGalleryItem(${idx}, 1)" class="w-6 h-6 rounded-full bg-black/70 hover:bg-primary text-white flex items-center justify-center opacity-100 sm:opacity-0 group-hover/gallery:opacity-100 transition-all shadow-xs" title="Geser ke kanan">
                            <span class="material-symbols-outlined text-[14px]">arrow_forward</span>
                        </button>
                    ` : ''}
                    <button type="button" onclick="removeGalleryItem(${idx})" class="w-6 h-6 rounded-full bg-danger text-white flex items-center justify-center opacity-100 sm:opacity-0 group-hover/gallery:opacity-100 transition-all hover:bg-danger/80 shadow-xs" title="Hapus Foto">
                        <span class="material-symbols-outlined text-[14px]">close</span>
                    </button>
                </div>
            </div>
        `;
    });

    container.innerHTML = html;
}

// ==================== 7. INITIALIZE FROM BACKEND ====================
function initVariantsFromBackend() {
    // 1. Initialize Colors
    const existingColors = @json($colorData);
    if (Array.isArray(existingColors) && existingColors.length > 0) {
        productColorsList = existingColors.map(c => ({
            id: c.id,
            color_name: c.color_name,
            color_code: c.color_code
        }));
    } else {
        productColorsList = [];
    }
    renderColorChips();

    // 2. Initialize Height
    const pHeight = document.getElementById('productHeight')?.value;
    if (pHeight) {
        singleThickness = parseFloat(pHeight) || 25;
        const singleInput = document.getElementById('singleThicknessInput');
        if (singleInput) singleInput.value = singleThickness;
    }

    // 3. Initialize Variants (Ukuran & SKU)
    const existingVariants = @json($variantData);
    if (Array.isArray(existingVariants) && existingVariants.length > 0) {
        // Detect if existing variants use completeness or multi-thickness
        let detectedComps = new Set();
        let detectedHeights = new Set();
        let detectedSizes = new Set();

        existingVariants.forEach(v => {
            const rawAttrs = v.attributes || {};

            // Detect custom completeness / attribute title (e.g. Feel or Kelengkapan)
            if (rawAttrs._completeness_title) {
                completenessAttributeTitle = rawAttrs._completeness_title;
            } else {
                for (const k in rawAttrs) {
                    if (!['width', 'length', 'height', 'weight', 'status', 'image', 'image_url', 'Ukuran', 'Ketebalan', 'thickness'].includes(k)) {
                        completenessAttributeTitle = k;
                        break;
                    }
                }
            }

            const compVal = rawAttrs._completeness_title ? rawAttrs[rawAttrs._completeness_title] : (rawAttrs.Kelengkapan || rawAttrs[completenessAttributeTitle] || null);
            if (compVal) {
                let cName = compVal;
                if (cName.toLowerCase().includes('kasur saja') || cName.toLowerCase().includes('mattress')) {
                    cName = 'Kasur Saja';
                } else if (cName.toLowerCase().includes('divan') || cName.toLowerCase().includes('full') || cName.toLowerCase().includes('set')) {
                    cName = 'Set Kasur + Divan';
                }
                detectedComps.add(cName);
            } else if (v.variant_name) {
                if (v.variant_name.includes('Kasur Saja') || v.variant_name.includes('Mattress Only')) {
                    detectedComps.add('Kasur Saja');
                } else if (v.variant_name.includes('Fullset') || v.variant_name.includes('Full Set') || v.variant_name.includes('Divan') || v.variant_name.includes('Set Kasur')) {
                    detectedComps.add('Set Kasur + Divan');
                }
            }
            if (rawAttrs.Ketebalan) {
                const num = parseFloat(String(rawAttrs.Ketebalan).replace(/\D+/g, ''));
                if (num) detectedHeights.add(num);
            } else if (v.height && parseFloat(v.height) > 0) {
                detectedHeights.add(parseFloat(v.height));
            }

            // Detect size
            const sizeStr = rawAttrs.Ukuran || formatStandardSize(v.variant_name);
            if (sizeStr) {
                detectedSizes.add(sizeStr);
            }
        });

        if (detectedComps.size > 0) {
            hasCompleteness = true;
            activeCompleteness = Array.from(detectedComps);
            // Ensure they exist in completenessList
            activeCompleteness.forEach(cName => {
                if (!completenessList.some(c => c.name === cName || c.short === cName)) {
                    completenessList.push({ name: cName, short: cName, code: (cName.toLowerCase().includes('kasur') ? 'KS' : 'SD') });
                }
            });
        } else {
            hasCompleteness = false;
        }

        if (detectedHeights.size > 1) {
            thicknessMode = 'multi';
            activeThicknesses = Array.from(detectedHeights).sort((a, b) => a - b);
            activeThicknesses.forEach(h => {
                if (!availableThicknesses.includes(h)) availableThicknesses.push(h);
            });
            availableThicknesses.sort((a, b) => a - b);
        } else {
            thicknessMode = 'single';
            if (detectedHeights.size === 1) {
                singleThickness = Array.from(detectedHeights)[0];
                const sInput = document.getElementById('singleThicknessInput');
                if (sInput) sInput.value = singleThickness;
            }
        }

        if (detectedSizes.size > 0) {
            selectedSizes = Array.from(detectedSizes);
            selectedSizes.forEach(s => {
                if (!allAvailableSizes.includes(s)) allAvailableSizes.push(s);
            });
        }

        // Setup Controls UI silently without triggering rebuildCombinations
        renderStandardSizes();
        const compToggle = document.getElementById('completenessToggle');
        if (compToggle) compToggle.checked = hasCompleteness;
        const compTitleInput = document.getElementById('completenessTitleInput');
        if (compTitleInput) compTitleInput.value = completenessAttributeTitle || 'Kelengkapan';

        const compNotice = document.getElementById('completenessDisabledNotice');
        const compContainer = document.getElementById('completenessOptionsContainer');
        if (hasCompleteness) {
            compNotice?.classList.add('hidden');
            compContainer?.classList.remove('hidden');
        } else {
            compNotice?.classList.remove('hidden');
            compContainer?.classList.add('hidden');
        }
        renderCompletenessCheckboxes();

        // Setup Thickness UI silently
        hasThickness = (detectedHeights.size > 0 || existingVariants.some(v => v.height && parseFloat(v.height) > 0));
        const thToggle = document.getElementById('thicknessToggle');
        if (thToggle) thToggle.checked = hasThickness;
        const thNotice = document.getElementById('thicknessDisabledNotice');
        const thContainer = document.getElementById('thicknessOptionsContainer');
        if (hasThickness) {
            thNotice?.classList.add('hidden');
            thContainer?.classList.remove('hidden');
        } else {
            thNotice?.classList.remove('hidden');
            thContainer?.classList.add('hidden');
        }

        const sBtn = document.getElementById('thModeSingleBtn');
        const mBtn = document.getElementById('thModeMultiBtn');
        const sPanel = document.getElementById('singleThicknessPanel');
        const mPanel = document.getElementById('multiThicknessPanel');
        const note = document.getElementById('thicknessFooterNote');
        if (thicknessMode === 'multi') {
            sBtn?.classList.remove('bg-white', 'text-primary', 'shadow-2xs');
            sBtn?.classList.add('text-on-surface-variant');
            mBtn?.classList.add('bg-white', 'text-primary', 'shadow-2xs');
            mBtn?.classList.remove('text-on-surface-variant');
            sPanel?.classList.add('hidden');
            mPanel?.classList.remove('hidden');
            if (note) note.textContent = 'Setiap ketebalan menghasilkan variasi harga & SKU tersendiri';
            renderMultiThicknessChips();
        } else {
            mBtn?.classList.remove('bg-white', 'text-primary', 'shadow-2xs');
            mBtn?.classList.add('text-on-surface-variant');
            sBtn?.classList.add('bg-white', 'text-primary', 'shadow-2xs');
            sBtn?.classList.remove('text-on-surface-variant');
            mPanel?.classList.add('hidden');
            sPanel?.classList.remove('hidden');
            if (note) note.textContent = 'Tinggi / tebal produk otomatis tersinkron ke kolom T (cm)';
        }

        // Direct load from existingVariants into variantRows preserving exact backend prices
        variantRows = existingVariants.map(v => {
            const rawAttrs = v.attributes || {};
            const dims = parseSizeDimensions(v.variant_name);
            const sizeStr = rawAttrs.Ukuran || formatStandardSize(v.variant_name);
            let compStr = rawAttrs.Kelengkapan || (completenessAttributeTitle ? rawAttrs[completenessAttributeTitle] : '') || '';
            if (compStr) {
                if (compStr.toLowerCase().includes('kasur saja') || compStr.toLowerCase().includes('mattress')) compStr = 'Kasur Saja';
                else if (compStr.toLowerCase().includes('divan') || compStr.toLowerCase().includes('full') || compStr.toLowerCase().includes('set')) compStr = 'Set Kasur + Divan';
            } else if (hasCompleteness && v.variant_name) {
                if (v.variant_name.includes('Kasur Saja') || v.variant_name.includes('Mattress Only')) compStr = 'Kasur Saja';
                else if (v.variant_name.includes('Fullset') || v.variant_name.includes('Full Set') || v.variant_name.includes('Divan') || v.variant_name.includes('Set Kasur')) compStr = 'Set Kasur + Divan';
            }

            const thVal = (thicknessMode === 'multi') ? (v.height || singleThickness) : (singleThickness || v.height || 25);
            const key = getCombinationKey(sizeStr, compStr, thVal);

            const compObj = compStr ? completenessList.find(c => c.name === compStr || c.short === compStr) : null;
            const autoSku = generateAutoSku(sizeStr, compObj ? compObj.code : null, thicknessMode === 'multi' ? thVal : null);
            const resolvedSku = (v.sku && String(v.sku).trim()) ? String(v.sku).trim() : autoSku;

            const basePrice = (v.base_price !== null && v.base_price !== undefined && v.base_price !== '') ? parseFloat(v.base_price) : 0;
            const sellPrice = (v.sell_price !== null && v.sell_price !== undefined && v.sell_price !== '') ? parseFloat(v.sell_price) : 0;
            const shippingCost = (v.shipping_cost !== undefined && v.shipping_cost !== null && v.shipping_cost !== '') ? parseFloat(v.shipping_cost) : '';

            const rowObj = {
                key: key,
                id: v.id || null,
                size: sizeStr,
                kelengkapan: compStr || null,
                tebal: hasThickness ? thVal : null,
                variant_name: v.variant_name || '',
                sku: resolvedSku,
                has_db_sku: !!(v.sku && String(v.sku).trim()),
                base_price: basePrice,
                sell_price: sellPrice,
                shipping_cost: shippingCost,
                length: v.length ?? dims.length ?? 200,
                width: v.width ?? dims.width ?? '',
                height: v.height ?? thVal,
                weight: v.weight ?? '',
                status: v.status !== undefined ? (v.status == 1 || v.status === true ? 1 : 0) : 1,
                image: v.image || null,
                image_url: v.image_url || null,
                image_file: null,
                excluded: false
            };

            // Register multiple key aliases into rowCache
            rowCache[key] = rowObj;
            rowCache[`${sizeStr}__${compStr || ''}__${thVal || ''}`] = rowObj;
            rowCache[`${sizeStr}__${compStr || ''}__`] = rowObj;
            rowCache[`${sizeStr}____`] = rowObj;
            if (v.id) rowCache[`id_${v.id}`] = rowObj;
            if (v.variant_name) rowCache[`name_${v.variant_name}`] = rowObj;

            return rowObj;
        });

        updateBatchTargetSelector();
        renderVariantsTable();
    } else {
        // Create mode default: start with 3 popular sizes
        selectedSizes = ['160 X 200', '180 X 200', '200 X 200'];
        hasCompleteness = false;
        thicknessMode = 'single';

        renderStandardSizes();
        const compToggle = document.getElementById('completenessToggle');
        if (compToggle) compToggle.checked = false;
        toggleCompletenessMode(false);
        setThicknessMode('single');

        rebuildCombinations();
    }
}

// ==================== 8. S3 UPLOAD HELPER ====================
async function uploadProductImage(file, folder = 'products') {
    const extension = file.name.split('.').pop().toLowerCase();
    let mimeType = file.type;
    if (!mimeType) {
        if (extension === 'png') mimeType = 'image/png';
        else if (extension === 'webp') mimeType = 'image/webp';
        else if (extension === 'gif') mimeType = 'image/gif';
        else if (extension === 'svg') mimeType = 'image/svg+xml';
        else mimeType = 'image/jpeg';
    }

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}';

    try {
        const authRes = await fetch('/api/v1/media/upload-url', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify({ mime_type: mimeType, extension: extension, folder: folder })
        });

        if (authRes.ok) {
            const { upload_url, file_path } = await authRes.json();
            const uploadRes = await fetch(upload_url, {
                method: 'PUT',
                headers: { 'Content-Type': mimeType },
                body: file
            });
            if (uploadRes.ok) {
                return file_path;
            }
        }
    } catch (err) {
        console.warn('Direct upload failed, fallback to server upload...', err);
    }

    const fallbackData = new FormData();
    fallbackData.append('file', file);
    fallbackData.append('folder', folder);

    const fallbackRes = await fetch('/api/v1/media/upload', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json'
        },
        body: fallbackData
    });

    if (!fallbackRes.ok) {
        let errData = await fallbackRes.json().catch(() => ({}));
        throw new Error(errData.message || 'Gagal mengunggah file (HTTP ' + fallbackRes.status + ')');
    }

    const resJson = await fallbackRes.json();
    return resJson.file_path;
}

// ==================== 9. FORM SUBMISSION ====================
async function submitProductForm() {
    const form = document.getElementById('productForm');
    const nameInput = document.getElementById('productNameInput');

    if (!nameInput.value.trim()) {
        alert('Nama Produk wajib diisi.');
        nameInput.focus();
        return;
    }

    saveCurrentTableInputs();

    const activeRows = variantRows.filter(r => !r.excluded);
    if (activeRows.length === 0) {
        alert('Mohon tentukan minimal 1 kombinasi variasi produk yang aktif.');
        return;
    }

    // Ensure active rows have default 0 for sell_price & base_price if empty or invalid
    for (let i = 0; i < activeRows.length; i++) {
        const r = activeRows[i];
        if (!r.variant_name || !r.variant_name.trim()) {
            alert(`Nama kombinasi pada baris ke-${i + 1} tidak boleh kosong.`);
            return;
        }
        if (r.sell_price === '' || r.sell_price === null || isNaN(parseFloat(r.sell_price)) || parseFloat(r.sell_price) < 0) {
            r.sell_price = 0;
        }
        if (r.base_price === '' || r.base_price === null || isNaN(parseFloat(r.base_price)) || parseFloat(r.base_price) < 0) {
            r.base_price = 0;
        }
    }

    // Loading State on Buttons
    const saveBtns = document.querySelectorAll('.btn-save');
    saveBtns.forEach(b => {
        b.disabled = true;
        b.classList.add('opacity-70');
        b.innerHTML = '<span class="material-symbols-outlined text-[16px] animate-spin">progress_activity</span> Mengunggah foto & menyimpan...';
    });

    try {
        // Upload custom row images if any
        for (let i = 0; i < variantRows.length; i++) {
            const rowItem = variantRows[i];
            if (rowItem && rowItem.image_file && !rowItem.image) {
                rowItem.image = await uploadProductImage(rowItem.image_file, 'products');
            }
        }

        // Upload new gallery files (Header gallery)
        const uploadedGalleryMap = new Map();
        for (let i = 0; i < galleryItems.length; i++) {
            const item = galleryItems[i];
            if (item.type === 'new' && item.file) {
                const gPath = await uploadProductImage(item.file, 'products');
                if (gPath) {
                    uploadedGalleryMap.set(item, gPath);
                }
            }
        }

        // Build variantsData
        const variantsData = [];
        activeRows.forEach((v, idx) => {
            const dims = parseSizeDimensions(v.variant_name || v.size || '');
            const finalWidth = (v.width !== '' && v.width !== null) ? parseFloat(v.width) : dims.width;
            const finalLength = (v.length !== '' && v.length !== null) ? parseFloat(v.length) : dims.length;
            const finalHeight = (v.height !== '' && v.height !== null) ? parseFloat(v.height) : (parseFloat(v.tebal) || parseFloat(document.getElementById('productHeight')?.value) || 25);
            const finalWeight = (v.weight !== '' && v.weight !== null) ? parseFloat(v.weight) : (parseFloat(document.getElementById('productWeight')?.value) || null);

            const attrObj = {
                width: finalWidth,
                length: finalLength,
                height: finalHeight,
                weight: finalWeight,
                status: true
            };

            // If kelengkapan/custom option is enabled, pass dynamic attribute title & Ukuran attributes for pos-dealer-web
            if (hasCompleteness && v.kelengkapan) {
                const compTitle = (document.getElementById('completenessTitleInput')?.value || completenessAttributeTitle || 'Kelengkapan').trim();
                attrObj[compTitle] = v.kelengkapan;
                attrObj['_completeness_title'] = compTitle;
                attrObj['Ukuran'] = v.size ? formatStandardSize(v.size) : (finalWidth + ' x ' + finalLength);
            }

            // If thickness is enabled, pass Ketebalan & Ukuran attributes for pos-dealer-web
            if (hasThickness) {
                if (thicknessMode === 'multi' && v.tebal) {
                    attrObj['Ketebalan'] = String(v.tebal) + ' cm';
                } else if (singleThickness) {
                    attrObj['Ketebalan'] = String(singleThickness) + ' cm';
                }
                if (!attrObj['Ukuran']) {
                    attrObj['Ukuran'] = v.size ? formatStandardSize(v.size) : (finalWidth + ' x ' + finalLength);
                }
            }

            const shippingVal = (v.shipping_cost !== '' && v.shipping_cost !== null && !isNaN(parseFloat(v.shipping_cost))) 
                ? parseFloat(v.shipping_cost) 
                : (parseFloat(document.getElementById('shippingCostInput')?.value) || 0);

            variantsData.push({
                id: v.id || null,
                sku: (v.sku || '').trim(),
                variant_name: v.variant_name.trim(),
                base_price: (v.base_price !== '' && v.base_price !== null && !isNaN(parseFloat(v.base_price))) ? parseFloat(v.base_price) : 0,
                sell_price: (v.sell_price !== '' && v.sell_price !== null && !isNaN(parseFloat(v.sell_price))) ? parseFloat(v.sell_price) : 0,
                price: (v.sell_price !== '' && v.sell_price !== null && !isNaN(parseFloat(v.sell_price))) ? parseFloat(v.sell_price) : 0,
                shipping_cost: shippingVal,
                length: finalLength,
                width: finalWidth,
                height: finalHeight,
                weight: finalWeight,
                stock_qty: 0,
                status: v.status == '1' || v.status === 1 ? 1 : 0,
                sort_order: idx,
                image: v.image || null,
                attributes: attrObj
            });
        });

        // Build colorsData
        const colorsData = productColorsList.map(c => ({
            id: c.id || null,
            color_name: c.color_name.trim(),
            color_code: c.color_code || '#1e293b'
        }));

        document.getElementById('variantsInput').value = JSON.stringify(variantsData);
        document.getElementById('colorsInput').value = JSON.stringify(colorsData);

        // Quill Content
        let quillHtml = window.quill ? window.quill.root.innerHTML : '';
        if (quillHtml === '<p><br></p>') quillHtml = '';
        document.getElementById('description-input').value = quillHtml;

        let formData = new FormData(form);

        // Upload main thumbnail if selected
        const thumbnailInput = document.getElementById('thumbnailInput');
        formData.delete('thumbnail_file');
        if (thumbnailInput.files && thumbnailInput.files.length > 0) {
            const uploadedThumb = await uploadProductImage(thumbnailInput.files[0], 'products');
            formData.append('thumbnail_file', uploadedThumb);
            formData.set('thumbnail', uploadedThumb);
        }

        // Append gallery images in exact reordered sequence
        formData.delete('existing_images[]');
        formData.delete('existing_image_orders[]');
        formData.delete('new_images[]');
        formData.delete('new_image_orders[]');

        galleryItems.forEach((item, orderIdx) => {
            if (item.type === 'existing' && item.id) {
                formData.append('existing_images[]', item.id);
                formData.append('existing_image_orders[]', orderIdx);
            } else if (item.type === 'new') {
                const path = uploadedGalleryMap.get(item);
                if (path) {
                    formData.append('new_images[]', path);
                    formData.append('new_image_orders[]', orderIdx);
                }
            }
        });

        let actionUrl = form.getAttribute('action') || form.action;
        if (window.location.protocol === 'https:' && actionUrl.startsWith('http://')) {
            actionUrl = actionUrl.replace('http://', 'https://');
        }

        let res = await fetch(actionUrl, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        });

        const data = await res.json().catch(() => ({}));

        if (res.ok && data.success !== false) {
            window.location.href = '{{ route("products.index") }}';
        } else {
            alert(data.message || 'Gagal menyimpan produk. Mohon periksa kembali input Anda.');
            saveBtns.forEach(b => {
                b.disabled = false;
                b.classList.remove('opacity-70');
                b.innerHTML = '<span class="material-symbols-outlined text-[18px]">save</span> Simpan Produk';
            });
        }
    } catch (err) {
        console.error(err);
        alert('Terjadi kesalahan: ' + err.message);
        saveBtns.forEach(b => {
            b.disabled = false;
            b.classList.remove('opacity-70');
            b.innerHTML = '<span class="material-symbols-outlined text-[18px]">save</span> Simpan Produk';
        });
    }
}

function updateAllNewRowSkus() {
    variantRows.forEach((v) => {
        if (!v.has_db_sku) {
            const compObj = completenessList.find(c => c.name === v.kelengkapan || c.short === v.kelengkapan);
            v.sku = generateAutoSku(v.size, compObj?.code, thicknessMode === 'multi' ? v.tebal : null);
        }
    });
    const rows = document.querySelectorAll('tr.variant-row');
    rows.forEach(r => {
        const idx = parseInt(r.dataset.index);
        if (!isNaN(idx) && variantRows[idx] && r.dataset.hasDbSku !== '1') {
            const skuInput = r.querySelector('.v-sku');
            if (skuInput) skuInput.value = variantRows[idx].sku;
        }
    });
}

// ==================== SHIPPING & EXPEDISI LOGIC ====================
let isEditMode = {{ isset($product) && $product->id ? 'true' : 'false' }};
let isShippingOverridden = isEditMode;

function onCourierTypeChanged() {
    $('.courier-card').each(function() {
        const radio = $(this).find('input[type="radio"]');
        if (radio.is(':checked')) {
            $(this).addClass('border-primary bg-primary/5 ring-1 ring-primary/30').removeClass('border-outline-variant/60');
        } else {
            $(this).removeClass('border-primary bg-primary/5 ring-1 ring-primary/30').addClass('border-outline-variant/60');
        }
    });
}

function onShippingSchemeChanged() {
    const scheme = $('input[name="shipping_scheme"]:checked').val();
    $('.scheme-card').each(function() {
        const radio = $(this).find('input[type="radio"]');
        if (radio.is(':checked')) {
            $(this).addClass('border-primary bg-primary/5 ring-1 ring-primary/30').removeClass('border-outline-variant/60');
        } else {
            $(this).removeClass('border-primary bg-primary/5 ring-1 ring-primary/30').addClass('border-outline-variant/60');
        }
    });

    if (scheme === 'fixed') {
        $('#fixedShippingCostContainer').removeClass('hidden');
        $('#batchShippingCostWrapper').removeClass('hidden');
    } else {
        $('#fixedShippingCostContainer').addClass('hidden');
        $('#batchShippingCostWrapper').addClass('hidden');
    }

    renderVariantsTable();
}

function calculateVolumetricWeight() {
    const length = parseFloat($('#productLength').val()) || 0;
    const width = parseFloat($('#productWidth').val()) || 0;
    const height = parseFloat($('#productHeight').val()) || 0;

    if (length > 0 && width > 0 && height > 0) {
        const volWeight = (length * width * height) / 6000;
        $('#volumetricWeightText').text(volWeight.toFixed(2) + ' kg');
        $('#volumetricPreviewBadge').removeClass('hidden');
    } else {
        $('#volumetricPreviewBadge').addClass('hidden');
    }
}

function checkCategoryShipping(forceSync = false) {
    const selectedOption = $('#categorySelect').find('option:selected');
    const settingType = selectedOption.data('setting-type');
    const courierType = selectedOption.data('courier-type');

    if (!selectedOption.val()) {
        $('#categoryShippingBadge').addClass('hidden');
        $('#categoryGlobalNotice').addClass('hidden');
        return;
    }

    const courierLabels = {
        'toko': 'Pengiriman by Toko',
        'expedisi': 'Pengiriman by Expedisi',
        'keduanya': 'Keduanya (Toko & Expedisi)'
    };

    if (settingType === 'global') {
        const label = courierLabels[courierType] || 'Keduanya';
        $('#categoryShippingBadge').removeClass('hidden');
        $('#categoryShippingBadgeText').text('Global Kategori: ' + label);
        
        if (!isShippingOverridden || forceSync) {
            $('#categoryGlobalNotice').removeClass('hidden');
            $('#categoryGlobalDesc').text(`Kategori ini menerapkan kurir global (${label}). Kurir produk diselaraskan, namun Anda tetap dapat memilih opsi lain.`);
            $(`input[name="courier_type"][value="${courierType}"]`).prop('checked', true);
            onCourierTypeChanged();
            if (forceSync) {
                isShippingOverridden = false;
            }
        } else {
            $('#categoryGlobalNotice').addClass('hidden');
        }
    } else {
        $('#categoryShippingBadge').removeClass('hidden');
        $('#categoryShippingBadgeText').text('Kategori: Pengaturan Bebas per Produk');
        $('#categoryGlobalNotice').addClass('hidden');
    }
}

function enableShippingOverride() {
    isShippingOverridden = true;
    $('#categoryGlobalNotice').addClass('hidden');
}

// ==================== INITIALIZATION ====================
$(document).ready(function() {
    window.quill = new Quill('#quill-editor', {
        theme: 'snow',
        placeholder: 'Tuliskan deskripsi lengkap, spesifikasi teknis, dan informasi produk...',
        modules: {
            toolbar: [
                [{ 'header': [1, 2, 3, false] }],
                ['bold', 'italic', 'underline', 'strike'],
                [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                ['clean']
            ]
        }
    });

    $('#categorySelect').select2({
        placeholder: 'Pilih Kategori Produk',
        allowClear: true,
        width: '100%'
    }).on('change', function() {
        checkCategoryShipping(true);
    });

    $('#brandSelect').select2({
        placeholder: 'Pilih Brand / Merek',
        allowClear: true,
        width: '100%'
    });

    $('#productNameInput').on('input', function() {
        let slug = $(this).val().toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/(^-|-$)+/g, '');
        $('#productSlug').val(slug);
        updateAllNewRowSkus();
    });

    initVariantsFromBackend();
    renderGalleryImages();

    // Live sync for variant table row inputs to prevent any unsaved data
    $(document).on('input change', 'tr.variant-row input, tr.variant-row select', function() {
        const row = this.closest('tr.variant-row');
        if (!row) return;
        const gIdx = parseInt(row.dataset.index);
        const key = row.dataset.key;
        const target = (!isNaN(gIdx) && variantRows[gIdx]) ? variantRows[gIdx] : variantRows.find(r => r.key === key);
        if (!target) return;

        if (this.classList.contains('v-sell-price')) {
            const val = parseFloat(this.value);
            target.sell_price = isNaN(val) ? 0 : val;
        } else if (this.classList.contains('v-base-price')) {
            const val = parseFloat(this.value);
            target.base_price = isNaN(val) ? 0 : val;
        } else if (this.classList.contains('v-shipping-cost')) {
            const val = parseFloat(this.value);
            target.shipping_cost = isNaN(val) ? 0 : val;
        } else if (this.classList.contains('v-length')) {
            const val = parseFloat(this.value);
            target.length = isNaN(val) ? null : val;
        } else if (this.classList.contains('v-width')) {
            const val = parseFloat(this.value);
            target.width = isNaN(val) ? null : val;
        } else if (this.classList.contains('v-height')) {
            const val = parseFloat(this.value);
            target.height = isNaN(val) ? null : val;
        } else if (this.classList.contains('v-weight')) {
            const val = parseFloat(this.value);
            target.weight = isNaN(val) ? null : val;
        } else if (this.classList.contains('v-variant-name')) {
            target.variant_name = this.value;
        } else if (this.classList.contains('v-status')) {
            target.status = this.value;
        }
    });

    // Initialize shipping UI state
    calculateVolumetricWeight();
    onShippingSchemeChanged();
    onCourierTypeChanged();
    checkCategoryShipping(false);

    // Expose all interactive functions to window for global inline event handlers
    window.renderGalleryImages = renderGalleryImages;
    window.handleGalleryImagesPicked = handleGalleryImagesPicked;
    window.removeGalleryItem = removeGalleryItem;
    window.removeExistingGalleryImage = removeExistingGalleryImage;
    window.removeNewGalleryImage = removeNewGalleryImage;
    window.moveGalleryItem = moveGalleryItem;
    window.handleGalleryDragStart = handleGalleryDragStart;
    window.handleGalleryDragEnd = handleGalleryDragEnd;
    window.handleGalleryDragOver = handleGalleryDragOver;
    window.handleGalleryDragEnter = handleGalleryDragEnter;
    window.handleGalleryDragLeave = handleGalleryDragLeave;
    window.handleGalleryDrop = handleGalleryDrop;
    window.calculateVolumetricWeight = calculateVolumetricWeight;
    window.onShippingSchemeChanged = onShippingSchemeChanged;
    window.onCourierTypeChanged = onCourierTypeChanged;
    window.checkCategoryShipping = checkCategoryShipping;
    window.enableShippingOverride = enableShippingOverride;
    window.addColorOption = addColorOption;
    window.addCustomColor = addCustomColor;
    window.removeColor = removeColor;
    window.toggleSize = toggleSize;
    window.addCustomSize = addCustomSize;
    window.toggleAllStandardSizes = toggleAllStandardSizes;
    window.toggleCompletenessMode = toggleCompletenessMode;
    window.toggleCompleteness = toggleCompletenessOption;
    window.toggleCompletenessOption = toggleCompletenessOption;
    window.addCustomCompleteness = addCustomCompleteness;
    window.onCompletenessTitleChanged = onCompletenessTitleChanged;
    window.toggleThicknessSwitch = toggleThicknessSwitch;
    window.setThicknessMode = setThicknessMode;
    window.onSingleThicknessChange = onSingleThicknessChange;
    window.setSingleThicknessQuick = setSingleThicknessQuick;
    window.toggleMultiThickness = toggleMultiThickness;
    window.toggleThickness = toggleMultiThickness;
    window.addCustomThickness = addCustomThickness;
    window.applyBatchSettings = applyBatchSettings;
    window.openRowImagePicker = openRowImagePicker;
    window.removeRowImage = removeRowImage;
    window.toggleExcludeRow = toggleExcludeRow;
    window.deleteVariantRow = deleteVariantRow;
    window.resetAllVariations = resetAllVariations;
    window.submitProductForm = submitProductForm;
    window.updateAllNewRowSkus = updateAllNewRowSkus;
    window.onRowNameChanged = onRowNameChanged;
    window.onRowSkuChanged = onRowSkuChanged;
    window.applyPriceToSize = applyPriceToSize;
    window.applyShippingToSize = applyShippingToSize;
    window.applyDefaultShippingToAllVariants = applyDefaultShippingToAllVariants;
    window.toggleUkuranCard = toggleUkuranCard;
    window.toggleAllUkuranCards = toggleAllUkuranCards;
});
</script>
@endpush
