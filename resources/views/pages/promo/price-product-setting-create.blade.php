@extends('layouts.app')

@section('title', 'Create Price Setting')

@section('content')
<div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
    <div>
        <h1 class="font-headline-lg text-headline-lg text-on-surface">Create Price Setting</h1>
        <nav class="flex items-center gap-2 text-body-md text-on-surface-variant mt-1">
            <a href="{{ route('dashboard') }}" class="text-primary hover:underline">Dashboard</a>
            <span class="material-symbols-outlined text-[16px]">chevron_right</span>
            <a href="{{ route('price-settings.index') }}" class="text-primary hover:underline">Price Settings</a>
            <span class="material-symbols-outlined text-[16px]">chevron_right</span>
            <span>Create</span>
        </nav>
    </div>
    <a href="{{ route('price-settings.index') }}" class="flex items-center gap-2 px-4 py-2 bg-surface-container-lowest border border-outline-variant text-secondary rounded-lg font-label-md hover:bg-surface-container transition-all">
        <span class="material-symbols-outlined text-[18px]">arrow_back</span> Back
    </a>
</div>

<form id="priceSettingForm" method="POST" action="{{ route('price-settings.store') }}">
    @csrf
    <div class="rounded-xl shadow-sm border border-outline-variant/30 p-6 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="space-y-1.5">
                <label class="block text-label-sm font-medium text-on-surface-variant">Campaign Name <span class="text-danger">*</span> <span class="inline-flex items-center cursor-help text-on-surface-variant relative group"><span class="material-symbols-outlined text-[18px]">info</span><span class="absolute right-0 top-full mt-2 w-80 bg-surface-container-highest rounded-lg shadow-lg border border-outline-variant p-4 text-body-xs text-on-surface-variant hidden group-hover:block z-50">Nama unik untuk campaign diskon</span></span></label>
                <input type="text" name="title" class="w-full px-3 py-2 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary/20 focus:outline-none" placeholder="e.g., Summer Collection 2024" required value="{{ old('title') }}">
                @error('title')<p class="text-danger text-sm">{{ $message }}</p>@enderror
            </div>
            <div class="space-y-1.5">
                <label class="block text-label-sm font-medium text-on-surface-variant">Code <span class="inline-flex items-center cursor-help text-on-surface-variant relative group"><span class="material-symbols-outlined text-[18px]">info</span><span class="absolute right-0 top-full mt-2 w-80 bg-surface-container-highest rounded-lg shadow-lg border border-outline-variant p-4 text-body-xs text-on-surface-variant hidden group-hover:block z-50">Kode voucher untuk campaign ini</span></span></label>
                <input type="text" name="code" class="w-full px-3 py-2 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary/20 focus:outline-none" placeholder="e.g., SUMMER24" value="{{ old('code') }}">
                @error('code')<p class="text-danger text-sm">{{ $message }}</p>@enderror
            </div>
        </div>
        <div class="space-y-1.5 mt-4">
            <label class="block text-label-sm font-medium text-on-surface-variant">Description</label>
            <textarea name="description" class="w-full px-3 py-2 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary/20 focus:outline-none" rows="3" placeholder="Campaign description...">{{ old('description') }}</textarea>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4">
            <div class="space-y-1.5">
                <label class="block text-label-sm font-medium text-on-surface-variant">Type <span class="inline-flex items-center cursor-help text-on-surface-variant relative group"><span class="material-symbols-outlined text-[18px]">info</span><span class="absolute right-0 top-full mt-2 w-80 bg-surface-container-highest rounded-lg shadow-lg border border-outline-variant p-4 text-body-xs text-on-surface-variant hidden group-hover:block z-50">Diskon langsung: potongan per item. Diskon volume: potongan berdasarkan qty pembelian</span></span></label>
                <select name="type" class="w-full px-3 py-2 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary/20 focus:outline-none bg-white">
                    <option value="1" {{ old('type', 1) == 1 ? 'selected' : '' }}>Diskon Langsung</option>
                    <option value="2" {{ old('type', 1) == 2 ? 'selected' : '' }}>Diskon Volume</option>
                </select>
            </div>
            <div class="space-y-1.5">
                <label class="block text-label-sm font-medium text-on-surface-variant">Discount Type <span class="inline-flex items-center cursor-help text-on-surface-variant relative group"><span class="material-symbols-outlined text-[18px]">info</span><span class="absolute right-0 top-full mt-2 w-80 bg-surface-container-highest rounded-lg shadow-lg border border-outline-variant p-4 text-body-xs text-on-surface-variant hidden group-hover:block z-50">Pilih bentuk diskon: persentase atau nominal Rupiah</span></span></label>
                <select name="discount_type" class="w-full px-3 py-2 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary/20 focus:outline-none bg-white">
                    <option value="1" {{ old('type', 1) == 1 ? 'selected' : '' }}>Percentage (%)</option>
                    <option value="2" {{ old('type', 1) == 2 ? 'selected' : '' }}>Nominal</option>
                </select>
            </div>
            <div class="space-y-1.5">
                <label class="block text-label-sm font-medium text-on-surface-variant">Discount Value <span class="text-danger">*</span> <span class="inline-flex items-center cursor-help text-on-surface-variant relative group"><span class="material-symbols-outlined text-[18px]">info</span><span class="absolute right-0 top-full mt-2 w-80 bg-surface-container-highest rounded-lg shadow-lg border border-outline-variant p-4 text-body-xs text-on-surface-variant hidden group-hover:block z-50">Nilai diskon: persentase 0-100 atau nominal Rupiah</span></span></label>
                <input type="number" name="discount_value" step="0.01" class="w-full px-3 py-2 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary/20 focus:outline-none" placeholder="0" required value="{{ old('discount_value') }}" id="discountValueInput">
                @error('discount_value')<p class="text-danger text-sm">{{ $message }}</p>@enderror
            </div>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
            <div class="space-y-1.5">
                <label class="block text-label-sm font-medium text-on-surface-variant">Min Purchase (Rp) <span class="inline-flex items-center cursor-help text-on-surface-variant relative group"><span class="material-symbols-outlined text-[18px]">info</span><span class="absolute right-0 top-full mt-2 w-80 bg-surface-container-highest rounded-lg shadow-lg border border-outline-variant p-4 text-body-xs text-on-surface-variant hidden group-hover:block z-50">Harga minimum pembelian agar diskon berlaku</span></span></label>
                <input type="number" name="min_purchase" step="0.01" class="w-full px-3 py-2 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary/20 focus:outline-none" placeholder="0" value="{{ old('min_purchase') }}">
            </div>
            <div class="space-y-1.5">
                <label class="block text-label-sm font-medium text-on-surface-variant">Max Discount (Rp) <span class="inline-flex items-center cursor-help text-on-surface-variant relative group"><span class="material-symbols-outlined text-[18px]">info</span><span class="absolute right-0 top-full mt-2 w-80 bg-surface-container-highest rounded-lg shadow-lg border border-outline-variant p-4 text-body-xs text-on-surface-variant hidden group-hover:block z-50">Batas maksimal potongan harga</span></span></label>
                <input type="number" name="max_discount" step="0.01" class="w-full px-3 py-2 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary/20 focus:outline-none" placeholder="0" value="{{ old('max_discount') }}">
            </div>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4">
            <div class="space-y-1.5">
                <label class="block text-label-sm font-medium text-on-surface-variant">Start Date <span class="inline-flex items-center cursor-help text-on-surface-variant relative group"><span class="material-symbols-outlined text-[18px]">info</span><span class="absolute right-0 top-full mt-2 w-80 bg-surface-container-highest rounded-lg shadow-lg border border-outline-variant p-4 text-body-xs text-on-surface-variant hidden group-hover:block z-50">Tanggal mulai diskon berlaku</span></span></label>
                <input type="date" name="start_date" class="w-full px-3 py-2 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary/20 focus:outline-none" value="{{ old('start_date') }}">
            </div>
            <div class="space-y-1.5">
                <label class="block text-label-sm font-medium text-on-surface-variant">End Date <span class="inline-flex items-center cursor-help text-on-surface-variant relative group"><span class="material-symbols-outlined text-[18px]">info</span><span class="absolute right-0 top-full mt-2 w-80 bg-surface-container-highest rounded-lg shadow-lg border border-outline-variant p-4 text-body-xs text-on-surface-variant hidden group-hover:block z-50">Tanggal berakhir diskon berlaku</span></span></label>
                <input type="date" name="end_date" class="w-full px-3 py-2 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary/20 focus:outline-none" value="{{ old('end_date') }}">
            </div>
            <div class="space-y-1.5">
                <label class="block text-label-sm font-medium text-on-surface-variant">Sort Order <span class="inline-flex items-center cursor-help text-on-surface-variant relative group"><span class="material-symbols-outlined text-[18px]">info</span><span class="absolute right-0 top-full mt-2 w-80 bg-surface-container-highest rounded-lg shadow-lg border border-outline-variant p-4 text-body-xs text-on-surface-variant hidden group-hover:block z-50">Urutan tampilan di halaman</span></span></label>
                <input type="number" name="sort_order" class="w-full px-3 py-2 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary/20 focus:outline-none" placeholder="0" value="{{ old('sort_order', 0) }}">
            </div>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
            <div class="space-y-1.5">
                <label class="block text-label-sm font-medium text-on-surface-variant">Scope <span class="inline-flex items-center cursor-help text-on-surface-variant relative group"><span class="material-symbols-outlined text-[18px]">info</span><span class="absolute right-0 top-full mt-2 w-80 bg-surface-container-highest rounded-lg shadow-lg border border-outline-variant p-4 text-body-xs text-on-surface-variant hidden group-hover:block z-50">Jangkauan produk yang terkena diskon</span></span></label>
                <select name="scope" class="w-full px-3 py-2 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary/20 focus:outline-none bg-white">
                    <option value="1" {{ old('scope', 1) == 1 ? 'selected' : '' }}>All products</option>
                    <option value="2" {{ old('scope', 1) == 2 ? 'selected' : '' }}>Specific products</option>
                    <option value="3" {{ old('scope', 1) == 3 ? 'selected' : '' }}>Category</option>
                </select>
            </div>
            <div class="space-y-1.5">
                <label class="block text-label-sm font-medium text-on-surface-variant">Status <span class="inline-flex items-center cursor-help text-on-surface-variant relative group"><span class="material-symbols-outlined text-[18px]">info</span><span class="absolute right-0 top-full mt-2 w-80 bg-surface-container-highest rounded-lg shadow-lg border border-outline-variant p-4 text-body-xs text-on-surface-variant hidden group-hover:block z-50">Aktif/nonaktifkan campaign</span></span></label>
                <select name="is_active" class="w-full px-3 py-2 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary/20 focus:outline-none bg-white">
                    <option value="1" {{ old('is_active', 1) == 1 ? 'selected' : '' }}>Active</option>
                    <option value="0" {{ old('is_active', 1) == 0 ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>
        </div>
        <div class="flex items-center gap-2 mt-4">
            <input type="checkbox" name="is_featured" id="isFeatured" class="w-4 h-4 rounded border-outline-variant text-primary focus:ring-primary" value="1" {{ old('is_featured') ? 'checked' : '' }}>
            <label for="isFeatured" class="text-label-sm font-medium text-on-surface-variant">Featured <span class="inline-flex items-center cursor-help text-on-surface-variant relative group"><span class="material-symbols-outlined text-[18px]">info</span><span class="absolute right-0 top-full mt-2 w-80 bg-surface-container-highest rounded-lg shadow-lg border border-outline-variant p-4 text-body-xs text-on-surface-variant hidden group-hover:block z-50">Tampilkan di posisi unggulan</span></span></label>
        </div>
    </div>

    <div class="rounded-xl shadow-sm border border-outline-variant/30 p-6 mb-6 hidden" id="volumeTierSection">
        <h3 class="font-headline-md text-headline-md text-on-surface mb-4">Volume Discount Tiers</h3>
        <p class="text-label-sm text-on-surface-variant mb-4">Atur diskon berdasarkan jumlah pembelian. Semakin banyak dibeli, semakin murah harganya.</p>
        <div id="volumeTierList" class="space-y-2">
            <!-- Tiers will be added here dynamically -->
        </div>
        <button type="button" class="mt-3 px-4 py-2 bg-surface-container-high text-primary font-medium rounded-lg hover:bg-surface-variant transition-colors text-label-sm" onclick="addVolumeTier()">
            <span class="material-symbols-outlined text-[16px] align-middle">add</span> Tambah Tier
        </button>
    </div>

    <div class="rounded-xl shadow-sm border border-outline-variant/30 p-6 mb-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="font-headline-md text-headline-md text-on-surface">Products</h3>
            <div class="flex items-center gap-3">
                <input type="text" id="productSearch" placeholder="Search products..." class="px-3 py-2 border border-outline-variant rounded-lg text-body-md focus:ring-1 focus:ring-primary focus:border-primary w-64">
                <div class="relative">
                    <button type="button" class="w-8 h-8 flex items-center justify-center rounded-full bg-primary/10 text-primary hover:bg-primary/20 transition-colors" onclick="toggleInfo()">
                        <span class="material-symbols-outlined text-[18px]">info</span>
                    </button>
                    <div id="inputInfo" class="hidden absolute right-0 top-10 w-80 bg-surface-container-lowest border border-outline-variant rounded-xl shadow-lg p-4 z-50 text-body-sm">
                        <h4 class="font-headline-md text-headline-md text-on-surface mb-2">Cara Penginputan Harga</h4>
                        <ul class="space-y-2 text-on-surface-variant">
                            <li class="flex items-start gap-2">
                                <span class="material-symbols-outlined text-[18px] text-primary mt-0.5">check_circle</span>
                                <span>Masukkan harga satuan untuk setiap variant dalam Rupiah (tanpa titik atau koma)</span>
                            </li>
                            <li class="flex items-start gap-2">
                                <span class="material-symbols-outlined text-[18px] text-primary mt-0.5">check_circle</span>
                                <span>Harga akan otomatis terkena diskon sesuai setting di atas (Percentage atau Nominal)</span>
                            </li>
                            <li class="flex items-start gap-2">
                                <span class="material-symbols-outlined text-[18px] text-primary mt-0.5">check_circle</span>
                                <span>Contoh: masukkan <strong>150000</strong> untuk harga Rp 150.000</span>
                            </li>
                            <li class="flex items-start gap-2">
                                <span class="material-symbols-outlined text-[18px] text-primary mt-0.5">check_circle</span>
                                <span>Centang checkbox untuk menyertakan variant pada price setting ini</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        <div class="mb-4">
            <div class="flex items-center gap-2 mb-2 overflow-x-auto pb-1" id="parentCategoryTabs">
                <button type="button" class="parent-cat-tab px-4 py-1.5 rounded-full text-label-sm font-medium bg-primary text-white whitespace-nowrap" data-category="all" onclick="selectParentCategory('all')">Semua</button>
                @foreach($categories as $cat)
                    <button type="button" class="parent-cat-tab px-4 py-1.5 rounded-full text-label-sm font-medium bg-surface-container-low text-on-surface-variant hover:bg-surface-container whitespace-nowrap" data-category="{{ $cat->slug }}" onclick="selectParentCategory('{{ $cat->slug }}')">{{ $cat->name }}</button>
                @endforeach
            </div>
            <div class="flex items-center gap-2 overflow-x-auto pb-1 hidden" id="subCategoryTabs">
                @foreach($categories as $cat)
                    @if($cat->children->count() > 0)
                        <button type="button" class="sub-cat-tab px-3 py-1 rounded-full text-label-xs font-medium bg-surface-container-low text-on-surface-variant hover:bg-surface-container whitespace-nowrap" data-parent="{{ $cat->slug }}" data-sub="all" onclick="selectSubCategory('{{ $cat->slug }}', 'all')">Semua {{ $cat->name }}</button>
                        @foreach($cat->children as $child)
                            <button type="button" class="sub-cat-tab px-3 py-1 rounded-full text-label-xs font-medium bg-surface-container-low text-on-surface-variant hover:bg-surface-container whitespace-nowrap hidden" data-parent="{{ $cat->slug }}" data-sub="{{ $child->slug }}" onclick="selectSubCategory('{{ $cat->slug }}', '{{ $child->slug }}')">{{ $child->name }}</button>
                        @endforeach
                    @endif
                @endforeach
            </div>
        </div>
        <div id="productVariantGrid" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-5">
            @foreach($products as $product)
                @php $attrs = $product->getAttributes(); @endphp
                @php $displayPrice = ($attrs['price'] ?? 0) ?: ($attrs['base_price'] ?? 0); @endphp
                <div class="product-variant-card bg-surface-container-lowest rounded-xl border border-outline-variant overflow-hidden shadow-sm flex flex-col group transition-all hover:shadow-md" data-product-id="{{ $product->id }}" data-category="{{ $product->category->slug ?? 'uncategorized' }}">
                    <div class="p-3.5 flex items-center gap-3 border-b border-outline-variant/30">
                        <div class="w-10 h-10 rounded-md overflow-hidden bg-surface-gray flex-shrink-0 border border-outline-variant/30">
                            @if($product->images->isNotEmpty())
                                <img src="{{ $product->images->first()->url }}" alt="{{ $product->name }}" class="w-full h-full object-cover">
                            @else
                                <div class="w-full h-full flex items-center justify-center text-on-surface-variant">
                                    <span class="material-symbols-outlined text-[20px]">image</span>
                                </div>
                            @endif
                        </div>
                        <div class="flex-1 min-w-0">
                            <h4 class="font-body-md text-body-md text-on-surface font-semibold truncate block">{{ $product->name }}</h4>
                            <p class="text-label-xs text-on-surface-variant">Rp{{ number_format($displayPrice, 2, ',', '.') }}</p>
                        </div>
                    </div>
                    <div class="p-3 space-y-1">
                        @php $hasStock = false; @endphp
                        @foreach($product->variants as $variant)
                            @php $vAttrs = $variant->getAttributes(); @endphp
                            @php $vp = ($vAttrs['price'] ?? 0) ?: ($attrs['price'] ?? 0) ?: ($attrs['base_price'] ?? 0); @endphp
                            @php $stock = $variant->stock_qty ?? 0; @endphp
                            @php $hasStock = $hasStock || $stock > 0; @endphp
                            @php $disabled = $stock <= 0 ? 'disabled' : ''; @endphp
                            <div class="variant-row py-1.5 px-2 rounded-md {{ $stock > 0 ? 'hover:bg-surface-container-low' : 'opacity-50' }} transition-colors">
                                <div class="flex items-start gap-2">
                                    <label class="flex items-center flex-shrink-0 pt-0.5">
                                        <input type="checkbox" class="variant-checkbox w-3.5 h-3.5 rounded border-outline-variant text-primary focus:ring-primary/30" value="{{ $variant->id }}" name="variant_ids[]" {{ $disabled }}>
                                    </label>
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center justify-between gap-2">
                                            <span class="text-label-sm text-on-surface font-medium truncate block leading-tight {{ $stock <= 0 ? 'text-on-surface-variant' : '' }}">{{ $variant->variant_name ?? ($variant->sku ?? 'Default') }}</span>
                                        </div>
                                        <div class="flex items-center gap-2 mt-1">
                                            <div class="relative w-52">
                                                <span class="absolute left-1.5 top-1/2 -translate-y-1/2 text-on-surface-variant text-[10px] font-bold">Rp</span>
                                                <input type="text" class="variant-price-input w-full pl-6 pr-1 py-1 text-body-xs text-right {{ $stock <= 0 ? 'bg-surface-container-high cursor-not-allowed' : 'bg-transparent border-b border-outline-variant focus:border-brand-gold' }}" name="variant_prices[{{ $variant->id }}]" value="{{ $vp }}" data-original="{{ $vp }}" oninput="updateDiscountInfo(this)" {{ $disabled }}>
                                            </div>
                                            <label class="discount-info text-[11px] text-on-surface-variant whitespace-nowrap w-44 text-right" data-price="{{ $vp }}">
                                                <span class="discount-text">→Rp{{ number_format($vp, 2, ',', '.') }}</span>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                        @if($product->variants->count() == 0)
                            <p class="text-label-xs text-on-surface-variant text-center py-2">Tidak ada variants</p>
                        @endif
                        @if(!$hasStock && $product->variants->count() > 0)
                            <p class="text-label-xs text-danger text-center py-1">Semua variants stok habis</p>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <div class="sticky bottom-0 border-t border-outline-variant flex justify-between items-center px-8 py-4 z-30 shadow-[0_-4px_10px_rgba(0,0,0,0.05)]">
        <div class="flex items-center gap-4">
            <span class="text-label-md font-label-md text-on-surface-variant">Selected: <span class="text-primary font-bold" id="selectedCount">0</span> variants</span>
        </div>
        <div class="flex gap-4">
            <a href="{{ route('price-settings.index') }}" class="px-8 py-3 border border-outline-variant text-primary font-bold rounded-lg hover:bg-surface-container transition-colors">Cancel</a>
            <button type="submit" class="px-10 py-3 bg-primary text-white rounded-lg font-label-md hover:opacity-90 transition-all shadow-sm">Save Price Setting</button>
        </div>
    </div>
</form>
@endsection

@push('scripts')
<script>
function toggleInfo() {
    const info = document.getElementById('inputInfo');
    info.classList.toggle('hidden');
}

document.addEventListener('click', function(e) {
    const info = document.getElementById('inputInfo');
    const btn = e.target.closest('button[onclick="toggleInfo()"]');
    if (!btn && !info.contains(e.target)) {
        info.classList.add('hidden');
    }
});

function updateSelectedCount() {
    const selected = document.querySelectorAll('.variant-checkbox:checked').length;
    document.getElementById('selectedCount').textContent = selected;
}

function getDiscountConfig() {
    const discountType = parseFloat(document.querySelector('select[name="discount_type"]')?.value) || 1;
    const discountValue = parseFloat(document.querySelector('input[name="discount_value"]')?.value) || 0;
    return { discountType, discountValue };
}

function calcDiscountedPrice(originalPrice, discountType, discountValue) {
    if (originalPrice <= 0 || discountValue <= 0) return originalPrice;
    let discounted = originalPrice;
    if (discountType == 1) {
        discounted = originalPrice - (originalPrice * discountValue / 100);
    } else if (discountType == 2) {
        discounted = originalPrice - discountValue;
        if (discounted < 0) discounted = 0;
    }
    return Math.round(discounted);
}

function parsePrice(str) {
    if (str.includes(',')) {
        let cleaned = str.replace(/\./g, '').replace(',', '.');
        return parseFloat(cleaned) || 0;
    }
    return parseFloat(str) || 0;
}

function updateDiscountInfo(input) {
    const row = input.closest('.variant-row');
    const discountEl = row?.querySelector('.discount-info');
    if (!discountEl) return;
    const currentPrice = parsePrice(input.value);
    const { discountType, discountValue } = getDiscountConfig();
    const discounted = calcDiscountedPrice(currentPrice, discountType, discountValue);
    const priceEl = discountEl.querySelector('.discount-text');
    if (priceEl) {
        priceEl.textContent = '→Rp' + discounted.toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }
}

function recalcAllDiscounts() {
    document.querySelectorAll('.variant-price-input').forEach(function(input) {
        updateDiscountInfo(input);
    });
}

function selectParentCategory(slug) {
    document.querySelectorAll('.parent-cat-tab').forEach(function(btn) {
        btn.classList.remove('bg-primary', 'text-white');
        btn.classList.add('bg-surface-container-low', 'text-on-surface-variant');
    });
    const activeBtn = document.querySelector('.parent-cat-tab[data-category="' + slug + '"]');
    if (activeBtn) {
        activeBtn.classList.remove('bg-surface-container-low', 'text-on-surface-variant');
        activeBtn.classList.add('bg-primary', 'text-white');
    }

    document.querySelectorAll('.sub-cat-tab').forEach(function(btn) {
        if (btn.dataset.parent === slug) {
            btn.classList.remove('hidden');
        } else {
            btn.classList.add('hidden');
        }
    });

    const subTabsContainer = document.getElementById('subCategoryTabs');
    if (slug === 'all') {
        subTabsContainer.classList.add('hidden');
    } else {
        subTabsContainer.classList.remove('hidden');
    }

    filterByCategory(slug, 'all');
}

function selectSubCategory(parentSlug, subSlug) {
    document.querySelectorAll('.sub-cat-tab[data-parent="' + parentSlug + '"]').forEach(function(btn) {
        btn.classList.remove('bg-primary', 'text-white');
        btn.classList.add('bg-surface-container-low', 'text-on-surface-variant');
    });
    const activeBtn = document.querySelector('.sub-cat-tab[data-parent="' + parentSlug + '"][data-sub="' + subSlug + '"]');
    if (activeBtn) {
        activeBtn.classList.remove('bg-surface-container-low', 'text-on-surface-variant');
        activeBtn.classList.add('bg-primary', 'text-white');
    }

    filterByCategory(parentSlug, subSlug);
}

function filterByCategory(parentSlug, subSlug) {
    const cards = document.querySelectorAll('.product-variant-card');
    cards.forEach(function(card) {
        const cardCat = card.dataset.category;
        if (parentSlug === 'all') {
            card.style.display = 'flex';
        } else if (subSlug === 'all') {
            card.style.display = cardCat === parentSlug ? 'flex' : 'none';
        } else {
            card.style.display = cardCat === subSlug ? 'flex' : 'none';
        }
    });
}

function filterProducts(query) {
    const cards = document.querySelectorAll('.product-variant-card');
    const q = query.toLowerCase();
    cards.forEach(function(card) {
        const name = card.querySelector('h4').textContent.toLowerCase();
        if (name.includes(q)) {
            card.style.display = 'flex';
        } else {
            card.style.display = 'none';
        }
    });
}

document.getElementById('productSearch').addEventListener('input', function() {
    filterProducts(this.value);
});

document.querySelectorAll('.variant-checkbox').forEach(function(cb) {
    cb.addEventListener('change', updateSelectedCount);
});

document.querySelectorAll('select[name="discount_type"], input[name="discount_value"]').forEach(function(el) {
    el.addEventListener('change', recalcAllDiscounts);
    el.addEventListener('input', recalcAllDiscounts);
});

function syncDiscountInput() {
    const type = document.querySelector('select[name="discount_type"]').value;
    const input = document.getElementById('discountValueInput');
    if (type == '1') {
        input.max = 100;
        input.placeholder = '0-100';
        input.step = '1';
    } else {
        input.max = '';
        input.placeholder = 'Nominal (Rp)';
        input.step = '0.01';
    }
}

document.querySelector('select[name="discount_type"]').addEventListener('change', function() {
    syncDiscountInput();
    recalcAllDiscounts();
});

syncDiscountInput();
recalcAllDiscounts();

function toggleVolumeTierSection() {
    const type = document.querySelector('select[name="type"]').value;
    const section = document.getElementById('volumeTierSection');
    if (type == '2') {
        section.classList.remove('hidden');
    } else {
        section.classList.add('hidden');
    }
}

function addVolumeTier(minPurchase, discountType, discountValue) {
    const list = document.getElementById('volumeTierList');
    const index = list.children.length;
    const row = document.createElement('div');
    row.className = 'flex items-center gap-2 p-3 rounded-lg bg-surface-container-low border border-outline-variant/60';
    row.setAttribute('data-tier-index', index);
    row.innerHTML = `
        <div class="flex items-center gap-2 flex-1">
            <span class="text-label-sm text-on-surface-variant whitespace-nowrap">Min Qty:</span>
            <input type="number" name="volume_tiers[${index}][min_purchase]" class="w-20 px-2 py-1 border border-outline-variant rounded text-body-xs text-center" value="${minPurchase || ''}" placeholder="0" min="0">
            <span class="text-label-sm text-on-surface-variant whitespace-nowrap">Diskon:</span>
            <select name="volume_tiers[${index}][discount_type]" class="px-2 py-1 border border-outline-variant rounded text-body-xs">
                <option value="1" ${discountType == '1' || !discountType ? 'selected' : ''}>%</option>
                <option value="2" ${discountType == '2' ? 'selected' : ''}>Rp</option>
            </select>
            <input type="number" name="volume_tiers[${index}][discount_value]" class="w-28 px-2 py-1 border border-outline-variant rounded text-body-xs text-right" value="${discountValue || ''}" placeholder="Nilai diskon" min="0">
            <span class="text-label-xs text-on-surface-variant">→</span>
            <span class="tier-result text-label-xs text-primary font-medium whitespace-nowrap">Harga setelah diskon</span>
        </div>
        <button type="button" class="text-on-surface-variant hover:text-danger transition-colors p-1" onclick="removeVolumeTier(this)">
            <span class="material-symbols-outlined text-[18px]">delete</span>
        </button>
    `;
    list.appendChild(row);
}

function removeVolumeTier(btn) {
    btn.closest('[data-tier-index]').remove();
}

document.querySelector('select[name="type"]').addEventListener('change', toggleVolumeTierSection);

toggleVolumeTierSection();
</script>
@endpush
