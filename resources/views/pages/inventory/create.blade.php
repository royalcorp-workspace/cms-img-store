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
            <a href="{{ route('inventory.index') }}" class="flex items-center gap-2 px-4 py-2 border border-outline-variant bg-white text-on-surface hover:bg-surface-container-high rounded-lg text-sm font-medium transition-all shadow-sm">
                <span class="material-symbols-outlined text-[18px]">arrow_back</span>
                Kembali ke Inventory
            </a>
        </div>
    </div>

    <!-- Standard Submenu Tabs -->
    @include('layouts.partials.inventory-submenu')

    <!-- Validation Errors -->
    @if(isset($errors) && $errors->any())
        <div class="mb-6 p-4 rounded-xl bg-danger/10 border border-danger/20 text-danger">
            <div class="font-semibold text-sm mb-1 flex items-center gap-2">
                <span class="material-symbols-outlined text-[18px]">error</span>
                Terdapat kesalahan pengisian data:
            </div>
            <ul class="list-disc list-inside text-xs space-y-1">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="bg-white rounded-xl shadow-sm border border-outline-variant/30 overflow-hidden w-full max-w-6xl">
        <div class="p-6 border-b border-outline-variant/30 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
            <div>
                <h2 class="text-base font-semibold text-on-surface">Form Alokasi Stok</h2>
                <p class="text-xs text-on-surface-variant mt-0.5">Pilih produk, lokasi gudang, dan channel toko untuk mengatur alokasi stok seluruh varian sekaligus.</p>
            </div>
            <span class="text-[11px] text-primary font-medium bg-primary/10 px-3 py-1 rounded-full self-start sm:self-auto">
                Formula: Available = On Stock - On Order - Outgoing
            </span>
        </div>

        <form action="{{ route('inventory.store') }}" method="POST" id="inventoryStoreForm" class="p-6 space-y-6">
            @csrf

            <!-- Primary Filters: Product, Warehouse, Store Channel (All with Select2) -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-5 p-5 bg-surface-container-lowest border border-outline-variant/40 rounded-xl">
                <!-- 1. Product Select2 -->
                <div>
                    <label class="block text-xs font-semibold text-on-surface mb-1.5">
                        Pilih Produk <span class="text-danger">*</span>
                    </label>
                    <select name="product_id" id="productSelect" required class="w-full select2-enable" data-placeholder="-- Cari atau Pilih Produk --">
                        <option value="">-- Cari atau Pilih Produk --</option>
                        @foreach($products as $prod)
                            <option value="{{ $prod->id }}" {{ old('product_id') == $prod->id ? 'selected' : '' }}>
                                {{ $prod->name }} ({{ $prod->code ?? '-' }})
                            </option>
                        @endforeach
                    </select>
                    <p class="text-[11px] text-on-surface-variant mt-1">Cari berdasarkan nama atau kode produk.</p>
                </div>

                <!-- 2. Warehouse Select2 -->
                <div>
                    <label class="block text-xs font-semibold text-on-surface mb-1.5">
                        Warehouse (Gudang) <span class="text-danger">*</span>
                    </label>
                    <select name="warehouse_id" id="warehouseSelect" required class="w-full select2-enable" data-placeholder="Pilih Warehouse...">
                        @foreach($warehouses as $wh)
                            <option value="{{ $wh->id }}" {{ (old('warehouse_id') == $wh->id || (!old('warehouse_id') && $defaultWarehouse && $defaultWarehouse->id == $wh->id)) ? 'selected' : '' }}>
                                {{ $wh->name }} ({{ $wh->code }})
                            </option>
                        @endforeach
                    </select>
                    <p class="text-[11px] text-on-surface-variant mt-1">Lokasi fisik penyimpanan produk.</p>
                </div>

                <!-- 3. Store Channel Select2 -->
                <div>
                    <label class="block text-xs font-semibold text-on-surface mb-1.5">
                        Store Channel <span class="text-danger">*</span>
                    </label>
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
                    <p class="text-[11px] text-on-surface-variant mt-1">Channel penjualan yang dialokasikan.</p>
                </div>
            </div>

            <!-- Placeholder when no product is selected yet -->
            <div id="noProductPlaceholder" class="p-10 text-center border-2 border-dashed border-outline-variant/60 rounded-xl bg-surface-container-lowest/50">
                <div class="w-12 h-12 rounded-full bg-primary/10 text-primary flex items-center justify-center mx-auto mb-3">
                    <span class="material-symbols-outlined text-2xl">inventory_2</span>
                </div>
                <h3 class="text-sm font-semibold text-on-surface">Pilih Produk Terlebih Dahulu</h3>
                <p class="text-xs text-on-surface-variant max-w-md mx-auto mt-1">
                    Pilih produk pada dropdown di atas untuk memunculkan seluruh varian produk dan menginput alokasi stok secara bersamaan.
                </p>
            </div>

            <!-- Dynamic Variants Stock Section -->
            <div id="variantsSection" class="hidden space-y-4">
                <!-- Section Header & Current Stock Sync -->
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pt-2">
                    <div>
                        <div class="flex items-center gap-2.5">
                            <h3 class="text-sm font-bold text-on-surface uppercase tracking-wider">Varian Produk & Alokasi Stok</h3>
                            <span id="variantCountBadge" class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-primary/10 text-primary">
                                0 Varian
                            </span>
                        </div>
                        <p class="text-xs text-on-surface-variant mt-0.5">
                            Atur stok untuk semua varian di bawah secara langsung. Centang varian yang ingin diperbarui.
                        </p>
                    </div>

                    <button type="button" id="btnSyncCurrentStock" class="flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-primary hover:bg-primary/5 rounded-lg border border-primary/20 transition-colors self-start sm:self-auto">
                        <span class="material-symbols-outlined text-[16px]">sync</span>
                        Muat Nilai Stok Terkini Gudang
                    </button>
                </div>

                <!-- Quick Bulk Set Toolbar ("Isi Cepat ke Semua Varian Terpilih") -->
                <div class="p-4 bg-amber-50/60 dark:bg-amber-950/20 border border-amber-200/60 dark:border-amber-800/30 rounded-xl">
                    <div class="flex items-center gap-2 mb-2">
                        <span class="material-symbols-outlined text-amber-600 dark:text-amber-400 text-[18px]">bolt</span>
                        <h4 class="text-xs font-bold text-amber-900 dark:text-amber-200 uppercase tracking-wide">
                            Aksi Cepat: Terapkan Nilai ke Semua Varian Terpilih
                        </h4>
                    </div>
                    <p class="text-[11px] text-amber-800/80 dark:text-amber-300/80 mb-3">
                        Ketik angka stok di bawah, lalu klik <strong>Terapkan</strong> untuk mengisi semua varian yang dicentang sekaligus.
                    </p>

                    <div class="flex flex-wrap items-center gap-3">
                        <div class="flex items-center gap-2">
                            <label class="text-[11px] font-semibold text-on-surface">On Stock:</label>
                            <input type="number" id="bulkOnStock" min="0" placeholder="0" class="w-24 h-9 px-2.5 border border-outline-variant rounded-lg text-xs font-bold bg-white focus:ring-2 focus:ring-primary/20 focus:border-primary focus:outline-none">
                        </div>

                        <div class="flex items-center gap-2">
                            <label class="text-[11px] font-semibold text-blue-700">Incoming:</label>
                            <input type="number" id="bulkIncoming" min="0" placeholder="0" class="w-24 h-9 px-2.5 border border-blue-200 rounded-lg text-xs font-bold bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 focus:outline-none">
                        </div>

                        <div class="flex items-center gap-2">
                            <label class="text-[11px] font-semibold text-amber-700">On Order:</label>
                            <input type="number" id="bulkOnOrder" min="0" placeholder="0" class="w-24 h-9 px-2.5 border border-amber-200 rounded-lg text-xs font-bold bg-white focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 focus:outline-none">
                        </div>

                        <div class="flex items-center gap-2">
                            <label class="text-[11px] font-semibold text-purple-700">Outgoing:</label>
                            <input type="number" id="bulkOutgoing" min="0" placeholder="0" class="w-24 h-9 px-2.5 border border-purple-200 rounded-lg text-xs font-bold bg-white focus:ring-2 focus:ring-purple-500/20 focus:border-purple-500 focus:outline-none">
                        </div>

                        <button type="button" id="btnApplyBulk" class="px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white font-semibold text-xs rounded-lg shadow-sm transition-colors flex items-center gap-1.5">
                            <span class="material-symbols-outlined text-[16px]">done_all</span>
                            Terapkan ke Terpilih
                        </button>

                        <button type="button" id="btnResetBulk" class="px-3 py-2 text-xs font-medium text-on-surface-variant hover:text-on-surface hover:bg-surface-container-high rounded-lg transition-colors border border-outline-variant">
                            Reset Input Cepat
                        </button>
                    </div>
                </div>

                <!-- Interactive Variants Table -->
                <div class="border border-outline-variant/40 rounded-xl overflow-x-auto shadow-sm">
                    <table class="w-full text-left text-xs border-collapse">
                        <thead>
                            <tr class="bg-surface-container-low border-b border-outline-variant/40 text-on-surface-variant font-semibold">
                                <th class="py-3 px-4 w-12 text-center">
                                    <input type="checkbox" id="selectAllVariants" checked class="w-4 h-4 rounded border-outline-variant text-primary focus:ring-primary/20 cursor-pointer" title="Pilih Semua / Batal Pilih">
                                </th>
                                <th class="py-3 px-4 min-w-[200px]">Varian & SKU</th>
                                <th class="py-3 px-4 w-32 min-w-[120px]">
                                    <div class="flex items-center gap-1 font-bold text-on-surface">
                                        On Stock <span class="text-danger">*</span>
                                    </div>
                                    <span class="text-[10px] text-on-surface-variant font-normal block">Fisik di gudang</span>
                                </th>
                                <th class="py-3 px-4 w-32 min-w-[120px]">
                                    <div class="font-bold text-blue-700">Incoming</div>
                                    <span class="text-[10px] text-on-surface-variant font-normal block">PO Masuk</span>
                                </th>
                                <th class="py-3 px-4 w-32 min-w-[120px]">
                                    <div class="font-bold text-amber-700">On Order</div>
                                    <span class="text-[10px] text-on-surface-variant font-normal block">Dipesan</span>
                                </th>
                                <th class="py-3 px-4 w-32 min-w-[120px]">
                                    <div class="font-bold text-purple-700">Outgoing</div>
                                    <span class="text-[10px] text-on-surface-variant font-normal block">Pengiriman</span>
                                </th>
                                <th class="py-3 px-4 w-32 min-w-[120px] text-center">
                                    <div class="font-bold text-emerald-700">Available</div>
                                    <span class="text-[10px] text-on-surface-variant font-normal block">Siap Dijual</span>
                                </th>
                            </tr>
                        </thead>
                        <tbody id="variantsTableBody" class="divide-y divide-outline-variant/20 bg-white">
                            <!-- Populated dynamically via JavaScript -->
                        </tbody>
                    </table>
                </div>

                <!-- Live Summary Bar -->
                <div class="p-4 bg-surface-container-low border border-outline-variant/40 rounded-xl flex flex-wrap items-center justify-between gap-4">
                    <div class="flex items-center gap-2">
                        <span class="text-xs text-on-surface-variant">Ringkasan Alokasi:</span>
                        <span id="selectedVariantsCount" class="text-xs font-bold text-on-surface bg-white px-2.5 py-1 rounded-md border border-outline-variant/40">
                            0 dari 0 Varian Dipilih
                        </span>
                    </div>

                    <div class="flex flex-wrap items-center gap-4 text-xs font-medium">
                        <div>
                            <span class="text-on-surface-variant">Total On Stock:</span>
                            <span id="totalOnStock" class="font-bold text-on-surface ml-1">0</span>
                        </div>
                        <div>
                            <span class="text-blue-700">Total Incoming:</span>
                            <span id="totalIncoming" class="font-bold text-blue-700 ml-1">0</span>
                        </div>
                        <div>
                            <span class="text-amber-700">Total On Order:</span>
                            <span id="totalOnOrder" class="font-bold text-amber-700 ml-1">0</span>
                        </div>
                        <div>
                            <span class="text-purple-700">Total Outgoing:</span>
                            <span id="totalOutgoing" class="font-bold text-purple-700 ml-1">0</span>
                        </div>
                        <div class="bg-emerald-50 text-emerald-700 px-3 py-1 rounded-lg border border-emerald-200">
                            <span class="font-semibold">Total Available:</span>
                            <span id="totalAvailable" class="font-extrabold text-sm ml-1">0</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="border-t border-outline-variant/30 pt-5 flex items-center justify-end gap-3">
                <a href="{{ route('inventory.index') }}" class="px-5 py-2.5 border border-outline-variant text-on-surface hover:bg-surface-container-high rounded-lg text-sm font-medium transition-colors">
                    Batal
                </a>
                <button type="submit" id="btnSubmitStock" class="px-6 py-2.5 bg-primary text-white hover:bg-primary/90 rounded-lg text-sm font-medium shadow-sm transition-colors flex items-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed">
                    <span class="material-symbols-outlined text-[18px]">save</span>
                    <span id="btnSubmitText">Simpan Stok</span>
                </button>
            </div>
        </form>
    </div>

    @push('scripts')
    <script>
        const productsData = @json($productsData);
        const oldVariants = @json(old('variants', []));
        const oldProductId = @json(old('product_id'));

        let currentProduct = null;

        function getExistingInventory(variant, warehouseId, channelId) {
            if (!variant.inventories || !variant.inventories.length) return null;
            return variant.inventories.find(inv => {
                const matchWh = !warehouseId || String(inv.warehouse_id) === String(warehouseId);
                const matchCh = !channelId || String(inv.store_channel_id) === String(channelId);
                return matchWh && matchCh;
            }) || null;
        }

        function calculateRowAvailable(row) {
            const onStock = parseInt(row.querySelector('.input-on-stock')?.value) || 0;
            const onOrder = parseInt(row.querySelector('.input-on-order')?.value) || 0;
            const outgoing = parseInt(row.querySelector('.input-outgoing')?.value) || 0;
            const available = Math.max(0, onStock - onOrder - outgoing);

            const badge = row.querySelector('.available-badge');
            if (badge) {
                badge.textContent = available;
                if (available > 0) {
                    badge.className = 'available-badge font-bold text-xs px-2.5 py-1 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200 inline-block min-w-[36px] text-center';
                } else {
                    badge.className = 'available-badge font-bold text-xs px-2.5 py-1 rounded-full bg-surface-container-high text-on-surface-variant border border-outline-variant/40 inline-block min-w-[36px] text-center';
                }
            }
            updateTotals();
        }

        function updateTotals() {
            let selectedCount = 0;
            let sumOnStock = 0;
            let sumIncoming = 0;
            let sumOnOrder = 0;
            let sumOutgoing = 0;
            let sumAvailable = 0;

            const rows = document.querySelectorAll('#variantsTableBody tr');
            const totalRows = rows.length;

            rows.forEach(row => {
                const cb = row.querySelector('.variant-cb');
                if (cb && cb.checked) {
                    selectedCount++;
                    const onStock = parseInt(row.querySelector('.input-on-stock')?.value) || 0;
                    const incoming = parseInt(row.querySelector('.input-incoming')?.value) || 0;
                    const onOrder = parseInt(row.querySelector('.input-on-order')?.value) || 0;
                    const outgoing = parseInt(row.querySelector('.input-outgoing')?.value) || 0;
                    const available = Math.max(0, onStock - onOrder - outgoing);

                    sumOnStock += onStock;
                    sumIncoming += incoming;
                    sumOnOrder += onOrder;
                    sumOutgoing += outgoing;
                    sumAvailable += available;
                }
            });

            document.getElementById('selectedVariantsCount').textContent = `${selectedCount} dari ${totalRows} Varian Dipilih`;
            document.getElementById('totalOnStock').textContent = sumOnStock;
            document.getElementById('totalIncoming').textContent = sumIncoming;
            document.getElementById('totalOnOrder').textContent = sumOnOrder;
            document.getElementById('totalOutgoing').textContent = sumOutgoing;
            document.getElementById('totalAvailable').textContent = sumAvailable;

            const submitBtn = document.getElementById('btnSubmitStock');
            const submitText = document.getElementById('btnSubmitText');
            if (submitBtn && submitText) {
                if (selectedCount === 0) {
                    submitBtn.disabled = true;
                    submitText.textContent = 'Pilih minimal 1 varian';
                } else {
                    submitBtn.disabled = false;
                    submitText.textContent = `Simpan Stok (${selectedCount} Varian)`;
                }
            }

            const selectAllCb = document.getElementById('selectAllVariants');
            if (selectAllCb) {
                selectAllCb.checked = (selectedCount === totalRows && totalRows > 0);
                selectAllCb.indeterminate = (selectedCount > 0 && selectedCount < totalRows);
            }
        }

        function renderVariantsTable(productId) {
            const tableBody = document.getElementById('variantsTableBody');
            const placeholder = document.getElementById('noProductPlaceholder');
            const section = document.getElementById('variantsSection');

            if (!productId) {
                placeholder.classList.remove('hidden');
                section.classList.add('hidden');
                tableBody.innerHTML = '';
                currentProduct = null;
                return;
            }

            currentProduct = productsData.find(p => String(p.id) === String(productId));

            if (!currentProduct || !currentProduct.variants || currentProduct.variants.length === 0) {
                placeholder.classList.add('hidden');
                section.classList.remove('hidden');
                tableBody.innerHTML = `
                    <tr>
                        <td colspan="7" class="py-8 text-center text-on-surface-variant">
                            <span class="material-symbols-outlined text-3xl mb-1 text-outline">info</span>
                            <p class="font-medium text-xs">Produk ini belum memiliki varian.</p>
                            <p class="text-[11px] text-on-surface-variant mt-0.5">Silakan tambahkan varian terlebih dahulu melalui menu Produk.</p>
                        </td>
                    </tr>
                `;
                document.getElementById('variantCountBadge').textContent = '0 Varian';
                updateTotals();
                return;
            }

            placeholder.classList.add('hidden');
            section.classList.remove('hidden');

            const warehouseId = document.getElementById('warehouseSelect').value;
            const channelId = document.getElementById('storeChannelSelect').value;

            document.getElementById('variantCountBadge').textContent = `${currentProduct.variants.length} Varian`;

            let rowsHtml = '';
            currentProduct.variants.forEach((v, index) => {
                // Check if old value exists
                let oldVal = null;
                if (Array.isArray(oldVariants)) {
                    oldVal = oldVariants.find(ov => String(ov.product_variant_id) === String(v.id));
                }

                const existingInv = getExistingInventory(v, warehouseId, channelId);

                const currentStockLabel = existingInv 
                    ? `Stok saat ini di gudang: <strong>${existingInv.on_stock}</strong> unit (Avail: ${existingInv.available})`
                    : `Belum ada stok di gudang ini`;

                const initialOnStock = oldVal ? (oldVal.on_stock ?? 0) : (existingInv ? existingInv.on_stock : 0);
                const initialIncoming = oldVal ? (oldVal.incoming ?? 0) : (existingInv ? existingInv.incoming : 0);
                const initialOnOrder = oldVal ? (oldVal.on_order ?? 0) : (existingInv ? existingInv.on_order : 0);
                const initialOutgoing = oldVal ? (oldVal.outgoing ?? 0) : (existingInv ? existingInv.outgoing : 0);

                const available = Math.max(0, initialOnStock - initialOnOrder - initialOutgoing);
                const availClass = available > 0 
                    ? 'bg-emerald-50 text-emerald-700 border-emerald-200' 
                    : 'bg-surface-container-high text-on-surface-variant border-outline-variant/40';

                rowsHtml += `
                    <tr class="variant-row hover:bg-surface-container-lowest transition-colors border-b border-outline-variant/20" data-variant-id="${v.id}" data-index="${index}">
                        <td class="py-3 px-4 text-center">
                            <input type="checkbox" class="variant-cb w-4 h-4 rounded border-outline-variant text-primary focus:ring-primary/20 cursor-pointer" checked>
                            <input type="hidden" name="variants[${index}][product_variant_id]" value="${v.id}" class="input-variant-id">
                        </td>
                        <td class="py-3 px-4">
                            <div class="font-bold text-on-surface text-xs">${v.variant_name || 'Standar'}</div>
                            <div class="flex items-center gap-1.5 mt-0.5">
                                <span class="font-mono text-[10px] bg-surface-container-high px-1.5 py-0.5 rounded text-on-surface-variant">
                                    SKU: ${v.sku || '-'}
                                </span>
                            </div>
                            <div class="text-[10px] text-on-surface-variant mt-1 current-stock-info">
                                ${currentStockLabel}
                            </div>
                        </td>
                        <td class="py-3 px-4">
                            <input type="number" name="variants[${index}][on_stock]" value="${initialOnStock}" min="0" required class="input-on-stock w-full h-9 px-2.5 border border-outline-variant rounded-lg text-xs font-bold text-on-surface bg-white focus:ring-2 focus:ring-primary/20 focus:border-primary focus:outline-none">
                        </td>
                        <td class="py-3 px-4">
                            <input type="number" name="variants[${index}][incoming]" value="${initialIncoming}" min="0" class="input-incoming w-full h-9 px-2.5 border border-blue-200 rounded-lg text-xs font-bold text-on-surface bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 focus:outline-none">
                        </td>
                        <td class="py-3 px-4">
                            <input type="number" name="variants[${index}][on_order]" value="${initialOnOrder}" min="0" class="input-on-order w-full h-9 px-2.5 border border-amber-200 rounded-lg text-xs font-bold text-on-surface bg-white focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 focus:outline-none">
                        </td>
                        <td class="py-3 px-4">
                            <input type="number" name="variants[${index}][outgoing]" value="${initialOutgoing}" min="0" class="input-outgoing w-full h-9 px-2.5 border border-purple-200 rounded-lg text-xs font-bold text-on-surface bg-white focus:ring-2 focus:ring-purple-500/20 focus:border-purple-500 focus:outline-none">
                        </td>
                        <td class="py-3 px-4 text-center">
                            <span class="available-badge font-bold text-xs px-2.5 py-1 rounded-full ${availClass} border inline-block min-w-[36px] text-center">
                                ${available}
                            </span>
                        </td>
                    </tr>
                `;
            });

            tableBody.innerHTML = rowsHtml;

            // Attach input event listeners for real-time calculations
            tableBody.querySelectorAll('tr').forEach(row => {
                row.querySelectorAll('input[type="number"]').forEach(input => {
                    input.addEventListener('input', () => calculateRowAvailable(row));
                });

                const cb = row.querySelector('.variant-cb');
                if (cb) {
                    cb.addEventListener('change', () => {
                        const isChecked = cb.checked;
                        row.classList.toggle('opacity-40', !isChecked);
                        row.classList.toggle('bg-surface-container-low/50', !isChecked);
                        row.querySelectorAll('input:not(.variant-cb)').forEach(inp => {
                            inp.disabled = !isChecked;
                        });
                        updateTotals();
                    });
                }
            });

            updateTotals();
        }

        // Apply bulk values to all checked variants
        function applyBulkValues() {
            const bulkOnStock = document.getElementById('bulkOnStock').value;
            const bulkIncoming = document.getElementById('bulkIncoming').value;
            const bulkOnOrder = document.getElementById('bulkOnOrder').value;
            const bulkOutgoing = document.getElementById('bulkOutgoing').value;

            const rows = document.querySelectorAll('#variantsTableBody tr');
            let appliedCount = 0;

            rows.forEach(row => {
                const cb = row.querySelector('.variant-cb');
                if (cb && cb.checked) {
                    if (bulkOnStock !== '') {
                        row.querySelector('.input-on-stock').value = parseInt(bulkOnStock) || 0;
                    }
                    if (bulkIncoming !== '') {
                        row.querySelector('.input-incoming').value = parseInt(bulkIncoming) || 0;
                    }
                    if (bulkOnOrder !== '') {
                        row.querySelector('.input-on-order').value = parseInt(bulkOnOrder) || 0;
                    }
                    if (bulkOutgoing !== '') {
                        row.querySelector('.input-outgoing').value = parseInt(bulkOutgoing) || 0;
                    }
                    calculateRowAvailable(row);
                    appliedCount++;
                }
            });

            if (appliedCount > 0 && typeof showToast === 'function') {
                showToast('success', `Berhasil menerapkan nilai cepat ke ${appliedCount} varian terpilih.`);
            }
        }

        // Reset bulk input fields
        function resetBulkInputs() {
            document.getElementById('bulkOnStock').value = '';
            document.getElementById('bulkIncoming').value = '';
            document.getElementById('bulkOnOrder').value = '';
            document.getElementById('bulkOutgoing').value = '';
        }

        // Sync values with existing warehouse stock
        function syncWithWarehouseStock() {
            if (!currentProduct || !currentProduct.variants) return;

            const warehouseId = document.getElementById('warehouseSelect').value;
            const channelId = document.getElementById('storeChannelSelect').value;

            const rows = document.querySelectorAll('#variantsTableBody tr');
            rows.forEach(row => {
                const vId = row.dataset.variantId;
                const variant = currentProduct.variants.find(v => String(v.id) === String(vId));
                if (!variant) return;

                const inv = getExistingInventory(variant, warehouseId, channelId);
                const infoDiv = row.querySelector('.current-stock-info');
                if (infoDiv) {
                    infoDiv.innerHTML = inv 
                        ? `Stok saat ini di gudang: <strong>${inv.on_stock}</strong> unit (Avail: ${inv.available})`
                        : `Belum ada stok di gudang ini`;
                }

                if (inv) {
                    row.querySelector('.input-on-stock').value = inv.on_stock;
                    row.querySelector('.input-incoming').value = inv.incoming;
                    row.querySelector('.input-on-order').value = inv.on_order;
                    row.querySelector('.input-outgoing').value = inv.outgoing;
                } else {
                    row.querySelector('.input-on-stock').value = 0;
                    row.querySelector('.input-incoming').value = 0;
                    row.querySelector('.input-on-order').value = 0;
                    row.querySelector('.input-outgoing').value = 0;
                }
                calculateRowAvailable(row);
            });

            if (typeof showToast === 'function') {
                showToast('info', 'Nilai stok diselaraskan dengan data terkini di gudang & channel terpilih.');
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            const productSelect = $('#productSelect');
            const warehouseSelect = $('#warehouseSelect');
            const storeChannelSelect = $('#storeChannelSelect');
            const selectAllCb = document.getElementById('selectAllVariants');
            const btnApplyBulk = document.getElementById('btnApplyBulk');
            const btnResetBulk = document.getElementById('btnResetBulk');
            const btnSync = document.getElementById('btnSyncCurrentStock');

            // Select2 events
            productSelect.on('change select2:select', function() {
                const prodId = $(this).val();
                renderVariantsTable(prodId);
            });

            warehouseSelect.on('change select2:select', function() {
                if (currentProduct) {
                    syncWithWarehouseStock();
                }
            });

            storeChannelSelect.on('change select2:select', function() {
                if (currentProduct) {
                    syncWithWarehouseStock();
                }
            });

            // Master checkbox Select / Deselect All
            if (selectAllCb) {
                selectAllCb.addEventListener('change', function() {
                    const checked = this.checked;
                    const rows = document.querySelectorAll('#variantsTableBody tr');
                    rows.forEach(row => {
                        const cb = row.querySelector('.variant-cb');
                        if (cb) {
                            cb.checked = checked;
                            row.classList.toggle('opacity-40', !checked);
                            row.classList.toggle('bg-surface-container-low/50', !checked);
                            row.querySelectorAll('input:not(.variant-cb)').forEach(inp => {
                                inp.disabled = !checked;
                            });
                        }
                    });
                    updateTotals();
                });
            }

            // Bulk action buttons
            if (btnApplyBulk) {
                btnApplyBulk.addEventListener('click', applyBulkValues);
            }
            if (btnResetBulk) {
                btnResetBulk.addEventListener('click', resetBulkInputs);
            }
            if (btnSync) {
                btnSync.addEventListener('click', syncWithWarehouseStock);
            }

            // Form submit validation
            document.getElementById('inventoryStoreForm').addEventListener('submit', function(e) {
                const selectedCbs = document.querySelectorAll('#variantsTableBody .variant-cb:checked');
                if (selectedCbs.length === 0) {
                    e.preventDefault();
                    if (typeof showErrorPopup === 'function') {
                        showErrorPopup('Pilih minimal 1 varian produk untuk disimpan.', 'Validasi Form');
                    } else {
                        alert('Pilih minimal 1 varian produk untuk disimpan.');
                    }
                }
            });

            // If old product_id or already selected on load
            const initProdId = oldProductId || productSelect.val();
            if (initProdId) {
                productSelect.val(initProdId).trigger('change');
            }
        });
    </script>
    @endpush
@endsection
