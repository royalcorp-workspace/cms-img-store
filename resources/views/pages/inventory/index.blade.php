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
            <a href="{{ route('inventory.import.form') }}" class="flex items-center gap-2 px-4 py-2 bg-surface-container text-on-surface hover:bg-surface-container-high rounded-lg text-sm font-medium transition-all border border-outline-variant/30">
                <span class="material-symbols-outlined text-[18px]">cloud_upload</span>
                Import Incoming
            </a>
            <a href="{{ route('inventory.create') }}" class="flex items-center gap-2 px-5 py-2 bg-primary text-white font-label-md hover:opacity-90 transition-all rounded-lg shadow-sm">
                <span class="material-symbols-outlined text-[18px]">add</span>
                Tambah Stok
            </a>
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

        <!-- Table Content -->
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-surface-gray/50 border-b border-outline-variant/30">
                        <th class="px-5 py-4 text-xs font-semibold text-on-surface-variant uppercase tracking-wider">Produk & Varian</th>
                        <th class="px-4 py-4 text-xs font-semibold text-on-surface-variant uppercase tracking-wider">Warehouse</th>
                        <th class="px-4 py-4 text-xs font-semibold text-on-surface-variant uppercase tracking-wider">Store Channel</th>
                        <th class="px-3 py-4 text-xs font-semibold text-on-surface-variant uppercase tracking-wider text-center">On Stock</th>
                        <th class="px-3 py-4 text-xs font-semibold text-on-surface-variant uppercase tracking-wider text-center">Incoming</th>
                        <th class="px-3 py-4 text-xs font-semibold text-on-surface-variant uppercase tracking-wider text-center">On Order</th>
                        <th class="px-3 py-4 text-xs font-semibold text-on-surface-variant uppercase tracking-wider text-center">Outgoing</th>
                        <th class="px-4 py-4 text-xs font-semibold text-on-surface-variant uppercase tracking-wider text-center">Available</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-outline-variant/20">
                    @forelse($inventories as $inv)
                        @php
                            $variant = $inv->variant;
                            $product = $inv->product;
                            $image = $variant?->image ?: ($product?->thumbnail_url ?: ($product?->images->first()?->url ?? ''));
                        @endphp
                        <tr class="hover:bg-surface-container/30 transition-colors group">
                            <!-- Product & Variant -->
                            <td class="px-5 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-12 h-12 bg-surface-gray rounded-lg overflow-hidden flex-shrink-0 border border-outline-variant/30">
                                        @if($image)
                                            <img class="w-full h-full object-cover" src="{{ $image }}" alt="{{ $product?->name ?? 'Product' }}">
                                        @else
                                            <div class="w-full h-full flex items-center justify-center text-on-surface-variant bg-surface-container">
                                                <span class="material-symbols-outlined text-[22px]">inventory_2</span>
                                            </div>
                                        @endif
                                    </div>
                                    <div>
                                        <div class="font-semibold text-sm text-on-surface">
                                            {{ $product?->name ?? 'Unknown Product' }}
                                        </div>
                                        <div class="mt-1 flex items-center gap-1.5 flex-wrap">
                                            @if($variant?->variant_name)
                                                <span class="text-[11px] bg-primary/10 text-primary font-medium px-2 py-0.5 rounded border border-primary/20">
                                                    {{ $variant->variant_name }}
                                                </span>
                                            @endif
                                            @if($variant?->sku)
                                                <span class="text-[10px] bg-surface-container-low text-on-surface-variant px-1.5 py-0.5 rounded border border-outline-variant/30 font-mono">
                                                    SKU: {{ $variant->sku }}
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </td>

                            <!-- Warehouse -->
                            <td class="px-4 py-4">
                                <div class="text-xs font-medium text-on-surface">
                                    {{ $inv->warehouse?->name ?? 'Gudang Utama' }}
                                </div>
                                <div class="text-[10px] font-mono text-on-surface-variant mt-0.5">
                                    {{ $inv->warehouse?->code ?? '-' }}
                                </div>
                            </td>

                            <!-- Store Channel -->
                            <td class="px-4 py-4">
                                <div class="text-xs font-medium text-on-surface flex items-center gap-1">
                                    <span class="material-symbols-outlined text-[15px] text-primary">storefront</span>
                                    <span>{{ $inv->channel?->name ?? 'Web IMG' }}</span>
                                </div>
                                <div class="text-[10px] text-on-surface-variant mt-0.5">
                                    <span>Toko:</span>
                                    <span class="font-medium text-secondary">{{ $inv->store?->name ?? 'Online Retail' }}</span>
                                </div>
                            </td>

                            <!-- On Stock -->
                            <td class="px-3 py-4 text-center">
                                <span class="inline-flex items-center px-2 py-1 rounded-md text-xs font-bold text-on-surface bg-surface-container-low border border-outline-variant/30">
                                    {{ number_format($inv->on_stock ?? 0) }}
                                </span>
                            </td>

                            <!-- Incoming -->
                            <td class="px-3 py-4 text-center">
                                <span class="inline-flex items-center px-2 py-1 rounded-md text-xs font-semibold {{ $inv->incoming > 0 ? 'bg-blue-50 text-blue-700 border border-blue-200' : 'text-on-surface-variant bg-surface-container-low' }}">
                                    {{ number_format($inv->incoming) }}
                                </span>
                            </td>

                            <!-- On Order -->
                            <td class="px-3 py-4 text-center">
                                <span class="inline-flex items-center px-2 py-1 rounded-md text-xs font-semibold {{ $inv->on_order > 0 ? 'bg-amber-50 text-amber-700 border border-amber-200' : 'text-on-surface-variant bg-surface-container-low' }}">
                                    {{ number_format($inv->on_order) }}
                                </span>
                            </td>

                            <!-- Outgoing -->
                            <td class="px-3 py-4 text-center">
                                <span class="inline-flex items-center px-2 py-1 rounded-md text-xs font-semibold {{ $inv->outgoing > 0 ? 'bg-purple-50 text-purple-700 border border-purple-200' : 'text-on-surface-variant bg-surface-container-low' }}">
                                    {{ number_format($inv->outgoing) }}
                                </span>
                            </td>

                            <!-- Available (On Stock - On Order - Outgoing) -->
                            <td class="px-4 py-4 text-center">
                                @if($inv->available > 0)
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-extrabold bg-success/15 text-success border border-success/30 shadow-xs">
                                        {{ number_format($inv->available) }}
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-1 rounded-md text-xs font-bold bg-danger/10 text-danger border border-danger/20">
                                        Habis (0)
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-12 text-center text-on-surface-variant">
                                <div class="flex flex-col items-center justify-center">
                                    <span class="material-symbols-outlined text-[48px] text-outline-variant mb-2">inventory_2</span>
                                    <p class="text-sm font-semibold text-on-surface">Tidak ada data inventory ditemukan</p>
                                    <p class="text-xs text-on-surface-variant mt-1">Coba sesuaikan kata kunci pencarian atau filter.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($inventories->hasPages())
            <div class="p-4 border-t border-outline-variant/30 bg-surface-container-lowest">
                {{ $inventories->links() }}
            </div>
        @endif
    </div>
@endsection
