@extends('layouts.app')

@section('title', 'Bulk Price Setting')

@section('content')
<!-- Header -->
    <div class="flex justify-between items-center mb-8">
        <div>
            <div class="flex items-center gap-2 text-primary font-label-md text-label-md mb-2">
                <span class="material-symbols-outlined text-[16px]">home</span>
                <span>Dashboard</span>
                <span class="material-symbols-outlined text-[14px]">chevron_right</span>
                <span>Price Settings</span>
                <span class="material-symbols-outlined text-[14px]">chevron_right</span>
                <span class="text-on-surface">Bulk Price Setting</span>
            </div>
            <h3 class="font-headline-xl text-headline-xl text-on-surface">Bulk Price Setting</h3>
        </div>
    </div>

    <!-- Metadata Form Section -->
    <section class="bg-surface-container-low rounded-xl p-8 border border-outline-variant/30 shadow-sm mb-6">
        <div class="flex flex-col md:flex-row gap-8">
            <div class="flex-1 space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-2">
                        <label class="font-label-md text-label-md text-on-surface-variant block">Campaign Name <span class="inline-flex items-center cursor-help text-on-surface-variant relative group"><span class="material-symbols-outlined text-[18px]">info</span><span class="absolute right-0 top-full mt-2 w-80 bg-surface-container-highest rounded-lg shadow-lg border border-outline-variant p-4 text-body-xs text-on-surface-variant hidden group-hover:block z-50">Nama campaign untuk bulk price setting ini</span></span></label>
                        <input class="w-full bg-surface-container-lowest border border-outline-variant rounded-lg px-4 py-3 focus:border-brand-gold focus:ring-0 text-body-md transition-all" type="text" value="Premium Heritage Winter Sale">
                    </div>
                    <div class="space-y-2">
                        <label class="font-label-md text-label-md text-on-surface-variant block">Global Discount (%) <span class="inline-flex items-center cursor-help text-on-surface-variant relative group"><span class="material-symbols-outlined text-[18px]">info</span><span class="absolute right-0 top-full mt-2 w-80 bg-surface-container-highest rounded-lg shadow-lg border border-outline-variant p-4 text-body-xs text-on-surface-variant hidden group-hover:block z-50">Persentase diskon yang berlaku untuk semua produk terpilih</span></span></label>
                        <div class="relative">
                            <input class="w-full bg-surface-container-lowest border border-outline-variant rounded-lg px-4 py-3 focus:border-brand-gold focus:ring-0 text-body-md transition-all" type="number" value="15">
                            <span class="absolute right-4 top-1/2 -translate-y-1/2 text-on-surface-variant">%</span>
                        </div>
                    </div>
                </div>
                <div class="space-y-2">
                    <label class="font-label-md text-label-md text-on-surface-variant block">Description <span class="inline-flex items-center cursor-help text-on-surface-variant relative group"><span class="material-symbols-outlined text-[18px]">info</span><span class="absolute right-0 top-full mt-2 w-80 bg-surface-container-highest rounded-lg shadow-lg border border-outline-variant p-4 text-body-xs text-on-surface-variant hidden group-hover:block z-50">Deskripsi singkat tentang campaign bulk price</span></span></label>
                    <textarea class="w-full bg-surface-container-lowest border border-outline-variant rounded-lg px-4 py-3 focus:border-brand-gold focus:ring-0 text-body-md transition-all resize-none" rows="2">Price adjustments for the upcoming exclusive heritage collection launch.</textarea>
                </div>
            </div>
            <div class="w-full md:w-72 flex flex-col justify-between p-6 bg-primary-container rounded-lg text-white">
                <div>
                    <p class="font-label-sm text-label-sm uppercase tracking-widest text-primary-fixed-dim/70">Estimated Impact</p>
                    <h3 class="text-headline-md font-headline-md mt-1">$42,850.00</h3>
                </div>
                <div class="mt-4 pt-4 border-t border-white/10">
                    <p class="text-label-sm text-white/60">Calculated based on 248 items selected in the current inventory batch.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Category Filters -->
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

    <!-- Product Grid Layout -->
    <form id="bulkPriceForm" action="{{ route('price-settings.bulk.store') }}" method="POST" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6 mb-8">
        @csrf
        @foreach($products as $product)
            @php
                $categorySlug = $product->category->slug ?? 'other';
                $variants = $product->variants;
                $mainImage = $product->thumbnail_url ?? 'https://via.placeholder.com/400x400?text=' . urlencode($product->name);
                $mainSku = $variants->first()->sku ?? 'N/A';
            @endphp
            <div class="product-card bg-surface-container-lowest rounded-xl border border-outline-variant overflow-hidden shadow-sm flex flex-col group transition-all hover:shadow-md" data-product-id="{{ $product->id }}" data-category="{{ $categorySlug }}">
                <div class="relative aspect-square cursor-pointer overflow-hidden image-container border-4 border-transparent transition-all duration-200" onclick="toggleSelection(this.parentElement)">
                    <img alt="{{ $product->name }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" src="{{ $mainImage }}">
                    <div class="checkmark-overlay absolute inset-0 bg-brand-gold/20 flex items-center justify-center opacity-0 transition-opacity duration-200">
                        <span class="material-symbols-outlined text-white font-bold text-4xl drop-shadow-md">check_circle</span>
                    </div>
                </div>
                <div class="p-5 flex flex-col flex-1 space-y-3">
                    <div class="space-y-1">
                        <h4 class="font-body-md text-body-md text-on-surface font-semibold line-clamp-1">{{ $product->name }}</h4>
                        <p class="text-label-sm text-on-surface-variant">SKU: {{ $mainSku }}</p>
                    </div>
                    @if($variants->count() > 0)
                        <div class="space-y-2 border-t border-outline-variant/30 pt-3">
                            @foreach($variants as $variant)
                                @php
                                    $rawVariant = $variant->getAttributes();
                                    $rawProduct = $product->getAttributes();

                                    $vp = 0;
                                    if (isset($rawVariant['price']) && $rawVariant['price'] != '' && $rawVariant['price'] != 0) {
                                        $vp = $rawVariant['price'];
                                    } elseif (isset($rawProduct['price']) && $rawProduct['price'] != '' && $rawProduct['price'] != 0) {
                                        $vp = $rawProduct['price'];
                                    } elseif (isset($rawProduct['base_price']) && $rawProduct['base_price'] != '' && $rawProduct['base_price'] != 0) {
                                        $vp = $rawProduct['base_price'];
                                    }
                                @endphp
                                <div class="flex items-center justify-between gap-2">
                                    <div class="flex-1 min-w-0">
                                        <span class="text-label-sm text-on-surface-variant truncate block">{{ $variant->variant_name }}</span>
                                        <span class="text-label-xs text-on-surface-variant/60">Stock: {{ $variant->stock_qty ?? 0 }}</span>
                                    </div>
                                    <div class="relative w-28">
                                        <span class="absolute left-2 top-1/2 -translate-y-1/2 text-on-surface-variant text-sm">Rp</span>
                                        <input class="variant-price-input w-full pl-8 pr-1 py-1.5 bg-surface-container-lowest border border-outline-variant rounded-lg text-body-sm font-body-md focus:border-brand-gold focus:ring-1 focus:ring-brand-gold transition-all text-right" type="text" name="variants[{{ $variant->id }}][price]" value="{{ $vp }}" data-original="{{ $vp }}" oninput="updateVariantChange(this)">
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="space-y-2 border-t border-outline-variant/30 pt-3">
                            @php
                                $bp = 0;
                                $rawProduct = $product->getAttributes();
                                if (isset($rawProduct['price']) && $rawProduct['price'] != '' && $rawProduct['price'] != 0) {
                                    $bp = $rawProduct['price'];
                                } elseif (isset($rawProduct['base_price']) && $rawProduct['base_price'] != '' && $rawProduct['base_price'] != 0) {
                                    $bp = $rawProduct['base_price'];
                                }
                            @endphp
                            <div class="flex items-center justify-between gap-2">
                                <div class="flex-1">
                                    <span class="text-label-sm text-on-surface-variant">Base Price</span>
                                </div>
                                <div class="relative w-28">
                                    <span class="absolute left-2 top-1/2 -translate-y-1/2 text-on-surface-variant text-sm">Rp</span>
                                    <input class="variant-price-input w-full pl-8 pr-1 py-1.5 bg-surface-container-lowest border border-outline-variant rounded-lg text-body-sm font-body-md focus:border-brand-gold focus:ring-1 focus:ring-brand-gold transition-all text-right" type="text" name="products[{{ $product->id }}][base_price]" value="{{ $bp }}" data-original="{{ $bp }}" oninput="updateVariantChange(this)">
                                </div>
                            </div>
                        </div>
                    @endif
                    <div class="flex items-center justify-between pt-2">
                        <span class="bg-surface-container-high text-on-surface-variant px-3 py-1 rounded-full text-label-sm font-semibold change-badge">No Change</span>
                    </div>
                </div>
            </div>
        @endforeach
    </form>

    <!-- Load More -->
    <div class="flex flex-col items-center gap-4 py-8">
        <p class="text-label-sm text-on-surface-variant">Showing {{ $products->count() }} of {{ $products->count() }} products</p>
    </div>

    <!-- Sticky Footer Action -->
    <div class="sticky bottom-0 bg-surface-container-low/90 backdrop-blur-md border-t border-outline-variant flex justify-between items-center px-8 py-4 z-30 shadow-[0_-4px_10px_rgba(0,0,0,0.05)]">
        <div class="flex items-center gap-4">
            <span class="text-label-md font-label-md text-on-surface-variant">Selected: <span class="text-primary font-bold" id="selectedCount">0 Items</span></span>
            <div class="h-4 w-px bg-outline-variant"></div>
            <span class="text-label-md font-label-md text-on-surface-variant">Total New Value: <span class="text-primary font-bold" id="totalNewValue">$0.00</span></span>
        </div>
        <div class="flex gap-4">
            <button type="button" class="px-8 py-3 bg-surface-container-high text-primary font-bold rounded-lg hover:bg-surface-variant transition-colors" onclick="resetAll()">Discard Changes</button>
            <button type="submit" form="bulkPriceForm" class="px-10 py-3 bg-brand-dark text-white font-bold rounded-lg hover:shadow-lg hover:-translate-y-0.5 transition-all active:translate-y-0">Apply Changes to Selected Items</button>
        </div>
    </div>

    <!-- Bottom Copyright -->
    <div class="flex justify-between items-center px-gutter py-4 border-t border-outline-variant bg-surface-container-low text-label-sm text-secondary">
        <span>2026 &copy; Larkon. Crafted by Techzaa</span>
        <div class="flex gap-6">
            <a href="#" class="hover:text-primary transition-colors">Support</a>
            <a href="#" class="hover:text-primary transition-colors">Privacy Policy</a>
            <a href="#" class="hover:text-primary transition-colors">Terms of Service</a>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    function toggleSelection(cardElement) {
        cardElement.classList.toggle('selected');
    }

    function filterProducts(category, button) {
        const cards = document.querySelectorAll('.product-card');
        const buttons = button.parentElement.querySelectorAll('button');

        buttons.forEach(btn => {
            btn.classList.remove('text-primary', 'font-bold', 'border-b-2', 'border-brand-gold');
            btn.classList.add('text-on-surface-variant');
        });

        button.classList.remove('text-on-surface-variant');
        button.classList.add('text-primary', 'font-bold', 'border-b-2', 'border-brand-gold');

        cards.forEach(card => {
            const cardCategory = card.getAttribute('data-category');
            if (category === 'all' || cardCategory === category) {
                card.style.display = 'flex';
            } else {
                card.style.display = 'none';
            }
        });
    }

    function updateVariantChange(input) {
        const card = document.querySelector('.product-card:hover') || document.activeElement?.closest('.product-card');
        if (!card) return;
        
        const inputs = card.querySelectorAll('.variant-price-input');
        if (inputs.length === 0) return;

        const badge = card.querySelector('.change-badge');
        let totalOriginal = 0;
        let totalCurrent = 0;

        inputs.forEach(input => {
            const original = parseFloat(input.dataset.original) || 0;
            const current = parseFloat(input.value.replace(/,/g, '')) || 0;
            totalOriginal += original;
            totalCurrent += current;
        });

        if (badge && totalOriginal > 0) {
            const pct = ((totalCurrent - totalOriginal) / totalOriginal * 100).toFixed(0);
            const sign = pct >= 0 ? '+' : '';
            const absPct = Math.abs(pct);
            if (absPct < 1) {
                badge.textContent = 'No Change';
                badge.className = 'bg-surface-container-high text-on-surface-variant px-3 py-1 rounded-full text-label-sm font-semibold change-badge';
            } else if (pct > 0) {
                badge.textContent = sign + pct + '% Rise';
                badge.className = 'bg-success/20 text-success px-3 py-1 rounded-full text-label-sm font-semibold change-badge';
            } else {
                badge.textContent = sign + pct + '% Drop';
                badge.className = 'bg-danger/20 text-danger px-3 py-1 rounded-full text-label-sm font-semibold change-badge';
            }
        }
    }

    document.querySelectorAll('.variant-price-input').forEach(input => {
        input.addEventListener('focus', function() {
            this.parentElement.closest('.product-card').classList.add('ring-2', 'ring-brand-gold/10');
        });
        input.addEventListener('blur', function() {
            this.parentElement.closest('.product-card').classList.remove('ring-2', 'ring-brand-gold/10');
        });
    });

    document.getElementById('bulkPriceForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const form = e.target;
        const formData = new FormData(form);
        const variants = {};
        const products = {};
        
        for (const [key, value] of formData.entries()) {
            const variantParts = key.match(/variants\[([^\]]+)\]\[price\]/);
            const productParts = key.match(/products\[([^\]]+)\]\[base_price\]/);
            
            if (variantParts) {
                if (!variants[variantParts[1]]) variants[variantParts[1]] = {};
                variants[variantParts[1]].id = variantParts[1];
                variants[variantParts[1]].price = value;
            }
            if (productParts) {
                if (!products[productParts[1]]) products[productParts[1]] = {};
                products[productParts[1]].id = productParts[1];
                products[productParts[1]].base_price = value;
            }
        }

        const payload = {
            variants: Object.values(variants),
            products: Object.values(products),
        };

        fetch(form.action, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': formData.get('_token'),
                'Content-Type': 'application/json',
                'Accept': 'application/json',
            },
            body: JSON.stringify(payload),
        })
        .then(response => response.json())
        .then(result => {
            if (result.success) {
                alert('Prices updated successfully!');
                location.reload();
            } else {
                alert('Failed to update prices: ' + (result.message || 'Unknown error'));
            }
        })
        .catch(() => alert('An error occurred.'));
    });

    function resetAll() {
        document.querySelectorAll('.variant-price-input').forEach(input => {
            input.value = input.dataset.original;
            updateVariantChange(input);
        });
        document.querySelectorAll('.product-card.selected').forEach(card => {
            card.classList.remove('selected');
        });
        updateFooterStats();
    }

    function updateFooterStats() {
        const selectedCards = document.querySelectorAll('.product-card.selected');
        document.getElementById('selectedCount').textContent = selectedCards.length + ' Items';
        
        let total = 0;
        selectedCards.forEach(card => {
            card.querySelectorAll('.variant-price-input').forEach(input => {
                const val = parsePrice(input.value);
                total += val;
            });
        });
        document.getElementById('totalNewValue').textContent = 'Rp' + total.toLocaleString('id-ID', { minimumFractionDigits: 0, maximumFractionDigits: 0 });
    }

    document.querySelectorAll('.product-card').forEach(card => {
        card.addEventListener('click', function(e) {
            if (!e.target.closest('.variant-price-input')) {
                this.classList.toggle('selected');
                updateFooterStats();
            }
        });
    });

    document.querySelectorAll('.variant-price-input').forEach(input => {
        input.addEventListener('input', updateVariantChange);
        input.addEventListener('focus', function() {
            this.parentElement.closest('.product-card').classList.add('ring-2', 'ring-brand-gold/10');
        });
        input.addEventListener('blur', function() {
            this.parentElement.closest('.product-card').classList.remove('ring-2', 'ring-brand-gold/10');
        });
    });

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
        const cards = document.querySelectorAll('.product-card');
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
</script>
@endpush
