@extends('layouts.app')

@section('title', 'Manajemen Hak Akses & Role')

@section('content')
<div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
    <div>
        <h1 class="font-headline-xl text-headline-xl text-on-surface">Manajemen Hak Akses & Role</h1>
        <p class="text-body-md text-on-surface-variant">Kelola tingkatan otoritas dan batasan akses modul untuk setiap pengguna admin.</p>
    </div>
    @can('roles.create')
        <a href="{{ route('roles.create') }}" class="flex items-center gap-2 bg-primary text-white px-5 py-2.5 rounded-lg font-headline-md text-headline-md hover:opacity-90 transition-all active:scale-95 shadow-sm">
            <span class="material-symbols-outlined text-[20px]">add_circle</span>
            Tambah Role Baru
        </a>
    @endcan
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

<!-- Metric Summary Cards -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-container-gap mb-8">
    <div class="bg-white p-card-padding rounded-xl shadow-subtle border border-surface-container flex items-center gap-4">
        <div class="w-12 h-12 rounded-lg bg-primary/10 flex items-center justify-center text-primary">
            <span class="material-symbols-outlined text-[28px]">shield_person</span>
        </div>
        <div>
            <p class="text-on-surface-variant font-label-md text-label-md">Total Role</p>
            <div class="flex items-baseline gap-2">
                <span class="font-metric-display text-metric-display text-on-surface">{{ $totalRoles }}</span>
                <span class="text-on-surface-variant text-label-sm">peran terdaftar</span>
            </div>
        </div>
    </div>
    <div class="bg-white p-card-padding rounded-xl shadow-subtle border border-surface-container flex items-center gap-4">
        <div class="w-12 h-12 rounded-lg bg-tertiary/10 flex items-center justify-center text-tertiary">
            <span class="material-symbols-outlined text-[28px]">group</span>
        </div>
        <div>
            <p class="text-on-surface-variant font-label-md text-label-md">Pengguna dengan Role</p>
            <div class="flex items-baseline gap-2">
                <span class="font-metric-display text-metric-display text-on-surface">{{ $totalUsers }}</span>
                <span class="text-success font-label-sm text-label-sm">admin aktif</span>
            </div>
        </div>
    </div>
    <div class="bg-white p-card-padding rounded-xl shadow-subtle border border-surface-container flex items-center gap-4">
        <div class="w-12 h-12 rounded-lg bg-warning/10 flex items-center justify-center text-warning">
            <span class="material-symbols-outlined text-[28px]">key</span>
        </div>
        <div>
            <p class="text-on-surface-variant font-label-md text-label-md">Total Permissions</p>
            <div class="flex items-baseline gap-2">
                <span class="font-metric-display text-metric-display text-on-surface">{{ $totalPermissions }}</span>
                <span class="text-on-surface-variant text-label-sm">fitur & route</span>
            </div>
        </div>
    </div>
</div>

