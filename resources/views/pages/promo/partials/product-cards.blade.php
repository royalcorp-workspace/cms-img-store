<style>
    /* Fix transparent checkmark issue in light mode */
    html:not(.dark) input[type="checkbox"].variant-checkbox:checked {
        background-image: url("data:image/svg+xml,%3csvg viewBox='0 0 16 16' fill='white' xmlns='http://www.w3.org/2000/svg'%3e%3cpath d='M12.207 4.793a1 1 0 010 1.414l-5 5a1 1 0 01-1.414 0l-2-2a1 1 0 011.414-1.414L6.5 9.086l4.293-4.293a1 1 0 011.414 0z'/%3e%3c/svg%3e") !important;
        background-color: #2563eb !important; /* blue-600 */
        border-color: #2563eb !important;
    }
</style>
@forelse($products as $product)
    @php 
        $selectedVariantIds = $selectedVariantIds ?? [];
        $variantPricesFromPivot = $variantPricesFromPivot ?? [];

        $displayPrice = (float) (
            $product->variants->where('stock_quantity', '>', 0)->where('sell_price', '>', 0)->min('sell_price')
            ?: $product->variants->where('stock_quantity', '>', 0)->where('base_price', '>', 0)->min('base_price')
            ?: $product->variants->where('sell_price', '>', 0)->min('sell_price')
            ?: ($product->base_price ?? 0)
        );
        
        $hasValidVariants = false;
        foreach($product->variants as $variant) {
            $origPrice = (float) ($variant->sell_price ?: $variant->base_price ?: 0);
            $stock = (int) ($variant->stock_quantity ?? 0);
            $isSelected = in_array($variant->id, $selectedVariantIds);
            if (($origPrice > 0 && $stock > 0) || $isSelected) {
                $hasValidVariants = true;
                break;
            }
        }
    @endphp

    @if(!$hasValidVariants)
        @continue
    @endif
    
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
                <p class="text-label-xs text-on-surface-variant font-medium">Mulai Rp{{ number_format($displayPrice, 0, ',', '.') }}</p>
            </div>
        </div>
        <div class="p-3 space-y-1">
            @php $hasStock = false; @endphp
            @foreach($product->variants as $variant)
                @php 
                    $origPrice = (float) ($variant->sell_price ?: $variant->base_price ?: 0);
                    $stock = (int) ($variant->stock_quantity ?? 0);
                    $isSelected = in_array($variant->id, $selectedVariantIds);
                @endphp
                @if(($stock <= 0 || $origPrice <= 0) && !$isSelected)
                    @continue
                @endif
                @php 
                    if ($stock > 0) $hasStock = true;
                    $disabled = ($origPrice <= 0 || $stock <= 0) ? 'disabled title="' . ($stock <= 0 ? 'Stok habis' : 'Harga 0 tidak dapat dipromokan') . '"' : '';
                    $savedDiscount = isset($variantPricesFromPivot[$variant->id]) && $variantPricesFromPivot[$variant->id] !== null ? (float)$variantPricesFromPivot[$variant->id] : '';
                @endphp
                <div class="variant-row py-1.5 px-2 rounded-md {{ ($origPrice > 0 && $stock > 0) ? 'hover:bg-surface-container-low' : 'opacity-50' }} transition-colors">
                    <div class="flex items-start gap-2">
                        <label class="flex items-center flex-shrink-0 pt-0.5">
                            <input type="checkbox" class="variant-checkbox w-3.5 h-3.5 rounded border-outline-variant text-blue-600 checked:bg-blue-600 accent-blue-600 focus:ring-blue-600/30" value="{{ $variant->id }}" {{ $isSelected ? 'checked' : '' }} {!! $disabled !!}>
                        </label>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center justify-between gap-2">
                                <div class="flex items-center gap-1.5 min-w-0">
                                    <span class="text-label-sm text-on-surface font-medium truncate block leading-tight {{ ($origPrice <= 0 || $stock <= 0) ? 'text-on-surface-variant' : '' }}">
                                        {{ $variant->variant_name ?? ($variant->sku ?? 'Default') }}
                                    </span>
                                    @if($stock > 0)
                                        <span class="text-[10px] font-semibold text-emerald-700 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-950/40 px-1.5 py-0.5 rounded border border-emerald-200 dark:border-emerald-800 whitespace-nowrap">
                                            Stok: {{ number_format($stock, 0, ',', '.') }}
                                        </span>
                                    @else
                                        <span class="text-[10px] font-semibold text-rose-700 dark:text-rose-400 bg-rose-50 dark:bg-rose-950/40 px-1.5 py-0.5 rounded border border-rose-200 dark:border-rose-800 whitespace-nowrap">
                                            Stok Habis
                                        </span>
                                    @endif
                                </div>
                                <span class="text-label-xs text-on-surface-variant font-medium whitespace-nowrap">
                                    Rp{{ number_format($origPrice, 0, ',', '.') }}
                                </span>
                            </div>
                            <div class="flex items-center gap-2 mt-1">
                                <div class="relative w-52">
                                    <span class="variant-unit-badge absolute left-2 top-1/2 -translate-y-1/2 text-on-surface-variant text-[11px] font-bold select-none">%</span>
                                    <input type="number" step="any" class="variant-price-input w-full pl-7 pr-2 py-1 text-body-xs text-right {{ ($origPrice <= 0 || $stock <= 0) ? 'bg-surface-container-high cursor-not-allowed' : 'bg-transparent border-b border-outline-variant focus:border-brand-gold' }}" value="{{ $savedDiscount }}" data-original-price="{{ $origPrice }}" placeholder="0-100" oninput="updateDiscountInfo(this)" {!! $disabled !!}>
                                </div>
                                <label class="discount-info text-[11px] text-on-surface-variant whitespace-nowrap w-44 text-right" data-price="{{ $origPrice }}">
                                    <span class="discount-text">→Rp{{ number_format($origPrice, 0, ',', '.') }}</span>
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
@empty
    <div class="col-span-full py-12 text-center text-on-surface-variant">
        <span class="material-symbols-outlined text-[48px] text-outline-variant mb-2">inventory_2</span>
        <p class="font-body-md font-medium">Tidak ada produk yang ditemukan</p>
        <p class="text-body-sm text-secondary">Coba ubah kata kunci pencarian atau pilih kategori lain.</p>
    </div>
@endforelse
