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

    <!-- Metric Cards Overview (Fokus Domain Item Master) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Card 1: Total Item Masters -->
        <div class="p-5 rounded-2xl bg-white border border-outline-variant shadow-xs flex flex-col justify-between hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-on-surface-variant uppercase tracking-wider">Total Item JDE</span>
                <div class="w-10 h-10 rounded-xl bg-blue-50 dark:bg-blue-950/40 text-blue-700 dark:text-blue-400 flex items-center justify-center">
                    <span class="material-symbols-outlined text-[22px]">inventory_2</span>
                </div>
            </div>
            <div class="mt-3">
                <div class="text-3xl font-extrabold text-on-surface tracking-tight">{{ number_format($stats['item_masters_total']) }}</div>
                <p class="text-xs text-on-surface-variant mt-1 flex items-center gap-1.5 font-medium">
                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                    <span>Tersimpan di temporary data</span>
                </p>
            </div>
            <div class="mt-4 pt-3 border-t border-outline-variant/60 flex items-center justify-between text-xs">
                <button @click="triggerPull('item_master')" :disabled="isLoading" class="font-bold text-primary hover:underline flex items-center gap-1 cursor-pointer">
                    <span class="material-symbols-outlined text-[15px]" :class="{ 'animate-spin': isLoading }">refresh</span> Tarik JDE
                </button>
                <span class="text-[11px] text-on-surface-variant font-medium">{{ $stats['last_sync_item'] ? \Carbon\Carbon::parse($stats['last_sync_item'])->diffForHumans() : 'Belum ditarik' }}</span>
            </div>
        </div>

        <!-- Card 2: Koleksi Kasur Ber-Brand -->
        <div class="p-5 rounded-2xl bg-white border border-outline-variant shadow-xs flex flex-col justify-between hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-on-surface-variant uppercase tracking-wider">Kasur Ber-Brand</span>
                <div class="w-10 h-10 rounded-xl bg-amber-50 dark:bg-amber-950/40 text-amber-700 dark:text-amber-400 flex items-center justify-center">
                    <span class="material-symbols-outlined text-[22px]">workspace_premium</span>
                </div>
            </div>
            <div class="mt-3">
                <div class="text-3xl font-extrabold text-amber-600 dark:text-amber-400 tracking-tight">{{ number_format($stats['item_masters_branded'] ?? 0) }}</div>
                <p class="text-xs text-on-surface-variant mt-1 font-medium truncate">
                    Elite, Lady Americana, Serenity, Royal
                </p>
            </div>
            <div class="mt-4 pt-3 border-t border-outline-variant/60 flex items-center justify-between text-xs">
                <span class="text-xs font-semibold text-amber-700 dark:text-amber-400 flex items-center gap-1">
                    <span class="material-symbols-outlined text-[15px]">verified</span> Prioritas Katalog
                </span>
                <span class="text-[11px] font-bold px-2 py-0.5 rounded-full bg-amber-100 dark:bg-amber-900/40 text-amber-800 dark:text-amber-300">
                    {{ round((($stats['item_masters_branded'] ?? 0) / max($stats['item_masters_total'], 1)) * 100) }}%
                </span>
            </div>
        </div>

        <!-- Card 3: Item Non-Brand / Part -->
        <div class="p-5 rounded-2xl bg-white border border-outline-variant shadow-xs flex flex-col justify-between hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-on-surface-variant uppercase tracking-wider">Item Non-Brand</span>
                <div class="w-10 h-10 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 flex items-center justify-center">
                    <span class="material-symbols-outlined text-[22px]">extension</span>
                </div>
            </div>
            <div class="mt-3">
                <div class="text-3xl font-extrabold text-on-surface tracking-tight">{{ number_format(max(0, $stats['item_masters_total'] - ($stats['item_masters_branded'] ?? 0))) }}</div>
                <p class="text-xs text-on-surface-variant mt-1 font-medium">
                    Busa lembaran, komponen & aksesoris
                </p>
            </div>
            <div class="mt-4 pt-3 border-t border-outline-variant/60 flex items-center justify-between text-xs text-on-surface-variant">
                <span>Data Pendukung</span>
                <span class="text-[11px] font-medium text-on-surface-variant">Arsip JDE</span>
            </div>
        </div>

        <!-- Card 4: Status Sinkronisasi Katalog -->
        <div class="p-5 rounded-2xl bg-white border border-outline-variant shadow-xs flex flex-col justify-between hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-on-surface-variant uppercase tracking-wider">Status Katalog</span>
                <div class="w-10 h-10 rounded-xl bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-400 flex items-center justify-center">
                    <span class="material-symbols-outlined text-[22px]">sync_saved_locally</span>
                </div>
            </div>
            <div class="mt-3">
                <div class="text-3xl font-extrabold text-emerald-600 dark:text-emerald-400 tracking-tight">{{ number_format($stats['item_masters_pending']) }}</div>
                <p class="text-xs text-on-surface-variant mt-1 flex items-center gap-1.5 font-medium">
                    <span class="w-2 h-2 rounded-full bg-amber-400"></span>
                    <span>Menunggu disalin ke katalog</span>
                </p>
            </div>
            <div class="mt-4 pt-3 border-t border-outline-variant/60 flex items-center justify-between text-xs text-on-surface-variant">
                <span class="text-emerald-700 dark:text-emerald-400 font-semibold flex items-center gap-1">
                    <span class="material-symbols-outlined text-[15px]">check_circle</span> Data Terisolasi
                </span>
                <span class="text-[11px]">Katalog Aman</span>
            </div>
        </div>
    </div>

    <!-- Quick Filter Brand Pills (UX Standard) -->
    <div class="flex items-center gap-2 overflow-x-auto pb-1 scrollbar-none text-xs">
        <a href="{{ route('admin.jde.index', ['tab' => $tab, 'search' => $search]) }}" 
           class="px-4 py-2 rounded-xl font-bold transition-all whitespace-nowrap {{ empty($brand) ? 'bg-primary text-white shadow-xs' : 'bg-white border border-outline-variant text-on-surface-variant hover:border-primary/40 hover:text-primary' }}">
            Semua Data ({{ number_format($stats['item_masters_total']) }})
        </a>
        <a href="{{ route('admin.jde.index', ['tab' => $tab, 'brand' => 'has_brand', 'search' => $search]) }}" 
           class="px-4 py-2 rounded-xl font-bold transition-all whitespace-nowrap flex items-center gap-1.5 {{ $brand === 'has_brand' ? 'bg-primary text-white shadow-xs' : 'bg-white border border-outline-variant text-on-surface-variant hover:border-primary/40 hover:text-primary' }}">
            <span class="w-2 h-2 rounded-full bg-amber-400"></span>
            ✨ Memiliki Brand ({{ number_format($stats['item_masters_branded'] ?? 0) }})
        </a>
        @foreach($brands ?? [] as $b)
            <a href="{{ route('admin.jde.index', ['tab' => $tab, 'brand' => $b, 'search' => $search]) }}" 
               class="px-3.5 py-2 rounded-xl font-semibold transition-all whitespace-nowrap {{ $brand === $b ? 'bg-primary text-white shadow-xs' : 'bg-white border border-outline-variant text-on-surface-variant hover:border-primary/40 hover:text-primary' }}">
                {{ $b }}
            </a>
        @endforeach
    </div>

    <!-- Table Container & Search Bar -->
    <div class="bg-white rounded-2xl border border-outline-variant shadow-xs overflow-hidden">
        <div class="p-4 border-b border-outline-variant flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-lg bg-blue-50 dark:bg-blue-950/50 text-primary flex items-center justify-center">
                    <span class="material-symbols-outlined text-[18px]">bed</span>
                </div>
                <div>
                    <h3 class="font-bold text-sm text-on-surface leading-tight">Data Item Master Kasur</h3>
                    <p class="text-[11px] text-on-surface-variant font-medium">Daftar item JDE siap ditinjau dan disinkronkan</p>
                </div>

                <!-- Info Icon Keterangan Data Temporary -->
                <div class="relative inline-flex items-center ml-1" x-data="{ showTableInfo: false }" @click.away="showTableInfo = false">
                    <button 
                        type="button" 
                        @click="showTableInfo = !showTableInfo" 
                        @mouseenter="showTableInfo = true" 
                        @mouseleave="showTableInfo = false" 
                        class="p-1 rounded-full text-on-surface-variant/70 hover:text-primary transition-colors focus:outline-none cursor-pointer" 
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
                        class="absolute left-0 top-full mt-2 w-72 p-3 bg-surface text-on-surface rounded-xl shadow-xl border border-outline-variant z-50 text-xs font-normal">
                        <p class="font-bold text-primary mb-1">Penyimpanan Sementara</p>
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
                    <thead>
                        <tr>
                            <th class="px-5 py-3.5">Produk Kasur & SKU</th>
                            <th class="px-5 py-3.5">Brand</th>
                            <th class="px-5 py-3.5">Spesifikasi Ukuran</th>
                            <th class="px-5 py-3.5">Status Katalog</th>
                            <th class="px-5 py-3.5 text-right">Update JDE</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-outline-variant text-on-surface">
                        @forelse($data as $row)
                            <tr class="hover:bg-primary/5 transition-colors">
                                <!-- Col 1: Rich Product Cell -->
                                <td class="px-5 py-3.5">
                                    <div class="font-bold text-on-surface text-[14px] leading-snug">{{ $row->description }}</div>
                                    <div class="flex items-center gap-2 mt-1">
                                        <span class="inline-flex items-center gap-1 font-mono text-[11px] font-semibold px-2 py-0.5 rounded bg-surface-container text-on-surface-variant border border-outline-variant/60" title="SKU / Second Item Number">
                                            <span class="material-symbols-outlined text-[13px] text-primary">barcode</span>
                                            {{ $row->second_item_number }}
                                        </span>
                                        <span class="text-[10px] font-mono text-on-surface-variant/80">#{{ $row->short_item_no }}</span>
                                        @if($row->template)
                                            <span class="text-[10px] px-1.5 py-0.5 rounded bg-slate-100 text-slate-600 font-medium truncate max-w-[120px]">{{ $row->template }}</span>
                                        @endif
                                    </div>
                                </td>

                                <!-- Col 2: Brand Pill -->
                                <td class="px-5 py-3.5 whitespace-nowrap">
                                    @php
                                        $bName = strtoupper(trim($row->sales_code1_desc ?? ''));
                                    @endphp
                                    @if($bName === 'ROYAL')
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-bold bg-blue-50 text-blue-800 border border-blue-200 shadow-2xs">
                                            <span class="w-1.5 h-1.5 rounded-full bg-blue-600"></span> ROYAL
                                        </span>
                                    @elseif($bName === 'ELITE')
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-bold bg-amber-50 text-amber-800 border border-amber-200 shadow-2xs">
                                            <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span> ELITE
                                        </span>
                                    @elseif($bName === 'LADY')
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-bold bg-rose-50 text-rose-800 border border-rose-200 shadow-2xs">
                                            <span class="w-1.5 h-1.5 rounded-full bg-rose-600"></span> LADY
                                        </span>
                                    @elseif($bName === 'SERENITY')
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-bold bg-purple-50 text-purple-800 border border-purple-200 shadow-2xs">
                                            <span class="w-1.5 h-1.5 rounded-full bg-purple-600"></span> SERENITY
                                        </span>
                                    @elseif(!empty($bName) && $bName !== 'NON-BRAND')
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-bold bg-slate-100 text-slate-800 border border-slate-300 shadow-2xs">
                                            <span class="w-1.5 h-1.5 rounded-full bg-slate-600"></span> {{ $bName }}
                                        </span>
                                    @else
                                        <span class="px-2 py-0.5 rounded text-[11px] font-medium text-slate-400 bg-slate-100">
                                            NON-BRAND
                                        </span>
                                    @endif
                                </td>

                                <!-- Col 3: Specifications & Size -->
                                <td class="px-5 py-3.5">
                                    <div class="flex items-center gap-1.5 flex-wrap">
                                        @if($row->description_line_2)
                                            <span class="font-mono text-xs font-semibold px-2 py-0.5 rounded-md bg-slate-100 text-slate-800 border border-slate-200">
                                                {{ $row->description_line_2 }}
                                            </span>
                                        @endif
                                        @if(!empty($row->segments))
                                            <span class="text-[11px] font-mono text-on-surface-variant bg-surface-container px-2 py-0.5 rounded border border-outline-variant/60" title="{{ json_encode($row->segments) }}">
                                                {{ $row->segments['segment1'] ?? '' }}/{{ $row->segments['segment2'] ?? '' }}
                                            </span>
                                        @endif
                                    </div>
                                </td>

                                <!-- Col 4: Status Indicator -->
                                <td class="px-5 py-3.5 whitespace-nowrap">
                                    @if($row->sync_status === 'PROCESSED')
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Di Katalog
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-amber-50 text-amber-700 border border-amber-200">
                                            <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span> Belum Disalin
                                        </span>
                                    @endif
                                </td>

                                <!-- Col 5: Update Date -->
                                <td class="px-5 py-3.5 text-right whitespace-nowrap">
                                    <div class="text-xs font-medium text-on-surface">
                                        {{ $row->date_update ? $row->date_update->format('d M Y') : '-' }}
                                    </div>
                                    <div class="text-[10px] text-on-surface-variant font-medium">
                                        {{ $row->updated_at ? $row->updated_at->diffForHumans() : '' }}
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-5 py-12 text-center text-on-surface-variant">
                                    <span class="material-symbols-outlined text-4xl text-outline-variant mb-2 block">inbox</span>
                                    <p class="font-bold text-sm text-on-surface">Belum ada data Item Master.</p>
                                    <p class="text-xs text-on-surface-variant mt-1">Silakan klik tombol "Tarik Item Master JDE" di kanan atas.</p>
                                </td>
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
