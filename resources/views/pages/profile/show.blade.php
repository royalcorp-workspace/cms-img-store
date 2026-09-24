@extends('layouts.app')

@section('title', 'Profil Pengguna Admin')

@section('content')
    @php
        $authUser = $admin ?? auth('admin')->user();
        $nameParts = explode(' ', trim($authUser->name ?? 'Admin'));
        $initials = strtoupper(substr($nameParts[0] ?? 'A', 0, 1) . substr($nameParts[1] ?? '', 0, 1));
        if (empty(trim($initials))) $initials = 'AD';
    @endphp

    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="font-headline-lg text-headline-lg text-on-surface font-bold">Profil Admin</h1>
            <p class="text-body-md text-on-surface-variant mt-1">Kelola data profil, informasi hak akses role, dan keamanan kata sandi akun admin.</p>
        </div>
    </div>

    @include('layouts.partials.system-submenu')

    @if(session('success'))
        <div class="mb-6 p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl flex items-center gap-3 shadow-sm">
            <span class="material-symbols-outlined text-emerald-600">check_circle</span>
            <span class="text-sm font-medium">{{ session('success') }}</span>
        </div>
    @endif

    @if(isset($errors) && $errors->any())
        <div class="mb-6 p-4 bg-red-50 border border-red-200 text-red-800 rounded-xl flex items-start gap-3 shadow-sm">
            <span class="material-symbols-outlined text-red-600 mt-0.5">error</span>
            <div class="text-sm">
                <p class="font-bold mb-1">Terdapat kesalahan pengisian data:</p>
                <ul class="list-disc list-inside space-y-0.5 text-xs text-red-700">
                    @foreach($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
        <!-- User Profile Card -->
        <div class="bg-white p-6 rounded-xl shadow-sm border border-outline-variant/40 text-center flex flex-col justify-between">
            <div>
                <div class="relative w-24 h-24 mx-auto mb-4">
                    <div class="w-24 h-24 rounded-full bg-gradient-to-tr from-primary to-primary-container text-white flex items-center justify-center text-3xl font-bold shadow-md">
                        {{ $initials }}
                    </div>
                    <span class="absolute bottom-1 right-1 w-5 h-5 rounded-full bg-emerald-500 border-2 border-white" title="Akun Aktif"></span>
                </div>

                <h3 class="font-bold text-lg text-on-surface mb-1">{{ $authUser->name ?? 'Admin' }}</h3>
                
                <div class="flex flex-wrap items-center justify-center gap-1.5 mb-3">
                    @forelse($roles as $role)
                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-primary/10 text-primary border border-primary/20">
                            <span class="material-symbols-outlined text-[14px]">verified_user</span>
                            {{ $role->name }}
                        </span>
                    @empty
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-surface-container text-on-surface-variant">
                            No Role Assigned
                        </span>
                    @endforelse
                </div>

                <p class="text-xs text-on-surface-variant font-mono mb-4">{{ $authUser->email ?? '-' }}</p>

                <div class="p-3 bg-surface-container-low rounded-xl border border-outline-variant/40 text-left text-xs space-y-2 mb-4">
                    <div class="flex justify-between items-center text-on-surface-variant">
                        <span>Status Akun:</span>
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                            Aktif
                        </span>
                    </div>
                    @if($authUser->phone)
                        <div class="flex justify-between text-on-surface-variant">
                            <span>No. Telepon:</span>
                            <span class="font-medium text-on-surface">{{ $authUser->phone }}</span>
                        </div>
                    @endif
                    <div class="flex justify-between text-on-surface-variant">
                        <span>Total Izin Akses:</span>
                        <span class="font-bold text-primary font-mono">
                            {{ $isSuperAdmin ? 'Semua (' . $permissionsCount . ')' : $permissionsCount }} Izin
                        </span>
                    </div>
                    <div class="flex justify-between text-on-surface-variant">
                        <span>Terdaftar Sejak:</span>
                        <span class="font-medium text-on-surface">{{ $authUser->created_at ? $authUser->created_at->format('d M Y') : '-' }}</span>
                    </div>
                </div>
            </div>

            <div class="pt-4 border-t border-outline-variant/30 space-y-2">
                <a href="{{ route('roles.index') }}" class="flex items-center justify-center gap-2 w-full px-4 py-2 border border-outline-variant text-on-surface rounded-lg text-xs font-semibold hover:bg-surface-container-low transition-colors">
                    <span class="material-symbols-outlined text-[16px] text-primary">shield</span>
                    <span>Kelola Peran & Hak Akses</span>
                </a>
            </div>
        </div>

        <!-- Right Side: Settings & Details Tabs -->
        <div class="lg:col-span-2 bg-white rounded-xl shadow-sm border border-outline-variant/40 overflow-hidden">
            <div class="border-b border-outline-variant/40 bg-surface-container-low/30">
                <nav class="flex overflow-x-auto">
                    <button type="button" id="tabBtnInfo" onclick="switchProfileTab('info')" class="px-5 py-3.5 text-center font-bold text-xs sm:text-sm text-primary border-b-2 border-primary bg-white flex items-center justify-center gap-2 transition-colors whitespace-nowrap">
                        <span class="material-symbols-outlined text-[18px]">badge</span>
                        <span>Informasi Akun</span>
                    </button>
                    <button type="button" id="tabBtnPerms" onclick="switchProfileTab('perms')" class="px-5 py-3.5 text-center font-medium text-xs sm:text-sm text-on-surface-variant hover:text-on-surface border-b-2 border-transparent transition-colors flex items-center justify-center gap-2 whitespace-nowrap">
                        <span class="material-symbols-outlined text-[18px]">security</span>
                        <span>Hak Akses & Role</span>
                        <span class="px-1.5 py-0.2 rounded-full text-[10px] bg-primary/10 text-primary font-bold">{{ $permissionsCount }}</span>
                    </button>
                    <button type="button" id="tabBtnSecurity" onclick="switchProfileTab('security')" class="px-5 py-3.5 text-center font-medium text-xs sm:text-sm text-on-surface-variant hover:text-on-surface border-b-2 border-transparent transition-colors flex items-center justify-center gap-2 whitespace-nowrap">
                        <span class="material-symbols-outlined text-[18px]">lock_reset</span>
                        <span>Keamanan & Password</span>
                    </button>
                </nav>
            </div>

            <!-- TAB 1: INFORMASI AKUN -->
            <div id="panelInfo" class="p-6">
                <form action="{{ route('profile.update') }}" method="POST" class="space-y-5">
                    @csrf
                    @method('PUT')

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div class="space-y-1.5">
                            <label class="block text-xs font-bold text-on-surface-variant uppercase tracking-wider">Nama Lengkap</label>
                            <input type="text" name="name" value="{{ old('name', $authUser->name) }}" required class="w-full bg-white border border-outline-variant rounded-lg px-4 py-2.5 text-sm text-on-surface focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all">
                        </div>

                        <div class="space-y-1.5">
                            <label class="block text-xs font-bold text-on-surface-variant uppercase tracking-wider">Email Admin</label>
                            <input type="email" name="email" value="{{ old('email', $authUser->email) }}" required class="w-full bg-white border border-outline-variant rounded-lg px-4 py-2.5 text-sm text-on-surface focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all font-mono">
                        </div>

                        <div class="space-y-1.5">
                            <label class="block text-xs font-bold text-on-surface-variant uppercase tracking-wider">No. Telepon / WhatsApp</label>
                            <input type="text" name="phone" value="{{ old('phone', $authUser->phone) }}" placeholder="Contoh: 081234567890" class="w-full bg-white border border-outline-variant rounded-lg px-4 py-2.5 text-sm text-on-surface focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all">
                        </div>

                        <div class="space-y-1.5">
                            <label class="block text-xs font-bold text-on-surface-variant uppercase tracking-wider">Peran (Role Utama)</label>
                            <input type="text" value="{{ $roles->pluck('name')->implode(', ') ?: 'No Role' }}" readonly class="w-full bg-surface-container-low border border-outline-variant/60 rounded-lg px-4 py-2.5 text-sm text-on-surface-variant cursor-not-allowed">
                        </div>

                        <div class="space-y-1.5">
                            <label class="block text-xs font-bold text-on-surface-variant uppercase tracking-wider">ID Pengguna (UUID)</label>
                            <input type="text" value="{{ $authUser->id }}" readonly class="w-full bg-surface-container-low border border-outline-variant/60 rounded-lg px-4 py-2.5 text-xs text-on-surface-variant font-mono cursor-not-allowed">
                        </div>

                        <div class="space-y-1.5">
                            <label class="block text-xs font-bold text-on-surface-variant uppercase tracking-wider">Waktu Pembuatan Akun</label>
                            <input type="text" value="{{ $authUser->created_at ? $authUser->created_at->format('d M Y H:i:s') : '-' }}" readonly class="w-full bg-surface-container-low border border-outline-variant/60 rounded-lg px-4 py-2.5 text-sm text-on-surface-variant cursor-not-allowed">
                        </div>
                    </div>

                    <div class="flex justify-end pt-4 border-t border-outline-variant/30">
                        <button type="submit" class="inline-flex items-center gap-2 px-5 py-2.5 bg-primary text-white text-xs font-bold rounded-lg shadow hover:bg-primary-hover transition-colors">
                            <span class="material-symbols-outlined text-[18px]">save</span>
                            <span>Simpan Perubahan</span>
                        </button>
                    </div>
                </form>
            </div>

            <!-- TAB 2: HAK AKSES & ROLE -->
            <div id="panelPerms" class="p-6 space-y-6 hidden">
                <div class="bg-surface-container-low p-4 rounded-xl border border-outline-variant/40 flex items-start gap-3">
                    <span class="material-symbols-outlined text-primary text-[24px]">verified</span>
                    <div class="text-xs text-on-surface space-y-1">
                        <p class="font-bold text-sm">Tingkat Hak Akses</p>
                        @if($isSuperAdmin)
                            <p class="text-on-surface-variant">Akun Anda terdaftar sebagai <span class="font-bold text-primary">Super Administrator</span>. Anda memiliki akses penuh tanpa batas ke semua menu, endpoint rute, fungsi export/import, dan tindakan manipulasi data sistem.</p>
                        @else
                            <p class="text-on-surface-variant">Hak akses akun Anda disesuaikan berdasarkan peran <span class="font-bold text-primary">{{ $roles->pluck('name')->implode(', ') }}</span> yang diberikan oleh Super Admin.</p>
                        @endif
                    </div>
                </div>

                <div>
                    <h4 class="font-bold text-xs text-on-surface uppercase tracking-wider mb-3">Daftar Peran yang Dimiliki</h4>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        @forelse($roles as $role)
                            <div class="p-4 border border-outline-variant/40 rounded-xl bg-white shadow-xs">
                                <div class="flex items-center justify-between mb-2">
                                    <span class="font-bold text-sm text-on-surface">{{ $role->name }}</span>
                                    <span class="px-2 py-0.5 rounded text-[10px] font-mono font-bold bg-primary/10 text-primary">{{ $role->slug }}</span>
                                </div>
                                <p class="text-xs text-on-surface-variant mb-3">{{ $role->description ?: 'Tidak ada deskripsi peran.' }}</p>
                                <div class="flex items-center gap-1.5 text-[11px] text-on-surface-variant">
                                    <span class="material-symbols-outlined text-[14px] text-emerald-600">check</span>
                                    <span>{{ $role->permissions->count() }} Izin Terhubung</span>
                                </div>
                            </div>
                        @empty
                            <p class="text-xs text-on-surface-variant italic">Belum ada role yang diasosiasikan.</p>
                        @endforelse
                    </div>
                </div>

                <div>
                    <div class="flex items-center justify-between mb-3">
                        <h4 class="font-bold text-xs text-on-surface uppercase tracking-wider">Izin Akses Fitur (Permissions)</h4>
                        <span class="text-xs text-on-surface-variant font-mono">{{ $permissionsCount }} Total Izin</span>
                    </div>

                    <div class="max-h-64 overflow-y-auto border border-outline-variant/40 rounded-xl p-3 bg-surface-container-low/20">
                        <div class="flex flex-wrap gap-1.5">
                            @forelse($permissions as $perm)
                                <span class="inline-flex items-center px-2 py-1 rounded bg-white border border-outline-variant/40 text-[11px] font-mono text-on-surface-variant hover:text-primary hover:border-primary transition-colors" title="{{ $perm->name }}">
                                    {{ $perm->name }}
                                </span>
                            @empty
                                <span class="text-xs text-on-surface-variant italic p-2">Tidak ada permission individual yang ditemukan.</span>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>

            <!-- TAB 3: KEAMANAN & PASSWORD -->
            <div id="panelSecurity" class="p-6 hidden">
                <form action="{{ route('profile.password') }}" method="POST" class="space-y-5 max-w-lg">
                    @csrf
                    @method('PUT')

                    <div class="space-y-1.5">
                        <label class="block text-xs font-bold text-on-surface-variant uppercase tracking-wider">Kata Sandi Saat Ini</label>
                        <input type="password" name="current_password" required placeholder="Masukkan kata sandi lama Anda" class="w-full bg-white border border-outline-variant rounded-lg px-4 py-2.5 text-sm text-on-surface focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all">
                    </div>

                    <div class="space-y-1.5">
                        <label class="block text-xs font-bold text-on-surface-variant uppercase tracking-wider">Kata Sandi Baru</label>
                        <input type="password" name="password" required placeholder="Minimal 6 karakter" class="w-full bg-white border border-outline-variant rounded-lg px-4 py-2.5 text-sm text-on-surface focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all">
                    </div>

                    <div class="space-y-1.5">
                        <label class="block text-xs font-bold text-on-surface-variant uppercase tracking-wider">Konfirmasi Kata Sandi Baru</label>
                        <input type="password" name="password_confirmation" required placeholder="Ulangi kata sandi baru" class="w-full bg-white border border-outline-variant rounded-lg px-4 py-2.5 text-sm text-on-surface focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all">
                    </div>

                    <div class="p-3 bg-amber-50 rounded-xl border border-amber-200 text-xs text-amber-800 flex items-start gap-2">
                        <span class="material-symbols-outlined text-[18px] text-amber-600 mt-0.5">info</span>
                        <span>Disarankan menggunakan kombinasi huruf besar, huruf kecil, angka, dan karakter khusus untuk menjaga keamanan akses akun CMS Anda.</span>
                    </div>

                    <div class="pt-4 border-t border-outline-variant/30">
                        <button type="submit" class="inline-flex items-center gap-2 px-5 py-2.5 bg-primary text-white text-xs font-bold rounded-lg shadow hover:bg-primary-hover transition-colors">
                            <span class="material-symbols-outlined text-[18px]">key</span>
                            <span>Perbarui Kata Sandi</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
    function switchProfileTab(tab) {
        const btnInfo = document.getElementById('tabBtnInfo');
        const btnPerms = document.getElementById('tabBtnPerms');
        const btnSecurity = document.getElementById('tabBtnSecurity');

        const panelInfo = document.getElementById('panelInfo');
        const panelPerms = document.getElementById('panelPerms');
        const panelSecurity = document.getElementById('panelSecurity');

        const buttons = [btnInfo, btnPerms, btnSecurity];
        const panels = [panelInfo, panelPerms, panelSecurity];

        buttons.forEach(b => {
            b.classList.remove('border-primary', 'text-primary', 'bg-white', 'font-bold');
            b.classList.add('border-transparent', 'text-on-surface-variant', 'font-medium');
        });

        panels.forEach(p => p.classList.add('hidden'));

        if (tab === 'perms') {
            btnPerms.classList.add('border-primary', 'text-primary', 'bg-white', 'font-bold');
            btnPerms.classList.remove('border-transparent', 'text-on-surface-variant', 'font-medium');
            panelPerms.classList.remove('hidden');
        } else if (tab === 'security') {
            btnSecurity.classList.add('border-primary', 'text-primary', 'bg-white', 'font-bold');
            btnSecurity.classList.remove('border-transparent', 'text-on-surface-variant', 'font-medium');
            panelSecurity.classList.remove('hidden');
        } else {
            btnInfo.classList.add('border-primary', 'text-primary', 'bg-white', 'font-bold');
            btnInfo.classList.remove('border-transparent', 'text-on-surface-variant', 'font-medium');
            panelInfo.classList.remove('hidden');
        }
    }
    </script>
@endsection
