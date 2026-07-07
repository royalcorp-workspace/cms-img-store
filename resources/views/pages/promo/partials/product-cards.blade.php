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
                @php $vp = isset($variantPricesFromPivot[$variant->id]) ? $variantPricesFromPivot[$variant->id] : (($vAttrs['price'] ?? 0) ?: ($attrs['price'] ?? 0) ?: ($attrs['base_price'] ?? 0)); @endphp
                @php $stock = $variant->stock_qty ?? 0; @endphp
                @php $hasStock = $hasStock || $stock > 0; @endphp
                @php $disabled = $stock <= 0 ? 'disabled' : ''; @endphp
                <div class="variant-row py-1.5 px-2 rounded-md {{ $stock > 0 ? 'hover:bg-surface-container-low' : 'opacity-50' }} transition-colors">
                    <div class="flex items-start gap-2">
                        <label class="flex items-center flex-shrink-0 pt-0.5">
                            <input type="checkbox" class="variant-checkbox w-3.5 h-3.5 rounded border-outline-variant text-primary focus:ring-primary/30" value="{{ $variant->id }}" {{ $stock > 0 && in_array($variant->id, $selectedVariantIds ?? []) ? 'checked' : '' }} {{ $disabled }}>
                        </label>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center justify-between gap-2">
                                <span class="text-label-sm text-on-surface font-medium truncate block leading-tight {{ $stock <= 0 ? 'text-on-surface-variant' : '' }}">{{ $variant->variant_name ?? ($variant->sku ?? 'Default') }}</span>
                            </div>
                            <div class="flex items-center gap-2 mt-1">
                                <div class="relative w-52">
                                    <span class="absolute left-1.5 top-1/2 -translate-y-1/2 text-on-surface-variant text-[10px] font-bold">Rp</span>
                                    <input type="text" class="variant-price-input w-full pl-6 pr-1 py-1 text-body-xs text-right {{ $stock <= 0 ? 'bg-surface-container-high cursor-not-allowed' : 'bg-transparent border-b border-outline-variant focus:border-brand-gold' }}" value="{{ $vp }}" data-original="{{ $vp }}" oninput="updateDiscountInfo(this)" {{ $disabled }}>
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
