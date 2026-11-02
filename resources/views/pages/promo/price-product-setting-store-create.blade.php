@extends('layouts.app')

@section('title', 'Create Store Pricing')

@section('content')
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="font-headline-lg text-headline-lg text-on-surface">Create Store Pricing</h1>
            <nav class="flex items-center gap-2 text-body-md text-on-surface-variant mt-1">
                <a href="{{ route('dashboard') }}" class="text-primary hover:underline">Dashboard</a>
                <span class="material-symbols-outlined text-[16px]">chevron_right</span>
                <a href="{{ route('price-product-setting-store.index') }}" class="text-primary hover:underline">Store Pricing</a>
                <span class="material-symbols-outlined text-[16px]">chevron_right</span>
                <span>Create</span>
            </nav>
        </div>
        <a href="{{ route('price-product-setting-store.index') }}" class="flex items-center gap-2 px-4 py-2 bg-surface-container-lowest border border-outline-variant text-secondary rounded-lg font-label-md hover:bg-surface-container transition-all">
            <span class="material-symbols-outlined text-[18px]">arrow_back</span> Back
        </a>
    </div>

    @include('layouts.partials.promotions-submenu')

    <form method="POST" action="{{ route('price-product-setting-store.store') }}">
        @csrf
        <div class="bg-white rounded-xl shadow-sm border border-outline-variant/30 p-6 mb-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="space-y-1.5">
                    <label class="block text-label-sm font-medium text-on-surface-variant">Store / Mitra Toko <span class="text-danger">*</span></label>
                    <select name="store_id" id="storeSelectInput" class="w-full px-3 py-2 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary/20 focus:outline-none bg-white select2-enable" required onchange="onStoreChange(this.value)">
                        <option value="">Pilih Store</option>
                        @foreach($stores as $store)
                            <option value="{{ $store->id }}" {{ old('store_id') == $store->id ? 'selected' : '' }}>
                                {{ $store->name }} ({{ $store->code }})
                            </option>
                        @endforeach
                    </select>
                    @error('store_id')<p class="text-danger text-sm">{{ $message }}</p>@enderror
                </div>
                <div class="space-y-1.5">
                    <label class="block text-label-sm font-medium text-on-surface-variant">Program Promo / Setting <span class="text-danger">*</span></label>
                    <select name="price_product_setting_id" id="promoSelectInput" class="w-full px-3 py-2 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary/20 focus:outline-none bg-white select2-enable" required onchange="onPromoChange(this.value)">
                        <option value="">Pilih Program Promo</option>
                        @foreach($priceSettings as $ps)
                            @php
                                $badge = match((int)$ps->scope_store_type) {
                                    1 => '[Tier]',
                                    2 => '[Khusus Toko]',
                                    3 => '[Channel Group]',
                                    default => '[Semua Toko]'
                                };
                            @endphp
                            <option value="{{ $ps->id }}" {{ old('price_product_setting_id') == $ps->id ? 'selected' : '' }}>
                                {{ $badge }} {{ $ps->title }} ({{ $ps->code }}) - {{ (int)$ps->discount_type === 1 ? (float)$ps->discount_value.'%' : 'Rp '.number_format((float)$ps->discount_value, 0, ',', '.') }}
                            </option>
                        @endforeach
                    </select>
                    @error('price_product_setting_id')<p class="text-danger text-sm">{{ $message }}</p>@enderror
                </div>
                <div class="space-y-1.5">
                    <label class="block text-label-sm font-medium text-on-surface-variant">Target Pricing <span class="text-danger">*</span></label>
                    <select name="target_type" id="targetType" class="w-full px-3 py-2 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary/20 focus:outline-none bg-white" onchange="toggleTargetSelect()">
                        <option value="product" {{ old('target_type', 'product') == 'product' ? 'selected' : '' }}>Produk (Induk)</option>
                        <option value="variant" {{ old('target_type', 'product') == 'variant' ? 'selected' : '' }}>Varian Spesifik</option>
                    </select>
                </div>
            </div>

            <div class="space-y-1.5 mt-4" id="productSelect">
                <label class="block text-label-sm font-medium text-on-surface-variant">Pilih Produk <span class="text-danger">*</span></label>
                <div class="relative">
                    <input type="text" id="productSearchInput" placeholder="Ketik nama produk untuk mencari..." autocomplete="off" value="{{ $products->firstWhere('id', old('product_id'))?->name ?? '' }}" class="w-full px-3 py-2 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary/20 focus:outline-none bg-white">
                    <input type="hidden" name="product_id" id="productIdInput" value="{{ old('product_id') }}">
                    <div id="productDropdown" class="hidden absolute z-50 w-full bg-white border border-outline-variant rounded-lg shadow-lg mt-1 max-h-60 overflow-y-auto"></div>
                </div>
                @error('product_id')<p class="text-danger text-sm">{{ $message }}</p>@enderror
            </div>

            <div class="space-y-1.5 mt-4 hidden" id="variantSelect">
                <label class="block text-label-sm font-medium text-on-surface-variant">Pilih Varian <span class="text-danger">*</span></label>
                <select name="variant_id" id="variantSelectInput" class="w-full px-3 py-2 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary/20 focus:outline-none bg-white select2-enable" onchange="onVariantChange(this.value)">
                    <option value="">Pilih Varian</option>
                    @foreach($products as $product)
                        @foreach($product->variants as $variant)
                            @php
                                $vPrice = $variant->sell_price > 0 ? $variant->sell_price : ($variant->base_price > 0 ? $variant->base_price : $product->base_price);
                            @endphp
                            <option value="{{ $variant->id }}" {{ old('variant_id') == $variant->id ? 'selected' : '' }} data-product-id="{{ $product->id }}" data-price="{{ $vPrice }}" data-name="{{ $product->name }} - {{ $variant->variant_name ?? $variant->sku }}">
                                {{ $product->name }} - {{ $variant->variant_name ?? $variant->sku }} (Rp {{ number_format($vPrice, 0, ',', '.') }})
                            </option>
                        @endforeach
                    @endforeach
                </select>
                @error('variant_id')<p class="text-danger text-sm">{{ $message }}</p>@enderror
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-outline-variant/30 p-6 mb-6">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="font-headline-md text-headline-md text-on-surface">Penyesuaian Harga (Rowset Adjustments)</h3>
                    <p class="text-label-sm text-on-surface-variant mt-0.5">Baris 0 adalah harga dasar toko. Baris selanjutnya adalah diskon atau penyesuaian promo khusus.</p>
                </div>
                <button type="button" class="px-4 py-2 bg-surface-container-high text-primary font-medium rounded-lg hover:bg-surface-variant transition-colors text-label-sm flex items-center gap-1.5" onclick="addAdjustmentRow('', '', 0)">
                    <span class="material-symbols-outlined text-[16px]">add</span> Tambah Baris
                </button>
            </div>
            <div id="adjustmentList" class="space-y-2.5">
                <!-- Rows added dynamically -->
            </div>
        </div>

        <div class="sticky bottom-0 bg-white/95 backdrop-blur-sm border-t border-outline-variant flex flex-col md:flex-row justify-between items-center px-6 py-4 z-30 shadow-[0_-4px_10px_rgba(0,0,0,0.06)] gap-4">
            <div class="flex items-center gap-5 flex-wrap text-sm">
                <span class="text-on-surface-variant">Baris: <strong class="text-primary" id="rowCount">0</strong></span>
                <span class="text-on-surface-variant border-l border-slate-200 pl-4">Harga Dasar: <strong class="text-slate-800 font-semibold" id="summaryBasePrice">Rp 0</strong></span>
                <span class="text-on-surface-variant border-l border-slate-200 pl-4">Penyesuaian Promo: <strong class="text-rose-600 font-semibold" id="summaryAdjustments">Rp 0</strong></span>
                <span class="text-on-surface-variant border-l border-slate-200 pl-4">Harga Akhir Toko: <strong class="text-emerald-700 font-bold text-base" id="summaryFinalPrice">Rp 0</strong></span>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('price-product-setting-store.index') }}" class="px-6 py-2.5 border border-outline-variant text-slate-700 font-semibold rounded-lg hover:bg-surface-container transition-colors text-sm">Batal</a>
                <button type="submit" class="px-8 py-2.5 bg-primary text-white rounded-lg font-label-md font-semibold hover:opacity-90 transition-all shadow-sm text-sm">Simpan Store Pricing</button>
            </div>
        </div>
    </form>
