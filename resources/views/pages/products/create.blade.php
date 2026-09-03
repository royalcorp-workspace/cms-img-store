@extends('layouts.app')

@section('title', isset($product) ? 'Edit Product' : 'Create Product')

@push('styles')
<link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
<style>
    .ql-editor {
        min-height: 250px;
        font-family: inherit;
        font-size: 1rem;
    }
    .ql-toolbar.ql-snow {
        border-color: var(--color-outline-variant, #cbd5e1);
        border-top-left-radius: 0.5rem;
        border-top-right-radius: 0.5rem;
        background-color: #f8fafc;
    }
    .ql-container.ql-snow {
        border-color: var(--color-outline-variant, #cbd5e1);
        border-bottom-left-radius: 0.5rem;
        border-bottom-right-radius: 0.5rem;
    }
</style>
@endpush

@section('content')
@php
    $productId = $product->id ?? null;
    $images = $product->images ?? collect([]);
    $variants = $product->variants ?? collect([]);
    $colors = $product->colors ?? collect([]);

    function buildCategoryOptions($categories, $parentId = null, $prefix = '', $selectedId = null)
    {
        $html = '';
        foreach ($categories->where('parent_id', $parentId) as $cat) {
            $selected = ($selectedId == $cat->id) ? 'selected' : '';
            $html .= '<option value="' . $cat->id . '" ' . $selected . '>' . $prefix . e($cat->name) . '</option>';
            $html .= buildCategoryOptions($categories, $cat->id, $prefix . '&nbsp;&nbsp;&nbsp;&nbsp;', $selectedId);
        }
        return $html;
    }
@endphp
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="font-headline-lg text-headline-lg text-on-surface">{{ $product ? 'Edit Product' : 'Create Product' }}</h1>
            <nav class="flex items-center gap-2 text-body-md text-on-surface-variant mt-1">
                <a href="{{ route('dashboard') }}" class="text-primary hover:underline">Dashboard</a>
                <span class="material-symbols-outlined text-[16px]">chevron_right</span>
                <a href="{{ route('products.index') }}" class="text-primary hover:underline">Products</a>
                <span class="material-symbols-outlined text-[16px]">chevron_right</span>
                <span>{{ $product ? 'Edit' : 'Create' }}</span>
            </nav>
        </div>
        <a href="{{ route('products.index') }}" class="flex items-center gap-2 px-4 py-2 bg-surface-container-lowest border border-outline-variant text-secondary rounded-lg font-label-md text-label-md hover:bg-surface-container transition-all">
            <span class="material-symbols-outlined text-[18px]">arrow_back</span> Back
        </a>
    </div>

    @include('layouts.partials.product-submenu')

    <div class="bg-white rounded-xl shadow-sm border border-outline-variant/30">
        <div class="p-6">
            <div class="flex border-b border-outline-variant mb-6">
                <button onclick="switchTab('details')" id="tab-details" class="tab-btn px-4 py-2 text-on-surface border-b-2 border-primary text-label-md font-label-md font-medium">Details</button>
                <button onclick="switchTab('media')" id="tab-media" class="tab-btn px-4 py-2 text-on-surface-variant hover:text-on-surface text-label-md font-label-md">Media</button>
                <button onclick="switchTab('variations')" id="tab-variations" class="tab-btn px-4 py-2 text-on-surface-variant hover:text-on-surface text-label-md font-label-md">Variations</button>
                <button onclick="switchTab('colors')" id="tab-colors" class="tab-btn px-4 py-2 text-on-surface-variant hover:text-on-surface text-label-md font-label-md hidden">Colors</button>
            </div>

            <form id="productForm" method="POST" action="{{ $product->id ? route('products.update', $product->id) : route('products.store') }}">
                @csrf
                @if($product) @method('PUT') @endif
                <input type="hidden" name="variants" id="variantsInput" value="">
                <input type="hidden" name="colors" id="colorsInput" value="">

                <div id="panel-details" class="tab-panel">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="space-y-1.5">
                            <label class="block text-label-sm font-medium text-on-surface-variant">Kode Produk <span class="inline-flex items-center cursor-help text-on-surface-variant relative group"><span class="material-symbols-outlined text-[18px]">info</span><span class="absolute right-0 top-full mt-2 w-80 bg-surface-container-highest rounded-lg shadow-lg border border-outline-variant p-4 text-body-xs text-on-surface-variant hidden group-hover:block z-50">Kode identitas unik produk (Otomatis dibuat oleh sistem)</span></span></label>
                            <input type="text" value="{{ $product->code ?? 'Auto Generated' }}" class="w-full px-3 py-2 border border-outline-variant rounded-lg bg-surface-container-low text-on-surface-variant cursor-not-allowed font-mono text-sm" disabled>
                        </div>
                        <div class="space-y-1.5">
                            <label class="block text-label-sm font-medium text-on-surface-variant">Product Name <span class="inline-flex items-center cursor-help text-on-surface-variant relative group"><span class="material-symbols-outlined text-[18px]">info</span><span class="absolute right-0 top-full mt-2 w-80 bg-surface-container-highest rounded-lg shadow-lg border border-outline-variant p-4 text-body-xs text-on-surface-variant hidden group-hover:block z-50">Nama produk yang akan ditampilkan di katalog</span></span></label>
                            <input type="text" name="name" value="{{ $product->name ?? '' }}" class="w-full px-3 py-2 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary/20 focus:outline-none" placeholder="Enter product name" required>
                        </div>
                        <div class="space-y-1.5">
                            <label class="block text-label-sm font-medium text-on-surface-variant">Product Slug <span class="inline-flex items-center cursor-help text-on-surface-variant relative group"><span class="material-symbols-outlined text-[18px]">info</span><span class="absolute right-0 top-full mt-2 w-80 bg-surface-container-highest rounded-lg shadow-lg border border-outline-variant p-4 text-body-xs text-on-surface-variant hidden group-hover:block z-50">URL ramah mesin pencari (otomatis dari nama produk)</span></span></label>
                            <input type="text" name="slug" id="productSlug" value="{{ $product->slug ?? '' }}" class="w-full px-3 py-2 border border-outline-variant rounded-lg bg-surface-container-low text-on-surface-variant cursor-not-allowed focus:outline-none" placeholder="auto-generated-slug" readonly>
                        </div>
                        <div class="space-y-1.5">
                            <label class="block text-label-sm font-medium text-on-surface-variant">Category <span class="inline-flex items-center cursor-help text-on-surface-variant relative group"><span class="material-symbols-outlined text-[18px]">info</span><span class="absolute right-0 top-full mt-2 w-80 bg-surface-container-highest rounded-lg shadow-lg border border-outline-variant p-4 text-body-xs text-on-surface-variant hidden group-hover:block z-50">Kategori untuk mengelompokkan produk</span></span></label>
                            <select name="category_id" id="categorySelect" class="w-full px-3 py-2 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary/20 focus:outline-none bg-white select2-enable">
                                <option value="">Select Category</option>
                                {!! buildCategoryOptions(\App\Models\Product\Category::all(), null, '', old('category_id', $product->category_id ?? '')) !!}
                            </select>
                        </div>
                        <div class="space-y-1.5">
                            <label class="block text-label-sm font-medium text-on-surface-variant">Brand <span class="inline-flex items-center cursor-help text-on-surface-variant relative group"><span class="material-symbols-outlined text-[18px]">info</span><span class="absolute right-0 top-full mt-2 w-80 bg-surface-container-highest rounded-lg shadow-lg border border-outline-variant p-4 text-body-xs text-on-surface-variant hidden group-hover:block z-50">Merek produk</span></span></label>
                            <select name="brand_id" id="brandSelect" class="w-full px-3 py-2 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary/20 focus:outline-none bg-white select2-enable">
                                <option value="">Select Brand</option>
                                @foreach(\App\Models\Product\Brand::all() as $brand)
                                    <option value="{{ $brand->id }}" {{ old('brand_id', $product->brand_id ?? '') == $brand->id ? 'selected' : '' }}>
                                        {{ $brand->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="space-y-1.5 md:col-span-2">
                            <label class="block text-label-sm font-medium text-on-surface-variant">Thumbnail <span class="inline-flex items-center cursor-help text-on-surface-variant relative group"><span class="material-symbols-outlined text-[18px]">info</span><span class="absolute right-0 top-full mt-2 w-80 bg-surface-container-highest rounded-lg shadow-lg border border-outline-variant p-4 text-body-xs text-on-surface-variant hidden group-hover:block z-50">Gambar utama produk yang muncul di katalog depan</span></span></label>
                            <div id="thumbnailPreviewContainer" class="mb-2 {{ (isset($product) && $product->thumbnail) ? '' : 'hidden' }}">
                                <img id="thumbnailPreview" src="{{ (isset($product) && $product->thumbnail_url) ? $product->thumbnail_url : '' }}" alt="Thumbnail" class="h-32 rounded-lg border border-outline-variant object-cover">
                            </div>
                            <input type="file" name="thumbnail_file" id="thumbnailInput" accept="image/*" class="w-full px-3 py-2 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary/20 focus:outline-none" onchange="previewThumbnail(this)">
                        </div>
                        <div class="space-y-1.5 md:col-span-2">
                            <label class="block text-label-sm font-medium text-on-surface-variant">Price (Rp) <span class="inline-flex items-center cursor-help text-on-surface-variant relative group"><span class="material-symbols-outlined text-[18px]">info</span><span class="absolute right-0 top-full mt-2 w-80 bg-surface-container-highest rounded-lg shadow-lg border border-outline-variant p-4 text-body-xs text-on-surface-variant hidden group-hover:block z-50">Harga dasar produk dalam Rupiah</span></span></label>
                            <input type="number" name="base_price" step="0.01" value="{{ $product->base_price ?? '' }}" class="w-full px-3 py-2 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary/20 focus:outline-none" placeholder="Enter base price">
                        </div>
                        <div class="md:col-span-2 space-y-2 hidden">
                            <label class="block text-label-sm font-medium text-on-surface-variant">Segments</label>
                            <div class="grid grid-cols-2 md:grid-cols-5 gap-3">
                                @for($i = 1; $i <= 10; $i++)
                                <div class="space-y-1">
                                    <label class="block text-label-xs text-on-surface-variant">Segment {{ $i }}</label>
                                    <input type="text" name="segments[{{ $i }}]" value="{{ old('segments.' . $i, $product->segments[$i] ?? '') }}" class="w-full px-3 py-2 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary/20 focus:outline-none text-sm" placeholder="Segment {{ $i }}">
                                </div>
                                @endfor
                            </div>
                        </div>
                        <div class="md:col-span-2 grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div class="space-y-1.5">
                                <label class="block text-label-sm font-medium text-on-surface-variant">Status Aktif <span class="inline-flex items-center cursor-help text-on-surface-variant relative group"><span class="material-symbols-outlined text-[18px]">info</span><span class="absolute right-0 top-full mt-2 w-80 bg-surface-container-highest rounded-lg shadow-lg border border-outline-variant p-4 text-body-xs text-on-surface-variant hidden group-hover:block z-50">Atur apakah produk aktif atau tidak</span></span></label>
                                <div class="flex items-center gap-4 pt-2">
                                    <label class="flex items-center gap-2 cursor-pointer">
                                        <input type="radio" name="status" value="1" {{ old('status', $product->status ?? 1) == 1 ? 'checked' : '' }} class="w-4 h-4 text-primary focus:ring-primary/30 border-outline-variant">
                                        <span class="text-body-sm text-on-surface font-medium">Yes</span>
                                    </label>
                                    <label class="flex items-center gap-2 cursor-pointer">
                                        <input type="radio" name="status" value="0" {{ old('status', $product->status ?? 1) == 0 ? 'checked' : '' }} class="w-4 h-4 text-primary focus:ring-primary/30 border-outline-variant">
                                        <span class="text-body-sm text-on-surface font-medium">No</span>
                                    </label>
                                </div>
                            </div>
                            <div class="space-y-1.5">
                                <label class="block text-label-sm font-medium text-on-surface-variant">Produk Baru <span class="inline-flex items-center cursor-help text-on-surface-variant relative group"><span class="material-symbols-outlined text-[18px]">info</span><span class="absolute right-0 top-full mt-2 w-80 bg-surface-container-highest rounded-lg shadow-lg border border-outline-variant p-4 text-body-xs text-on-surface-variant hidden group-hover:block z-50">Tandai jika ini adalah produk keluaran terbaru</span></span></label>
                                <div class="flex items-center gap-4 pt-2">
                                    <label class="flex items-center gap-2 cursor-pointer">
                                        <input type="radio" name="is_new" value="1" {{ old('is_new', $product->is_new ?? 0) == 1 ? 'checked' : '' }} class="w-4 h-4 text-primary focus:ring-primary/30 border-outline-variant">
                                        <span class="text-body-sm text-on-surface font-medium">Yes</span>
                                    </label>
                                    <label class="flex items-center gap-2 cursor-pointer">
                                        <input type="radio" name="is_new" value="0" {{ old('is_new', $product->is_new ?? 0) == 0 ? 'checked' : '' }} class="w-4 h-4 text-primary focus:ring-primary/30 border-outline-variant">
                                        <span class="text-body-sm text-on-surface font-medium">No</span>
                                    </label>
                                </div>
                            </div>
                            <div class="space-y-1.5">
                                <label class="block text-label-sm font-medium text-on-surface-variant">Best Seller <span class="inline-flex items-center cursor-help text-on-surface-variant relative group"><span class="material-symbols-outlined text-[18px]">info</span><span class="absolute right-0 top-full mt-2 w-80 bg-surface-container-highest rounded-lg shadow-lg border border-outline-variant p-4 text-body-xs text-on-surface-variant hidden group-hover:block z-50">Tandai jika ini adalah produk terlaris</span></span></label>
                                <div class="flex items-center gap-4 pt-2">
                                    <label class="flex items-center gap-2 cursor-pointer">
                                        <input type="radio" name="best_seller" value="1" {{ old('best_seller', $product->best_seller ?? 0) == 1 ? 'checked' : '' }} class="w-4 h-4 text-primary focus:ring-primary/30 border-outline-variant">
                                        <span class="text-body-sm text-on-surface font-medium">Yes</span>
                                    </label>
                                    <label class="flex items-center gap-2 cursor-pointer">
                                        <input type="radio" name="best_seller" value="0" {{ old('best_seller', $product->best_seller ?? 0) == 0 ? 'checked' : '' }} class="w-4 h-4 text-primary focus:ring-primary/30 border-outline-variant">
                                        <span class="text-body-sm text-on-surface font-medium">No</span>
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="md:col-span-2 space-y-1.5">
                            <label class="block text-label-sm font-medium text-on-surface-variant">Short Description <span class="inline-flex items-center cursor-help text-on-surface-variant relative group"><span class="material-symbols-outlined text-[18px]">info</span><span class="absolute right-0 top-full mt-2 w-80 bg-surface-container-highest rounded-lg shadow-lg border border-outline-variant p-4 text-body-xs text-on-surface-variant hidden group-hover:block z-50">Ringkasan atau deskripsi singkat produk</span></span></label>
                            <textarea name="short_description" class="w-full px-3 py-2 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary/20 focus:outline-none" rows="2" placeholder="Enter short description">{{ $product->short_description ?? '' }}</textarea>
                        </div>
                        <div class="md:col-span-2 space-y-1.5">
                            <label class="block text-label-sm font-medium text-on-surface-variant">Description <span class="inline-flex items-center cursor-help text-on-surface-variant relative group"><span class="material-symbols-outlined text-[18px]">info</span><span class="absolute right-0 top-full mt-2 w-80 bg-surface-container-highest rounded-lg shadow-lg border border-outline-variant p-4 text-body-xs text-on-surface-variant hidden group-hover:block z-50">Deskripsi lengkap produk untuk customer</span></span></label>
                            <div class="border border-outline-variant rounded-lg overflow-hidden focus-within:ring-2 focus-within:ring-primary/20">
                                <div id="quill-editor" style="height: 300px;">{!! $product->description ?? '' !!}</div>
                            </div>
                            <input type="hidden" name="description" id="description-input" value="{{ $product->description ?? '' }}">
                        </div>
                        <div class="md:col-span-2 space-y-1.5">
                            <label class="block text-label-sm font-medium text-on-surface-variant">Durasi Garansi <span class="inline-flex items-center cursor-help text-on-surface-variant relative group"><span class="material-symbols-outlined text-[18px]">info</span><span class="absolute right-0 top-full mt-2 w-80 bg-surface-container-highest rounded-lg shadow-lg border border-outline-variant p-4 text-body-xs text-on-surface-variant hidden group-hover:block z-50">Tuliskan durasi garansi jika ada (contoh: 15 Tahun). Kosongkan jika tidak ada.</span></span></label>
                            <input type="text" name="warranty_duration" value="{{ $product->warranty_duration ?? '' }}" class="w-full px-3 py-2 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary/20 focus:outline-none" placeholder="Contoh: 15 Tahun">
                        </div>
                    </div>
                </div>

                <div id="panel-media" class="tab-panel hidden">
                    <div class="space-y-4">
                        <div class="border-2 border-dashed border-outline-variant rounded-lg p-6 text-center">
                            <input type="file" id="mediaInput" accept="image/*" multiple class="hidden" onchange="handleMediaUpload(this)">
                            <label for="mediaInput" class="cursor-pointer inline-flex items-center gap-2 px-4 py-2 bg-primary text-white rounded-lg font-label-md hover:opacity-90 transition-all shadow-sm">
                                <span class="material-symbols-outlined text-[18px]">upload</span> Upload Images
                            </label>
                            <p class="text-body-sm text-on-surface-variant mt-2">Select multiple images to upload (max 2MB each)</p>
                        </div>
                        <div id="localPreviewContainer" class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        </div>
                    </div>
                </div>

                <div id="panel-variations" class="tab-panel hidden">
                    <div class="space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="space-y-1.5">
                                <label class="block text-label-sm font-medium text-on-surface-variant">SKU</label>
                                <input type="text" id="vSku" class="w-full px-3 py-2 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary/20 focus:outline-none" placeholder="SKU">
                            </div>
                            <div class="space-y-1.5">
                                <label class="block text-label-sm font-medium text-on-surface-variant">Variant Name</label>
                                <input type="text" id="vName" class="w-full px-3 py-2 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary/20 focus:outline-none" placeholder="e.g. Red / Large">
                            </div>
                            <div class="col-span-1 md:col-span-2 space-y-1.5 bg-surface-container/30 p-3 rounded-lg border border-outline-variant/30">
                                <label class="block text-label-sm font-medium text-on-surface-variant flex justify-between items-center">
                                    <span>Attributes (e.g., Size: 180x200, Type: Fullset)</span>
                                    <button type="button" onclick="addVAttributeRow()" class="text-primary hover:underline font-normal">+ Add Attribute</button>
                                </label>
                                <div id="vAttributesContainer" class="space-y-2">
                                </div>
                            </div>
                            <div class="space-y-1.5">
                                <label class="block text-label-sm font-medium text-on-surface-variant">Price (Rp)</label>
                                <input type="number" step="0.01" id="vPrice" class="w-full px-3 py-2 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary/20 focus:outline-none" placeholder="0.00">
                            </div>
                            <div class="space-y-1.5">
                                <label class="block text-label-sm font-medium text-on-surface-variant">Stock Qty</label>
                                <input type="number" id="vStock" class="w-full px-3 py-2 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary/20 focus:outline-none" placeholder="0">
                            </div>
                            <div class="space-y-1.5 hidden">
                                <label class="block text-label-sm font-medium text-on-surface-variant">Min Order Qty</label>
                                <input type="number" id="vMinOrder" class="w-full px-3 py-2 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary/20 focus:outline-none" placeholder="1">
                            </div>
                            <div class="space-y-1.5">
                                <label class="block text-label-sm font-medium text-on-surface-variant">Status</label>
                                <select id="vStatus" class="w-full px-3 py-2 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary/20 focus:outline-none bg-white">
                                    <option value="1">Active</option>
                                    <option value="0">Inactive</option>
                                </select>
                            </div>
                        </div>
                        <div class="flex items-center gap-3">
                            <button type="button" onclick="addVariant()" id="addVariantBtn" class="px-4 py-2 bg-primary text-white rounded-lg font-label-md hover:opacity-90 transition-all shadow-sm">Add Variation</button>
                            <button type="button" onclick="updateLocalVariant()" id="updateVariantBtn" class="px-4 py-2 bg-secondary text-white rounded-lg font-label-md hover:opacity-90 transition-all shadow-sm hidden">Update Variation</button>
                            <button type="button" onclick="cancelEditVariant()" id="cancelVariantBtn" class="px-4 py-2 border border-outline-variant text-on-surface-variant rounded-lg font-label-md hover:bg-surface-container transition-colors hidden">Cancel</button>
                        </div>
                        <div id="variantsList" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            @foreach($variants as $v)
                                <div class="border border-outline-variant rounded-lg p-4">
                                    <div class="flex items-start justify-between mb-3">
                                        <div>
                                            <p class="font-body-md text-body-md text-on-surface font-semibold">{{ $v->variant_name ?? ($v->sku ?? 'Variant') }}</p>
                                            <p class="text-label-sm text-on-surface-variant">SKU: {{ $v->sku ?? '-' }}</p>
                                        </div>
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-label-sm font-label-sm {{ ($v->status ?? 1) ? 'bg-success/10 text-success' : 'bg-danger/10 text-danger' }}">
                                            <span class="w-1.5 h-1.5 rounded-full bg-current"></span> {{ ($v->status ?? 1) ? 'Active' : 'Inactive' }}
                                        </span>
                                    </div>
                                    <div class="grid grid-cols-2 gap-3 text-body-sm">
                                        <div>
                                            <p class="text-on-surface-variant">Price</p>
                                            <p class="font-medium text-on-surface">Rp{{ number_format($v->price ?? 0, 2, ',', '.') }}</p>
                                        </div>
                                        <div>
                                            <p class="text-on-surface-variant">Stock</p>
                                            <p class="font-medium text-on-surface">{{ $v->stock_qty ?? 0 }}</p>
                                        </div>
                                        <div>
                                            <p class="text-on-surface-variant">Height</p>
                                            <p class="font-medium text-on-surface">{{ $v->height ?? 0 }} cm</p>
                                        </div>
                                        <div>
                                            <p class="text-on-surface-variant">Weight</p>
                                            <p class="font-medium text-on-surface">{{ $v->weight ?? 0 }} kg</p>
                                        </div>
                                    </div>
                                    <div class="flex justify-end mt-3 pt-3 border-t border-outline-variant/20">
                                        <button type="button" onclick="editVariant('{{ $v->id }}', {{ json_encode(['sku' => $v->sku, 'variant_name' => $v->variant_name, 'price' => $v->price, 'stock_qty' => $v->stock_qty, 'min_order_qty' => $v->min_order_qty, 'status' => $v->status]) }})" class="text-primary hover:opacity-80 text-label-sm flex items-center gap-1 mr-3">
                                            <span class="material-symbols-outlined text-[16px]">edit</span> Edit
                                        </button>
                                        <button type="button" onclick="deleteVariant('{{ $v->id }}')" class="text-danger hover:opacity-80 text-label-sm flex items-center gap-1">
                                            <span class="material-symbols-outlined text-[16px]">delete</span> Delete
                                        </button>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div id="panel-colors" class="tab-panel hidden">
                    <div class="space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="space-y-1.5">
                                <label class="block text-label-sm font-medium text-on-surface-variant">Color Name</label>
                                <input type="text" id="cName" class="w-full px-3 py-2 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary/20 focus:outline-none" placeholder="e.g. Red, Blue, Black">
                            </div>
                            <div class="space-y-1.5">
                                <label class="block text-label-sm font-medium text-on-surface-variant">Color Code (Hex)</label>
                                <div class="flex items-center gap-2">
                                    <input type="color" id="cColorPicker" value="#FF0000" class="w-10 h-10 rounded border border-outline-variant cursor-pointer" onchange="document.getElementById('cCode').value = this.value">
                                    <input type="text" id="cCode" class="w-full px-3 py-2 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary/20 focus:outline-none" placeholder="#FF0000" value="#FF0000">
                                </div>
                            </div>
                            <div class="space-y-1.5">
                                <label class="block text-label-sm font-medium text-on-surface-variant">Status</label>
                                <select id="cStatus" class="w-full px-3 py-2 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary/20 focus:outline-none bg-white">
                                    <option value="1">Active</option>
                                    <option value="0">Inactive</option>
                                </select>
                            </div>
                        </div>
                        <button type="button" onclick="addColor()" class="px-4 py-2 bg-primary text-white rounded-lg font-label-md hover:opacity-90 transition-all shadow-sm">Add Color</button>
                        <div id="colorsList" class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        </div>
                    </div>
                </div>

                <div id="variantModal" class="fixed inset-0 bg-black/50 z-50 hidden items-center justify-center">
                    <div class="bg-white rounded-xl shadow-xl w-full max-w-2xl mx-4 max-h-[90vh] overflow-y-auto">
                        <div class="p-6 border-b border-outline-variant flex items-center justify-between">
                            <h3 id="variantModalTitle" class="font-headline-md text-headline-md text-on-surface">Add New Variation</h3>
                            <button type="button" onclick="closeVariantModal()" class="text-on-surface-variant hover:text-on-surface">
                                <span class="material-symbols-outlined text-[20px]">close</span>
                            </button>
                        </div>
                        <div class="p-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div class="space-y-1.5">
                                    <label class="block text-label-sm font-medium text-on-surface-variant">SKU</label>
                                    <input type="text" id="mvSku" class="w-full px-3 py-2 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary/20 focus:outline-none" placeholder="SKU">
                                </div>
                                <div class="space-y-1.5">
                                    <label class="block text-label-sm font-medium text-on-surface-variant">Variant Name</label>
                                    <input type="text" id="mvName" class="w-full px-3 py-2 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary/20 focus:outline-none" placeholder="e.g. Red / Large">
                                </div>
                                <div class="col-span-1 md:col-span-2 space-y-1.5 bg-surface-container/30 p-3 rounded-lg border border-outline-variant/30">
                                    <label class="block text-label-sm font-medium text-on-surface-variant flex justify-between items-center">
                                        <span>Attributes</span>
                                        <button type="button" onclick="addMvAttributeRow()" class="text-primary hover:underline font-normal">+ Add Attribute</button>
                                    </label>
                                    <div id="mvAttributesContainer" class="space-y-2">
                                    </div>
                                </div>
                                <div class="space-y-1.5">
                                    <label class="block text-label-sm font-medium text-on-surface-variant">Price (Rp)</label>
                                    <input type="number" step="0.01" id="mvPrice" class="w-full px-3 py-2 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary/20 focus:outline-none" placeholder="0.00">
                                </div>
                                <div class="space-y-1.5">
                                    <label class="block text-label-sm font-medium text-on-surface-variant">Stock Qty</label>
                                    <input type="number" id="mvStock" class="w-full px-3 py-2 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary/20 focus:outline-none" placeholder="0">
                                </div>
                                <div class="space-y-1.5 hidden">
                                <label class="block text-label-sm font-medium text-on-surface-variant">Min Order Qty</label>
                                    <input type="number" id="mvMinOrder" class="w-full px-3 py-2 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary/20 focus:outline-none" placeholder="1">
                                </div>
                                <div class="space-y-1.5">
                                    <label class="block text-label-sm font-medium text-on-surface-variant">Status</label>
                                    <select id="mvStatus" class="w-full px-3 py-2 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary/20 focus:outline-none bg-white">
                                        <option value="1">Active</option>
                                        <option value="0">Inactive</option>
                                    </select>
                                </div>
                            </div>
                            <div class="flex justify-end gap-3 mt-6 pt-4 border-t border-outline-variant">
                                <button type="button" onclick="closeVariantModal()" class="px-5 py-2 border border-outline-variant text-on-surface-variant rounded-lg font-label-md hover:bg-surface-container transition-colors">Cancel</button>
                                <button type="button" onclick="saveVariantFromModal()" class="px-5 py-2 bg-primary text-white rounded-lg font-label-md hover:opacity-90 transition-all shadow-sm">Save Variation</button>
                            </div>
                        </div>
                    </div>
                </div>

                <div id="warningModal" class="fixed inset-0 bg-black/50 z-[60] hidden items-center justify-center">
                    <div class="bg-white rounded-xl shadow-xl w-full max-w-md mx-4">
                        <div class="p-6">
                            <div class="flex items-center gap-3 mb-4">
                                <div class="w-10 h-10 rounded-full bg-warning/10 flex items-center justify-center flex-shrink-0">
                                    <span class="material-symbols-outlined text-warning text-[24px]">warning</span>
                                </div>
                                <h3 class="font-headline-md text-headline-md text-on-surface">Validation Warning</h3>
                            </div>
                            <p class="text-body-md text-on-surface-variant mb-6" id="warningMessage"></p>
                            <div class="flex justify-end">
                                <button type="button" onclick="closeWarningModal()" class="px-5 py-2 bg-primary text-white rounded-lg font-label-md hover:opacity-90 transition-all shadow-sm">OK</button>
                            </div>
                        </div>
                    </div>
                </div>

                <div id="colorModal" class="fixed inset-0 bg-black/50 z-50 hidden items-center justify-center">
                    <div class="bg-white rounded-xl shadow-xl w-full max-w-md mx-4">
                        <div class="p-6 border-b border-outline-variant flex items-center justify-between">
                            <h3 id="colorModalTitle" class="font-headline-md text-headline-md text-on-surface">Add New Color</h3>
                            <button type="button" onclick="closeColorModal()" class="text-on-surface-variant hover:text-on-surface">
                                <span class="material-symbols-outlined text-[20px]">close</span>
                            </button>
                        </div>
                        <div class="p-6">
                            <div class="grid grid-cols-1 gap-4">
                                <div class="space-y-1.5">
                                    <label class="block text-label-sm font-medium text-on-surface-variant">Color Name</label>
                                    <input type="text" id="mcName" class="w-full px-3 py-2 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary/20 focus:outline-none" placeholder="e.g. Red, Blue, Black">
                                </div>
                                <div class="space-y-1.5">
                                    <label class="block text-label-sm font-medium text-on-surface-variant">Color Code (Hex)</label>
                                    <div class="flex items-center gap-2">
                                        <input type="color" id="mcColorPicker" value="#FF0000" class="w-10 h-10 rounded border border-outline-variant cursor-pointer" onchange="document.getElementById('mcCode').value = this.value">
                                        <input type="text" id="mcCode" class="w-full px-3 py-2 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary/20 focus:outline-none" placeholder="#FF0000" value="#FF0000">
                                    </div>
                                </div>
                                <div class="space-y-1.5">
                                    <label class="block text-label-sm font-medium text-on-surface-variant">Status</label>
                                    <select id="mcStatus" class="w-full px-3 py-2 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary/20 focus:outline-none bg-white">
                                        <option value="1">Active</option>
                                        <option value="0">Inactive</option>
                                    </select>
                                </div>
                            </div>
                            <div class="flex justify-end gap-3 mt-6 pt-4 border-t border-outline-variant">
                                <button type="button" onclick="closeColorModal()" class="px-5 py-2 border border-outline-variant text-on-surface-variant rounded-lg font-label-md hover:bg-surface-container transition-colors">Cancel</button>
                                <button type="button" onclick="saveColorFromModal()" class="px-5 py-2 bg-primary text-white rounded-lg font-label-md hover:opacity-90 transition-all shadow-sm">Save Color</button>
                            </div>
                        </div>
                    </div>
                </div>

                <hr class="my-6 border-outline-variant">
                <div class="flex justify-end gap-3">
                    <a href="{{ route('products.index') }}" class="px-5 py-2 border border-outline-variant text-on-surface-variant rounded-lg font-label-md hover:bg-surface-container transition-colors">Discard</a>
                    <button type="submit" class="px-5 py-2 bg-primary text-white rounded-lg font-label-md hover:opacity-90 transition-all shadow-sm">{{ $product ? 'Update Product' : 'Create Product' }}</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
<script src="https://cdn.quilljs.com/1.3.6/quill.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>
<script>
function switchTab(tab) {
    document.querySelectorAll('.tab-panel').forEach(p => p.classList.add('hidden'));
    document.querySelectorAll('.tab-btn').forEach(b => {
        b.classList.remove('border-b-2', 'border-primary', 'text-on-surface', 'font-medium');
        b.classList.add('text-on-surface-variant');
    });
    document.getElementById('panel-' + tab).classList.remove('hidden');
    const btn = document.getElementById('tab-' + tab);
    btn.classList.add('border-b-2', 'border-primary', 'text-on-surface', 'font-medium');
    btn.classList.remove('text-on-surface-variant');
}

const productId = '{{ $productId }}';
let localImages = [];
@foreach($images as $img)
localImages.push({ id: '{{ $img->id }}', url: '{{ $img->url }}' });
@endforeach
@php
    $variantData = $variants->map(function ($v) {
        return [
            'id' => $v->id,
            'sku' => $v->sku ?? '',
            'variant_name' => $v->variant_name ?? '',
            'attributes' => (function() use ($v) {
                $raw = $v->getRawOriginal('attributes');
                if (!$raw) return null;
                $parsed = is_string($raw) ? json_decode($raw, true) : $raw;
                if (is_array($parsed)) {
                    foreach (['width', 'length', 'height', 'weight'] as $ik) {
                        unset($parsed[$ik]);
                    }
                }
                return $parsed;
            })(),
            'price' => $v->price ?? 0,
            'stock_qty' => $v->stock_quantity ?? 0,
            'min_order_qty' => $v->min_order_qty ?? 1,
            'status' => $v->status ?? 1,
        ];
    })->values()->all();

    $colorData = $colors->map(function ($c) {
        return [
            'id' => $c->id,
            'color_name' => $c->color_name ?? '',
            'color_code' => $c->color_code ?? '#FF0000',
            'status' => $c->status ?? 1,
        ];
    })->values()->all();
@endphp
let localVariants = @json($variantData);
let localColors = @json($colorData);

async function handleMediaUpload(input) {
    const files = input.files;
    if (files.length === 0) return;
    
    for (const file of files) {
        const reader = new FileReader();
        reader.onload = function(e) {
            localImages.push({ id: null, file: file, url: e.target.result });
            renderLocalPreviews();
        };
        reader.readAsDataURL(file);
    }
    input.value = '';
}

function previewThumbnail(input) {
    const container = document.getElementById('thumbnailPreviewContainer');
    const preview = document.getElementById('thumbnailPreview');
    
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.src = e.target.result;
            container.classList.remove('hidden');
        }
        reader.readAsDataURL(input.files[0]);
    }
}

function renderLocalPreviews() {
    const container = document.getElementById('localPreviewContainer');
    container.innerHTML = '';
    localImages.forEach(function(img, index) {
        const div = document.createElement('div');
        div.className = 'relative group border border-outline-variant rounded-lg overflow-hidden cursor-move';
        div.setAttribute('data-index', index);
        div.innerHTML = '<img src="' + img.url + '" alt="" class="w-full h-32 object-cover"><button type="button" onclick="removeLocalImage(' + index + ')" class="absolute top-1 right-1 bg-danger text-white rounded-full p-1 opacity-0 group-hover:opacity-100 transition-opacity"><span class="material-symbols-outlined text-[16px]">close</span></button>';
        container.appendChild(div);
    });
    
    if (window.sortableMedia) {
        window.sortableMedia.destroy();
    }
    window.sortableMedia = new Sortable(container, {
        animation: 150,
        onEnd: function (evt) {
            const itemEl = evt.item;
            const newIndex = evt.newIndex;
            const oldIndex = evt.oldIndex;
            const element = localImages.splice(oldIndex, 1)[0];
            localImages.splice(newIndex, 0, element);
            renderLocalPreviews();
        },
    });
}

function removeLocalImage(index) {
    localImages.splice(index, 1);
    renderLocalPreviews();
}

async function deleteMedia(id) {
    // Legacy function, replaced by removeLocalImage for all images
}


function renderVariants() {
    const container = document.getElementById('variantsList');
    container.innerHTML = '';
    localVariants.forEach(function(v, index) {
        const isSaved = !!v.id;
        const div = document.createElement('div');
        div.className = 'border border-outline-variant rounded-lg p-4';
        div.innerHTML = `
            <div class="flex items-start justify-between mb-3">
                <div>
                    <p class="font-body-md text-body-md text-on-surface font-semibold">${v.variant_name || v.sku || 'Variant'}</p>
                    <p class="text-label-sm text-on-surface-variant">SKU: ${v.sku || '-'}</p>
                </div>
                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-label-sm font-label-sm ${v.status == 1 ? 'bg-success/10 text-success' : 'bg-danger/10 text-danger'}">
                    <span class="w-1.5 h-1.5 rounded-full bg-current"></span> ${v.status == 1 ? 'Active' : 'Inactive'}
                </span>
            </div>
            ${v.attributes && typeof v.attributes === 'object' && Object.keys(v.attributes).length > 0 ? `
                <div class="mb-3">
                    <p class="text-on-surface-variant text-label-sm mb-1">Attributes:</p>
                    <div class="flex flex-wrap gap-1">
                        ${Object.entries(v.attributes).map(([k, val]) => `<span class="px-2 py-0.5 bg-surface-container-high rounded text-body-xs text-on-surface">${k}: ${val}</span>`).join('')}
                    </div>
                </div>
            ` : ''}
            <div class="flex justify-between items-center bg-surface-container/50 rounded p-2 mb-3">
                <div class="text-center">
                    <p class="text-[10px] text-on-surface-variant uppercase tracking-wider">Base Price</p>
                    <p class="font-body-sm font-semibold text-on-surface">Rp ${parseFloat(v.base_price||0).toLocaleString('id-ID')}</p>
                </div>
                <div class="text-center">
                    <p class="text-[10px] text-on-surface-variant uppercase tracking-wider">Sell Price</p>
                    <p class="font-body-sm font-semibold text-primary">Rp ${parseFloat(v.sell_price||0).toLocaleString('id-ID')}</p>
                </div>
                <div class="text-center">
                    <p class="text-[10px] text-on-surface-variant uppercase tracking-wider">Stock</p>
                    <p class="font-body-sm font-semibold text-on-surface">${v.stock_qty||0}</p>
                </div>
            </div>
            <div class="flex justify-end mt-3 pt-3 border-t border-outline-variant/20">
                <button type="button" onclick="editLocalVariant(${index})" class="text-primary hover:opacity-80 text-label-sm flex items-center gap-1 mr-3">
                    <span class="material-symbols-outlined text-[16px]">edit</span> Edit
                </button>
                <button type="button" onclick="removeLocalVariant(${index})" class="text-danger hover:opacity-80 text-label-sm flex items-center gap-1">
                    <span class="material-symbols-outlined text-[16px]">delete</span> Delete
                </button>
            </div>
        `;
        container.appendChild(div);
    });
    document.getElementById('variantsInput').value = JSON.stringify(localVariants);
}

function renderColors() {
    const container = document.getElementById('colorsList');
    container.innerHTML = '';
    localColors.forEach(function(c, index) {
        const isSaved = !!c.id;
        const div = document.createElement('div');
        div.className = 'border border-outline-variant rounded-lg p-4';
        div.innerHTML = `
            <div class="flex items-start justify-between mb-3">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-full border border-outline-variant" style="background-color: ${c.color_code}"></div>
                    <div>
                        <p class="font-body-md text-body-md text-on-surface font-semibold">${c.color_name || 'Unnamed Color'}</p>
                        <p class="text-label-sm text-on-surface-variant font-mono">${c.color_code}</p>
                    </div>
                </div>
                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-label-sm font-label-sm ${c.status == 1 ? 'bg-success/10 text-success' : 'bg-danger/10 text-danger' }">
                    <span class="w-1.5 h-1.5 rounded-full bg-current"></span> ${c.status == 1 ? 'Active' : 'Inactive'}
                </span>
            </div>
            <div class="flex justify-end mt-3 pt-3 border-t border-outline-variant/20">
                ${isSaved ? '<button type="button" class="text-primary hover:opacity-80 text-label-sm flex items-center gap-1 mr-3"><span class="material-symbols-outlined text-[16px]">edit</span> Edit</button>' : ''}
                <button type="button" onclick="removeColor(${index})" class="text-danger hover:opacity-80 text-label-sm flex items-center gap-1">
                    <span class="material-symbols-outlined text-[16px]">delete</span> Delete
                </button>
            </div>
        `;
        container.appendChild(div);
    });
    document.getElementById('colorsInput').value = JSON.stringify(localColors);
}

function addColor() {
    const name = document.getElementById('cName').value.trim();
    const code = document.getElementById('cCode').value.trim();

    if (!code) {
        showWarningModal('Please select a color code.');
        return;
    }

    const payload = {
        color_name: name,
        color_code: code,
        status: document.getElementById('cStatus').value == '1' ? 1 : 0,
    };
    localColors.push(payload);
    renderColors();
    document.getElementById('cName').value = '';
    document.getElementById('cCode').value = '#FF0000';
    document.getElementById('cColorPicker').value = '#FF0000';
    document.getElementById('cStatus').value = '1';
}

function removeColor(index) {
    localColors.splice(index, 1);
    renderColors();
}

async function editColor(id, data) {
    currentEditColorId = id;
    document.getElementById('cName').value = data.color_name || '';
    document.getElementById('cCode').value = data.color_code || '#FF0000';
    document.getElementById('cColorPicker').value = data.color_code || '#FF0000';
    document.getElementById('cStatus').value = data.status == 1 ? '1' : '0';
    openColorModal();
}

function openColorModal() {
    document.getElementById('colorModal').classList.remove('hidden');
    document.getElementById('colorModal').classList.add('flex');
}

function closeColorModal() {
    document.getElementById('colorModal').classList.add('hidden');
    document.getElementById('colorModal').classList.remove('flex');
    currentEditColorId = null;
}

async function saveColorFromModal() {
    const name = document.getElementById('mcName').value.trim();
    const code = document.getElementById('mcCode').value.trim();

    if (!code) {
        showWarningModal('Please select a color code.');
        return;
    }

    const payload = {
        color_name: name,
        color_code: code,
        status: document.getElementById('mcStatus').value == '1' ? 1 : 0,
    };
    try {
        let res;
        if (currentEditColorId) {
            res = await fetch('/api/v1/products/colors/' + currentEditColorId, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify(payload)
            });
        } else {
            res = await fetch('/api/v1/products/' + productId + '/colors', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify(payload)
            });
        }
        if (res.ok) {
            closeColorModal();
            location.reload();
        }
    } catch (e) {
        console.error(e);
        showWarningModal('Failed to save color');
    }
}

