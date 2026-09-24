@extends('layouts.app')

@section('title', 'Permissions & Route Akses')

@section('content')
<div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
    <div>
        <h1 class="font-headline-xl text-headline-xl text-on-surface">Daftar Hak Akses (Permissions)</h1>
        <p class="text-body-md text-on-surface-variant">Daftar permission route yang diproteksi oleh RBAC sistem.</p>
    </div>
    <form method="POST" action="{{ route('permissions.sync') }}">
        @csrf
        <button type="submit" class="flex items-center gap-2 px-5 py-2.5 bg-primary text-white rounded-lg font-headline-md text-headline-md hover:opacity-90 transition-all active:scale-95 shadow-sm">
            <span class="material-symbols-outlined text-[20px]">sync</span>
            Sinkronkan Route Permissions
        </button>
    </form>
</div>

@include('layouts.partials.system-submenu')

@if(session('success'))
    <div class="mb-6 p-4 rounded-xl bg-success/10 border border-success/20 text-success text-body-md flex items-center gap-2">
        <span class="material-symbols-outlined text-[20px]">check_circle</span>
        <span>{{ session('success') }}</span>
    </div>
@endif

@if(session('error'))
    <div class="mb-6 p-4 rounded-xl bg-danger/10 border border-danger/20 text-danger text-body-md flex items-center gap-2">
        <span class="material-symbols-outlined text-[20px]">error</span>
        <span>{{ session('error') }}</span>
    </div>
@endif

<!-- Metric Cards -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-container-gap mb-8">
    <div class="bg-white p-card-padding rounded-xl shadow-subtle border border-surface-container flex items-center gap-4">
        <div class="w-12 h-12 rounded-lg bg-primary/10 flex items-center justify-center text-primary">
            <span class="material-symbols-outlined text-[28px]">key</span>
        </div>
        <div>
            <p class="text-on-surface-variant font-label-md text-label-md">Total Permissions</p>
            <div class="flex items-baseline gap-2">
                <span class="font-metric-display text-metric-display text-on-surface">{{ $totalPermissions }}</span>
                <span class="text-on-surface-variant text-label-sm">hak akses</span>
            </div>
        </div>
    </div>
    <div class="bg-white p-card-padding rounded-xl shadow-subtle border border-surface-container flex items-center gap-4">
        <div class="w-12 h-12 rounded-lg bg-tertiary/10 flex items-center justify-center text-tertiary">
            <span class="material-symbols-outlined text-[28px]">folder</span>
        </div>
        <div>
            <p class="text-on-surface-variant font-label-md text-label-md">Kelompok Modul</p>
            <div class="flex items-baseline gap-2">
                <span class="font-metric-display text-metric-display text-on-surface">{{ count($groups) }}</span>
                <span class="text-success font-label-sm text-label-sm">grup</span>
            </div>
        </div>
    </div>
    <div class="bg-white p-card-padding rounded-xl shadow-subtle border border-surface-container flex items-center gap-4">
        <div class="w-12 h-12 rounded-lg bg-success/10 flex items-center justify-center text-success">
            <span class="material-symbols-outlined text-[28px]">verified</span>
        </div>
        <div>
            <p class="text-on-surface-variant font-label-md text-label-md">Status Aktif</p>
            <div class="flex items-baseline gap-2">
                <span class="font-metric-display text-metric-display text-on-surface">{{ $activePermissions }}</span>
                <span class="text-success font-label-sm text-label-sm">siap digunakan</span>
            </div>
        </div>
    </div>
</div>

<div class="bg-white rounded-xl shadow-subtle border border-surface-container overflow-hidden">
    <div class="p-6 border-b border-surface-container flex flex-col sm:flex-row justify-between items-center gap-4 bg-surface-container-low/30">
        <div>
            <h3 class="font-headline-md text-headline-md text-on-surface">Daftar Permissions</h3>
            <p class="text-body-sm text-on-surface-variant">Permissions yang terdaftar dan dapat diasosiasikan ke setiap Role.</p>
        </div>
        <form method="GET" action="{{ route('permissions.index') }}" class="flex flex-wrap items-center gap-2 w-full sm:w-auto">
            <select name="group" onchange="this.form.submit()" class="px-3 py-1.5 border border-outline-variant rounded-lg text-body-sm focus:outline-none focus:border-primary">
                <option value="">Semua Modul</option>
                @foreach($groups as $grp)
                    <option value="{{ $grp }}" {{ $groupFilter === $grp ? 'selected' : '' }}>{{ $grp }}</option>
                @endforeach
            </select>
            <div class="relative flex-1 sm:w-64">
                <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-on-surface-variant text-[18px]">search</span>
                <input type="text" name="search" value="{{ $search }}" placeholder="Cari nama / route..." class="w-full pl-9 pr-3 py-1.5 border border-outline-variant rounded-lg text-body-sm focus:outline-none focus:border-primary">
            </div>
            @if($search || $groupFilter)
                <a href="{{ route('permissions.index') }}" class="px-3 py-1.5 border border-outline-variant text-on-surface-variant rounded-lg text-body-sm hover:bg-surface-container">Reset</a>
            @endif
        </form>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-surface-container-low/50 text-on-surface-variant border-b border-surface-container font-label-md text-label-md">
                    <th class="px-6 py-3.5">Nama Permission / Route</th>
                    <th class="px-6 py-3.5">Kelompok (Group)</th>
                    <th class="px-6 py-3.5 text-center">Aksi / Action</th>
                    <th class="px-6 py-3.5">Deskripsi</th>
                    <th class="px-6 py-3.5 text-center">Guard</th>
                    <th class="px-6 py-3.5 text-center">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-surface-container text-body-md text-on-surface">
                @forelse($permissions as $perm)
                    <tr class="hover:bg-surface-container-low/30 transition-colors">
                        <td class="px-6 py-3.5">
                            <code class="px-2 py-0.5 bg-surface-container text-primary font-mono text-xs rounded font-bold">
                                {{ $perm->name }}
                            </code>
                        </td>
                        <td class="px-6 py-3.5 font-medium text-on-surface">
                            {{ $perm->group ?: '-' }}
                        </td>
                        <td class="px-6 py-3.5 text-center">
                            <span class="px-2 py-0.5 bg-surface-container text-on-surface-variant rounded text-xs font-mono">
                                {{ $perm->action ?: '-' }}
                            </span>
                        </td>
                        <td class="px-6 py-3.5 text-on-surface-variant text-body-sm">
                            {{ $perm->description ?: '-' }}
                        </td>
                        <td class="px-6 py-3.5 text-center font-mono text-xs text-on-surface-variant">
                            {{ $perm->guard_name }}
                        </td>
                        <td class="px-6 py-3.5 text-center">
                            @if($perm->is_active)
                                <span class="px-2 py-0.5 bg-success/10 text-success rounded-full text-xs font-medium">Aktif</span>
                            @else
                                <span class="px-2 py-0.5 bg-danger/10 text-danger rounded-full text-xs font-medium">Nonaktif</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center text-on-surface-variant">
                            <span class="material-symbols-outlined text-[48px] text-outline-variant mb-2 block">key_off</span>
                            <p class="font-medium text-body-lg">Tidak ada permission ditemukan.</p>
                            <p class="text-body-sm text-on-surface-variant mt-1">Gunakan tombol "Sinkronkan Route Permissions" untuk memuat seluruh route yang tersedia.</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($permissions->hasPages())
        <div class="p-4 border-t border-surface-container flex justify-between items-center">
            {{ $permissions->links() }}
        </div>
    @endif
</div>
@endsection
