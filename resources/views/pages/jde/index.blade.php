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
            @if($tab === 'item_master')
                <button 
                    type="button"
                    @click="batchCopy()"
                    :disabled="isLoading"
                    class="px-4 py-2.5 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white font-label-md font-semibold flex items-center gap-2 shadow-sm transition-all disabled:opacity-50 cursor-pointer">
                    <span class="material-symbols-outlined text-[18px]">bolt</span>
                    <span>Salin Branded ke Katalog</span>
                </button>
            @endif
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
                        <tr class="bg-surface-variant/30 text-on-surface-variant text-xs font-bold uppercase tracking-wider">
                            <th class="w-10 px-4 py-3.5 text-center">
                                <input type="checkbox" @change="toggleSelectAll($event)" :checked="isAllSelected" class="w-5 h-5 rounded-md border-2 border-slate-400 hover:border-blue-600 checked:bg-blue-600 checked:border-blue-600 text-blue-600 focus:ring-2 focus:ring-blue-500/40 bg-white transition-all cursor-pointer shadow-xs" title="Pilih Semua di Halaman Ini">
                            </th>
                            <th class="px-5 py-3.5">Produk Kasur & SKU</th>
                            <th class="px-5 py-3.5">Brand</th>
                            <th class="px-5 py-3.5">Spesifikasi Ukuran</th>
                            <th class="px-5 py-3.5">Status Katalog</th>
                            <th class="px-5 py-3.5">Update JDE</th>
                            <th class="px-5 py-3.5 text-center">Aksi Salin</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-outline-variant text-on-surface">
                        @forelse($data as $row)
                            <tr class="hover:bg-primary/5 transition-colors" :class="{ 'bg-blue-50/70 border-l-4 border-l-blue-600': selectedItemIds.includes('{{ $row->id }}') }">
                                <td class="w-10 px-4 py-3.5 text-center">
                                    <input type="checkbox" value="{{ $row->id }}" x-model="selectedItemIds" class="w-5 h-5 rounded-md border-2 border-slate-400 hover:border-blue-600 checked:bg-blue-600 checked:border-blue-600 text-blue-600 focus:ring-2 focus:ring-blue-500/40 bg-white transition-all cursor-pointer shadow-xs">
                                </td>
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
                                <td class="px-5 py-3.5 text-xs text-on-surface whitespace-nowrap">
                                    <div class="font-medium text-on-surface">
                                        {{ $row->date_update ? $row->date_update->format('d M Y') : '-' }}
                                    </div>
                                    <div class="text-[10px] text-on-surface-variant font-medium">
                                        {{ $row->updated_at ? $row->updated_at->diffForHumans() : '' }}
                                    </div>
                                </td>

                                <!-- Col 6: Actions -->
                                <td class="px-5 py-3.5 text-center whitespace-nowrap">
                                    @if($row->sync_status === 'PROCESSED')
                                        <div class="flex items-center justify-center gap-2">
                                            @if($row->processed_product_id)
                                                <a href="{{ route('products.show', $row->processed_product_id) }}" target="_blank" class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-semibold bg-emerald-100 text-emerald-800 hover:bg-emerald-200 transition-colors" title="Lihat di Katalog Produk">
                                                    <span class="material-symbols-outlined text-[14px]">visibility</span>
                                                    <span>Lihat</span>
                                                </a>
                                            @else
                                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-semibold bg-emerald-100 text-emerald-800">
                                                    <span class="material-symbols-outlined text-[14px]">check</span>
                                                    <span>Tersalin</span>
                                                </span>
                                            @endif
                                            <button 
                                                type="button" 
                                                @click="openSingleModal('{{ $row->id }}', '{{ addslashes($row->second_item_number) }}', '{{ addslashes($row->description) }}')" 
                                                class="p-1 rounded-md text-on-surface-variant hover:text-primary hover:bg-surface-variant transition-colors" 
                                                title="Salin Ulang / Ubah Distribusi">
                                                <span class="material-symbols-outlined text-[16px]">refresh</span>
                                            </button>
                                        </div>
                                    @else
                                        <button 
                                            type="button" 
                                            @click="openSingleModal('{{ $row->id }}', '{{ addslashes($row->second_item_number) }}', '{{ addslashes($row->description) }}')" 
                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-bold bg-primary text-white hover:bg-primary/90 shadow-2xs transition-all cursor-pointer">
                                            <span class="material-symbols-outlined text-[15px]">content_copy</span>
                                            <span>Salin ke Produk</span>
                                        </button>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-5 py-12 text-center text-on-surface-variant">
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

    <!-- Floating Bulk Selection Bar (Seperti display Price Product Setting) -->
    <div x-show="selectedItemIds.length > 0" 
         x-cloak 
         class="fixed bottom-6 left-1/2 -translate-x-1/2 z-40 bg-slate-900 text-white rounded-2xl px-6 py-3.5 shadow-2xl flex items-center gap-4 border border-slate-700 animate-slide-up">
        <div class="flex items-center gap-2.5">
            <span class="w-7 h-7 rounded-lg bg-blue-600 text-white flex items-center justify-center font-bold text-xs shadow-xs">
                <span x-text="selectedItemIds.length"></span>
            </span>
            <span class="text-xs font-bold text-white"><span x-text="selectedItemIds.length"></span> Item Master JDE Dipilih</span>
        </div>
        <div class="h-4 w-[1px] bg-slate-700"></div>
        <div class="flex items-center gap-2">
            <button type="button" @click="selectedItemIds = []" class="px-3 py-1.5 rounded-lg text-xs font-semibold text-slate-300 hover:text-white hover:bg-slate-800 transition-colors">
                Batal
            </button>
            <button type="button" @click="openBulkModal()" class="px-4 py-1.5 rounded-lg text-xs font-bold bg-blue-600 hover:bg-blue-500 text-white flex items-center gap-1.5 shadow-sm transition-all cursor-pointer">
                <span class="material-symbols-outlined text-[16px]">share_location</span>
                <span>Salin & Distribusikan</span>
            </button>
        </div>
    </div>

    <!-- Modal Distribusi Salin Katalog JDE -->
    <div x-show="distributionModalOpen" 
         x-cloak 
         class="fixed inset-0 z-50 overflow-y-auto bg-black/60 flex items-center justify-center p-4 backdrop-blur-xs"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">
        
        <div class="bg-white rounded-2xl max-w-xl w-full p-6 shadow-2xl border border-slate-200 relative my-8"
             @click.away="distributionModalOpen = false">
            <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                <div class="flex items-center gap-2.5">
                    <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-700 flex items-center justify-center shadow-2xs">
                        <span class="material-symbols-outlined text-[24px]">share_location</span>
                    </div>
                    <div>
                        <h3 class="font-bold text-slate-900 text-base">Salin ke Katalog & Distribusi Produk</h3>
                        <p class="text-xs text-slate-500">Tentukan segmentasi channel dan toko tujuan produk ini</p>
                    </div>
                </div>
                <button type="button" @click="distributionModalOpen = false" class="text-slate-400 hover:text-slate-600 p-1 rounded-lg">
                    <span class="material-symbols-outlined text-[20px]">close</span>
                </button>
            </div>

            <div class="mt-4 space-y-4">
                <!-- Info Item Terpilih -->
                <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-200 flex items-center justify-between">
                    <div>
                        <span class="text-[10px] font-bold text-slate-500 uppercase tracking-wider block">Target Salin JDE</span>
                        <span class="text-xs font-bold text-slate-800" x-text="itemsSummary"></span>
                    </div>
                    <span class="px-2.5 py-1 rounded-full text-xs font-extrabold bg-blue-100 text-blue-800 shrink-0">
                        <span x-text="selectedItemIds.length"></span> Item
                    </span>
                </div>

                <!-- Pilihan Channel / Segmentasi -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-2">1. Pilih Segmentasi Channel Katalog:</label>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <label class="p-3.5 rounded-xl border cursor-pointer transition-all flex flex-col justify-between"
                               :class="distributionTargetChannel === 'store_dealer' ? 'border-blue-600 bg-blue-50/40 ring-2 ring-blue-600/20' : 'border-slate-200 hover:bg-slate-50'">
                            <div class="flex items-center gap-2 mb-1.5">
                                <input type="radio" value="store_dealer" x-model="distributionTargetChannel" class="text-blue-600 focus:ring-blue-500">
                                <span class="font-bold text-xs text-slate-800">🏢 Toko Mebel / B2B Dealer</span>
                            </div>
                            <p class="text-[11px] text-slate-500 pl-5">Disimpan ke tabel <code>store_products</code> khusus transaksi kasir & showroom toko mitra.</p>
                        </label>

                        <label class="p-3.5 rounded-xl border cursor-pointer transition-all flex flex-col justify-between"
                               :class="distributionTargetChannel === 'web_retail' ? 'border-blue-600 bg-blue-50/40 ring-2 ring-blue-600/20' : 'border-slate-200 hover:bg-slate-50'">
                            <div class="flex items-center gap-2 mb-1.5">
                                <input type="radio" value="web_retail" x-model="distributionTargetChannel" class="text-blue-600 focus:ring-blue-500">
                                <span class="font-bold text-xs text-slate-800">🌐 Online Retail (Web IMG)</span>
                            </div>
                            <p class="text-[11px] text-slate-500 pl-5">Disimpan ke tabel <code>products</code> untuk e-commerce resmi publik D2C.</p>
                        </label>
                    </div>
                </div>

                <!-- Bagian Distribusi Toko (Hanya jika Toko Mebel) -->
                <div x-show="distributionTargetChannel === 'store_dealer'" class="space-y-3 pt-2 border-t border-slate-100">
                    <label class="block text-xs font-bold text-slate-700">2. Pilih Distribusi Toko Tujuan:</label>
                    
                    <div class="space-y-2">
                        <label class="flex items-center gap-2 text-xs font-semibold text-slate-800 cursor-pointer">
                            <input type="radio" value="all" x-model="distributionScope" class="text-blue-600 focus:ring-blue-500">
                            <span>Distribusikan ke Semua Toko Binaan (Multi-Toko)</span>
                        </label>

                        <template x-if="currentStore">
                            <label class="flex items-center gap-2 text-xs font-semibold text-slate-800 cursor-pointer">
                                <input type="radio" value="current" x-model="distributionScope" class="text-blue-600 focus:ring-blue-500">
                                <span>Hanya ke Toko Aktif Saat Ini: <strong class="text-blue-700" x-text="currentStore.name"></strong></span>
                            </label>
                        </template>

                        <label class="flex items-center gap-2 text-xs font-semibold text-slate-800 cursor-pointer">
                            <input type="radio" value="specific" x-model="distributionScope" class="text-blue-600 focus:ring-blue-500">
                            <span>Pilih Toko Tertentu (Checklist Spesifik)</span>
                        </label>
                    </div>

                    <!-- Checklist Toko Spesifik -->
                    <div x-show="distributionScope === 'specific'" class="mt-2 p-3 rounded-xl border border-slate-200 bg-slate-50 space-y-2">
                        <div class="relative">
                            <span class="material-symbols-outlined absolute left-2.5 top-1/2 -translate-y-1/2 text-slate-400 text-[16px]">search</span>
                            <input type="text" x-model="storeSearch" placeholder="Cari nama atau No. AN8 toko..." class="w-full pl-8 pr-3 py-1.5 text-xs rounded-lg border border-slate-300 bg-white focus:outline-none focus:ring-1 focus:ring-blue-500">
                        </div>

                        <div class="max-h-40 overflow-y-auto divide-y divide-slate-100 pr-1">
                            <template x-for="s in filteredStores" :key="s.id">
                                <label class="flex items-center justify-between p-2 hover:bg-white rounded-lg cursor-pointer text-xs transition-colors">
                                    <div class="flex items-center gap-2">
                                        <input type="checkbox" :value="s.id" x-model="selectedStoreIds" class="w-4.5 h-4.5 rounded-md border-2 border-slate-400 checked:bg-blue-600 checked:border-blue-600 text-blue-600 focus:ring-2 focus:ring-blue-500/40 bg-white transition-all cursor-pointer">
                                        <span class="font-bold text-slate-800" x-text="s.name"></span>
                                    </div>
                                    <span class="text-[10px] font-mono font-semibold px-2 py-0.5 rounded bg-slate-200/60 text-slate-700" x-text="s.address_number ? 'AN8: ' + s.address_number : s.code"></span>
                                </label>
                            </template>
                        </div>
                        <div class="text-[11px] text-slate-500 pt-1 flex justify-between">
                            <span><span x-text="selectedStoreIds.length"></span> toko dipilih</span>
                            <button type="button" @click="selectedStoreIds = storesList.map(s => s.id)" class="text-blue-600 font-bold hover:underline">Pilih Semua</button>
                        </div>
                    </div>

                    <!-- Pengaturan Display & Kasir -->
                    <div class="pt-2 grid grid-cols-1 sm:grid-cols-2 gap-2">
                        <label class="flex items-center gap-2.5 p-2.5 rounded-xl border border-slate-200 bg-slate-50 hover:bg-white transition-colors cursor-pointer text-xs">
                            <input type="checkbox" x-model="isFloorDisplay" class="w-5 h-5 rounded-md border-2 border-slate-400 checked:bg-blue-600 checked:border-blue-600 text-blue-600 focus:ring-2 focus:ring-blue-500/40 bg-white transition-all cursor-pointer">
                            <div>
                                <span class="font-bold text-slate-800 block">Pajang di Showroom</span>
                                <span class="text-[10px] text-slate-500 block">Floor display aktif</span>
                            </div>
                        </label>
                        <label class="flex items-center gap-2.5 p-2.5 rounded-xl border border-slate-200 bg-slate-50 hover:bg-white transition-colors cursor-pointer text-xs">
                            <input type="checkbox" x-model="isActiveForPos" class="w-5 h-5 rounded-md border-2 border-slate-400 checked:bg-blue-600 checked:border-blue-600 text-blue-600 focus:ring-2 focus:ring-blue-500/40 bg-white transition-all cursor-pointer">
                            <div>
                                <span class="font-bold text-slate-800 block">Aktif untuk POS Kasir</span>
                                <span class="text-[10px] text-slate-500 block">Dapat ditransaksikan</span>
                            </div>
                        </label>
                    </div>
                </div>

                <!-- Footer Buttons -->
                <div class="flex items-center justify-end gap-2.5 pt-4 border-t border-slate-100">
                    <button type="button" @click="distributionModalOpen = false" class="px-4 py-2 rounded-xl text-xs font-bold text-slate-600 hover:bg-slate-100 transition-colors">
                        Batal
                    </button>
                    <button type="button" @click="submitDistribution()" :disabled="isLoading" class="px-5 py-2 rounded-xl text-xs font-bold bg-blue-600 hover:bg-blue-700 text-white shadow-sm flex items-center gap-1.5 transition-colors cursor-pointer">
                        <span class="material-symbols-outlined text-[16px]" :class="{ 'animate-spin': isLoading }">sync</span>
                        <span x-text="isLoading ? 'Memproses Salin...' : 'Salin & Distribusikan (' + selectedItemIds.length + ')'"></span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function jdeSyncHandler() {
    return {
        isLoading: false,
        selectedItemIds: [],
        availableRowIds: @json($tab === 'item_master' ? $data->pluck('id')->toArray() : []),
        storesList: @json($availableStores ?? []),
        currentStore: @json(session('current_store')),
        distributionModalOpen: false,
        distributionTargetChannel: 'store_dealer',
        distributionScope: 'all',
        selectedStoreIds: [],
        isFloorDisplay: true,
        isActiveForPos: true,
        storeSearch: '',
        itemsSummary: '',

        get isAllSelected() {
            return this.availableRowIds.length > 0 && this.selectedItemIds.length === this.availableRowIds.length;
        },

        toggleSelectAll(e) {
            if (e.target.checked) {
                this.selectedItemIds = [...this.availableRowIds];
            } else {
                this.selectedItemIds = [];
            }
        },

        get filteredStores() {
            if (!this.storeSearch) return this.storesList;
            const q = this.storeSearch.toLowerCase();
            return this.storesList.filter(s => 
                (s.name && s.name.toLowerCase().includes(q)) || 
                (s.address_number && String(s.address_number).includes(q)) ||
                (s.code && s.code.toLowerCase().includes(q))
            );
        },

        openSingleModal(id, sku, desc) {
            this.selectedItemIds = [id];
            this.itemsSummary = `${sku} - ${desc}`;
            this.distributionModalOpen = true;
        },

        openBulkModal() {
            if (this.selectedItemIds.length === 0) return;
            this.itemsSummary = `${this.selectedItemIds.length} item JDE terpilih`;
            this.distributionModalOpen = true;
        },

        batchCopy() {
            if (this.selectedItemIds.length > 0) {
                this.openBulkModal();
            } else if (this.availableRowIds.length > 0) {
                this.selectedItemIds = [...this.availableRowIds];
                this.itemsSummary = `${this.selectedItemIds.length} item JDE terpilih (Halaman Ini)`;
                this.distributionModalOpen = true;
            } else {
                this.itemsSummary = 'Semua Item JDE Ber-Brand';
                this.distributionModalOpen = true;
            }
        },

        async submitDistribution() {
            if (this.selectedItemIds.length === 0) {
                alert('Silakan pilih minimal 1 item JDE untuk disalin.');
                return;
            }

            let storeIds = [];
            if (this.distributionTargetChannel === 'store_dealer') {
                if (this.distributionScope === 'current' && this.currentStore) {
                    storeIds = [this.currentStore.id];
                } else if (this.distributionScope === 'specific') {
                    if (this.selectedStoreIds.length === 0) {
                        alert('Silakan pilih minimal 1 toko tujuan distribusi.');
                        return;
                    }
                    storeIds = this.selectedStoreIds;
                }
            }

            this.isLoading = true;
            try {
                const response = await fetch("{{ route('admin.jde.item-master.copy-batch') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        ids: this.selectedItemIds,
                        target_channel: this.distributionTargetChannel,
                        store_ids: storeIds,
                        is_floor_display: this.isFloorDisplay,
                        is_active_for_pos: this.isActiveForPos,
                    })
                });

                const res = await response.json();
                this.distributionModalOpen = false;

                if (res.success) {
                    if (typeof window.showSuccessPopup === 'function') {
                        await window.showSuccessPopup(res.message);
                    } else if (typeof Swal !== 'undefined') {
                        await Swal.fire('Berhasil!', res.message, 'success');
                    } else {
                        alert(res.message);
                    }
                    window.location.reload();
                } else {
                    if (typeof window.showErrorPopup === 'function') {
                        window.showErrorPopup(res.message, 'Gagal Menyalin');
                    } else {
                        alert(res.message);
                    }
                }
            } catch (err) {
                alert('Kesalahan jaringan: ' + err.message);
            } finally {
                this.isLoading = false;
            }
        },

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