@endsection

@push('scripts')
<script>
const storesData = {!! json_encode($storesJson ?? []) !!};
const promosData = {!! json_encode($promosJson ?? []) !!};
const productsData = {!! json_encode($productsJson ?? []) !!};

function toggleTargetSelect() {
    const type = document.getElementById('targetType').value;
    const productDiv = document.getElementById('productSelect');
    const variantDiv = document.getElementById('variantSelect');
    const productSearchInput = document.getElementById('productSearchInput');
    const productIdInput = document.getElementById('productIdInput');
    const variantSelect = document.getElementById('variantSelectInput');

    if (type === 'product') {
        productDiv.classList.remove('hidden');
        variantDiv.classList.add('hidden');
        if (variantSelect) variantSelect.value = '';
    } else {
        productDiv.classList.add('hidden');
        variantDiv.classList.remove('hidden');
        if (productIdInput) productIdInput.value = '';
        if (productSearchInput) productSearchInput.value = '';
        const dropdown = document.getElementById('productDropdown');
        if (dropdown) dropdown.classList.add('hidden');
    }
}

function addAdjustmentRow(name, desc, amount) {
    const list = document.getElementById('adjustmentList');
    const index = list.children.length;
    const row = document.createElement('div');
    row.className = 'flex items-center gap-2 p-3 rounded-lg bg-surface-container-low border border-outline-variant/60';
    row.setAttribute('data-row-index', index);
    row.innerHTML = `
        <span class="text-label-sm font-mono text-on-surface-variant w-8 text-center">${index}</span>
        <input type="text" name="adjustments[${index}][adjustment_name]" class="w-32 px-3 py-1.5 border border-outline-variant rounded-lg text-body-xs font-mono font-medium uppercase" value="${name || ''}" placeholder="KODE">
        <input type="text" name="adjustments[${index}][adjustment_name_desc]" class="flex-1 px-3 py-1.5 border border-outline-variant rounded-lg text-body-xs" value="${desc || (index === 0 ? 'Harga Dasar Toko' : 'Diskon / Potongan Toko')}" placeholder="Keterangan Penyesuaian">
        <div class="relative w-48">
            <span class="absolute left-2.5 top-1/2 -translate-y-1/2 text-xs text-slate-400 font-medium">Rp</span>
            <input type="number" name="adjustments[${index}][adjustment_amount]" class="w-full pl-8 pr-3 py-1.5 border border-outline-variant rounded-lg text-body-xs text-right font-mono font-semibold" value="${amount !== undefined ? amount : 0}" placeholder="0" step="0.01" oninput="recalculateSummary()">
        </div>
        <button type="button" class="text-on-surface-variant hover:text-danger transition-colors p-1.5 rounded-lg hover:bg-rose-50" onclick="removeAdjustmentRow(this)">
            <span class="material-symbols-outlined text-[18px]">delete</span>
        </button>
    `;
    list.appendChild(row);
    recalculateSummary();
}

