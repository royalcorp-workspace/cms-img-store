@extends('layouts.app')

@section('title', 'Edit Price Setting')

@section('content')
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="font-headline-lg text-headline-lg text-on-surface">Edit Price Setting</h1>
            <nav class="flex items-center gap-2 text-body-md text-on-surface-variant mt-1">
                <a href="{{ route('dashboard') }}" class="text-primary hover:underline">Dashboard</a>
                <span class="material-symbols-outlined text-[16px]">chevron_right</span>
                <a href="{{ route('price-settings.index') }}" class="text-primary hover:underline">Price Settings</a>
                <span class="material-symbols-outlined text-[16px]">chevron_right</span>
                <span>Edit</span>
            </nav>
        </div>
        <a href="{{ route('price-settings.index') }}" class="flex items-center gap-2 px-4 py-2 bg-surface-container-lowest border border-outline-variant text-secondary rounded-lg font-label-md hover:bg-surface-container transition-all">
            <span class="material-symbols-outlined text-[18px]">arrow_back</span> Back
        </a>
    </div>

    <form id="priceSettingForm" method="POST" action="{{ route('price-settings.update', $setting->id) }}">
        @csrf
        @method('PUT')
        <!-- Hidden inputs container for storing state across AJAX paginations -->
        <div id="hidden-inputs-container"></div>
        <div class="rounded-xl shadow-sm border border-outline-variant/30 p-6 mb-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="space-y-1.5">
                    <label class="block text-label-sm font-medium text-on-surface-variant">Campaign Name <span class="text-danger">*</span> <span class="inline-flex items-center cursor-help text-on-surface-variant relative group"><span class="material-symbols-outlined text-[18px]">info</span><span class="absolute right-0 top-full mt-2 w-80 bg-surface-container-highest rounded-lg shadow-lg border border-outline-variant p-4 text-body-xs text-on-surface-variant hidden group-hover:block z-50">Nama unik untuk campaign diskon</span></span></label>
                    <input type="text" name="title" class="w-full px-3 py-2 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary/20 focus:outline-none" placeholder="e.g., Summer Collection 2024" required value="{{ old('title', $setting->title) }}">
                    @error('title')<p class="text-danger text-sm">{{ $message }}</p>@enderror
                </div>
                <div class="space-y-1.5">
                    <label class="block text-label-sm font-medium text-on-surface-variant">Code <span class="inline-flex items-center cursor-help text-on-surface-variant relative group"><span class="material-symbols-outlined text-[18px]">info</span><span class="absolute right-0 top-full mt-2 w-80 bg-surface-container-highest rounded-lg shadow-lg border border-outline-variant p-4 text-body-xs text-on-surface-variant hidden group-hover:block z-50">Kode voucher untuk campaign ini</span></span></label>
                    <input type="text" name="code" class="w-full px-3 py-2 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary/20 focus:outline-none" placeholder="e.g., SUMMER24" value="{{ old('code', $setting->code) }}">
                    @error('code')<p class="text-danger text-sm">{{ $message }}</p>@enderror
                </div>
            </div>
            <div class="space-y-1.5 mt-4">
                <label class="block text-label-sm font-medium text-on-surface-variant">Description</label>
                <textarea name="description" class="w-full px-3 py-2 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary/20 focus:outline-none" rows="3" placeholder="Campaign description...">{{ old('description', $setting->description) }}</textarea>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4">
                <div class="space-y-1.5">
                    <label class="block text-label-sm font-medium text-on-surface-variant">Type <span class="inline-flex items-center cursor-help text-on-surface-variant relative group"><span class="material-symbols-outlined text-[18px]">info</span><span class="absolute right-0 top-full mt-2 w-80 bg-surface-container-highest rounded-lg shadow-lg border border-outline-variant p-4 text-body-xs text-on-surface-variant hidden group-hover:block z-50">Diskon langsung: potongan per item. Diskon volume: potongan berdasarkan qty pembelian</span></span></label>
                    <select name="type" class="w-full px-3 py-2 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary/20 focus:outline-none bg-white">
                        <option value="1" {{ old('type', $setting->type) == 1 ? 'selected' : '' }}>Diskon Langsung</option>
                        <option value="2" {{ old('type', $setting->type) == 2 ? 'selected' : '' }}>Diskon Volume</option>
                    </select>
                </div>
                <div class="space-y-1.5">
                    <label class="block text-label-sm font-medium text-on-surface-variant">Discount Type <span class="inline-flex items-center cursor-help text-on-surface-variant relative group"><span class="material-symbols-outlined text-[18px]">info</span><span class="absolute right-0 top-full mt-2 w-80 bg-surface-container-highest rounded-lg shadow-lg border border-outline-variant p-4 text-body-xs text-on-surface-variant hidden group-hover:block z-50">Pilih bentuk diskon: persentase atau nominal Rupiah</span></span></label>
                    <select name="discount_type" class="w-full px-3 py-2 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary/20 focus:outline-none bg-white">
                        <option value="1" {{ old('discount_type', $setting->discount_type) == 1 ? 'selected' : '' }}>Percentage (%)</option>
                        <option value="2" {{ old('discount_type', $setting->discount_type) == 2 ? 'selected' : '' }}>Nominal</option>
                    </select>
                </div>
                <div class="space-y-1.5">
                    <label class="block text-label-sm font-medium text-on-surface-variant">Discount Value <span class="text-danger">*</span> <span class="inline-flex items-center cursor-help text-on-surface-variant relative group"><span class="material-symbols-outlined text-[18px]">info</span><span class="absolute right-0 top-full mt-2 w-80 bg-surface-container-highest rounded-lg shadow-lg border border-outline-variant p-4 text-body-xs text-on-surface-variant hidden group-hover:block z-50">Nilai diskon: persentase 0-100 atau nominal Rupiah</span></span></label>
                    <input type="number" name="discount_value" step="0.01" class="w-full px-3 py-2 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary/20 focus:outline-none" placeholder="0" required value="{{ old('discount_value', $setting->discount_value) }}" id="discountValueInput">
                    @error('discount_value')<p class="text-danger text-sm">{{ $message }}</p>@enderror
                </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                <div class="space-y-1.5">
                    <label class="block text-label-sm font-medium text-on-surface-variant">Min Purchase (Rp) <span class="inline-flex items-center cursor-help text-on-surface-variant relative group"><span class="material-symbols-outlined text-[18px]">info</span><span class="absolute right-0 top-full mt-2 w-80 bg-surface-container-highest rounded-lg shadow-lg border border-outline-variant p-4 text-body-xs text-on-surface-variant hidden group-hover:block z-50">Harga minimum pembelian agar diskon berlaku</span></span></label>
                    <input type="number" name="min_purchase" step="0.01" class="w-full px-3 py-2 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary/20 focus:outline-none" placeholder="0" value="{{ old('min_purchase', $setting->min_purchase) }}">
                </div>
                <div class="space-y-1.5">
                    <label class="block text-label-sm font-medium text-on-surface-variant">Max Discount (Rp) <span class="inline-flex items-center cursor-help text-on-surface-variant relative group"><span class="material-symbols-outlined text-[18px]">info</span><span class="absolute right-0 top-full mt-2 w-80 bg-surface-container-highest rounded-lg shadow-lg border border-outline-variant p-4 text-body-xs text-on-surface-variant hidden group-hover:block z-50">Batas maksimal potongan harga</span></span></label>
                    <input type="number" name="max_discount" step="0.01" class="w-full px-3 py-2 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary/20 focus:outline-none" placeholder="0" value="{{ old('max_discount', $setting->max_discount) }}">
                </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4">
                <div class="space-y-1.5">
                    <label class="block text-label-sm font-medium text-on-surface-variant">Start Date <span class="inline-flex items-center cursor-help text-on-surface-variant relative group"><span class="material-symbols-outlined text-[18px]">info</span><span class="absolute right-0 top-full mt-2 w-80 bg-surface-container-highest rounded-lg shadow-lg border border-outline-variant p-4 text-body-xs text-on-surface-variant hidden group-hover:block z-50">Tanggal mulai diskon berlaku</span></span></label>
                    <input type="date" name="start_date" class="w-full px-3 py-2 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary/20 focus:outline-none" value="{{ old('start_date', $setting->start_date?->format('Y-m-d')) }}">
                </div>
                <div class="space-y-1.5">
                    <label class="block text-label-sm font-medium text-on-surface-variant">End Date <span class="inline-flex items-center cursor-help text-on-surface-variant relative group"><span class="material-symbols-outlined text-[18px]">info</span><span class="absolute right-0 top-full mt-2 w-80 bg-surface-container-highest rounded-lg shadow-lg border border-outline-variant p-4 text-body-xs text-on-surface-variant hidden group-hover:block z-50">Tanggal berakhir diskon berlaku</span></span></label>
                    <input type="date" name="end_date" class="w-full px-3 py-2 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary/20 focus:outline-none" value="{{ old('end_date', $setting->end_date?->format('Y-m-d')) }}">
                </div>
                <div class="space-y-1.5">
                    <label class="block text-label-sm font-medium text-on-surface-variant">Sort Order <span class="inline-flex items-center cursor-help text-on-surface-variant relative group"><span class="material-symbols-outlined text-[18px]">info</span><span class="absolute right-0 top-full mt-2 w-80 bg-surface-container-highest rounded-lg shadow-lg border border-outline-variant p-4 text-body-xs text-on-surface-variant hidden group-hover:block z-50">Urutan tampilan di halaman</span></span></label>
                    <input type="number" name="sort_order" class="w-full px-3 py-2 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary/20 focus:outline-none" placeholder="0" value="{{ old('sort_order', $setting->sort_order) }}">
                </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                <div class="space-y-1.5">
                    <label class="block text-label-sm font-medium text-on-surface-variant">Scope <span class="inline-flex items-center cursor-help text-on-surface-variant relative group"><span class="material-symbols-outlined text-[18px]">info</span><span class="absolute right-0 top-full mt-2 w-80 bg-surface-container-highest rounded-lg shadow-lg border border-outline-variant p-4 text-body-xs text-on-surface-variant hidden group-hover:block z-50">Jangkauan produk yang terkena diskon</span></span></label>
                    <select name="scope" class="w-full px-3 py-2 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary/20 focus:outline-none bg-white">
                        <option value="1" {{ old('scope', $setting->scope) == 1 ? 'selected' : '' }}>All products</option>
                        <option value="2" {{ old('scope', $setting->scope) == 2 ? 'selected' : '' }}>Specific products</option>
                        <option value="3" {{ old('scope', $setting->scope) == 3 ? 'selected' : '' }}>Category</option>
                    </select>
                </div>
                <div class="space-y-1.5">
                    <label class="block text-label-sm font-medium text-on-surface-variant">Status <span class="inline-flex items-center cursor-help text-on-surface-variant relative group"><span class="material-symbols-outlined text-[18px]">info</span><span class="absolute right-0 top-full mt-2 w-80 bg-surface-container-highest rounded-lg shadow-lg border border-outline-variant p-4 text-body-xs text-on-surface-variant hidden group-hover:block z-50">Aktif/nonaktifkan campaign</span></span></label>
                    <select name="is_active" class="w-full px-3 py-2 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary/20 focus:outline-none bg-white">
                        <option value="1" {{ old('is_active', $setting->is_active) == 1 ? 'selected' : '' }}>Active</option>
                        <option value="0" {{ old('is_active', $setting->is_active) == 0 ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>
            </div>
            <div class="grid grid-cols-1 gap-4 mt-4">
                <div class="space-y-1.5">
                    <label class="block text-label-sm font-medium text-on-surface-variant">Store Scope <span class="inline-flex items-center cursor-help text-on-surface-variant relative group"><span class="material-symbols-outlined text-[18px]">info</span><span class="absolute right-0 top-full mt-2 w-80 bg-surface-container-highest rounded-lg shadow-lg border border-outline-variant p-4 text-body-xs text-on-surface-variant hidden group-hover:block z-50">Pilih toko mana saja yang akan mendapatkan harga khusus untuk diskon ini. Kosongkan untuk semua toko.</span></span></label>
                    <select name="scope_store_type" id="scopeStoreType" class="w-full px-3 py-2 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary/20 focus:outline-none bg-white" onchange="toggleStoreScopeSelect()">
                        <option value="0" {{ old('scope_store_type', $setting->scope_store_type ?? 0) == 0 ? 'selected' : '' }}>Semua Store</option>
                        <option value="1" {{ old('scope_store_type', $setting->scope_store_type ?? 0) == 1 ? 'selected' : '' }}>Berdasarkan Tier</option>
                        <option value="2" {{ old('scope_store_type', $setting->scope_store_type ?? 0) == 2 ? 'selected' : '' }}>Store Tertentu</option>
                        <option value="3" {{ old('scope_store_type', $setting->scope_store_type ?? 0) == 3 ? 'selected' : '' }}>Berdasarkan Channel Group</option>
                    </select>
                    <div class="mt-2 hidden" id="storeTierSelect">
                        <select name="scope_tier_id" class="w-full px-3 py-2 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary/20 focus:outline-none bg-white">
                            <option value="">Pilih Tier</option>
                            @foreach($tiers as $t)
                                <option value="{{ $t->id }}" {{ old('scope_tier_id', $setting->scope_store_id ?? '') == $t->id ? 'selected' : '' }}>{{ $t->name }} (Level {{ $t->level }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mt-2 hidden" id="storeSelect">
                        <select name="scope_store_id" class="w-full px-3 py-2 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary/20 focus:outline-none bg-white">
                            <option value="">Pilih Store</option>
                            @foreach($stores as $s)
                                <option value="{{ $s->id }}" {{ old('scope_store_id', $setting->scope_store_id ?? '') == $s->id ? 'selected' : '' }}>{{ $s->name }} ({{ $s->code }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mt-2 hidden" id="channelGroupSelect">
                        <select name="scope_channel_group_id" class="w-full px-3 py-2 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary/20 focus:outline-none bg-white">
                            <option value="">Pilih Channel Group</option>
                            @foreach($channelGroups as $g)
                                <option value="{{ $g->id }}" {{ old('scope_channel_group_id', $setting->scope_store_id ?? '') == $g->id ? 'selected' : '' }}>{{ $g->name }} ({{ $g->code }})</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
            <div class="flex items-center gap-2 mt-4">
                <input type="checkbox" name="is_featured" id="isFeatured" class="w-4 h-4 rounded border-outline-variant text-primary focus:ring-primary" value="1" {{ old('is_featured', $setting->is_featured) ? 'checked' : '' }}>
                <label for="isFeatured" class="text-label-sm font-medium text-on-surface-variant">Featured <span class="inline-flex items-center cursor-help text-on-surface-variant relative group"><span class="material-symbols-outlined text-[18px]">info</span><span class="absolute right-0 top-full mt-2 w-80 bg-surface-container-highest rounded-lg shadow-lg border border-outline-variant p-4 text-body-xs text-on-surface-variant hidden group-hover:block z-50">Tampilkan di posisi unggulan</span></span></label>
            </div>
        </div>

        <div class="rounded-xl shadow-sm border border-outline-variant/30 p-6 mb-6 hidden" id="volumeTierSection">
            <h3 class="font-headline-md text-headline-md text-on-surface mb-4">Volume Discount Tiers</h3>
            <p class="text-label-sm text-on-surface-variant mb-4">Atur diskon berdasarkan jumlah pembelian. Semakin banyak dibeli, semakin murah harganya.</p>
            <div id="volumeTierList" class="space-y-2">
                @if(isset($volumeTiers) && $volumeTiers->count() > 0)
                    @foreach($volumeTiers as $tier)
                        <div class="flex items-center gap-2 p-3 rounded-lg bg-surface-container-low border border-outline-variant/60" data-tier-index="{{ $loop->index }}">
                            <div class="flex items-center gap-2 flex-1">
                                <span class="text-label-sm text-on-surface-variant whitespace-nowrap">Min Qty:</span>
                                <input type="number" name="volume_tiers[{{ $loop->index }}][min_purchase]" class="w-20 px-2 py-1 border border-outline-variant rounded text-body-xs text-center" value="{{ $tier->min_purchase }}" placeholder="0" min="0">
                                <span class="text-label-sm text-on-surface-variant whitespace-nowrap">Diskon:</span>
                                <select name="volume_tiers[{{ $loop->index }}][discount_type]" class="px-2 py-1 border border-outline-variant rounded text-body-xs">
                                    <option value="1" {{ $tier->discount_type == 1 ? 'selected' : '' }}>%</option>
                                    <option value="2" {{ $tier->discount_type == 2 ? 'selected' : '' }}>Rp</option>
                                </select>
                                <input type="number" name="volume_tiers[{{ $loop->index }}][discount_value]" class="w-28 px-2 py-1 border border-outline-variant rounded text-body-xs text-right" value="{{ $tier->discount_value }}" placeholder="Nilai diskon" min="0">
                                <span class="text-label-xs text-on-surface-variant">→</span>
                                <span class="tier-result text-label-xs text-primary font-medium whitespace-nowrap">Harga setelah diskon</span>
                            </div>
                            <button type="button" class="text-on-surface-variant hover:text-danger transition-colors p-1" onclick="removeVolumeTier(this)">
                                <span class="material-symbols-outlined text-[18px]">delete</span>
                            </button>
                        </div>
                    @endforeach
                @endif
            </div>
            <button type="button" class="mt-3 px-4 py-2 bg-surface-container-high text-primary font-medium rounded-lg hover:bg-surface-variant transition-colors text-label-sm" onclick="addVolumeTier()">
                <span class="material-symbols-outlined text-[16px] align-middle">add</span> Tambah Tier
            </button>
        </div>

        <div class="rounded-xl shadow-sm border border-outline-variant/30 p-6 mb-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-headline-md text-headline-md text-on-surface">Products</h3>
                <div class="flex items-center gap-3">
                    <input type="text" id="productSearch" placeholder="Search products..." class="px-3 py-2 border border-outline-variant rounded-lg text-body-md focus:ring-1 focus:ring-primary focus:border-primary w-64">
                    <div class="relative">
                        <button type="button" class="w-8 h-8 flex items-center justify-center rounded-full bg-primary/10 text-primary hover:bg-primary/20 transition-colors" onclick="toggleInfo()">
                            <span class="material-symbols-outlined text-[18px]">info</span>
                        </button>
                        <div id="inputInfo" class="hidden absolute right-0 top-10 w-80 bg-surface-container-lowest border border-outline-variant rounded-xl shadow-lg p-4 z-50 text-body-sm">
                            <h4 class="font-headline-md text-headline-md text-on-surface mb-2">Cara Penginputan Harga</h4>
                            <ul class="space-y-2 text-on-surface-variant">
                                <li class="flex items-start gap-2">
                                    <span class="material-symbols-outlined text-[18px] text-primary mt-0.5">check_circle</span>
                                    <span>Masukkan harga satuan untuk setiap variant dalam Rupiah (tanpa titik atau koma)</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <span class="material-symbols-outlined text-[18px] text-primary mt-0.5">check_circle</span>
                                    <span>Harga akan otomatis terkena diskon sesuai setting di atas (Percentage atau Nominal)</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <span class="material-symbols-outlined text-[18px] text-primary mt-0.5">check_circle</span>
                                    <span>Contoh: masukkan <strong>150000</strong> untuk harga Rp 150.000</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <span class="material-symbols-outlined text-[18px] text-primary mt-0.5">check_circle</span>
                                    <span>Centang checkbox untuk menyertakan variant pada price setting ini</span>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
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
            <div id="productVariantGrid" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-5">
                @include('pages.promo.partials.product-cards')
            </div>

            <!-- Load More Container -->
            <div class="mt-8 flex justify-center {{ $products->hasMorePages() ? '' : 'hidden' }}" id="loadMoreContainer">
                <button type="button" id="loadMoreBtn" class="px-6 py-2.5 bg-surface-container-high text-primary font-medium rounded-lg hover:bg-surface-variant transition-all text-label-md flex items-center gap-2 shadow-sm border border-outline-variant/30">
                    <span class="material-symbols-outlined text-[18px]">expand_more</span> Load More Products
                </button>
            </div>
        </div>

        <div class="sticky bottom-0 border-t border-outline-variant flex justify-between items-center px-8 py-4 z-30 shadow-[0_-4px_10px_rgba(0,0,0,0.05)]">
            <div class="flex items-center gap-4">
                <span class="text-label-md font-label-md text-on-surface-variant">Selected: <span class="text-primary font-bold" id="selectedCount">0</span> variants</span>
            </div>
            <div class="flex gap-4">
                <a href="{{ route('price-settings.index') }}" class="px-8 py-3 border border-outline-variant text-primary font-bold rounded-lg hover:bg-surface-container transition-colors">Cancel</a>
                <button type="submit" class="px-10 py-3 bg-primary text-white rounded-lg font-label-md hover:opacity-90 transition-all shadow-sm">Update Price Setting</button>
            </div>
        </div>
    </form>
@endsection

@push('scripts')
<script>
// Persistent selected variants state
const selectedVariants = {
    @foreach($selectedVariantIds as $vId)
        "{{ $vId }}": "{{ $variantPricesFromPivot[$vId] ?? '' }}",
    @endforeach
};

function toggleInfo() {
    const info = document.getElementById('inputInfo');
    info.classList.toggle('hidden');
}

document.addEventListener('click', function(e) {
    const info = document.getElementById('inputInfo');
    if (!info) return;
    const btn = e.target.closest('button[onclick="toggleInfo()"]');
    if (!btn && !info.contains(e.target)) {
        info.classList.add('hidden');
    }
});

function getDiscountConfig() {
    const discountType = parseFloat(document.querySelector('select[name="discount_type"]')?.value) || 1;
    const discountValue = parseFloat(document.querySelector('input[name="discount_value"]')?.value) || 0;
    return { discountType, discountValue };
}

function calcDiscountedPrice(originalPrice, discountType, discountValue) {
    if (originalPrice <= 0 || discountValue <= 0) return originalPrice;
    let discounted = originalPrice;
    if (discountType == 1) {
        discounted = originalPrice - (originalPrice * discountValue / 100);
    } else if (discountType == 2) {
        discounted = originalPrice - discountValue;
        if (discounted < 0) discounted = 0;
    }
    return Math.round(discounted);
}

function parsePrice(str) {
    if (typeof str !== 'string') str = String(str);
    if (str.includes(',')) {
        let cleaned = str.replace(/\./g, '').replace(',', '.');
        return parseFloat(cleaned) || 0;
    }
    return parseFloat(str) || 0;
}

function updateDiscountInfo(input) {
    const row = input.closest('.variant-row');
    const discountEl = row?.querySelector('.discount-info');
    if (!discountEl) return;
    const currentPrice = parsePrice(input.value);
    const { discountType, discountValue } = getDiscountConfig();
    const discounted = calcDiscountedPrice(currentPrice, discountType, discountValue);
    const priceEl = discountEl.querySelector('.discount-text');
    if (priceEl) {
        priceEl.textContent = '→Rp' + discounted.toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }
}

function recalcAllDiscounts() {
    document.querySelectorAll('.variant-price-input').forEach(function(input) {
        updateDiscountInfo(input);
    });
}

function syncHiddenInputs() {
    const container = document.getElementById('hidden-inputs-container');
    if (!container) return;
    container.innerHTML = '';
    
    Object.keys(selectedVariants).forEach(variantId => {
        const price = selectedVariants[variantId];
        
        const idInput = document.createElement('input');
        idInput.type = 'hidden';
        idInput.name = 'variant_ids[]';
        idInput.value = variantId;
        
        const priceInput = document.createElement('input');
        priceInput.type = 'hidden';
        priceInput.name = `variant_prices[${variantId}]`;
        priceInput.value = price;
        
        container.appendChild(idInput);
        container.appendChild(priceInput);
    });
    
    const countBadge = document.getElementById('selectedCount');
    if (countBadge) {
        countBadge.textContent = Object.keys(selectedVariants).length;
    }
}

function restoreCheckedStates() {
    document.querySelectorAll('.variant-checkbox').forEach(cb => {
        const variantId = cb.value;
        const row = cb.closest('.variant-row');
        const priceInput = row?.querySelector('.variant-price-input');
        
        if (selectedVariants.hasOwnProperty(variantId)) {
            cb.checked = true;
            if (priceInput) {
                priceInput.value = selectedVariants[variantId];
                updateDiscountInfo(priceInput);
            }
        } else {
            cb.checked = false;
        }
        
        // Remove names from visible inputs to prevent duplicate/empty submits
        cb.removeAttribute('name');
        if (priceInput) {
            priceInput.removeAttribute('name');
        }
    });
}

function handleCheckboxChange(e) {
    const cb = e.target;
    const variantId = cb.value;
    const row = cb.closest('.variant-row');
    const priceInput = row?.querySelector('.variant-price-input');
    
    if (cb.checked) {
        selectedVariants[variantId] = priceInput ? priceInput.value : '';
    } else {
        delete selectedVariants[variantId];
    }
    syncHiddenInputs();
}

function handlePriceInput(e) {
    const input = e.target;
    const row = input.closest('.variant-row');
    const cb = row?.querySelector('.variant-checkbox');
    if (!cb) return;
    const variantId = cb.value;
    
    updateDiscountInfo(input);
    
    if (cb.checked) {
        selectedVariants[variantId] = input.value;
        syncHiddenInputs();
    }
}

function bindEventHandlers() {
    document.querySelectorAll('.variant-checkbox').forEach(cb => {
        cb.removeEventListener('change', handleCheckboxChange);
        cb.addEventListener('change', handleCheckboxChange);
    });
    
    document.querySelectorAll('.variant-price-input').forEach(input => {
        input.removeEventListener('input', handlePriceInput);
        input.addEventListener('input', handlePriceInput);
    });
}

// AJAX Loader & Search State
let currentPage = 1;
let currentSearch = '';
let currentCategory = 'all';

async function fetchProducts(append = false) {
    const loader = document.getElementById('page-loader');
    if (loader) loader.classList.remove('hidden');
    
    const url = new URL(window.location.href);
    url.searchParams.set('page', currentPage);
    url.searchParams.set('search', currentSearch);
    url.searchParams.set('category', currentCategory);
    
    try {
        const response = await fetch(url, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        });
        const data = await response.json();
        
        const grid = document.getElementById('productVariantGrid');
        if (append) {
            grid.insertAdjacentHTML('beforeend', data.html);
        } else {
            grid.innerHTML = data.html;
        }
        
        const loadMoreContainer = document.getElementById('loadMoreContainer');
        if (data.hasMore) {
            loadMoreContainer.classList.remove('hidden');
        } else {
            loadMoreContainer.classList.add('hidden');
        }
        
        restoreCheckedStates();
        bindEventHandlers();
        
    } catch (error) {
        console.error('Error fetching products:', error);
        if (typeof showToast === 'function') {
            showToast('error', 'Gagal memuat produk');
        }
    } finally {
        if (loader) loader.classList.add('hidden');
    }
}

// Load More Click
document.getElementById('loadMoreBtn').addEventListener('click', () => {
    currentPage++;
    fetchProducts(true);
});

// Category Filter Overrides
function selectParentCategory(slug) {
    document.querySelectorAll('.parent-cat-tab').forEach(btn => {
        btn.classList.remove('bg-primary', 'text-white');
        btn.classList.add('bg-surface-container-low', 'text-on-surface-variant');
    });
    const activeBtn = document.querySelector('.parent-cat-tab[data-category="' + slug + '"]');
    if (activeBtn) {
        activeBtn.classList.remove('bg-surface-container-low', 'text-on-surface-variant');
        activeBtn.classList.add('bg-primary', 'text-white');
    }

    document.querySelectorAll('.sub-cat-tab').forEach(btn => {
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

    currentPage = 1;
    currentCategory = slug;
    fetchProducts(false);
}

function selectSubCategory(parentSlug, subSlug) {
    document.querySelectorAll('.sub-cat-tab[data-parent="' + parentSlug + '"]').forEach(btn => {
        btn.classList.remove('bg-primary', 'text-white');
        btn.classList.add('bg-surface-container-low', 'text-on-surface-variant');
    });
    const activeBtn = document.querySelector('.sub-cat-tab[data-parent="' + parentSlug + '"][data-sub="' + subSlug + '"]');
    if (activeBtn) {
        activeBtn.classList.remove('bg-surface-container-low', 'text-on-surface-variant');
        activeBtn.classList.add('bg-primary', 'text-white');
    }

    currentPage = 1;
    currentCategory = subSlug === 'all' ? parentSlug : subSlug;
    fetchProducts(false);
}

// Search
let searchTimeout;
document.getElementById('productSearch').addEventListener('input', function(e) {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        currentPage = 1;
        currentSearch = e.target.value;
        fetchProducts(false);
    }, 300);
});

document.querySelectorAll('select[name="discount_type"], input[name="discount_value"]').forEach(function(el) {
    el.addEventListener('change', recalcAllDiscounts);
    el.addEventListener('input', recalcAllDiscounts);
});

function syncDiscountInput() {
    const type = document.querySelector('select[name="discount_type"]').value;
    const input = document.getElementById('discountValueInput');
    if (type == '1') {
        input.max = 100;
        input.placeholder = '0-100';
        input.step = '1';
    } else {
        input.max = '';
        input.placeholder = 'Nominal (Rp)';
        input.step = '0.01';
    }
}

document.querySelector('select[name="discount_type"]').addEventListener('change', function() {
    syncDiscountInput();
    recalcAllDiscounts();
});

function toggleVolumeTierSection() {
    const type = document.querySelector('select[name="type"]').value;
    const section = document.getElementById('volumeTierSection');
    if (type == '2') {
        section.classList.remove('hidden');
    } else {
        section.classList.add('hidden');
    }
}

function addVolumeTier(minPurchase, discountType, discountValue) {
    const list = document.getElementById('volumeTierList');
    const index = list.children.length;
    const row = document.createElement('div');
    row.className = 'flex items-center gap-2 p-3 rounded-lg bg-surface-container-low border border-outline-variant/60';
    row.setAttribute('data-tier-index', index);
    row.innerHTML = `
        <div class="flex items-center gap-2 flex-1">
            <span class="text-label-sm text-on-surface-variant whitespace-nowrap">Min Qty:</span>
            <input type="number" name="volume_tiers[${index}][min_purchase]" class="w-20 px-2 py-1 border border-outline-variant rounded text-body-xs text-center" value="${minPurchase || ''}" placeholder="0" min="0">
            <span class="text-label-sm text-on-surface-variant whitespace-nowrap">Diskon:</span>
            <select name="volume_tiers[${index}][discount_type]" class="px-2 py-1 border border-outline-variant rounded text-body-xs">
                <option value="1" ${discountType == '1' || !discountType ? 'selected' : ''}>%</option>
                <option value="2" ${discountType == '2' ? 'selected' : ''}>Rp</option>
            </select>
            <input type="number" name="volume_tiers[${index}][discount_value]" class="w-28 px-2 py-1 border border-outline-variant rounded text-body-xs text-right" value="${discountValue || ''}" placeholder="Nilai diskon" min="0">
            <span class="text-label-xs text-on-surface-variant">→</span>
            <span class="tier-result text-label-xs text-primary font-medium whitespace-nowrap">Harga setelah diskon</span>
        </div>
        <button type="button" class="text-on-surface-variant hover:text-danger transition-colors p-1" onclick="removeVolumeTier(this)">
            <span class="material-symbols-outlined text-[18px]">delete</span>
        </button>
    `;
    list.appendChild(row);
}

function removeVolumeTier(btn) {
    btn.closest('[data-tier-index]').remove();
}

document.querySelector('select[name="type"]').addEventListener('change', toggleVolumeTierSection);

function toggleStoreScopeSelect() {
    const type = document.getElementById('scopeStoreType').value;
    const tierDiv = document.getElementById('storeTierSelect');
    const storeDiv = document.getElementById('storeSelect');
    const channelDiv = document.getElementById('channelGroupSelect');
    
    tierDiv.classList.add('hidden');
    storeDiv.classList.add('hidden');
    channelDiv.classList.add('hidden');
    
    if (type == '1') {
        tierDiv.classList.remove('hidden');
    } else if (type == '2') {
        storeDiv.classList.remove('hidden');
    } else if (type == '3') {
        channelDiv.classList.remove('hidden');
    }
}

// Initial setup
syncDiscountInput();
toggleVolumeTierSection();
toggleStoreScopeSelect();

// Restore and bind on load
restoreCheckedStates();
bindEventHandlers();
syncHiddenInputs();
recalcAllDiscounts();
</script>
@endpush