async function deleteColor(id) {
    if (!confirm('Delete this color?')) return;
    try {
        const res = await fetch('/api/v1/products/colors/' + id, {
            method: 'DELETE',
            headers: {'X-CSRF-TOKEN': '{{ csrf_token() }}'}
        });
        if (res.ok) location.reload();
    } catch (e) {
        console.error(e);
        alert('Failed to delete color');
    }
}

const predefinedAttributes = {
    'Ukuran': ['90x200', '100x200', '120x200', '140x200', '160x200', '180x200', '200x200', 'Lainnya'],
    'Kelengkapan': ['Mattress Only', 'Full Set', 'Bed Set', 'Headboard Only', 'Lainnya'],
    'Warna/Motif': ['Sage', 'Blue', 'White', 'Black', 'Grey', 'Brown', 'Lainnya'],
    'Tinggi Kasur': ['T10', 'T15', 'T20', 'T25', 'T30', 'T35', 'T40', 'Lainnya'],
    'Feel': ['Plush', 'Medium', 'Firm', 'Extra Firm', 'Lainnya']
};

function createAttributeRow(key = '', val = '') {
    const div = document.createElement('div');
    div.className = 'flex gap-2 items-center attribute-row mv-attr-row';
    
    const predefinedKeys = Object.keys(predefinedAttributes);
    const isCustomKey = key !== '' && !predefinedKeys.includes(key);
    const selectKeyVal = isCustomKey ? 'Lainnya' : key;
    
    // For values
    let options = [];
    if (predefinedAttributes[key]) {
        options = predefinedAttributes[key];
    }
    const isCustomVal = val !== '' && options.length > 0 && !options.includes(val);
    const selectValVal = isCustomVal ? 'Lainnya' : val;
    const isValueDropdownHidden = options.length === 0;

    div.innerHTML = `
        <div class="w-1/3 flex gap-2">
            <select class="w-full px-3 py-1.5 text-sm border border-outline-variant rounded-md focus:ring-2 focus:ring-primary/20 focus:outline-none" onchange="toggleAttrKey(this)">
                <option value="">Pilih Level / Atribut</option>
                ${predefinedKeys.map(k => `<option value="${k}" ${selectKeyVal === k ? 'selected' : ''}>${k}</option>`).join('')}
                <option value="Lainnya" ${selectKeyVal === 'Lainnya' ? 'selected' : ''}>Lainnya...</option>
            </select>
            <input type="text" class="attr-key mv-attr-name ${isCustomKey ? '' : 'hidden'} w-full px-3 py-1.5 text-sm border border-outline-variant rounded-md focus:ring-2 focus:ring-primary/20 focus:outline-none" placeholder="Nama Level" value="${key}">
        </div>
        <div class="flex-1 flex gap-2">
            <select class="attr-val-select ${isValueDropdownHidden ? 'hidden' : ''} w-full px-3 py-1.5 text-sm border border-outline-variant rounded-md focus:ring-2 focus:ring-primary/20 focus:outline-none" onchange="toggleAttrVal(this)">
                <option value="">Pilih Nilai</option>
                ${options.map(o => `<option value="${o}" ${selectValVal === o ? 'selected' : ''}>${o}</option>`).join('')}
                ${options.includes('Lainnya') ? '' : `<option value="Lainnya" ${selectValVal === 'Lainnya' ? 'selected' : ''}>Lainnya...</option>`}
            </select>
            <input type="text" class="attr-val mv-attr-val ${(!isValueDropdownHidden && !isCustomVal) ? 'hidden' : ''} w-full px-3 py-1.5 text-sm border border-outline-variant rounded-md focus:ring-2 focus:ring-primary/20 focus:outline-none" placeholder="Isi Nilai" value="${val}">
        </div>
        <button type="button" onclick="this.parentElement.remove()" class="text-danger hover:opacity-80 material-symbols-outlined text-[18px]">close</button>
    `;
    return div;
}

