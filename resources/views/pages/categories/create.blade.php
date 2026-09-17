@extends('layouts.app')

@section('title', 'Create Category')

@section('content')
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="font-headline-lg text-headline-lg text-on-surface">Create Category</h1>
            <nav class="flex items-center gap-2 text-label-sm text-on-surface-variant mt-1 font-medium">
                <a href="{{ route('dashboard') }}" class="hover:text-primary transition-colors">eCommerce</a>
                <span class="material-symbols-outlined text-[14px]">chevron_right</span>
                <a href="{{ route('categories.index') }}" class="hover:text-primary transition-colors">Categories</a>
                <span class="material-symbols-outlined text-[14px]">chevron_right</span>
                <span class="text-on-surface">Create</span>
            </nav>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('categories.index') }}" class="inline-flex items-center gap-1.5 px-4 py-2 border border-outline-variant text-on-surface-variant hover:bg-surface-container rounded-xl text-xs font-semibold transition-colors">
                <span class="material-symbols-outlined text-[16px]">arrow_back</span>
                <span>Batal</span>
            </a>
            <button type="submit" form="categoryForm" class="btn-save inline-flex items-center gap-2 px-5 py-2 bg-primary text-white hover:opacity-90 rounded-xl text-xs font-bold transition-all shadow-sm active:scale-95">
                <span class="material-symbols-outlined text-[18px]">save</span>
                <span>Simpan Kategori</span>
            </button>
        </div>
    </div>

    @include('layouts.partials.product-submenu')

    <form id="categoryForm" action="{{ route('categories.store') }}" method="POST" enctype="multipart/form-data" class="w-full space-y-6" data-folder="categories">
        @csrf

        {{-- Card 1: Informasi Utama Kategori --}}
        <div class="w-full bg-white rounded-2xl shadow-sm border border-outline-variant/30 p-6 space-y-6">
            <div class="border-b border-outline-variant/20 pb-3">
                <h2 class="text-base font-bold text-on-surface flex items-center gap-2">
                    <span class="material-symbols-outlined text-primary text-[20px]">category</span>
                    Informasi Kategori
                </h2>
                <p class="text-xs text-on-surface-variant mt-0.5">Nama kategori, slug URL, tagline, deskripsi, dan kategori induk.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="space-y-1.5">
                    <label class="block text-label-sm font-medium text-on-surface-variant">Nama Kategori <span class="text-danger">*</span></label>
                    <input type="text" id="categoryName" name="name" value="{{ old('name') }}" class="w-full px-3 py-2 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary/20 focus:outline-none" placeholder="e.g. Kasur Busa" required>
                    @error('name') <span class="text-danger text-xs font-semibold">{{ $message }}</span> @enderror
                </div>

                <div class="space-y-1.5">
                    <label class="block text-label-sm font-medium text-on-surface-variant">Slug (URL)</label>
                    <input type="text" id="categorySlug" name="slug" value="{{ old('slug') }}" class="w-full px-3 py-2 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary/20 focus:outline-none" placeholder="e.g. kasur-busa (otomatis jika kosong)">
                    @error('slug') <span class="text-danger text-xs font-semibold">{{ $message }}</span> @enderror
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="space-y-1.5">
                    <label class="block text-label-sm font-medium text-on-surface-variant">Tagline Kategori</label>
                    <input type="text" name="tagline" value="{{ old('tagline') }}" class="w-full px-3 py-2 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary/20 focus:outline-none" placeholder="e.g. Solusi tidur berkualitas dan nyaman">
                    @error('tagline') <span class="text-danger text-xs font-semibold">{{ $message }}</span> @enderror
                </div>

                <div class="space-y-1.5">
                    <label class="block text-label-sm font-medium text-on-surface-variant">Parent Category (Kategori Induk)</label>
                    <select name="parent_id" class="w-full px-3 py-2 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary/20 focus:outline-none bg-white select2-enable">
                        <option value="">None (Top Level / Kategori Utama)</option>
                        @foreach($allCategories as $cat)
                            <option value="{{ $cat->id }}" {{ old('parent_id') == $cat->id ? 'selected' : '' }}>
                                {{ $cat->parent ? $cat->parent->name . ' → ' : '' }}{{ $cat->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('parent_id') <span class="text-danger text-xs font-semibold">{{ $message }}</span> @enderror
                </div>
            </div>

            <div class="space-y-1.5">
                <label class="block text-label-sm font-medium text-on-surface-variant">Deskripsi Kategori</label>
                <textarea name="description" rows="3" class="w-full px-3 py-2 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary/20 focus:outline-none" placeholder="Tuliskan deskripsi lengkap tentang kategori ini...">{{ old('description') }}</textarea>
                @error('description') <span class="text-danger text-xs font-semibold">{{ $message }}</span> @enderror
            </div>
        </div>

        {{-- Card 2: Pengaturan Kurir & Ongkir --}}
        <div class="w-full bg-white rounded-2xl shadow-sm border border-outline-variant/30 p-6 space-y-6">
            <div class="border-b border-outline-variant/20 pb-3">
                <h2 class="text-base font-bold text-on-surface flex items-center gap-2">
                    <span class="material-symbols-outlined text-primary text-[20px]">local_shipping</span>
                    Pengaturan Kurir & Ongkos Kirim Kategori
                </h2>
                <p class="text-xs text-on-surface-variant mt-0.5">Tentukan bagaimana metode pengiriman dan ongkos kirim dihitung untuk produk-produk di dalam kategori ini.</p>
            </div>

            <div class="space-y-4">
                <div class="space-y-1.5">
                    <label class="block text-label-sm font-medium text-on-surface-variant">Tipe Pengaturan Kurir</label>
                    <select id="courier_setting_type" name="courier_setting_type" onchange="toggleCourierOptions()" class="w-full md:w-1/2 px-3 py-2 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary/20 focus:outline-none bg-white text-sm">
                        <option value="detail" {{ old('courier_setting_type', 'detail') === 'detail' ? 'selected' : '' }}>Detail / Kondisional (Kurir ditentukan per masing-masing produk)</option>
                        <option value="global" {{ old('courier_setting_type') === 'global' ? 'selected' : '' }}>Global (Semua produk dalam kategori ini mengikuti aturan seragam di bawah)</option>
                    </select>
                    <p class="text-[11px] text-on-surface-variant">Pilih "Global" jika semua produk di kategori ini memiliki kebijakan pengiriman yang sama (misal seluruh matras besar hanya kurir toko).</p>
                </div>

                <div id="globalCourierContainer" class="p-5 bg-surface-container-lowest rounded-xl border border-outline-variant/40 space-y-5 transition-all">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-1.5">
                            <label class="block text-label-sm font-medium text-on-surface-variant">Pilihan Tipe Kurir</label>
                            <select id="courier_type" name="courier_type" class="w-full px-3 py-2 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary/20 focus:outline-none bg-white text-sm">
                                <option value="keduanya" {{ old('courier_type', 'keduanya') === 'keduanya' ? 'selected' : '' }}>Keduanya (Kurir Toko & Kurir Ekspedisi)</option>
                                <option value="toko" {{ old('courier_type') === 'toko' ? 'selected' : '' }}>Hanya Pengiriman Kurir Toko</option>
                                <option value="expedisi" {{ old('courier_type') === 'expedisi' ? 'selected' : '' }}>Hanya Pengiriman Ekspedisi (Biteship)</option>
                            </select>
                        </div>

                        <div class="space-y-1.5">
                            <label class="block text-label-sm font-medium text-on-surface-variant">Skema Perhitungan Ongkir</label>
                            <select id="shipping_scheme" name="shipping_scheme" onchange="toggleShippingCostField()" class="w-full px-3 py-2 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary/20 focus:outline-none bg-white text-sm">
                                <option value="dimension" {{ old('shipping_scheme', 'dimension') === 'dimension' ? 'selected' : '' }}>Hitung dari Dimensi & Berat (Standar Ekspedisi / Kurir Toko)</option>
                                <option value="fixed" {{ old('shipping_scheme') === 'fixed' ? 'selected' : '' }}>Ongkos Kirim Tetap (Fixed Flat Rate)</option>
                            </select>
                        </div>
                    </div>

                    <div id="fixedCostContainer" class="space-y-1.5 md:w-1/2">
                        <label class="block text-label-sm font-medium text-on-surface-variant">Nominal Ongkir Tetap (Rp)</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-on-surface-variant text-sm font-semibold">Rp</span>
                            <input type="number" step="1000" min="0" id="shipping_cost" name="shipping_cost" value="{{ old('shipping_cost', 0) }}" class="w-full pl-10 pr-3 py-2 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary/20 focus:outline-none" placeholder="0">
                        </div>
                        <p class="text-[11px] text-on-surface-variant">Nominal tarif ongkir tetap yang dibebankan per pesanan jika skema dipilih 'Fixed'.</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Card 3: Banner Web & Mobile --}}
        <div class="w-full bg-white rounded-2xl shadow-sm border border-outline-variant/30 p-6 space-y-6">
            <div class="border-b border-outline-variant/20 pb-3">
                <h2 class="text-base font-bold text-on-surface flex items-center gap-2">
                    <span class="material-symbols-outlined text-primary text-[20px]">panorama</span>
                    Banner Kategori
                </h2>
                <p class="text-xs text-on-surface-variant mt-0.5">Upload banner kategori untuk tampilan desktop (web) dan aplikasi mobile.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="space-y-3 p-4 border border-outline-variant/30 rounded-xl bg-surface-container/20">
                    <label class="block text-label-sm font-medium text-on-surface-variant">Banner Desktop (Web)</label>
                    <div id="previewBannerWeb" class="hidden mb-2">
                        <img src="" alt="Banner Web Preview" class="h-28 w-full object-cover rounded-lg border border-outline-variant shadow-sm">
                    </div>
                    <input type="file" id="categoryBannerWeb" name="banner_web" accept="image/*" onchange="previewImage(this, 'previewBannerWeb')" class="w-full text-xs text-gray-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-primary file:text-white hover:file:opacity-90">
                    <p class="text-[11px] text-on-surface-variant">Rekomendasi rasio: 16:9 atau lebar minimal 1200px. Maks: 5MB.</p>
                    @error('banner_web') <span class="text-danger text-xs font-semibold">{{ $message }}</span> @enderror
                </div>

                <div class="space-y-3 p-4 border border-outline-variant/30 rounded-xl bg-surface-container/20">
                    <label class="block text-label-sm font-medium text-on-surface-variant">Banner Mobile (App / Mobile Web)</label>
                    <div id="previewBannerMobile" class="hidden mb-2">
                        <img src="" alt="Banner Mobile Preview" class="h-28 w-full object-cover rounded-lg border border-outline-variant shadow-sm">
                    </div>
                    <input type="file" id="categoryBannerMobile" name="banner_mobile" accept="image/*" onchange="previewImage(this, 'previewBannerMobile')" class="w-full text-xs text-gray-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-primary file:text-white hover:file:opacity-90">
                    <p class="text-[11px] text-on-surface-variant">Rekomendasi rasio: 4:3 atau kotak. Maks: 5MB.</p>
                    @error('banner_mobile') <span class="text-danger text-xs font-semibold">{{ $message }}</span> @enderror
                </div>
            </div>
        </div>

        {{-- Card 4: Pengaturan Tambahan (Sort, Status, Garansi) --}}
        <div class="w-full bg-white rounded-2xl shadow-sm border border-outline-variant/30 p-6 space-y-6">
            <div class="border-b border-outline-variant/20 pb-3">
                <h2 class="text-base font-bold text-on-surface flex items-center gap-2">
                    <span class="material-symbols-outlined text-primary text-[20px]">tune</span>
                    Status & Visibilitas
                </h2>
                <p class="text-xs text-on-surface-variant mt-0.5">Urutan tampilan dan status aktif kategori.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="space-y-1.5">
                    <label class="block text-label-sm font-medium text-on-surface-variant">Sort Order (Urutan)</label>
                    <input type="number" name="sort_order" value="{{ old('sort_order', 0) }}" min="0" class="w-full px-3 py-2 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary/20 focus:outline-none">
                    @error('sort_order') <span class="text-danger text-xs font-semibold">{{ $message }}</span> @enderror
                </div>

                <div class="flex items-center pt-6">
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" name="status" value="1" {{ old('status', '1') ? 'checked' : '' }} class="sr-only peer">
                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-primary/20 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-success"></div>
                        <span class="ml-3 text-label-md font-medium text-on-surface-variant">Kategori Aktif</span>
                    </label>
                </div>

                <div class="flex items-center pt-6">
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" name="has_warranty" value="1" {{ old('has_warranty', '1') ? 'checked' : '' }} class="sr-only peer">
                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-primary/20 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary"></div>
                        <span class="ml-3 text-label-md font-medium text-on-surface-variant">Mendukung Garansi</span>
                    </label>
                </div>
            </div>

            <div class="flex items-center gap-3 pt-6 border-t border-outline-variant/20">
                <button type="submit" class="px-6 py-2.5 bg-primary text-white font-label-md hover:opacity-90 transition-all rounded-lg text-sm font-bold shadow-sm active:scale-95">Simpan Kategori</button>
                <a href="{{ route('categories.index') }}" class="px-6 py-2.5 border border-outline-variant text-on-surface rounded-lg font-label-md hover:bg-surface-container transition-colors text-sm font-semibold">Batal</a>
            </div>
        </div>
    </form>
