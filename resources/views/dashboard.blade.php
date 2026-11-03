@extends('layouts.app')

@section('title', 'Dashboard ' . ($roleType === 'sales' ? 'Sales Lapangan' : ($roleType === 'store' ? 'Mitra Toko' : 'Manajemen')))

@section('content')
    @php
        $hour = (int) now()->format('H');
        $greeting = $hour < 11 ? 'Selamat Pagi' : ($hour < 15 ? 'Selamat Siang' : ($hour < 19 ? 'Selamat Sore' : 'Selamat Malam'));
    @endphp

    {{-- ========================================================================= --}}
    {{-- 1. CLEAN & SOPHISTICATED HEADER (BERSIH, PROFESIONAL, NON-TEKNIS)         --}}
    {{-- ========================================================================= --}}
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2.5">
                <h1 class="text-2xl font-black text-slate-800 dark:text-white tracking-tight">
                    {{ $greeting }}, {{ $user?->name ?? 'Admin User' }}
                </h1>
                @if($roleType === 'sales')
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-100 text-amber-800 dark:bg-amber-950/50 dark:text-amber-300 border border-amber-300 dark:border-amber-800">
                        Sales Lapangan
                    </span>
                @elseif($roleType === 'store')
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-blue-100 text-blue-800 dark:bg-blue-950/50 dark:text-blue-300 border border-blue-300 dark:border-blue-800">
                        Mitra Toko
                    </span>
                @else
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-indigo-100 text-indigo-800 dark:bg-indigo-950/50 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-800">
                        Manajemen
                    </span>
                @endif
            </div>
            <p class="text-xs text-slate-500 dark:text-stone-400 mt-1">
                @if($roleType === 'sales')
                    Monitoring toko binaan dan pesanan penjualan secara langsung.
                @elseif($roleType === 'store')
                    Kelola penjualan kasir, kasur pajangan showroom, dan sisa limit belanja toko Anda.
                @else
                    Ringkasan aktivitas penjualan, kinerja mitra toko, dan pembaruan produk.
                @endif
            </p>
        </div>

        <!-- Right Controls: Filter Bulan & Quick Action -->
        <div class="flex items-center gap-2.5">
            <form method="GET" class="flex items-center gap-2">
                <div class="flex items-center gap-1.5 bg-white dark:bg-surface-container border border-slate-200 dark:border-stone-800 rounded-xl px-3 py-2 shadow-2xs hover:border-slate-300 dark:hover:border-stone-700 transition-colors">
                    <span class="material-symbols-outlined text-[18px] text-slate-400 dark:text-stone-400">calendar_month</span>
                    <input type="month" name="month" value="{{ $month }}" 
                           class="bg-transparent text-slate-700 dark:text-stone-200 text-xs font-bold focus:outline-none cursor-pointer tracking-wider [color-scheme:light] dark:[color-scheme:dark]" 
                           onchange="this.form.submit()">
                </div>
            </form>
            @if($roleType === 'admin')
                <a href="{{ route('admin.jde.index') }}" 
                   class="px-3.5 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs shadow-2xs transition-all flex items-center gap-1.5">
                    <span class="material-symbols-outlined text-[16px]">sync_alt</span>
                    <span>Produk Baru dari Pusat</span>
                </a>
            @elseif($roleType === 'sales')
                <a href="{{ route('stores.index') }}" 
                   class="px-3.5 py-2 rounded-xl bg-amber-600 hover:bg-amber-700 text-white font-bold text-xs shadow-2xs transition-all flex items-center gap-1.5">
                    <span class="material-symbols-outlined text-[16px]">store</span>
                    <span>Kelola Toko</span>
                </a>
            @endif
        </div>
    </div>

    {{-- ========================================================================= --}}
    {{-- DASHBOARD KHUSUS SALES CANVASSER --}}
    {{-- ========================================================================= --}}
    @if($roleType === 'sales')
        <!-- Sales Representative Card -->
        <div class="mb-6 p-5 rounded-3xl bg-gradient-to-r from-amber-500 via-amber-600 to-orange-600 text-white shadow-lg shadow-amber-500/10 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 rounded-2xl bg-white/20 backdrop-blur-md flex items-center justify-center font-bold text-white shrink-0 shadow-inner">
                    <span class="material-symbols-outlined text-[32px]">badge</span>
                </div>
                <div>
                    <span class="px-2 py-0.5 rounded bg-black/25 text-amber-100 text-[11px] font-mono font-bold tracking-wider uppercase">
                        Kode Sales: {{ $user->sales_code ?? 'SC001' }}
                    </span>
                    <h2 class="text-xl font-black mt-1">{{ $user->name }}</h2>
                    <p class="text-xs text-amber-100/90 mt-0.5 flex items-center gap-3">
                        <span><span class="material-symbols-outlined text-[14px] align-middle">mail</span> {{ $user->email }}</span>
                        @if($user->phone)
                            <span><span class="material-symbols-outlined text-[14px] align-middle">call</span> {{ $user->phone }}</span>
                        @endif
                    </p>
                </div>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('stores.index') }}" class="px-4 py-2.5 rounded-2xl bg-white text-amber-900 font-extrabold text-xs hover:bg-amber-50 shadow-sm transition-all flex items-center gap-1.5">
                    <span class="material-symbols-outlined text-[18px]">store</span>
                    <span>Kelola Toko Binaan</span>
                </a>
            </div>
        </div>

        <!-- Sales KPI Metrics -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
            <div class="bg-white dark:bg-surface-container p-5 rounded-3xl shadow-sm border border-slate-200/80 dark:border-stone-800/80 hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-slate-500 dark:text-stone-400 uppercase tracking-wider">Toko Binaan</span>
                    <div class="w-10 h-10 rounded-2xl bg-amber-50 dark:bg-amber-950/40 text-amber-600 dark:text-amber-400 flex items-center justify-center shadow-xs">
                        <span class="material-symbols-outlined text-[22px]">storefront</span>
                    </div>
                </div>
                <h3 class="text-3xl font-black text-slate-800 dark:text-white mt-2">{{ number_format($assignedStores->count()) }}</h3>
                <p class="text-[11px] text-slate-500 dark:text-stone-400 mt-1">Toko aktif yang dinaungi</p>
            </div>

            <div class="bg-white dark:bg-surface-container p-5 rounded-3xl shadow-sm border border-slate-200/80 dark:border-stone-800/80 hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-slate-500 dark:text-stone-400 uppercase tracking-wider">Omset Binaan ({{ $month }})</span>
                    <div class="w-10 h-10 rounded-2xl bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shadow-xs">
                        <span class="material-symbols-outlined text-[22px]">payments</span>
                    </div>
                </div>
                <h3 class="text-2xl font-black text-emerald-600 dark:text-emerald-400 mt-2">Rp {{ number_format($salesTotalRevenue, 0, ',', '.') }}</h3>
                <p class="text-[11px] text-slate-500 dark:text-stone-400 mt-1">Total nilai order toko binaan</p>
            </div>

            <div class="bg-white dark:bg-surface-container p-5 rounded-3xl shadow-sm border border-slate-200/80 dark:border-stone-800/80 hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-slate-500 dark:text-stone-400 uppercase tracking-wider">Total Order Bulan Ini</span>
                    <div class="w-10 h-10 rounded-2xl bg-blue-50 dark:bg-blue-950/40 text-blue-600 dark:text-blue-400 flex items-center justify-center shadow-xs">
                        <span class="material-symbols-outlined text-[22px]">shopping_cart</span>
                    </div>
                </div>
                <h3 class="text-3xl font-black text-blue-600 dark:text-blue-400 mt-2">{{ number_format($salesTotalOrders) }}</h3>
                <p class="text-[11px] text-slate-500 dark:text-stone-400 mt-1">Transaksi terbit bulan ini</p>
            </div>

            <div class="bg-white dark:bg-surface-container p-5 rounded-3xl shadow-sm border border-slate-200/80 dark:border-stone-800/80 hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-slate-500 dark:text-stone-400 uppercase tracking-wider">Toko Aktif Order</span>
                    <div class="w-10 h-10 rounded-2xl bg-purple-50 dark:bg-purple-950/40 text-purple-600 dark:text-purple-400 flex items-center justify-center shadow-xs">
                        <span class="material-symbols-outlined text-[22px]">trending_up</span>
                    </div>
                </div>
                <h3 class="text-3xl font-black text-purple-600 dark:text-purple-400 mt-2">{{ $activeStoresCount }} / {{ $assignedStores->count() }}</h3>
                <p class="text-[11px] text-slate-500 dark:text-stone-400 mt-1">Toko dengan transaksi di bulan ini</p>
            </div>
        </div>

        <!-- Daftar Toko Binaan Sales -->
        <div class="mb-8">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-base font-black text-slate-800 dark:text-white flex items-center gap-2">
                    <span class="material-symbols-outlined text-[22px] text-amber-600 dark:text-amber-400">store</span>
                    <span>Monitoring Toko Binaan & Plafon Kredit</span>
                </h2>
                <a href="{{ route('stores.index') }}" class="text-xs font-bold text-indigo-600 dark:text-indigo-400 hover:underline">Lihat Semua Toko &rarr;</a>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @forelse($assignedStores as $st)
                    <div class="bg-white dark:bg-surface-container rounded-3xl p-5 border border-slate-200/80 dark:border-stone-800/80 shadow-sm hover:shadow-md transition-all">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <div class="flex items-center gap-2">
                                    <h3 class="font-bold text-slate-800 dark:text-white text-base leading-snug">{{ $st['name'] }}</h3>
                                    <span class="text-[10px] font-black px-2 py-0.5 rounded-full bg-blue-50 dark:bg-blue-950/40 text-blue-700 dark:text-blue-300 border border-blue-200 dark:border-blue-800">{{ $st['tier_name'] }}</span>
                                </div>
                                <p class="text-xs font-mono font-medium text-slate-500 dark:text-stone-400 mt-0.5">
                                    No. Mitra: {{ $st['address_number'] }} &bull; Telp: {{ $st['phone'] }}
                                </p>
                            </div>
                            <span class="px-2.5 py-1 rounded-xl text-xs font-extrabold {{ $st['order_count'] > 0 ? 'bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800' : 'bg-slate-50 dark:bg-stone-800 text-slate-600 dark:text-stone-300' }}">
                                {{ $st['order_count'] }} Order (Rp {{ number_format($st['order_revenue'], 0, ',', '.') }})
                            </span>
                        </div>

                        <!-- Progress Limit Belanja Toko -->
                        <div class="mt-4 pt-3 border-t border-slate-100 dark:border-stone-800">
                            <div class="flex justify-between text-xs mb-1">
                                <span class="text-slate-500 dark:text-stone-400">Limit Belanja: <strong class="text-slate-700 dark:text-stone-200">Rp {{ number_format($st['credit_limit'], 0, ',', '.') }}</strong></span>
                                <span class="font-bold text-slate-700 dark:text-stone-200">Sisa Limit: Rp {{ number_format($st['available_credit'], 0, ',', '.') }}</span>
                            </div>
                            <div class="w-full bg-slate-100 dark:bg-stone-800 rounded-full h-2 overflow-hidden">
                                <div class="h-full rounded-full transition-all {{ $st['utilization'] > 85 ? 'bg-rose-500' : ($st['utilization'] > 60 ? 'bg-amber-500' : 'bg-indigo-600') }}" style="width: {{ min(100, $st['utilization']) }}%"></div>
                            </div>
                            <div class="flex justify-between items-center text-[10px] text-slate-400 dark:text-stone-400 mt-1">
                                <span>Terpakai: Rp {{ number_format($st['outstanding'], 0, ',', '.') }}</span>
                                <span class="font-bold">{{ $st['utilization'] }}% Limit Terpakai</span>
                            </div>
                        </div>

                        <div class="mt-4 pt-3 border-t border-slate-100 dark:border-stone-800 flex items-center justify-between gap-2">
                            <a href="{{ route('products.index', ['view' => 'store', 'store_id' => $st['id']]) }}" class="px-3.5 py-2 rounded-xl border border-slate-200 dark:border-stone-700 text-slate-700 dark:text-stone-200 hover:bg-slate-50 dark:hover:bg-stone-800 text-xs font-bold flex items-center gap-1 transition-colors">
                                <span class="material-symbols-outlined text-[16px]">inventory_2</span>
                                <span>Katalog Toko</span>
                            </a>
                            <a href="{{ route('stores.impersonate', $st['id']) }}" class="px-3.5 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold flex items-center gap-1 transition-all shadow-sm">
                                <span class="material-symbols-outlined text-[16px]">touch_app</span>
                                <span>Kunjungi & Buat Order</span>
                            </a>
                        </div>
                    </div>
                @empty
                    <div class="col-span-2 p-8 text-center bg-white dark:bg-surface-container rounded-3xl border border-slate-200 dark:border-stone-800">
                        <span class="material-symbols-outlined text-[40px] text-slate-300 dark:text-stone-600">storefront</span>
                        <p class="text-sm font-bold text-slate-600 dark:text-stone-300 mt-2">Belum ada toko yang ditugaskan ke sales ini.</p>
                        <p class="text-xs text-slate-400 dark:text-stone-500 mt-0.5">Hubungi Tim Manajemen Pusat untuk penugasan mitra toko binaan.</p>
                    </div>
                @endforelse
            </div>
        </div>

    {{-- ========================================================================= --}}
    {{-- DASHBOARD KHUSUS MITRA TOKO / SPG TOKO --}}
    {{-- ========================================================================= --}}
    @elseif($roleType === 'store')
        @if($store)
            <!-- Store Hero Banner -->
            <div class="mb-6 p-6 rounded-3xl bg-gradient-to-r from-blue-700 via-indigo-800 to-indigo-900 text-white shadow-lg flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div class="flex items-center gap-4">
                    <div class="w-14 h-14 rounded-2xl bg-white/20 backdrop-blur-md flex items-center justify-center font-bold text-white shrink-0 shadow-inner">
                        <span class="material-symbols-outlined text-[32px]">storefront</span>
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="px-2 py-0.5 rounded bg-black/25 text-blue-100 text-[11px] font-mono font-bold tracking-wider uppercase">
                                No. Mitra: {{ $store->address_number ?? $store->code }}
                            </span>
                            <span class="px-2 py-0.5 rounded bg-blue-500/40 text-white text-[11px] font-bold">
                                Kategori: {{ $store->tier?->name ?? 'Reguler' }}
                            </span>
                        </div>
                        <h2 class="text-xl font-black mt-1">{{ $store->name }}</h2>
                        <p class="text-xs text-blue-100/90 mt-0.5">
                            {{ $store->address ?? 'Showroom Toko Resmi Mitra IMG' }} &bull; Telp: {{ $store->phone ?? '-' }}
                        </p>
                    </div>
                </div>
                <div class="flex items-center gap-2.5">
                    <a href="{{ route('orders.index') }}" class="px-4 py-2.5 rounded-2xl bg-emerald-500 hover:bg-emerald-600 text-white font-extrabold text-xs shadow-md shadow-emerald-500/20 transition-all flex items-center gap-1.5">
                        <span class="material-symbols-outlined text-[18px]">point_of_sale</span>
                        <span>Pesanan & Kasir</span>
                    </a>
                    <a href="{{ route('products.index', ['view' => 'store']) }}" class="px-4 py-2.5 rounded-2xl bg-white/10 hover:bg-white/20 text-white font-bold text-xs border border-white/20 transition-all flex items-center gap-1.5">
                        <span class="material-symbols-outlined text-[18px]">bed</span>
                        <span>Katalog Kasur</span>
                    </a>
                </div>
            </div>

            <!-- Plafon & Display Highlight Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                <div class="bg-white dark:bg-surface-container p-5 rounded-3xl shadow-sm border border-slate-200/80 dark:border-stone-800/80">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold text-slate-500 dark:text-stone-400 uppercase tracking-wider">Sisa Limit Belanja</span>
                        <div class="w-10 h-10 rounded-2xl bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shadow-xs">
                            <span class="material-symbols-outlined text-[22px]">account_balance_wallet</span>
                        </div>
                    </div>
                    @php
                        $availCredit = max(0, (float)$store->credit_limit - (float)$store->outstanding_balance);
                    @endphp
                    <h3 class="text-2xl font-black text-emerald-600 dark:text-emerald-400 mt-2">Rp {{ number_format($availCredit, 0, ',', '.') }}</h3>
                    <p class="text-[11px] text-slate-500 dark:text-stone-400 mt-1">Dari total limit Rp {{ number_format((float)$store->credit_limit, 0, ',', '.') }}</p>
                </div>

                <div class="bg-white dark:bg-surface-container p-5 rounded-3xl shadow-sm border border-slate-200/80 dark:border-stone-800/80">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold text-slate-500 dark:text-stone-400 uppercase tracking-wider">Pajangan Showroom</span>
                        <div class="w-10 h-10 rounded-2xl bg-blue-50 dark:bg-blue-950/40 text-blue-600 dark:text-blue-400 flex items-center justify-center shadow-xs">
                            <span class="material-symbols-outlined text-[22px]">bed</span>
                        </div>
                    </div>
                    <h3 class="text-3xl font-black text-blue-600 dark:text-blue-400 mt-2">{{ number_format($storeMetrics['floor_display']) }} Unit</h3>
                    <p class="text-[11px] text-slate-500 dark:text-stone-400 mt-1">Kasur terpajang di toko</p>
                </div>

                <div class="bg-white dark:bg-surface-container p-5 rounded-3xl shadow-sm border border-slate-200/80 dark:border-stone-800/80">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold text-slate-500 dark:text-stone-400 uppercase tracking-wider">Produk Aktif Kasir</span>
                        <div class="w-10 h-10 rounded-2xl bg-indigo-50 dark:bg-indigo-950/40 text-indigo-600 dark:text-indigo-400 flex items-center justify-center shadow-xs">
                            <span class="material-symbols-outlined text-[22px]">shopping_cart_checkout</span>
                        </div>
                    </div>
                    <h3 class="text-3xl font-black text-indigo-600 dark:text-indigo-400 mt-2">{{ number_format($storeMetrics['pos_active']) }} Produk</h3>
                    <p class="text-[11px] text-slate-500 dark:text-stone-400 mt-1">Dari {{ $storeMetrics['total_products'] }} total produk toko</p>
                </div>

                <div class="bg-white dark:bg-surface-container p-5 rounded-3xl shadow-sm border border-slate-200/80 dark:border-stone-800/80">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold text-slate-500 dark:text-stone-400 uppercase tracking-wider">Penjualan Kasir Hari Ini</span>
                        <div class="w-10 h-10 rounded-2xl bg-purple-50 dark:bg-purple-950/40 text-purple-600 dark:text-purple-400 flex items-center justify-center shadow-xs">
                            <span class="material-symbols-outlined text-[22px]">receipt_long</span>
                        </div>
                    </div>
                    <h3 class="text-2xl font-black text-purple-600 dark:text-purple-400 mt-2">Rp {{ number_format($storeMetrics['today_revenue'], 0, ',', '.') }}</h3>
                    <p class="text-[11px] text-slate-500 dark:text-stone-400 mt-1">{{ $storeMetrics['today_orders'] }} transaksi kasir hari ini</p>
                </div>
            </div>
        @endif

    {{-- ========================================================================= --}}
    {{-- DASHBOARD SUPER ADMIN / ADMIN PUSAT (BENTO GRID 2.0 & CATCHY METRICS)     --}}
    {{-- ========================================================================= --}}
    @else
        <!-- Section Header -->
        <div class="flex items-center justify-between mb-4">
            <div>
                <h2 class="text-base font-black text-slate-800 dark:text-white tracking-tight flex items-center gap-2">
                    <span class="material-symbols-outlined text-[22px] text-indigo-600 dark:text-indigo-400">monitoring</span>
                    <span>Ringkasan Kinerja Penjualan & Toko</span>
                </h2>
                <p class="text-xs text-slate-500 dark:text-stone-400 mt-0.5">Statistik data penjualan dan operasional mitra toko secara langsung.</p>
            </div>
            <span class="text-xs font-bold text-slate-400 dark:text-stone-300 bg-slate-100 dark:bg-stone-800 px-3 py-1 rounded-full border border-slate-200/60 dark:border-stone-700/60">Periode: {{ $month }}</span>
        </div>

        <!-- Bento Grid 2.0 KPI Highlights -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4 mb-8">
            <!-- 1. Mitra Toko Aktif -->
            <div class="bg-white dark:bg-surface-container rounded-3xl p-5 border border-slate-200/80 dark:border-stone-800/80 shadow-[0_4px_20px_-4px_rgba(0,0,0,0.04)] dark:shadow-none hover:shadow-xl hover:-translate-y-1 transition-all duration-300 relative overflow-hidden group">
                <div class="absolute top-0 inset-x-0 h-1 bg-gradient-to-r from-blue-500 to-indigo-600"></div>
                <div class="flex items-center justify-between">
                    <span class="text-[11px] font-black uppercase tracking-wider text-slate-400 dark:text-stone-400">Mitra Toko Aktif</span>
                    <div class="w-11 h-11 rounded-2xl bg-gradient-to-tr from-blue-600 to-indigo-600 text-white flex items-center justify-center shadow-md shadow-blue-500/25 group-hover:scale-110 transition-transform">
                        <span class="material-symbols-outlined text-[22px]">storefront</span>
                    </div>
                </div>
                <h3 class="text-3xl font-black text-slate-800 dark:text-white mt-3">{{ number_format($adminKpis['total_stores']) }}</h3>
                <div class="mt-3 flex items-center justify-between text-[11px]">
                    <span class="text-slate-500 dark:text-stone-400 font-medium">Jaringan Toko Mitra</span>
                    <span class="font-extrabold text-blue-600 dark:text-blue-300 bg-blue-50 dark:bg-blue-950/50 px-2 py-0.5 rounded-full border border-blue-100 dark:border-blue-900/60">Nasional</span>
                </div>
            </div>

            <!-- 2. Sales Lapangan -->
            <div class="bg-white dark:bg-surface-container rounded-3xl p-5 border border-slate-200/80 dark:border-stone-800/80 shadow-[0_4px_20px_-4px_rgba(0,0,0,0.04)] dark:shadow-none hover:shadow-xl hover:-translate-y-1 transition-all duration-300 relative overflow-hidden group">
                <div class="absolute top-0 inset-x-0 h-1 bg-gradient-to-r from-amber-400 to-orange-500"></div>
                <div class="flex items-center justify-between">
                    <span class="text-[11px] font-black uppercase tracking-wider text-slate-400 dark:text-stone-400">Sales Lapangan</span>
                    <div class="w-11 h-11 rounded-2xl bg-gradient-to-tr from-amber-500 to-orange-500 text-white flex items-center justify-center shadow-md shadow-amber-500/25 group-hover:scale-110 transition-transform">
                        <span class="material-symbols-outlined text-[22px]">badge</span>
                    </div>
                </div>
                <h3 class="text-3xl font-black text-slate-800 dark:text-white mt-3">{{ number_format($adminKpis['total_sales']) }}</h3>
                <div class="mt-3 flex items-center justify-between text-[11px]">
                    <span class="text-slate-500 dark:text-stone-400 font-medium">Tim Penjualan</span>
                    <span class="font-extrabold text-amber-700 dark:text-amber-300 bg-amber-50 dark:bg-amber-950/50 px-2 py-0.5 rounded-full border border-amber-100 dark:border-amber-900/60">Aktif</span>
                </div>
            </div>

            <!-- 3. Katalog Produk Online -->
            <div class="bg-white dark:bg-surface-container rounded-3xl p-5 border border-slate-200/80 dark:border-stone-800/80 shadow-[0_4px_20px_-4px_rgba(0,0,0,0.04)] dark:shadow-none hover:shadow-xl hover:-translate-y-1 transition-all duration-300 relative overflow-hidden group">
                <div class="absolute top-0 inset-x-0 h-1 bg-gradient-to-r from-emerald-400 to-teal-500"></div>
                <div class="flex items-center justify-between">
                    <span class="text-[11px] font-black uppercase tracking-wider text-slate-400 dark:text-stone-400">Katalog Produk Online</span>
                    <div class="w-11 h-11 rounded-2xl bg-gradient-to-tr from-emerald-500 to-teal-600 text-white flex items-center justify-center shadow-md shadow-emerald-500/25 group-hover:scale-110 transition-transform">
                        <span class="material-symbols-outlined text-[22px]">shopping_bag</span>
                    </div>
                </div>
                <h3 class="text-3xl font-black text-slate-800 dark:text-white mt-3">{{ number_format($adminKpis['total_web_products']) }}</h3>
                <div class="mt-3 flex items-center justify-between text-[11px]">
                    <span class="text-slate-500 dark:text-stone-400 font-medium">Produk Siap Jual</span>
                    <span class="font-extrabold text-emerald-700 dark:text-emerald-300 bg-emerald-50 dark:bg-emerald-950/50 px-2 py-0.5 rounded-full border border-emerald-100 dark:border-emerald-900/60">Tayang Online</span>
                </div>
            </div>

            <!-- 4. Produk Baru dari Pusat -->
            <div class="bg-white dark:bg-surface-container rounded-3xl p-5 border border-slate-200/80 dark:border-stone-800/80 shadow-[0_4px_20px_-4px_rgba(0,0,0,0.04)] dark:shadow-none hover:shadow-xl hover:-translate-y-1 transition-all duration-300 relative overflow-hidden group">
                <div class="absolute top-0 inset-x-0 h-1 bg-gradient-to-r from-purple-500 to-pink-500"></div>
                <div class="flex items-center justify-between">
                    <span class="text-[11px] font-black uppercase tracking-wider text-slate-400 dark:text-stone-400">Produk Baru dari Pusat</span>
                    <div class="w-11 h-11 rounded-2xl bg-gradient-to-tr from-purple-600 to-pink-500 text-white flex items-center justify-center shadow-md shadow-purple-500/25 group-hover:scale-110 transition-transform">
                        <span class="material-symbols-outlined text-[22px]">sync_alt</span>
                    </div>
                </div>
                <h3 class="text-3xl font-black text-purple-700 dark:text-purple-300 mt-3">{{ number_format($adminKpis['total_jde_staging']) }}</h3>
                <div class="mt-3 flex items-center justify-between text-[11px]">
                    <span class="text-slate-500 dark:text-stone-400 font-medium">Perlu Ditinjau</span>
                    <a href="{{ route('admin.jde.index') }}" class="font-extrabold text-purple-600 dark:text-purple-400 hover:underline">Lihat &rarr;</a>
                </div>
            </div>

            <!-- 5. Total Penjualan Selesai -->
            <div class="bg-white dark:bg-surface-container rounded-3xl p-5 border border-slate-200/80 dark:border-stone-800/80 shadow-[0_4px_20px_-4px_rgba(0,0,0,0.04)] dark:shadow-none hover:shadow-xl hover:-translate-y-1 transition-all duration-300 relative overflow-hidden group">
                <div class="absolute top-0 inset-x-0 h-1 bg-gradient-to-r from-indigo-700 to-slate-900"></div>
                <div class="flex items-center justify-between">
                    <span class="text-[11px] font-black uppercase tracking-wider text-slate-400 dark:text-stone-400">Penjualan Selesai</span>
                    <div class="w-11 h-11 rounded-2xl bg-gradient-to-tr from-indigo-600 to-slate-900 text-white flex items-center justify-center shadow-md shadow-indigo-600/25 group-hover:scale-110 transition-transform">
                        <span class="material-symbols-outlined text-[22px]">account_balance_wallet</span>
                    </div>
                </div>
                <h3 class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white mt-3 truncate" title="Rp {{ number_format($adminKpis['total_revenue'], 0, ',', '.') }}">
                    Rp {{ number_format($adminKpis['total_revenue'], 0, ',', '.') }}
                </h3>
                <div class="mt-3 flex items-center justify-between text-[11px]">
                    <span class="text-slate-500 dark:text-stone-400 font-medium">Diterima Pelanggan</span>
                    <span class="font-extrabold text-indigo-700 dark:text-indigo-300 bg-indigo-50 dark:bg-indigo-950/50 px-2 py-0.5 rounded-full border border-indigo-100 dark:border-indigo-900/60">{{ $month }}</span>
                </div>
            </div>
        </div>

        {{-- ========================================================================= --}}
        {{-- ORDER PIPELINE JOURNEY (CATCHY INTERACTIVE PROCESS FLOW)                 --}}
        {{-- ========================================================================= --}}
        <div class="bg-white dark:bg-surface-container rounded-3xl p-6 border border-slate-200/80 dark:border-stone-800/80 shadow-sm mb-8">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-6">
                <div>
                    <h2 class="text-base font-black text-slate-800 dark:text-white tracking-tight flex items-center gap-2">
                        <span class="material-symbols-outlined text-[22px] text-blue-600 dark:text-blue-400">local_shipping</span>
                        <span>Alur Pemrosesan Pesanan</span>
                    </h2>
                    <p class="text-xs text-slate-500 dark:text-stone-400 mt-0.5">Status pesanan dari transaksi masuk hingga pesanan diterima pelanggan.</p>
                </div>
                <div class="flex items-center gap-2">
                    <span class="text-xs font-bold text-slate-600 dark:text-stone-300 bg-slate-100 dark:bg-stone-800 px-3 py-1.5 rounded-xl border border-slate-200/60 dark:border-stone-700/60">
                        Total Pesanan: <strong class="text-slate-900 dark:text-white">{{ number_format($stats['total'] ?? 0) }} Pesanan</strong>
                    </span>
                </div>
            </div>

            <!-- Connected Visual Pipeline Bar -->
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3 mb-4">
                <!-- 1. Keranjang -->
                <div class="p-3.5 rounded-2xl border border-slate-200 dark:border-stone-700 bg-slate-50/50 dark:bg-stone-800/40 hover:bg-slate-50 dark:hover:bg-stone-800/80 transition-all text-center group">
                    <div class="flex items-center justify-center gap-1.5 text-[10px] font-bold text-slate-500 dark:text-stone-400 uppercase tracking-wider mb-1">
                        <span class="w-2 h-2 rounded-full bg-slate-400"></span>
                        <span>1. Keranjang</span>
                    </div>
                    <span class="text-2xl font-black text-slate-700 dark:text-stone-200 group-hover:scale-105 transition-transform block">
                        {{ number_format($stats['draft'] ?? 0) }}
                    </span>
                    <span class="text-[10px] text-slate-400 dark:text-stone-400 mt-0.5 block">Belum Checkout</span>
                </div>

                <!-- 2. Menunggu Bayar -->
                <div class="p-3.5 rounded-2xl border border-amber-200 dark:border-amber-900/50 bg-amber-50/40 dark:bg-amber-950/30 hover:bg-amber-50 dark:hover:bg-amber-950/50 transition-all text-center group">
                    <div class="flex items-center justify-center gap-1.5 text-[10px] font-bold text-amber-700 dark:text-amber-400 uppercase tracking-wider mb-1">
                        <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                        <span>2. Menunggu Bayar</span>
                    </div>
                    <span class="text-2xl font-black text-amber-600 dark:text-amber-300 group-hover:scale-105 transition-transform block">
                        {{ number_format($stats['pending'] ?? 0) }}
                    </span>
                    <span class="text-[10px] text-amber-600/80 dark:text-amber-400/80 mt-0.5 block">Menunggu Pembayaran</span>
                </div>

                <!-- 3. Dikonfirmasi -->
                <div class="p-3.5 rounded-2xl border border-blue-200 dark:border-blue-900/50 bg-blue-50/40 dark:bg-blue-950/30 hover:bg-blue-50 dark:hover:bg-blue-950/50 transition-all text-center group">
                    <div class="flex items-center justify-center gap-1.5 text-[10px] font-bold text-blue-700 dark:text-blue-400 uppercase tracking-wider mb-1">
                        <span class="w-2 h-2 rounded-full bg-blue-500"></span>
                        <span>3. Dikonfirmasi</span>
                    </div>
                    <span class="text-2xl font-black text-blue-600 dark:text-blue-300 group-hover:scale-105 transition-transform block">
                        {{ number_format($stats['confirmed'] ?? 0) }}
                    </span>
                    <span class="text-[10px] text-blue-600/80 dark:text-blue-400/80 mt-0.5 block">Pembayaran Diterima</span>
                </div>

                <!-- 4. Diproses -->
                <div class="p-3.5 rounded-2xl border border-indigo-200 dark:border-indigo-900/50 bg-indigo-50/40 dark:bg-indigo-950/30 hover:bg-indigo-50 dark:hover:bg-indigo-950/50 transition-all text-center group">
                    <div class="flex items-center justify-center gap-1.5 text-[10px] font-bold text-indigo-700 dark:text-indigo-400 uppercase tracking-wider mb-1">
                        <span class="w-2 h-2 rounded-full bg-indigo-500"></span>
                        <span>4. Diproses</span>
                    </div>
                    <span class="text-2xl font-black text-indigo-600 dark:text-indigo-300 group-hover:scale-105 transition-transform block">
                        {{ number_format($stats['processing'] ?? 0) }}
                    </span>
                    <span class="text-[10px] text-indigo-600/80 dark:text-indigo-400/80 mt-0.5 block">Sedang Dikemas</span>
                </div>

                <!-- 5. Dikirim -->
                <div class="p-3.5 rounded-2xl border border-purple-200 dark:border-purple-900/50 bg-purple-50/40 dark:bg-purple-950/30 hover:bg-purple-50 dark:hover:bg-purple-950/50 transition-all text-center group">
                    <div class="flex items-center justify-center gap-1.5 text-[10px] font-bold text-purple-700 dark:text-purple-400 uppercase tracking-wider mb-1">
                        <span class="w-2 h-2 rounded-full bg-purple-500"></span>
                        <span>5. Dikirim</span>
                    </div>
                    <span class="text-2xl font-black text-purple-600 dark:text-purple-300 group-hover:scale-105 transition-transform block">
                        {{ number_format($stats['shipped'] ?? 0) }}
                    </span>
                    <span class="text-[10px] text-purple-600/80 dark:text-purple-400/80 mt-0.5 block">Dalam Pengiriman</span>
                </div>

                <!-- 6. Selesai -->
                <div class="p-3.5 rounded-2xl border border-emerald-200 dark:border-emerald-900/50 bg-emerald-50/50 dark:bg-emerald-950/30 hover:bg-emerald-50 dark:hover:bg-emerald-950/50 transition-all text-center group">
                    <div class="flex items-center justify-center gap-1.5 text-[10px] font-bold text-emerald-700 dark:text-emerald-400 uppercase tracking-wider mb-1">
                        <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                        <span>6. Selesai</span>
                    </div>
                    <span class="text-2xl font-black text-emerald-600 dark:text-emerald-300 group-hover:scale-105 transition-transform block">
                        {{ number_format($stats['delivered'] ?? 0) }}
                    </span>
                    <span class="text-[10px] text-emerald-600/80 dark:text-emerald-400/80 mt-0.5 block">Pesanan Sampai</span>
                </div>
            </div>

            <!-- Exception Metrics (Cancelled & Returned) -->
            <div class="flex flex-wrap items-center justify-between gap-3 pt-3 border-t border-slate-100 dark:border-stone-800 text-xs">
                <span class="text-slate-400 dark:text-stone-400 font-medium">Status Lainnya:</span>
                <div class="flex items-center gap-3">
                    <div class="flex items-center gap-1.5 px-3 py-1 rounded-xl bg-rose-50 dark:bg-rose-950/50 text-rose-700 dark:text-rose-300 border border-rose-200 dark:border-rose-900/60">
                        <span class="w-2 h-2 rounded-full bg-rose-500"></span>
                        <span class="font-bold">Dibatalkan:</span>
                        <span class="font-black">{{ number_format($stats['cancelled'] ?? 0) }}</span>
                    </div>
                    <div class="flex items-center gap-1.5 px-3 py-1 rounded-xl bg-orange-50 dark:bg-orange-950/50 text-orange-700 dark:text-orange-300 border border-orange-200 dark:border-orange-900/60">
                        <span class="w-2 h-2 rounded-full bg-orange-500"></span>
                        <span class="font-bold">Retur:</span>
                        <span class="font-black">{{ number_format($stats['returned'] ?? 0) }}</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- ========================================================================= --}}
        {{-- OPERATIONAL QUICK ACCESS HUB (MODERN ACTION CARDS)                        --}}
        {{-- ========================================================================= --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
            <a href="{{ route('stores.index') }}" 
               class="p-5 rounded-3xl bg-white dark:bg-surface-container border border-slate-200/80 dark:border-stone-800/80 shadow-sm hover:shadow-lg hover:border-blue-300 dark:hover:border-blue-700/60 transition-all group flex items-start gap-4">
                <div class="w-12 h-12 rounded-2xl bg-blue-50 dark:bg-blue-950/50 text-blue-600 dark:text-blue-400 flex items-center justify-center shrink-0 group-hover:scale-110 transition-transform">
                    <span class="material-symbols-outlined text-[24px]">store</span>
                </div>
                <div>
                    <div class="flex items-center gap-1">
                        <h3 class="text-sm font-bold text-slate-800 dark:text-white group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors">Mitra Toko</h3>
                        <span class="material-symbols-outlined text-[16px] text-slate-400 dark:text-stone-400 group-hover:translate-x-1 transition-transform">arrow_forward</span>
                    </div>
                    <p class="text-[11px] text-slate-500 dark:text-stone-400 mt-1">Kelola profil toko, limit belanja, dan penugasan sales lapangan.</p>
                </div>
            </a>

            <a href="{{ route('admin.jde.index') }}" 
               class="p-5 rounded-3xl bg-white dark:bg-surface-container border border-slate-200/80 dark:border-stone-800/80 shadow-sm hover:shadow-lg hover:border-purple-300 dark:hover:border-purple-700/60 transition-all group flex items-start gap-4">
                <div class="w-12 h-12 rounded-2xl bg-purple-50 dark:bg-purple-950/50 text-purple-600 dark:text-purple-400 flex items-center justify-center shrink-0 group-hover:scale-110 transition-transform">
                    <span class="material-symbols-outlined text-[24px]">database</span>
                </div>
                <div>
                    <div class="flex items-center gap-1">
                        <h3 class="text-sm font-bold text-slate-800 dark:text-white group-hover:text-purple-600 dark:group-hover:text-purple-400 transition-colors">Katalog Baru dari Pusat</h3>
                        <span class="material-symbols-outlined text-[16px] text-slate-400 dark:text-stone-400 group-hover:translate-x-1 transition-transform">arrow_forward</span>
                    </div>
                    <p class="text-[11px] text-slate-500 dark:text-stone-400 mt-1">Pemeriksaan dan persetujuan artikel produk baru dari pusat untuk ditayangkan.</p>
                </div>
            </a>

            <a href="{{ route('price-product-setting-store.index') }}" 
               class="p-5 rounded-3xl bg-white dark:bg-surface-container border border-slate-200/80 dark:border-stone-800/80 shadow-sm hover:shadow-lg hover:border-amber-300 dark:hover:border-amber-700/60 transition-all group flex items-start gap-4">
                <div class="w-12 h-12 rounded-2xl bg-amber-50 dark:bg-amber-950/50 text-amber-600 dark:text-amber-400 flex items-center justify-center shrink-0 group-hover:scale-110 transition-transform">
                    <span class="material-symbols-outlined text-[24px]">sell</span>
                </div>
                <div>
                    <div class="flex items-center gap-1">
                        <h3 class="text-sm font-bold text-slate-800 dark:text-white group-hover:text-amber-600 dark:group-hover:text-amber-400 transition-colors">Harga Khusus & Promo Toko</h3>
                        <span class="material-symbols-outlined text-[16px] text-slate-400 dark:text-stone-400 group-hover:translate-x-1 transition-transform">arrow_forward</span>
                    </div>
                    <p class="text-[11px] text-slate-500 dark:text-stone-400 mt-1">Atur harga khusus toko, potongan harga, dan promo kasir.</p>
                </div>
            </a>

            <a href="{{ route('orders.index') }}" 
               class="p-5 rounded-3xl bg-white dark:bg-surface-container border border-slate-200/80 dark:border-stone-800/80 shadow-sm hover:shadow-lg hover:border-emerald-300 dark:hover:border-emerald-700/60 transition-all group flex items-start gap-4">
                <div class="w-12 h-12 rounded-2xl bg-emerald-50 dark:bg-emerald-950/50 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shrink-0 group-hover:scale-110 transition-transform">
                    <span class="material-symbols-outlined text-[24px]">receipt_long</span>
                </div>
                <div>
                    <div class="flex items-center gap-1">
                        <h3 class="text-sm font-bold text-slate-800 dark:text-white group-hover:text-emerald-600 dark:group-hover:text-emerald-400 transition-colors">Daftar Semua Pesanan</h3>
                        <span class="material-symbols-outlined text-[16px] text-slate-400 dark:text-stone-400 group-hover:translate-x-1 transition-transform">arrow_forward</span>
                    </div>
                    <p class="text-[11px] text-slate-500 dark:text-stone-400 mt-1">Kelola transaksi pesanan, status kurir, dan pelunasan.</p>
                </div>
            </a>
        </div>

        {{-- ========================================================================= --}}
        {{-- CHARTS SECTION (LINE & DONUT ANALYTICS)                                  --}}
        {{-- ========================================================================= --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
            <div class="bg-white dark:bg-surface-container p-6 rounded-3xl shadow-sm border border-slate-200/80 dark:border-stone-800/80">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h2 class="text-sm font-black text-slate-800 dark:text-white flex items-center gap-2">
                            <span class="material-symbols-outlined text-[20px] text-indigo-600 dark:text-indigo-400">trending_up</span>
                            <span>Tren Pesanan Harian ({{ $month }})</span>
                        </h2>
                        <p class="text-[11px] text-slate-400 dark:text-stone-400 mt-0.5">Grafik jumlah transaksi harian sepanjang bulan ini.</p>
                    </div>
                </div>
                <div class="relative h-72">
                    <canvas id="orderChart"></canvas>
                </div>
            </div>

            <div class="bg-white dark:bg-surface-container p-6 rounded-3xl shadow-sm border border-slate-200/80 dark:border-stone-800/80">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h2 class="text-sm font-black text-slate-800 dark:text-white flex items-center gap-2">
                            <span class="material-symbols-outlined text-[20px] text-purple-600 dark:text-purple-400">pie_chart</span>
                            <span>Status Pesanan Saat Ini</span>
                        </h2>
                        <p class="text-[11px] text-slate-400 dark:text-stone-400 mt-0.5">Proporsi status pesanan yang sedang berjalan.</p>
                    </div>
                </div>
                <div class="relative h-72">
                    <canvas id="orderPieChart"></canvas>
                </div>
            </div>
        </div>
    @endif
@endsection

@if($roleType === 'admin')
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
<script>
(function() {
    const ctx = document.getElementById('orderChart');
    if (!ctx) return;

    const isDark = document.documentElement.classList.contains('dark');
    const labels = @json(array_column($dailyStats ?? [], 'day'));
    const data = @json(array_column($dailyStats ?? [], 'total'));

    const canvas = ctx.getContext('2d');
    const gradient = canvas.createLinearGradient(0, 0, 0, 280);
    gradient.addColorStop(0, isDark ? 'rgba(99, 102, 241, 0.45)' : 'rgba(79, 70, 229, 0.35)');
    gradient.addColorStop(1, 'rgba(79, 70, 229, 0.01)');

    new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels.map(d => `Tgl ${d}`),
            datasets: [{
                label: 'Jumlah Order',
                data: data,
                borderColor: isDark ? '#818cf8' : '#4F46E5',
                backgroundColor: gradient,
                borderWidth: 3,
                pointBackgroundColor: isDark ? '#818cf8' : '#4F46E5',
                pointBorderColor: isDark ? '#1c1917' : '#ffffff',
                pointBorderWidth: 2,
                pointRadius: 4,
                pointHoverRadius: 6,
                fill: true,
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: {
                intersect: false,
                mode: 'index',
            },
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: isDark ? 'rgba(28, 25, 23, 0.95)' : 'rgba(15, 23, 42, 0.9)',
                    titleColor: '#fff',
                    bodyColor: '#e2e8f0',
                    borderColor: isDark ? '#44403c' : 'transparent',
                    borderWidth: isDark ? 1 : 0,
                    padding: 10,
                    cornerRadius: 10,
                }
            },
            scales: {
                x: {
                    grid: { display: false },
                    ticks: { color: isDark ? '#a8a29e' : '#64748b', font: { size: 10, weight: 600 } }
                },
                y: {
                    beginAtZero: true,
                    ticks: { stepSize: 1, color: isDark ? '#a8a29e' : '#64748b', font: { size: 10, weight: 600 } },
                    grid: { color: isDark ? 'rgba(87, 83, 78, 0.3)' : 'rgba(226, 232, 240, 0.6)', borderDash: [4, 4] }
                }
            }
        }
    });
})();

(function() {
    const ctx = document.getElementById('orderPieChart');
    if (!ctx) return;

    const isDark = document.documentElement.classList.contains('dark');
    const labels = @json(array_values($statusLabels ?? []));
    const keys = @json(array_keys($statusLabels ?? []));
    const statusCounts = @json($stats ?? []);
    const counts = keys.map(k => statusCounts[k] ?? 0);
    const colors = @json(array_values($statusColors ?? []));

    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: labels,
            datasets: [{
                data: counts,
                backgroundColor: colors,
                borderColor: isDark ? '#1c1917' : '#ffffff',
                borderWidth: 3,
                hoverOffset: 8
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '72%',
            plugins: {
                legend: {
                    position: 'right',
                    labels: {
                        color: isDark ? '#e7e5e4' : '#334155',
                        font: { size: 11, weight: 600 },
                        padding: 12,
                        usePointStyle: true,
                        pointStyle: 'circle'
                    }
                },
                tooltip: {
                    backgroundColor: isDark ? 'rgba(28, 25, 23, 0.95)' : 'rgba(15, 23, 42, 0.9)',
                    titleColor: '#fff',
                    bodyColor: '#e2e8f0',
                    borderColor: isDark ? '#44403c' : 'transparent',
                    borderWidth: isDark ? 1 : 0,
                    padding: 10,
                    cornerRadius: 10,
                }
            }
        }
    });
})();
</script>
@endpush
@endif
