@extends('layouts.app')

@section('title', 'Profile')

@section('content')
    @php
        $authUser = $user ?? auth()->user();
        $nameParts = explode(' ', trim($authUser->name ?? 'User'));
        $initials = strtoupper(substr($nameParts[0] ?? 'U', 0, 1) . substr($nameParts[1] ?? '', 0, 1));
        if (empty(trim($initials))) $initials = 'U';
    @endphp

    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
        <div>
            <h1 class="font-headline-lg text-headline-lg text-on-surface">My Profile</h1>
            <p class="text-body-md text-on-surface-variant mt-1">Manage your personal information, order history, and account settings.</p>
        </div>
    </div>

    @include('layouts.partials.system-submenu')

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-container-gap mb-8">
        <!-- User Profile Card -->
        <div class="bg-white p-card-padding rounded-xl shadow-sm border border-outline-variant/30 text-center">
            <div class="w-20 h-20 rounded-full bg-primary text-white flex items-center justify-center text-[28px] font-headline-lg mx-auto mb-4 shadow-sm font-bold">
                {{ $initials }}
            </div>
            <h3 class="font-headline-md text-headline-md text-on-surface mb-1 font-bold">{{ $authUser->name ?? 'User' }}</h3>
            <p class="text-body-md text-secondary mb-1">
                {{ $customer ? 'Pelanggan Terdaftar' : ($authUser->roles->first()?->name ?? 'Pengguna Sistem') }}
            </p>
            <p class="text-label-sm text-on-surface-variant mb-6 font-mono">{{ $authUser->email ?? '-' }}</p>

            @if($customer)
                <div class="p-3 bg-surface-container-low rounded-xl border border-outline-variant/40 text-left text-xs space-y-1 mb-4">
                    <div class="flex justify-between text-on-surface-variant">
                        <span>Total Pesanan:</span>
                        <span class="font-bold text-primary font-mono">{{ $orders->count() }}</span>
                    </div>
                    @if($customer->phone)
                        <div class="flex justify-between text-on-surface-variant">
                            <span>No. Telepon:</span>
                            <span class="font-medium text-on-surface">{{ $customer->phone }}</span>
                        </div>
                    @endif
                </div>
            @endif

            <a href="{{ route('orders.index') }}" class="flex items-center gap-2 w-full px-4 py-2.5 border border-outline-variant text-on-surface rounded-lg font-label-md hover:bg-surface-container transition-colors justify-center text-xs font-semibold">
                <span class="material-symbols-outlined text-[18px] text-primary">receipt_long</span>
                <span>Semua Daftar Pesanan</span>
            </a>
        </div>

        <!-- Tabs Container -->
        <div class="lg:col-span-2 bg-white rounded-xl shadow-sm border border-outline-variant/30 overflow-hidden">
            <div class="border-b border-outline-variant">
                <nav class="flex">
                    <button type="button" id="tabBtnOrders" onclick="switchProfileTab('orders')" class="flex-1 px-4 py-3.5 text-center font-label-md font-bold text-primary border-b-2 border-primary bg-surface-container-low/30 flex items-center justify-center gap-1.5 transition-colors">
                        <span class="material-symbols-outlined text-[18px]">local_shipping</span>
                        <span>Riwayat Pesanan</span>
                        @if($orders->isNotEmpty())
                            <span class="px-1.5 py-0.2 rounded-full text-[10px] bg-primary/10 text-primary font-bold">{{ $orders->count() }}</span>
                        @endif
                    </button>
                    <button type="button" id="tabBtnInfo" onclick="switchProfileTab('info')" class="flex-1 px-4 py-3.5 text-center font-label-md text-on-surface-variant hover:text-on-surface border-b-2 border-transparent transition-colors flex items-center justify-center gap-1.5">
                        <span class="material-symbols-outlined text-[18px]">person</span>
                        <span>Informasi Akun</span>
                    </button>
                </nav>
            </div>

            <!-- TAB 1: RIWAYAT PESANAN (ORDER HISTORY) -->
            <div id="panelOrders" class="p-4 sm:p-6 space-y-4">
                <div class="flex items-center justify-between pb-2 border-b border-outline-variant/30">
                    <div>
                        <h4 class="font-bold text-sm text-on-surface">Riwayat Pesanan & Status Pengiriman</h4>
                        <p class="text-xs text-on-surface-variant">Nomor resi dan checkpoint pelacakan pengiriman kurir.</p>
                    </div>
                </div>

                <div class="overflow-x-auto border border-outline-variant/30 rounded-xl">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-surface-container-low border-b border-outline-variant/40">
                            <tr>
                                <th class="px-4 py-3 font-bold text-on-surface">No. Pesanan</th>
                                <th class="px-4 py-3 font-bold text-on-surface">Tanggal</th>
                                <th class="px-4 py-3 font-bold text-on-surface">Total</th>
                                <th class="px-4 py-3 font-bold text-on-surface">Status</th>
                                <th class="px-4 py-3 font-bold text-on-surface">Resi & Pengiriman</th>
                                <th class="px-4 py-3 font-bold text-on-surface text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-outline-variant/30">
                            @forelse($orders as $ord)
                                <tr class="hover:bg-surface-container-low/40 transition-colors">
                                    <td class="px-4 py-3 font-mono font-bold text-primary">
                                        <a href="{{ route('orders.show', $ord->id) }}" class="hover:underline">
                                            #{{ $ord->order_number ?? strtoupper(substr($ord->id, 0, 8)) }}
                                        </a>
                                    </td>
                                    <td class="px-4 py-3 text-on-surface-variant">
                                        {{ $ord->created_at ? $ord->created_at->format('d M Y') : '-' }}
                                    </td>
                                    <td class="px-4 py-3 font-semibold text-on-surface">
                                        Rp {{ number_format($ord->total ?? 0, 0, ',', '.') }}
                                    </td>
                                    <td class="px-4 py-3">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-bold {{ $ord->statusBadgeClass }}">
                                            {{ $ord->statusLabel() }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3">
                                        @if($ord->resi)
                                            <div class="space-y-1">
                                                <div class="font-mono font-bold text-xs text-primary select-all">
                                                    {{ $ord->resi }}
                                                </div>
                                                <div class="flex items-center gap-1 text-[11px] text-on-surface-variant flex-wrap">
                                                    <span class="font-medium">{{ $ord->courier_name ?? 'Kurir' }}</span>
                                                    <span>&bull;</span>
                                                    <span class="inline-flex items-center gap-0.5 px-1.5 py-0.2 rounded text-[10px] font-bold border {{ $ord->delivery_status_badge_class }}">
                                                        {{ $ord->delivery_status_label }}
                                                    </span>
                                                </div>
                                            </div>
                                        @elseif($ord->delivery_status)
                                            <div class="space-y-1">
                                                <div class="text-on-surface-variant italic text-[11px]">Resi belum terbit</div>
                                                <div class="flex items-center gap-1 text-[11px] text-on-surface-variant flex-wrap">
                                                    <span class="font-medium">{{ $ord->courier_name ?? 'Kurir' }}</span>
                                                    <span>&bull;</span>
                                                    <span class="inline-flex items-center gap-0.5 px-1.5 py-0.2 rounded text-[10px] font-bold border {{ $ord->delivery_status_badge_class }}">
                                                        {{ $ord->delivery_status_label }}
                                                    </span>
                                                </div>
                                            </div>
                                        @else
                                            <span class="text-on-surface-variant italic text-[11px]">Belum ada resi</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <a href="{{ route('orders.show', $ord->id) }}" class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg border border-outline-variant text-primary hover:bg-primary/5 text-xs font-semibold transition-colors">
                                            <span class="material-symbols-outlined text-[14px]">visibility</span>
                                            <span>Lacak</span>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-4 py-8 text-center text-on-surface-variant">
                                        Belum ada riwayat pesanan.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- TAB 2: PERSONAL INFORMATION -->
            <div id="panelInfo" class="p-gutter space-y-6 hidden">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-1.5">
                        <label class="block text-label-sm font-bold text-on-surface-variant uppercase tracking-wider">Name</label>
                        <input type="text" value="{{ $authUser->name ?? 'Admin' }}" class="w-full bg-surface-gray border border-outline-variant rounded-lg px-4 py-2.5 text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all" readonly>
                    </div>
                    <div class="space-y-1.5">
                        <label class="block text-label-sm font-bold text-on-surface-variant uppercase tracking-wider">Email Address</label>
                        <input type="email" value="{{ $authUser->email ?? 'admin@example.com' }}" class="w-full bg-surface-gray border border-outline-variant rounded-lg px-4 py-2.5 text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all" readonly>
                    </div>
                    <div class="space-y-1.5">
                        <label class="block text-label-sm font-bold text-on-surface-variant uppercase tracking-wider">Role</label>
                        <input type="text" value="{{ $authUser->roles->first()?->name ?? 'User' }}" class="w-full bg-surface-gray border border-outline-variant rounded-lg px-4 py-2.5 text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all" readonly>
                    </div>
                    <div class="space-y-1.5">
                        <label class="block text-label-sm font-bold text-on-surface-variant uppercase tracking-wider">Created At</label>
                        <input type="text" value="{{ $authUser->created_at ? $authUser->created_at->format('d M Y H:i') : '-' }}" class="w-full bg-surface-gray border border-outline-variant rounded-lg px-4 py-2.5 text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all" readonly>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
    function switchProfileTab(tab) {
        const btnOrders = document.getElementById('tabBtnOrders');
        const btnInfo = document.getElementById('tabBtnInfo');
        const panelOrders = document.getElementById('panelOrders');
        const panelInfo = document.getElementById('panelInfo');

        if (tab === 'info') {
            btnInfo.classList.add('border-primary', 'text-primary', 'bg-surface-container-low/30', 'font-bold');
            btnInfo.classList.remove('border-transparent', 'text-on-surface-variant');
            btnOrders.classList.remove('border-primary', 'text-primary', 'bg-surface-container-low/30', 'font-bold');
            btnOrders.classList.add('border-transparent', 'text-on-surface-variant');

            panelInfo.classList.remove('hidden');
            panelOrders.classList.add('hidden');
        } else {
            btnOrders.classList.add('border-primary', 'text-primary', 'bg-surface-container-low/30', 'font-bold');
            btnOrders.classList.remove('border-transparent', 'text-on-surface-variant');
            btnInfo.classList.remove('border-primary', 'text-primary', 'bg-surface-container-low/30', 'font-bold');
            btnInfo.classList.add('border-transparent', 'text-on-surface-variant');

            panelOrders.classList.remove('hidden');
            panelInfo.classList.add('hidden');
        }
    }
    </script>
@endsection
