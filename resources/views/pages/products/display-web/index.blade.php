@extends('layouts.app')

@section('title', 'Display Web - Urutan Produk')

@section('content')
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="font-headline-lg text-headline-lg text-on-surface">Display Web</h1>
            <nav class="flex items-center gap-2 text-label-sm text-on-surface-variant mt-1 font-medium">
                <a href="{{ route('dashboard') }}" class="hover:text-primary transition-colors">eCommerce</a>
                <span class="material-symbols-outlined text-[14px]">chevron_right</span>
                <a href="{{ route('products.index') }}" class="hover:text-primary transition-colors">Products</a>
                <span class="material-symbols-outlined text-[14px]">chevron_right</span>
                <span class="text-on-surface">Display Web</span>
            </nav>
        </div>
        <div class="flex items-center gap-3">
            <button 
                type="button" 
                id="btn-save-order"
                class="hidden items-center gap-2 px-5 py-2.5 bg-primary text-white rounded-lg font-bold text-sm hover:opacity-90 active:scale-95 transition-all shadow-md cursor-pointer"
            >
                <span class="material-symbols-outlined text-[18px]">save</span>
                <span id="btn-save-order-text">Simpan Urutan</span>
            </button>
            <a href="{{ route('products.index') }}" class="flex items-center gap-2 px-4 py-2 bg-surface-container text-on-surface hover:bg-surface-container-high rounded-lg font-label-md text-label-md transition-all border border-outline-variant/30">
                <span class="material-symbols-outlined text-[18px]">arrow_back</span>
                Kembali ke Produk
            </a>
        </div>
    </div>

    @include('layouts.partials.product-submenu')

    <!-- TAB NAVIGATION BAR: All Produk, Per Kategori, Per Brand, Per Suggest -->
    <div class="bg-white rounded-2xl border border-outline-variant/30 p-2 mb-4 shadow-2xs">
        <div class="flex items-center gap-2 overflow-x-auto pb-1 sm:pb-0">
            <!-- Tab 1: All Produk -->
            <a 
                href="{{ route('products.display-web.index', ['tab' => 'all']) }}" 
                class="flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-bold transition-all whitespace-nowrap {{ $tab === 'all' ? 'bg-primary text-white shadow-xs' : 'text-on-surface-variant hover:text-on-surface hover:bg-surface-container' }}"
            >
                <span class="material-symbols-outlined text-[18px]">apps</span>
                <span>All Produk</span>
                @if($tab === 'all')
                    <span class="px-1.5 py-0.5 rounded-full bg-white/20 text-white text-[10px] font-black">{{ $products->count() }}</span>
                @endif
            </a>

            <!-- Tab 2: Per Kategori -->
            <a 
                href="{{ route('products.display-web.index', ['tab' => 'category']) }}" 
                class="flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-bold transition-all whitespace-nowrap {{ $tab === 'category' ? 'bg-primary text-white shadow-xs' : 'text-on-surface-variant hover:text-on-surface hover:bg-surface-container' }}"
            >
                <span class="material-symbols-outlined text-[18px]">category</span>
                <span>Per Kategori</span>
                <span class="px-1.5 py-0.5 rounded-full {{ $tab === 'category' ? 'bg-white/20 text-white' : 'bg-surface-container text-on-surface-variant' }} text-[10px] font-bold">
                    {{ $categories->count() }}
                </span>
            </a>

            <!-- Tab 3: Per Brand -->
            <a 
                href="{{ route('products.display-web.index', ['tab' => 'brand']) }}" 
                class="flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-bold transition-all whitespace-nowrap {{ $tab === 'brand' ? 'bg-primary text-white shadow-xs' : 'text-on-surface-variant hover:text-on-surface hover:bg-surface-container' }}"
            >
                <span class="material-symbols-outlined text-[18px]">verified</span>
                <span>Per Brand</span>
                <span class="px-1.5 py-0.5 rounded-full {{ $tab === 'brand' ? 'bg-white/20 text-white' : 'bg-surface-container text-on-surface-variant' }} text-[10px] font-bold">
                    {{ $brands->count() }}
                </span>
            </a>

            <!-- Tab 4: Per Suggest -->
            <a 
                href="{{ route('products.display-web.index', ['tab' => 'suggest']) }}" 
                class="flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-bold transition-all whitespace-nowrap {{ $tab === 'suggest' ? 'bg-primary text-white shadow-xs' : 'text-on-surface-variant hover:text-on-surface hover:bg-surface-container' }}"
            >
                <span class="material-symbols-outlined text-[18px]">recommend</span>
                <span>Per Suggest</span>
            </a>
        </div>
    </div>

    <!-- CONTEXT BAR BERDASARKAN TAB AKTIF -->
    @if($tab === 'category')
        <!-- Category Filter Pills -->
        <div class="bg-white rounded-xl border border-outline-variant/30 p-3 mb-4 shadow-2xs">
            <div class="flex items-center justify-between gap-3 mb-2.5">
                <span class="text-xs font-bold text-on-surface flex items-center gap-1.5">
                    <span class="material-symbols-outlined text-primary text-[18px]">filter_alt</span>
                    Pilih Kategori:
                </span>
                <form method="GET" action="{{ route('products.display-web.index') }}" class="relative w-48 sm:w-64">
                    <input type="hidden" name="tab" value="category">
                    <input type="hidden" name="category_id" value="{{ $categoryId }}">
                    <span class="material-symbols-outlined absolute left-2.5 top-1/2 -translate-y-1/2 text-on-surface-variant text-[16px]">search</span>
                    <input type="text" name="search" value="{{ $search }}" placeholder="Cari nama produk..." class="w-full h-8 pl-8 pr-2.5 text-xs border border-outline-variant rounded-lg bg-surface-container-lowest focus:ring-1 focus:ring-primary focus:outline-none">
                </form>
            </div>
            <div class="flex items-center gap-2 overflow-x-auto pb-1">
                @foreach($categories as $category)
                    <a 
                        href="{{ route('products.display-web.index', ['tab' => 'category', 'category_id' => $category->id]) }}" 
                        class="px-3 py-1.5 rounded-lg text-xs font-semibold whitespace-nowrap transition-all flex items-center gap-1.5 {{ $categoryId == $category->id ? 'bg-primary text-white font-bold shadow-2xs' : 'bg-surface-container/60 hover:bg-surface-container text-on-surface' }}"
                    >
                        <span>{{ $category->name }}</span>
                        <span class="text-[10px] {{ $categoryId == $category->id ? 'bg-white/20 text-white' : 'bg-surface-container-high text-on-surface-variant' }} px-1.5 py-0.2 rounded-full font-bold">
                            {{ $category->products_count }}
                        </span>
                    </a>
                @endforeach
            </div>
        </div>
    @elseif($tab === 'brand')
        <!-- Brand Filter Pills -->
        <div class="bg-white rounded-xl border border-outline-variant/30 p-3 mb-4 shadow-2xs">
            <div class="flex items-center justify-between gap-3 mb-2.5">
                <span class="text-xs font-bold text-on-surface flex items-center gap-1.5">
                    <span class="material-symbols-outlined text-primary text-[18px]">branding_watermark</span>
                    Pilih Brand:
                </span>
                <form method="GET" action="{{ route('products.display-web.index') }}" class="relative w-48 sm:w-64">
                    <input type="hidden" name="tab" value="brand">
                    <input type="hidden" name="brand_id" value="{{ $brandId }}">
                    <span class="material-symbols-outlined absolute left-2.5 top-1/2 -translate-y-1/2 text-on-surface-variant text-[16px]">search</span>
                    <input type="text" name="search" value="{{ $search }}" placeholder="Cari nama produk..." class="w-full h-8 pl-8 pr-2.5 text-xs border border-outline-variant rounded-lg bg-surface-container-lowest focus:ring-1 focus:ring-primary focus:outline-none">
                </form>
            </div>
            <div class="flex items-center gap-2 overflow-x-auto pb-1">
                @foreach($brands as $brand)
                    <a 
                        href="{{ route('products.display-web.index', ['tab' => 'brand', 'brand_id' => $brand->id]) }}" 
                        class="px-3 py-1.5 rounded-lg text-xs font-semibold whitespace-nowrap transition-all flex items-center gap-1.5 {{ $brandId == $brand->id ? 'bg-primary text-white font-bold shadow-2xs' : 'bg-surface-container/60 hover:bg-surface-container text-on-surface' }}"
                    >
                        <span>{{ $brand->name }}</span>
                        <span class="text-[10px] {{ $brandId == $brand->id ? 'bg-white/20 text-white' : 'bg-surface-container-high text-on-surface-variant' }} px-1.5 py-0.2 rounded-full font-bold">
                            {{ $brand->products_count }}
                        </span>
                    </a>
                @endforeach
            </div>
        </div>
    @elseif($tab === 'suggest')
        <!-- Target Product Selector for Suggestions -->
        <div class="bg-white rounded-xl border border-outline-variant/30 p-3 mb-4 shadow-2xs">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div class="flex items-center gap-2 flex-1">
                    <span class="material-symbols-outlined text-primary text-[20px]">recommend</span>
                    <div>
                        <span class="text-xs font-bold text-on-surface">Pilih Produk Utama:</span>
                        <p class="text-[11px] text-on-surface-variant">Atur urutan produk rekomendasi (suggest) yang akan tampil di halaman produk ini.</p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <select 
                        id="suggestProductSelector" 
                        class="px-3 py-1.5 border border-outline-variant rounded-lg text-xs font-semibold text-on-surface bg-white focus:ring-2 focus:ring-primary/20 focus:outline-none cursor-pointer max-w-xs sm:max-w-sm"
                        onchange="window.location.href='{{ route('products.display-web.index', ['tab' => 'suggest']) }}&product_id=' + this.value"
                    >
                        @foreach($suggestParentProducts as $p)
                            <option value="{{ $p->id }}" {{ $productId == $p->id ? 'selected' : '' }}>
                                {{ $p->name }} ({{ $p->suggested_products_count }} Saran)
                            </option>
                        @endforeach
                    </select>
                    @if($activeProduct)
                        <button 
                            type="button" 
                            onclick="openAddSuggestionModal()" 
                            class="px-3 py-1.5 bg-primary text-white rounded-lg text-xs font-bold hover:opacity-90 transition-all flex items-center gap-1 shrink-0 cursor-pointer shadow-2xs"
                        >
                            <span class="material-symbols-outlined text-[16px]">add</span>
                            <span>Tambah Saran</span>
                        </button>
                    @endif
                </div>
            </div>
        </div>
    @else
        <!-- All Products Search Bar -->
        <div class="bg-white rounded-xl border border-outline-variant/30 p-3 mb-4 shadow-2xs flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div class="flex items-center gap-2">
                <span class="material-symbols-outlined text-primary text-[20px]">drag_indicator</span>
                <div>
                    <span class="text-xs font-bold text-on-surface">Urutan Tampilan Seluruh Produk</span>
                    <p class="text-[11px] text-on-surface-variant">Klik dan geser kartu produk untuk mengubah urutan tampil di website e-commerce.</p>
                </div>
            </div>
            <form method="GET" action="{{ route('products.display-web.index') }}" class="relative w-full sm:w-72">
                <input type="hidden" name="tab" value="all">
                <span class="material-symbols-outlined absolute left-2.5 top-1/2 -translate-y-1/2 text-on-surface-variant text-[16px]">search</span>
                <input type="text" name="search" value="{{ $search }}" placeholder="Cari nama atau kode produk..." class="w-full h-8 pl-8 pr-2.5 text-xs border border-outline-variant rounded-lg bg-surface-container-lowest focus:ring-1 focus:ring-primary focus:outline-none">
            </form>
        </div>
    @endif

    <!-- Instruction & Autosave Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2.5 bg-primary/5 border border-primary/20 rounded-xl px-4 py-2.5 mb-4">
        <div class="flex items-center gap-2 text-xs font-semibold text-on-surface">
            <span class="w-2 h-2 rounded-full bg-primary animate-pulse"></span>
            <span>Menampilkan <strong class="text-primary font-bold">{{ $products->count() }}</strong> produk. Geser kartu langsung untuk memindahkan posisi.</span>
        </div>
        <div class="flex items-center gap-4 text-xs font-semibold">
            <label class="flex items-center gap-2 cursor-pointer select-none">
                <input type="checkbox" id="toggle-autosave" class="rounded border-outline-variant text-primary focus:ring-primary h-4 w-4">
                <span class="text-on-surface-variant font-medium">Simpan Otomatis Saat Digeser</span>
            </label>
        </div>
    </div>

    <!-- Unsaved Alert Banner -->
    <div id="unsaved-banner" class="hidden items-center justify-between p-3 bg-amber-50 border border-amber-200 text-amber-900 rounded-xl mb-4 animate-fade-in shadow-xs">
        <div class="flex items-center gap-2">
            <span class="material-symbols-outlined text-amber-600 text-[18px]">warning</span>
            <span class="text-xs font-bold">Ada perubahan urutan yang belum disimpan!</span>
        </div>
        <button type="button" onclick="document.getElementById('btn-save-order').click()" class="px-3 py-1 bg-amber-600 hover:bg-amber-700 text-white font-bold text-xs rounded-lg transition-colors cursor-pointer">
            Simpan Sekarang
        </button>
    </div>

    <!-- PRODUCT COMPACT GRID (IMAGE SAJA & LABEL NAMA DIBAWAH) -->
    @if($products->isEmpty())
        <div class="bg-white rounded-2xl border border-outline-variant/30 p-12 text-center">
            <div class="w-14 h-14 rounded-full bg-surface-container mx-auto flex items-center justify-center text-on-surface-variant mb-3">
                <span class="material-symbols-outlined text-[28px]">inventory_2</span>
            </div>
            <h3 class="text-sm font-bold text-on-surface mb-1">
                @if($tab === 'suggest')
                    Belum Ada Produk Saran untuk Produk Ini
                @else
                    Tidak Ada Produk Ditemukan
                @endif
            </h3>
            <p class="text-xs text-on-surface-variant max-w-sm mx-auto">
                @if($tab === 'suggest')
                    Klik tombol "Tambah Saran" di atas untuk menambahkan produk rekomendasi.
                @else
                    Silakan ubah filter kategori, brand, atau kata kunci pencarian.
                @endif
            </p>
        </div>
    @else
        <!-- Ultra-compact Grid: Image Saja & Label Nama Dibawah -->
        <div 
            id="sortable-product-list" 
            class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-6 lg:grid-cols-8 xl:grid-cols-10 gap-2.5 sm:gap-3 mb-10"
        >
            @foreach($products as $product)
                <div 
                    class="product-row bg-white rounded-xl border border-outline-variant/30 hover:border-primary hover:shadow-md transition-all p-1.5 flex flex-col cursor-grab active:cursor-grabbing select-none group relative overflow-hidden"
                    data-id="{{ $product->id }}"
                    title="Geser untuk memindahkan urutan {{ $product->name }}"
                >
                    <!-- Thumbnail Box with Floating Rank Badge -->
                    <div class="w-full aspect-square rounded-lg bg-surface-container overflow-hidden relative border border-outline-variant/20">
                        @php
                            $imgSrc = $product->thumbnail ?: ($product->images->first()?->url ?? '');
                        @endphp
                        <img 
                            src="{{ $imgSrc }}" 
                            alt="{{ $product->name }}" 
                            class="w-full h-full object-cover pointer-events-none group-hover:scale-105 transition-transform duration-200" 
                            onerror="this.onerror=null; this.src='https://placehold.co/150x150?text=No+Image';"
                        >
                        
                        <!-- Floating Rank Badge -->
                        <span class="product-rank-badge absolute top-1 left-1 px-1.5 py-0.5 rounded-md bg-black/70 backdrop-blur-xs text-white text-[10px] font-black leading-none shadow-xs">
                            #{{ $loop->iteration }}
                        </span>

                        @if($tab === 'suggest')
                            <!-- Remove Suggestion Button -->
                            <button 
                                type="button" 
                                onclick="removeSuggestion('{{ $product->id }}')" 
                                class="absolute top-1 right-1 w-5 h-5 rounded-md bg-danger/90 hover:bg-danger text-white flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity hover:scale-110 shadow-xs cursor-pointer z-10" 
                                title="Hapus dari rekomendasi"
                            >
                                <span class="material-symbols-outlined text-[13px]">close</span>
                            </button>
                        @endif
                    </div>

                    <!-- Label Nama di Bawah -->
                    <div class="mt-1 px-0.5 pb-0.5 flex-1 flex items-center justify-center">
                        <p class="text-[11px] font-semibold text-on-surface line-clamp-2 leading-tight text-center group-hover:text-primary transition-colors" title="{{ $product->name }}">
                            {{ $product->name }}
                        </p>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    <!-- MODAL TAMBAH PRODUK SARAN (HANYA AKTIF DI TAB SUGGEST) -->
    @if($tab === 'suggest' && $activeProduct)
        <div id="addSuggestionModal" class="hidden fixed inset-0 z-50 overflow-y-auto bg-black/50 backdrop-blur-xs flex items-center justify-center p-4">
            <div class="bg-white rounded-2xl shadow-xl border border-outline-variant/40 max-w-lg w-full overflow-hidden animate-scale-in">
                <div class="px-5 py-4 bg-surface-container-lowest border-b border-outline-variant/30 flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-bold text-on-surface">Tambah Produk Saran / Rekomendasi</h3>
                        <p class="text-[11px] text-on-surface-variant">Produk target: <strong class="text-on-surface">{{ $activeProduct->name }}</strong></p>
                    </div>
                    <button type="button" onclick="closeAddSuggestionModal()" class="p-1 text-on-surface-variant hover:text-on-surface rounded-lg hover:bg-surface-container transition-colors">
                        <span class="material-symbols-outlined text-[20px]">close</span>
                    </button>
                </div>

                <div class="p-4 space-y-3">
                    <div>
                        <input 
                            type="text" 
                            id="modalSearchSuggestionInput" 
                            placeholder="Ketik nama atau kode produk..." 
                            class="w-full px-3 py-2 border border-outline-variant rounded-lg text-xs focus:ring-2 focus:ring-primary/20 focus:outline-none"
                            oninput="filterModalSuggestionItems(this.value)"
                        >
                    </div>

                    <div id="modalSuggestionItemsContainer" class="max-h-80 overflow-y-auto space-y-1.5 divide-y divide-outline-variant/20 pr-1">
                        @forelse($availableSuggestions as $avail)
                            <div class="modal-suggest-item pt-1.5 flex items-center justify-between gap-3 hover:bg-surface-container/30 p-2 rounded-xl transition-colors" data-name="{{ strtolower($avail->name . ' ' . $avail->code) }}">
                                <div class="flex items-center gap-2.5 min-w-0">
                                    <div class="w-9 h-9 rounded-lg bg-surface-container overflow-hidden shrink-0 border border-outline-variant/20">
                                        <img src="{{ $avail->thumbnail }}" alt="" class="w-full h-full object-cover" onerror="this.onerror=null; this.src='https://placehold.co/100x100?text=No+Image';">
                                    </div>
                                    <div class="min-w-0">
                                        <p class="text-xs font-bold text-on-surface truncate">{{ $avail->name }}</p>
                                        <span class="text-[10px] font-mono text-on-surface-variant">{{ $avail->code ?: 'NO-SKU' }}</span>
                                    </div>
                                </div>
                                <button 
                                    type="button" 
                                    onclick="addSuggestion('{{ $avail->id }}')" 
                                    class="px-2.5 py-1 bg-primary text-white rounded-lg text-xs font-bold hover:opacity-90 transition-all shrink-0 cursor-pointer"
                                >
                                    Pilih
                                </button>
                            </div>
                        @empty
                            <p class="text-xs text-on-surface-variant text-center py-6">Semua produk sudah ditambahkan ke saran.</p>
                        @endforelse
                    </div>
                </div>

                <div class="px-5 py-3 bg-surface-container-lowest border-t border-outline-variant/30 flex justify-end">
                    <button type="button" onclick="closeAddSuggestionModal()" class="px-4 py-1.5 border border-outline-variant text-on-surface-variant hover:bg-surface-container rounded-lg text-xs font-bold transition-all">
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    @endif
@endsection

