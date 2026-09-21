@extends('layouts.app')

@section('title', 'Detail Produk - ' . ($product->name ?? 'Produk'))

@section('content')
@php
    $validVariants = $product->variants ?? collect([]);
    $minSellPrice = $validVariants->where('sell_price', '>', 0)->min('sell_price') ?? 0;
    $maxSellPrice = $validVariants->where('sell_price', '>', 0)->max('sell_price') ?? $minSellPrice;
    $totalStock = $validVariants->sum('stock_quantity') ?? 0;

    if (!function_exists('formatCompleteness')) {
        function formatCompleteness($val, $variantName = '') {
            $str = strtolower(trim((string)$val . ' ' . (string)$variantName));
            if (str_contains($str, 'full') || str_contains($str, 'divan') || str_contains($str, 'set')) {
                return 'Fullset';
            }
            return 'Mattress Only';
        }
    }

    // Group variants by Ukuran Produk
    $groupedVariants = $validVariants->groupBy(function($v) {
        if (!empty($v->attributes['Ukuran'])) {
            return strtoupper(trim($v->attributes['Ukuran']));
        }
        if (preg_match('/^(\d+\s*[xX×]\s*\d+)/', $v->variant_name, $m)) {
            return strtoupper(trim($m[1]));
        }
        if (isset($v->attributes['width'], $v->attributes['length']) && $v->attributes['width'] > 0 && $v->attributes['length'] > 0) {
            return str_pad((string)$v->attributes['width'], 3, '0', STR_PAD_LEFT) . ' X ' . $v->attributes['length'];
        }
        if (($v->width ?? 0) > 0 && ($v->length ?? 0) > 0) {
            return str_pad((string)(int)$v->width, 3, '0', STR_PAD_LEFT) . ' X ' . (int)$v->length;
        }
        return $v->variant_name ?: 'Standar';
    })->sortKeys(SORT_NATURAL);

    // Shipping dimensions calculation
    $dimP = (float)($product->length ?? 0);
    $dimL = (float)($product->width ?? 0);
    $dimT = (float)($product->height ?? 0);
    $dimW = (float)($product->weight ?? 0);

    if (($dimP <= 0 || $dimL <= 0 || $dimT <= 0) && $validVariants->isNotEmpty()) {
        $vDim = $validVariants->first(function($v) {
            return ($v->length ?? 0) > 0 && ($v->width ?? 0) > 0 && ($v->height ?? 0) > 0;
        });
        if ($vDim) {
            $dimP = (float)$vDim->length;
            $dimL = (float)$vDim->width;
            $dimT = (float)$vDim->height;
        }
    }
    if ($dimW <= 0 && $validVariants->isNotEmpty()) {
        $vW = $validVariants->first(function($v) { return ($v->weight ?? 0) > 0; });
        if ($vW) {
            $dimW = (float)$vW->weight;
        }
    }
@endphp