function toggleAttrKey(selectObj) {
    const keyInput = selectObj.nextElementSibling;
    const row = selectObj.closest('.attribute-row');
    const valSelect = row.querySelector('.attr-val-select');
    const valInput = row.querySelector('.attr-val');
    
    if (selectObj.value === 'Lainnya') {
        keyInput.classList.remove('hidden');
        keyInput.value = '';
        keyInput.focus();
        
        // Hide value dropdown, show text input
        valSelect.classList.add('hidden');
        valSelect.innerHTML = '<option value="">Pilih Nilai</option>';
        valInput.classList.remove('hidden');
        valInput.value = '';
    } else {
        keyInput.classList.add('hidden');
        keyInput.value = selectObj.value;
        
        // Populate value dropdown
        const options = predefinedAttributes[selectObj.value];
        if (options && options.length > 0) {
            valSelect.classList.remove('hidden');
            valSelect.innerHTML = '<option value="">Pilih Nilai</option>' + options.map(o => `<option value="${o}">${o}</option>`).join('');
            valInput.classList.add('hidden');
            valInput.value = '';
        } else {
            valSelect.classList.add('hidden');
            valSelect.innerHTML = '<option value="">Pilih Nilai</option>';
            valInput.classList.remove('hidden');
            valInput.value = '';
        }
    }
}