<!-- Table Card -->
<div class="bg-white rounded-xl shadow-subtle border border-surface-container overflow-hidden">
    <div class="p-6 border-b border-surface-container flex flex-col sm:flex-row justify-between items-center gap-4 bg-surface-container-low/30">
        <div>
            <h3 class="font-headline-md text-headline-md text-on-surface">Daftar Role Sistem</h3>
            <p class="text-body-sm text-on-surface-variant">Klik detail atau edit untuk mengelola hak akses per modul.</p>
        </div>
        <form method="GET" action="{{ route('roles.index') }}" class="flex items-center gap-2 w-full sm:w-auto">
            <div class="relative w-full sm:w-64">
                <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-on-surface-variant text-[18px]">search</span>
                <input type="text" name="search" value="{{ $search }}" placeholder="Cari role..." class="w-full pl-9 pr-3 py-1.5 border border-outline-variant rounded-lg text-body-sm focus:outline-none focus:border-primary">
            </div>
            @if($search)
                <a href="{{ route('roles.index') }}" class="px-3 py-1.5 border border-outline-variant text-on-surface-variant rounded-lg text-body-sm hover:bg-surface-container">Reset</a>
            @endif
        </form>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-surface-container-low/50 text-on-surface-variant border-b border-surface-container font-label-md text-label-md">
                    <th class="px-6 py-3.5">Nama Role</th>
                    <th class="px-6 py-3.5">Deskripsi</th>
                    <th class="px-6 py-3.5 text-center">Level Otoritas</th>
                    <th class="px-6 py-3.5 text-center">Hak Akses Aktif</th>
                    <th class="px-6 py-3.5 text-center">Admin Terpasang</th>
                    <th class="px-6 py-3.5 text-center">Tipe Role</th>
                    <th class="px-6 py-3.5 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-surface-container text-body-md text-on-surface">
                @forelse($roles as $role)
                    <tr class="hover:bg-surface-container-low/30 transition-colors">
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-lg {{ $role->is_system ? 'bg-primary/10 text-primary' : 'bg-surface-container text-on-surface-variant' }} flex items-center justify-center shrink-0">
                                    <span class="material-symbols-outlined text-[20px]">
                                        {{ $role->is_system ? 'verified_user' : 'group' }}
                                    </span>
                                </div>
                                <div>
                                    <strong class="font-bold block text-on-surface">{{ $role->name }}</strong>
                                    <span class="text-xs font-mono text-on-surface-variant">{{ $role->slug }}</span>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 max-w-xs text-on-surface-variant truncate">
                            {{ $role->description ?: '-' }}
                        </td>
                        <td class="px-6 py-4 text-center">
                            <span class="px-2.5 py-1 bg-surface-container rounded-full text-xs font-semibold font-mono text-on-surface">
                                Level {{ $role->level }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <span class="px-2.5 py-1 bg-primary/10 text-primary rounded-full text-xs font-bold font-mono">
                                {{ $role->permissions_count }} Akses
                            </span>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <span class="text-xs font-medium text-on-surface">
                                {{ $role->admins_count }} Pengguna
                            </span>
                        </td>
                        <td class="px-6 py-4 text-center">
                            @if($role->is_system)
                                <span class="inline-flex items-center gap-1 text-[11px] px-2 py-0.5 bg-warning/10 text-warning rounded-full font-medium">
                                    <span class="material-symbols-outlined text-[13px]">lock</span>
                                    Sistem
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 text-[11px] px-2 py-0.5 bg-success/10 text-success rounded-full font-medium">
                                    <span class="material-symbols-outlined text-[13px]">tune</span>
                                    Kustom
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-right">
                            <div class="flex items-center justify-end gap-1.5">
                                @can('roles.show')
                                    <a href="{{ route('roles.show', $role->id) }}" class="p-1.5 text-on-surface-variant hover:text-primary hover:bg-surface-container rounded-lg transition-colors" title="Lihat Detail">
                                        <span class="material-symbols-outlined text-[18px]">visibility</span>
                                    </a>
                                @endcan
                                @can('roles.edit')
                                    <a href="{{ route('roles.edit', $role->id) }}" class="p-1.5 text-on-surface-variant hover:text-primary hover:bg-surface-container rounded-lg transition-colors" title="Edit Role">
                                        <span class="material-symbols-outlined text-[18px]">edit</span>
                                    </a>
                                @endcan
                                @can('roles.destroy')
                                    @if(!$role->is_system && !in_array($role->slug, ['admin', 'super-admin']))
                                        <form method="POST" action="{{ route('roles.destroy', $role->id) }}" onsubmit="return confirm('Apakah Anda yakin ingin menghapus role \'{{ $role->name }}\'?')" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="p-1.5 text-on-surface-variant hover:text-danger hover:bg-danger/10 rounded-lg transition-colors" title="Hapus Role">
                                                <span class="material-symbols-outlined text-[18px]">delete</span>
                                            </button>
                                        </form>
                                    @endif
                                @endcan
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-6 py-12 text-center text-on-surface-variant">
                            <span class="material-symbols-outlined text-[48px] text-outline-variant mb-2 block">shield_person</span>
                            <p class="font-medium text-body-lg">Tidak ada data role ditemukan.</p>
                            <p class="text-body-sm text-on-surface-variant mt-1">Coba gunakan kata kunci pencarian lain atau buat role baru.</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($roles->hasPages())
        <div class="p-4 border-t border-surface-container flex justify-between items-center">
            {{ $roles->links() }}
        </div>
    @endif
</div>
@endsection