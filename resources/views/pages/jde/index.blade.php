@extends('layouts.app')

@section('title', 'Master Data JDE')

@section('content')
<div class="w-full space-y-6" x-data="jdeSyncHandler()">
    <!-- Header Section -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <h1 class="font-headline-lg text-headline-lg font-bold text-on-surface flex items-center gap-2">
                    <span class="material-symbols-outlined text-primary text-3xl">sync_alt</span>
                    Master Data JDE
                </h1>
                
                <!-- Info Icon Keterangan Data Temporary -->
                <div class="relative inline-flex items-center" x-data="{ showHeaderInfo: false }" @click.away="showHeaderInfo = false">
                    <button 
                        type="button" 
                        @click="showHeaderInfo = !showHeaderInfo" 
                        @mouseenter="showHeaderInfo = true" 
                        @mouseleave="showHeaderInfo = false" 
                        class="p-1 rounded-full text-on-surface-variant/70 hover:text-primary hover:bg-surface-variant/50 transition-colors focus:outline-none cursor-pointer" 
                        title="Keterangan Data">
                        <span class="material-symbols-outlined text-[20px] align-middle">info</span>
                    </button>
                    <div 
                        x-show="showHeaderInfo" 
                        x-cloak 
                        x-transition:enter="transition ease-out duration-150"
                        x-transition:enter-start="opacity-0 translate-y-1"
                        x-transition:enter-end="opacity-100 translate-y-0"
                        x-transition:leave="transition ease-in duration-100"
                        x-transition:leave-start="opacity-100 translate-y-0"
                        x-transition:leave-end="opacity-0 translate-y-1"
                        class="absolute left-0 top-full mt-2 w-80 p-3.5 bg-surface text-on-surface rounded-xl shadow-xl border border-outline/20 z-50 text-xs font-normal">
                        <div class="font-semibold text-primary flex items-center gap-1.5 mb-1 text-sm">
                            <span class="material-symbols-outlined text-base">verified_user</span>
                            <span>Penyimpanan Data Temporary</span>
                        </div>
                        <p class="text-on-surface-variant leading-relaxed">
                            Data yang ditarik dari JDE disimpan sementara di sini. Data ini aman dan tidak akan langsung mengubah katalog produk sebelum Anda menyalinnya.
                        </p>
                    </div>
                </div>
            </div>
            <nav class="flex items-center gap-2 text-label-sm text-on-surface-variant mt-1 font-medium">
                <a href="{{ route('dashboard') }}" class="hover:text-primary transition-colors">Dashboard</a>
                <span class="material-symbols-outlined text-[14px]">chevron_right</span>
                <span class="text-on-surface">Master Data JDE</span>
            </nav>
        </div>
        <div class="flex items-center gap-2">
            <button 
                type="button"
                @click="triggerPull('item_master')"
                :disabled="isLoading"
                class="px-5 py-2.5 rounded-lg bg-primary text-on-primary hover:bg-primary/90 font-label-md font-semibold flex items-center gap-2 shadow-sm transition-all disabled:opacity-50">
                <span class="material-symbols-outlined text-[18px]" :class="{ 'animate-spin': isLoading }">sync</span>
                <span x-text="isLoading ? 'Menarik Data...' : 'Tarik Item Master JDE'"></span>
            </button>
        </div>
    </div>

    @include('layouts.partials.jde-submenu')

    <!-- Metric Cards Overview (Fase 1: Fokus Item Master) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Item Masters Card -->
        <div class="p-5 rounded-2xl bg-surface border border-outline/30 shadow-sm flex flex-col justify-between">
            <div class="flex items-center justify-between">
                <span class="text-label-sm font-semibold text-on-surface-variant uppercase tracking-wider">Item Master</span>
                <span class="p-2 rounded-lg bg-primary/10 text-primary material-symbols-outlined text-xl">bed</span>
            </div>
            <div class="mt-4">
                <div class="text-3xl font-extrabold text-on-surface">{{ number_format($stats['item_masters_total']) }}</div>
                <div class="text-xs text-on-surface-variant mt-1.5 flex items-center gap-1.5 flex-wrap">
                    <span class="inline-flex items-center gap-1 text-primary font-medium">
                        <span class="inline-block w-2 h-2 rounded-full bg-primary"></span>
                        <span>{{ number_format($stats['item_masters_branded'] ?? 0) }} ber-brand</span>
                    </span>
                    <span class="text-outline">·</span>
                    <span class="inline-flex items-center gap-1 text-on-surface-variant">
                        <span class="inline-block w-2 h-2 rounded-full bg-warning"></span>
                        <span>{{ number_format($stats['item_masters_pending']) }} belum disalin</span>
                    </span>
                </div>
            </div>
            <div class="mt-4 pt-3 border-t border-outline/20 flex items-center justify-between">
                <button @click="triggerPull('item_master')" :disabled="isLoading" class="text-xs font-semibold text-primary hover:underline flex items-center gap-1">
                    <span class="material-symbols-outlined text-[14px]">refresh</span> Tarik Item Master
                </button>
                <span class="text-[11px] text-on-surface-variant">{{ $stats['last_sync_item'] ? \Carbon\Carbon::parse($stats['last_sync_item'])->diffForHumans() : 'Belum ditarik' }}</span>
            </div>
        </div>

        {{-- Sementara di-hide / di-komen sesuai kebutuhan fase 1 (Hanya Item Master yang Aktif) --}}
        {{--
        <!-- Base Prices Card -->
        <div class="p-5 rounded-2xl bg-surface border border-outline/30 shadow-sm flex flex-col justify-between opacity-50">
            <div class="flex items-center justify-between">
                <span class="text-label-sm font-semibold text-on-surface-variant uppercase tracking-wider">Base Price</span>
                <span class="p-2 rounded-lg bg-success/10 text-success material-symbols-outlined text-xl">payments</span>
            </div>
            <div class="mt-4">
                <div class="text-3xl font-extrabold text-on-surface">{{ number_format($stats['base_prices_total']) }}</div>
                <div class="text-xs text-on-surface-variant mt-1">Daftar harga dasar per cabang</div>
            </div>
            <div class="mt-4 pt-3 border-t border-outline/20 flex items-center justify-between">
                <button @click="triggerPull('base_price')" :disabled="isLoading" class="text-xs font-semibold text-primary hover:underline flex items-center gap-1">
                    <span class="material-symbols-outlined text-[14px]">refresh</span> Tarik Base Price
                </button>
                <span class="text-[11px] text-on-surface-variant">{{ $stats['last_sync_price'] ? \Carbon\Carbon::parse($stats['last_sync_price'])->diffForHumans() : 'Belum ditarik' }}</span>
            </div>
        </div>

        <!-- Branch Plants Card -->
        <div class="p-5 rounded-2xl bg-surface border border-outline/30 shadow-sm flex flex-col justify-between opacity-50">
            <div class="flex items-center justify-between">
                <span class="text-label-sm font-semibold text-on-surface-variant uppercase tracking-wider">Cabang JDE</span>
                <span class="p-2 rounded-lg bg-info/10 text-info material-symbols-outlined text-xl">factory</span>
            </div>
            <div class="mt-4">
                <div class="text-3xl font-extrabold text-on-surface">{{ number_format($stats['branches_total']) }}</div>
                <div class="text-xs text-on-surface-variant mt-1">Daftar cabang regional pabrik</div>
            </div>
            <div class="mt-4 pt-3 border-t border-outline/20 flex items-center justify-between">
                <button @click="triggerPull('branch')" :disabled="isLoading" class="text-xs font-semibold text-primary hover:underline flex items-center gap-1">
                    <span class="material-symbols-outlined text-[14px]">refresh</span> Tarik Cabang
                </button>
                <span class="text-[11px] text-on-surface-variant">{{ $stats['last_sync_branch'] ? \Carbon\Carbon::parse($stats['last_sync_branch'])->diffForHumans() : 'Belum ditarik' }}</span>
            </div>
        </div>

        <!-- Customers Card -->
        <div class="p-5 rounded-2xl bg-surface border border-outline/30 shadow-sm flex flex-col justify-between opacity-50">
            <div class="flex items-center justify-between">
                <span class="text-label-sm font-semibold text-on-surface-variant uppercase tracking-wider">Toko Retail</span>
                <span class="p-2 rounded-lg bg-purple-500/10 text-purple-600 material-symbols-outlined text-xl">store</span>
            </div>
            <div class="mt-4">
                <div class="text-3xl font-extrabold text-on-surface">{{ number_format($stats['customers_total']) }}</div>
                <div class="text-xs text-on-surface-variant mt-1">Daftar mitra toko retail</div>
            </div>
            <div class="mt-4 pt-3 border-t border-outline/20 flex items-center justify-between">
                <button @click="triggerPull('customer')" :disabled="isLoading" class="text-xs font-semibold text-primary hover:underline flex items-center gap-1">
                    <span class="material-symbols-outlined text-[14px]">refresh</span> Tarik Toko
                </button>
                <span class="text-[11px] text-on-surface-variant">{{ $stats['last_sync_customer'] ? \Carbon\Carbon::parse($stats['last_sync_customer'])->diffForHumans() : 'Belum ditarik' }}</span>
            </div>
        </div>
        --}}
    </div>

    <!-- Table Container & Search Bar -->
    <div class="bg-surface rounded-2xl border border-outline/30 shadow-sm overflow-hidden">
        <div class="p-4 border-b border-outline/20 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="flex items-center gap-2.5">
                <span class="material-symbols-outlined text-primary text-xl">bed</span>
                <span class="font-headline-sm text-base font-bold text-on-surface">Data Item Master</span>
                <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-primary/10 text-primary">
                    {{ number_format($stats['item_masters_total']) }} Data
                </span>

                <!-- Info Icon Keterangan Data Temporary -->
                <div class="relative inline-flex items-center" x-data="{ showTableInfo: false }" @click.away="showTableInfo = false">
                    <button 
                        type="button" 
                        @click="showTableInfo = !showTableInfo" 
                        @mouseenter="showTableInfo = true" 
                        @mouseleave="showTableInfo = false" 
                        class="p-0.5 rounded-full text-on-surface-variant/60 hover:text-primary transition-colors focus:outline-none cursor-pointer" 
                        title="Info Penyimpanan">
                        <span class="material-symbols-outlined text-[18px] align-middle">info</span>
                    </button>
                    <div 
                        x-show="showTableInfo" 
                        x-cloak 
                        x-transition:enter="transition ease-out duration-150"
                        x-transition:enter-start="opacity-0 translate-y-1"
                        x-transition:enter-end="opacity-100 translate-y-0"
                        x-transition:leave="transition ease-in duration-100"
                        x-transition:leave-start="opacity-100 translate-y-0"
                        x-transition:leave-end="opacity-0 translate-y-1"
                        class="absolute left-0 top-full mt-1.5 w-72 p-3 bg-surface text-on-surface rounded-xl shadow-xl border border-outline/20 z-50 text-xs font-normal">
                        <p class="font-semibold text-primary mb-1">Penyimpanan Sementara</p>
                        <p class="text-on-surface-variant leading-relaxed">
                            Data yang ditarik disimpan sementara di sini agar aman dan dapat ditinjau sebelum disalin ke katalog produk.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Search & Filter Form -->
            <form method="GET" action="{{ route('admin.jde.index') }}" class="flex flex-wrap items-center gap-2">
                <input type="hidden" name="tab" value="{{ $tab }}">
                
                @if($tab === 'item_master')
                    <div class="relative">
                        <select 
                            name="brand" 
                            onchange="this.form.submit()" 
                            class="pl-3 pr-8 py-1.5 text-xs font-medium rounded-lg border border-outline bg-surface text-on-surface focus:outline-none focus:ring-2 focus:ring-primary/40 cursor-pointer">
                            <option value="">Semua (Brand Teratas)</option>
                            <option value="has_brand" {{ ($brand ?? '') === 'has_brand' ? 'selected' : '' }}>Hanya yang Memiliki Brand</option>
                            <option value="no_brand" {{ ($brand ?? '') === 'no_brand' ? 'selected' : '' }}>Tanpa Brand</option>
                            @if(!empty($brands))
                                <optgroup label="Pilih Brand Spesifik:">
                                    @foreach($brands as $b)
                                        <option value="{{ $b }}" {{ ($brand ?? '') === $b ? 'selected' : '' }}>{{ $b }}</option>
                                    @endforeach
                                </optgroup>
                            @endif
                        </select>
                    </div>
                @endif

                <div class="relative">
                    <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-on-surface-variant text-[18px]">search</span>
                    <input 
                        type="text" 
                        name="search" 
                        value="{{ $search }}" 
                        placeholder="Cari SKU, nama, deskripsi..." 
                        class="pl-9 pr-4 py-1.5 text-sm rounded-lg border border-outline bg-surface text-on-surface focus:outline-none focus:ring-2 focus:ring-primary/40 w-52 sm:w-64">
                </div>
                <button type="submit" class="px-3 py-1.5 rounded-lg bg-surface-variant text-on-surface hover:bg-outline/20 text-sm font-medium">Filter</button>
                @if($search || !empty($brand))
                    <a href="{{ route('admin.jde.index', ['tab' => $tab]) }}" class="text-xs text-danger hover:underline">Reset</a>
                @endif
            </form>
        </div>

        <!-- Table Content Based on Active Tab -->
        <div class="overflow-x-auto">
            @if($tab === 'item_master')
                <table class="w-full text-left text-sm">
                    <thead class="bg-surface-variant/40 text-on-surface-variant text-xs uppercase font-semibold border-b border-outline/20">
                        <tr>
                            <th class="px-4 py-3">Short Item</th>
                            <th class="px-4 py-3">SKU (2nd Item No)</th>
                            <th class="px-4 py-3">Deskripsi Kasur</th>
                            <th class="px-4 py-3">Brand</th>
                            <th class="px-4 py-3">Ukuran (Line 2)</th>
                            <th class="px-4 py-3">Segments (JSON)</th>
                            <th class="px-4 py-3">Status</th>
                            <th class="px-4 py-3 text-right">Update JDE</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-outline/10 text-on-surface">
                        @forelse($data as $row)
                            <tr class="hover:bg-surface-variant/20 transition-colors">
                                <td class="px-4 py-3 font-mono text-xs font-bold text-primary">{{ $row->short_item_no }}</td>
                                <td class="px-4 py-3 font-mono text-xs font-semibold">{{ $row->second_item_number }}</td>
                                <td class="px-4 py-3">
                                    <div class="font-medium text-on-surface">{{ $row->description }}</div>
                                    <div class="text-[11px] text-on-surface-variant">{{ $row->template }}</div>
                                </td>
                                <td class="px-4 py-3">
                                    @if(!empty($row->sales_code1_desc) && $row->sales_code1_desc !== 'NON-BRAND')
                                        <span class="px-2.5 py-0.5 rounded text-[11px] font-bold bg-primary/10 text-primary border border-primary/20">
                                            {{ $row->sales_code1_desc }}
                                        </span>
                                    @else
                                        <span class="px-2 py-0.5 rounded text-[11px] font-medium text-on-surface-variant/60 bg-surface-variant/50">
                                            NON-BRAND
                                        </span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 font-mono text-xs">{{ $row->description_line_2 ?: '-' }}</td>
                                <td class="px-4 py-3">
                                    <span class="text-[11px] font-mono bg-surface-variant px-2 py-1 rounded max-w-[200px] truncate inline-block" title="{{ json_encode($row->segments) }}">
                                        {{ $row->segments['segment1'] ?? '' }}/{{ $row->segments['segment2'] ?? '' }}/{{ $row->segments['segment5'] ?? '' }}x{{ $row->segments['segment6'] ?? '' }}
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase {{ $row->sync_status === 'PROCESSED' ? 'bg-success/15 text-success' : 'bg-warning/15 text-warning' }}">
                                        {{ $row->sync_status }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-right text-xs text-on-surface-variant">
                                    {{ $row->date_update ? $row->date_update->format('Y-m-d') : '-' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-4 py-8 text-center text-on-surface-variant">Belum ada data Item Master. Silakan klik tombol "Tarik Item Master" di atas.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            @elseif($tab === 'base_price')
                <table class="w-full text-left text-sm">
                    <thead class="bg-surface-variant/40 text-on-surface-variant text-xs uppercase font-semibold border-b border-outline/20">
                        <tr>
                            <th class="px-4 py-3">SKU</th>
                            <th class="px-4 py-3">Branch Plant (BU)</th>
                            <th class="px-4 py-3">Nama Cabang BP</th>
                            <th class="px-4 py-3">Base Price Grosir</th>
                            <th class="px-4 py-3">Effective Date</th>
                            <th class="px-4 py-3">Expired Date</th>
                            <th class="px-4 py-3">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-outline/10 text-on-surface">
                        @forelse($data as $row)
                            <tr class="hover:bg-surface-variant/20 transition-colors">
                                <td class="px-4 py-3 font-mono text-xs font-semibold">{{ $row->second_item_number }}</td>
                                <td class="px-4 py-3 font-mono text-xs font-bold text-primary">{{ $row->business_unit }}</td>
                                <td class="px-4 py-3 text-xs">{{ $row->business_unit_desc ?: '-' }}</td>
                                <td class="px-4 py-3 font-mono text-sm font-bold text-success">Rp {{ number_format((float)$row->base_price, 0, ',', '.') }}</td>
                                <td class="px-4 py-3 text-xs">{{ $row->effective_date ? $row->effective_date->format('Y-m-d') : '-' }}</td>
                                <td class="px-4 py-3 text-xs">{{ $row->expired_date ? $row->expired_date->format('Y-m-d') : '-' }}</td>
                                <td class="px-4 py-3">
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase {{ $row->sync_status === 'PROCESSED' ? 'bg-success/15 text-success' : 'bg-warning/15 text-warning' }}">
                                        {{ $row->sync_status }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-4 py-8 text-center text-on-surface-variant">Belum ada data Base Price. Silakan klik tombol "Tarik Base Price" di atas.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            @elseif($tab === 'branch')
                <table class="w-full text-left text-sm">
                    <thead class="bg-surface-variant/40 text-on-surface-variant text-xs uppercase font-semibold border-b border-outline/20">
                        <tr>
                            <th class="px-4 py-3">Business Unit (BP)</th>
                            <th class="px-4 py-3">Kode Cabang</th>
                            <th class="px-4 py-3">Nama Cabang Pabrik</th>
                            <th class="px-4 py-3">Company</th>
                            <th class="px-4 py-3">Stocking Type</th>
                            <th class="px-4 py-3">Status</th>
                            <th class="px-4 py-3 text-right">Update JDE</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-outline/10 text-on-surface">
                        @forelse($data as $row)
                            <tr class="hover:bg-surface-variant/20 transition-colors">
                                <td class="px-4 py-3 font-mono text-xs font-bold text-primary">{{ $row->business_unit }}</td>
                                <td class="px-4 py-3 font-mono text-xs">{{ $row->cabang_code }}</td>
                                <td class="px-4 py-3 font-semibold">{{ $row->cabang_desc }}</td>
                                <td class="px-4 py-3 text-xs">{{ $row->company }}</td>
                                <td class="px-4 py-3 text-xs">{{ $row->stocking_type }}</td>
                                <td class="px-4 py-3">
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase bg-info/15 text-info">
                                        {{ $row->sync_status }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-right text-xs text-on-surface-variant">
                                    {{ $row->date_update ? $row->date_update->format('Y-m-d') : '-' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-4 py-8 text-center text-on-surface-variant">Belum ada data Branch Plant. Silakan klik tombol "Tarik Branch" di atas.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            @elseif($tab === 'customer')
                <table class="w-full text-left text-sm">
                    <thead class="bg-surface-variant/40 text-on-surface-variant text-xs uppercase font-semibold border-b border-outline/20">
                        <tr>
                            <th class="px-4 py-3">Address No (JDE)</th>
                            <th class="px-4 py-3">Nama Toko Retail</th>
                            <th class="px-4 py-3">Branch Plant (BU)</th>
                            <th class="px-4 py-3">Wilayah / Kota</th>
                            <th class="px-4 py-3">Plafon Kredit</th>
                            <th class="px-4 py-3">Termin</th>
                            <th class="px-4 py-3">Hold Code</th>
                            <th class="px-4 py-3">Channel</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-outline/10 text-on-surface">
                        @forelse($data as $row)
                            <tr class="hover:bg-surface-variant/20 transition-colors">
                                <td class="px-4 py-3 font-mono text-xs font-bold text-primary">{{ $row->address_number }}</td>
                                <td class="px-4 py-3 font-semibold text-on-surface">{{ $row->alpha_name }}</td>
                                <td class="px-4 py-3 font-mono text-xs">{{ $row->business_unit }}</td>
                                <td class="px-4 py-3 text-xs">{{ $row->cabang_desc ?: '-' }}</td>
                                <td class="px-4 py-3 font-mono text-xs font-semibold text-success">Rp {{ number_format((float)$row->credit_limit, 0, ',', '.') }}</td>
                                <td class="px-4 py-3 text-xs">{{ $row->payment_term }} Hari</td>
                                <td class="px-4 py-3 font-mono text-xs">
                                    @if(trim($row->hold_code) === '')
                                        <span class="px-2 py-0.5 rounded bg-success/15 text-success font-bold text-[10px]">LANCAR</span>
                                    @else
                                        <span class="px-2 py-0.5 rounded bg-danger/15 text-danger font-bold text-[10px]">HOLD: {{ $row->hold_code }}</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-xs">
                                    <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-surface-variant text-on-surface-variant">{{ $row->channel_desc }}</span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-4 py-8 text-center text-on-surface-variant">Belum ada data Customer. Silakan klik tombol "Tarik Customer" di atas.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            @endif
        </div>

        <!-- Pagination -->
        @if($data->hasPages())
            <div class="p-4 border-t border-outline/20">
                {{ $data->links() }}
            </div>
        @endif
    </div>
</div>

<script>
function jdeSyncHandler() {
    return {
        isLoading: false,

        async triggerPull(type = 'item_master') {
            const confirmed = await window.showConfirmDialog({
                title: 'Tarik Item Master JDE',
                text: 'Apakah Anda yakin ingin menarik data Item Master dari JDE ke tabel temporary?',
                icon: 'question',
                confirmButtonText: 'Ya, Tarik Item Master',
                cancelButtonText: 'Batal'
            });

            if (!confirmed.isConfirmed) {
                return;
            }

            this.isLoading = true;
            try {
                const response = await fetch("{{ route('admin.jde.pull') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ type: 'item_master' })
                });

                const res = await response.json();
                if (res.success) {
                    await window.showSuccessPopup(res.message || 'Data Item Master berhasil ditarik ke tabel temporary.');
                    window.location.reload();
                } else {
                    window.showErrorPopup(res.message || 'Gagal menarik data Item Master dari JDE.', 'Penarikan Gagal');
                }
            } catch (err) {
                window.showErrorPopup('Koneksi bermasalah: ' + err.message, 'Kesalahan Jaringan');
            } finally {
                this.isLoading = false;
            }
        }
    }
}
</script>
@endsection