<div class="w-full space-y-6 pb-16">
    <!-- Header Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-outline-variant/40 pb-4">
        <div class="space-y-1">
            <div class="flex items-center gap-2.5 flex-wrap">
                <h1 class="font-headline-lg text-2xl font-bold text-on-surface">
                    {{ $product->name ?? 'Detail Produk' }}
                </h1>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-mono font-bold bg-primary/10 text-primary border border-primary/20">
                    #{{ $product->code ?? 'PROD' }}
                </span>
                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold {{ ($product->status ?? 1) ? 'bg-success/10 text-success' : 'bg-danger/10 text-danger' }}">
                    <span class="w-1.5 h-1.5 rounded-full bg-current"></span> {{ ($product->status ?? 1) ? 'Aktif' : 'Nonaktif' }}
                </span>
                @if($product->best_seller ?? false)
                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-amber-50 text-amber-700 border border-amber-200">
                        <span class="material-symbols-outlined text-[14px]">star</span> Best Seller
                    </span>
                @endif
                @if($product->is_new ?? false)
                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-blue-50 text-blue-700 border border-blue-200">
                        <span class="material-symbols-outlined text-[14px]">auto_awesome</span> New
                    </span>
                @endif
                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold {{ ($product->show_on_web ?? true) ? 'bg-indigo-50 text-indigo-700 border border-indigo-200' : 'bg-surface-container-high text-on-surface-variant border border-outline-variant/60' }}">
                    <span class="material-symbols-outlined text-[14px]">public</span> {{ ($product->show_on_web ?? true) ? 'Tampil di Web' : 'Sembunyi di Web' }}
                </span>
            </div>
            <nav class="flex items-center gap-2 text-xs text-on-surface-variant">
                <a href="{{ route('dashboard') }}" class="text-primary hover:underline">Dashboard</a>
                <span class="material-symbols-outlined text-[14px]">chevron_right</span>
                <a href="{{ route('products.index') }}" class="text-primary hover:underline">Produk</a>
                <span class="material-symbols-outlined text-[14px]">chevron_right</span>
                <span class="text-on-surface font-medium">{{ $product->name ?? 'Detail' }}</span>
            </nav>
        </div>

        <div class="flex items-center gap-2.5">
            <a href="{{ route('products.index') }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 border border-outline-variant text-on-surface-variant hover:bg-surface-container rounded-xl text-xs font-semibold transition-colors">
                <span class="material-symbols-outlined text-[16px]">arrow_back</span>
                <span>Kembali</span>
            </a>
            <a href="{{ route('products.edit', $product->id) }}" class="inline-flex items-center gap-2 px-4 py-2 bg-primary text-white hover:opacity-90 rounded-xl text-xs font-bold transition-all shadow-sm active:scale-95">
                <span class="material-symbols-outlined text-[17px]">edit</span>
                <span>Edit Produk</span>
            </a>
        </div>
    </div>

    @include('layouts.partials.product-submenu')

    <!-- 1. INFORMASI PRODUK & FOTO UTAMA -->
    <div class="w-full bg-white rounded-2xl shadow-sm border border-outline-variant/30 p-6 space-y-6">
        <div class="border-b border-outline-variant/20 pb-3 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
            <div>
                <h2 class="text-base font-bold text-on-surface flex items-center gap-2">
                    <span class="material-symbols-outlined text-primary text-[22px]">description</span>
                    Informasi Produk & Foto Utama
                </h2>
                <p class="text-xs text-on-surface-variant mt-0.5">Nama produk, URL slug, kategori, brand, garansi, dimensi, dan foto katalog.</p>
            </div>
            <div class="sm:text-right bg-primary/5 sm:bg-transparent p-2 sm:p-0 rounded-xl">
                <span class="text-[10px] font-bold text-on-surface-variant uppercase tracking-wider block">Rentang Harga Jual</span>
                <span class="text-base font-bold text-primary">
                    @if($minSellPrice > 0)
                        @if($minSellPrice == $maxSellPrice)
                            Rp{{ number_format($minSellPrice, 0, ',', '.') }}
                        @else
                            Rp{{ number_format($minSellPrice, 0, ',', '.') }} - Rp{{ number_format($maxSellPrice, 0, ',', '.') }}
                        @endif
                    @else
                        -
                    @endif
                </span>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- 2 Cols on Left: Fields Summary -->
            <div class="lg:col-span-2 grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Kode Produk -->
                <div class="p-3.5 bg-surface-container-lowest border border-outline-variant/40 rounded-xl space-y-1">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-on-surface-variant">Kode Produk</span>
                    <p class="font-mono text-sm font-semibold text-primary">#{{ $product->code ?? '-' }}</p>
                </div>

                <!-- Nama Produk -->
                <div class="p-3.5 bg-surface-container-lowest border border-outline-variant/40 rounded-xl space-y-1">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-on-surface-variant">Nama Produk</span>
                    <p class="text-sm font-bold text-on-surface leading-tight">{{ $product->name ?? '-' }}</p>
                </div>

                <!-- Slug URL -->
                <div class="p-3.5 bg-surface-container-lowest border border-outline-variant/40 rounded-xl space-y-1">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-on-surface-variant">Product Slug (URL)</span>
                    <div class="flex items-center text-xs font-mono text-on-surface-variant truncate">
                        <span class="text-slate-400 select-none">/products/</span>
                        <span class="font-semibold text-on-surface truncate">{{ $product->slug ?? '-' }}</span>
                    </div>
                </div>

                <!-- Kategori -->
                <div class="p-3.5 bg-surface-container-lowest border border-outline-variant/40 rounded-xl space-y-1">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-on-surface-variant">Kategori</span>
                    <p class="text-sm font-semibold text-on-surface">{{ $product->category->name ?? '-' }}</p>
                </div>

                <!-- Brand -->
                <div class="p-3.5 bg-surface-container-lowest border border-outline-variant/40 rounded-xl space-y-1">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-on-surface-variant">Brand / Merek</span>
                    <p class="text-sm font-semibold text-on-surface">{{ $product->brand->name ?? '-' }}</p>
                </div>

                <!-- Garansi -->
                <div class="p-3.5 bg-surface-container-lowest border border-outline-variant/40 rounded-xl space-y-1">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-on-surface-variant">Durasi Garansi</span>
                    <p class="text-sm font-semibold text-on-surface flex items-center gap-1.5">
                        <span class="material-symbols-outlined text-[16px] text-amber-500">verified_user</span>
                        {{ $product->warranty_duration ?? 'Tidak Ada Garansi' }}
                    </p>
                </div>

                <!-- Dimensi & Berat Pengiriman -->
                <div class="p-3.5 bg-surface-container-lowest border border-outline-variant/40 rounded-xl space-y-1.5 md:col-span-2">
                    <div class="flex items-center justify-between">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-on-surface-variant">Dimensi & Berat (Default Pengiriman)</span>
                        <span class="text-[10px] px-2 py-0.5 bg-primary/10 text-primary font-bold rounded">Kalkulasi Ongkir</span>
                    </div>
                    <div class="flex items-center gap-3 flex-wrap text-xs text-on-surface pt-0.5">
                        <span class="flex items-center gap-1">
                            <strong class="font-medium text-on-surface-variant">P × L × T:</strong>
                            <span class="font-semibold">{{ ($dimP > 0 || $dimL > 0 || $dimT > 0) ? "{$dimP} × {$dimL} × {$dimT} cm" : 'Sesuai Varian' }}</span>
                        </span>
                        <span class="text-outline-variant">|</span>
                        <span class="flex items-center gap-1">
                            <strong class="font-medium text-on-surface-variant">Berat:</strong>
                            <span class="font-semibold">{{ $dimW > 0 ? "{$dimW} kg" : 'Sesuai Varian' }}</span>
                        </span>
                        <span class="text-outline-variant">|</span>
                        <span class="flex items-center gap-1">
                            <strong class="font-medium text-on-surface-variant">Total Stok:</strong>
                            <span class="font-bold text-primary">{{ number_format($totalStock, 0, ',', '.') }} unit</span>
                        </span>
                    </div>
                </div>
            </div>

            <!-- 1 Col on Right: Foto Utama & Galeri -->
            <div class="space-y-3 flex flex-col items-center justify-center">
                @php
                    $mainImgSrc = $product->thumbnail_url ?: ($product->images->first()?->url ?: null);
                    $allImages = collect([]);
                    if ($product->thumbnail_url) {
                        $allImages->push((object)['url' => $product->thumbnail_url, 'label' => 'Thumbnail']);
                    }
                    if ($product->images) {
                        foreach ($product->images as $img) {
                            if ($img->url && (!$product->thumbnail_url || $img->url !== $product->thumbnail_url)) {
                                $allImages->push((object)['url' => $img->url, 'label' => $img->alt_text ?? 'Foto Produk']);
                            }
                        }
                    }
                @endphp

                <div class="w-full max-w-[260px] aspect-square rounded-2xl border border-outline-variant/60 bg-surface-container-lowest overflow-hidden flex items-center justify-center p-2 shadow-2xs group relative">
                    @if($mainImgSrc)
                        <img id="detailMainImage" src="{{ $mainImgSrc }}" alt="{{ $product->name }}" class="w-full h-full object-contain rounded-xl transition-transform duration-300 group-hover:scale-105">
                    @else
                        <div class="flex flex-col items-center justify-center text-on-surface-variant/40">
                            <span class="material-symbols-outlined text-[44px]">image_not_supported</span>
                            <span class="text-xs mt-1 font-medium">Belum ada foto</span>
                        </div>
                    @endif
                </div>

                @if($allImages->count() > 1)
                    <div class="flex items-center justify-center gap-2 overflow-x-auto max-w-full py-1">
                        @foreach($allImages as $img)
                            <button type="button" onclick="switchProductPreview('{{ $img->url }}', this)" class="gallery-thumb-btn w-12 h-12 rounded-xl border-2 {{ $loop->first ? 'border-primary ring-2 ring-primary/20' : 'border-outline-variant/60 opacity-70' }} hover:opacity-100 hover:border-primary overflow-hidden shrink-0 transition-all">
                                <img src="{{ $img->url }}" alt="{{ $img->label }}" class="w-full h-full object-cover">
                            </button>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        <!-- Deskripsi Singkat & Lengkap -->
        @if(!empty($product->short_description) || !empty($product->description))
            <div class="pt-4 border-t border-outline-variant/20 space-y-4">
                @if(!empty($product->short_description))
                    <div class="space-y-1">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-on-surface-variant block">Ringkasan Singkat (Short Description)</span>
                        <div class="text-xs text-on-surface leading-relaxed p-3.5 bg-surface-container-lowest rounded-xl border border-outline-variant/40">
                            {{ $product->short_description }}
                        </div>
                    </div>
                @endif

                @if(!empty($product->description))
                    <div class="space-y-1">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-on-surface-variant block">Deskripsi Lengkap Produk</span>
                        <div class="text-xs text-on-surface leading-relaxed p-4 bg-surface-container-lowest rounded-xl border border-outline-variant/40 prose prose-sm max-w-none">
                            {!! $product->description !!}
                        </div>
                    </div>
                @endif
            </div>
        @endif
    </div>

    <!-- 2. PENGATURAN KURIR & SKEMA ONGKOS KIRIM -->
    @php
        $volumetricWeight = ($dimP > 0 && $dimL > 0 && $dimT > 0) ? round(($dimP * $dimL * $dimT) / 6000, 2) : 0;
        $shippingScheme = $product->shipping_scheme ?? 'dimension';
        $courierType = $product->courier_type ?? 'keduanya';
        $shippingCost = (float)($product->shipping_cost ?? 0);
        $categorySetting = $product->category?->courier_setting_type ?? 'detail';

        $variantShippingCosts = $product->variants->pluck('shipping_cost')->filter(fn($c) => $c !== null)->map(fn($c) => (float)$c);
        $minVariantShipping = $variantShippingCosts->isNotEmpty() ? $variantShippingCosts->min() : $shippingCost;
        $maxVariantShipping = $variantShippingCosts->isNotEmpty() ? $variantShippingCosts->max() : $shippingCost;
    @endphp
    <div class="w-full bg-white rounded-2xl shadow-sm border border-outline-variant/30 p-6 space-y-5">
        <div class="border-b border-outline-variant/20 pb-3 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
            <div class="space-y-0.5">
                <h2 class="text-base font-bold text-on-surface flex items-center gap-2">
                    <span class="material-symbols-outlined text-primary text-[22px]">local_shipping</span>
                    Pengaturan Kurir & Skema Ongkos Kirim
                </h2>
                <p class="text-xs text-on-surface-variant">Metode kurir yang didukung dan perhitungan ongkos kirim pengiriman barang.</p>
            </div>
            @if($categorySetting === 'global')
                <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-blue-50 text-blue-700 border border-blue-200 text-xs font-semibold rounded-xl shrink-0">
                    <span class="material-symbols-outlined text-[15px]">info</span>
                    Kategori Global ({{ $product->category->courier_type_label ?? 'Keduanya' }})
                </span>
            @endif
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <!-- Tipe Kurir Card -->
            <div class="p-4 bg-surface-container-lowest border border-outline-variant/40 rounded-xl space-y-2">
                <span class="text-[10px] font-bold uppercase tracking-wider text-on-surface-variant block">Tipe Kurir Pengiriman</span>
                <div class="flex items-center gap-3">
                    @if($courierType === 'toko')
                        <div class="w-10 h-10 rounded-xl bg-blue-100 text-blue-700 flex items-center justify-center shrink-0">
                            <span class="material-symbols-outlined text-[22px]">store</span>
                        </div>
                        <div>
                            <p class="text-sm font-bold text-on-surface">Pengiriman by Toko</p>
                            <p class="text-[11px] text-on-surface-variant">Dikirim menggunakan armada / kurir internal toko</p>
                        </div>
                    @elseif($courierType === 'expedisi')
                        <div class="w-10 h-10 rounded-xl bg-amber-100 text-amber-800 flex items-center justify-center shrink-0">
                            <span class="material-symbols-outlined text-[22px]">local_shipping</span>
                        </div>
                        <div>
                            <p class="text-sm font-bold text-on-surface">Pengiriman by Expedisi</p>
                            <p class="text-[11px] text-on-surface-variant">Logistik ekspedisi kargo / pihak ketiga</p>
                        </div>
                    @else
                        <div class="w-10 h-10 rounded-xl bg-emerald-100 text-emerald-800 flex items-center justify-center shrink-0">
                            <span class="material-symbols-outlined text-[22px]">sync_alt</span>
                        </div>
                        <div>
                            <p class="text-sm font-bold text-on-surface">Keduanya (Toko & Expedisi)</p>
                            <p class="text-[11px] text-on-surface-variant">Pembeli dapat memilih kurir toko atau ekspedisi</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Skema Ongkir Card -->
            <div class="p-4 bg-surface-container-lowest border border-outline-variant/40 rounded-xl space-y-2">
                <span class="text-[10px] font-bold uppercase tracking-wider text-on-surface-variant block">Skema Ongkos Kirim</span>
                @if($shippingScheme === 'fixed')
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-amber-100 text-amber-800 flex items-center justify-center shrink-0">
                            <span class="material-symbols-outlined text-[22px]">payments</span>
                        </div>
                        <div>
                            <div class="flex items-center gap-2">
                                <p class="text-sm font-bold text-on-surface">Ongkos Kirim Tetap (Per Varian)</p>
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-900 border border-amber-300">Flat Rate</span>
                            </div>
                            <div class="flex items-baseline gap-1.5 mt-0.5">
                                <p class="text-sm font-bold text-amber-900 font-mono">
                                    @if($minVariantShipping == $maxVariantShipping)
                                        Rp{{ number_format($minVariantShipping, 0, ',', '.') }}
                                    @else
                                        Rp{{ number_format($minVariantShipping, 0, ',', '.') }} - Rp{{ number_format($maxVariantShipping, 0, ',', '.') }}
                                    @endif
                                </p>
                                <span class="text-[11px] text-on-surface-variant font-medium">(Sesuai ukuran / variasi)</span>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-indigo-100 text-indigo-800 flex items-center justify-center shrink-0">
                            <span class="material-symbols-outlined text-[22px]">straighten</span>
                        </div>
                        <div>
                            <div class="flex items-center gap-2">
                                <p class="text-sm font-bold text-on-surface">Hitung Berdasarkan Dimensi & Berat</p>
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-indigo-50 text-indigo-700 border border-indigo-200">Kalkulasi Otomatis</span>
                            </div>
                            <p class="text-[11px] text-on-surface-variant mt-0.5">
                                Dihitung real-time dari volume (P × L × T) dan berat aktual produk sesuai tarif ekspedisi.
                            </p>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <!-- Dimensi & Volumetrik Box -->
        <div class="p-4 bg-surface-container-lowest/70 border border-outline-variant/40 rounded-xl">
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 text-xs">
                <div class="space-y-1">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-on-surface-variant block">Panjang (P)</span>
                    <span class="font-bold text-sm text-on-surface">{{ $dimP > 0 ? "{$dimP} cm" : '-' }}</span>
                </div>
                <div class="space-y-1">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-on-surface-variant block">Lebar (L)</span>
                    <span class="font-bold text-sm text-on-surface">{{ $dimL > 0 ? "{$dimL} cm" : '-' }}</span>
                </div>
                <div class="space-y-1">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-on-surface-variant block">Tinggi / Tebal (T)</span>
                    <span class="font-bold text-sm text-on-surface">{{ $dimT > 0 ? "{$dimT} cm" : '-' }}</span>
                </div>
                <div class="space-y-1">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-on-surface-variant block">Berat Aktual</span>
                    <span class="font-bold text-sm text-on-surface">{{ $dimW > 0 ? "{$dimW} kg" : '-' }}</span>
                </div>
            </div>
            @if($volumetricWeight > 0)
                <div class="mt-3 pt-3 border-t border-outline-variant/30 flex items-center justify-between flex-wrap gap-2 text-xs">
                    <div class="flex items-center gap-1.5 text-on-surface-variant text-[11px]">
                        <span class="material-symbols-outlined text-[15px] text-primary">scale</span>
                        <span>Estimasi Berat Volumetrik Kargo (P × L × T / 6000):</span>
                    </div>
                    <span class="font-mono font-bold text-primary bg-primary/10 px-2.5 py-0.5 rounded-lg border border-primary/20">
                        {{ $volumetricWeight }} kg
                    </span>
                </div>
            @endif
        </div>
    </div>

    <!-- 3. PILIHAN WARNA PRODUK (PRODUCT COLORS) -->
    <div class="w-full bg-white rounded-2xl shadow-sm border border-outline-variant/30 p-6 space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-outline-variant/20 pb-3">
            <div class="flex items-center gap-2">
                <span class="material-symbols-outlined text-emerald-600 text-[22px]">palette</span>
                <div>
                    <h3 class="text-sm font-bold text-on-surface uppercase tracking-wider">
                        Pilihan Warna Produk (Kain / Matras)
                    </h3>
                    <p class="text-xs text-on-surface-variant">
                        Opsi swatch warna katalog di POS / Web. Warna tidak membludak di tabel SKU ataupun mengubah harga.
                    </p>
                </div>
            </div>
            <span class="px-3 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200/60 self-start sm:self-auto">
                {{ $product->colors->count() }} Pilihan Warna
            </span>
        </div>

        @if($product->colors->count() > 0)
            <div class="flex flex-wrap items-center gap-2.5 p-3.5 bg-surface-container-lowest/70 rounded-xl border border-outline-variant/40">
                @foreach($product->colors as $color)
                    <div class="inline-flex items-center gap-2 px-3 py-2 bg-white rounded-xl border border-outline-variant/60 shadow-2xs hover:border-emerald-500/50 transition-all">
                        <span class="w-4 h-4 rounded-full border border-black/20 shadow-inner shrink-0" style="background-color: {{ $color->color_code }};"></span>
                        <span class="text-xs font-bold text-on-surface">{{ $color->color_name }}</span>
                        <span class="text-[10px] font-mono text-on-surface-variant/80">{{ $color->color_code }}</span>
                    </div>
                @endforeach
            </div>
        @else
            <div class="text-center py-6 border border-dashed border-outline-variant/50 rounded-xl bg-surface-container-lowest/40">
                <span class="material-symbols-outlined text-on-surface-variant/40 text-[28px]">palette</span>
                <p class="text-xs font-medium text-on-surface-variant mt-1">Tidak ada pilihan warna khusus (menggunakan warna standar produk).</p>
            </div>
        @endif
    </div>

    <!-- 4. VARIASI PRODUK & HARGA PER UKURAN PRODUK -->
    <div class="w-full bg-white rounded-2xl shadow-sm border border-outline-variant/30 p-6 space-y-6">
        <div class="border-b border-outline-variant/20 pb-4 flex flex-col md:flex-row md:items-center justify-between gap-3">
            <div class="space-y-1">
                <h2 class="text-base font-bold text-on-surface flex items-center gap-2">
                    <span class="material-symbols-outlined text-primary text-[22px]">layers</span>
                    Variasi Produk & Harga
                </h2>
                <p class="text-xs text-on-surface-variant">
                    Dikelompokkan berdasarkan ukuran produk untuk kemudahan pengecekan harga, kelengkapan, dan ketebalan/tinggi.
                </p>
            </div>
            
            <div class="flex items-center gap-2.5 flex-wrap">
                <!-- Search Input -->
                <div class="relative">
                    <span class="material-symbols-outlined absolute left-3 top-2 text-[16px] text-on-surface-variant">search</span>
                    <input type="text" id="variantSearchInput" oninput="filterSizeCards(this.value)" placeholder="Cari ukuran, SKU, tebal..." class="pl-8 pr-3 py-1.5 bg-surface-container-lowest border border-outline-variant rounded-xl text-xs focus:ring-2 focus:ring-primary/20 focus:outline-none w-48 sm:w-56">
                </div>

                <!-- Toggle All Button -->
                <button type="button" onclick="toggleAllSizes()" id="btnToggleAll" class="px-3 py-1.5 border border-outline-variant text-on-surface-variant hover:bg-surface-container rounded-xl text-xs font-semibold flex items-center gap-1.5 transition-colors">
                    <span class="material-symbols-outlined text-[16px]" id="iconToggleAll">unfold_more</span>
                    <span id="labelToggleAll">Buka / Tutup Semua</span>
                </button>

                <span class="px-3 py-1.5 rounded-xl text-xs font-bold bg-primary/10 text-primary border border-primary/20">
                    {{ $validVariants->count() }} Variasi ({{ $groupedVariants->count() }} Ukuran)
                </span>
            </div>
        </div>

        @if($groupedVariants->isEmpty())
            <div class="text-center py-10 border border-dashed border-outline-variant/50 rounded-2xl bg-surface-container-lowest/40 space-y-2">
                <span class="material-symbols-outlined text-on-surface-variant/40 text-[40px]">inventory_2</span>
                <p class="text-xs font-medium text-on-surface-variant">Belum ada data variasi produk yang tersimpan.</p>
                <a href="{{ route('products.edit', $product->id) }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-primary text-white rounded-xl text-xs font-bold hover:opacity-90 transition-all">
                    <span class="material-symbols-outlined text-[16px]">add</span> Tambah Variasi
                </a>
            </div>
        @else
            <div class="space-y-3" id="sizesAccordionContainer">
                @foreach($groupedVariants as $sizeName => $sizeVariants)
                    @php
                        $sizeMinPrice = $sizeVariants->where('sell_price', '>', 0)->min('sell_price') ?? 0;
                        $sizeMaxPrice = $sizeVariants->where('sell_price', '>', 0)->max('sell_price') ?? $sizeMinPrice;
                        $sizeStock = $sizeVariants->sum('stock_quantity') ?? 0;
                        $cardId = 'size-card-' . $loop->index;
                        $bodyId = 'size-body-' . $loop->index;
                        $chevronId = 'size-chevron-' . $loop->index;
                    @endphp
                    <div class="size-group-card border border-outline-variant/50 rounded-2xl overflow-hidden bg-white shadow-2xs transition-all hover:border-primary/40" id="{{ $cardId }}" data-size="{{ strtolower($sizeName) }}" data-keywords="{{ strtolower($sizeName . ' ' . $sizeVariants->pluck('sku')->implode(' ') . ' ' . $sizeVariants->pluck('variant_name')->implode(' ')) }}">
                        <!-- Accordion Header -->
                        <div onclick="toggleSizeCard('{{ $bodyId }}', '{{ $chevronId }}')" class="cursor-pointer bg-surface-container-lowest/90 hover:bg-surface-container-low/70 px-4 py-3.5 flex items-center justify-between gap-3 select-none transition-colors border-b border-transparent">
                            <div class="flex items-center gap-3 flex-wrap">
                                <span class="w-8 h-8 rounded-xl bg-primary/10 text-primary flex items-center justify-center shrink-0">
                                    <span class="material-symbols-outlined text-[18px]">bed</span>
                                </span>
                                <div>
                                    <span class="text-sm font-bold text-on-surface block sm:inline mr-2">{{ $sizeName }}</span>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-bold bg-slate-100 text-slate-700 border border-slate-200">
                                        {{ $sizeVariants->count() }} Variasi
                                    </span>
                                </div>
                            </div>

                            <div class="flex items-center gap-3 shrink-0">
                                <div class="text-right hidden sm:block">
                                    <span class="text-xs font-bold text-primary block">
                                        @if($sizeMinPrice > 0)
                                            @if($sizeMinPrice == $sizeMaxPrice)
                                                Rp{{ number_format($sizeMinPrice, 0, ',', '.') }}
                                            @else
                                                Rp{{ number_format($sizeMinPrice, 0, ',', '.') }} - Rp{{ number_format($sizeMaxPrice, 0, ',', '.') }}
                                            @endif
                                        @else
                                            Rp 0
                                        @endif
                                    </span>
                                    <span class="text-[10px] text-on-surface-variant font-medium">Stok: {{ number_format($sizeStock, 0, ',', '.') }} unit</span>
                                </div>

                                <span class="material-symbols-outlined text-[20px] text-on-surface-variant transition-transform duration-200" id="{{ $chevronId }}">
                                    expand_more
                                </span>
                            </div>
                        </div>

                        <!-- Accordion Body (Collapsible Table) -->
                        <div id="{{ $bodyId }}" class="size-group-body overflow-x-auto border-t border-outline-variant/30">
                            <table class="w-full text-left text-xs border-collapse">
                                <thead>
                                    <tr class="bg-surface-container-lowest/60 text-on-surface-variant uppercase text-[10px] font-bold tracking-wider border-b border-outline-variant/30">
                                        <th class="py-2.5 px-3 w-10 text-center">#</th>
                                        <th class="py-2.5 px-3">Kelengkapan</th>
                                        <th class="py-2.5 px-3">Tebal</th>
                                        <th class="py-2.5 px-3">SKU</th>
                                        <th class="py-2.5 px-3">Dimensi & Berat</th>
                                        <th class="py-2.5 px-3 text-right">Harga Modal</th>
                                        <th class="py-2.5 px-3 text-right">Harga Jual</th>
                                        @if($shippingScheme === 'fixed')
                                            <th class="py-2.5 px-3 text-right bg-amber-50/50 text-amber-900 border-l border-r border-amber-200/50">Ongkir Tetap</th>
                                        @endif
                                        <th class="py-2.5 px-3 text-center">Stok</th>
                                        <th class="py-2.5 px-3 text-center">Status</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-outline-variant/20">
                                    @foreach($sizeVariants as $idx => $v)
                                        @php
                                            $rawAttrs = $v->attributes ?? [];
                                            $compType = formatCompleteness($rawAttrs['Kelengkapan'] ?? '', $v->variant_name);
                                            $thText = !empty($rawAttrs['Ketebalan']) ? $rawAttrs['Ketebalan'] : (($v->height ?? 0) > 0 ? $v->height . ' cm' : '-');
                                            $dimStr = (($v->length ?? 0) > 0 || ($v->width ?? 0) > 0 || ($v->height ?? 0) > 0)
                                                ? ($v->length ?? '-') . ' × ' . ($v->width ?? '-') . ' × ' . ($v->height ?? '-') . ' cm'
                                                : '-';
                                            $weightStr = ($v->weight ?? 0) > 0 ? $v->weight . ' kg' : null;
                                        @endphp
                                        <tr class="hover:bg-surface-container-lowest/90 transition-colors variant-data-row" data-row-keyword="{{ strtolower($compType . ' ' . $thText . ' ' . ($v->sku ?? '') . ' ' . ($v->variant_name ?? '')) }}">
                                            <td class="py-2.5 px-3 text-center text-on-surface-variant font-mono text-[11px]">{{ $idx + 1 }}</td>
                                            
                                            <!-- Kelengkapan (Mattress Only vs Fullset) -->
                                            <td class="py-2.5 px-3">
                                                @if($compType === 'Fullset')
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded-lg text-[11px] font-bold bg-purple-50 text-purple-700 border border-purple-200">
                                                        Fullset
                                                    </span>
                                                @else
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded-lg text-[11px] font-bold bg-sky-50 text-sky-700 border border-sky-200">
                                                        Mattress Only
                                                    </span>
                                                @endif
                                            </td>

                                            <!-- Tebal -->
                                            <td class="py-2.5 px-3 font-semibold text-on-surface">
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-medium bg-slate-100 text-slate-700 border border-slate-200">
                                                    {{ $thText }}
                                                </span>
                                            </td>

                                            <!-- SKU -->
                                            <td class="py-2.5 px-3">
                                                @if(!empty($v->sku))
                                                    <code class="text-[11px] font-mono font-medium text-slate-800 bg-slate-100 px-2 py-0.5 rounded select-all border border-slate-200/60">
                                                        {{ $v->sku }}
                                                    </code>
                                                @else
                                                    <span class="text-on-surface-variant/50">-</span>
                                                @endif
                                            </td>

                                            <!-- Dimensi & Berat -->
                                            <td class="py-2.5 px-3 text-on-surface-variant text-[11px]">
                                                <span>{{ $dimStr }}</span>
                                                @if($weightStr)
                                                    <span class="text-slate-400">|</span>
                                                    <span class="font-medium text-on-surface">{{ $weightStr }}</span>
                                                @endif
                                            </td>

                                            <!-- Harga Modal -->
                                            <td class="py-2.5 px-3 text-right text-on-surface-variant font-mono">
                                                Rp{{ number_format($v->base_price ?? 0, 0, ',', '.') }}
                                            </td>

                                            <!-- Harga Jual -->
                                            <td class="py-2.5 px-3 text-right">
                                                <span class="font-bold text-primary font-mono text-xs">
                                                    Rp{{ number_format($v->sell_price ?? $v->base_price ?? 0, 0, ',', '.') }}
                                                </span>
                                            </td>

                                            @if($shippingScheme === 'fixed')
                                                <!-- Ongkir Tetap -->
                                                <td class="py-2.5 px-3 text-right bg-amber-50/20 border-l border-r border-amber-200/40">
                                                    <span class="font-bold text-amber-900 font-mono text-xs">
                                                        Rp{{ number_format($v->shipping_cost ?? $shippingCost ?? 0, 0, ',', '.') }}
                                                    </span>
                                                </td>
                                            @endif

                                            <!-- Stok -->
                                            <td class="py-2.5 px-3 text-center">
                                                <span class="font-bold {{ ($v->stock_quantity ?? 0) > 0 ? 'text-on-surface' : 'text-danger' }}">
                                                    {{ number_format($v->stock_quantity ?? 0, 0, ',', '.') }}
                                                </span>
                                            </td>

                                            <!-- Status -->
                                            <td class="py-2.5 px-3 text-center">
                                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-semibold {{ ($v->status ?? 1) ? 'bg-success/10 text-success' : 'bg-danger/10 text-danger' }}">
                                                    <span class="w-1 h-1 rounded-full bg-current"></span>
                                                    {{ ($v->status ?? 1) ? 'Aktif' : 'Nonaktif' }}
                                                </span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endforeach
            </div>
            <div id="noVariantFoundMsg" class="hidden text-center py-8 border border-dashed border-outline-variant/50 rounded-2xl bg-surface-container-lowest/40 space-y-1">
                <span class="material-symbols-outlined text-on-surface-variant/40 text-[32px]">search_off</span>
                <p class="text-xs font-semibold text-on-surface">Tidak ada variasi yang cocok dengan pencarian.</p>
                <p class="text-[11px] text-on-surface-variant">Coba gunakan kata kunci lain (misal: "160", "20 cm", "Fullset", dll).</p>
            </div>
        @endif
    </div>

    <!-- 5. SEGMEN PRODUK (IF APPLICABLE) -->
    @if(!empty($product->segments) && count(array_filter($product->segments, fn($v) => !empty($v))) > 0)
        <div class="w-full bg-white rounded-2xl shadow-sm border border-outline-variant/30 p-6 space-y-4">
            <div class="border-b border-outline-variant/20 pb-3 flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-bold text-on-surface uppercase tracking-wider flex items-center gap-2">
                        <span class="material-symbols-outlined text-primary text-[20px]">category</span>
                        Segmen Produk
                    </h3>
                    <p class="text-xs text-on-surface-variant mt-0.5">Klasifikasi segmentasi pasar atau target konsumen produk.</p>
                </div>
                <span class="px-3 py-1 rounded-full text-xs font-bold bg-primary/10 text-primary border border-primary/20">
                    {{ count(array_filter($product->segments, fn($v) => !empty($v))) }} Segmen
                </span>
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3">
                @foreach($product->segments as $key => $value)
                    @if(!empty($value))
                        @php
                            $segTitle = is_numeric($key) ? 'Segmen ' . $key : \Illuminate\Support\Str::of($key)->replace(['_', '-'], ' ')->title();
                        @endphp
                        <div class="p-3 bg-surface-container-lowest border border-outline-variant/40 rounded-xl space-y-1">
                            <span class="text-[10px] font-bold uppercase tracking-wider text-on-surface-variant block">{{ $segTitle }}</span>
                            <p class="text-xs font-semibold text-on-surface">{{ $value }}</p>
                        </div>
                    @endif
                @endforeach
            </div>
        </div>
    @endif
