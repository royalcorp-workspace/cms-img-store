@extends('layouts.app')

@section('title', 'Edit Store Pricing')

@section('content')
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="font-headline-lg text-headline-lg text-on-surface">Edit Store Pricing</h1>
            <nav class="flex items-center gap-2 text-body-md text-on-surface-variant mt-1">
                <a href="{{ route('dashboard') }}" class="text-primary hover:underline">Dashboard</a>
                <span class="material-symbols-outlined text-[16px]">chevron_right</span>
                <a href="{{ route('price-product-setting-store.index') }}" class="text-primary hover:underline">Store Pricing</a>
                <span class="material-symbols-outlined text-[16px]">chevron_right</span>
                <span>Edit</span>
            </nav>
        </div>
        <a href="{{ route('price-product-setting-store.index') }}" class="flex items-center gap-2 px-4 py-2 bg-surface-container-lowest border border-outline-variant text-secondary rounded-lg font-label-md hover:bg-surface-container transition-all">
            <span class="material-symbols-outlined text-[18px]">arrow_back</span> Back
        </a>
    </div>

    <form method="POST" action="{{ route('price-product-setting-store.update', $pricing->id) }}">
        @csrf
        @method('PUT')
        <div class="rounded-xl shadow-sm border border-outline-variant/30 p-6 mb-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="space-y-1.5">
                    <label class="block text-label-sm font-medium text-on-surface-variant">Store <span class="text-danger">*</span></label>
                    <select name="store_id" class="w-full px-3 py-2 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary/20 focus:outline-none bg-white" required>
                        <option value="">Select Store</option>
                        @foreach($stores as $store)
                            <option value="{{ $store->id }}" {{ old('store_id', $pricing->store_id) == $store->id ? 'selected' : '' }}>{{ $store->name }} ({{ $store->code }})</option>
                        @endforeach
                    </select>
                    @error('store_id')<p class="text-danger text-sm">{{ $message }}</p>@enderror
                </div>
                <div class="space-y-1.5">
                    <label class="block text-label-sm font-medium text-on-surface-variant">Target Type <span class="text-danger">*</span></label>
                    <select name="target_type" id="targetType" class="w-full px-3 py-2 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary/20 focus:outline-none bg-white" onchange="toggleTargetSelect()">
                        <option value="product" {{ old('target_type', $pricing->product_id ? 'product' : 'variant') == 'product' ? 'selected' : '' }}>Product</option>
                        <option value="variant" {{ old('target_type', $pricing->variant_id ? 'variant' : 'product') == 'variant' ? 'selected' : '' }}>Variant</option>
                    </select>
                </div>
            </div>

            <div class="space-y-1.5 mt-4" id="productSelect">
                <label class="block text-label-sm font-medium text-on-surface-variant">Product <span class="text-danger">*</span></label>
                <div class="relative">
                    <input type="text" id="productSearchInput" placeholder="Type product name..." autocomplete="off" value="{{ $products->firstWhere('id', old('product_id', $pricing->product_id))?->name ?? '' }}" class="w-full px-3 py-2 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary/20 focus:outline-none bg-white">
                    <input type="hidden" name="product_id" id="productIdInput" value="{{ old('product_id', $pricing->product_id) }}">
                    <div id="productDropdown" class="hidden absolute z-50 w-full bg-white border border-outline-variant rounded-lg shadow-lg mt-1 max-h-60 overflow-y-auto"></div>
                </div>
                @error('product_id')<p class="text-danger text-sm">{{ $message }}</p>@enderror
            </div>

            <div class="space-y-1.5 mt-4 {{ old('target_type', $pricing->variant_id ? 'variant' : 'product') == 'product' ? 'hidden' : '' }}" id="variantSelect">
                <label class="block text-label-sm font-medium text-on-surface-variant">Variant <span class="text-danger">*</span></label>
                <select name="variant_id" class="w-full px-3 py-2 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary/20 focus:outline-none bg-white">
                    <option value="">Select Variant</option>
                    @foreach($products as $product)
                        @foreach($product->variants as $variant)
                            <option value="{{ $variant->id }}" {{ old('variant_id', $pricing->variant_id) == $variant->id ? 'selected' : '' }} data-product-id="{{ $product->id }}">{{ $product->name }} - {{ $variant->variant_name ?? $variant->sku }}</option>
                        @endforeach
                    @endforeach
                </select>
                @error('variant_id')<p class="text-danger text-sm">{{ $message }}</p>@enderror
            </div>
        </div>

        <div class="rounded-xl shadow-sm border border-outline-variant/30 p-6 mb-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-headline-md text-headline-md text-on-surface">Adjustments (Rowset)</h3>
                <button type="button" class="px-4 py-2 bg-surface-container-high text-primary font-medium rounded-lg hover:bg-surface-variant transition-colors text-label-sm" onclick="addAdjustmentRow()">
                    <span class="material-symbols-outlined text-[16px] align-middle">add</span> Add Row
                </button>
            </div>
            <p class="text-label-sm text-on-surface-variant mb-4">Define rowset adjustments for this store. Row 0 is the base price, subsequent rows are discounts/additions.</p>
            <div id="adjustmentList" class="space-y-2">
                @php
                    $adjustments = old('adjustments', $pricing->adjustments ?? []);
                    if (empty($adjustments)) $adjustments = [['adjustment_name' => '', 'adjustment_name_desc' => 'Base Price', 'adjustment_amount' => 0]];
                @endphp
                @foreach($adjustments as $i => $adj)
                    <div class="flex items-center gap-2 p-3 rounded-lg bg-surface-container-low border border-outline-variant/60" data-row-index="{{ $i }}">
                        <span class="text-label-sm text-on-surface-variant w-8">{{ $i }}</span>
                        <input type="text" name="adjustments[{{ $i }}][adjustment_name]" class="w-24 px-2 py-1 border border-outline-variant rounded text-body-xs" value="{{ $adj['adjustment_name'] ?? '' }}" placeholder="Code">
                        <input type="text" name="adjustments[{{ $i }}][adjustment_name_desc]" class="flex-1 px-2 py-1 border border-outline-variant rounded text-body-xs" value="{{ $adj['adjustment_name_desc'] ?? '' }}" placeholder="Description">
                        <input type="number" name="adjustments[{{ $i }}][adjustment_amount]" class="w-40 px-2 py-1 border border-outline-variant rounded text-body-xs text-right" value="{{ $adj['adjustment_amount'] ?? 0 }}" placeholder="Amount" step="0.01">
                        <button type="button" class="text-on-surface-variant hover:text-danger transition-colors p-1" onclick="removeAdjustmentRow(this)">
                            <span class="material-symbols-outlined text-[18px]">delete</span>
                        </button>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="sticky bottom-0 border-t border-outline-variant flex justify-between items-center px-8 py-4 z-30 shadow-[0_-4px_10px_rgba(0,0,0,0.05)]">
            <div class="flex items-center gap-4">
                <span class="text-label-md font-label-md text-on-surface-variant">Rows: <span class="text-primary font-bold" id="rowCount">0</span></span>
            </div>
            <div class="flex gap-4">
                <a href="{{ route('price-product-setting-store.index') }}" class="px-8 py-3 border border-outline-variant text-primary font-bold rounded-lg hover:bg-surface-container transition-colors">Cancel</a>
                <button type="submit" class="px-10 py-3 bg-primary text-white rounded-lg font-label-md hover:opacity-90 transition-all shadow-sm">Update Store Pricing</button>
            </div>
        </div>
    </form>