function removeAdjustmentRow(btn) {
    const row = btn.closest('[data-row-index]');
    row.remove();
    reindexRows();
    recalculateSummary();
}

function reindexRows() {
    const list = document.getElementById('adjustmentList');
    Array.from(list.children).forEach((row, idx) => {
        row.setAttribute('data-row-index', idx);
        row.querySelector('span').textContent = idx;
        row.querySelector('input[name*="adjustment_name"]').name = `adjustments[${idx}][adjustment_name]`;
        row.querySelector('input[name*="adjustment_name_desc"]').name = `adjustments[${idx}][adjustment_name_desc]`;
        row.querySelector('input[name*="adjustment_amount"]').name = `adjustments[${idx}][adjustment_amount]`;
    });
}

function recalculateSummary() {
    const list = document.getElementById('adjustmentList');
    const rows = list.querySelectorAll('[data-row-index]');
    let basePrice = 0;
    let totalAdjustments = 0;
    let finalPrice = 0;

    rows.forEach((row, idx) => {
        const amtInput = row.querySelector('input[name*="adjustment_amount"]');
        const val = parseFloat(amtInput?.value || 0) || 0;
        if (idx === 0) {
            basePrice = val;
        } else {
            totalAdjustments += val;
        }
        finalPrice += val;
    });

    finalPrice = Math.max(0, finalPrice);

    const rowCountEl = document.getElementById('rowCount');
    const basePriceEl = document.getElementById('summaryBasePrice');
    const adjEl = document.getElementById('summaryAdjustments');
    const finalPriceEl = document.getElementById('summaryFinalPrice');

    if (rowCountEl) rowCountEl.textContent = rows.length;
    if (basePriceEl) basePriceEl.textContent = 'Rp ' + Math.round(basePrice).toLocaleString('id-ID');
    if (adjEl) {
        adjEl.textContent = (totalAdjustments < 0 ? '-' : '+') + 'Rp ' + Math.abs(Math.round(totalAdjustments)).toLocaleString('id-ID');
        adjEl.className = totalAdjustments < 0 ? 'text-rose-600 font-semibold' : 'text-slate-600 font-semibold';
    }
    if (finalPriceEl) finalPriceEl.textContent = 'Rp ' + Math.round(finalPrice).toLocaleString('id-ID');
}

