@extends('layouts.app')

@section('title', 'Edit Product Bundle')

@section('content')
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="font-headline-lg text-headline-lg text-on-surface">Edit Setting Product Bundling</h1>
            <nav class="flex items-center gap-2 text-label-sm text-on-surface-variant mt-1 font-medium">
                <a href="{{ route('dashboard') }}" class="hover:text-primary transition-colors">eCommerce</a>
                <span class="material-symbols-outlined text-[14px]">chevron_right</span>
                <a href="{{ route('vouchers.index') }}" class="hover:text-primary transition-colors">Promotions</a>
                <span class="material-symbols-outlined text-[14px]">chevron_right</span>
                <a href="{{ route('bundlings.index') }}" class="hover:text-primary transition-colors">Bundling</a>
                <span class="material-symbols-outlined text-[14px]">chevron_right</span>
                <span class="text-on-surface">Edit</span>
            </nav>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('bundlings.index') }}" class="inline-flex items-center gap-1.5 px-4 py-2 border border-outline-variant text-on-surface-variant hover:bg-surface-container rounded-xl text-xs font-semibold transition-colors">
                <span class="material-symbols-outlined text-[16px]">arrow_back</span>
                <span>Batal</span>
            </a>
            <button type="submit" form="bundleForm" class="btn-save inline-flex items-center gap-2 px-5 py-2 bg-primary text-white hover:opacity-90 rounded-xl text-xs font-bold transition-all shadow-sm active:scale-95">
                <span class="material-symbols-outlined text-[18px]">save</span>
                <span>Perbarui Bundling</span>
            </button>
        </div>
    </div>

    @if($errors->any())
        <div class="mb-6 p-4 rounded-xl bg-danger/10 border border-danger/20 text-danger text-body-md">
            <div class="font-bold flex items-center gap-2 mb-1">
                <span class="material-symbols-outlined text-[20px]">error</span>
                <span>Mohon periksa kesalahan input berikut:</span>
            </div>
            <ul class="list-disc list-inside text-xs space-y-1">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @php
        $firstItem = $bundling->bundleItems->where('is_suggest', false)->first();
        $mainProductInit = [
            'product_id' => $firstItem?->product_id ?? '',
            'variant_id' => $firstItem?->variant_id ?? '',
            'search' => $firstItem?->product?->name ?? '',
        ];

        $suggestItemsInit = $bundling->bundleItems->where('is_suggest', true)->map(fn($item) => [
            'product_id' => $item->product_id,
            'variant_id' => $item->variant_id ?? '',
            'bundle_price' => $item->bundle_price !== null ? (float)$item->bundle_price : '',
            'discount_percent' => $item->discount_percent !== null ? (float)$item->discount_percent : '',
            'search' => $item->product?->name ?? '',
        ])->values();

        if ($suggestItemsInit->isEmpty()) {
            $suggestItemsInit = collect([[
                'product_id' => '',
                'variant_id' => '',
                'bundle_price' => '',
                'discount_percent' => '',
                'search' => '',
            ]]);
        }
    @endphp

    <form id="bundleForm" action="{{ route('bundlings.update', $bundling->id) }}" method="POST" enctype="multipart/form-data" class="w-full space-y-6"
         x-data="{ 
             products: @js($products),
             bundleableProducts: @js($bundleableProducts),
             mainProduct: @js($mainProductInit),
             suggestItems: @js($suggestItemsInit),
             bundleName: '{{ old('name', $bundling->name) }}',
             isNameCustom: true,

             addSuggestItem() {
                 this.suggestItems.push({ product_id: '', variant_id: '', bundle_price: '', discount_percent: '', search: '' });
             },
             removeSuggestItem(index) {
                 if (this.suggestItems.length > 1) {
                     this.suggestItems.splice(index, 1);
                 }
             },

             getProduct(id) {
                 return this.products.find(p => String(p.id) === String(id)) || this.bundleableProducts.find(p => String(p.id) === String(id));
             },
             getItemPrice(productId, variantId) {
                 const p = this.getProduct(productId);
                 if (!p) return 0;
                 if (variantId) {
                     const v = p.variants?.find(v => String(v.id) === String(variantId));
                     if (v && Number(v.sell_price) > 0) return Number(v.sell_price);
                     if (v && Number(v.price) > 0) return Number(v.price);
                 }
                 if (p.variants && p.variants.length > 0) {
                     const firstWithSell = p.variants.find(v => Number(v.sell_price) > 0);
                     if (firstWithSell) return Number(firstWithSell.sell_price);
                     const firstWithPrice = p.variants.find(v => Number(v.price) > 0);
                     if (firstWithPrice) return Number(firstWithPrice.price);
                 }
                 return Number(p.single_price || p.min_price || 0);
             },

             get mainProductPriceDisplay() {
                 const p = this.getProduct(this.mainProduct.product_id);
                 if (!p) return '-';
                 if (this.mainProduct.variant_id) {
                     const v = p.variants?.find(v => String(v.id) === String(this.mainProduct.variant_id));
                     if (v && Number(v.sell_price || v.price) > 0) {
                         return 'Rp ' + Number(v.sell_price || v.price).toLocaleString('id-ID');
                     }
                 }
                 return p.price_range_text || ('Rp ' + Number(p.min_price || 0).toLocaleString('id-ID'));
             },
             get mainProductPrice() {
                 return this.getItemPrice(this.mainProduct.product_id, this.mainProduct.variant_id);
             },
             get suggestTotalNormal() {
                 return this.suggestItems.reduce((sum, item) => {
                     return sum + this.getItemPrice(item.product_id, item.variant_id);
                 }, 0);
             },
             get suggestTotalBundle() {
                 return this.suggestItems.reduce((sum, item) => {
                     const normal = this.getItemPrice(item.product_id, item.variant_id);
                     const bPrice = (item.bundle_price !== '' && item.bundle_price !== null) ? Number(item.bundle_price) : normal;
                     return sum + bPrice;
                 }, 0);
             },
             get totalComboPrice() {
                 return this.mainProductPrice + this.suggestTotalBundle;
             },
             get totalComboPriceDisplay() {
                 const p = this.getProduct(this.mainProduct.product_id);
                 if (!p) return 'Rp ' + this.suggestTotalBundle.toLocaleString('id-ID');
                 if (this.mainProduct.variant_id) {
                     return 'Rp ' + this.totalComboPrice.toLocaleString('id-ID');
                 }
                 if (p.has_price_range && p.min_price > 0 && p.max_price > p.min_price) {
                     const minCombo = p.min_price + this.suggestTotalBundle;
                     const maxCombo = p.max_price + this.suggestTotalBundle;
                     return 'Rp ' + minCombo.toLocaleString('id-ID') + ' - Rp ' + maxCombo.toLocaleString('id-ID');
                 }
                 return 'Rp ' + this.totalComboPrice.toLocaleString('id-ID');
             },
             get totalSavings() {
                 return Math.max(0, this.suggestTotalNormal - this.suggestTotalBundle);
             },

             onSuggestDiscountChange(item) {
                 const normal = this.getItemPrice(item.product_id, item.variant_id);
                 if (normal > 0 && item.discount_percent !== '') {
                     item.bundle_price = Math.round(normal * (1 - Number(item.discount_percent) / 100));
                 }
             },
             onSuggestPriceChange(item) {
                 const normal = this.getItemPrice(item.product_id, item.variant_id);
                 if (normal > 0 && item.bundle_price !== '' && item.bundle_price !== null) {
                     item.discount_percent = Math.max(0, Math.round(((normal - Number(item.bundle_price)) / normal) * 100));
                 }
             },

             updateAutoName() {
                 if (this.isNameCustom) return;
                 const mainP = this.getProduct(this.mainProduct.product_id);
                 if (!mainP) return;
                 const suggestNames = this.suggestItems
                     .map(item => this.getProduct(item.product_id)?.name)
                     .filter(Boolean);
                 if (suggestNames.length > 0) {
                     this.bundleName = 'Bundling ' + mainP.name + ' + ' + suggestNames.join(' & ');
                 } else {
                     this.bundleName = 'Bundling ' + mainP.name;
                 }
             },

             get slug() {
                 return (this.bundleName || '')
                     .toLowerCase()
                     .trim()
                     .replace(/[^a-z0-9\s-]/g, '')
                     .replace(/[\s-]+/g, '-')
                     .replace(/^-+|-+$/g, '');
             }
         }">
        @csrf
        @method('PUT')

        <!-- 1. Produk Utama (Trigger Bundling) -->
        <div class="bg-white rounded-xl shadow-xs border border-outline-variant/40 p-5 space-y-4">
            <div class="border-b border-outline-variant/30 pb-3 flex items-center justify-between">
                <div>
                    <h2 class="text-sm font-bold text-on-surface">1. Produk Utama</h2>
                    <p class="text-xs text-on-surface-variant">Produk yang memicu penawaran bundling murah (Contoh: Kasur Matras).</p>
                </div>
                <div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" name="is_active" value="1" {{ $bundling->status ? 'checked' : '' }} class="sr-only peer">
                        <div class="w-9 h-5 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-success"></div>
                        <span class="ml-2 text-xs font-medium text-on-surface">Aktif</span>
                    </label>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Autocomplete Produk Utama -->
                <div class="relative" x-data="{ open: false }">
                    <label class="block text-xs font-semibold text-on-surface-variant mb-1">Cari & Pilih Produk Utama <span class="text-danger">*</span></label>
                    <div class="relative">
                        <input type="text" 
                               x-model="mainProduct.search" 
                               @focus="open = true" 
                               @click.outside="open = false" 
                               placeholder="Ketik untuk mencari produk utama..." 
                               class="w-full px-3 py-2 text-xs border border-outline-variant rounded-lg focus:outline-none focus:border-primary bg-white">
                        <span x-show="mainProduct.product_id" @click="mainProduct.product_id = ''; mainProduct.search = ''; mainProduct.variant_id = ''; updateAutoName()" class="absolute right-2.5 top-2 text-gray-400 hover:text-gray-600 cursor-pointer text-xs">✕</span>
                    </div>
                    <input type="hidden" name="items[0][product_id]" :value="mainProduct.product_id" required>
                    <input type="hidden" name="items[0][quantity]" value="1">

                    <div x-show="open" class="absolute z-20 w-full mt-1 bg-white border border-outline-variant rounded-lg shadow-md max-h-48 overflow-y-auto">
                        <template x-for="p in products.filter(item => !mainProduct.search || item.name.toLowerCase().includes(mainProduct.search.toLowerCase()))" :key="p.id">
                            <div @click="mainProduct.product_id = p.id; mainProduct.search = p.name; mainProduct.variant_id = ''; open = false; updateAutoName()" 
                                 class="px-3 py-2 text-xs hover:bg-surface-gray cursor-pointer flex justify-between items-center border-b border-outline-variant/10">
                                <span x-text="p.name" class="font-medium text-on-surface"></span>
                                <span class="text-primary font-bold ml-2 shrink-0" x-text="p.price_range_text || ('Rp ' + Number(p.min_price || 0).toLocaleString('id-ID'))"></span>
                            </div>
                        </template>
                    </div>
                </div>

                <!-- Varian Produk Utama (Opsional) -->
                <div>
                    <label class="block text-xs font-semibold text-on-surface-variant mb-1">Varian Produk Utama (Opsional)</label>
                    <select name="items[0][variant_id]" x-model="mainProduct.variant_id" class="w-full px-3 py-2 text-xs border border-outline-variant rounded-lg focus:outline-none focus:border-primary bg-white">
                        <option value="">-- Berlaku untuk Semua Varian --</option>
                        <template x-if="mainProduct.product_id">
                            <template x-for="v in (getProduct(mainProduct.product_id)?.variants || [])" :key="v.id">
                                <option :value="v.id" :selected="v.id == mainProduct.variant_id" x-text="`${v.variant_name} (Rp ${Number(v.sell_price || v.price || 0).toLocaleString('id-ID')})`"></option>
                            </template>
                        </template>
                    </select>
                </div>
            </div>

            <!-- Harga Produk Utama -->
            <div x-show="mainProduct.product_id" class="text-xs text-on-surface-variant flex items-center gap-2">
                <span>Harga Produk Utama:</span>
                <strong class="text-primary font-bold" x-text="mainProductPriceDisplay"></strong>
            </div>
        </div>

        <!-- 2. Produk Pelengkap yang Di-bundling (Hanya Produk Variasi <= 1) -->
        <div class="bg-white rounded-xl shadow-xs border border-outline-variant/40 p-5 space-y-4">
            <div class="border-b border-outline-variant/30 pb-3 flex items-center justify-between">
                <div>
                    <h2 class="text-sm font-bold text-on-surface">2. Produk Tambahan / Pelengkap (Harga Turun)</h2>
                    <p class="text-xs text-on-surface-variant">Produk pelengkap dengan harga khusus jika dibeli bersama produk utama (Pasti 1 harga).</p>
                </div>
                <button type="button" @click="addSuggestItem()" class="px-3 py-1.5 bg-amber-50 text-amber-800 hover:bg-amber-100 rounded-lg text-xs font-semibold transition-colors">
                    + Tambah Produk
                </button>
            </div>

            <div class="space-y-3">
                <template x-for="(sItem, sIdx) in suggestItems" :key="'s-' + sIdx">
                    <div class="p-3.5 rounded-lg border border-amber-200/70 bg-amber-50/20 space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold text-amber-900" x-text="`Produk Tambahan #${sIdx + 1}`"></span>
                            <button type="button" x-show="suggestItems.length > 1" @click="removeSuggestItem(sIdx)" class="text-danger hover:underline text-xs">
                                Hapus
                            </button>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                            <!-- Autocomplete Produk Tambahan -->
                            <div class="relative md:col-span-1" x-data="{ open: false }">
                                <label class="block text-xs font-medium text-on-surface-variant mb-1">Pilih Produk <span class="text-danger">*</span></label>
                                <div class="relative">
                                    <input type="text" 
                                           x-model="sItem.search" 
                                           @focus="open = true" 
                                           @click.outside="open = false" 
                                           placeholder="Ketik nama produk pelengkap..." 
                                           class="w-full px-3 py-2 text-xs border border-amber-300 rounded-lg focus:outline-none focus:border-amber-500 bg-white">
                                    <span x-show="sItem.product_id" @click="sItem.product_id = ''; sItem.search = ''; sItem.variant_id = ''; updateAutoName()" class="absolute right-2.5 top-2 text-gray-400 hover:text-gray-600 cursor-pointer text-xs">✕</span>
                                </div>
                                <input type="hidden" :name="`suggest_items[${sIdx}][product_id]`" :value="sItem.product_id" required>
                                <input type="hidden" :name="`suggest_items[${sIdx}][variant_id]`" :value="sItem.variant_id">
                                <input type="hidden" :name="`suggest_items[${sIdx}][quantity]`" value="1">

                                <div x-show="open" class="absolute z-20 w-full mt-1 bg-white border border-outline-variant rounded-lg shadow-md max-h-48 overflow-y-auto">
                                    <template x-for="p in bundleableProducts.filter(item => !sItem.search || item.name.toLowerCase().includes(sItem.search.toLowerCase()))" :key="p.id">
                                        <div @click="
                                                sItem.product_id = p.id; 
                                                sItem.variant_id = (p.variants && p.variants[0]) ? p.variants[0].id : ''; 
                                                sItem.search = p.name; 
                                                open = false; 
                                                onSuggestDiscountChange(sItem); 
                                                updateAutoName()
                                             " 
                                             class="px-3 py-2 text-xs hover:bg-surface-gray cursor-pointer flex justify-between items-center border-b border-outline-variant/10">
                                            <span x-text="p.name" class="font-medium text-on-surface"></span>
                                            <span class="text-primary font-bold ml-2 shrink-0" x-text="p.price_range_text || ('Rp ' + Number(p.single_price || p.min_price || 0).toLocaleString('id-ID'))"></span>
                                        </div>
                                    </template>
                                </div>
                            </div>

                            <!-- Harga Normal -->
                            <div>
                                <label class="block text-xs font-medium text-on-surface-variant mb-1">Harga Asli (1 Harga)</label>
                                <div class="px-3 py-2 bg-white rounded-lg border border-gray-200 text-xs text-gray-500 font-semibold line-through" x-text="'Rp ' + getItemPrice(sItem.product_id, sItem.variant_id).toLocaleString('id-ID')"></div>
                            </div>

                            <!-- Harga Bundling -->
                            <div>
                                <label class="block text-xs font-semibold text-amber-900 mb-1">Harga Bundling Turun Menjadi (Rp) <span class="text-danger">*</span></label>
                                <input type="number" step="100" min="0" :name="`suggest_items[${sIdx}][bundle_price]`" x-model.number="sItem.bundle_price" @input="onSuggestPriceChange(sItem)" placeholder="Misal: 150000" class="w-full px-3 py-2 text-xs font-bold text-amber-900 border border-amber-300 rounded-lg focus:outline-none focus:border-amber-500 bg-white" required>
                            </div>
                        </div>

                        <!-- Info Diskon -->
                        <div x-show="sItem.product_id && sItem.bundle_price > 0 && getItemPrice(sItem.product_id, sItem.variant_id) > sItem.bundle_price" class="text-xs text-emerald-700 font-medium">
                            ✓ Hemat Rp <span class="font-bold" x-text="(getItemPrice(sItem.product_id, sItem.variant_id) - sItem.bundle_price).toLocaleString('id-ID')"></span> (<span x-text="sItem.discount_percent"></span>%) saat dibeli bersama produk utama.
                        </div>
                    </div>
                </template>
            </div>
        </div>

        <!-- 3. Ringkasan Promo -->
        <div class="bg-white rounded-xl shadow-xs border border-outline-variant/40 p-5 space-y-4">
            <div class="border-b border-outline-variant/30 pb-3">
                <h2 class="text-sm font-bold text-on-surface">3. Nama Promo & Ringkasan</h2>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-on-surface-variant mb-1">Nama Promo Bundling <span class="text-danger">*</span></label>
                    <input type="text" name="name" x-model="bundleName" class="w-full px-3 py-2 text-xs border border-outline-variant rounded-lg focus:outline-none focus:border-primary font-semibold" placeholder="Contoh: Bundling Kasur Matras + Sprei" required>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-on-surface-variant mb-1">Slug URL (Otomatis)</label>
                    <input type="text" :value="slug" disabled class="w-full px-3 py-2 text-xs border border-outline-variant rounded-lg bg-surface-gray/80 text-gray-500 font-mono cursor-not-allowed" placeholder="otomatis-berdasarkan-nama">
                    <input type="hidden" name="slug" :value="slug">
                </div>
            </div>

            <!-- Ringkasan Total Bersahabat -->
            <div class="p-4 rounded-lg bg-surface-gray/50 border border-outline-variant/30 space-y-1.5 text-xs">
                <div class="flex justify-between text-on-surface-variant">
                    <span>Produk Utama:</span>
                    <span class="font-bold text-on-surface" x-text="mainProductPriceDisplay"></span>
                </div>
                <div class="flex justify-between text-on-surface-variant">
                    <span>Produk Tambahan (Harga Bundling):</span>
                    <span class="font-bold text-amber-800" x-text="'Rp ' + suggestTotalBundle.toLocaleString('id-ID')"></span>
                </div>
                <div class="pt-2 border-t border-outline-variant/30 flex justify-between font-bold text-sm text-primary">
                    <span>Total Paket Bundling:</span>
                    <span x-text="totalComboPriceDisplay"></span>
                </div>
                <div x-show="totalSavings > 0" class="text-right text-xs text-emerald-600 font-semibold">
                    (Hemat Rp <span x-text="totalSavings.toLocaleString('id-ID')"></span> pada produk pelengkap)
                </div>
                <input type="hidden" name="price" :value="totalComboPrice">
            </div>
        </div>

        <div class="flex items-center justify-end gap-3 pt-2">
            <a href="{{ route('bundlings.index') }}" class="px-5 py-2 border border-outline-variant text-on-surface-variant rounded-lg text-xs font-semibold hover:bg-surface-container">Batal</a>
            <button type="submit" class="btn-save px-5 py-2 bg-primary text-white rounded-lg text-xs font-bold hover:opacity-90">Perbarui Bundling</button>
        </div>
    </form>
@endsection