@endsection

@push('scripts')
<script>
function toggleTargetSelect() {
    const type = document.getElementById('targetType').value;
    const productDiv = document.getElementById('productSelect');
    const variantDiv = document.getElementById('variantSelect');
    const productSearchInput = document.getElementById('productSearchInput');
    const productIdInput = document.getElementById('productIdInput');
    const variantSelect = document.querySelector('select[name="variant_id"]');
    if (type === 'product') {
        productDiv.classList.remove('hidden');
        variantDiv.classList.add('hidden');
        if (variantSelect) variantSelect.value = '';
        if (productIdInput && !productIdInput.value && productSearchInput) {
            productSearchInput.value = '';
        }
    } else {
        productDiv.classList.add('hidden');
        variantDiv.classList.remove('hidden');
        if (productIdInput) productIdInput.value = '';
        if (productSearchInput) productSearchInput.value = '';
        const dropdown = document.getElementById('productDropdown');
        if (dropdown) dropdown.classList.add('hidden');
        if (variantSelect) {
            Array.from(variantSelect.options).forEach(opt => { opt.style.display = ''; });
        }
    }
}

function addAdjustmentRow(name, desc, amount) {
    const list = document.getElementById('adjustmentList');
    const index = list.children.length;
    const row = document.createElement('div');
    row.className = 'flex items-center gap-2 p-3 rounded-lg bg-surface-container-low border border-outline-variant/60';
    row.setAttribute('data-row-index', index);
    row.innerHTML = `
        <span class="text-label-sm text-on-surface-variant w-8">${index}</span>
        <input type="text" name="adjustments[${index}][adjustment_name]" class="w-24 px-2 py-1 border border-outline-variant rounded text-body-xs" value="${name || ''}" placeholder="Code">
        <input type="text" name="adjustments[${index}][adjustment_name_desc]" class="flex-1 px-2 py-1 border border-outline-variant rounded text-body-xs" value="${desc || 'Base Price'}" placeholder="Description">
        <input type="number" name="adjustments[${index}][adjustment_amount]" class="w-40 px-2 py-1 border border-outline-variant rounded text-body-xs text-right" value="${amount || 0}" placeholder="Amount" step="0.01">
        <button type="button" class="text-on-surface-variant hover:text-danger transition-colors p-1" onclick="removeAdjustmentRow(this)">
            <span class="material-symbols-outlined text-[18px]">delete</span>
        </button>
    `;
    list.appendChild(row);
    updateRowCount();
}