function applyPromoToAdjustments() {
    const promoId = document.getElementById('promoSelectInput').value;
    const promo = promosData[promoId];
    if (!promo) return;

    const list = document.getElementById('adjustmentList');
    const row0 = list.querySelector('[data-row-index="0"]');
    if (!row0) return;
    const baseAmtInput = row0.querySelector('input[name*="adjustment_amount"]');
    const basePrice = parseFloat(baseAmtInput?.value || 0) || 0;

    let discountAmt = 0;
    if (parseInt(promo.discount_type) === 1) {
        discountAmt = Math.round(basePrice * (parseFloat(promo.discount_value) / 100));
    } else {
        discountAmt = Math.min(basePrice, parseFloat(promo.discount_value) || 0);
    }

    let row1 = list.querySelector('[data-row-index="1"]');
    if (!row1) {
        addAdjustmentRow(promo.code, 'Diskon Promo: ' + promo.title, -discountAmt);
    } else {
        const nameInput = row1.querySelector('input[name*="adjustment_name"]');
        const descInput = row1.querySelector('input[name*="adjustment_name_desc"]');
        const amtInput = row1.querySelector('input[name*="adjustment_amount"]');
        if (nameInput) nameInput.value = promo.code;
        if (descInput) descInput.value = 'Diskon Promo: ' + promo.title;
        if (amtInput) amtInput.value = -discountAmt;
        recalculateSummary();
    }
}

function setBasePriceFromProduct(price, name) {
    const list = document.getElementById('adjustmentList');
    let row0 = list.querySelector('[data-row-index="0"]');
    if (!row0) {
        addAdjustmentRow('BASE', 'Harga Dasar: ' + name, price);
    } else {
        const nameInput = row0.querySelector('input[name*="adjustment_name"]');
        const descInput = row0.querySelector('input[name*="adjustment_name_desc"]');
        const amtInput = row0.querySelector('input[name*="adjustment_amount"]');
        if (nameInput) nameInput.value = 'BASE';
        if (descInput) descInput.value = 'Harga Dasar: ' + name;
        if (amtInput) amtInput.value = price;
    }
    applyPromoToAdjustments();
    recalculateSummary();
}

