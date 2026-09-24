@extends('layouts.app')

@section('title', 'Detail Hak Akses: ' . $role->name)

@section('content')
<div class="mb-6">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <a href="{{ route('roles.index') }}" class="text-primary hover:underline text-body-sm flex items-center gap-1">
                    <span class="material-symbols-outlined text-[16px]">arrow_back</span>
                    Kembali ke Daftar Role
                </a>
            </div>
            <h1 class="font-headline-xl text-headline-xl text-on-surface">Detail Hak Akses: {{ $role->name }}</h1>
            <p class="text-body-md text-on-surface-variant">Melihat daftar modul dan aksi yang dapat diakses oleh role ini.</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('roles.index') }}" class="px-4 py-2 border border-outline-variant text-on-surface-variant rounded-lg font-label-md hover:bg-surface-container transition-colors">
                Kembali
            </a>
            @can('roles.edit')
                <a href="{{ route('roles.edit', $role->id) }}" class="flex items-center gap-2 px-5 py-2 bg-primary text-white rounded-lg font-label-md hover:opacity-90 transition-all shadow-sm">
                    <span class="material-symbols-outlined text-[18px]">edit</span>
                    Edit Role Ini
                </a>
            @endcan
        </div>
    </div>
</div>

@include('layouts.partials.system-submenu')

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Role Overview Card -->
    <div class="lg:col-span-1 space-y-6">
        <div class="bg-white p-6 rounded-xl shadow-subtle border border-surface-container">
            <div class="flex items-center gap-3 mb-4 pb-4 border-b border-surface-container">
                <div class="w-12 h-12 rounded-xl bg-primary/10 flex items-center justify-center text-primary">
                    <span class="material-symbols-outlined text-[28px]">shield_person</span>
                </div>
                <div>
                    <h3 class="font-headline-md text-headline-md text-on-surface">{{ $role->name }}</h3>
                    <span class="text-label-sm font-mono text-on-surface-variant bg-surface-container px-2 py-0.5 rounded">Level {{ $role->level }}</span>
                </div>
            </div>

            <div class="space-y-3 text-body-md">
                <div>
                    <span class="text-on-surface-variant text-label-sm uppercase block font-medium">Slug Identifikasi:</span>
                    <code class="text-xs bg-surface-container px-2 py-1 rounded text-primary font-mono">{{ $role->slug }}</code>
                </div>
                <div>
                    <span class="text-on-surface-variant text-label-sm uppercase block font-medium">Deskripsi:</span>
                    <p class="text-on-surface mt-0.5">{{ $role->description ?: 'Tidak ada deskripsi.' }}</p>
                </div>
                <div>
                    <span class="text-on-surface-variant text-label-sm uppercase block font-medium">Tipe Role:</span>
                    @if($role->is_system)
                        <span class="inline-flex items-center gap-1 text-xs px-2.5 py-0.5 bg-warning/10 text-warning rounded-full font-medium mt-1">
                            <span class="material-symbols-outlined text-[14px]">lock</span>
                            Role Sistem (Bawaan)
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1 text-xs px-2.5 py-0.5 bg-success/10 text-success rounded-full font-medium mt-1">
                            <span class="material-symbols-outlined text-[14px]">tune</span>
                            Role Kustom
                        </span>
                    @endif
                </div>
                <div>
                    <span class="text-on-surface-variant text-label-sm uppercase block font-medium">Total Hak Akses Aktif:</span>
                    <strong class="text-headline-sm text-primary font-bold">{{ count($rolePermissions) }}</strong>
                </div>
            </div>
        </div>
    </div>

    <!-- Read-Only Permissions Tree -->
    <div class="lg:col-span-2">
        <div class="bg-white p-6 rounded-xl shadow-subtle border border-surface-container">
            <div class="flex items-center justify-between pb-4 mb-4 border-b border-surface-container">
                <h3 class="font-headline-md text-headline-md text-on-surface flex items-center gap-2">
                    <span class="material-symbols-outlined text-primary">verified_user</span>
                    Cakupan Hak Akses Role
                </h3>
                <span class="text-xs text-on-surface-variant">Centang biru = diizinkan</span>
            </div>

            <div class="divide-y divide-surface-container">
                @foreach ($menus as $menu)
                    @php
                        $routeVal = $menu->route_name ?: $menu->permission ?: ('group.' . $menu->id);
                        $isGroupChecked = in_array($routeVal, $rolePermissions);
                    @endphp
                    <div class="py-4 menu-group-item">
                        <div class="flex items-center justify-between bg-surface-container-low/50 p-3 rounded-lg">
                            <div class="flex items-center gap-2.5">
                                <input type="checkbox" 
                                       disabled
                                       class="w-4 h-4 rounded border-outline-variant text-primary" 
                                       {{ $isGroupChecked ? 'checked' : '' }}>
                                <div class="font-bold text-on-surface text-body-md flex items-center gap-2">
                                    @if($menu->icon)
                                        <span class="material-symbols-outlined text-primary text-[20px]">{{ $menu->icon }}</span>
                                    @endif
                                    <span>{{ $menu->title }}</span>
                                </div>
                            </div>
                        </div>

                        @if(count($menu->childs))
                            <div class="mt-2">
                                @include('pages.roles.childs', [
                                    'childs' => $menu->childs, 
                                    'disabled' => true, 
                                    'rolePermissions' => $rolePermissions
                                ])
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
@endsection