function toggleAttrVal(selectObj) {
    const valInput = selectObj.nextElementSibling;
    if (selectObj.value === 'Lainnya') {
        valInput.classList.remove('hidden');
        valInput.value = '';
        valInput.focus();
    } else {
        valInput.classList.add('hidden');
        valInput.value = selectObj.value;
    }
}

function addVAttributeRow(key = '', val = '') {
    document.getElementById('vAttributesContainer').appendChild(createAttributeRow(key, val));
}

function addMvAttributeRow(key = '', val = '') {
    document.getElementById('mvAttributesContainer').appendChild(createAttributeRow(key, val));
}

function getAttributesFromContainer(containerId) {
    const container = document.getElementById(containerId);
    const rows = container.querySelectorAll('.attribute-row');
    const attrs = {};
    rows.forEach(r => {
        const key = r.querySelector('.attr-key').value.trim();
        const val = r.querySelector('.attr-val').value.trim();
        if (key && val) {
            attrs[key] = val;
        }
    });
    return Object.keys(attrs).length > 0 ? attrs : null;
}

function renderAttributesToContainer(containerId, attrs) {
    const container = document.getElementById(containerId);
    container.innerHTML = '';
    if (attrs && typeof attrs === 'object') {
        for (const [key, val] of Object.entries(attrs)) {
            container.appendChild(createAttributeRow(key, val));
        }
    }
}

