@extends('layouts.app')

@section('title', 'Admin Users')

@section('content')
<div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
    <div>
        <h1 class="font-headline-xl text-headline-xl text-on-surface">Manajemen Pengguna Admin</h1>
        <p class="text-body-md text-on-surface-variant">Kelola akun administrator CMS beserta penetapan hak akses role masing-masing.</p>
    </div>
    @can('users.store')
        <button type="button" onclick="openCreateModal()" class="flex items-center gap-2 px-5 py-2.5 bg-primary text-white rounded-lg font-headline-md text-headline-md hover:opacity-90 transition-all active:scale-95 shadow-sm">
            <span class="material-symbols-outlined text-[20px]">person_add</span>
            Tambah Admin Baru
        </button>
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

@if($errors->any())
    <div class="mb-6 p-4 rounded-xl bg-danger/10 border border-danger/20 text-danger text-body-md">
        <ul class="list-disc list-inside space-y-1">
            @foreach($errors->all() as $err)
                <li>{{ $err }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="bg-white rounded-xl shadow-subtle border border-surface-container overflow-hidden">
    <div class="p-6 border-b border-surface-container flex flex-col sm:flex-row justify-between items-center gap-4 bg-surface-container-low/30">
        <div>
            <h3 class="font-headline-md text-headline-md text-on-surface">Daftar Akun Admin</h3>
            <p class="text-body-sm text-on-surface-variant">Akun internal yang memiliki akses login ke CMS.</p>
        </div>
        <form method="GET" action="{{ route('users.index') }}" class="flex items-center gap-2 w-full sm:w-auto">
            <div class="relative w-full sm:w-64">
                <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-on-surface-variant text-[18px]">search</span>
                <input type="text" name="search" value="{{ $search }}" placeholder="Cari nama, email, no HP..." class="w-full pl-9 pr-3 py-1.5 border border-outline-variant rounded-lg text-body-sm focus:outline-none focus:border-primary">
            </div>
            @if($search)
                <a href="{{ route('users.index') }}" class="px-3 py-1.5 border border-outline-variant text-on-surface-variant rounded-lg text-body-sm hover:bg-surface-container">Reset</a>
            @endif
        </form>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-surface-container-low/50 text-on-surface-variant border-b border-surface-container font-label-md text-label-md">
                    <th class="px-6 py-3.5">Nama Admin</th>
                    <th class="px-6 py-3.5">Email</th>
                    <th class="px-6 py-3.5">No. Telepon</th>
                    <th class="px-6 py-3.5 text-center">Role / Hak Akses</th>
                    <th class="px-6 py-3.5 text-center">Status</th>
                    <th class="px-6 py-3.5 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-surface-container text-body-md text-on-surface">
                @forelse($users as $user)
                    @php
                        $userRole = $user->roles->first();
                    @endphp
                    <tr class="hover:bg-surface-container-low/30 transition-colors">
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-full bg-primary/10 text-primary flex items-center justify-center font-bold text-sm shrink-0">
                                    {{ strtoupper(substr($user->name, 0, 2)) }}
                                </div>
                                <div>
                                    <strong class="font-bold text-on-surface block">{{ $user->name }}</strong>
                                    <span class="text-xs text-on-surface-variant">ID: {{ substr($user->id, 0, 8) }}...</span>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 font-mono text-sm text-on-surface">
                            {{ $user->email }}
                        </td>
                        <td class="px-6 py-4 text-on-surface-variant text-sm">
                            {{ $user->phone ?: '-' }}
                        </td>
                        <td class="px-6 py-4 text-center">
                            @if($userRole)
                                <span class="px-2.5 py-1 bg-primary/10 text-primary rounded-full text-xs font-semibold">
                                    {{ $userRole->name }}
                                </span>
                            @else
                                <span class="px-2 py-0.5 bg-surface-container text-on-surface-variant rounded-full text-xs">
                                    Belum Ada Role
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-center">
                            @if($user->is_active)
                                <span class="px-2 py-0.5 bg-success/10 text-success rounded-full text-xs font-medium">Aktif</span>
                            @else
                                <span class="px-2 py-0.5 bg-danger/10 text-danger rounded-full text-xs font-medium">Nonaktif</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-right">
                            <div class="flex items-center justify-end gap-1.5">
                                @can('users.update')
                                    <button type="button" 
                                            onclick='openEditModal(@json($user), "{{ $userRole ? $userRole->id : '' }}")' 
                                            class="p-1.5 text-on-surface-variant hover:text-primary hover:bg-surface-container rounded-lg transition-colors" 
                                            title="Edit Admin">
                                        <span class="material-symbols-outlined text-[18px]">edit</span>
                                    </button>
                                @endcan
                                @can('users.destroy')
                                    @if(auth()->guard('admin')->id() !== $user->id)
                                        <form method="POST" action="{{ route('users.destroy', $user->id) }}" onsubmit="return confirm('Apakah Anda yakin ingin menonaktifkan admin {{ $user->name }}?')" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="p-1.5 text-on-surface-variant hover:text-danger hover:bg-danger/10 rounded-lg transition-colors" title="Nonaktifkan Admin">
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
                        <td colspan="6" class="px-6 py-12 text-center text-on-surface-variant">
                            <span class="material-symbols-outlined text-[48px] text-outline-variant mb-2 block">group_off</span>
                            <p class="font-medium text-body-lg">Tidak ada data pengguna admin ditemukan.</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($users->hasPages())
        <div class="p-4 border-t border-surface-container">
            {{ $users->links() }}
        </div>
    @endif
</div>

<!-- Modal Create Admin -->
<div id="modal-create-user" class="fixed inset-0 z-50 bg-black/50 backdrop-blur-xs hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-xl shadow-xl border border-surface-container max-w-md w-full overflow-hidden">
        <div class="p-5 border-b border-surface-container flex items-center justify-between bg-surface-container-low">
            <h3 class="font-headline-md text-headline-md text-on-surface flex items-center gap-2">
                <span class="material-symbols-outlined text-primary">person_add</span>
                Tambah Pengguna Admin
            </h3>
            <button type="button" onclick="closeCreateModal()" class="text-on-surface-variant hover:text-on-surface">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>
        <form method="POST" action="{{ route('users.store') }}" class="p-6 space-y-4">
            @csrf
            <div>
                <label for="create_name" class="block font-label-md text-label-md text-on-surface mb-1">Nama Lengkap <span class="text-danger">*</span></label>
                <input type="text" name="name" id="create_name" required placeholder="Contoh: Budi Santoso" class="w-full px-3 py-2 border border-outline-variant rounded-lg text-body-md focus:outline-none focus:border-primary">
            </div>
            <div>
                <label for="create_email" class="block font-label-md text-label-md text-on-surface mb-1">Email Login <span class="text-danger">*</span></label>
                <input type="email" name="email" id="create_email" required placeholder="admin@img.com" class="w-full px-3 py-2 border border-outline-variant rounded-lg text-body-md focus:outline-none focus:border-primary">
            </div>
            <div>
                <label for="create_phone" class="block font-label-md text-label-md text-on-surface mb-1">No. Telepon / WhatsApp</label>
                <input type="text" name="phone" id="create_phone" placeholder="08123456789" class="w-full px-3 py-2 border border-outline-variant rounded-lg text-body-md focus:outline-none focus:border-primary">
            </div>
            <div>
                <label for="create_role_id" class="block font-label-md text-label-md text-on-surface mb-1">Pilih Role (Hak Akses) <span class="text-danger">*</span></label>
                <select name="role_id" id="create_role_id" required class="w-full px-3 py-2 border border-outline-variant rounded-lg text-body-md focus:outline-none focus:border-primary">
                    <option value="">-- Pilih Role --</option>
                    @foreach($roles as $role)
                        <option value="{{ $role->id }}">{{ $role->name }} (Level {{ $role->level }})</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="create_password" class="block font-label-md text-label-md text-on-surface mb-1">Password <span class="text-danger">*</span></label>
                <input type="password" name="password" id="create_password" required minlength="6" placeholder="Minimal 6 karakter" class="w-full px-3 py-2 border border-outline-variant rounded-lg text-body-md focus:outline-none focus:border-primary">
            </div>
            <div class="flex items-center justify-end gap-2 pt-4 border-t border-surface-container">
                <button type="button" onclick="closeCreateModal()" class="px-4 py-2 border border-outline-variant text-on-surface-variant rounded-lg font-label-md hover:bg-surface-container">Batal</button>
                <button type="submit" class="px-5 py-2 bg-primary text-white rounded-lg font-label-md hover:opacity-90">Simpan Admin</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Edit Admin -->
<div id="modal-edit-user" class="fixed inset-0 z-50 bg-black/50 backdrop-blur-xs hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-xl shadow-xl border border-surface-container max-w-md w-full overflow-hidden">
        <div class="p-5 border-b border-surface-container flex items-center justify-between bg-surface-container-low">
            <h3 class="font-headline-md text-headline-md text-on-surface flex items-center gap-2">
                <span class="material-symbols-outlined text-primary">edit</span>
                Edit Pengguna Admin
            </h3>
            <button type="button" onclick="closeEditModal()" class="text-on-surface-variant hover:text-on-surface">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>
        <form id="form-edit-user" method="POST" action="" class="p-6 space-y-4">
            @csrf
            @method('PUT')
            <div>
                <label for="edit_name" class="block font-label-md text-label-md text-on-surface mb-1">Nama Lengkap <span class="text-danger">*</span></label>
                <input type="text" name="name" id="edit_name" required class="w-full px-3 py-2 border border-outline-variant rounded-lg text-body-md focus:outline-none focus:border-primary">
            </div>
            <div>
                <label for="edit_email" class="block font-label-md text-label-md text-on-surface mb-1">Email Login <span class="text-danger">*</span></label>
                <input type="email" name="email" id="edit_email" required class="w-full px-3 py-2 border border-outline-variant rounded-lg text-body-md focus:outline-none focus:border-primary">
            </div>
            <div>
                <label for="edit_phone" class="block font-label-md text-label-md text-on-surface mb-1">No. Telepon / WhatsApp</label>
                <input type="text" name="phone" id="edit_phone" class="w-full px-3 py-2 border border-outline-variant rounded-lg text-body-md focus:outline-none focus:border-primary">
            </div>
            <div>
                <label for="edit_role_id" class="block font-label-md text-label-md text-on-surface mb-1">Pilih Role (Hak Akses) <span class="text-danger">*</span></label>
                <select name="role_id" id="edit_role_id" required class="w-full px-3 py-2 border border-outline-variant rounded-lg text-body-md focus:outline-none focus:border-primary">
                    @foreach($roles as $role)
                        <option value="{{ $role->id }}">{{ $role->name }} (Level {{ $role->level }})</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="edit_password" class="block font-label-md text-label-md text-on-surface mb-1">Password Baru (Opsional)</label>
                <input type="password" name="password" id="edit_password" minlength="6" placeholder="Kosongkan jika tidak ingin mengubah password" class="w-full px-3 py-2 border border-outline-variant rounded-lg text-body-md focus:outline-none focus:border-primary">
            </div>
            <div>
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="is_active" id="edit_is_active" value="1" class="w-4 h-4 rounded border-outline-variant text-primary focus:ring-primary/20">
                    <span class="text-body-md text-on-surface font-medium">Status Akun Aktif</span>
                </label>
            </div>
            <div class="flex items-center justify-end gap-2 pt-4 border-t border-surface-container">
                <button type="button" onclick="closeEditModal()" class="px-4 py-2 border border-outline-variant text-on-surface-variant rounded-lg font-label-md hover:bg-surface-container">Batal</button>
                <button type="submit" class="px-5 py-2 bg-primary text-white rounded-lg font-label-md hover:opacity-90">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
function openCreateModal() {
    document.getElementById('modal-create-user').classList.remove('hidden');
}
function closeCreateModal() {
    document.getElementById('modal-create-user').classList.add('hidden');
}

function openEditModal(user, roleId) {
    const form = document.getElementById('form-edit-user');
    form.action = `/users/${user.id}`;
    document.getElementById('edit_name').value = user.name || '';
    document.getElementById('edit_email').value = user.email || '';
    document.getElementById('edit_phone').value = user.phone || '';
    document.getElementById('edit_role_id').value = roleId || '';
    document.getElementById('edit_is_active').checked = !!user.is_active;
    document.getElementById('edit_password').value = '';
    document.getElementById('modal-edit-user').classList.remove('hidden');
}
function closeEditModal() {
    document.getElementById('modal-edit-user').classList.add('hidden');
}
</script>
@endpush
@endsection