function onStoreChange(storeId) {
    const promoSelect = document.getElementById('promoSelectInput');
    const store = storesData[storeId];
    if (!promoSelect) return;

    Array.from(promoSelect.options).forEach(opt => {
        if (!opt.value) return;
        const p = promosData[opt.value];
        if (!p) return;

        let badge = '[Semua Toko]';
        if (parseInt(p.scope_store_type) === 2) {
            badge = (store && p.scope_store_id === store.id) ? '[Khusus Toko Ini]' : '[Khusus Toko Lain]';
        } else if (parseInt(p.scope_store_type) === 1) {
            badge = (store && p.scope_store_id === store.tier_id) ? '[Tier Toko Ini]' : '[Tier Lain]';
        } else if (parseInt(p.scope_store_type) === 3) {
            badge = (store && p.scope_store_id === store.store_group_id) ? '[Group Toko Ini]' : '[Group Lain]';
        }

        const discStr = parseInt(p.discount_type) === 1 ? p.discount_value + '%' : 'Rp ' + Number(p.discount_value).toLocaleString('id-ID');
        opt.textContent = `${badge} ${p.title} (${p.code}) - ${discStr}`;
    });
}

function onPromoChange(promoId) {
    const promo = promosData[promoId];
    if (promo && parseInt(promo.scope_store_type) === 2 && promo.scope_store_id) {
        const storeSelect = document.getElementById('storeSelectInput');
        if (storeSelect && !storeSelect.value) {
            storeSelect.value = promo.scope_store_id;
            onStoreChange(promo.scope_store_id);
        }
    }
    applyPromoToAdjustments();
}

function onVariantChange(variantId) {
    const variantSelect = document.getElementById('variantSelectInput');
    const selectedOption = variantSelect?.options[variantSelect.selectedIndex];
    if (!selectedOption || !variantId) return;

    const price = parseFloat(selectedOption.dataset.price || 0);
    const name = selectedOption.dataset.name || 'Varian';
    setBasePriceFromProduct(price, name);
}

// Product search dropdown
const productSearchInput = document.getElementById('productSearchInput');
const productDropdown = document.getElementById('productDropdown');
const productIdInput = document.getElementById('productIdInput');

function renderProductDropdown(items) {
    productDropdown.innerHTML = '';
    if (items.length === 0) {
        productDropdown.classList.add('hidden');
        return;
    }
    items.forEach(p => {
        const div = document.createElement('div');
        div.className = 'px-3 py-2.5 hover:bg-surface-container-low cursor-pointer text-body-sm text-on-surface border-b border-slate-100 last:border-b-0 flex justify-between items-center';
        div.innerHTML = `<span>${p.name}</span> <span class="text-xs font-mono font-semibold text-slate-500">Rp ${Number(p.base_price).toLocaleString('id-ID')}</span>`;
        div.addEventListener('click', function() {
            productIdInput.value = p.id;
            productSearchInput.value = p.name;
            productDropdown.classList.add('hidden');
            setBasePriceFromProduct(p.base_price, p.name);
            filterVariants(p.id);
        });
        productDropdown.appendChild(div);
    });
    productDropdown.classList.remove('hidden');
}

productSearchInput.addEventListener('input', function() {
    const query = this.value.toLowerCase().trim();
    if (query.length < 2) {
        productDropdown.classList.add('hidden');
        return;
    }
    const allProducts = Object.values(productsData);
    const filtered = allProducts.filter(p => p.name.toLowerCase().includes(query));
    renderProductDropdown(filtered);
});

productSearchInput.addEventListener('focus', function() {
    if (this.value.trim().length >= 2) {
        const query = this.value.toLowerCase().trim();
        const allProducts = Object.values(productsData);
        const filtered = allProducts.filter(p => p.name.toLowerCase().includes(query));
        renderProductDropdown(filtered);
    }
});

document.addEventListener('click', function(e) {
    if (!productSearchInput.contains(e.target) && !productDropdown.contains(e.target)) {
        productDropdown.classList.add('hidden');
    }
});

function filterVariants(productId) {
    const variantSelect = document.getElementById('variantSelectInput');
    if (!variantSelect) return;
    const options = variantSelect.querySelectorAll('option');
    options.forEach(opt => { opt.style.display = ''; });
    if (!productId) return;
    options.forEach(opt => {
        if (opt.dataset.productId && opt.dataset.productId !== String(productId)) {
            opt.style.display = 'none';
        }
    });
}

// Initial default row
addAdjustmentRow('BASE', 'Harga Dasar Toko', 0);
</script>
@endpush
