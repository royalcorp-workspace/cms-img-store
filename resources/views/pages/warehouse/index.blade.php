@extends('layouts.app')

@section('title', 'Warehouse')

@section('content')
    <!-- Header Section -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="font-headline-lg text-headline-lg text-on-surface">Warehouse</h1>
            <nav class="flex items-center gap-2 text-label-sm text-on-surface-variant mt-1 font-medium">
                <a href="{{ route('dashboard') }}" class="hover:text-primary transition-colors">eCommerce</a>
                <span class="material-symbols-outlined text-[14px]">chevron_right</span>
                <span class="text-on-surface">Warehouse</span>
            </nav>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('warehouses.create') }}" class="flex items-center gap-2 px-5 py-2 bg-primary text-white font-label-md hover:opacity-90 transition-all rounded-lg shadow-sm">
                <span class="material-symbols-outlined text-[18px]">add</span>
                Tambah Gudang
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

    <!-- Table Card -->
    <div class="bg-white rounded-lg shadow-sm border border-outline-variant/30 overflow-hidden mb-8">
        <!-- Filter Header -->
        <div class="p-4 border-b border-outline-variant/30 bg-surface-container-lowest">
            <form method="GET" action="{{ route('warehouses.index') }}" class="flex flex-col sm:flex-row sm:items-center gap-3">
                <div class="flex-1 relative min-w-[240px]">
                    <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-on-surface-variant text-[18px]">search</span>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama gudang, kode, atau kota..." class="w-full h-10 pl-9 pr-3 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary/20 focus:border-primary focus:outline-none text-sm bg-white transition-all">
                </div>

                <div class="flex items-center gap-3">
                    <div class="w-40">
                        <select name="status" class="w-full h-10 px-3 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary/20 focus:border-primary focus:outline-none text-sm bg-white text-on-surface cursor-pointer transition-all" onchange="this.form.submit()">
                            <option value="">Semua Status</option>
                            <option value="1" {{ request('status') === '1' ? 'selected' : '' }}>Aktif</option>
                            <option value="0" {{ request('status') === '0' ? 'selected' : '' }}>Nonaktif</option>
                        </select>
                    </div>

                    <div class="flex items-center gap-2 shrink-0">
                        <button type="submit" class="h-10 px-4 bg-primary text-white hover:bg-primary/90 rounded-lg text-sm font-medium transition-colors shadow-sm inline-flex items-center gap-1.5">
                            <span class="material-symbols-outlined text-[18px]">filter_list</span>
                            <span>Filter</span>
                        </button>
                        @if(request()->hasAny(['search', 'status']))
                            <a href="{{ route('warehouses.index') }}" class="h-10 px-3 bg-danger/10 text-danger hover:bg-danger/20 rounded-lg text-sm font-medium transition-colors inline-flex items-center gap-1" title="Reset Filter">
                                <span class="material-symbols-outlined text-[18px]">restart_alt</span>
                                <span class="hidden sm:inline">Reset</span>
                            </a>
                        @endif
                    </div>
                </div>
            </form>
        </div>

        <!-- Table -->
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-surface-gray/50 border-b border-outline-variant/30">
                        <th class="px-6 py-4 text-xs font-semibold text-on-surface-variant uppercase tracking-wider">Kode Gudang</th>
                        <th class="px-6 py-4 text-xs font-semibold text-on-surface-variant uppercase tracking-wider">Nama Gudang</th>
                        <th class="px-6 py-4 text-xs font-semibold text-on-surface-variant uppercase tracking-wider">Kota</th>
                        <th class="px-6 py-4 text-xs font-semibold text-on-surface-variant uppercase tracking-wider">Alamat</th>
                        <th class="px-6 py-4 text-xs font-semibold text-on-surface-variant uppercase tracking-wider text-center">Item Stok</th>
                        <th class="px-6 py-4 text-xs font-semibold text-on-surface-variant uppercase tracking-wider text-center">Status</th>
                        <th class="px-6 py-4 text-xs font-semibold text-on-surface-variant uppercase tracking-wider text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-outline-variant/20">
                    @forelse($warehouses as $wh)
                        <tr class="hover:bg-surface-container/30 transition-colors group">
                            <td class="px-6 py-4">
                                <span class="font-mono text-xs font-bold px-2.5 py-1 rounded bg-surface-container-low text-on-surface border border-outline-variant/30">
                                    {{ $wh->code }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="font-semibold text-sm text-on-surface flex items-center gap-2">
                                    <span class="material-symbols-outlined text-[18px] text-primary">warehouse</span>
                                    <span>{{ $wh->name }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-sm text-on-surface-variant">
                                {{ $wh->city ?: '-' }}
                            </td>
                            <td class="px-6 py-4 text-sm text-on-surface-variant max-w-xs truncate">
                                {{ $wh->address ?: '-' }}
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-primary/10 text-primary border border-primary/20">
                                    {{ $wh->inventories_count ?? 0 }} item
                                </span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                @if($wh->status)
                                    <span class="inline-flex items-center gap-1 text-xs font-semibold text-success bg-success/10 border border-success/20 px-2 py-0.5 rounded-full">
                                        <span class="w-1.5 h-1.5 rounded-full bg-success"></span>
                                        Aktif
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 text-xs font-semibold text-on-surface-variant bg-surface-container border border-outline-variant/30 px-2 py-0.5 rounded-full">
                                        <span class="w-1.5 h-1.5 rounded-full bg-on-surface-variant"></span>
                                        Nonaktif
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <a href="{{ route('warehouses.edit', $wh->id) }}" class="p-1.5 text-on-surface-variant hover:text-primary hover:bg-surface-container-high rounded-lg transition-colors" title="Edit Gudang">
                                        <span class="material-symbols-outlined text-[18px]">edit</span>
                                    </a>
                                    <form action="{{ route('warehouses.destroy', $wh->id) }}" method="POST" class="inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus gudang ini?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-1.5 text-on-surface-variant hover:text-danger hover:bg-danger/10 rounded-lg transition-colors" title="Hapus Gudang">
                                            <span class="material-symbols-outlined text-[18px]">delete</span>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center text-on-surface-variant">
                                <div class="flex flex-col items-center justify-center">
                                    <span class="material-symbols-outlined text-[48px] text-outline-variant mb-2">warehouse</span>
                                    <p class="text-sm font-semibold text-on-surface">Tidak ada data gudang ditemukan</p>
                                    <p class="text-xs text-on-surface-variant mt-1">Klik tombol 'Tambah Gudang' untuk membuat data gudang baru.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($warehouses->hasPages())
            <div class="p-4 border-t border-outline-variant/30 bg-surface-container-lowest">
                {{ $warehouses->links() }}
            </div>
        @endif
    </div>
@endsection
