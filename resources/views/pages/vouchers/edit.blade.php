@extends('layouts.app')

@section('title', 'Edit Voucher')

@section('content')
<div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
    <div>
        <h1 class="font-headline-lg text-headline-lg text-on-surface">Edit Voucher</h1>
        <nav class="flex items-center gap-2 text-body-md text-on-surface-variant mt-1">
            <a href="{{ route('dashboard') }}" class="text-primary hover:underline">Dashboard</a>
            <span class="material-symbols-outlined text-[16px]">chevron_right</span>
            <a href="{{ route('vouchers.index') }}" class="text-primary hover:underline">Vouchers</a>
            <span class="material-symbols-outlined text-[16px]">chevron_right</span>
            <span>Edit</span>
        </nav>
    </div>
    <a href="{{ route('vouchers.index') }}" class="flex items-center gap-2 px-4 py-2 bg-surface-container-lowest border border-outline-variant text-secondary rounded-lg font-label-md hover:bg-surface-container transition-all">
        <span class="material-symbols-outlined text-[18px]">arrow_back</span> Back
    </a>
</div>

@include('layouts.partials.promotions-submenu')

@php
    $selectedCustomerIds = old('customer_ids', $voucher->customers->pluck('id')->toArray());
    $selectedCategoryIds = old('category_ids', $voucher->categories->pluck('id')->toArray());
    $selectedProductIds = old('product_ids', $voucher->products->pluck('id')->toArray());
    $selectedBrandIds = old('brand_ids', $voucher->brands->pluck('id')->toArray());
    $selectedCustomerGroupIds = old('customer_group_ids', $voucher->customerGroups->pluck('id')->toArray());
@endphp

