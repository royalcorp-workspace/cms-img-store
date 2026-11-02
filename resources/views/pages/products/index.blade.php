@extends('layouts.app')

@section('title', 'Products')

@section('content')
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="font-headline-lg text-headline-lg text-on-surface">Products</h1>
            <nav class="flex items-center gap-2 text-label-sm text-on-surface-variant mt-1 font-medium">
                <a href="{{ route('dashboard') }}" class="hover:text-primary transition-colors">eCommerce</a>
                <span class="material-symbols-outlined text-[14px]">chevron_right</span>
                <span class="text-on-surface">Products</span>
            </nav>
        </div>
        <div class="flex items-center gap-2.5">
            @can('products.export')
                <a href="{{ route('products.export', request()->query()) }}" target="_blank" rel="noopener noreferrer" data-no-loader="true" class="flex items-center gap-1.5 px-4 py-2 bg-emerald-700 hover:bg-emerald-800 text-white rounded-md font-label-md text-label-md transition-all shadow-xs" title="Export seluruh produk atau hasil filter ke file Excel Item Master">
                    <span class="material-symbols-outlined text-[18px]">download</span>
                    <span>Export Excel</span>
                </a>
            @endcan
            @can('products.import.form')
                <a href="{{ route('products.import.form') }}" class="flex items-center gap-2 px-4 py-2 bg-surface-container text-on-surface hover:bg-surface-container-high rounded-md font-label-md text-label-md transition-all border border-outline-variant/30">
                    <span class="material-symbols-outlined text-[18px]">cloud_upload</span>
                    Bulk Import
                </a>
            @endcan
            @can('products.create')
                <a href="{{ route('products.create') }}" class="flex items-center gap-2 px-5 py-2 bg-primary text-white font-label-md hover:opacity-90 transition-all">
                    <span class="material-symbols-outlined text-[18px]">add</span>
                    Create Product
                </a>
            @endcan
        </div>
    </div>

    @include('layouts.partials.product-submenu')

    @if(!empty($isPrivilegedUser))
        <div class="mb-5 flex flex-col sm:flex-row sm:items-center justify-between gap-3 p-3 bg-slate-100/90 dark:bg-stone-900/90 rounded-2xl border border-slate-200 dark:border-stone-800 shadow-2xs">
            <div class="flex items-center gap-2.5">
                <span class="w-2.5 h-2.5 rounded-full {{ !$isStoreView ? 'bg-primary' : 'bg-blue-600 dark:bg-sky-400' }} animate-pulse"></span>
                <span class="text-xs font-bold text-slate-500 dark:text-stone-400 uppercase tracking-wider">Kanal / Toko Aktif:</span>
                <span class="text-xs font-extrabold {{ !$isStoreView ? 'text-primary dark:text-primary' : 'text-blue-700 dark:text-sky-300' }}">
                    {{ !$isStoreView ? '🌐 Official Web Retail (D2C Publik)' : '🏬 ' . ($currentStore['name'] ?? 'Toko Dealer') }}
                </span>
            </div>

            <!-- Unified Omnichannel Context Autocomplete Selector -->
            <div class="relative" 
                 x-data="{
                    open: false,
                    search: '',
                    isOnline: {{ !$isStoreView ? 'true' : 'false' }},
                    selectedId: '{{ !$isStoreView ? 'online' : ($currentStore['id'] ?? '') }}',
                    selectedName: '{{ !$isStoreView ? 'Web Official Store (Online Retail IMG)' : addslashes($currentStore['name'] ?? 'Pilih Toko...') }}',
                    selectedAn8: '{{ !$isStoreView ? '' : ($currentStore['address_number'] ?? ($currentStore['code'] ?? '')) }}',
                    stores: [],
                    loading: false,
                    selectedIndex: -1,
                    init() {
                        this.fetchStores();
                    },
                    fetchStores() {
                        this.loading = true;
                        fetch('{{ route('products.stores-autocomplete') }}?q=' + encodeURIComponent(this.search), {
                            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                        })
                            .then(res => res.json())
                            .then(data => {
                                this.stores = data;
                                this.loading = false;
                            })
                            .catch(() => { this.loading = false; });
                    },
                    selectStore(item) {
                        this.open = false;
                        if (item.id === 'online' || item.is_online) {
                            window.location.href = '{{ route('products.index', ['view' => 'online']) }}';
                        } else {
                            window.location.href = '{{ route('products.index') }}?view=store&store_id=' + encodeURIComponent(item.id);
                        }
                    },
                    onKey(e) {
                        if (!this.open) {
                            if (e.key === 'ArrowDown' || e.key === 'Enter') {
                                this.open = true;
                                this.fetchStores();
                            }
                            return;
                        }
                        if (e.key === 'ArrowDown') {
                            e.preventDefault();
                            this.selectedIndex = (this.selectedIndex + 1) % this.stores.length;
                        } else if (e.key === 'ArrowUp') {
                            e.preventDefault();
                            this.selectedIndex = (this.selectedIndex - 1 + this.stores.length) % this.stores.length;
                        } else if (e.key === 'Enter' && this.selectedIndex >= 0) {
                            e.preventDefault();
                            this.selectStore(this.stores[this.selectedIndex]);
                        } else if (e.key === 'Escape') {
                            this.open = false;
                        }
                    }
                 }"
                 @click.outside="open = false"
                 @keydown="onKey($event)">
                
                <div class="flex items-center gap-2.5">
                    <span class="text-sm font-bold text-slate-700 dark:text-stone-300 whitespace-nowrap hidden sm:inline">Ganti Kanal / Toko:</span>
                    <!-- Trigger Button -->
                    <button type="button" 
                            @click="open = !open; if(open) { $nextTick(() => $refs.storeSearchInput.focus()); fetchStores(); }"
                            class="h-11 min-w-[280px] sm:min-w-[380px] px-3.5 rounded-xl border-2 border-slate-300 dark:border-stone-700 hover:border-blue-600 dark:hover:border-sky-500 bg-white dark:bg-stone-800 text-left flex items-center justify-between gap-3 shadow-2xs hover:shadow-sm transition-all cursor-pointer">
                        <div class="flex items-center gap-2.5 min-w-0">
                            <span class="material-symbols-outlined text-[22px] shrink-0" :class="isOnline ? 'text-primary' : 'text-blue-600 dark:text-sky-400'" x-text="isOnline ? 'public' : 'storefront'"></span>
                            <div class="min-w-0 truncate">
                                <span class="text-sm font-extrabold text-slate-900 dark:text-stone-100 truncate block tracking-tight" x-text="selectedName"></span>
                            </div>
                        </div>
                        <div class="flex items-center gap-2 shrink-0">
                            <template x-if="selectedAn8">
                                <span class="text-xs font-mono font-bold px-2.5 py-0.5 rounded-lg bg-blue-100 dark:bg-sky-950/80 text-blue-800 dark:text-sky-300 border border-blue-200 dark:border-sky-800" x-text="'AN8: ' + selectedAn8"></span>
                            </template>
                            <template x-if="isOnline">
                                <span class="text-xs font-bold px-2.5 py-0.5 rounded-lg bg-emerald-100 dark:bg-emerald-950/80 text-emerald-800 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800">Online Retail</span>
                            </template>
                            <span class="material-symbols-outlined text-[20px] text-slate-400 dark:text-stone-400 transition-transform duration-200" :class="{ 'rotate-180': open }">expand_more</span>
                        </div>
                    </button>
                </div>

                <!-- Autocomplete Dropdown Menu -->
                <div x-show="open" 
                     x-transition:enter="transition ease-out duration-150"
                     x-transition:enter-start="opacity-0 translate-y-1"
                     x-transition:enter-end="opacity-100 translate-y-0"
                     x-transition:leave="transition ease-in duration-100"
                     x-transition:leave-start="opacity-100 translate-y-0"
                     x-transition:leave-end="opacity-0 translate-y-1"
                     class="absolute right-0 top-full mt-2 w-96 sm:w-[480px] bg-white dark:bg-stone-800 border border-slate-200 dark:border-stone-700 rounded-2xl shadow-2xl z-50 overflow-hidden divide-y divide-slate-100 dark:divide-stone-700">
                    
                    <!-- Search Box in Dropdown -->
                    <div class="p-3 bg-slate-50/90 dark:bg-stone-900/90 border-b border-slate-200 dark:border-stone-700 relative">
                        <span class="material-symbols-outlined absolute left-5 top-1/2 -translate-y-1/2 text-slate-400 dark:text-stone-400 text-[20px]">search</span>
                        <input type="text" 
                               x-ref="storeSearchInput"
                               x-model="search"
                               @input.debounce.250ms="fetchStores()"
                               placeholder="Ketik 'web', nama toko, atau nomor AN8 JDE..."
                               class="w-full h-11 pl-10 pr-9 text-sm font-semibold rounded-xl border border-slate-300 dark:border-stone-600 bg-white dark:bg-stone-800 text-slate-900 dark:text-stone-100 focus:outline-none focus:border-blue-600 dark:focus:border-sky-500 focus:ring-2 focus:ring-blue-500/20 dark:focus:ring-sky-500/20 transition-all placeholder:text-slate-400 dark:placeholder:text-stone-500">
                        <span x-show="loading" class="material-symbols-outlined absolute right-5 top-1/2 -translate-y-1/2 text-slate-400 dark:text-stone-400 text-[18px] animate-spin">progress_activity</span>
                    </div>

                    <!-- List Suggestions -->
                    <div class="max-h-72 overflow-y-auto divide-y divide-slate-100 dark:divide-stone-700/60">
                        <template x-for="(st, idx) in stores" :key="st.id">
                            <div @click="selectStore(st)"
                                 @mouseenter="selectedIndex = idx"
                                 :class="{ 'bg-blue-50/80 dark:bg-sky-950/60 border-l-4 border-l-blue-600 dark:border-l-sky-400': selectedId === st.id, 'bg-slate-50 dark:bg-stone-700/50': selectedIndex === idx && selectedId !== st.id }"
                                 class="p-3.5 hover:bg-slate-50 dark:hover:bg-stone-700/50 cursor-pointer flex items-center justify-between gap-3 transition-colors">
                                <div class="flex items-center gap-3 min-w-0">
                                    <div class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0 shadow-2xs"
                                         :class="st.is_online ? 'bg-primary/10 text-primary border border-primary/20' : 'bg-blue-50 dark:bg-sky-950/50 text-blue-600 dark:text-sky-400 border border-blue-200 dark:border-sky-800/60'">
                                        <span class="material-symbols-outlined text-[22px]" x-text="st.is_online ? 'public' : 'storefront'"></span>
                                    </div>
                                    <div class="min-w-0">
                                        <div class="flex items-center gap-2">
                                            <span class="font-extrabold text-sm text-slate-900 dark:text-stone-100 truncate" x-text="st.name"></span>
                                            <template x-if="selectedId === st.id">
                                                <span class="material-symbols-outlined text-[18px] text-blue-600 dark:text-sky-400 shrink-0">check_circle</span>
                                            </template>
                                        </div>
                                        <div class="flex items-center gap-2 mt-1">
                                            <template x-if="st.is_online">
                                                <span class="text-xs font-bold px-2 py-0.5 rounded bg-emerald-100 dark:bg-emerald-950/80 text-emerald-800 dark:text-emerald-300">Kanal Resmi Publik B2C</span>
                                            </template>
                                            <template x-if="!st.is_online">
                                                <div class="flex items-center gap-2">
                                                    <span class="text-xs font-mono font-bold px-2 py-0.5 rounded bg-slate-100 dark:bg-stone-700 text-slate-800 dark:text-stone-200" x-text="st.address_number ? 'AN8: ' + st.address_number : st.code"></span>
                                                    <span class="text-xs text-slate-500 dark:text-stone-400 font-semibold" x-text="'Tier: ' + st.tier_name"></span>
                                                </div>
                                            </template>
                                        </div>
                                    </div>
                                </div>
                                <div class="text-right shrink-0">
                                    <span class="px-2.5 py-1 rounded-lg bg-blue-50 dark:bg-sky-900/50 hover:bg-blue-100 dark:hover:bg-sky-800/70 text-blue-700 dark:text-sky-300 text-xs font-bold transition-colors">Buka &rarr;</span>
                                </div>
                            </div>
                        </template>

                        <div x-show="!loading && stores.length === 0" class="p-8 text-center text-sm text-slate-400 dark:text-stone-500">
                            <span class="material-symbols-outlined text-[32px] text-slate-300 dark:text-stone-600 block mb-1">store</span>
                            Tidak ada kanal atau toko yang cocok.
                        </div>
                    </div>

                    <!-- Footer -->
                    <div class="px-4 py-2 bg-slate-50 dark:bg-stone-900 text-xs text-slate-500 dark:text-stone-400 text-center font-semibold">
                        <span x-text="stores.length + ' kanal & toko tersedia'"></span>
                    </div>
                </div>
            </div>
        </div>
    @endif

    @if($isStoreView)
        <div class="mb-5 p-4 rounded-2xl bg-blue-50/70 dark:bg-blue-950/40 border border-blue-200 dark:border-blue-900/60 flex flex-col sm:flex-row sm:items-center justify-between gap-3 shadow-2xs">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-blue-600 text-white flex items-center justify-center font-bold shadow-xs shrink-0">
                    <span class="material-symbols-outlined text-[22px]">storefront</span>
                </div>
                <div>
                    <h2 class="text-sm font-bold text-blue-950 dark:text-blue-100 flex items-center gap-2">
                        <span>Katalog Toko: {{ $currentStore['name'] ?? 'Toko Dealer' }}</span>
                        <span class="text-[11px] font-mono font-semibold px-2 py-0.5 rounded bg-blue-100 dark:bg-blue-900/70 text-blue-800 dark:text-blue-200 border border-blue-200 dark:border-blue-800">AN8: {{ $currentStore['address_number'] ?? '-' }}</span>
                    </h2>
                    <p class="text-xs text-blue-800/80 dark:text-blue-300/80 mt-0.5">
                        Katalog terisolasi khusus toko dealer (Tabel: <code>finish_goods.store_products</code>). Aman dari perubahan toko lain & web publik.
                    </p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                @if(session('assisted_by_sales'))
                    <span class="px-2.5 py-1 rounded-md text-[11px] font-bold bg-amber-100 dark:bg-amber-950/80 text-amber-800 dark:text-amber-300 border border-amber-300 dark:border-amber-700">
                        Mode Asistensi Sales
                    </span>
                    <a href="{{ route('stores.clear-context') }}" class="px-3 py-1 bg-white dark:bg-stone-800 border border-slate-300 dark:border-stone-700 text-slate-700 dark:text-stone-200 hover:bg-slate-50 dark:hover:bg-stone-700 rounded-lg text-xs font-semibold shadow-2xs">
                        Ganti Toko
                    </a>
                @endif
                <span class="px-3 py-1 rounded-full text-xs font-extrabold bg-blue-600 text-white shadow-2xs">
                    {{ number_format($products->total()) }} Artikel Toko
                </span>
            </div>
        </div>

        @if(isset($activePromo) && $activePromo)
            <div class="mb-5 p-4 rounded-2xl bg-gradient-to-r from-rose-50 via-pink-50 to-amber-50 dark:from-rose-950/40 dark:via-pink-950/30 dark:to-amber-950/30 border border-rose-200 dark:border-rose-900/60 flex flex-col sm:flex-row sm:items-center justify-between gap-3 shadow-2xs">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-rose-600 text-white flex items-center justify-center font-bold shadow-xs shrink-0">
                        <span class="material-symbols-outlined text-[22px]">local_offer</span>
                    </div>
                    <div>
                        <div class="flex items-center gap-2 flex-wrap">
                            @if((int)$activePromo->scope_store_type === 2)
                                <span class="text-[10px] font-extrabold px-2 py-0.5 rounded bg-rose-700 text-white tracking-wider">
                                    PROMO KHUSUS TOKO: {{ $currentStore['name'] ?? 'Toko Dealer' }}
                                </span>
                            @elseif((int)$activePromo->scope_store_type === 1)
                                <span class="text-[10px] font-extrabold px-2 py-0.5 rounded bg-amber-600 text-white tracking-wider">
                                    PROMO TIER: {{ $currentStore['tier_name'] ?? 'TIER KHUSUS' }}
                                </span>
                            @elseif((int)$activePromo->scope_store_type === 3)
                                <span class="text-[10px] font-extrabold px-2 py-0.5 rounded bg-indigo-600 text-white tracking-wider">
                                    PROMO CHANNEL GROUP
                                </span>
                            @else
                                <span class="text-[10px] font-extrabold px-2 py-0.5 rounded bg-rose-600 text-white tracking-wider">
                                    PROMO PRICE SETTING AKTIF
                                </span>
                            @endif
                            <h3 class="text-sm font-bold text-rose-950 dark:text-rose-100">{{ $activePromo->title }}</h3>
                        </div>
                        <p class="text-xs text-rose-800/80 dark:text-rose-300/80 mt-1">
                            Diskon <strong>{{ (int)$activePromo->discount_type === 1 ? (float)$activePromo->discount_value.'%' : 'Rp '.number_format((float)$activePromo->discount_value, 0, ',', '.') }}</strong>
                            @if((int)$activePromo->scope === 1)
                                berlaku untuk <strong>seluruh produk</strong> di toko ini.
                            @else
                                berlaku untuk <strong>produk tertentu</strong> yang dipilih pada promo ini.
                            @endif
                            Harga promo langsung terhitung otomatis dari Base Price / Harga Jual.
                        </p>
                    </div>
                </div>
                <div class="text-right shrink-0">
                    <span class="text-[11px] font-semibold text-rose-700 dark:text-rose-300 bg-rose-100 dark:bg-rose-950/80 border border-transparent dark:border-rose-900/60 px-2.5 py-1 rounded-md">
                        Kode: {{ $activePromo->code }}
                    </span>
                </div>
            </div>
        @endif
    @else
        <div class="mb-5 p-4 rounded-2xl bg-primary/5 dark:bg-primary/10 border border-primary/20 dark:border-primary/30 flex flex-col sm:flex-row sm:items-center justify-between gap-3 shadow-2xs">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-primary text-white flex items-center justify-center font-bold shadow-xs shrink-0">
                    <span class="material-symbols-outlined text-[22px]">public</span>
                </div>
                <div>
                    <h2 class="text-sm font-bold text-slate-900 dark:text-stone-100 flex items-center gap-2">
                        <span>Katalog Kanal: Web Official Retail (D2C Publik)</span>
                        <span class="text-[11px] font-mono font-semibold px-2 py-0.5 rounded bg-primary/10 dark:bg-primary/20 text-primary">Kanal Toko Pusat</span>
                    </h2>
                    <p class="text-xs text-slate-600 dark:text-stone-400 mt-0.5">
                        Katalog produk aktif yang tampil di website utama IMG Store untuk penjualan retail online langsung ke konsumen.
                    </p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <span class="px-3 py-1 rounded-full text-xs font-extrabold bg-primary text-white shadow-2xs">
                    {{ number_format($products->total()) }} Produk Aktif
                </span>
            </div>
        </div>
    @endif

    <!-- Filter Card with overflow-visible so Autocomplete Dropdown can pop out cleanly -->
    <div class="bg-white dark:bg-stone-900 rounded-2xl shadow-sm border border-slate-200 dark:border-stone-800 p-4 mb-5 relative z-30 overflow-visible">
        <form method="GET" action="{{ route('products.index') }}" class="flex flex-col lg:flex-row lg:items-center gap-3">
            @if(request('view'))
                <input type="hidden" name="view" value="{{ request('view') }}">
            @endif
            @if(request('store_id'))
                <input type="hidden" name="store_id" value="{{ request('store_id') }}">
            @endif

            <!-- Autocomplete Search Input -->
            <div class="flex-1 relative min-w-[280px]" 
                 x-data="{
                    searchQuery: '{{ request('search') }}',
                    suggestions: [],
                    isOpen: false,
                    isLoading: false,
                    selectedIndex: -1,
                    fetchSuggestions() {
                        if (this.searchQuery.trim().length < 1) {
                            this.suggestions = [];
                            this.isOpen = false;
                            return;
                        }
                        this.isLoading = true;
                        fetch('{{ route('products.autocomplete') }}?q=' + encodeURIComponent(this.searchQuery) + '&view={{ $isStoreView ? 'store' : 'online' }}&store_id={{ $currentStore['id'] ?? '' }}', {
                            headers: {
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        })
                            .then(r => r.json())
                            .then(data => {
                                this.suggestions = data;
                                this.isOpen = Array.isArray(data) && data.length > 0;
                                this.isLoading = false;
                            })
                            .catch(err => {
                                console.error('Autocomplete fetch error:', err);
                                this.isLoading = false;
                            });
                    },
                    selectSuggestion(item) {
                        this.searchQuery = item.name;
                        this.isOpen = false;
                        $nextTick(() => {
                            $el.closest('form').submit();
                        });
                    },
                    onKeyDown(e) {
                        if (!this.isOpen) return;
                        if (e.key === 'ArrowDown') {
                            e.preventDefault();
                            this.selectedIndex = (this.selectedIndex + 1) % this.suggestions.length;
                        } else if (e.key === 'ArrowUp') {
                            e.preventDefault();
                            this.selectedIndex = (this.selectedIndex - 1 + this.suggestions.length) % this.suggestions.length;
                        } else if (e.key === 'Enter' && this.selectedIndex >= 0) {
                            e.preventDefault();
                            this.selectSuggestion(this.suggestions[this.selectedIndex]);
                        } else if (e.key === 'Escape') {
                            this.isOpen = false;
                        }
                    }
                 }"
                 @click.outside="isOpen = false">
                
                <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-[20px]">search</span>
                <input type="text" 
                       name="search" 
                       x-model="searchQuery"
                       @input.debounce.250ms="fetchSuggestions()"
                       @focus="if (suggestions.length > 0) { isOpen = true; } else if (searchQuery.trim().length >= 1) { fetchSuggestions(); }"
                       @keydown="onKeyDown($event)"
                       placeholder="Cari nama kasur, kode SKU, atau brand..." 
                       autocomplete="off"
                       class="w-full h-11 pl-10 pr-10 border-2 border-slate-300 hover:border-slate-400 focus:border-primary rounded-xl focus:ring-2 focus:ring-primary/20 focus:outline-none text-sm font-medium bg-white transition-all shadow-xs">
                       
                <!-- Spinner or Clear Button -->
                <div class="absolute right-3 top-1/2 -translate-y-1/2 flex items-center">
                    <span x-show="isLoading" class="material-symbols-outlined text-[20px] text-slate-400 animate-spin">progress_activity</span>
                    <button type="button" x-show="!isLoading && searchQuery.length > 0" @click="searchQuery = ''; suggestions = []; isOpen = false; $el.closest('form').submit()" class="text-slate-400 hover:text-slate-600 transition-colors">
                        <span class="material-symbols-outlined text-[18px]">cancel</span>
                    </button>
                </div>

                <!-- Autocomplete Dropdown List -->
                <div x-show="isOpen" 
                     x-transition:enter="transition ease-out duration-150"
                     x-transition:enter-start="opacity-0 translate-y-1"
                     x-transition:enter-end="opacity-100 translate-y-0"
                     x-transition:leave="transition ease-in duration-100"
                     x-transition:leave-start="opacity-100 translate-y-0"
                     x-transition:leave-end="opacity-0 translate-y-1"
                     class="absolute left-0 right-0 top-full mt-2 bg-white border border-slate-200 rounded-2xl shadow-2xl z-50 overflow-hidden divide-y divide-slate-100 max-h-80 overflow-y-auto">
                    <div class="px-4 py-2 bg-slate-50 text-xs font-bold text-slate-500 uppercase tracking-wider flex items-center justify-between border-b border-slate-100">
                        <span>Saran Produk Cepat</span>
                        <span class="text-xs font-semibold text-slate-600" x-text="suggestions.length + ' item ditemukan'"></span>
                    </div>
                    <template x-for="(item, idx) in suggestions" :key="item.id">
                        <div @click="selectSuggestion(item)"
                             @mouseenter="selectedIndex = idx"
                             :class="{ 'bg-blue-50/80': selectedIndex === idx }"
                             class="p-3 hover:bg-slate-50 cursor-pointer flex items-center justify-between gap-3 transition-colors">
                            <div class="flex items-center gap-3 min-w-0">
                                <div class="w-10 h-10 rounded-xl bg-slate-100 border border-slate-200 flex items-center justify-center shrink-0 text-slate-500 overflow-hidden shadow-2xs">
                                    <template x-if="item.image">
                                        <img :src="item.image" :alt="item.name" class="w-full h-full object-cover">
                                    </template>
                                    <template x-if="!item.image">
                                        <span class="material-symbols-outlined text-[22px] text-blue-600">bed</span>
                                    </template>
                                </div>
                                <div class="min-w-0">
                                    <p class="text-sm font-bold text-slate-900 truncate leading-snug" x-text="item.name"></p>
                                    <div class="flex items-center gap-2 mt-0.5">
                                        <span class="text-xs font-mono font-bold px-2 py-0.5 rounded bg-slate-100 text-slate-700" x-text="item.sku"></span>
                                        <span class="text-xs text-slate-500 font-medium" x-text="item.brand"></span>
                                    </div>
                                </div>
                            </div>
                            <div class="text-right shrink-0">
                                <span class="text-sm font-extrabold text-blue-700 block" x-text="item.price_label"></span>
                                <template x-if="item.is_floor_display">
                                    <span class="text-[11px] font-bold text-emerald-600">Display Showroom</span>
                                </template>
                            </div>
                        </div>
                    </template>
                </div>
            </div>

            <div class="flex flex-wrap sm:flex-nowrap items-center gap-3">
                <!-- Autocomplete Kategori Filter -->
                <div class="w-full sm:w-48 relative"
                     :class="{ 'z-40': open }"
                     x-data="{
                         open: false,
                         search: '',
                         selectedId: '{{ request('category_id') }}',
                         items: {{ Js::from($categories->map(fn($c) => ['id' => (string)$c->id, 'name' => $c->name])) }},
                         get selectedName() {
                             const found = this.items.find(i => String(i.id) === String(this.selectedId));
                             return found ? found.name : '';
                         },
                         get filteredItems() {
                             if (!this.search.trim()) return this.items;
                             const q = this.search.toLowerCase();
                             return this.items.filter(i => i.name.toLowerCase().includes(q));
                         },
                         select(item) {
                             this.selectedId = item ? item.id : '';
                             this.open = false;
                             this.search = '';
                             $nextTick(() => {
                                 $el.closest('form').submit();
                             });
                         },
                         clear(e) {
                             e.stopPropagation();
                             this.select(null);
                         }
                     }"
                     @click.outside="open = false; search = ''"
                     @keydown.escape.window="open = false">
                     
                    <input type="hidden" name="category_id" :value="selectedId">

                    <!-- Trigger -->
                    <div role="button" 
                         tabindex="0"
                         @click="open = !open; if(open) { $nextTick(() => $refs.catSearch?.focus()); }"
                         @keydown.enter.prevent="open = !open; if(open) { $nextTick(() => $refs.catSearch?.focus()); }"
                         class="w-full h-11 px-3 border-2 rounded-xl text-left flex items-center justify-between gap-1.5 transition-all shadow-xs bg-white text-sm font-semibold cursor-pointer select-none"
                         :class="selectedId ? 'border-primary text-slate-900 bg-primary/5' : 'border-slate-300 hover:border-slate-400 text-slate-700'">
                        <div class="flex items-center gap-1.5 min-w-0">
                            <span class="material-symbols-outlined text-[18px] text-slate-400 shrink-0">category</span>
                            <span class="truncate block text-xs sm:text-sm font-semibold" x-text="selectedName ? selectedName : 'Semua Kategori'"></span>
                        </div>
                        <div class="flex items-center gap-1 shrink-0">
                            <template x-if="selectedId">
                                <span role="button" @click.stop="clear($event)" class="text-slate-400 hover:text-danger p-0.5 rounded transition-colors inline-flex items-center cursor-pointer" title="Hapus filter kategori">
                                    <span class="material-symbols-outlined text-[16px]">close</span>
                                </span>
                            </template>
                            <span class="material-symbols-outlined text-[18px] text-slate-400 transition-transform duration-200" :class="open ? 'rotate-180 text-primary' : ''">expand_more</span>
                        </div>
                    </div>

                    <!-- Dropdown Popover -->
                    <div x-show="open" 
                         x-transition:enter="transition ease-out duration-150"
                         x-transition:enter-start="opacity-0 translate-y-1"
                         x-transition:enter-end="opacity-100 translate-y-0"
                         x-transition:leave="transition ease-in duration-100"
                         x-transition:leave-start="opacity-100 translate-y-0"
                         x-transition:leave-end="opacity-0 translate-y-1"
                         class="absolute left-0 top-full mt-1.5 w-64 bg-white border border-slate-200 rounded-xl shadow-xl z-50 overflow-hidden divide-y divide-slate-100"
                         style="display: none;">
                         
                        <div class="p-2 bg-slate-50 relative">
                            <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 text-[16px]">search</span>
                            <input type="text" 
                                   x-ref="catSearch"
                                   x-model="search"
                                   @keydown.enter.prevent="if (filteredItems.length > 0) select(filteredItems[0])"
                                   placeholder="Cari kategori..." 
                                   class="w-full h-8 pl-8 pr-2 text-xs font-medium bg-white border border-slate-200 rounded-lg focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary/20">
                        </div>

                        <div class="max-h-56 overflow-y-auto divide-y divide-slate-50 text-xs font-semibold py-1">
                            <div @click="select(null)"
                                 class="px-3 py-2 hover:bg-slate-100 cursor-pointer flex items-center justify-between text-slate-600 transition-colors"
                                 :class="!selectedId ? 'bg-primary/10 text-primary font-bold' : ''">
                                <span>Semua Kategori</span>
                                <span x-show="!selectedId" class="material-symbols-outlined text-[16px] text-primary">check</span>
                            </div>
                            <template x-for="item in filteredItems" :key="item.id">
                                <div @click="select(item)"
                                     class="px-3 py-2 hover:bg-slate-100 cursor-pointer flex items-center justify-between text-slate-700 transition-colors"
                                     :class="String(selectedId) === String(item.id) ? 'bg-primary/10 text-primary font-bold' : ''">
                                    <span x-text="item.name" class="truncate"></span>
                                    <span x-show="String(selectedId) === String(item.id)" class="material-symbols-outlined text-[16px] text-primary">check</span>
                                </div>
                            </template>
                            <div x-show="filteredItems.length === 0" class="px-3 py-3 text-center text-xs text-slate-400 italic">
                                Kategori tidak ditemukan
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Autocomplete Brand Filter -->
                <div class="w-full sm:w-44 relative"
                     :class="{ 'z-40': open }"
                     x-data="{
                         open: false,
                         search: '',
                         selectedId: '{{ request('brand_id') }}',
                         items: {{ Js::from($brands->map(fn($b) => ['id' => (string)$b->id, 'name' => $b->name])) }},
                         get selectedName() {
                             const found = this.items.find(i => String(i.id) === String(this.selectedId));
                             return found ? found.name : '';
                         },
                         get filteredItems() {
                             if (!this.search.trim()) return this.items;
                             const q = this.search.toLowerCase();
                             return this.items.filter(i => i.name.toLowerCase().includes(q));
                         },
                         select(item) {
                             this.selectedId = item ? item.id : '';
                             this.open = false;
                             this.search = '';
                             $nextTick(() => {
                                 $el.closest('form').submit();
                             });
                         },
                         clear(e) {
                             e.stopPropagation();
                             this.select(null);
                         }
                     }"
                     @click.outside="open = false; search = ''"
                     @keydown.escape.window="open = false">
                     
                    <input type="hidden" name="brand_id" :value="selectedId">

                    <!-- Trigger -->
                    <div role="button" 
                         tabindex="0"
                         @click="open = !open; if(open) { $nextTick(() => $refs.brandSearch?.focus()); }"
                         @keydown.enter.prevent="open = !open; if(open) { $nextTick(() => $refs.brandSearch?.focus()); }"
                         class="w-full h-11 px-3 border-2 rounded-xl text-left flex items-center justify-between gap-1.5 transition-all shadow-xs bg-white text-sm font-semibold cursor-pointer select-none"
                         :class="selectedId ? 'border-primary text-slate-900 bg-primary/5' : 'border-slate-300 hover:border-slate-400 text-slate-700'">
                        <div class="flex items-center gap-1.5 min-w-0">
                            <span class="material-symbols-outlined text-[18px] text-slate-400 shrink-0">branding_watermark</span>
                            <span class="truncate block text-xs sm:text-sm font-semibold" x-text="selectedName ? selectedName : 'Semua Brand'"></span>
                        </div>
                        <div class="flex items-center gap-1 shrink-0">
                            <template x-if="selectedId">
                                <span role="button" @click.stop="clear($event)" class="text-slate-400 hover:text-danger p-0.5 rounded transition-colors inline-flex items-center cursor-pointer" title="Hapus filter brand">
                                    <span class="material-symbols-outlined text-[16px]">close</span>
                                </span>
                            </template>
                            <span class="material-symbols-outlined text-[18px] text-slate-400 transition-transform duration-200" :class="open ? 'rotate-180 text-primary' : ''">expand_more</span>
                        </div>
                    </div>

                    <!-- Dropdown Popover -->
                    <div x-show="open" 
                         x-transition:enter="transition ease-out duration-150"
                         x-transition:enter-start="opacity-0 translate-y-1"
                         x-transition:enter-end="opacity-100 translate-y-0"
                         x-transition:leave="transition ease-in duration-100"
                         x-transition:leave-start="opacity-100 translate-y-0"
                         x-transition:leave-end="opacity-0 translate-y-1"
                         class="absolute left-0 top-full mt-1.5 w-60 bg-white border border-slate-200 rounded-xl shadow-xl z-50 overflow-hidden divide-y divide-slate-100"
                         style="display: none;">
                         
                        <div class="p-2 bg-slate-50 relative">
                            <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 text-[16px]">search</span>
                            <input type="text" 
                                   x-ref="brandSearch"
                                   x-model="search"
                                   @keydown.enter.prevent="if (filteredItems.length > 0) select(filteredItems[0])"
                                   placeholder="Cari brand..." 
                                   class="w-full h-8 pl-8 pr-2 text-xs font-medium bg-white border border-slate-200 rounded-lg focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary/20">
                        </div>

                        <div class="max-h-56 overflow-y-auto divide-y divide-slate-50 text-xs font-semibold py-1">
                            <div @click="select(null)"
                                 class="px-3 py-2 hover:bg-slate-100 cursor-pointer flex items-center justify-between text-slate-600 transition-colors"
                                 :class="!selectedId ? 'bg-primary/10 text-primary font-bold' : ''">
                                <span>Semua Brand</span>
                                <span x-show="!selectedId" class="material-symbols-outlined text-[16px] text-primary">check</span>
                            </div>
                            <template x-for="item in filteredItems" :key="item.id">
                                <div @click="select(item)"
                                     class="px-3 py-2 hover:bg-slate-100 cursor-pointer flex items-center justify-between text-slate-700 transition-colors"
                                     :class="String(selectedId) === String(item.id) ? 'bg-primary/10 text-primary font-bold' : ''">
                                    <span x-text="item.name" class="truncate"></span>
                                    <span x-show="String(selectedId) === String(item.id)" class="material-symbols-outlined text-[16px] text-primary">check</span>
                                </div>
                            </template>
                            <div x-show="filteredItems.length === 0" class="px-3 py-3 text-center text-xs text-slate-400 italic">
                                Brand tidak ditemukan
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Autocomplete Tag Filter -->
                <div class="w-full sm:w-44 relative"
                     :class="{ 'z-40': open }"
                     x-data="{
                         open: false,
                         search: '',
                         selectedId: '{{ request('tag_id') }}',
                         items: {{ Js::from(($tags ?? collect())->map(fn($t) => ['id' => (string)$t->id, 'name' => $t->name])) }},
                         get selectedName() {
                             const found = this.items.find(i => String(i.id) === String(this.selectedId));
                             return found ? found.name : '';
                         },
                         get filteredItems() {
                             if (!this.search.trim()) return this.items;
                             const q = this.search.toLowerCase();
                             return this.items.filter(i => i.name.toLowerCase().includes(q));
                         },
                         select(item) {
                             this.selectedId = item ? item.id : '';
                             this.open = false;
                             this.search = '';
                             $nextTick(() => {
                                 $el.closest('form').submit();
                             });
                         },
                         clear(e) {
                             e.stopPropagation();
                             this.select(null);
                         }
                     }"
                     @click.outside="open = false; search = ''"
                     @keydown.escape.window="open = false">
                     
                    <input type="hidden" name="tag_id" :value="selectedId">

                    <!-- Trigger -->
                    <div role="button" 
                         tabindex="0"
                         @click="open = !open; if(open) { $nextTick(() => $refs.tagSearch?.focus()); }"
                         @keydown.enter.prevent="open = !open; if(open) { $nextTick(() => $refs.tagSearch?.focus()); }"
                         class="w-full h-11 px-3 border-2 rounded-xl text-left flex items-center justify-between gap-1.5 transition-all shadow-xs bg-white text-sm font-semibold cursor-pointer select-none"
                         :class="selectedId ? 'border-primary text-slate-900 bg-primary/5' : 'border-slate-300 hover:border-slate-400 text-slate-700'">
                        <div class="flex items-center gap-1.5 min-w-0">
                            <span class="material-symbols-outlined text-[18px] text-slate-400 shrink-0">label</span>
                            <span class="truncate block text-xs sm:text-sm font-semibold" x-text="selectedName ? selectedName : 'Semua Tag'"></span>
                        </div>
                        <div class="flex items-center gap-1 shrink-0">
                            <template x-if="selectedId">
                                <span role="button" @click.stop="clear($event)" class="text-slate-400 hover:text-danger p-0.5 rounded transition-colors inline-flex items-center cursor-pointer" title="Hapus filter tag">
                                    <span class="material-symbols-outlined text-[16px]">close</span>
                                </span>
                            </template>
                            <span class="material-symbols-outlined text-[18px] text-slate-400 transition-transform duration-200" :class="open ? 'rotate-180 text-primary' : ''">expand_more</span>
                        </div>
                    </div>

                    <!-- Dropdown Popover -->
                    <div x-show="open" 
                         x-transition:enter="transition ease-out duration-150"
                         x-transition:enter-start="opacity-0 translate-y-1"
                         x-transition:enter-end="opacity-100 translate-y-0"
                         x-transition:leave="transition ease-in duration-100"
                         x-transition:leave-start="opacity-100 translate-y-0"
                         x-transition:leave-end="opacity-0 translate-y-1"
                         class="absolute left-0 sm:right-0 sm:left-auto top-full mt-1.5 w-60 bg-white border border-slate-200 rounded-xl shadow-xl z-50 overflow-hidden divide-y divide-slate-100"
                         style="display: none;">
                         
                        <div class="p-2 bg-slate-50 relative">
                            <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 text-[16px]">search</span>
                            <input type="text" 
                                   x-ref="tagSearch"
                                   x-model="search"
                                   @keydown.enter.prevent="if (filteredItems.length > 0) select(filteredItems[0])"
                                   placeholder="Cari tag..." 
                                   class="w-full h-8 pl-8 pr-2 text-xs font-medium bg-white border border-slate-200 rounded-lg focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary/20">
                        </div>

                        <div class="max-h-56 overflow-y-auto divide-y divide-slate-50 text-xs font-semibold py-1">
                            <div @click="select(null)"
                                 class="px-3 py-2 hover:bg-slate-100 cursor-pointer flex items-center justify-between text-slate-600 transition-colors"
                                 :class="!selectedId ? 'bg-primary/10 text-primary font-bold' : ''">
                                <span>Semua Tag</span>
                                <span x-show="!selectedId" class="material-symbols-outlined text-[16px] text-primary">check</span>
                            </div>
                            <template x-for="item in filteredItems" :key="item.id">
                                <div @click="select(item)"
                                     class="px-3 py-2 hover:bg-slate-100 cursor-pointer flex items-center justify-between text-slate-700 transition-colors"
                                     :class="String(selectedId) === String(item.id) ? 'bg-primary/10 text-primary font-bold' : ''">
                                    <span x-text="item.name" class="truncate"></span>
                                    <span x-show="String(selectedId) === String(item.id)" class="material-symbols-outlined text-[16px] text-primary">check</span>
                                </div>
                            </template>
                            <div x-show="filteredItems.length === 0" class="px-3 py-3 text-center text-xs text-slate-400 italic">
                                Tag tidak ditemukan
                            </div>
                        </div>
                    </div>
                </div>
                <div class="flex items-center gap-2 shrink-0">
                    <button type="submit" class="h-11 px-5 bg-primary text-white hover:bg-primary/90 rounded-xl text-sm font-bold transition-colors shadow-sm inline-flex items-center gap-1.5 cursor-pointer">
                        <span class="material-symbols-outlined text-[18px]">filter_list</span>
                        <span>Filter</span>
                    </button>
                    @if(request()->hasAny(['search', 'category_id', 'brand_id', 'tag_id']))
                        <a href="{{ route('products.index', array_filter(['view' => request('view'), 'store_id' => request('store_id')])) }}" class="h-11 px-3.5 bg-danger/10 text-danger hover:bg-danger/20 rounded-xl text-sm font-bold transition-colors inline-flex items-center gap-1" title="Reset Filters">
                            <span class="material-symbols-outlined text-[18px]">restart_alt</span>
                            <span class="hidden sm:inline">Reset</span>
                        </a>
                    @endif
                </div>
            </div>
        </form>
    </div>

    <!-- Table Container Card -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden mb-8"
         x-data="{ editModalOpen: false, currentId: '', currentName: '', currentPrice: '', currentDisplay: true, currentPos: true, formAction: '' }">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200 text-xs font-bold text-slate-500 uppercase tracking-wider">
                        <th class="px-6 py-4">Produk & SKU</th>
                        <th class="px-6 py-4">Kategori & Brand</th>
                        <th class="px-6 py-4">Harga Jual</th>
                        <th class="px-6 py-4">Promo</th>
                        <th class="px-6 py-4">Status & Kanal</th>
                        <th class="px-6 py-4 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-sm">
                    @if($isStoreView)
                        @forelse($products ?? [] as $prod)
                            @php
                                $imgUrl = $prod->variant?->getImageUrlAttribute() ?: ($prod->variant?->product?->thumbnail_url ?: ($prod->variant?->product?->images->first()?->url ?? ''));
                            @endphp
                            <tr class="hover:bg-slate-50/70 transition-colors">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-12 h-12 bg-neutral-50 rounded-xl overflow-hidden shrink-0 border border-slate-200 flex items-center justify-center shadow-2xs">
                                            @if($imgUrl)
                                                <img class="w-full h-full object-contain p-0.5" src="{{ $imgUrl }}" alt="{{ $prod->item_name }}">
                                            @else
                                                <span class="material-symbols-outlined text-[22px] text-blue-600">bed</span>
                                            @endif
                                        </div>
                                        <div class="min-w-0">
                                            <div class="font-bold text-slate-900 text-[14px] leading-snug">{{ $prod->item_name }}</div>
                                            <div class="flex items-center gap-1.5 mt-1 flex-wrap">
                                                <span class="font-mono text-[11px] font-bold px-2 py-0.5 rounded bg-slate-100 text-slate-700 border border-slate-200">
                                                    {{ $prod->sku }}
                                                </span>
                                                @if($prod->item_size)
                                                    <span class="text-[11px] font-mono text-slate-600 font-semibold px-1.5 py-0.5 rounded bg-slate-50 border border-slate-200">
                                                        {{ $prod->item_size }}
                                                    </span>
                                                @endif
                                                @if($prod->short_item_no)
                                                    <span class="text-[10px] font-mono text-slate-400">#{{ $prod->short_item_no }}</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="font-bold text-xs text-slate-900">{{ $prod->brand_name ?: 'ROYAL' }}</div>
                                    <div class="text-xs text-slate-500 font-medium mt-0.5">{{ $prod->category_name ?: '-' }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="font-extrabold text-sm text-slate-900 font-mono">
                                        Rp {{ number_format((float)$prod->selling_price, 0, ',', '.') }}
                                    </div>
                                    @if($prod->custom_retail_price > 0)
                                        <span class="text-[10px] text-blue-600 font-bold block">(Custom Toko)</span>
                                    @else
                                        <span class="text-[10px] text-slate-400 block font-sans">Modal: Rp {{ number_format((float)$prod->base_price, 0, ',', '.') }}</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($prod->active_promo && $prod->promo_discount > 0)
                                        <div>
                                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-md text-[11px] font-extrabold bg-rose-50 text-rose-700 border border-rose-200" title="{{ $prod->active_promo->title }}">
                                                <span class="material-symbols-outlined text-[13px]">local_offer</span>
                                                <span>{{ (int)$prod->active_promo->discount_type === 1 ? (float)$prod->active_promo->discount_value.'%' : 'Rp '.number_format((float)$prod->active_promo->discount_value, 0, ',', '.') }}</span>
                                            </span>
                                            <div class="font-extrabold text-rose-700 text-xs font-mono mt-0.5">
                                                Rp {{ number_format((float)$prod->promo_price, 0, ',', '.') }}
                                            </div>
                                        </div>
                                    @else
                                        <span class="text-xs text-slate-400">-</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex flex-col gap-1 items-start">
                                        @if($prod->is_floor_display)
                                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Showroom
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-medium bg-slate-100 text-slate-600">
                                                Gudang
                                            </span>
                                        @endif
                                        @if($prod->is_active_for_pos)
                                            <span class="text-[10px] font-semibold text-blue-600">POS Kasir: ON</span>
                                        @else
                                            <span class="text-[10px] text-slate-400">POS Kasir: OFF</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-center whitespace-nowrap">
                                    <button type="button"
                                            @click="
                                                editModalOpen = true;
                                                currentId = '{{ $prod->id }}';
                                                currentName = '{{ addslashes($prod->item_name) }}';
                                                currentPrice = '{{ (float)$prod->selling_price }}';
                                                currentDisplay = {{ $prod->is_floor_display ? 'true' : 'false' }};
                                                currentPos = {{ $prod->is_active_for_pos ? 'true' : 'false' }};
                                                formAction = '{{ route('store-products.update', $prod->id) }}';
                                            "
                                            class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg bg-blue-50 text-blue-700 hover:bg-blue-100 font-bold text-xs transition-colors border border-blue-200 shadow-2xs">
                                        <span class="material-symbols-outlined text-[15px]">tune</span>
                                        <span>Atur</span>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-12 text-center text-slate-500">
                                    <span class="material-symbols-outlined text-4xl text-slate-300 mb-2 block">inbox</span>
                                    <p class="font-bold text-sm text-slate-800">Belum ada produk di katalog toko ini.</p>
                                    <p class="text-xs text-slate-400 mt-1">Produk dapat disalin dari Master Data JDE ke toko ini.</p>
                                </td>
                            </tr>
                        @endforelse
                    @else
                        @forelse($products ?? [] as $product)
                            @php
                                $imgUrl = $product->thumbnail_url ?: ($product->images->first()?->url ?? '');
                                $minPrice = $product->variants->min('sell_price') ?? 0;
                                $maxPrice = $product->variants->max('sell_price') ?? 0;
                            @endphp
                            <tr class="hover:bg-slate-50/70 transition-colors group">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-12 h-12 bg-neutral-50 rounded-xl overflow-hidden shrink-0 border border-slate-200 flex items-center justify-center shadow-2xs">
                                            @if($imgUrl)
                                                <img class="w-full h-full object-contain p-0.5" src="{{ $imgUrl }}" alt="{{ $product->name }}">
                                            @else
                                                <span class="material-symbols-outlined text-[22px] text-primary">bed</span>
                                            @endif
                                        </div>
                                        <div class="min-w-0">
                                            <a href="{{ route('products.show', $product->id) }}" class="font-bold text-slate-900 text-[14px] leading-snug hover:text-primary transition-colors block">{{ $product->name }}</a>
                                            <div class="flex items-center gap-1.5 mt-1 flex-wrap">
                                                <span class="font-mono text-[11px] font-bold px-2 py-0.5 rounded bg-slate-100 text-slate-700 border border-slate-200">
                                                    {{ $product->code ?: 'WEB' }}
                                                </span>
                                                @if($product->variants && $product->variants->isNotEmpty())
                                                    @foreach($product->variants->take(3) as $variant)
                                                        @if(!empty($variant->sku))
                                                            <span class="text-[10px] bg-slate-50 text-slate-600 px-1.5 py-0.5 rounded border border-slate-200 font-mono">{{ $variant->sku }}</span>
                                                        @endif
                                                    @endforeach
                                                    @if($product->variants->count() > 3)
                                                        <span class="text-[10px] text-slate-500 px-1 font-mono">+{{ $product->variants->count() - 3 }}</span>
                                                    @endif
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="font-bold text-xs text-slate-900">{{ $product->brand->name ?? '-' }}</div>
                                    <div class="text-xs text-slate-500 font-medium mt-0.5">{{ $product->category->name ?? '-' }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="font-extrabold text-sm text-slate-900 font-mono">
                                        @if($minPrice == $maxPrice)
                                            Rp {{ number_format($minPrice, 0, ',', '.') }}
                                        @else
                                            Rp {{ number_format($minPrice, 0, ',', '.') }} - Rp {{ number_format($maxPrice, 0, ',', '.') }}
                                        @endif
                                    </div>
                                    <span class="text-[10px] text-slate-400 block font-sans">Harga Jual Web</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($product->tags && $product->tags->isNotEmpty())
                                        <div class="flex flex-wrap gap-1">
                                            @foreach($product->tags as $t)
                                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-md text-[11px] font-extrabold bg-rose-50 text-rose-700 border border-rose-200">
                                                    <span class="material-symbols-outlined text-[13px]">local_offer</span>
                                                    <span>{{ $t->name }}</span>
                                                </span>
                                            @endforeach
                                        </div>
                                    @else
                                        <span class="text-xs text-slate-400">-</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex flex-col gap-1 items-start">
                                        @if($product->status)
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 bg-emerald-50 text-emerald-700 border border-emerald-200 rounded-full text-[11px] font-bold">
                                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Aktif
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 bg-rose-50 text-rose-700 border border-rose-200 rounded-full text-[11px] font-bold">
                                                <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span> Nonaktif
                                            </span>
                                        @endif
                                        <span class="text-[10px] font-semibold text-primary">🌐 Web IMG Store</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-center whitespace-nowrap">
                                    <div class="flex items-center gap-1 justify-center">
                                        @can('products.show')
                                            <a href="{{ route('products.show', $product->id) }}" class="p-1.5 rounded-lg text-slate-500 hover:text-blue-600 hover:bg-blue-50 transition-colors" title="Lihat Detail">
                                                <span class="material-symbols-outlined text-[18px]">visibility</span>
                                            </a>
                                        @endcan
                                        @can('products.edit')
                                            <a href="{{ route('products.edit', $product->id) }}" class="p-1.5 rounded-lg text-slate-500 hover:text-amber-600 hover:bg-amber-50 transition-colors" title="Edit Produk">
                                                <span class="material-symbols-outlined text-[18px]">edit</span>
                                            </a>
                                        @endcan
                                        @can('products.destroy')
                                            <button type="button" onclick="confirmDeleteProduct('{{ $product->id }}', '{{ addslashes($product->name) }}')" class="p-1.5 rounded-lg text-slate-500 hover:text-rose-600 hover:bg-rose-50 transition-colors cursor-pointer" title="Hapus Produk">
                                                <span class="material-symbols-outlined text-[18px]">delete</span>
                                            </button>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-12 text-center text-slate-500">
                                    <span class="material-symbols-outlined text-4xl text-slate-300 mb-2 block">inbox</span>
                                    <p class="font-bold text-sm text-slate-800">Tidak ada produk ditemukan.</p>
                                </td>
                            </tr>
                        @endforelse
                    @endif
                </tbody>
            </table>
        </div>
        @if(isset($products) && $products->hasPages())
            <div class="px-6 py-4 border-t border-outline-variant flex items-center justify-between">
                <p class="text-label-sm text-on-surface-variant font-medium">Showing {{ $products->firstItem() }} to {{ $products->lastItem() }} of {{ $products->total() }} products</p>
                <div class="flex gap-1">
                    {{ $products->links() }}
                </div>
            </div>
        @endif

        @if($isStoreView)
            <!-- Modal Edit Harga Jual Toko & Display -->
            <div x-show="editModalOpen" 
                 x-cloak 
                 class="fixed inset-0 z-50 overflow-y-auto bg-black/50 flex items-center justify-center p-4"
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="transition ease-in duration-150"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0">
                
                <div class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl border border-slate-200 relative"
                     @click.away="editModalOpen = false">
                    <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                        <div class="flex items-center gap-2">
                            <span class="material-symbols-outlined text-blue-600 text-[24px]">tune</span>
                            <h3 class="font-bold text-slate-900 text-base">Atur Harga & Display Toko</h3>
                        </div>
                        <button type="button" @click="editModalOpen = false" class="text-slate-400 hover:text-slate-600">
                            <span class="material-symbols-outlined text-[20px]">close</span>
                        </button>
                    </div>

                    <form method="POST" :action="formAction" class="mt-4 space-y-4">
                        @csrf
                        @method('PATCH')

                        <div>
                            <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1">Artikel Produk</label>
                            <p class="font-bold text-slate-800 text-sm" x-text="currentName"></p>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">
                                Harga Jual Toko (Rp) <span class="text-rose-500">*</span>
                            </label>
                            <input type="number" name="custom_retail_price" x-model="currentPrice" min="0" step="1000" required
                                   class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 font-mono text-sm font-bold text-slate-800 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 focus:outline-none"
                                   placeholder="Contoh: 3500000">
                            <p class="text-[11px] text-slate-500 mt-1">Harga jual retail resmi di toko ini. Promo price setting akan otomatis memotong harga ini.</p>
                        </div>

                        <div class="space-y-3 pt-2">
                            <label class="flex items-center gap-3 p-3 rounded-xl border border-slate-200 bg-slate-50 hover:bg-slate-100/70 cursor-pointer transition-colors">
                                <input type="checkbox" name="is_floor_display" value="1" x-model="currentDisplay" class="w-4 h-4 text-blue-600 rounded border-slate-300 focus:ring-blue-500">
                                <div>
                                    <span class="text-xs font-bold text-slate-800 block">Pajang di Showroom (Floor Display)</span>
                                    <span class="text-[11px] text-slate-500 block">Tandai kasur ini dipajang fisik pada lantai toko</span>
                                </div>
                            </label>

                            <label class="flex items-center gap-3 p-3 rounded-xl border border-slate-200 bg-slate-50 hover:bg-slate-100/70 cursor-pointer transition-colors">
                                <input type="checkbox" name="is_active_for_pos" value="1" x-model="currentPos" class="w-4 h-4 text-blue-600 rounded border-slate-300 focus:ring-blue-500">
                                <div>
                                    <span class="text-xs font-bold text-slate-800 block">Aktif untuk Transaksi Kasir POS</span>
                                    <span class="text-[11px] text-slate-500 block">Dapat dipilih pramuniaga saat melayani transaksi pembelian</span>
                                </div>
                            </label>
                        </div>

                        <div class="flex items-center justify-end gap-2.5 pt-4 border-t border-slate-100">
                            <button type="button" @click="editModalOpen = false" class="px-4 py-2 rounded-xl text-xs font-bold text-slate-600 hover:bg-slate-100 transition-colors">
                                Batal
                            </button>
                            <button type="submit" class="px-5 py-2 rounded-xl text-xs font-bold bg-blue-600 hover:bg-blue-700 text-white shadow-sm transition-colors">
                                Simpan Perubahan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        @endif
    </div>
@endsection

@push('scripts')
<script>
function confirmDeleteProduct(id, name) {
    if (typeof Swal !== 'undefined') {
        Swal.fire({
            title: 'Hapus Produk?',
            text: `Apakah Anda yakin ingin menghapus produk "${name}"?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#6b7280',
            confirmButtonText: 'Ya, Hapus',
            cancelButtonText: 'Batal',
            showLoaderOnConfirm: true,
            preConfirm: () => {
                return fetch(`{{ url('products') }}/${id}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    }
                })
                .then(async response => {
                    const data = await response.json();
                    if (!response.ok) {
                        throw new Error(data.message || 'Gagal menghapus produk');
                    }
                    return data;
                })
                .catch(error => {
                    Swal.showValidationMessage(error.message);
                });
            },
            allowOutsideClick: () => !Swal.isLoading()
        }).then((result) => {
            if (result.isConfirmed && result.value) {
                Swal.fire({
                    title: 'Berhasil!',
                    text: result.value.message || 'Produk berhasil dihapus.',
                    icon: 'success',
                    timer: 1500,
                    showConfirmButton: false
                }).then(() => {
                    window.location.reload();
                });
            }
        });
    } else {
        if (!confirm(`Apakah Anda yakin ingin menghapus produk "${name}"?`)) return;
        fetch(`{{ url('products') }}/${id}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            }
        })
        .then(async response => {
            const data = await response.json();
            if (!response.ok) {
                showToast('error', data.message || 'Gagal menghapus produk');
            } else {
                showToast('success', data.message || 'Produk berhasil dihapus.');
                setTimeout(() => window.location.reload(), 1000);
            }
        })
        .catch(err => {
            showToast('error', 'Terjadi kesalahan koneksi saat menghapus produk');
        });
    }
}
</script>
@endpush