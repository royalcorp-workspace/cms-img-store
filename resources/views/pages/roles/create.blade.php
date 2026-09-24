@extends('layouts.app')

@section('title', 'Buat Hak Akses (Role)')

@section('content')
<div class="mb-6">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="font-headline-xl text-headline-xl text-on-surface">Buat Hak Akses Baru</h1>
            <p class="text-body-md text-on-surface-variant">Tentukan nama role dan pilih izin/hak akses yang diberikan kepada role ini.</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('roles.index') }}" class="px-4 py-2 border border-outline-variant text-on-surface-variant rounded-lg font-label-md hover:bg-surface-container transition-colors">
                Batal
            </a>
            <button type="submit" form="form-create-role" class="flex items-center gap-2 px-5 py-2 bg-primary text-white rounded-lg font-label-md hover:opacity-90 transition-all shadow-sm">
                <span class="material-symbols-outlined text-[18px]">save</span>
                Simpan Role
            </button>
        </div>
    </div>
</div>

@include('layouts.partials.system-submenu')

@if($errors->any())
    <div class="mb-6 p-4 rounded-xl bg-danger/10 border border-danger/20 text-danger text-body-md">
        <ul class="list-disc list-inside space-y-1">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form id="form-create-role" method="POST" action="{{ route('roles.store') }}">
    @csrf
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Role Information -->
        <div class="lg:col-span-1 space-y-6">
            <div class="bg-white p-6 rounded-xl shadow-subtle border border-surface-container">
                <h3 class="font-headline-md text-headline-md text-on-surface mb-4 pb-2 border-b border-surface-container flex items-center gap-2">
                    <span class="material-symbols-outlined text-primary">admin_panel_settings</span>
                    Informasi Role
                </h3>

                <div class="space-y-4">
                    <div>
                        <label for="name" class="block font-label-md text-label-md text-on-surface mb-1">Nama Role <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="name" required value="{{ old('name') }}" placeholder="Contoh: Warehouse Staff" class="w-full px-3 py-2 border border-outline-variant rounded-lg text-body-md focus:outline-none focus:border-primary">
                    </div>

                    <div>
                        <label for="level" class="block font-label-md text-label-md text-on-surface mb-1">Tingkatan (Level)</label>
                        <input type="number" name="level" id="level" min="1" max="100" value="{{ old('level', 10) }}" class="w-full px-3 py-2 border border-outline-variant rounded-lg text-body-md focus:outline-none focus:border-primary">
                        <p class="text-[12px] text-on-surface-variant mt-1">Nilai 1 - 100. Semakin tinggi semakin besar otoritas levelnya.</p>
                    </div>

                    <div>
                        <label for="description" class="block font-label-md text-label-md text-on-surface mb-1">Deskripsi</label>
                        <textarea name="description" id="description" rows="3" placeholder="Jelaskan cakupan tugas atau akses role ini..." class="w-full px-3 py-2 border border-outline-variant rounded-lg text-body-md focus:outline-none focus:border-primary">{{ old('description') }}</textarea>
                    </div>
                </div>
            </div>

            <div class="bg-primary/5 p-5 rounded-xl border border-primary/20">
                <div class="flex items-start gap-3">
                    <span class="material-symbols-outlined text-primary text-[20px] mt-0.5">info</span>
                    <div class="text-body-sm text-on-surface">
                        <strong class="font-medium text-primary block mb-1">Struktur Hak Akses Berjenjang</strong>
                        Hak akses disusun dari Modul Utama &rarr; Submenu &rarr; Aksi Detail (Index, Create, Edit, Delete, Export, dll). Memilih modul utama secara otomatis mencentang seluruh aksi di bawahnya.
                    </div>
                </div>
            </div>
        </div>

        <!-- Permissions Hierarchy Tree -->
        <div class="lg:col-span-2">
            <div class="bg-white p-6 rounded-xl shadow-subtle border border-surface-container">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-4 mb-4 border-b border-surface-container gap-3">
                    <div>
                        <h3 class="font-headline-md text-headline-md text-on-surface flex items-center gap-2">
                            <span class="material-symbols-outlined text-primary">shield_person</span>
                            Daftar Hak Akses Menu & Aksi
                        </h3>
                        <p class="text-body-sm text-on-surface-variant">Centang menu dan fitur yang diizinkan untuk role ini.</p>
                    </div>
                    <div class="flex items-center gap-2">
                        <button type="button" id="btn-select-all" class="px-3 py-1.5 text-xs font-semibold bg-surface-container text-on-surface hover:bg-primary hover:text-white rounded-lg transition-colors">
                            Pilih Semua
                        </button>
                        <button type="button" id="btn-deselect-all" class="px-3 py-1.5 text-xs font-semibold bg-surface-container text-on-surface hover:bg-danger hover:text-white rounded-lg transition-colors">
                            Batalkan Semua
                        </button>
                    </div>
                </div>

                <!-- Live Search in Permissions -->
                <div class="mb-4 relative">
                    <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-on-surface-variant text-[18px]">search</span>
                    <input type="text" id="permission-search" placeholder="Cari nama menu, aksi, atau route..." class="w-full pl-9 pr-4 py-2 border border-outline-variant rounded-lg text-body-sm focus:outline-none focus:border-primary">
                </div>

                <div class="divide-y divide-surface-container" id="menu-tree-container">
                    @foreach ($menus as $menu)
                        <div class="py-4 menu-group-item">
                            <div class="flex items-center justify-between bg-surface-container-low/50 hover:bg-surface-container-low p-3 rounded-lg transition-colors">
                                <div class="flex items-center gap-2.5">
                                    @php
                                        $routeVal = $menu->route_name ?: $menu->permission ?: ('group.' . $menu->id);
                                    @endphp
                                    <input type="checkbox" 
                                           class="role-checkbox parent-checkbox w-4 h-4 rounded border-outline-variant text-primary focus:ring-primary/20 cursor-pointer" 
                                           name="permission[{{ $menu->id }}]" 
                                           id="menu-{{ $menu->id }}" 
                                           value="{{ $routeVal }}">
                                    <label for="menu-{{ $menu->id }}" class="cursor-pointer font-bold text-on-surface text-body-md flex items-center gap-2 select-none">
                                        @if($menu->icon)
                                            <span class="material-symbols-outlined text-primary text-[20px]">{{ $menu->icon }}</span>
                                        @endif
                                        <span>{{ $menu->title }}</span>
                                        <span class="text-[11px] font-normal text-on-surface-variant bg-surface-container px-2 py-0.5 rounded-full">
                                            {{ count($menu->childs) }} Submenu
                                        </span>
                                    </label>
                                </div>
                                <button type="button" class="toggle-group-btn text-on-surface-variant hover:text-primary p-1">
                                    <span class="material-symbols-outlined transition-transform duration-200">expand_more</span>
                                </button>
                            </div>

                            @if(count($menu->childs))
                                <div class="group-children-content mt-2">
                                    @include('pages.roles.childs', [
                                        'childs' => $menu->childs, 
                                        'disabled' => false, 
                                        'rolePermissions' => []
                                    ])
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</form>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    // 1. Cascading checkbox check / uncheck (Parent -> Descendants)
    document.querySelectorAll('.role-checkbox').forEach(function (checkbox) {
        checkbox.addEventListener('change', function () {
            const isChecked = this.checked;
            const li = this.closest('li, .menu-group-item');
            if (li) {
                // Check or uncheck all descendants
                const childrenCheckboxes = li.querySelectorAll('.role-checkbox');
                childrenCheckboxes.forEach(function (childCb) {
                    childCb.checked = isChecked;
                });
            }

            // Check upwards to parent if all siblings checked
            updateParentCheckboxes(this);
        });
    });

    function updateParentCheckboxes(childCheckbox) {
        let parentLi = childCheckbox.closest('ul')?.closest('li, .menu-group-item');
        while (parentLi) {
            const parentCb = parentLi.querySelector(':scope > div input.role-checkbox');
            const subCheckboxes = parentLi.querySelectorAll(':scope > ul input.role-checkbox, :scope > div.group-children-content input.role-checkbox');
            if (parentCb && subCheckboxes.length > 0) {
                const someChecked = Array.from(subCheckboxes).some(cb => cb.checked);
                if (childCheckbox.checked) {
                    parentCb.checked = true;
                } else if (!someChecked) {
                    parentCb.checked = false;
                }
            }
            parentLi = parentLi.parentElement?.closest('li, .menu-group-item');
        }
    }

    // 2. Select All & Deselect All
    document.getElementById('btn-select-all').addEventListener('click', function () {
        document.querySelectorAll('.role-checkbox').forEach(cb => cb.checked = true);
    });

    document.getElementById('btn-deselect-all').addEventListener('click', function () {
        document.querySelectorAll('.role-checkbox').forEach(cb => cb.checked = false);
    });

    // 3. Toggle Collapsible Group
    document.querySelectorAll('.toggle-group-btn').forEach(btn => {
        btn.addEventListener('click', function () {
            const group = this.closest('.menu-group-item');
            const content = group.querySelector('.group-children-content');
            const icon = this.querySelector('.material-symbols-outlined');
            if (content) {
                content.classList.toggle('hidden');
                icon.style.transform = content.classList.contains('hidden') ? 'rotate(-90deg)' : 'rotate(0deg)';
            }
        });
    });

    // 4. Live Search Filter
    const searchInput = document.getElementById('permission-search');
    searchInput.addEventListener('input', function () {
        const query = this.value.toLowerCase().trim();
        document.querySelectorAll('.menu-group-item').forEach(group => {
            const text = group.textContent.toLowerCase();
            if (query === '' || text.includes(query)) {
                group.style.display = '';
                const content = group.querySelector('.group-children-content');
                if (content && query !== '') {
                    content.classList.remove('hidden');
                }
            } else {
                group.style.display = 'none';
            }
        });
    });
});
</script>
@endpush
@endsection