function addVariant() {
    const sku = document.getElementById('vSku').value.trim();
    const name = document.getElementById('vName').value.trim();
    const price = document.getElementById('vPrice').value;
    const stock = document.getElementById('vStock').value;

    if (!sku && !name) {
        showWarningModal('Please fill in at least SKU or Variant Name.');
        return;
    }
    if (!price || parseFloat(price) < 0) {
        showWarningModal('Please enter a valid Price.');
        document.getElementById('vPrice').focus();
        return;
    }
    if (stock === '' || parseInt(stock) < 0) {
        showWarningModal('Please enter a valid Stock Qty.');
        document.getElementById('vStock').focus();
        return;
    }

    const payload = {
        sku: sku,
        variant_name: name,
        attributes: getAttributesFromContainer('vAttributesContainer'),
        price: price,
        stock_qty: stock,
        min_order_qty: document.getElementById('vMinOrder').value || 1,
        status: document.getElementById('vStatus').value == '1' ? 1 : 0,
    };
    localVariants.push(payload);
    renderVariants();
    document.getElementById('vSku').value = '';
    document.getElementById('vName').value = '';
    document.getElementById('vAttributesContainer').innerHTML = '';
    document.getElementById('vPrice').value = '';
    document.getElementById('vStock').value = '';
    document.getElementById('vMinOrder').value = '';
    document.getElementById('vStatus').value = '1';
}

