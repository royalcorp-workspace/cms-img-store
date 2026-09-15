@extends('layouts.app')

@section('title', 'Orders')

@section('content')
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="font-headline-lg text-headline-lg text-on-surface">Orders</h1>
            <nav class="flex items-center gap-2 text-label-sm text-on-surface-variant mt-1 font-medium">
                <a href="{{ route('dashboard') }}" class="hover:text-primary transition-colors">eCommerce</a>
                <span class="material-symbols-outlined text-[14px]">chevron_right</span>
                <span class="text-on-surface">Orders</span>
            </nav>
        </div>
    </div>

    @include('layouts.partials.sales-submenu')

    <div class="bg-white rounded-2xl shadow-sm border border-outline-variant/30 overflow-hidden">
        <div class="p-4 border-b border-outline-variant flex flex-col sm:flex-row gap-3 justify-between">
            <div class="flex items-center gap-2">
                <div class="relative">
                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-on-surface-variant">
                        <span class="material-symbols-outlined text-[18px]">search</span>
                    </span>
                    <input type="text" id="searchInput" placeholder="Search orders..." class="pl-9 pr-4 py-2 border border-outline-variant rounded-lg text-body-md focus:ring-2 focus:ring-primary/20 focus:outline-none w-64" value="{{ request('search') }}">
                </div>
                <select id="statusFilter" class="px-3 py-2 border border-outline-variant rounded-lg text-body-md focus:ring-2 focus:ring-primary/20 focus:outline-none bg-white">
                    <option value="">All Status</option>
                    <option value="0" {{ request('status') === '0' ? 'selected' : '' }}>Draft</option>
                    <option value="1" {{ request('status') === '1' ? 'selected' : '' }}>Pending Approval</option>
                    <option value="2" {{ request('status') === '2' ? 'selected' : '' }}>Confirmed</option>
                    <option value="3" {{ request('status') === '3' ? 'selected' : '' }}>Processing</option>
                    <option value="4" {{ request('status') === '4' ? 'selected' : '' }}>Shipped</option>
                    <option value="5" {{ request('status') === '5' ? 'selected' : '' }}>Delivered</option>
                    <option value="6" {{ request('status') === '6' ? 'selected' : '' }}>Cancelled</option>
                    <option value="7" {{ request('status') === '7' ? 'selected' : '' }}>Returned</option>
                </select>
            </div>
            <div class="flex items-center gap-2">
                <input type="date" id="startDate" class="px-3 py-2 border border-outline-variant rounded-lg text-body-md focus:ring-2 focus:ring-primary/20 focus:outline-none bg-white" value="{{ request('start_date', $startDate) }}">
                <span class="text-on-surface-variant">to</span>
                <input type="date" id="endDate" class="px-3 py-2 border border-outline-variant rounded-lg text-body-md focus:ring-2 focus:ring-primary/20 focus:outline-none bg-white" value="{{ request('end_date', $endDate) }}">
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-surface-gray">
                        <th class="px-gutter py-4 font-label-md text-label-md text-on-surface-variant uppercase tracking-wider">Order ID</th>
                        <th class="px-gutter py-4 font-label-md text-label-md text-on-surface-variant uppercase tracking-wider">Date</th>
                        <th class="px-gutter py-4 font-label-md text-label-md text-on-surface-variant uppercase tracking-wider">Customer</th>
                        <th class="px-gutter py-4 font-label-md text-label-md text-on-surface-variant uppercase tracking-wider">Payment</th>
                        <th class="px-gutter py-4 font-label-md text-label-md text-on-surface-variant uppercase tracking-wider text-center">Status</th>
                        <th class="px-gutter py-4 font-label-md text-label-md text-on-surface-variant uppercase tracking-wider">Resi</th>
                        <th class="px-gutter py-4 font-label-md text-label-md text-on-surface-variant uppercase tracking-wider">Total</th>
                        <th class="px-gutter py-4 font-label-md text-label-md text-on-surface-variant uppercase tracking-wider text-center">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-outline-variant/20">
                    @forelse($orders as $order)
                        <tr class="hover:bg-surface-container/30 transition-colors">
                            <td class="px-gutter py-4">
                                <span class="font-headline-md text-headline-md text-primary font-semibold">#{{ $order->order_number }}</span>
                            </td>
                            <td class="px-gutter py-4 font-body-md text-body-md text-on-surface-variant">{{ $order->created_at->format('d M Y') }}</td>
                            <td class="px-gutter py-4">
                                @if($order->customer)
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-full bg-primary flex items-center justify-center text-white text-label-sm font-bold">
                                            {{ strtoupper(substr($order->customer->name, 0, 2)) }}
                                        </div>
                                        <span class="font-body-md text-body-md text-on-surface">{{ $order->customer->name }}</span>
                                    </div>
                                @else
                                    <span class="text-on-surface-variant">Guest</span>
                                @endif
                            </td>
                            <td class="px-gutter py-4 font-body-md text-body-md text-on-surface-variant">{{ ucfirst(str_replace('_', ' ', $order->payment_method ?? 'N/A')) }}</td>
                            <td class="px-gutter py-4 text-center">
                                @php
                                    $statusClass = match($order->status) {
                                        \App\Models\Order\Order::STATUS_DRAFT => 'bg-gray-100 text-gray-600',
                                        \App\Models\Order\Order::STATUS_PENDING_APPROVAL => 'bg-warning/10 text-warning',
                                        \App\Models\Order\Order::STATUS_CONFIRMED => 'bg-blue-100 text-blue-700',
                                        \App\Models\Order\Order::STATUS_PROCESSING => 'bg-indigo-100 text-indigo-700',
                                        \App\Models\Order\Order::STATUS_SHIPPED => 'bg-purple-100 text-purple-700',
                                        \App\Models\Order\Order::STATUS_DELIVERED => 'bg-success/10 text-success',
                                        \App\Models\Order\Order::STATUS_CANCELLED => 'bg-danger/10 text-danger',
                                        \App\Models\Order\Order::STATUS_RETURNED => 'bg-orange-100 text-orange-700',
                                        default => 'bg-gray-100 text-gray-600',
                                    };
                                    $statusLabel = $order->statusLabel();
                                @endphp
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-label-sm font-label-sm {{ $statusClass }}">
                                    <span class="w-1.5 h-1.5 rounded-full bg-current"></span> {{ $statusLabel }}
                                </span>
                            </td>
                            <td class="px-gutter py-4" id="order-resi-badge-{{ $order->id }}">
                                <script>
                                    window.ordersCache = window.ordersCache || {};
                                    window.ordersCache['{{ $order->id }}'] = @json($order->resi_modal_data);
                                </script>
                                @if($order->resi)
                                    <div class="flex items-center gap-1.5">
                                        <button type="button" onclick="openResiModal('{{ $order->id }}', 'tracking')" class="font-mono text-xs font-bold text-primary bg-primary/5 hover:bg-primary/10 px-2 py-0.5 rounded border border-primary/20 text-left transition-colors" title="Lihat Status Resi">
                                            {{ $order->resi }}
                                        </button>
                                        <button type="button" onclick="openResiModal('{{ $order->id }}', 'tracking')" class="p-1 text-on-surface-variant hover:text-primary transition-colors" title="Lihat Status Resi">
                                            <span class="material-symbols-outlined text-[16px]">visibility</span>
                                        </button>
                                    </div>
                                    <div class="flex items-center gap-1 text-[10px] text-on-surface-variant mt-1 flex-wrap">
                                        <span class="font-medium">{{ $order->courier_name ?? 'Kurir' }}</span>
                                        <span>&bull;</span>
                                        <span class="inline-flex items-center gap-0.5 px-1.5 py-0.2 rounded text-[9px] font-bold border {{ $order->delivery_status_badge_class }}">
                                            {{ $order->delivery_status_label }}
                                        </span>
                                    </div>
                                @elseif($order->status >= \App\Models\Order\Order::STATUS_SHIPPED)
                                    <div class="space-y-1">
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded bg-slate-100 text-slate-500 text-[10px] font-semibold border border-slate-200" title="Resi sudah terkunci (Pesanan {{ $order->statusLabel() }})">
                                            <span class="material-symbols-outlined text-[12px]">lock</span>
                                            <span>Resi Terkunci</span>
                                        </span>
                                        @if($order->delivery_status)
                                            <div class="inline-flex items-center gap-0.5 px-1.5 py-0.2 rounded text-[9px] font-bold border {{ $order->delivery_status_badge_class }}">
                                                {{ $order->delivery_status_label }}
                                            </div>
                                        @endif
                                    </div>
                                @elseif($order->delivery_status)
                                    <button type="button" onclick="openResiModal('{{ $order->id }}', 'tracking')" class="text-left group" title="Lihat riwayat logs">
                                        <div class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-bold border {{ $order->delivery_status_badge_class }}">
                                            {{ $order->delivery_status_label }}
                                        </div>
                                        <div class="text-[9px] text-primary group-hover:underline mt-0.5">+ Tambah Resi</div>
                                    </button>
                                @else
                                    <button type="button" onclick="openResiModal('{{ $order->id }}')" class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg border border-dashed border-primary/40 text-[11px] font-semibold text-primary hover:bg-primary/5 transition-colors">
                                        <span class="material-symbols-outlined text-[14px]">add</span>
                                        <span>+ Resi</span>
                                    </button>
                                @endif
                            </td>
                            <td class="px-gutter py-4 font-body-md text-body-md text-on-surface font-medium">Rp{{ number_format($order->total, 2, ',', '.') }}</td>
                            <td class="px-gutter py-4">
                                <div class="flex gap-2 justify-center">
                                    <button type="button" onclick="openResiModal('{{ $order->id }}', '{{ ($order->resi || $order->delivery_status) ? 'tracking' : 'manual' }}')" class="text-on-surface-variant hover:text-primary transition-colors" title="Resi & Log Pengiriman">
                                        <span class="material-symbols-outlined text-[18px]">local_shipping</span>
                                    </button>
                                    <a href="{{ route('orders.show', $order->id) }}" class="text-on-surface-variant hover:text-primary transition-colors" title="View"><span class="material-symbols-outlined text-[18px]">visibility</span></a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-gutter py-8 text-center text-on-surface-variant">No orders found</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-gutter py-4 border-t border-outline-variant bg-surface-container-low/30 flex justify-between items-center">
            <p class="font-body-md text-body-md text-on-surface-variant">Showing {{ $orders->firstItem() ?? 0 }}-{{ $orders->lastItem() ?? 0 }} of {{ number_format($orders->total()) }} orders</p>
            <div class="flex gap-2">
                {{ $orders->links() }}
            </div>
        </div>
    </div>

    <script>
    function buildUrl() {
        const search = document.getElementById('searchInput').value;
        const status = document.getElementById('statusFilter').value;
        const startDate = document.getElementById('startDate').value;
        const endDate = document.getElementById('endDate').value;

        const params = new URLSearchParams();
        if (search) params.set('search', search);
        if (status !== '') params.set('status', status);
        if (startDate) params.set('start_date', startDate);
        if (endDate) params.set('end_date', endDate);

        const query = params.toString();
        return query ? '?' + query : window.location.pathname;
    }

    document.getElementById('searchInput').addEventListener('input', function() {
        window.location.href = buildUrl();
    });

    document.getElementById('statusFilter').addEventListener('change', function() {
        window.location.href = buildUrl();
    });

    document.getElementById('startDate').addEventListener('change', function() {
        window.location.href = buildUrl();
    });

    document.getElementById('endDate').addEventListener('change', function() {
        window.location.href = buildUrl();
    });
    </script>

    @include('pages.orders.partials.resi-modal')
@endsection
