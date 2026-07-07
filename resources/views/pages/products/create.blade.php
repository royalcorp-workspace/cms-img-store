@extends('layouts.app')

@section('title', isset($product) ? 'Edit Product' : 'Create Product')

@section('content')
@php
    $productId = $product->id ?? null;
    $images = $product->images ?? collect([]);
    $variants = $product->variants ?? collect([]);
    $colors = $product->colors ?? collect([]);

    function buildCategoryOptions($categories, $parentId = null, $prefix = '')
    {
        $html = '';
        foreach ($categories->where('parent_id', $parentId) as $cat) {
            $selected = (old('category_id', $product->category_id ?? '') == $cat->id) ? 'selected' : '';
            $html .= '<option value="' . $cat->id . '" ' . $selected . '>' . $prefix . e($cat->name) . '</option>';
            $html .= buildCategoryOptions($categories, $cat->id, $prefix . '&nbsp;&nbsp;&nbsp;&nbsp;');
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

    <div class="bg-white rounded-xl shadow-sm border border-outline-variant/30">
        <div class="p-6">
            <div class="flex border-b border-outline-variant mb-6">
                <button onclick="switchTab('details')" id="tab-details" class="tab-btn px-4 py-2 text-on-surface border-b-2 border-primary text-label-md font-label-md font-medium">Details</button>
                <button onclick="switchTab('media')" id="tab-media" class="tab-btn px-4 py-2 text-on-surface-variant hover:text-on-surface text-label-md font-label-md">Media</button>
                <button onclick="switchTab('variations')" id="tab-variations" class="tab-btn px-4 py-2 text-on-surface-variant hover:text-on-surface text-label-md font-label-md">Variations</button>
                <button onclick="switchTab('colors')" id="tab-colors" class="tab-btn px-4 py-2 text-on-surface-variant hover:text-on-surface text-label-md font-label-md">Colors</button>
            </div>

            <form id="productForm" method="POST" action="{{ $product->id ? route('products.update', $product->id) : route('products.store') }}">
                @csrf
                @if($product) @method('PUT') @endif
                <input type="hidden" name="variants" id="variantsInput" value="">
                <input type="hidden" name="colors" id="colorsInput" value="">

                <div id="panel-details" class="tab-panel">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="space-y-1.5">
                            <label class="block text-label-sm font-medium text-on-surface-variant">Product Name <span class="inline-flex items-center cursor-help text-on-surface-variant relative group"><span class="material-symbols-outlined text-[18px]">info</span><span class="absolute right-0 top-full mt-2 w-80 bg-surface-container-highest rounded-lg shadow-lg border border-outline-variant p-4 text-body-xs text-on-surface-variant hidden group-hover:block z-50">Nama produk yang akan ditampilkan di katalog</span></span></label>
                            <input type="text" name="name" value="{{ $product->name ?? '' }}" class="w-full px-3 py-2 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary/20 focus:outline-none" placeholder="Enter product name" required>
                        </div>
                        <div class="space-y-1.5">
                            <label class="block text-label-sm font-medium text-on-surface-variant">Category <span class="inline-flex items-center cursor-help text-on-surface-variant relative group"><span class="material-symbols-outlined text-[18px]">info</span><span class="absolute right-0 top-full mt-2 w-80 bg-surface-container-highest rounded-lg shadow-lg border border-outline-variant p-4 text-body-xs text-on-surface-variant hidden group-hover:block z-50">Kategori untuk mengelompokkan produk</span></span></label>
                            <select name="category_id" id="categorySelect" class="w-full px-3 py-2 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary/20 focus:outline-none bg-white">
                                <option value="">Select Category</option>
                                {!! buildCategoryOptions(\App\Models\Product\Category::all()) !!}
                            </select>
                        </div>
                        <div class="space-y-1.5">
                            <label class="block text-label-sm font-medium text-on-surface-variant">SKU <span class="inline-flex items-center cursor-help text-on-surface-variant relative group"><span class="material-symbols-outlined text-[18px]">info</span><span class="absolute right-0 top-full mt-2 w-80 bg-surface-container-highest rounded-lg shadow-lg border border-outline-variant p-4 text-body-xs text-on-surface-variant hidden group-hover:block z-50">Kode unik untuk identifikasi produk</span></span></label>
                            <input type="text" name="sku" value="{{ $product->sku ?? $product->id ?? '' }}" class="w-full px-3 py-2 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary/20 focus:outline-none" placeholder="Enter SKU">
                        </div>
                        <div class="space-y-1.5">
                            <label class="block text-label-sm font-medium text-on-surface-variant">Price (Rp) <span class="inline-flex items-center cursor-help text-on-surface-variant relative group"><span class="material-symbols-outlined text-[18px]">info</span><span class="absolute right-0 top-full mt-2 w-80 bg-surface-container-highest rounded-lg shadow-lg border border-outline-variant p-4 text-body-xs text-on-surface-variant hidden group-hover:block z-50">Harga jual produk dalam Rupiah</span></span></label>
                            <input type="number" name="price" step="0.01" value="{{ $product->price ?? '' }}" class="w-full px-3 py-2 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary/20 focus:outline-none" placeholder="Enter price">
                        </div>
                        <div class="space-y-1.5">
                            <label class="block text-label-sm font-medium text-on-surface-variant">Stock Quantity <span class="inline-flex items-center cursor-help text-on-surface-variant relative group"><span class="material-symbols-outlined text-[18px]">info</span><span class="absolute right-0 top-full mt-2 w-80 bg-surface-container-highest rounded-lg shadow-lg border border-outline-variant p-4 text-body-xs text-on-surface-variant hidden group-hover:block z-50">Jumlah stok tersedia untuk dijual</span></span></label>
                            <input type="number" name="stock" value="{{ $product->stock ?? '' }}" class="w-full px-3 py-2 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary/20 focus:outline-none" placeholder="Enter stock">
                        </div>
                        <div class="md:col-span-2 space-y-2">
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
                        <div class="space-y-1.5">
                            <label class="block text-label-sm font-medium text-on-surface-variant">Status <span class="inline-flex items-center cursor-help text-on-surface-variant relative group"><span class="material-symbols-outlined text-[18px]">info</span><span class="absolute right-0 top-full mt-2 w-80 bg-surface-container-highest rounded-lg shadow-lg border border-outline-variant p-4 text-body-xs text-on-surface-variant hidden group-hover:block z-50">Atur apakah produk aktif atau tidak</span></span></label>
                            <select name="status" class="w-full px-3 py-2 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary/20 focus:outline-none bg-white">
                                <option value="1" {{ old('status', $product->status ?? 1) == 1 ? 'selected' : '' }}>Active</option>
                                <option value="0" {{ old('status', $product->status ?? 1) == 0 ? 'selected' : '' }}>Inactive</option>
                            </select>
                        </div>
                        <div class="md:col-span-2 space-y-1.5">
                            <label class="block text-label-sm font-medium text-on-surface-variant">Description <span class="inline-flex items-center cursor-help text-on-surface-variant relative group"><span class="material-symbols-outlined text-[18px]">info</span><span class="absolute right-0 top-full mt-2 w-80 bg-surface-container-highest rounded-lg shadow-lg border border-outline-variant p-4 text-body-xs text-on-surface-variant hidden group-hover:block z-50">Deskripsi lengkap produk untuk customer</span></span></label>
                            <textarea name="description" class="w-full px-3 py-2 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary/20 focus:outline-none" rows="4" placeholder="Enter product description">{{ $product->description ?? '' }}</textarea>
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
                        <div id="mediaPreview" class="grid grid-cols-2 md:grid-cols-4 gap-4">
                            @foreach($images as $img)
                            <div class="relative group border border-outline-variant rounded-lg overflow-hidden">
                                <img src="{{ $img->url }}" alt="{{ $img->alt_text ?? '' }}" class="w-full h-32 object-cover">
                                <button type="button" onclick="deleteMedia('{{ $img->id }}')" class="absolute top-1 right-1 bg-danger text-white rounded-full p-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                    <span class="material-symbols-outlined text-[16px]">close</span>
                                </button>
                            </div>
                            @endforeach
                            <div id="localPreviewContainer"></div>
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
                            <div class="space-y-1.5">
                                <label class="block text-label-sm font-medium text-on-surface-variant">Width</label>
                                <input type="number" step="0.01" id="vWidth" class="w-full px-3 py-2 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary/20 focus:outline-none" placeholder="cm">
                            </div>
                            <div class="space-y-1.5">
                                <label class="block text-label-sm font-medium text-on-surface-variant">Length</label>
                                <input type="number" step="0.01" id="vLength" class="w-full px-3 py-2 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary/20 focus:outline-none" placeholder="cm">
                            </div>
                            <div class="space-y-1.5">
                                <label class="block text-label-sm font-medium text-on-surface-variant">Height</label>
                                <input type="number" step="0.01" id="vHeight" class="w-full px-3 py-2 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary/20 focus:outline-none" placeholder="cm">
                            </div>
                            <div class="space-y-1.5">
                                <label class="block text-label-sm font-medium text-on-surface-variant">Weight</label>
                                <input type="number" step="0.01" id="vWeight" class="w-full px-3 py-2 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary/20 focus:outline-none" placeholder="kg">
                            </div>
                            <div class="space-y-1.5">
                                <label class="block text-label-sm font-medium text-on-surface-variant">Price (Rp)</label>
                                <input type="number" step="0.01" id="vPrice" class="w-full px-3 py-2 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary/20 focus:outline-none" placeholder="0.00">
                            </div>
                            <div class="space-y-1.5">
                                <label class="block text-label-sm font-medium text-on-surface-variant">Stock Qty</label>
                                <input type="number" id="vStock" class="w-full px-3 py-2 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary/20 focus:outline-none" placeholder="0">
                            </div>
                            <div class="space-y-1.5">
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
                                            <p class="text-on-surface-variant">Dimensions</p>
                                            <p class="font-medium text-on-surface">{{ $v->width ?? 0 }}x{{ $v->length ?? 0 }}x{{ $v->height ?? 0 }} cm</p>
                                        </div>
                                        <div>
                                            <p class="text-on-surface-variant">Weight</p>
                                            <p class="font-medium text-on-surface">{{ $v->weight ?? 0 }} kg</p>
                                        </div>
                                    </div>
                                    <div class="flex justify-end mt-3 pt-3 border-t border-outline-variant/20">
                                        <button type="button" onclick="editVariant('{{ $v->id }}', {{ json_encode(['sku' => $v->sku, 'variant_name' => $v->variant_name, 'width' => $v->width, 'length' => $v->length, 'height' => $v->height, 'weight' => $v->weight, 'price' => $v->price, 'stock_qty' => $v->stock_qty, 'min_order_qty' => $v->min_order_qty, 'status' => $v->status]) }})" class="text-primary hover:opacity-80 text-label-sm flex items-center gap-1 mr-3">
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
                                <div class="space-y-1.5">
                                    <label class="block text-label-sm font-medium text-on-surface-variant">Width</label>
                                    <input type="number" step="0.01" id="mvWidth" class="w-full px-3 py-2 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary/20 focus:outline-none" placeholder="cm">
                                </div>
                                <div class="space-y-1.5">
                                    <label class="block text-label-sm font-medium text-on-surface-variant">Length</label>
                                    <input type="number" step="0.01" id="mvLength" class="w-full px-3 py-2 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary/20 focus:outline-none" placeholder="cm">
                                </div>
                                <div class="space-y-1.5">
                                    <label class="block text-label-sm font-medium text-on-surface-variant">Height</label>
                                    <input type="number" step="0.01" id="mvHeight" class="w-full px-3 py-2 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary/20 focus:outline-none" placeholder="cm">
                                </div>
                                <div class="space-y-1.5">
                                    <label class="block text-label-sm font-medium text-on-surface-variant">Weight</label>
                                    <input type="number" step="0.01" id="mvWeight" class="w-full px-3 py-2 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary/20 focus:outline-none" placeholder="kg">
                                </div>
                                <div class="space-y-1.5">
                                    <label class="block text-label-sm font-medium text-on-surface-variant">Price (Rp)</label>
                                    <input type="number" step="0.01" id="mvPrice" class="w-full px-3 py-2 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary/20 focus:outline-none" placeholder="0.00">
                                </div>
                                <div class="space-y-1.5">
                                    <label class="block text-label-sm font-medium text-on-surface-variant">Stock Qty</label>
                                    <input type="number" id="mvStock" class="w-full px-3 py-2 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary/20 focus:outline-none" placeholder="0">
                                </div>
                                <div class="space-y-1.5">
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
@php
    $variantData = $variants->map(function ($v) {
        return [
            'id' => $v->id,
            'sku' => $v->sku ?? '',
            'variant_name' => $v->variant_name ?? '',
            'width' => $v->width ?? 0,
            'length' => $v->length ?? 0,
            'height' => $v->height ?? 0,
            'weight' => $v->weight ?? 0,
            'price' => $v->price ?? 0,
            'stock_qty' => $v->stock_qty ?? 0,
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
    if (!input.files || !input.files.length) return;
    const files = Array.from(input.files);

    if (productId) {
        for (const file of files) {
            const formData = new FormData();
            formData.append('image', file);
            formData.append('alt_text', file.name);
            formData.append('sort_order', 0);
            formData.append('status', 1);
            try {
                const res = await fetch('/api/v1/products/' + productId + '/images', {
                    method: 'POST',
                    headers: {'X-CSRF-TOKEN': '{{ csrf_token() }}'},
                    body: formData
                });
                if (res.ok) {
                    const data = await res.json();
                    const container = document.getElementById('mediaPreview');
                    const div = document.createElement('div');
                    div.className = 'relative group border border-outline-variant rounded-lg overflow-hidden';
                    div.innerHTML = '<img src="' + data.data.url + '" alt="" class="w-full h-32 object-cover"><button type="button" onclick="deleteMedia(\'' + data.data.id + '\')" class="absolute top-1 right-1 bg-danger text-white rounded-full p-1 opacity-0 group-hover:opacity-100 transition-opacity"><span class="material-symbols-outlined text-[16px]">close</span></button>';
                    container.insertBefore(div, document.getElementById('localPreviewContainer'));
                }
            } catch (e) {
                console.error(e);
                alert('Failed to upload image: ' + file.name);
            }
        }
    } else {
        for (const file of files) {
            const reader = new FileReader();
            reader.onload = function(e) {
                localImages.push({ file: file, url: e.target.result });
                renderLocalPreviews();
            };
            reader.readAsDataURL(file);
        }
    }
    input.value = '';
}

function renderLocalPreviews() {
    const container = document.getElementById('localPreviewContainer');
    container.innerHTML = '';
    localImages.forEach(function(img, index) {
        const div = document.createElement('div');
        div.className = 'relative group border border-outline-variant rounded-lg overflow-hidden';
        div.innerHTML = '<img src="' + img.url + '" alt="" class="w-full h-32 object-cover"><button type="button" onclick="removeLocalImage(' + index + ')" class="absolute top-1 right-1 bg-danger text-white rounded-full p-1 opacity-0 group-hover:opacity-100 transition-opacity"><span class="material-symbols-outlined text-[16px]">close</span></button>';
        container.appendChild(div);
    });
}

function removeLocalImage(index) {
    localImages.splice(index, 1);
    renderLocalPreviews();
}

async function deleteMedia(id) {
    if (!confirm('Delete this image?')) return;
    try {
        const res = await fetch('/api/v1/products/images/' + id, {
            method: 'DELETE',
            headers: {'X-CSRF-TOKEN': '{{ csrf_token() }}'}
        });
        if (res.ok) location.reload();
    } catch (e) {
        console.error(e);
        alert('Failed to delete image');
    }
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
            <div class="grid grid-cols-2 gap-3 text-body-sm">
                <div>
                    <p class="text-on-surface-variant">Price</p>
                    <p class="font-medium text-on-surface">Rp${Number(v.price).toLocaleString('id-ID', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</p>
                </div>
                <div>
                    <p class="text-on-surface-variant">Stock</p>
                    <p class="font-medium text-on-surface">${v.stock_qty || 0}</p>
                </div>
                <div>
                    <p class="text-on-surface-variant">Dimensions</p>
                    <p class="font-medium text-on-surface">${v.width || 0}x${v.length || 0}x${v.height || 0} cm</p>
                </div>
                <div>
                    <p class="text-on-surface-variant">Weight</p>
                    <p class="font-medium text-on-surface">${v.weight || 0} kg</p>
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
        width: document.getElementById('vWidth').value,
        length: document.getElementById('vLength').value,
        height: document.getElementById('vHeight').value,
        weight: document.getElementById('vWeight').value,
        price: price,
        stock_qty: stock,
        min_order_qty: document.getElementById('vMinOrder').value || 1,
        status: document.getElementById('vStatus').value == '1' ? 1 : 0,
    };
    localVariants.push(payload);
    renderVariants();
    document.getElementById('vSku').value = '';
    document.getElementById('vName').value = '';
    document.getElementById('vWidth').value = '';
    document.getElementById('vLength').value = '';
    document.getElementById('vHeight').value = '';
    document.getElementById('vWeight').value = '';
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
    document.getElementById('mvWidth').value = data.width || '';
    document.getElementById('mvLength').value = data.length || '';
    document.getElementById('mvHeight').value = data.height || '';
    document.getElementById('mvWeight').value = data.weight || '';
    document.getElementById('mvPrice').value = data.price || '';
    document.getElementById('mvStock').value = data.stock_qty || '';
    document.getElementById('mvMinOrder').value = data.min_order_qty || '';
    document.getElementById('mvStatus').value = data.status == 1 ? '1' : '0';
    openVariantModal();
}

async function editLocalVariant(index) {
    const v = localVariants[index];
    currentEditLocalIndex = index;
    document.getElementById('vSku').value = v.sku || '';
    document.getElementById('vName').value = v.variant_name || '';
    document.getElementById('vWidth').value = v.width || '';
    document.getElementById('vLength').value = v.length || '';
    document.getElementById('vHeight').value = v.height || '';
    document.getElementById('vWeight').value = v.weight || '';
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
    document.getElementById('vWidth').value = '';
    document.getElementById('vLength').value = '';
    document.getElementById('vHeight').value = '';
    document.getElementById('vWeight').value = '';
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
        width: document.getElementById('vWidth').value,
        length: document.getElementById('vLength').value,
        height: document.getElementById('vHeight').value,
        weight: document.getElementById('vWeight').value,
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
    const price = document.getElementById('mvPrice').value;
    const stock = document.getElementById('mvStock').value;

    if (!sku && !name) {
        showWarningModal('Please fill in at least SKU or Variant Name.');
        return;
    }
    if (!price || parseFloat(price) < 0) {
        showWarningModal('Please enter a valid Price.');
        document.getElementById('mvPrice').focus();
        return;
    }
    if (stock === '' || parseInt(stock) < 0) {
        showWarningModal('Please enter a valid Stock Qty.');
        document.getElementById('mvStock').focus();
        return;
    }

    const payload = {
        sku: sku,
        variant_name: name,
        width: document.getElementById('mvWidth').value,
        length: document.getElementById('mvLength').value,
        height: document.getElementById('mvHeight').value,
        weight: document.getElementById('mvWeight').value,
        price: price,
        stock_qty: stock,
        min_order_qty: document.getElementById('mvMinOrder').value || 1,
        status: document.getElementById('mvStatus').value == '1' ? 1 : 0,
    };
    try {
        let res;
        if (currentEditVariantId) {
            res = await fetch('/api/v1/products/variants/' + currentEditVariantId, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify(payload)
            });
        } else {
            res = await fetch('/api/v1/products/' + productId + '/variants', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify(payload)
            });
        }
        if (res.ok) {
            closeVariantModal();
            location.reload();
        }
    } catch (e) {
        console.error(e);
        showWarningModal('Failed to save variant');
    }
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

document.getElementById('productForm').addEventListener('submit', function(e) {
    document.getElementById('variantsInput').value = JSON.stringify(localVariants);
    document.getElementById('colorsInput').value = JSON.stringify(localColors);
});

renderVariants();
renderColors();

$(document).ready(function() {
    $('#categorySelect').select2({
        placeholder: 'Select Category',
        allowClear: true,
        width: '100%'
    });
});
</script>
@endpush