function removeLocalVariant(index) {
    localVariants.splice(index, 1);
    renderVariants();
}

let currentEditVariantId = null;

async function editVariant(id, data) {
    currentEditVariantId = id;
    document.getElementById('mvSku').value = data.sku || '';
    document.getElementById('mvName').value = data.variant_name || '';
    document.getElementById('mvPrice').value = data.price || '';
    document.getElementById('mvStock').value = data.stock_qty || '';
    document.getElementById('mvMinOrder').value = data.min_order_qty || '';
    document.getElementById('mvStatus').value = data.status == 1 ? '1' : '0';
    renderAttributesToContainer('mvAttributesContainer', data.attributes);
    openVariantModal();
}

async function editLocalVariant(index) {
    const v = localVariants[index];
    currentEditLocalIndex = index;
    document.getElementById('vSku').value = v.sku || '';
    document.getElementById('vName').value = v.variant_name || '';
    renderAttributesToContainer('vAttributesContainer', v.attributes);
    document.getElementById('vPrice').value = v.price || '';
    document.getElementById('vStock').value = v.stock_qty || '';
    document.getElementById('vMinOrder').value = v.min_order_qty || '';
    document.getElementById('vStatus').value = v.status == 1 ? '1' : '0';
    document.getElementById('addVariantBtn').classList.add('hidden');
    document.getElementById('updateVariantBtn').classList.remove('hidden');
    document.getElementById('cancelVariantBtn').classList.remove('hidden');
}