@push('styles')
<style>
    .sortable-chosen-tile {
        outline: 2px solid var(--color-primary, #0052cc) !important;
        transform: scale(1.05) !important;
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.15) !important;
        z-index: 30 !important;
    }
    .sortable-ghost-tile {
        opacity: 0.25 !important;
    }
    .sortable-drag-tile {
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25) !important;
        opacity: 0.9 !important;
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const listEl = document.getElementById('sortable-product-list');
    const saveBtn = document.getElementById('btn-save-order');
    const saveBtnText = document.getElementById('btn-save-order-text');
    const unsavedBanner = document.getElementById('unsaved-banner');
    const autoSaveCheckbox = document.getElementById('toggle-autosave');
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

    const currentTab = '{{ $tab }}';
    const currentCategoryId = '{{ $categoryId }}';
    const currentBrandId = '{{ $brandId }}';
    const currentProductId = '{{ $productId }}';

    let hasUnsavedChanges = false;

    // Load autoSave preference from localStorage
    const savedAutoSave = localStorage.getItem('display_web_autosave');
    if (savedAutoSave === '1' && autoSaveCheckbox) {
        autoSaveCheckbox.checked = true;
    }

    if (autoSaveCheckbox) {
        autoSaveCheckbox.addEventListener('change', function () {
            localStorage.setItem('display_web_autosave', this.checked ? '1' : '0');
        });
    }

    function updateRankBadges() {
        if (!listEl) return;
        const badges = listEl.querySelectorAll('.product-rank-badge');
        badges.forEach((badge, idx) => {
            badge.textContent = '#' + (idx + 1);
        });
    }

    function markUnsaved() {
        hasUnsavedChanges = true;
        if (autoSaveCheckbox && autoSaveCheckbox.checked) {
            saveOrder();
        } else {
            if (saveBtn) {
                saveBtn.classList.remove('hidden');
                saveBtn.classList.add('flex');
            }
            if (unsavedBanner) {
                unsavedBanner.classList.remove('hidden');
                unsavedBanner.classList.add('flex');
            }
        }
    }

    function markSaved() {
        hasUnsavedChanges = false;
        if (saveBtn) {
            saveBtn.classList.add('hidden');
            saveBtn.classList.remove('flex');
        }
        if (unsavedBanner) {
            unsavedBanner.classList.add('hidden');
            unsavedBanner.classList.remove('flex');
        }
    }

    // Initialize SortableJS directly on product tiles with single class names
    if (listEl) {
        const sortable = new Sortable(listEl, {
            animation: 200,
            ghostClass: 'sortable-ghost-tile',
            chosenClass: 'sortable-chosen-tile',
            dragClass: 'sortable-drag-tile',
            onEnd: function () {
                updateRankBadges();
                markUnsaved();
            }
        });
    }

    // Save Order Function (Handles all tabs: all, category, brand, suggest)
    function saveOrder() {
        if (!listEl) return;
        const rows = listEl.querySelectorAll('.product-row');
        const productIds = Array.from(rows).map(row => row.getAttribute('data-id'));

        if (productIds.length === 0) return;

        if (saveBtn) {
            saveBtn.disabled = true;
            saveBtnText.textContent = 'Menyimpan...';
        }

        let targetUrl = '{{ route("products.display-web.reorder") }}';
        let payload = {
            product_ids: productIds,
            category_id: currentCategoryId || null,
            brand_id: currentBrandId || null
        };

        if (currentTab === 'suggest') {
            targetUrl = '{{ route("products.display-web.reorder-suggestions") }}';
            payload = {
                product_id: currentProductId,
                suggested_ids: productIds
            };
        }

        fetch(targetUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify(payload)
        })
        .then(res => res.json())
        .then(data => {
            if (saveBtn) saveBtn.disabled = false;
            if (saveBtnText) saveBtnText.textContent = 'Simpan Urutan';

            if (data.success) {
                markSaved();
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        toast: true,
                        position: 'top-end',
                        icon: 'success',
                        title: data.message || 'Urutan display web berhasil disimpan!',
                        showConfirmButton: false,
                        timer: 2000
                    });
                }
            } else {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal Menyimpan',
                        text: data.message || 'Terjadi kesalahan saat menyimpan urutan.'
                    });
                } else {
                    alert(data.message || 'Gagal menyimpan');
                }
            }
        })
        .catch(err => {
            if (saveBtn) saveBtn.disabled = false;
            if (saveBtnText) saveBtnText.textContent = 'Simpan Urutan';
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'error',
                    title: 'Kesalahan Jaringan',
                    text: err.message
                });
            } else {
                alert('Kesalahan jaringan: ' + err.message);
            }
        });
    }

    if (saveBtn) {
        saveBtn.addEventListener('click', saveOrder);
    }

    // Modal Add Suggestion Handlers
    window.openAddSuggestionModal = function() {
        const modal = document.getElementById('addSuggestionModal');
        if (modal) modal.classList.remove('hidden');
    };

    window.closeAddSuggestionModal = function() {
        const modal = document.getElementById('addSuggestionModal');
        if (modal) modal.classList.add('hidden');
    };

    window.filterModalSuggestionItems = function(query) {
        const items = document.querySelectorAll('.modal-suggest-item');
        const q = (query || '').toLowerCase().trim();
        items.forEach(it => {
            const name = it.getAttribute('data-name') || '';
            if (q === '' || name.includes(q)) {
                it.classList.remove('hidden');
            } else {
                it.classList.add('hidden');
            }
        });
    };

    window.addSuggestion = function(suggestedProductId) {
        if (!currentProductId || !suggestedProductId) return;

        fetch('{{ route("products.display-web.add-suggestion") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify({
                product_id: currentProductId,
                suggested_product_id: suggestedProductId
            })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                window.location.reload();
            } else {
                alert(data.message || 'Gagal menambahkan produk saran.');
            }
        })
        .catch(err => alert('Terjadi kesalahan: ' + err.message));
    };

    window.removeSuggestion = function(suggestedProductId) {
        if (!currentProductId || !suggestedProductId) return;
        if (!confirm('Hapus produk ini dari daftar saran?')) return;

        fetch('{{ route("products.display-web.remove-suggestion") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify({
                product_id: currentProductId,
                suggested_product_id: suggestedProductId
            })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                window.location.reload();
            } else {
                alert(data.message || 'Gagal menghapus produk saran.');
            }
        })
        .catch(err => alert('Terjadi kesalahan: ' + err.message));
    };

    // Warn before navigating if changes are unsaved
    window.addEventListener('beforeunload', function (e) {
        if (hasUnsavedChanges) {
            e.preventDefault();
            e.returnValue = '';
        }
    });
});
</script>
@endpush