<div class="bg-white rounded-xl shadow-sm border border-outline-variant/30">
    <div class="p-6">
        <form id="voucherForm" method="POST" action="{{ route('vouchers.update', $voucher->id) }}">
            @csrf
            @method('PUT')
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="space-y-1.5">
                    <label class="block text-label-sm font-medium text-on-surface-variant">Voucher Code <span class="text-danger">*</span> <span class="inline-flex items-center cursor-help text-on-surface-variant relative group"><span class="material-symbols-outlined text-[18px]">info</span><span class="absolute right-0 top-full mt-2 w-80 bg-surface-container-highest rounded-lg shadow-lg border border-outline-variant p-4 text-body-xs text-on-surface-variant hidden group-hover:block z-50">Kode unik voucher yang akan digunakan oleh customer saat checkout transaksi.</span></span></label>
                    <input type="text" name="code" class="w-full h-11 px-3.5 py-2.5 border border-outline-variant rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary focus:outline-none uppercase font-mono text-sm" placeholder="e.g., SUMMER2024" value="{{ old('code', $voucher->code) }}" required>
                    @error('code')<p class="text-danger text-sm">{{ $message }}</p>@enderror
                </div>
                <div class="space-y-1.5">
                    <label class="block text-label-sm font-medium text-on-surface-variant">Title <span class="inline-flex items-center cursor-help text-on-surface-variant relative group"><span class="material-symbols-outlined text-[18px]">info</span><span class="absolute right-0 top-full mt-2 w-80 bg-surface-container-highest rounded-lg shadow-lg border border-outline-variant p-4 text-body-xs text-on-surface-variant hidden group-hover:block z-50">Nama/internal label voucher untuk keperluan admin.</span></span></label>
                    <input type="text" name="title" class="w-full h-11 px-3.5 py-2.5 border border-outline-variant rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary focus:outline-none text-sm" placeholder="Voucher title" value="{{ old('title', $voucher->title) }}">
                    @error('title')<p class="text-danger text-sm">{{ $message }}</p>@enderror
                </div>
            </div>

            <div class="space-y-1.5 mt-4">
                <label class="block text-label-sm font-medium text-on-surface-variant">Description <span class="inline-flex items-center cursor-help text-on-surface-variant relative group"><span class="material-symbols-outlined text-[18px]">info</span><span class="absolute right-0 top-full mt-2 w-80 bg-surface-container-highest rounded-lg shadow-lg border border-outline-variant p-4 text-body-xs text-on-surface-variant hidden group-hover:block z-50">Keterangan detail syarat & ketentuan penggunaan voucher. Bisa ditampilkan di halaman checkout.</span></span></label>
                <textarea name="description" rows="2" class="w-full px-3.5 py-2.5 border border-outline-variant rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary focus:outline-none text-sm" placeholder="Voucher description">{{ old('description', $voucher->description) }}</textarea>
            </div>

            <!-- Discount Type & Value -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                <div class="space-y-1.5">
                    <label class="block text-label-sm font-medium text-on-surface-variant">Discount Type <span class="text-danger">*</span> <span class="inline-flex items-center cursor-help text-on-surface-variant relative group"><span class="material-symbols-outlined text-[18px]">info</span><span class="absolute right-0 top-full mt-2 w-80 bg-surface-container-highest rounded-lg shadow-lg border border-outline-variant p-4 text-body-xs text-on-surface-variant hidden group-hover:block z-50">Tipe diskon yang diberikan: persentase (%), nominal tetap (Rp), potongan ongkir (Rp), atau bonus produk (pcs).</span></span></label>
                    <select name="type" id="typeSelect" class="w-full h-11 px-3.5 py-2.5 bg-white border border-outline-variant rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary focus:outline-none text-sm cursor-pointer">
                        <option value="1" {{ old('type', $voucher->type) == 1 ? 'selected' : '' }}>Percentage (%)</option>
                        <option value="2" {{ old('type', $voucher->type) == 2 ? 'selected' : '' }}>Fixed Amount (Rp)</option>
                        <option value="3" {{ old('type', $voucher->type) == 3 ? 'selected' : '' }}>Shipping Discount (Rp)</option>
                        <option value="4" {{ old('type', $voucher->type) == 4 ? 'selected' : '' }}>Bonus Product (pcs)</option>
                    </select>
                </div>
                <div class="space-y-1.5">
                    <label class="block text-label-sm font-medium text-on-surface-variant">Value <span class="text-danger">*</span> <span class="inline-flex items-center cursor-help text-on-surface-variant relative group"><span class="material-symbols-outlined text-[18px]">info</span><span class="absolute right-0 top-full mt-2 w-80 bg-surface-container-highest rounded-lg shadow-lg border border-outline-variant p-4 text-body-xs text-on-surface-variant hidden group-hover:block z-50">Nilai diskon sesuai tipe yang dipilih: masukkan persentase (%), nominal Rp, biaya ongkir, atau jumlah pcs bonus.</span></span></label>
                    <input type="number" name="value" step="0.01" class="w-full h-11 px-3.5 py-2.5 border border-outline-variant rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary focus:outline-none text-sm" placeholder="0" value="{{ old('value', $voucher->value) }}" required>
                    @error('value')<p class="text-danger text-sm">{{ $message }}</p>@enderror
                </div>
            </div>

            <!-- Min Purchase & Max Discount -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                <div class="space-y-1.5">
                    <label class="block text-label-sm font-medium text-on-surface-variant">Min Purchase (Rp) <span class="inline-flex items-center cursor-help text-on-surface-variant relative group"><span class="material-symbols-outlined text-[18px]">info</span><span class="absolute right-0 top-full mt-2 w-80 bg-surface-container-highest rounded-lg shadow-lg border border-outline-variant p-4 text-body-xs text-on-surface-variant hidden group-hover:block z-50">Total belanja minimum agar voucher bisa digunakan. Isi 0 jika tidak ada minimum.</span></span></label>
                    <input type="number" name="min_purchase" step="0.01" class="w-full h-11 px-3.5 py-2.5 border border-outline-variant rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary focus:outline-none text-sm" placeholder="0" value="{{ old('min_purchase', $voucher->min_purchase) }}">
                </div>
                <div class="space-y-1.5" id="maxDiscountContainer">
                    <label class="block text-label-sm font-medium text-on-surface-variant">Max Discount (Rp) <span class="inline-flex items-center cursor-help text-on-surface-variant relative group"><span class="material-symbols-outlined text-[18px]">info</span><span class="absolute right-0 top-full mt-2 w-80 bg-surface-container-highest rounded-lg shadow-lg border border-outline-variant p-4 text-body-xs text-on-surface-variant hidden group-hover:block z-50">Batas maksimum diskon yang diberikan (khusus tipe persentase). Isi 0 jika tidak ada batas.</span></span></label>
                    <input type="number" name="max_discount" step="0.01" class="w-full h-11 px-3.5 py-2.5 border border-outline-variant rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary focus:outline-none text-sm" placeholder="0" value="{{ old('max_discount', $voucher->max_discount) }}">
                </div>
            </div>

            <!-- Start Date & End Date -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                <div class="space-y-1.5">
                    <label class="block text-label-sm font-medium text-on-surface-variant">Start Date <span class="inline-flex items-center cursor-help text-on-surface-variant relative group"><span class="material-symbols-outlined text-[18px]">info</span><span class="absolute right-0 top-full mt-2 w-80 bg-surface-container-highest rounded-lg shadow-lg border border-outline-variant p-4 text-body-xs text-on-surface-variant hidden group-hover:block z-50">Tanggal mulai voucher aktif. Kosongkan jika voucher langsung aktif setelah dibuat.</span></span></label>
                    <input type="date" name="start_date" class="w-full h-11 px-3.5 py-2.5 border border-outline-variant rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary focus:outline-none text-sm" value="{{ old('start_date', $voucher->start_date ? $voucher->start_date->format('Y-m-d') : '') }}">
                </div>
                <div class="space-y-1.5">
                    <label class="block text-label-sm font-medium text-on-surface-variant">End Date <span class="inline-flex items-center cursor-help text-on-surface-variant relative group"><span class="material-symbols-outlined text-[18px]">info</span><span class="absolute right-0 top-full mt-2 w-80 bg-surface-container-highest rounded-lg shadow-lg border border-outline-variant p-4 text-body-xs text-on-surface-variant hidden group-hover:block z-50">Tanggal kadaluarsa voucher. Kosongkan jika tidak ada batas waktu.</span></span></label>
                    <input type="date" name="end_date" class="w-full h-11 px-3.5 py-2.5 border border-outline-variant rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary focus:outline-none text-sm" value="{{ old('end_date', $voucher->end_date ? $voucher->end_date->format('Y-m-d') : '') }}">
                </div>
            </div>

            <!-- Usage Limits -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                <div class="space-y-1.5">
                    <label class="block text-label-sm font-medium text-on-surface-variant">Usage Limit <span class="inline-flex items-center cursor-help text-on-surface-variant relative group"><span class="material-symbols-outlined text-[18px]">info</span><span class="absolute right-0 top-full mt-2 w-80 bg-surface-container-highest rounded-lg shadow-lg border border-outline-variant p-4 text-body-xs text-on-surface-variant hidden group-hover:block z-50">Banyaknya voucher ini bisa ditukarkan secara keseluruhan. Isi 0 jika unlimited.</span></span></label>
                    <input type="number" name="usage_limit" class="w-full h-11 px-3.5 py-2.5 border border-outline-variant rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary focus:outline-none text-sm" placeholder="0 = unlimited" value="{{ old('usage_limit', $voucher->usage_limit) }}">
                </div>
                <div class="space-y-1.5">
                    <label class="block text-label-sm font-medium text-on-surface-variant">Per User Limit <span class="inline-flex items-center cursor-help text-on-surface-variant relative group"><span class="material-symbols-outlined text-[18px]">info</span><span class="absolute right-0 top-full mt-2 w-80 bg-surface-container-highest rounded-lg shadow-lg border border-outline-variant p-4 text-body-xs text-on-surface-variant hidden group-hover:block z-50">Batas pemakaian voucher per satu akun customer. Isi 0 jika unlimited per user.</span></span></label>
                    <input type="number" name="usage_limit_per_user" class="w-full h-11 px-3.5 py-2.5 border border-outline-variant rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary focus:outline-none text-sm" placeholder="0 = unlimited" value="{{ old('usage_limit_per_user', $voucher->usage_limit_per_user) }}">
                </div>
            </div>

            <!-- Hidden Store ID & Require Follow -->
            <input type="hidden" name="store_id" value="{{ $voucher->store_id }}">
            <input type="hidden" name="require_follow" value="{{ $voucher->require_follow ? 1 : 0 }}">

            <!-- Visibilitas Voucher & Scope -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                <div class="space-y-1.5">
                    <label class="block text-label-sm font-medium text-on-surface-variant">Visibilitas Voucher <span class="text-danger">*</span> <span class="inline-flex items-center cursor-help text-on-surface-variant relative group"><span class="material-symbols-outlined text-[18px]">info</span><span class="absolute right-0 top-full mt-2 w-80 bg-surface-container-highest rounded-lg shadow-lg border border-outline-variant p-4 text-body-xs text-on-surface-variant hidden group-hover:block z-50">Pilih apakah voucher tampil untuk umum langsung, harus diklaim dulu oleh pembeli (seperti Shopee), atau tersembunyi (hanya lewat kode rahasia).</span></span></label>
                    <select name="visibility" id="visibilitySelect" class="w-full h-11 px-3.5 py-2.5 bg-white border border-outline-variant rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary focus:outline-none text-sm cursor-pointer">
                        <option value="public" {{ old('visibility', $voucher->visibility ?? 'public') === 'public' ? 'selected' : '' }}>Publik (Otomatis Tersedia)</option>
                        <option value="claimable" {{ old('visibility', $voucher->visibility) === 'claimable' ? 'selected' : '' }}>Klaim Terlebih Dahulu (Claimable)</option>
                        <option value="hidden" {{ old('visibility', $voucher->visibility) === 'hidden' ? 'selected' : '' }}>Tersembunyi (Hanya Lewat Kode Rahasia)</option>
                    </select>
                </div>
                <div class="space-y-1.5">
                    <label class="block text-label-sm font-medium text-on-surface-variant">Cakupan Voucher (Scope) <span class="text-danger">*</span> <span class="inline-flex items-center cursor-help text-on-surface-variant relative group"><span class="material-symbols-outlined text-[18px]">info</span><span class="absolute right-0 top-full mt-2 w-80 bg-surface-container-highest rounded-lg shadow-lg border border-outline-variant p-4 text-body-xs text-on-surface-variant hidden group-hover:block z-50">Tentukan cakupan voucher: Semua Produk Aktif, Pelanggan Tertentu, Kategori Tertentu, Produk/Artikel Tertentu, Brand Tertentu, Brand & Artikel, atau Group Customer.</span></span></label>
                    <select name="scope" id="scopeSelect" class="w-full h-11 px-3.5 py-2.5 bg-white border border-outline-variant rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary focus:outline-none text-sm cursor-pointer">
                        <option value="1" {{ old('scope', $voucher->scope) == 1 ? 'selected' : '' }}>1. Semua Produk Aktif (Voucher Toko / Web)</option>
                        <option value="2" {{ old('scope', $voucher->scope) == 2 ? 'selected' : '' }}>2. Customer Tertentu (Voucher Terbatas)</option>
                        <option value="3" {{ old('scope', $voucher->scope) == 3 ? 'selected' : '' }}>3. Kategori Produk Tertentu</option>
                        <option value="4" {{ old('scope', $voucher->scope) == 4 ? 'selected' : '' }}>4. Produk Tertentu (Per Artikel)</option>
                        <option value="5" {{ old('scope', $voucher->scope) == 5 ? 'selected' : '' }}>5. Brand Tertentu</option>
                        <option value="6" {{ old('scope', $voucher->scope) == 6 ? 'selected' : '' }}>6. Brand & Artikel Tertentu</option>
                        <option value="7" {{ old('scope', $voucher->scope) == 7 ? 'selected' : '' }}>7. Group Customer (Pembeli Terpilih - Karyawan / Reseller)</option>
                    </select>
                </div>
            </div>

            <!-- Scope 2: Customer Selection -->
            <div id="customerSelect" class="space-y-2 mt-5 hidden p-4 rounded-xl border border-outline-variant bg-surface-container-lowest/60 shadow-sm">
                <div class="flex items-center justify-between flex-wrap gap-2 pb-2 border-b border-outline-variant/40">
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-outlined text-primary text-[20px]">person</span>
                        <label class="text-sm font-semibold text-on-surface">Pilih Customer <span class="text-danger">*</span></label>
                        <span id="customerCountBadge" class="px-2.5 py-0.5 rounded-full bg-primary/10 text-primary font-semibold text-[11px]">0 customer dipilih</span>
                    </div>
                    <div class="flex items-center gap-2 text-xs">
                        <button type="button" id="selectAllCustomers" class="text-primary hover:underline font-medium flex items-center gap-1"><span class="material-symbols-outlined text-[15px]">select_all</span> Pilih Semua</button>
                        <span class="text-outline-variant">|</span>
                        <button type="button" id="clearAllCustomers" class="text-secondary hover:text-danger font-medium flex items-center gap-1"><span class="material-symbols-outlined text-[15px]">clear_all</span> Hapus Semua</button>
                    </div>
                </div>
                <div class="pt-1">
                    <select name="customer_ids[]" id="customersSelectInput" multiple class="w-full">
                        @foreach($customers as $c)
                            <option value="{{ $c->id }}" 
                                data-name="{{ $c->name }}"
                                data-email="{{ $c->email ?? '' }}"
                                data-phone="{{ $c->phone ?? '' }}"
                                data-type="{{ $c->customer_type == 2 ? 'Reseller' : 'Customer' }}"
                                data-initials="{{ strtoupper(substr($c->name, 0, 2)) }}"
                                {{ in_array($c->id, $selectedCustomerIds) ? 'selected' : '' }}>
                                {{ $c->name }} ({{ $c->email ?? $c->phone ?? 'No contact' }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <p class="text-xs text-on-surface-variant flex items-center gap-1 mt-1"><span class="material-symbols-outlined text-[15px]">info</span> Cari customer berdasarkan nama, email, atau nomor HP.</p>
                @error('customer_ids')<p class="text-danger text-sm">{{ $message }}</p>@enderror
            </div>

            <!-- Scope 3: Category Selection -->
            <div id="categorySelect" class="space-y-2 mt-5 hidden p-4 rounded-xl border border-outline-variant bg-surface-container-lowest/60 shadow-sm">
                <div class="flex items-center justify-between flex-wrap gap-2 pb-2 border-b border-outline-variant/40">
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-outlined text-primary text-[20px]">category</span>
                        <label class="text-sm font-semibold text-on-surface">Pilih Kategori Produk <span class="text-danger">*</span></label>
                        <span id="categoryCountBadge" class="px-2.5 py-0.5 rounded-full bg-primary/10 text-primary font-semibold text-[11px]">0 kategori dipilih</span>
                    </div>
                    <div class="flex items-center gap-2 text-xs">
                        <button type="button" id="selectAllCategories" class="text-primary hover:underline font-medium flex items-center gap-1"><span class="material-symbols-outlined text-[15px]">select_all</span> Pilih Semua</button>
                        <span class="text-outline-variant">|</span>
                        <button type="button" id="clearAllCategories" class="text-secondary hover:text-danger font-medium flex items-center gap-1"><span class="material-symbols-outlined text-[15px]">clear_all</span> Hapus Semua</button>
                    </div>
                </div>
                <div class="pt-1">
                    <select name="category_ids[]" id="categoriesSelectInput" multiple class="w-full">
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ in_array($cat->id, $selectedCategoryIds) ? 'selected' : '' }}>{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>
                <p class="text-xs text-on-surface-variant flex items-center gap-1 mt-1"><span class="material-symbols-outlined text-[15px]">info</span> Voucher berlaku untuk seluruh produk di dalam kategori yang dipilih.</p>
                @error('category_ids')<p class="text-danger text-sm">{{ $message }}</p>@enderror
            </div>

            <!-- Scope 5 & 6: Brand Selection (Ditempatkan sebelum Product agar Scope 6 bisa pilih Brand dulu) -->
            <div id="brandSelect" class="space-y-2 mt-5 hidden p-4 rounded-xl border border-outline-variant bg-surface-container-lowest/60 shadow-sm">
                <div class="flex items-center justify-between flex-wrap gap-2 pb-2 border-b border-outline-variant/40">
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-outlined text-primary text-[20px]">branding_watermark</span>
                        <label class="text-sm font-semibold text-on-surface">Pilih Brand <span class="text-danger">*</span></label>
                        <span id="brandCountBadge" class="px-2.5 py-0.5 rounded-full bg-primary/10 text-primary font-semibold text-[11px]">0 brand dipilih</span>
                    </div>
                    <div class="flex items-center gap-2 text-xs">
                        <button type="button" id="selectAllBrands" class="text-primary hover:underline font-medium flex items-center gap-1"><span class="material-symbols-outlined text-[15px]">select_all</span> Pilih Semua</button>
                        <span class="text-outline-variant">|</span>
                        <button type="button" id="clearAllBrands" class="text-secondary hover:text-danger font-medium flex items-center gap-1"><span class="material-symbols-outlined text-[15px]">clear_all</span> Hapus Semua</button>
                    </div>
                </div>
                <div class="pt-1">
                    <select name="brand_ids[]" id="brandsSelectInput" multiple class="w-full">
                        @foreach($brands as $b)
                            <option value="{{ $b->id }}" {{ in_array($b->id, $selectedBrandIds) ? 'selected' : '' }}>{{ $b->name }}</option>
                        @endforeach
                    </select>
                </div>
                <p id="brandHelperText" class="text-xs text-on-surface-variant flex items-center gap-1 mt-1"><span class="material-symbols-outlined text-[15px]">info</span> Voucher hanya berlaku untuk produk dari brand resmi yang dipilih.</p>
                @error('brand_ids')<p class="text-danger text-sm">{{ $message }}</p>@enderror
            </div>

            <!-- Scope 4 & 6: Product Selection (Per Artikel) -->
            <div id="productSelect" class="space-y-2 mt-5 hidden p-4 rounded-xl border border-outline-variant bg-surface-container-lowest/60 shadow-sm">
                <div class="flex items-center justify-between flex-wrap gap-2 pb-2 border-b border-outline-variant/40">
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-outlined text-primary text-[20px]">inventory_2</span>
                        <label class="text-sm font-semibold text-on-surface">Pilih Produk (Artikel) <span class="text-danger">*</span></label>
                        <span id="productCountBadge" class="px-2.5 py-0.5 rounded-full bg-primary/10 text-primary font-semibold text-[11px]">0 produk dipilih</span>
                    </div>
                    <div class="flex items-center gap-2 text-xs" id="productActionButtons">
                        <button type="button" id="selectAllProducts" class="text-primary hover:underline font-medium flex items-center gap-1"><span class="material-symbols-outlined text-[15px]">select_all</span> Pilih Semua</button>
                        <span class="text-outline-variant">|</span>
                        <button type="button" id="clearAllProducts" class="text-secondary hover:text-danger font-medium flex items-center gap-1"><span class="material-symbols-outlined text-[15px]">clear_all</span> Hapus Semua</button>
                    </div>
                </div>

                <!-- Info banner khusus scope 6 jika brand belum dipilih -->
                <div id="brandFirstNotice" class="hidden text-xs text-amber-800 bg-amber-50 border border-amber-200/80 rounded-lg p-3 flex items-center gap-2">
                    <span class="material-symbols-outlined text-amber-600 text-[18px]">info</span>
                    <span>Silakan <strong>pilih brand di atas terlebih dahulu</strong> untuk menampilkan daftar artikel produk yang tersedia.</span>
                </div>

                <div class="pt-1" id="productSelectWrapper">
                    <select name="product_ids[]" id="productsSelectInput" multiple class="w-full">
                        @foreach($products as $p)
                            <option value="{{ $p->id }}" 
                                data-brand-id="{{ $p->brand_id }}"
                                data-brand-name="{{ $p->brand->name ?? '' }}"
                                {{ in_array($p->id, $selectedProductIds) ? 'selected' : '' }}>
                                {{ $p->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <p id="productHelperText" class="text-xs text-on-surface-variant flex items-center gap-1 mt-1"><span class="material-symbols-outlined text-[15px]">info</span> Voucher hanya berlaku untuk artikel/produk tertentu yang dipilih.</p>
                @error('product_ids')<p class="text-danger text-sm">{{ $message }}</p>@enderror
            </div>

            <!-- Scope 7: Customer Group Selection -->
            <div id="customerGroupSelect" class="space-y-2 mt-5 hidden p-4 rounded-xl border border-outline-variant bg-surface-container-lowest/60 shadow-sm">
                <div class="flex items-center justify-between flex-wrap gap-2 pb-2 border-b border-outline-variant/40">
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-outlined text-primary text-[20px]">group</span>
                        <label class="text-sm font-semibold text-on-surface">Pilih Group Customer <span class="text-danger">*</span></label>
                        <span id="customerGroupCountBadge" class="px-2.5 py-0.5 rounded-full bg-primary/10 text-primary font-semibold text-[11px]">0 group dipilih</span>
                    </div>
                    <div class="flex items-center gap-2 text-xs">
                        <button type="button" id="selectAllCustomerGroups" class="text-primary hover:underline font-medium flex items-center gap-1"><span class="material-symbols-outlined text-[15px]">select_all</span> Pilih Semua</button>
                        <span class="text-outline-variant">|</span>
                        <button type="button" id="clearAllCustomerGroups" class="text-secondary hover:text-danger font-medium flex items-center gap-1"><span class="material-symbols-outlined text-[15px]">clear_all</span> Hapus Semua</button>
                    </div>
                </div>
                <div class="pt-1">
                    <select name="customer_group_ids[]" id="customerGroupsSelectInput" multiple class="w-full">
                        @foreach($customerGroups as $g)
                            <option value="{{ $g->id }}" 
                                data-discount="{{ floatval($g->discount_percent) }}"
                                data-members="{{ $g->members_count ?? $g->members()->count() }}"
                                {{ in_array($g->id, $selectedCustomerGroupIds) ? 'selected' : '' }}>
                                {{ $g->name }} ({{ $g->members_count ?? $g->members()->count() }} anggota • Diskon: {{ floatval($g->discount_percent) }}%)
                            </option>
                        @endforeach
                    </select>
                </div>
                <p class="text-xs text-on-surface-variant flex items-center gap-1 mt-1"><span class="material-symbols-outlined text-[15px]">info</span> Hanya pelanggan yang terdaftar di group tersebut yang berhak menggunakan voucher ini.</p>
                @error('customer_group_ids')<p class="text-danger text-sm">{{ $message }}</p>@enderror
            </div>

            <!-- Checkboxes: Show on Web, Allow Stacking, New Customer -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-6">
                <div class="flex items-center gap-2">
                    <input type="checkbox" name="show_on_web" id="showOnWeb" class="w-4 h-4 rounded border-outline-variant text-primary focus:ring-primary" value="1" {{ old('show_on_web', $voucher->show_on_web) ? 'checked' : '' }}>
                    <label for="showOnWeb" class="text-label-sm font-medium text-on-surface-variant">Tampilkan di Website (Show on Web)</label>
                </div>

                <div class="flex items-center gap-2">
                    <input type="checkbox" name="valid_for_new_customer" id="validForNewCustomer" class="w-4 h-4 rounded border-outline-variant text-primary focus:ring-primary" value="1" {{ old('valid_for_new_customer', $voucher->valid_for_new_customer) ? 'checked' : '' }}>
                    <label for="validForNewCustomer" class="text-label-sm font-medium text-on-surface-variant">Khusus Pembeli Baru (First-time Buyer)</label>
                </div>

                <div id="allowStackingContainer" class="flex items-center gap-2 hidden">
                    <input type="checkbox" name="allow_stacking" id="allowStacking" class="w-4 h-4 rounded border-outline-variant text-primary focus:ring-primary" value="1" {{ old('allow_stacking', $voucher->allow_stacking) ? 'checked' : '' }}>
                    <label for="allowStacking" class="text-label-sm font-medium text-on-surface-variant">Bisa Digabung (Stackable dengan Voucher Diskon Belanja)</label>
                </div>
            </div>

            <!-- Status -->
            <div class="space-y-1.5 mt-4">
                <label class="block text-label-sm font-medium text-on-surface-variant">Status</label>
                <select name="is_active" id="statusSelect" class="w-full h-11 px-3.5 py-2.5 bg-white border border-outline-variant rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary focus:outline-none text-sm cursor-pointer">
                    <option value="1" {{ old('is_active', $voucher->is_active) ? 'selected' : '' }}>Active</option>
                    <option value="0" {{ old('is_active', $voucher->is_active) ? '' : 'selected' }}>Inactive</option>
                </select>
            </div>

            <hr class="my-6 border-outline-variant">
            <div class="flex justify-end gap-3">
                <a href="{{ route('vouchers.index') }}" class="px-5 py-2 border border-outline-variant text-on-surface-variant rounded-lg font-label-md hover:bg-surface-container transition-colors">Cancel</a>
                <button type="submit" class="px-5 py-2 bg-primary text-white rounded-lg font-label-md hover:opacity-90 transition-all shadow-sm">Update Voucher</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<style>
/* Clean, modern Select2 styling */
.select2-container {
    width: 100% !important;
}
.select2-container--default .select2-selection--single {
    height: 44px !important;
    border: 1px solid var(--color-outline-variant, #cbd5e1) !important;
    border-radius: 0.75rem !important;
    padding: 0 14px !important;
    background-color: #ffffff !important;
    display: flex !important;
    align-items: center !important;
    transition: all 0.2s ease-in-out !important;
}
.select2-container--default.select2-container--focus .select2-selection--single,
.select2-container--default.select2-container--open .select2-selection--single {
    border-color: var(--color-primary, #2563eb) !important;
    box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12) !important;
    outline: none !important;
}
.select2-container--default .select2-selection--single .select2-selection__rendered {
    color: var(--color-on-surface, #1e293b) !important;
    font-size: 0.875rem !important;
    padding-left: 0 !important;
    line-height: normal !important;
}
.select2-container--default .select2-selection--single .select2-selection__arrow {
    height: 100% !important;
    right: 12px !important;
    top: 0 !important;
}
.select2-container--default .select2-selection--multiple {
    min-height: 44px !important;
    border: 1px solid var(--color-outline-variant, #cbd5e1) !important;
    border-radius: 0.75rem !important;
    padding: 4px 34px 4px 10px !important;
    background-color: #ffffff !important;
    display: flex !important;
    flex-wrap: wrap !important;
    align-items: center !important;
    gap: 4px !important;
    position: relative !important;
    transition: all 0.2s ease-in-out !important;
}
.select2-container--default.select2-container--focus .select2-selection--multiple,
.select2-container--default.select2-container--open .select2-selection--multiple {
    border-color: var(--color-primary, #2563eb) !important;
    box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12) !important;
    outline: none !important;
}
.select2-container--default .select2-selection--multiple .select2-selection__choice {
    background-color: #eff6ff !important;
    border: 1px solid #bfdbfe !important;
    color: #1d4ed8 !important;
    border-radius: 9999px !important;
    padding: 3px 10px !important;
    font-size: 0.75rem !important;
    font-weight: 600 !important;
    display: inline-flex !important;
    align-items: center !important;
    gap: 6px !important;
    box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05) !important;
}
.select2-container--default .select2-selection--multiple .select2-selection__choice__remove {
    color: #3b82f6 !important;
    font-weight: bold !important;
    font-size: 13px !important;
    margin-right: 2px !important;
    padding: 0 !important;
    border: none !important;
    background: transparent !important;
    cursor: pointer !important;
    transition: color 0.15s ease !important;
}
.select2-container--default .select2-selection--multiple .select2-selection__choice__remove:hover {
    color: #dc2626 !important;
}
.select2-dropdown {
    border: 1px solid var(--color-outline-variant, #cbd5e1) !important;
    border-radius: 0.75rem !important;
    box-shadow: 0 12px 28px -4px rgba(0, 0, 0, 0.12), 0 4px 10px -2px rgba(0, 0, 0, 0.05) !important;
    overflow: hidden !important;
    z-index: 9999 !important;
    background-color: #ffffff !important;
}
.select2-container--default .select2-results__option--highlighted[aria-selected] {
    background-color: #eff6ff !important;
    color: #1d4ed8 !important;
}
.select2-container--default .select2-results__option[aria-selected=true] {
    background-color: var(--color-primary, #1c0e07) !important;
    color: #ffffff !important;
}
</style>
<script>
// Cache all products data for dynamic filtering
const allProductOptions = [];
$('#productsSelectInput option').each(function() {
    allProductOptions.push({
        id: $(this).val(),
        name: $(this).text().trim(),
        brandId: $(this).data('brand-id') ? String($(this).data('brand-id')) : '',
        brandName: $(this).data('brand-name') ? String($(this).data('brand-name')) : '',
        selected: $(this).is(':selected')
    });
});

function filterProductsByBrand() {
    const scope = String($('#scopeSelect').val() || document.getElementById('scopeSelect').value);
    const $productSelect = $('#productsSelectInput');
    const $brandSelect = $('#brandsSelectInput');
    const selectedBrands = ($brandSelect.val() || []).map(String);
    const notice = document.getElementById('brandFirstNotice');
    const productWrapper = document.getElementById('productSelectWrapper');
    const productActions = document.getElementById('productActionButtons');
    const helperText = document.getElementById('productHelperText');

    if (scope === '6') {
        // Scope 6: Brand & Artikel Tertentu (Pilih Brand dulu, lalu Artikel)
        if (selectedBrands.length === 0) {
            // Belum ada brand dipilih
            if (notice) notice.classList.remove('hidden');
            if (productWrapper) productWrapper.classList.add('opacity-40', 'pointer-events-none');
            if (productActions) productActions.classList.add('hidden');
            if (helperText) helperText.textContent = 'Pilih brand di atas terlebih dahulu untuk menampilkan daftar artikel.';
            
            // Clear products
            $productSelect.val(null).empty().trigger('change');
            return;
        }

        // Sudah ada brand dipilih
        if (notice) notice.classList.add('hidden');
        if (productWrapper) productWrapper.classList.remove('opacity-40', 'pointer-events-none');
        if (productActions) productActions.classList.remove('hidden');
        if (helperText) helperText.textContent = 'Menampilkan artikel dari brand yang dipilih di atas.';

        const currentlySelected = $productSelect.val() || [];
        $productSelect.empty();

        const filtered = allProductOptions.filter(p => selectedBrands.includes(p.brandId));
        filtered.forEach(p => {
            const isSel = currentlySelected.includes(p.id) || p.selected;
            const opt = new Option(p.name, p.id, isSel, isSel);
            $(opt).attr('data-brand-id', p.brandId);
            $(opt).attr('data-brand-name', p.brandName);
            $productSelect.append(opt);
        });

        $productSelect.trigger('change');
    } else if (scope === '4') {
        // Scope 4: Produk Tertentu (Semua produk tersedia langsung)
        if (notice) notice.classList.add('hidden');
        if (productWrapper) productWrapper.classList.remove('opacity-40', 'pointer-events-none');
        if (productActions) productActions.classList.remove('hidden');
        if (helperText) helperText.textContent = 'Voucher hanya berlaku untuk artikel/produk tertentu yang dipilih.';

        const currentlySelected = $productSelect.val() || [];
        if ($productSelect.find('option').length !== allProductOptions.length) {
            $productSelect.empty();
            allProductOptions.forEach(p => {
                const isSel = currentlySelected.includes(p.id) || p.selected;
                const opt = new Option(p.name, p.id, isSel, isSel);
                $(opt).attr('data-brand-id', p.brandId);
                $(opt).attr('data-brand-name', p.brandName);
                $productSelect.append(opt);
            });
            $productSelect.trigger('change');
        }
    }
}

function adjustFormFields() {
    const type = String($('#typeSelect').val() || document.getElementById('typeSelect').value);
    const scope = String($('#scopeSelect').val() || document.getElementById('scopeSelect').value);

    const customerSelect = document.getElementById('customerSelect');
    const categorySelect = document.getElementById('categorySelect');
    const productSelect = document.getElementById('productSelect');
    const brandSelect = document.getElementById('brandSelect');
    const customerGroupSelect = document.getElementById('customerGroupSelect');
    const maxDiscountContainer = document.getElementById('maxDiscountContainer');

    // Scope 2: Customer Tertentu
    if (customerSelect) customerSelect.classList.toggle('hidden', scope !== '2');

    // Scope 3: Kategori Tertentu
    if (categorySelect) categorySelect.classList.toggle('hidden', scope !== '3');

    // Scope 5 & 6: Brand Tertentu
    if (brandSelect) brandSelect.classList.toggle('hidden', !['5', '6'].includes(scope));

    // Scope 4 & 6: Produk Tertentu (Artikel)
    if (productSelect) productSelect.classList.toggle('hidden', !['4', '6'].includes(scope));

    // Scope 7: Group Customer
    if (customerGroupSelect) customerGroupSelect.classList.toggle('hidden', scope !== '7');

    // Brand & Product dependency filter
    filterProductsByBrand();

    // Max discount is relevant for percentage discount (type 1)
    if (maxDiscountContainer) {
        maxDiscountContainer.classList.toggle('hidden', type !== '1');
    }

    // Allow stacking ONLY available for Shipping Discount (type 3)
    const allowStackingContainer = document.getElementById('allowStackingContainer');
    const allowStackingInput = document.getElementById('allowStacking');
    if (allowStackingContainer) {
        const isShipping = type === '3';
        allowStackingContainer.classList.toggle('hidden', !isShipping);
        if (!isShipping && allowStackingInput) {
            allowStackingInput.checked = false;
        }
    }
}

// Option Formatters
function formatCustomerOption(state) {
    if (!state.id) return state.text;
    const element = state.element;
    if (!element) return state.text;
    const name = element.dataset.name || state.text;
    const email = element.dataset.email || '';
    const phone = element.dataset.phone || '';
    const type = element.dataset.type || '';
    const initials = element.dataset.initials || name.substring(0, 2).toUpperCase();
    
    const contactParts = [];
    if (email) contactParts.push(email);
    if (phone) contactParts.push(phone);
    const contactInfo = contactParts.join(' • ');

    const badge = type === 'Reseller' 
        ? '<span class="px-2 py-0.5 text-[10px] font-semibold bg-amber-100 text-amber-800 rounded-full flex-shrink-0">Reseller</span>'
        : '';

    return $(`
        <div class="flex items-center gap-3 py-1">
            <div class="w-8 h-8 rounded-full bg-primary/10 text-primary flex items-center justify-center font-bold text-xs flex-shrink-0 border border-primary/20">
                ${initials}
            </div>
            <div class="flex-1 min-w-0">
                <div class="flex items-center gap-2">
                    <span class="font-medium text-sm text-on-surface truncate">${name}</span>
                    ${badge}
                </div>
                ${contactInfo ? `<div class="text-xs text-on-surface-variant truncate mt-0.5">${contactInfo}</div>` : ''}
            </div>
        </div>
    `);
}

function formatCustomerSelection(state) {
    if (!state.id) return state.text;
    return state.element?.dataset.name || state.text;
}

function formatBrandOption(state) {
    if (!state.id) return state.text;
    return $(`
        <div class="flex items-center gap-2.5 py-1">
            <span class="w-6 h-6 rounded-md bg-primary/10 text-primary flex items-center justify-center flex-shrink-0">
                <span class="material-symbols-outlined text-[16px]">branding_watermark</span>
            </span>
            <span class="text-sm font-medium text-on-surface truncate">${state.text}</span>
        </div>
    `);
}

function formatCategoryOption(state) {
    if (!state.id) return state.text;
    return $(`
        <div class="flex items-center gap-2.5 py-1">
            <span class="w-6 h-6 rounded-md bg-primary/10 text-primary flex items-center justify-center flex-shrink-0">
                <span class="material-symbols-outlined text-[16px]">category</span>
            </span>
            <span class="text-sm font-medium text-on-surface truncate">${state.text}</span>
        </div>
    `);
}

function formatProductOption(state) {
    if (!state.id) return state.text;
    const brandName = state.element?.dataset.brandName || $(state.element).attr('data-brand-name') || '';
    return $(`
        <div class="flex items-center justify-between gap-3 py-1">
            <div class="flex items-center gap-2.5 min-w-0">
                <span class="w-6 h-6 rounded-md bg-primary/10 text-primary flex items-center justify-center flex-shrink-0">
                    <span class="material-symbols-outlined text-[16px]">inventory_2</span>
                </span>
                <span class="text-sm font-medium text-on-surface truncate">${state.text}</span>
            </div>
            ${brandName ? `<span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-surface-container-high text-on-surface-variant flex-shrink-0 border border-outline-variant/40">${brandName}</span>` : ''}
        </div>
    `);
}

function formatGroupOption(state) {
    if (!state.id) return state.text;
    const discount = state.element?.dataset.discount || $(state.element).attr('data-discount') || '';
    return $(`
        <div class="flex items-center justify-between gap-3 py-1">
            <div class="flex items-center gap-2.5 min-w-0">
                <span class="w-6 h-6 rounded-md bg-primary/10 text-primary flex items-center justify-center flex-shrink-0">
                    <span class="material-symbols-outlined text-[16px]">groups</span>
                </span>
                <span class="text-sm font-medium text-on-surface truncate">${state.text}</span>
            </div>
            ${discount && parseFloat(discount) > 0 ? `<span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200 flex-shrink-0">Diskon ${discount}%</span>` : ''}
        </div>
    `);
}

// Setup Multi-Select Helper with count badge, templateResult & Select All / Clear All
function setupMultiSelect(selectId, badgeId, selectAllBtnId, clearAllBtnId, unitLabel, placeholder, templateFn) {
    const $select = $(selectId);
    if (!$select.length) return;

    function updateBadge() {
        const count = $select.val()?.length || 0;
        const badge = document.querySelector(badgeId);
        if (badge) {
            badge.textContent = `${count} ${unitLabel} dipilih`;
        }
    }

    const config = {
        placeholder: placeholder,
        allowClear: true,
        width: '100%',
        closeOnSelect: false
    };

    if (templateFn) {
        config.templateResult = templateFn;
    }

    $select.select2(config).on('change', updateBadge);
    updateBadge();

    $(selectAllBtnId).on('click', function() {
        const allIds = $select.find('option').map(function() { return $(this).val(); }).get();
        $select.val(allIds).trigger('change');
    });

    $(clearAllBtnId).on('click', function() {
        $select.val(null).trigger('change');
    });
}

$(document).ready(function() {
    // 0. Single Selects Initialization
    $('#typeSelect, #visibilitySelect, #scopeSelect, #statusSelect').select2({
        width: '100%',
        minimumResultsForSearch: Infinity
    });

    $('#typeSelect, #scopeSelect').on('select2:select change', function() {
        adjustFormFields();
    });

    // 1. Customers Select
    const $customerSelect = $('#customersSelectInput');
    $customerSelect.select2({
        placeholder: "Cari nama, email, atau nomor HP customer...",
        allowClear: true,
        width: '100%',
        closeOnSelect: false,
        templateResult: formatCustomerOption,
        templateSelection: formatCustomerSelection,
        matcher: function(params, data) {
            if ($.trim(params.term) === '') return data;
            if (!data.id) return null;
            const term = params.term.toLowerCase();
            const element = data.element;
            const name = (element?.dataset.name || data.text || '').toLowerCase();
            const email = (element?.dataset.email || '').toLowerCase();
            const phone = (element?.dataset.phone || '').toLowerCase();

            if (name.includes(term) || email.includes(term) || phone.includes(term)) {
                return data;
            }
            return null;
        }
    }).on('change', function() {
        const count = $(this).val()?.length || 0;
        $('#customerCountBadge').text(`${count} customer dipilih`);
    });

    $('#selectAllCustomers').on('click', function() {
        const allIds = $customerSelect.find('option').map(function() { return $(this).val(); }).get();
        $customerSelect.val(allIds).trigger('change');
    });

    $('#clearAllCustomers').on('click', function() {
        $customerSelect.val(null).trigger('change');
    });

    // 2. Categories Select
    setupMultiSelect('#categoriesSelectInput', '#categoryCountBadge', '#selectAllCategories', '#clearAllCategories', 'kategori', 'Pilih kategori produk...', formatCategoryOption);

    // 3. Brands Select (When changed, re-filter products if scope is 6)
    setupMultiSelect('#brandsSelectInput', '#brandCountBadge', '#selectAllBrands', '#clearAllBrands', 'brand', 'Pilih brand resmi...', formatBrandOption);
    $('#brandsSelectInput').on('change', function() {
        filterProductsByBrand();
    });

    // 4. Products Select
    setupMultiSelect('#productsSelectInput', '#productCountBadge', '#selectAllProducts', '#clearAllProducts', 'produk', 'Cari dan pilih produk/artikel...', formatProductOption);

    // 5. Customer Groups Select
    setupMultiSelect('#customerGroupsSelectInput', '#customerGroupCountBadge', '#selectAllCustomerGroups', '#clearAllCustomerGroups', 'group', 'Pilih group customer...', formatGroupOption);

    adjustFormFields();
});
</script>
@endpush