function cancelEditVariant() {
    currentEditLocalIndex = null;
    document.getElementById('addVariantBtn').classList.remove('hidden');
    document.getElementById('updateVariantBtn').classList.add('hidden');
    document.getElementById('cancelVariantBtn').classList.add('hidden');
    document.getElementById('vSku').value = '';
    document.getElementById('vName').value = '';
    document.getElementById('vAttributesContainer').innerHTML = '';
    document.getElementById('vPrice').value = '';
    document.getElementById('vStock').value = '';
    document.getElementById('vMinOrder').value = '';
    document.getElementById('vStatus').value = '1';
}

let currentEditLocalIndex = null;

function updateLocalVariant() {
    const sku = document.getElementById('vSku').value.trim();
    const name = document.getElementById('vName').value.trim();
    const price = document.getElementById('vPrice').value;
    const stock = document.getElementById('vStock').value;

    if (!sku && !name) {
        showWarningModal('Please fill in at least SKU or Variant Name.');
        return;
    }
    if (!price || parseFloat(price) < 0) {
        showWarningModal('Please enter a valid Price.');
        document.getElementById('vPrice').focus();
        return;
    }
    if (stock === '' || parseInt(stock) < 0) {
        showWarningModal('Please enter a valid Stock Qty.');
        document.getElementById('vStock').focus();
        return;
    }

    localVariants[currentEditLocalIndex] = {
        ...localVariants[currentEditLocalIndex],
        sku: sku,
        variant_name: name,
        attributes: getAttributesFromContainer('vAttributesContainer'),
        price: price,
        stock_qty: stock,
        min_order_qty: document.getElementById('vMinOrder').value || 1,
        status: document.getElementById('vStatus').value == '1' ? 1 : 0,
    };
    renderVariants();
    cancelEditVariant();
}

