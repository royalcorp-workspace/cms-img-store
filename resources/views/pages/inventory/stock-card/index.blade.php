@extends('layouts.app')

@section('title', 'Kartu Stok (Stock Card)')

@section('content')
    <!-- Header Section -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="font-headline-lg text-headline-lg text-on-surface">Kartu Stok (Stock Card)</h1>
            <nav class="flex items-center gap-2 text-label-sm text-on-surface-variant mt-1 font-medium">
                <a href="{{ route('dashboard') }}" class="hover:text-primary transition-colors">eCommerce</a>
                <span class="material-symbols-outlined text-[14px]">chevron_right</span>
                <a href="{{ route('inventory.index') }}" class="hover:text-primary transition-colors">Inventory</a>
                <span class="material-symbols-outlined text-[14px]">chevron_right</span>
                <span class="text-on-surface">Kartu Stok</span>
            </nav>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('inventory.index') }}" class="flex items-center gap-2 px-4 py-2 border border-outline-variant bg-white text-on-surface hover:bg-surface-container-high rounded-lg text-sm font-medium transition-all shadow-2xs">
                <span class="material-symbols-outlined text-[18px]">inventory_2</span>
                Lihat Stok Inventory
            </a>
            <a href="{{ route('inventory.import.form') }}" class="flex items-center gap-2 px-4 py-2 bg-primary text-white hover:bg-primary/90 rounded-lg text-sm font-semibold transition-all shadow-sm">
                <span class="material-symbols-outlined text-[18px]">cloud_upload</span>
                Import Spreadsheet
            </a>
        </div>
    </div>

    <!-- Standard Submenu Tabs -->
    @include('layouts.partials.inventory-submenu')

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <!-- Total Mutasi -->
        <div class="bg-white rounded-xl p-5 border border-outline-variant/30 shadow-2xs flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold text-on-surface-variant uppercase tracking-wider">Total Mutasi</p>
                <h3 class="text-2xl font-black text-on-surface mt-1">{{ number_format($stats['total_mutations'] ?? 0) }}</h3>
                <p class="text-[11px] text-slate-500 mt-0.5">Catatan pergerakan stok</p>
            </div>
            <div class="w-12 h-12 rounded-xl bg-primary/10 flex items-center justify-center text-primary">
                <span class="material-symbols-outlined text-[26px]">swap_horiz</span>
            </div>
        </div>

        <!-- Total Masuk -->
        <div class="bg-white rounded-xl p-5 border border-outline-variant/30 shadow-2xs flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold text-on-surface-variant uppercase tracking-wider">Total Qty Masuk</p>
                <h3 class="text-2xl font-black text-success mt-1">+{{ number_format($stats['total_qty_in'] ?? 0) }}</h3>
                <p class="text-[11px] text-slate-500 mt-0.5">Incoming &amp; penambahan stok</p>
            </div>
            <div class="w-12 h-12 rounded-xl bg-success/10 flex items-center justify-center text-success">
                <span class="material-symbols-outlined text-[26px]">call_received</span>
            </div>
        </div>

        <!-- Total Keluar -->
        <div class="bg-white rounded-xl p-5 border border-outline-variant/30 shadow-2xs flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold text-on-surface-variant uppercase tracking-wider">Total Qty Keluar</p>
                <h3 class="text-2xl font-black text-purple-600 mt-1">-{{ number_format($stats['total_qty_out'] ?? 0) }}</h3>
                <p class="text-[11px] text-slate-500 mt-0.5">Outgoing &amp; pengiriman</p>
            </div>
            <div class="w-12 h-12 rounded-xl bg-purple-50 flex items-center justify-center text-purple-600">
                <span class="material-symbols-outlined text-[26px]">call_made</span>
            </div>
        </div>

        <!-- Mutasi Hari Ini -->
        <div class="bg-white rounded-xl p-5 border border-outline-variant/30 shadow-2xs flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold text-on-surface-variant uppercase tracking-wider">Mutasi Hari Ini</p>
                <h3 class="text-2xl font-black text-blue-600 mt-1">{{ number_format($stats['today_mutations'] ?? 0) }}</h3>
                <p class="text-[11px] text-slate-500 mt-0.5">{{ now()->isoFormat('D MMMM Y') }}</p>
            </div>
            <div class="w-12 h-12 rounded-xl bg-blue-50 flex items-center justify-center text-blue-600">
                <span class="material-symbols-outlined text-[26px]">today</span>
            </div>
        </div>
    </div>

    <!-- Filter & Table Card -->
    <div class="bg-white rounded-xl shadow-sm border border-outline-variant/30 overflow-hidden mb-6">
        <!-- Filter Form -->
        <div class="p-4 border-b border-outline-variant/30 bg-surface-container-lowest">
            <form method="GET" action="{{ route('inventory.stock-card.index') }}" class="flex flex-col lg:flex-row gap-3">
                <!-- Search Box -->
                <div class="flex-1 relative">
                    <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-outline text-[18px]">search</span>
                    <input 
                        type="text" 
                        name="search" 
                        value="{{ request('search') }}" 
                        placeholder="Cari SKU, Nama Produk, Varian, atau No. Referensi..." 
                        class="w-full h-10 pl-9 pr-3 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary/20 focus:border-primary focus:outline-none text-xs bg-white text-on-surface placeholder:text-on-surface-variant/60 transition-all"
                    >
                </div>

                <!-- Select Filters -->
                <div class="flex flex-wrap items-center gap-3">
                    <!-- Warehouse Filter -->
                    <div class="w-full sm:w-44">
                        <select name="warehouse_id" class="w-full h-10 px-3 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary/20 focus:border-primary focus:outline-none text-xs bg-white text-on-surface cursor-pointer transition-all" onchange="this.form.submit()">
                            <option value="">Semua Gudang</option>
                            @foreach($warehouses as $wh)
                                <option value="{{ $wh->id }}" {{ request('warehouse_id') == $wh->id ? 'selected' : '' }}>
                                    {{ $wh->name }} ({{ $wh->code }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Channel Filter -->
                    <div class="w-full sm:w-44">
                        <select name="store_channel_id" class="w-full h-10 px-3 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary/20 focus:border-primary focus:outline-none text-xs bg-white text-on-surface cursor-pointer transition-all" onchange="this.form.submit()">
                            <option value="">Semua Channel</option>
                            @foreach($channels as $ch)
                                <option value="{{ $ch->id }}" {{ request('store_channel_id') == $ch->id ? 'selected' : '' }}>
                                    {{ $ch->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Transaction Type Filter -->
                    <div class="w-full sm:w-40">
                        <select name="transaction_type" class="w-full h-10 px-3 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary/20 focus:border-primary focus:outline-none text-xs bg-white text-on-surface cursor-pointer transition-all" onchange="this.form.submit()">
                            <option value="">Semua Tipe</option>
                            <option value="incoming" {{ request('transaction_type') == 'incoming' ? 'selected' : '' }}>Incoming (+)</option>
                            <option value="outgoing" {{ request('transaction_type') == 'outgoing' ? 'selected' : '' }}>Outgoing (-)</option>
                            <option value="table_edit" {{ request('transaction_type') == 'table_edit' ? 'selected' : '' }}>Tabel Edit</option>
                            <option value="import" {{ request('transaction_type') == 'import' ? 'selected' : '' }}>Import Spreadsheet</option>
                            <option value="adjustment" {{ request('transaction_type') == 'adjustment' ? 'selected' : '' }}>Penyesuaian (Manual)</option>
                        </select>
                    </div>

                    <!-- Date Range -->
                    <div class="flex items-center gap-1.5">
                        <input 
                            type="date" 
                            name="start_date" 
                            value="{{ request('start_date') }}" 
                            class="h-10 px-2.5 border border-outline-variant rounded-lg text-xs bg-white focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary"
                            title="Tanggal Mulai"
                        >
                        <span class="text-xs text-slate-400">-</span>
                        <input 
                            type="date" 
                            name="end_date" 
                            value="{{ request('end_date') }}" 
                            class="h-10 px-2.5 border border-outline-variant rounded-lg text-xs bg-white focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary"
                            title="Tanggal Akhir"
                        >
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex items-center gap-2 shrink-0">
                        <button type="submit" class="h-10 px-4 bg-primary text-white hover:bg-primary/90 rounded-lg text-xs font-semibold transition-colors shadow-2xs inline-flex items-center gap-1.5">
                            <span class="material-symbols-outlined text-[16px]">filter_list</span>
                            <span>Filter</span>
                        </button>
                        @if(request()->hasAny(['search', 'warehouse_id', 'store_channel_id', 'transaction_type', 'start_date', 'end_date']))
                            <a href="{{ route('inventory.stock-card.index') }}" class="h-10 px-3 bg-danger/10 text-danger hover:bg-danger/20 rounded-lg text-xs font-semibold transition-colors inline-flex items-center gap-1" title="Reset Filter">
                                <span class="material-symbols-outlined text-[16px]">restart_alt</span>
                                <span class="hidden sm:inline">Reset</span>
                            </a>
                        @endif
                    </div>
                </div>
            </form>
        </div>

        <!-- Table Data -->
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-surface-container-low/60 border-b border-outline-variant/30 text-on-surface-variant text-[11px] font-bold uppercase tracking-wider">
                        <th class="py-3 px-4 min-w-[130px]">Waktu / Tanggal</th>
                        <th class="py-3 px-4 min-w-[200px]">Produk &amp; Varian (SKU)</th>
                        <th class="py-3 px-3 min-w-[140px]">Gudang &amp; Channel</th>
                        <th class="py-3 px-3 text-center min-w-[110px]">Tipe Mutasi</th>
                        <th class="py-3 px-3 text-center min-w-[80px] bg-success/5 text-success">Masuk (+)</th>
                        <th class="py-3 px-3 text-center min-w-[80px] bg-purple-50/50 text-purple-700">Keluar (-)</th>
                        <th class="py-3 px-3 text-center min-w-[85px] bg-slate-50">Stok Awal</th>
                        <th class="py-3 px-3 text-center min-w-[85px] bg-blue-50/50 font-bold text-blue-950">Stok Akhir</th>
                        <th class="py-3 px-4 min-w-[180px]">Referensi &amp; Keterangan</th>
                        <th class="py-3 px-3 text-center min-w-[100px]">Operator</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-outline-variant/20">
                    @forelse($stockCards as $card)
                        @php
                            $variant = $card->variant;
                            $product = $card->product ?: $variant?->product;
                            $wh = $card->warehouse;
                            $ch = $card->channel;

                            // Badge color per transaction type
                            $typeBadgeClass = match($card->transaction_type) {
                                'incoming' => 'bg-success/15 text-success border-success/30',
                                'outgoing' => 'bg-purple-100 text-purple-800 border-purple-200',
                                'table_edit' => 'bg-blue-100 text-blue-800 border-blue-200',
                                'import' => 'bg-teal-100 text-teal-800 border-teal-200',
                                'adjustment' => 'bg-amber-100 text-amber-800 border-amber-200',
                                'web_order' => 'bg-indigo-100 text-indigo-800 border-indigo-200',
                                default => 'bg-slate-100 text-slate-700 border-slate-200',
                            };

                            $typeLabel = match($card->transaction_type) {
                                'incoming' => 'Incoming',
                                'outgoing' => 'Outgoing',
                                'table_edit' => 'Tabel Edit',
                                'import' => 'Import File',
                                'adjustment' => 'Penyesuaian',
                                'web_order' => 'Order Web',
                                default => ucfirst($card->transaction_type),
                            };
                        @endphp
                        <tr class="hover:bg-slate-50/80 transition-colors">
                            <!-- Waktu / Tanggal -->
                            <td class="py-3 px-4 font-mono text-[11px] text-slate-600 whitespace-nowrap">
                                <div class="font-bold text-on-surface">{{ $card->created_at->format('d/m/Y') }}</div>
                                <div class="text-[10px] text-slate-400">{{ $card->created_at->format('H:i:s') }} WIB</div>
                            </td>

                            <!-- SKU & Varian -->
                            <td class="py-3 px-4">
                                <div class="font-bold text-on-surface text-xs">
                                    {{ $product?->name ?? 'Produk' }}
                                </div>
                                <div class="text-[11px] text-slate-600 mt-0.5 flex items-center gap-1.5 flex-wrap">
                                    <span class="font-semibold text-primary">{{ $variant?->variant_name ?? '-' }}</span>
                                    @if($variant?->sku)
                                        <span class="font-mono text-[10px] bg-slate-100 text-slate-700 px-1.5 py-0.2 rounded border border-outline-variant/30">
                                            {{ $variant->sku }}
                                        </span>
                                    @endif
                                </div>
                            </td>

                            <!-- Gudang & Channel -->
                            <td class="py-3 px-3">
                                <div class="text-xs font-semibold text-on-surface flex items-center gap-1">
                                    <span class="material-symbols-outlined text-[13px] text-slate-500">warehouse</span>
                                    <span>{{ $wh?->name ?? '-' }}</span>
                                </div>
                                <div class="text-[10px] text-slate-500 mt-0.5 flex items-center gap-1">
                                    <span class="material-symbols-outlined text-[12px] text-primary">storefront</span>
                                    <span>{{ $ch?->name ?? '-' }}</span>
                                </div>
                            </td>

                            <!-- Tipe Mutasi -->
                            <td class="py-3 px-3 text-center">
                                <span class="inline-block px-2.5 py-0.5 rounded-full text-[10px] font-bold border {{ $typeBadgeClass }}">
                                    {{ $typeLabel }}
                                </span>
                            </td>

                            <!-- Qty Masuk -->
                            <td class="py-3 px-3 text-center bg-success/5 font-mono">
                                @if($card->qty_in > 0)
                                    <span class="font-extrabold text-success text-xs">+{{ number_format($card->qty_in) }}</span>
                                @else
                                    <span class="text-slate-300">-</span>
                                @endif
                            </td>

                            <!-- Qty Keluar -->
                            <td class="py-3 px-3 text-center bg-purple-50/30 font-mono">
                                @if($card->qty_out > 0)
                                    <span class="font-extrabold text-purple-700 text-xs">-{{ number_format($card->qty_out) }}</span>
                                @else
                                    <span class="text-slate-300">-</span>
                                @endif
                            </td>

                            <!-- Stok Sebelum -->
                            <td class="py-3 px-3 text-center bg-slate-50/50 font-mono text-xs text-slate-600">
                                {{ number_format($card->stock_before) }}
                            </td>

                            <!-- Stok Sesudah -->
                            <td class="py-3 px-3 text-center bg-blue-50/40 font-mono font-bold text-xs text-blue-900">
                                {{ number_format($card->stock_after) }}
                            </td>

                            <!-- Referensi & Keterangan -->
                            <td class="py-3 px-4">
                                @if($card->reference_number)
                                    <span class="font-mono text-[10px] font-bold bg-slate-100 text-slate-700 px-1.5 py-0.5 rounded border border-outline-variant/30">
                                        {{ $card->reference_number }}
                                    </span>
                                @endif
                                <div class="text-[11px] text-slate-600 mt-0.5">
                                    {{ $card->notes ?: '-' }}
                                </div>
                            </td>

                            <!-- Operator / User -->
                            <td class="py-3 px-3 text-center">
                                <span class="text-[11px] font-semibold text-slate-700 bg-slate-100 px-2 py-0.5 rounded-full inline-block">
                                    {{ $card->creator ?: 'System' }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="py-12 text-center text-on-surface-variant bg-white">
                                <span class="material-symbols-outlined text-[48px] text-outline-variant mb-2">history_toggle_off</span>
                                <p class="text-sm font-semibold text-on-surface">Belum ada riwayat mutasi stok pada kartu stok.</p>
                                <p class="text-xs text-on-surface-variant mt-1">Setiap perubahan stok dari tabel inventory atau spreadsheet akan tercatat di sini secara otomatis.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($stockCards->hasPages())
            <div class="p-4 border-t border-outline-variant/30 bg-surface-container-lowest">
                {{ $stockCards->links() }}
            </div>
        @endif
    </div>
@endsection