</div>

@push('scripts')
<script>
    // Switch preview in gallery
    function switchProductPreview(imgSrc, btnEl) {
        const mainImg = document.getElementById('detailMainImage');
        if (mainImg && imgSrc) {
            mainImg.src = imgSrc;
        }
        document.querySelectorAll('.gallery-thumb-btn').forEach(btn => {
            btn.classList.remove('border-primary', 'ring-2', 'ring-primary/20');
            btn.classList.add('border-outline-variant/60', 'opacity-70');
        });
        if (btnEl) {
            btnEl.classList.remove('border-outline-variant/60', 'opacity-70');
            btnEl.classList.add('border-primary', 'ring-2', 'ring-primary/20');
        }
    }

    // Toggle individual size accordion card
    function toggleSizeCard(bodyId, chevronId) {
        const body = document.getElementById(bodyId);
        const chevron = document.getElementById(chevronId);
        if (!body) return;

        if (body.classList.contains('hidden')) {
            body.classList.remove('hidden');
            if (chevron) chevron.style.transform = 'rotate(0deg)';
        } else {
            body.classList.add('hidden');
            if (chevron) chevron.style.transform = 'rotate(-90deg)';
        }
    }

    // Toggle all size cards
    let allExpanded = true;
    function toggleAllSizes() {
        const bodies = document.querySelectorAll('.size-group-body');
        const chevrons = document.querySelectorAll('[id^="size-chevron-"]');
        const icon = document.getElementById('iconToggleAll');

        allExpanded = !allExpanded;

        bodies.forEach(b => {
            if (allExpanded) {
                b.classList.remove('hidden');
            } else {
                b.classList.add('hidden');
            }
        });

        chevrons.forEach(c => {
            c.style.transform = allExpanded ? 'rotate(0deg)' : 'rotate(-90deg)';
        });

        if (icon) {
            icon.textContent = allExpanded ? 'unfold_less' : 'unfold_more';
        }
    }

    // Realtime filter size cards and rows
    function filterSizeCards(term) {
        term = (term || '').toLowerCase().trim();
        const cards = document.querySelectorAll('.size-group-card');
        const noFoundMsg = document.getElementById('noVariantFoundMsg');
        let visibleCount = 0;

        cards.forEach(card => {
            const keywords = card.getAttribute('data-keywords') || '';
            const rows = card.querySelectorAll('.variant-data-row');

            if (!term) {
                card.classList.remove('hidden');
                rows.forEach(r => r.classList.remove('hidden'));
                visibleCount++;
                return;
            }

            if (keywords.includes(term)) {
                card.classList.remove('hidden');
                visibleCount++;
                // Check individual rows within this matched card
                let hasRowMatch = false;
                rows.forEach(r => {
                    const rowKw = r.getAttribute('data-row-keyword') || '';
                    if (rowKw.includes(term) || card.getAttribute('data-size').includes(term)) {
                        r.classList.remove('hidden');
                        hasRowMatch = true;
                    } else {
                        r.classList.add('hidden');
                    }
                });
                // Ensure card body is expanded so matches are visible
                const body = card.querySelector('.size-group-body');
                if (body) body.classList.remove('hidden');
            } else {
                card.classList.add('hidden');
            }
        });

        if (noFoundMsg) {
            if (visibleCount === 0 && term !== '') {
                noFoundMsg.classList.remove('hidden');
            } else {
                noFoundMsg.classList.add('hidden');
            }
        }
    }
</script>
@endpush
@endsection