function showWarningModal(message) {
    document.getElementById('warningMessage').textContent = message;
    document.getElementById('warningModal').classList.remove('hidden');
    document.getElementById('warningModal').classList.add('flex');
}

function closeWarningModal() {
    document.getElementById('warningModal').classList.add('hidden');
    document.getElementById('warningModal').classList.remove('flex');
}

function openVariantModal() {
    document.getElementById('variantModalTitle').textContent = currentEditVariantId ? 'Edit Variation' : 'Add New Variation';
    document.getElementById('variantModal').classList.remove('hidden');
    document.getElementById('variantModal').classList.add('flex');
}

function closeVariantModal() {
    document.getElementById('variantModal').classList.add('hidden');
    document.getElementById('variantModal').classList.remove('flex');
}

async function saveVariantFromModal() {
    const sku = document.getElementById('mvSku').value.trim();
    const name = document.getElementById('mvName').value.trim();
    const basePrice = document.getElementById('mvBasePrice').value;
    const sellPrice = document.getElementById('mvSellPrice').value;
    const stock = document.getElementById('mvStock').value;
    const minOrder = document.getElementById('mvMinOrder').value;
    const sort = document.getElementById('mvSort').value;
    const status = document.getElementById('mvStatus').value;

    const payload = {
        sku: sku,
        variant_name: name,
        attributes: getAttributesFromContainer('mvAttributesContainer'),
        base_price: parseFloat(basePrice) || 0,
        sell_price: parseFloat(sellPrice) || 0,
        stock_qty: parseInt(stock) || 0,
        min_order_qty: parseInt(minOrder) || 1,
        sort_order: parseInt(sort) || 0,
        status: parseInt(status) || 0
    };

    if (currentEditVariantIndex !== null) {
        payload.id = localVariants[currentEditVariantIndex].id || null;
        localVariants[currentEditVariantIndex] = payload;
    } else {
        localVariants.push(payload);
    }
    renderVariants();
    closeVariantModal();
}

async function deleteVariant(id) {
    if (!confirm('Delete this variant?')) return;
    try {
        const res = await fetch('/api/v1/products/variants/' + id, {
            method: 'DELETE',
            headers: {'X-CSRF-TOKEN': '{{ csrf_token() }}'}
        });
        if (res.ok) location.reload();
    } catch (e) {
        console.error(e);
        alert('Failed to delete variant');
    }
}

async function uploadProductImage(file) {
    const extension = file.name.split('.').pop().toLowerCase();
    let mimeType = file.type;
    if (!mimeType) {
        if (extension === 'png') mimeType = 'image/png';
        else if (extension === 'webp') mimeType = 'image/webp';
        else if (extension === 'gif') mimeType = 'image/gif';
        else if (extension === 'svg') mimeType = 'image/svg+xml';
        else mimeType = 'image/jpeg';
    }

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}';

    // 1. Coba Direct Upload via S3 Pre-Signed URL terlebih dahulu
    try {
        const authRes = await fetch('/api/v1/media/upload-url', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify({ mime_type: mimeType, extension: extension })
        });

        if (authRes.ok) {
            const { upload_url, file_path } = await authRes.json();

            const uploadRes = await fetch(upload_url, {
                method: 'PUT',
                headers: {
                    'Content-Type': mimeType
                },
                body: file
            });

            if (uploadRes.ok) {
                return file_path;
            }
        }
    } catch (directErr) {
        console.warn('Direct S3 upload could not connect from browser, falling back to server upload...', directErr);
    }

    // 2. Fallback jika Direct Upload dari browser tidak dapat menjangkau endpoint (misal mixed content / network)
    const fallbackData = new FormData();
    fallbackData.append('file', file);

    const fallbackRes = await fetch('/api/v1/media/upload', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json'
        },
        body: fallbackData
    });

    if (!fallbackRes.ok) {
        let errData = await fallbackRes.json().catch(() => ({}));
        throw new Error(errData.message || 'Gagal mengunggah file ke Object Storage (HTTP ' + fallbackRes.status + ')');
    }

    const resJson = await fallbackRes.json();
    return resJson.file_path;
}

document.getElementById('productForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    document.getElementById('variantsInput').value = JSON.stringify(localVariants);
    document.getElementById('colorsInput').value = JSON.stringify(localColors);
    
    // Get Quill content
    var quillHtml = quill.root.innerHTML;
    // If empty (only contains <p><br></p>), set it to empty string
    if (quillHtml === '<p><br></p>') {
        quillHtml = '';
    }
    document.getElementById('description-input').value = quillHtml;

    let formData = new FormData(this);
    const submitBtn = this.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerText;
    
    try {
        submitBtn.innerText = 'Uploading Media...';
        submitBtn.disabled = true;

        // Direct upload thumbnail if selected
        const thumbnailInput = document.getElementById('thumbnailInput');
        formData.delete('thumbnail_file');
        if (thumbnailInput.files.length > 0) {
            const uploadedPath = await uploadProductImage(thumbnailInput.files[0]);
            formData.append('thumbnail_file', uploadedPath);
        }
        
        // Direct upload new_images
        for (let i = 0; i < localImages.length; i++) {
            let img = localImages[i];
            if (img.file) {
                submitBtn.innerText = `Uploading Image ${i+1}...`;
                const uploadedPath = await uploadProductImage(img.file);
                formData.append('new_images[]', uploadedPath);
                formData.append('new_image_orders[]', i);
            } else if (img.id) {
                formData.append('existing_images[]', img.id);
                formData.append('existing_image_orders[]', i);
            }
        }

        submitBtn.innerText = 'Saving...';

        let res = await fetch(this.action, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        });
        
        if (res.ok) {
            window.location.href = '{{ route("products.index") }}';
        } else {
            const errData = await res.json().catch(() => ({ message: 'Server error (' + res.status + ')' }));
            console.error(errData);
            alert(errData.message || 'Failed to save product');
            submitBtn.innerText = originalText;
            submitBtn.disabled = false;
        }
    } catch(err) {
        console.error(err);
        alert('Error saving product: ' + err.message);
        const submitBtn = this.querySelector('button[type="submit"]');
        submitBtn.innerText = '{{ $product ? "Update Product" : "Create Product" }}';
        submitBtn.disabled = false;
    }
});

renderLocalPreviews();
renderVariants();
renderColors();

$(document).ready(function() {
    // Initialize Quill
    window.quill = new Quill('#quill-editor', {
        theme: 'snow',
        modules: {
            toolbar: [
                [{ 'header': [1, 2, 3, false] }],
                ['bold', 'italic', 'underline', 'strike'],
                [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                ['link', 'clean']
            ]
        }
    });

    $('#categorySelect').select2({
        placeholder: 'Select Category',
        allowClear: true,
        width: '100%'
    });
    $('#brandSelect').select2({
        placeholder: 'Select Brand',
        allowClear: true,
        width: '100%'
    });

    $('input[name="name"]').on('input', function() {
        let slug = $(this).val().toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/(^-|-$)+/g, '');
        $('#productSlug').val(slug);
    });

    @if($errors->has('slug'))
        alert('Warning: Slug (URL) yang dihasilkan sudah digunakan oleh data lain. Silakan ubah nama atau slug secara manual.');
    @endif
});
</script>
@endpush
