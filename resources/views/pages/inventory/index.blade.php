@extends('layouts.app')

@section('title', 'Inventory')

@section('content')
    <!-- Header Section -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="font-headline-lg text-headline-lg text-on-surface">Inventory</h1>
            <nav class="flex items-center gap-2 text-label-sm text-on-surface-variant mt-1 font-medium">
                <a href="{{ route('dashboard') }}" class="hover:text-primary transition-colors">eCommerce</a>
                <span class="material-symbols-outlined text-[14px]">chevron_right</span>
                <span class="text-on-surface">Inventory</span>
            </nav>
        </div>
        <div class="flex items-center gap-3">
            @can('inventory.import.form')
                <a href="{{ route('inventory.import.form') }}" class="flex items-center gap-2 px-4 py-2 bg-surface-container text-on-surface hover:bg-surface-container-high rounded-lg text-sm font-medium transition-all border border-outline-variant/30">
                    <span class="material-symbols-outlined text-[18px]">cloud_upload</span>
                    Import Incoming
                </a>
            @endcan
            @can('inventory.create')
                <a href="{{ route('inventory.create') }}" class="flex items-center gap-2 px-5 py-2 bg-primary text-white font-label-md hover:opacity-90 transition-all rounded-lg shadow-sm">
                    <span class="material-symbols-outlined text-[18px]">add</span>
                    Tambah Stok
                </a>
            @endcan
        </div>
    </div>

    <!-- Standard Submenu Tabs -->
    @include('layouts.partials.inventory-submenu')

    <!-- Flash Alerts -->
    @if(session('success'))
        <div class="mb-6 p-4 rounded-lg bg-success/10 border border-success/20 text-success flex items-center justify-between">
            <div class="flex items-center gap-2">
                <span class="material-symbols-outlined text-[20px]">check_circle</span>
                <span class="text-sm font-medium">{{ session('success') }}</span>
            </div>
            <button type="button" onclick="this.parentElement.remove()" class="text-success hover:opacity-75">
                <span class="material-symbols-outlined text-[18px]">close</span>
            </button>
        </div>
    @endif
    @if(session('error'))
        <div class="mb-6 p-4 rounded-lg bg-danger/10 border border-danger/20 text-danger flex items-center justify-between">
            <div class="flex items-center gap-2">
                <span class="material-symbols-outlined text-[20px]">error</span>
                <span class="text-sm font-medium">{{ session('error') }}</span>
            </div>
            <button type="button" onclick="this.parentElement.remove()" class="text-danger hover:opacity-75">
                <span class="material-symbols-outlined text-[18px]">close</span>
            </button>
        </div>
    @endif

    <!-- 5 Metric KPI Cards (On Stock, Incoming, On Order, Outgoing, Available) -->
    <div class="grid grid-cols-2 md:grid-cols-5 gap-3.5 mb-6">
        <!-- 1. On Stock -->
        <div class="bg-white p-4 rounded-xl shadow-sm border border-outline-variant/30">
            <div class="flex items-center justify-between">
                <span class="text-on-surface-variant text-[11px] font-semibold uppercase tracking-wider">On Stock</span>
                <span class="p-1.5 rounded-lg bg-slate-500/10 text-slate-700 material-symbols-outlined text-[18px]">inventory</span>
            </div>
            <div class="mt-2 flex items-baseline gap-1.5">
                <span class="text-2xl font-bold text-on-surface">{{ number_format($stats['total_on_stock'] ?? 0) }}</span>
                <span class="text-[11px] text-on-surface-variant">unit</span>
            </div>
            <p class="text-[10px] text-on-surface-variant mt-1">Total stok fisik gudang</p>
        </div>

        <!-- 2. Incoming -->
        <div class="bg-white p-4 rounded-xl shadow-sm border border-outline-variant/30">
            <div class="flex items-center justify-between">
                <span class="text-on-surface-variant text-[11px] font-semibold uppercase tracking-wider">Incoming</span>
                <span class="p-1.5 rounded-lg bg-blue-500/10 text-blue-600 material-symbols-outlined text-[18px]">move_to_inbox</span>
            </div>
            <div class="mt-2 flex items-baseline gap-1.5">
                <span class="text-2xl font-bold text-blue-700">{{ number_format($stats['total_incoming'] ?? 0) }}</span>
                <span class="text-[11px] text-on-surface-variant">unit</span>
            </div>
            <p class="text-[10px] text-on-surface-variant mt-1">Stok masuk / PO supplier</p>
        </div>

        <!-- 3. On Order -->
        <div class="bg-white p-4 rounded-xl shadow-sm border border-outline-variant/30">
            <div class="flex items-center justify-between">
                <span class="text-on-surface-variant text-[11px] font-semibold uppercase tracking-wider">On Order</span>
                <span class="p-1.5 rounded-lg bg-amber-500/10 text-amber-600 material-symbols-outlined text-[18px]">shopping_cart_checkout</span>
            </div>
            <div class="mt-2 flex items-baseline gap-1.5">
                <span class="text-2xl font-bold text-amber-700">{{ number_format($stats['total_on_order'] ?? 0) }}</span>
                <span class="text-[11px] text-on-surface-variant">unit</span>
            </div>
            <p class="text-[10px] text-on-surface-variant mt-1">Dipesan pelanggan web</p>
        </div>

        <!-- 4. Outgoing -->
        <div class="bg-white p-4 rounded-xl shadow-sm border border-outline-variant/30">
            <div class="flex items-center justify-between">
                <span class="text-on-surface-variant text-[11px] font-semibold uppercase tracking-wider">Outgoing</span>
                <span class="p-1.5 rounded-lg bg-purple-500/10 text-purple-600 material-symbols-outlined text-[18px]">outbox</span>
            </div>
            <div class="mt-2 flex items-baseline gap-1.5">
                <span class="text-2xl font-bold text-purple-700">{{ number_format($stats['total_outgoing'] ?? 0) }}</span>
                <span class="text-[11px] text-on-surface-variant">unit</span>
            </div>
            <p class="text-[10px] text-on-surface-variant mt-1">Dalam proses ekspedisi</p>
        </div>

        <!-- 5. Available -->
        <div class="bg-white p-4 rounded-xl shadow-sm border-2 border-success/40 bg-success/5">
            <div class="flex items-center justify-between">
                <span class="text-success text-[11px] font-bold uppercase tracking-wider">Available</span>
                <span class="p-1.5 rounded-lg bg-success/15 text-success material-symbols-outlined text-[18px]">verified</span>
            </div>
            <div class="mt-2 flex items-baseline gap-1.5">
                <span class="text-2xl font-extrabold text-success">{{ number_format($stats['total_available'] ?? 0) }}</span>
                <span class="text-[11px] text-success/80 font-medium">unit</span>
            </div>
            <p class="text-[10px] text-success/80 mt-1 font-medium">Sisa siap dijual langsung</p>
        </div>
    </div>

    <!-- Inventory Table Card -->
    <div class="bg-white rounded-lg shadow-sm border border-outline-variant/30 overflow-hidden mb-8">
        <!-- Filter Header -->
        <div class="p-4 border-b border-outline-variant/30 bg-surface-container-lowest">
            <form method="GET" action="{{ route('inventory.index') }}" class="flex flex-col lg:flex-row lg:items-center gap-3">
                <div class="flex-1 relative min-w-[240px]">
                    <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-on-surface-variant text-[18px]">search</span>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama produk, varian, atau SKU..." class="w-full h-10 pl-9 pr-3 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary/20 focus:border-primary focus:outline-none text-sm bg-white transition-all">
                </div>

                <div class="flex flex-wrap sm:flex-nowrap items-center gap-3">
                    <!-- Warehouse Filter -->
                    <div class="w-full sm:w-48">
                        <select name="warehouse_id" class="w-full h-10 px-3 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary/20 focus:border-primary focus:outline-none text-sm bg-white text-on-surface cursor-pointer transition-all" onchange="this.form.submit()">
                            <option value="">Semua Warehouse</option>
                            @foreach($warehouses as $wh)
                                <option value="{{ $wh->id }}" {{ request('warehouse_id') == $wh->id ? 'selected' : '' }}>{{ $wh->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Store Channel Filter -->
                    <div class="w-full sm:w-56">
                        <select name="store_channel_id" class="w-full select2-channel" data-placeholder="Semua Channel" onchange="this.form.submit()">
                            <option value="">Semua Channel</option>
                            @foreach($channels as $ch)
                                <option value="{{ $ch->id }}" 
                                        data-store="{{ $ch->store->name ?? '-' }}" 
                                        data-code="{{ $ch->code }}"
                                        {{ request('store_channel_id') == $ch->id ? 'selected' : '' }}>
                                    {{ $ch->name }} ({{ $ch->store->name ?? '-' }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Status Filter -->
                    <div class="w-full sm:w-44">
                        <select name="status" class="w-full h-10 px-3 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary/20 focus:border-primary focus:outline-none text-sm bg-white text-on-surface cursor-pointer transition-all" onchange="this.form.submit()">
                            <option value="">Semua Status</option>
                            <option value="available" {{ request('status') == 'available' ? 'selected' : '' }}>Tersedia (>0)</option>
                            <option value="out_of_stock" {{ request('status') == 'out_of_stock' ? 'selected' : '' }}>Stok Habis (0)</option>
                            <option value="on_stock" {{ request('status') == 'on_stock' ? 'selected' : '' }}>Ada On Stock</option>
                            <option value="incoming" {{ request('status') == 'incoming' ? 'selected' : '' }}>Ada Incoming</option>
                            <option value="on_order" {{ request('status') == 'on_order' ? 'selected' : '' }}>Ada On Order</option>
                            <option value="outgoing" {{ request('status') == 'outgoing' ? 'selected' : '' }}>Ada Outgoing</option>
                        </select>
                    </div>

                    <div class="flex items-center gap-2 shrink-0">
                        <button type="submit" class="h-10 px-4 bg-primary text-white hover:bg-primary/90 rounded-lg text-sm font-medium transition-colors shadow-sm inline-flex items-center gap-1.5">
                            <span class="material-symbols-outlined text-[18px]">filter_list</span>
                            <span>Filter</span>
                        </button>
                        @if(request()->hasAny(['search', 'warehouse_id', 'store_channel_id', 'status']))
                            <a href="{{ route('inventory.index') }}" class="h-10 px-3 bg-danger/10 text-danger hover:bg-danger/20 rounded-lg text-sm font-medium transition-colors inline-flex items-center gap-1" title="Reset Filter">
                                <span class="material-symbols-outlined text-[18px]">restart_alt</span>
                                <span class="hidden sm:inline">Reset</span>
                            </a>
                        @endif
                    </div>
                </div>
            </form>
        </div>

        <!-- Segregated Products & Variants List -->
        <div class="divide-y divide-outline-variant/30">
            @forelse($products as $product)
                @php
                    $pImg = $product->thumbnail_url ?: ($product->images->first()?->url ?? '');
                    $totalProdVariants = $product->variants->count();
                    $totalProdOnStock = 0;
                    $totalProdIncoming = 0;
                    $totalProdOnOrder = 0;
                    $totalProdOutgoing = 0;
                    $totalProdAvailable = 0;

                    foreach ($product->variants as $v) {
                        $vInv = $v->inventories->first();
                        $onStk = $vInv ? (int)$vInv->on_stock : 0;
                        $inc = $vInv ? (int)$vInv->incoming : 0;
                        $onOrd = $vInv ? (int)$vInv->on_order : 0;
                        $outg = $vInv ? (int)$vInv->outgoing : 0;
                        $avail = $vInv ? (int)$vInv->available : max(0, $onStk - $onOrd);

                        $totalProdOnStock += $onStk;
                        $totalProdIncoming += $inc;
                        $totalProdOnOrder += $onOrd;
                        $totalProdOutgoing += $outg;
                        $totalProdAvailable += $avail;
                    }
                @endphp
                <div class="product-inventory-group border-b border-outline-variant/30 last:border-b-0 bg-white" id="product-group-{{ $product->id }}">
                    <!-- Product Header Bar (Segregasi Produk) -->
                    <div class="p-4 bg-slate-50/70 hover:bg-slate-100/70 transition-colors flex flex-col md:flex-row md:items-center justify-between gap-4 border-l-4 border-l-primary">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 min-w-[40px] max-w-[40px] min-h-[40px] max-h-[40px] rounded-lg bg-surface-container-low border border-outline-variant/30 overflow-hidden flex-shrink-0 shadow-2xs flex items-center justify-center">
                                @if($pImg)
                                    <img src="{{ $pImg }}" alt="{{ $product->name }}" class="w-full h-full object-cover">
                                @else
                                    <span class="material-symbols-outlined text-outline-variant text-[20px]">inventory_2</span>
                                @endif
                            </div>
                            <div>
                                <div class="flex items-center gap-2 flex-wrap">
                                    <h3 class="text-sm font-bold text-on-surface">{{ $product->name }}</h3>
                                    @if($product->code)
                                        <span class="text-[10px] font-mono font-bold bg-white text-on-surface-variant px-2 py-0.5 rounded border border-outline-variant/40">
                                            {{ $product->code }}
                                        </span>
                                    @endif
                                    @if($product->category)
                                        <span class="text-[10px] bg-primary/10 text-primary font-bold px-2 py-0.5 rounded-full">
                                            {{ $product->category->name }}
                                        </span>
                                    @endif
                                    @if($product->brand)
                                        <span class="text-[10px] bg-secondary/10 text-secondary font-semibold px-2 py-0.5 rounded-full">
                                            {{ $product->brand->name }}
                                        </span>
                                    @endif
                                </div>
                                <div class="text-xs text-on-surface-variant mt-1 flex items-center gap-2">
                                    <span class="font-medium text-slate-600">{{ $totalProdVariants }} Varian</span>
                                    <span>•</span>
                                    <span class="text-[11px]">Total Fisik: <strong class="text-on-surface prod-total-onstock">{{ number_format($totalProdOnStock) }}</strong> unit</span>
                                    <span>•</span>
                                    <span class="text-[11px]">Total Siap Jual: <strong class="text-success prod-total-available">{{ number_format($totalProdAvailable) }}</strong> unit</span>
                                </div>
                            </div>
                        </div>

                        <!-- Right Actions for Product Header -->
                        <div class="flex items-center gap-3 shrink-0">
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-extrabold {{ $totalProdAvailable > 0 ? 'bg-success/15 text-success border border-success/30' : 'bg-danger/10 text-danger border border-danger/20' }}">
                                <span class="w-2 h-2 rounded-full {{ $totalProdAvailable > 0 ? 'bg-success' : 'bg-danger' }}"></span>
                                <span>{{ $totalProdAvailable > 0 ? 'Tersedia (' . number_format($totalProdAvailable) . ' unit)' : 'Stok Habis' }}</span>
                            </span>
                            <button type="button" onclick="toggleProductVariants('{{ $product->id }}')" class="p-1.5 hover:bg-white rounded-lg text-on-surface-variant hover:text-on-surface border border-outline-variant/30 transition-all" title="Buka / Tutup Varian">
                                <span class="material-symbols-outlined text-[20px] transition-transform duration-200" id="chevron-{{ $product->id }}">expand_less</span>
                            </button>
                        </div>
                    </div>

                    <!-- Variants Table for this Product (Tampilan Varian & Direct Edit Stock) -->
                    <div id="variants-container-{{ $product->id }}" class="overflow-x-auto border-t border-outline-variant/20">
                        <table class="w-full text-left border-collapse text-xs">
                            <thead>
                                <tr class="bg-surface-container-low/60 border-b border-outline-variant/20 text-on-surface-variant text-[11px] font-bold uppercase tracking-wider">
                                    <th class="py-2.5 px-4 min-w-[220px]">Varian & SKU</th>
                                    <th class="py-2.5 px-3 min-w-[150px]">Lokasi Gudang</th>
                                    <th class="py-2.5 px-3 text-center min-w-[100px] bg-slate-100/70 text-slate-800">
                                        On Stock
                                    </th>
                                    <th class="py-2.5 px-3 text-center min-w-[120px] bg-blue-50/70 text-blue-900 border-x border-blue-200/60">
                                        Incoming (Input Stok)
                                    </th>
                                    <th class="py-2.5 px-2.5 text-center min-w-[75px]">On Order</th>
                                    <th class="py-2.5 px-2.5 text-center min-w-[75px]">Outgoing</th>
                                    <th class="py-2.5 px-3 text-center min-w-[95px] bg-success/5 text-success">Available</th>
                                    <th class="py-2.5 px-3 text-center min-w-[85px]">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-outline-variant/15">
                                @forelse($product->variants as $variant)
                                    @php
                                        $inventory = $variant->inventories->first();
                                        $onStockVal = $inventory ? (int)$inventory->on_stock : 0;
                                        $incomingVal = $inventory ? (int)$inventory->incoming : 0;
                                        $onOrderVal = $inventory ? (int)$inventory->on_order : 0;
                                        $outgoingVal = $inventory ? (int)$inventory->outgoing : 0;
                                        // Available: on stock - on order
                                        $availableVal = $inventory ? (int)$inventory->available : max(0, $onStockVal - $onOrderVal);
                                        $whName = $inventory?->warehouse?->name ?? ($defaultWarehouse?->name ?? 'Gudang Utama');
                                        $whCode = $inventory?->warehouse?->code ?? ($defaultWarehouse?->code ?? 'GD-JKT01');
                                        $chName = $inventory?->channel?->name ?? ($defaultChannel?->name ?? 'Web IMG');
                                    @endphp
                                    <tr class="hover:bg-slate-50/70 transition-colors variant-row" id="variant-row-{{ $variant->id }}" data-variant-id="{{ $variant->id }}" data-product-id="{{ $product->id }}">
                                        <!-- Variant Name & SKU -->
                                        <td class="py-3 px-4">
                                            <div class="font-bold text-on-surface text-xs flex items-center gap-1.5">
                                                <span class="material-symbols-outlined text-[15px] text-primary">subdirectory_arrow_right</span>
                                                <span>{{ $variant->variant_name }}</span>
                                            </div>
                                            <div class="mt-0.5 ml-5 flex items-center gap-2 flex-wrap">
                                                @if($variant->sku)
                                                    <span class="font-mono text-[10px] text-on-surface-variant bg-slate-100 px-1.5 py-0.5 rounded border border-outline-variant/20">
                                                        SKU: {{ $variant->sku }}
                                                    </span>
                                                @endif
                                                @if(!empty($variant->attributes) && is_array($variant->attributes))
                                                    @foreach($variant->attributes as $k => $vAttr)
                                                        @if(!in_array($k, ['width', 'length', 'height', 'weight', 'image', 'image_url', '_completeness_title']) && is_string($vAttr))
                                                            <span class="text-[10px] text-slate-500">{{ $k }}: {{ $vAttr }}</span>
                                                        @endif
                                                    @endforeach
                                                @endif
                                            </div>
                                        </td>

                                        <!-- Location & Channel -->
                                        <td class="py-3 px-3">
                                            <div class="text-xs font-semibold text-on-surface flex items-center gap-1">
                                                <span class="material-symbols-outlined text-[14px] text-slate-500">warehouse</span>
                                                <span>{{ $whName }}</span>
                                            </div>
                                            <div class="text-[10px] text-on-surface-variant mt-0.5 flex items-center gap-1">
                                                <span class="material-symbols-outlined text-[13px] text-primary">storefront</span>
                                                <span>{{ $chName }}</span>
                                            </div>
                                        </td>

                                        <!-- On Stock: on stock + incoming terakhir - outgoing -->
                                        <td class="py-2.5 px-3 text-center bg-slate-50/50">
                                            <div class="inline-flex flex-col items-center">
                                                <span id="onstock-display-{{ $variant->id }}" class="font-mono font-bold text-xs text-on-surface">
                                                    {{ number_format($onStockVal) }}
                                                </span>
                                                <span id="onstock-calc-{{ $variant->id }}" class="text-[10px] font-bold text-blue-600 hidden"></span>
                                            </div>
                                        </td>

                                        <!-- DIRECT EDITABLE INCOMING (HANYA INCOMING YANG EDITABLE, NILAI TERAKHIR TAMPIL BUKAN 0) -->
                                        <td class="py-2.5 px-3 text-center bg-blue-50/30 border-x border-blue-200/50">
                                            <div class="inline-flex items-center justify-center">
                                                <input 
                                                    type="number" 
                                                    min="0" 
                                                    step="1"
                                                    placeholder="0"
                                                    value="{{ $incomingVal }}" 
                                                    id="incoming-input-{{ $variant->id }}"
                                                    data-variant-id="{{ $variant->id }}"
                                                    data-product-id="{{ $product->id }}"
                                                    data-initial-onstock="{{ $onStockVal }}"
                                                    data-initial-incoming="{{ $incomingVal }}"
                                                    data-outgoing="{{ $outgoingVal }}"
                                                    data-on-order="{{ $onOrderVal }}"
                                                    oninput="handleIncomingInput('{{ $variant->id }}')"
                                                    onkeydown="if(event.key==='Enter') { event.preventDefault(); quickSaveIncoming('{{ $variant->id }}'); }"
                                                    class="incoming-edit-input w-20 px-2 py-1 text-center font-mono font-bold text-xs bg-white border border-blue-300 rounded-lg text-blue-950 focus:ring-2 focus:ring-blue-400/30 focus:border-blue-500 focus:outline-none shadow-2xs transition-all"
                                                    title="Input incoming terakhir (tersimpan tidak 0). On Stock: on stock + incoming terakhir - outgoing"
                                                >
                                            </div>
                                        </td>

                                        <!-- On Order: Order yang sudah melakukan pembayaran -->
                                        <td class="py-3 px-2.5 text-center">
                                            <span class="inline-block px-2 py-0.5 rounded text-[11px] font-semibold {{ $onOrderVal > 0 ? 'bg-amber-50 text-amber-700 border border-amber-200' : 'text-slate-400' }}" title="Order yang sudah melakukan pembayaran">
                                                {{ number_format($onOrderVal) }}
                                            </span>
                                        </td>

                                        <!-- Outgoing: Data yang sudah dikirim / delivery -->
                                        <td class="py-3 px-2.5 text-center">
                                            <span class="inline-block px-2 py-0.5 rounded text-[11px] font-semibold {{ $outgoingVal > 0 ? 'bg-purple-50 text-purple-700 border border-purple-200' : 'text-slate-400' }}" title="Data yang sudah dikirim / delivery">
                                                {{ number_format($outgoingVal) }}
                                            </span>
                                        </td>

                                        <!-- Available: on stock - on order -->
                                        <td class="py-3 px-3 text-center bg-success/5">
                                            <span id="available-badge-{{ $variant->id }}" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-extrabold {{ $availableVal > 0 ? 'bg-success/15 text-success border border-success/30' : 'bg-danger/10 text-danger border border-danger/20' }}">
                                                {{ number_format($availableVal) }}
                                            </span>
                                        </td>

                                        <!-- Action: Quick Save Button -->
                                        <td class="py-3 px-3 text-center">
                                            <button 
                                                type="button" 
                                                id="btn-save-{{ $variant->id }}"
                                                onclick="quickSaveIncoming('{{ $variant->id }}')" 
                                                class="inline-flex items-center justify-center gap-1 px-2.5 py-1 bg-primary text-white rounded-lg text-xs font-bold hover:bg-primary/90 transition-all shadow-2xs"
                                                title="Simpan nilai incoming terakhir"
                                            >
                                                <span class="material-symbols-outlined text-[15px]">save</span>
                                                <span>Simpan</span>
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="py-4 px-4 text-center text-xs text-on-surface-variant italic">
                                            Produk ini belum memiliki varian.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            @empty
                <div class="p-12 text-center text-on-surface-variant bg-white rounded-lg">
                    <span class="material-symbols-outlined text-[48px] text-outline-variant mb-2">inventory_2</span>
                    <p class="text-sm font-semibold text-on-surface">Tidak ada data produk ditemukan</p>
                    <p class="text-xs text-on-surface-variant mt-1">Coba sesuaikan kata kunci pencarian atau filter.</p>
                </div>
            @endforelse
        </div>

        <!-- Pagination -->
        @if($products->hasPages())
            <div class="p-4 border-t border-outline-variant/30 bg-surface-container-lowest">
                {{ $products->links() }}
            </div>
        @endif
    </div>

    <!-- Toast Notification Container -->
    <div id="inventory-toast" class="fixed bottom-6 right-6 z-50 transform transition-all duration-300 translate-y-20 opacity-0 pointer-events-none flex items-center gap-2.5 px-4 py-3 rounded-xl shadow-lg text-sm font-bold text-white bg-slate-900">
        <span class="material-symbols-outlined text-[20px] text-success" id="toast-icon">check_circle</span>
        <span id="toast-message">Stok berhasil diperbarui</span>
    </div>
@endsection

@push('scripts')
<script>
    function toggleProductVariants(productId) {
        const container = document.getElementById(`variants-container-${productId}`);
        const chevron = document.getElementById(`chevron-${productId}`);
        if (!container || !chevron) return;

        if (container.classList.contains('hidden')) {
            container.classList.remove('hidden');
            chevron.style.transform = 'rotate(0deg)';
        } else {
            container.classList.add('hidden');
            chevron.style.transform = 'rotate(180deg)';
        }
    }

    function showInventoryToast(message, isSuccess = true) {
        const toast = document.getElementById('inventory-toast');
        const toastMessage = document.getElementById('toast-message');
        const toastIcon = document.getElementById('toast-icon');
        if (!toast) return;

        toastMessage.textContent = message;
        if (isSuccess) {
            toastIcon.textContent = 'check_circle';
            toastIcon.className = 'material-symbols-outlined text-[20px] text-success';
        } else {
            toastIcon.textContent = 'error';
            toastIcon.className = 'material-symbols-outlined text-[20px] text-danger';
        }

        toast.classList.remove('translate-y-20', 'opacity-0', 'pointer-events-none');
        toast.classList.add('translate-y-0', 'opacity-100');

        setTimeout(() => {
            toast.classList.remove('translate-y-0', 'opacity-100');
            toast.classList.add('translate-y-20', 'opacity-0', 'pointer-events-none');
        }, 3000);
    }

    window.handleIncomingInput = function(variantId) {
        const incInput = document.getElementById(`incoming-input-${variantId}`);
        const onstockDisplay = document.getElementById(`onstock-display-${variantId}`);
        const onstockCalc = document.getElementById(`onstock-calc-${variantId}`);
        const availBadge = document.getElementById(`available-badge-${variantId}`);
        if (!incInput) return;

        const baseOnStock = parseInt(incInput.dataset.initialOnstock || '0');
        const onOrder = parseInt(incInput.dataset.onOrder || '0');

        const currentInc = parseInt(incInput.value) || 0;

        // Logika bisnis: On Stock sekarang + incoming baru (misal on_stock 20 + incoming 50 = 70)
        const newOnStock = Math.max(0, baseOnStock + currentInc);
        // Available : on stock - on order
        const newAvailable = Math.max(0, newOnStock - onOrder);

        if (onstockDisplay) {
            onstockDisplay.textContent = newOnStock.toLocaleString('id-ID');
        }

        if (onstockCalc) {
            if (currentInc > 0) {
                onstockCalc.textContent = `(+${currentInc})`;
                onstockCalc.classList.remove('hidden');
            } else {
                onstockCalc.classList.add('hidden');
            }
        }

        if (availBadge) {
            availBadge.textContent = newAvailable.toLocaleString('id-ID');
            if (newAvailable > 0) {
                availBadge.className = 'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-extrabold bg-success/15 text-success border border-success/30';
            } else {
                availBadge.className = 'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-extrabold bg-danger/10 text-danger border border-danger/20';
            }
        }

        const productId = incInput.dataset.productId;
        updateProductTotals(productId);
    }

    window.updateProductTotals = function(productId) {
        const prodGroup = document.getElementById(`product-group-${productId}`);
        if (!prodGroup) return;

        let sumOnStock = 0;
        let sumAvail = 0;
        prodGroup.querySelectorAll('.variant-row').forEach(row => {
            const incInp = row.querySelector('.incoming-edit-input');
            if (!incInp) return;

            const base = parseInt(incInp.dataset.initialOnstock || '0');
            const ord = parseInt(incInp.dataset.onOrder || '0');

            const curInc = parseInt(incInp.value) || 0;
            const totalStock = Math.max(0, base + curInc);
            sumOnStock += totalStock;
            sumAvail += Math.max(0, totalStock - ord);
        });

        const totOnStockEl = prodGroup.querySelector('.prod-total-onstock');
        const totAvailEl = prodGroup.querySelector('.prod-total-available');
        if (totOnStockEl) totOnStockEl.textContent = sumOnStock.toLocaleString('id-ID');
        if (totAvailEl) totAvailEl.textContent = sumAvail.toLocaleString('id-ID');
    };

    window.quickSaveIncoming = function(variantId) {
        const incInput = document.getElementById(`incoming-input-${variantId}`);
        const btn = document.getElementById(`btn-save-${variantId}`);
        const onstockDisplay = document.getElementById(`onstock-display-${variantId}`);
        const onstockCalc = document.getElementById(`onstock-calc-${variantId}`);
        const availBadge = document.getElementById(`available-badge-${variantId}`);
        if (!incInput) return;

        const incomingVal = parseInt(incInput.value);

        if (isNaN(incomingVal) || incomingVal < 0) {
            showInventoryToast('Jumlah incoming harus berupa angka minimal 0', false);
            incInput.focus();
            return;
        }

        const productId = incInput.dataset.productId;

        // Loading state on button
        if (btn) {
            btn.disabled = true;
            btn.classList.add('opacity-70');
            btn.innerHTML = '<span class="material-symbols-outlined text-[15px] animate-spin">progress_activity</span>';
        }

        fetch('{{ route('inventory.quick-update') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                variant_id: variantId,
                product_id: productId,
                incoming: incomingVal,
                warehouse_id: '{{ request('warehouse_id') }}' || null,
                store_channel_id: '{{ request('store_channel_id') }}' || null
            })
        })
        .then(res => res.json())
        .then(data => {
            if (btn) {
                btn.disabled = false;
                btn.classList.remove('opacity-70');
                btn.innerHTML = '<span class="material-symbols-outlined text-[15px]">check</span><span>Tersimpan</span>';
                btn.classList.remove('bg-primary');
                btn.classList.add('bg-success');
                setTimeout(() => {
                    btn.innerHTML = '<span class="material-symbols-outlined text-[15px]">save</span><span>Simpan</span>';
                    btn.classList.remove('bg-success');
                    btn.classList.add('bg-primary');
                }, 2000);
            }

            if (data.success) {
                // Update datasets and keep inputs showing current saved values (do not reset to empty/0)
                incInput.dataset.initialOnstock = data.data.on_stock;
                incInput.dataset.initialIncoming = data.data.incoming;
                incInput.value = data.data.incoming;

                if (onstockDisplay) onstockDisplay.textContent = Number(data.data.on_stock).toLocaleString('id-ID');
                if (onstockCalc) onstockCalc.classList.add('hidden');
                if (availBadge) availBadge.textContent = Number(data.data.available).toLocaleString('id-ID');
                updateProductTotals(productId);

                showInventoryToast(data.message || `Stok berhasil disimpan. On Stock sekarang: ${data.data.on_stock}`, true);
            } else {
                showInventoryToast(data.message || 'Gagal memperbarui stok', false);
            }
        })
        .catch(err => {
            console.error(err);
            if (btn) {
                btn.disabled = false;
                btn.classList.remove('opacity-70');
                btn.innerHTML = '<span class="material-symbols-outlined text-[15px]">save</span><span>Simpan</span>';
            }
            showInventoryToast('Terjadi kesalahan saat menyimpan stok', false);
        });
    }
</script>
@endpush
