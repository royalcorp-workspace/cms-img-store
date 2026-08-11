@extends('layouts.app')

@section('title', 'Create Product Bundle')

@section('content')
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="font-headline-lg text-headline-lg text-on-surface">Create Product Bundle</h1>
            <nav class="flex items-center gap-2 text-label-sm text-on-surface-variant mt-1 font-medium">
                <a href="{{ route('dashboard') }}" class="flex items-center gap-2 px-5 py-2 bg-primary text-white font-label-md hover:opacity-90 transition-all">eCommerce</a>
                <span class="material-symbols-outlined text-[14px]">chevron_right</span>
                <a href="{{ route('bundlings.index') }}" class="hover:text-primary transition-colors">Bundling</a>
                <span class="material-symbols-outlined text-[14px]">chevron_right</span>
                <span class="text-on-surface">Create</span>
            </nav>
        </div>
    </div>

    @include('layouts.partials.promotions-submenu')

    <div class="p-6 max-w-3xl" 
         x-data="{ 
             products: @js($products),
             items: [{ product_id: '', variant_id: '', quantity: 1 }],
             bundlePrice: {{ old('price', 0) }},
             addItem() {
                 this.items.push({ product_id: '', variant_id: '', quantity: 1 });
             },
             get itemSum() {
                 return this.items.reduce((sum, item) => {
                     const p = this.products.find(prod => String(prod.id) === String(item.product_id));
                     if (!p) return sum;
                     let itemPrice = Number(p.base_price) || 0;
                     if (item.variant_id) {
                         const v = p.variants.find(v => String(v.id) === String(item.variant_id));
                         if (v) itemPrice = Number(v.price) || 0;
                     }
                     return sum + (itemPrice * Number(item.quantity || 1));
                 }, 0);
             },
             validateSubmit(e) {
                 if (this.bundlePrice > this.itemSum) {
                     e.preventDefault();
                     alert('Harga bundling (Rp' + this.bundlePrice.toLocaleString('id-ID') + ') tidak boleh lebih mahal dari total harga item (Rp' + this.itemSum.toLocaleString('id-ID') + ')');
                 }
             }
         }">
        <form action="{{ route('bundlings.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6" @submit="validateSubmit">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="space-y-1.5">
                    <label class="block text-label-sm font-medium text-on-surface-variant">Bundle Name <span class="text-danger">*</span></label>
                    <input type="text" name="name" value="{{ old('name') }}" class="w-full px-3 py-2 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary/20 focus:outline-none" placeholder="e.g. Paket Kamar Tidur Utama" required>
                    @error('name') <span class="text-danger text-xs font-semibold">{{ $message }}</span> @enderror
                </div>

                <div class="space-y-1.5">
                    <label class="block text-label-sm font-medium text-on-surface-variant">Slug (URL Name)</label>
                    <input type="text" name="slug" value="{{ old('slug') }}" class="w-full px-3 py-2 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary/20 focus:outline-none" placeholder="e.g. paket-kamar-tidur-utama">
                    @error('slug') <span class="text-danger text-xs font-semibold">{{ $message }}</span> @enderror
                </div>
            </div>

            <div class="space-y-1.5">
                <label class="block text-label-sm font-medium text-on-surface-variant">Description</label>
                <textarea name="description" rows="3" class="w-full px-3 py-2 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary/20 focus:outline-none" placeholder="Describe the bundle package...">{{ old('description') }}</textarea>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 pt-4 border-t border-outline-variant/30">
                <div class="space-y-4">
                    <div class="space-y-1.5">
                        <label class="block text-label-sm font-medium text-on-surface-variant">Bundle Main Image (Square)</label>
                        <input type="file" name="image" accept="image/*" class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-primary file:text-white hover:file:opacity-90">
                    </div>
                    <div class="space-y-1.5">
                        <label class="block text-label-sm font-medium text-on-surface-variant">Bundle Banner Image (Landscape)</label>
                        <input type="file" name="banner_image" accept="image/*" class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-primary file:text-white hover:file:opacity-90">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div class="space-y-1.5">
                        <label class="block text-label-sm font-medium text-on-surface-variant">Bundle Price (Rp) <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" name="price" x-model.number="bundlePrice" class="w-full px-3 py-2 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary/20 focus:outline-none" required>
                        <div class="text-xs text-on-surface-variant mt-1">
                            Total Harga Item: <span class="font-bold" x-text="'Rp ' + itemSum.toLocaleString('id-ID')"></span>
                            <div x-show="bundlePrice > itemSum" class="text-danger font-bold mt-0.5"><span class="material-symbols-outlined text-[12px] align-middle">error</span> Harga melebihi total item!</div>
                        </div>
                        @error('price') <span class="text-danger text-xs font-semibold">{{ $message }}</span> @enderror
                    </div>

                    <div class="flex items-center pt-6">
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="is_active" value="1" checked class="sr-only peer">
                            <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-primary/20 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-success"></div>
                            <span class="ml-3 text-label-md font-medium text-on-surface-variant">Active Bundle</span>
                        </label>
                    </div>
                </div>
            </div>

            <!-- Items Section -->
            <div class="space-y-4 pt-6 border-t border-outline-variant/30">
                <div class="flex justify-between items-center">
                    <h3 class="font-headline-md text-headline-md text-on-surface">Bundle Items <span class="text-danger">*</span></h3>
                    <button type="button" @click="addItem()" class="px-3 py-1.5 bg-primary/10 text-primary hover:bg-primary/20 rounded-lg text-label-md font-bold transition-all flex items-center gap-1">
                        <span class="material-symbols-outlined text-[18px]">add</span> Add Product
                    </button>
                </div>

                <div class="space-y-3">
                    <template x-for="(item, index) in items" :key="index">
                        <div class="flex flex-col md:flex-row items-start md:items-center gap-3 bg-surface-container/30 p-3 rounded-lg border border-outline-variant/20">
                            <!-- Product Select -->
                            <div class="flex-1">
                                <label class="block text-label-sm font-medium text-on-surface-variant mb-1">Product</label>
                                <select :name="`items[${index}][product_id]`" x-model="item.product_id" @change="item.variant_id = ''" required class="w-full border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary/20">
                                    <option value="">-- Choose Product --</option>
                                    <template x-for="prod in products" :key="prod.id">
                                        <option :value="prod.id" x-text="`${prod.name} (Base Price: Rp${Number(prod.base_price || 0).toLocaleString('id-ID')})`"></option>
                                    </template>
                                </select>
                            </div>
                            <div class="w-48">
                                <label class="block text-label-sm font-medium text-on-surface-variant mb-1">Variant (Optional)</label>
                                <select :name="`items[${index}][variant_id]`" x-model="item.variant_id" class="w-full border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary/20">
                                    <option value="">-- Bebas Pilih di Web --</option>
                                    <template x-if="item.product_id">
                                        <template x-for="variant in (products.find(p => p.id == item.product_id)?.variants || [])" :key="variant.id">
                                            <option :value="variant.id" x-text="`${variant.variant_name} (Rp${Number(variant.price || 0).toLocaleString('id-ID')})`"></option>
                                        </template>
                                    </template>
                                </select>
                            </div>

                            <!-- Quantity -->
                            <div class="w-24 w-full md:w-24">
                                <label class="block text-[11px] font-bold text-on-surface-variant mb-1 uppercase">Qty</label>
                                <input type="number" :name="`items[${index}][quantity]`" x-model="item.quantity" min="1" placeholder="Qty" required class="w-full px-3 py-2 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary/20">
                            </div>

                            <!-- Delete button -->
                            <div class="pt-6">
                                <button type="button" @click="if (items.length > 1) items.splice(index, 1)" class="w-9 h-9 rounded-md bg-danger/10 text-danger hover:bg-danger/20 transition-all flex items-center justify-center">
                                    <span class="material-symbols-outlined text-[18px]">delete</span>
                                </button>
                            </div>
                        </div>
                    </template>
                </div>
                @error('items') <span class="text-danger text-xs font-semibold">{{ $message }}</span> @enderror
            </div>

            <div class="flex items-center gap-3 pt-6 border-t border-outline-variant/30">
                <button type="submit" class="px-6 py-2.5 bg-primary text-white font-label-md hover:opacity-90 transition-all">Save Bundle</button>
                <a href="{{ route('bundlings.index') }}" class="px-6 py-2.5 border border-outline-variant text-on-surface rounded-lg font-label-md hover:bg-surface-container transition-colors">Cancel</a>
            </div>
        </form>
    </div>
@endsection
