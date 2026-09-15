@extends('layouts.app')

@section('title', 'Tambah Stok Inventory')

@section('content')
    <!-- Header Section -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="font-headline-lg text-headline-lg text-on-surface">Tambah Stok Inventory</h1>
            <nav class="flex items-center gap-2 text-label-sm text-on-surface-variant mt-1 font-medium">
                <a href="{{ route('dashboard') }}" class="hover:text-primary transition-colors">eCommerce</a>
                <span class="material-symbols-outlined text-[14px]">chevron_right</span>
                <a href="{{ route('inventory.index') }}" class="hover:text-primary transition-colors">Inventory</a>
                <span class="material-symbols-outlined text-[14px]">chevron_right</span>
                <span class="text-on-surface">Tambah Stok</span>
            </nav>
        </div>
        <div>
            <a href="{{ route('inventory.index') }}" class="flex items-center gap-2 px-4 py-2 border border-outline-variant bg-white text-on-surface hover:bg-surface-container-high rounded-lg text-sm font-medium transition-all">
                <span class="material-symbols-outlined text-[18px]">arrow_back</span>
                Kembali ke Inventory
            </a>
        </div>
    </div>

    <!-- Standard Submenu Tabs -->
    @include('layouts.partials.inventory-submenu')

    <!-- Validation Errors -->
    @if($errors->any())
        <div class="mb-6 p-4 rounded-lg bg-danger/10 border border-danger/20 text-danger">
            <div class="font-medium text-sm mb-1">Terdapat kesalahan pengisian data:</div>
            <ul class="list-disc list-inside text-xs space-y-1">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="bg-white rounded-xl shadow-sm border border-outline-variant/30 overflow-hidden max-w-4xl">
        <div class="p-6 border-b border-outline-variant/30">
            <h2 class="text-base font-semibold text-on-surface">Form Alokasi Stok</h2>
            <p class="text-xs text-on-surface-variant mt-0.5">Pilih produk, varian, lokasi warehouse, dan channel toko untuk mengatur alokasi stok.</p>
        </div>

        <form action="{{ route('inventory.store') }}" method="POST" class="p-6 space-y-6">
            @csrf

            <!-- Product & Variant Selection -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-on-surface mb-1.5">Pilih Produk <span class="text-danger">*</span></label>
                    <select name="product_id" id="productSelect" required class="w-full h-11 px-3 border border-outline-variant rounded-lg text-sm bg-white focus:ring-2 focus:ring-primary/20 focus:border-primary focus:outline-none" onchange="loadVariants()">
                        <option value="">-- Pilih Produk --</option>
                        @foreach($products as $prod)
                            <option value="{{ $prod->id }}" data-variants="{{ json_encode($prod->variants) }}" {{ old('product_id') == $prod->id ? 'selected' : '' }}>
                                {{ $prod->name }} ({{ $prod->code ?? '-' }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-on-surface mb-1.5">Pilih Varian <span class="text-danger">*</span></label>
                    <select name="product_variant_id" id="variantSelect" required class="w-full h-11 px-3 border border-outline-variant rounded-lg text-sm bg-white focus:ring-2 focus:ring-primary/20 focus:border-primary focus:outline-none">
                        <option value="">-- Pilih Produk Terlebih Dahulu --</option>
                    </select>
                </div>
            </div>

            <!-- Warehouse & Store Channel Selection -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-on-surface mb-1.5">Warehouse (Gudang) <span class="text-danger">*</span></label>
                    <select name="warehouse_id" required class="w-full h-11 px-3 border border-outline-variant rounded-lg text-sm bg-white focus:ring-2 focus:ring-primary/20 focus:border-primary focus:outline-none">
                        @foreach($warehouses as $wh)
                            <option value="{{ $wh->id }}" {{ (old('warehouse_id') == $wh->id || (!old('warehouse_id') && $defaultWarehouse && $defaultWarehouse->id == $wh->id)) ? 'selected' : '' }}>
                                {{ $wh->name }} ({{ $wh->code }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-on-surface mb-1.5">Store Channel <span class="text-danger">*</span></label>
                    <select name="store_channel_id" id="storeChannelSelect" required class="w-full select2-channel" data-placeholder="Pilih atau cari Store Channel..." data-ajax-url="{{ route('inventory.channels.search') }}">
                        <option value="">-- Cari atau Pilih Store Channel --</option>
                        @foreach($channels as $ch)
                            <option value="{{ $ch->id }}" 
                                    data-store="{{ $ch->store->name ?? '-' }}" 
                                    data-code="{{ $ch->code }}"
                                    {{ (old('store_channel_id') == $ch->id || (!old('store_channel_id') && $defaultChannel && $defaultChannel->id == $ch->id)) ? 'selected' : '' }}>
                                {{ $ch->name }} (Toko: {{ $ch->store->name ?? '-' }})
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <!-- 5 Inventory Statuses Pattern -->
            <div class="border-t border-outline-variant/30 pt-4">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-xs font-semibold uppercase tracking-wider text-on-surface-variant">Pola Status Stok Inventory</h3>
                    <span class="text-[11px] text-primary font-medium bg-primary/10 px-2 py-0.5 rounded">
                        Available = On Stock - On Order - Outgoing
                    </span>
                </div>

                <div class="grid grid-cols-2 md:grid-cols-5 gap-3.5">
                    <!-- 1. On Stock -->
                    <div class="p-3 bg-surface-container-low border border-outline-variant/40 rounded-xl">
                        <label class="block text-[11px] font-bold text-on-surface uppercase mb-1">On Stock *</label>
                        <input type="number" name="on_stock" id="inputOnStock" min="0" value="{{ old('on_stock', 0) }}" required class="w-full h-10 px-3 border border-outline-variant rounded-lg text-base font-bold text-on-surface bg-white focus:ring-2 focus:ring-primary/20 focus:border-primary focus:outline-none" oninput="calculateAvailable()">
                        <p class="text-[10px] text-on-surface-variant mt-1">Stok fisik di gudang.</p>
                    </div>

                    <!-- 2. Incoming -->
                    <div class="p-3 bg-blue-500/5 border border-blue-500/20 rounded-xl">
                        <label class="block text-[11px] font-bold text-blue-700 uppercase mb-1">Incoming</label>
                        <input type="number" name="incoming" id="inputIncoming" min="0" value="{{ old('incoming', 0) }}" class="w-full h-10 px-3 border border-blue-300 rounded-lg text-base font-bold text-on-surface bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 focus:outline-none">
                        <p class="text-[10px] text-on-surface-variant mt-1">Stok masuk / PO.</p>
                    </div>

                    <!-- 3. On Order -->
                    <div class="p-3 bg-amber-500/5 border border-amber-500/20 rounded-xl">
                        <label class="block text-[11px] font-bold text-amber-700 uppercase mb-1">On Order</label>
                        <input type="number" name="on_order" id="inputOnOrder" min="0" value="{{ old('on_order', 0) }}" class="w-full h-10 px-3 border border-amber-300 rounded-lg text-base font-bold text-on-surface bg-white focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 focus:outline-none" oninput="calculateAvailable()">
                        <p class="text-[10px] text-on-surface-variant mt-1">Dipesan pelanggan.</p>
                    </div>

                    <!-- 4. Outgoing -->
                    <div class="p-3 bg-purple-500/5 border border-purple-500/20 rounded-xl">
                        <label class="block text-[11px] font-bold text-purple-700 uppercase mb-1">Outgoing</label>
                        <input type="number" name="outgoing" id="inputOutgoing" min="0" value="{{ old('outgoing', 0) }}" class="w-full h-10 px-3 border border-purple-300 rounded-lg text-base font-bold text-on-surface bg-white focus:ring-2 focus:ring-purple-500/20 focus:border-purple-500 focus:outline-none" oninput="calculateAvailable()">
                        <p class="text-[10px] text-on-surface-variant mt-1">Dalam pengiriman.</p>
                    </div>

                    <!-- 5. Available (Auto calculated) -->
                    <div class="p-3 bg-success/10 border-2 border-success/30 rounded-xl flex flex-col justify-between">
                        <div>
                            <label class="block text-[11px] font-extrabold text-success uppercase mb-1">Available (Otomatis)</label>
                            <div id="previewAvailable" class="text-2xl font-extrabold text-success mt-1">0</div>
                        </div>
                        <p class="text-[10px] text-success/80 mt-1">Sisa siap dijual.</p>
                    </div>
                </div>
            </div>

            <div class="border-t border-outline-variant/30 pt-4 flex items-center justify-end gap-3">
                <a href="{{ route('inventory.index') }}" class="px-5 py-2.5 border border-outline-variant text-on-surface hover:bg-surface-container-high rounded-lg text-sm font-medium transition-colors">
                    Batal
                </a>
                <button type="submit" class="px-6 py-2.5 bg-primary text-white hover:bg-primary/90 rounded-lg text-sm font-medium shadow-sm transition-colors flex items-center gap-2">
                    <span class="material-symbols-outlined text-[18px]">save</span>
                    Simpan Stok
                </button>
            </div>
        </form>
    </div>

    @push('scripts')
    <script>
        function calculateAvailable() {
            const onStock = parseInt(document.getElementById('inputOnStock').value) || 0;
            const onOrder = parseInt(document.getElementById('inputOnOrder').value) || 0;
            const outgoing = parseInt(document.getElementById('inputOutgoing').value) || 0;
            const available = Math.max(0, onStock - onOrder - outgoing);
            document.getElementById('previewAvailable').innerText = available;
        }

        function loadVariants() {
            const prodSelect = document.getElementById('productSelect');
            const varSelect = document.getElementById('variantSelect');
            const selectedOpt = prodSelect.options[prodSelect.selectedIndex];

            varSelect.innerHTML = '<option value="">-- Pilih Varian --</option>';

            if (!selectedOpt || !selectedOpt.dataset.variants) {
                return;
            }

            try {
                const variants = JSON.parse(selectedOpt.dataset.variants);
                const oldVariantId = "{{ old('product_variant_id') }}";

                variants.forEach(v => {
                    const opt = document.createElement('option');
                    opt.value = v.id;
                    opt.textContent = `${v.variant_name || 'Standar'} (SKU: ${v.sku || '-'})`;
                    if (oldVariantId && oldVariantId === v.id) {
                        opt.selected = true;
                    }
                    varSelect.appendChild(opt);
                });
            } catch (e) {
                console.error('Error parsing variants:', e);
            }
        }

        document.addEventListener('DOMContentLoaded', () => {
            if (document.getElementById('productSelect').value) {
                loadVariants();
            }
            calculateAvailable();
        });
    </script>
    @endpush
@endsection