function removeAdjustmentRow(btn) {
    const row = btn.closest('[data-row-index]');
    if (document.getElementById('adjustmentList').children.length <= 1) {
        alert('At least one adjustment row is required');
        return;
    }
    row.remove();
    reindexRows();
    updateRowCount();
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

function updateRowCount() {
    document.getElementById('rowCount').textContent = document.getElementById('adjustmentList').children.length;
}

updateRowCount();

const productData = @json($products->map(fn($p) => ['id' => $p->id, 'name' => $p->name]));
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
        div.className = 'px-3 py-2 hover:bg-surface-container-low cursor-pointer text-body-sm text-on-surface';
        div.textContent = p.name;
        div.addEventListener('click', function() {
            productIdInput.value = p.id;
            productSearchInput.value = p.name;
            productDropdown.classList.add('hidden');
            filterVariants(p.id);
        });
        productDropdown.appendChild(div);
    });
    productDropdown.classList.remove('hidden');
}

productSearchInput.addEventListener('input', function() {
    const query = this.value.toLowerCase();
    if (query.length < 2) {
        productDropdown.classList.add('hidden');
        return;
    }
    const filtered = productData.filter(p => p.name.toLowerCase().includes(query));
    renderProductDropdown(filtered);
});

productSearchInput.addEventListener('focus', function() {
    if (this.value.length >= 2) {
        const query = this.value.toLowerCase();
        const filtered = productData.filter(p => p.name.toLowerCase().includes(query));
        renderProductDropdown(filtered);
    }
});

document.addEventListener('click', function(e) {
    if (!productSearchInput.contains(e.target) && !productDropdown.contains(e.target)) {
        productDropdown.classList.add('hidden');
    }
});

function filterVariants(productId) {
    const variantSelect = document.querySelector('select[name="variant_id"]');
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
</script>
@endpush