@endsection

@push('scripts')
<script>
    function previewImage(input, previewContainerId) {
        const container = document.getElementById(previewContainerId);
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                container.classList.remove('hidden');
                container.querySelector('img').src = e.target.result;
            }
            reader.readAsDataURL(input.files[0]);
        }
    }

    function toggleCourierOptions() {
        const type = document.getElementById('courier_setting_type').value;
        const container = document.getElementById('globalCourierContainer');
        if (type === 'global') {
            container.style.display = 'block';
        } else {
            container.style.display = 'none';
        }
    }

    function toggleShippingCostField() {
        const scheme = document.getElementById('shipping_scheme').value;
        const costContainer = document.getElementById('fixedCostContainer');
        if (scheme === 'fixed') {
            costContainer.style.display = 'block';
        } else {
            costContainer.style.display = 'none';
        }
    }

    document.getElementById('categoryName').addEventListener('input', function() {
        const slugInput = document.getElementById('categorySlug');
        if (!slugInput.dataset.manualEdited) {
            slugInput.value = this.value
                .toLowerCase()
                .replace(/[^a-z0-9\s-]/g, '')
                .trim()
                .replace(/[\s-]+/g, '-');
        }
    });

    document.getElementById('categorySlug').addEventListener('input', function() {
        this.dataset.manualEdited = 'true';
    });

    document.addEventListener('DOMContentLoaded', function() {
        toggleCourierOptions();
        toggleShippingCostField();
    });
</script>
@endpush
