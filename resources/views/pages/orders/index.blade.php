@extends('layouts.app')

@section('title', 'Orders')

@section('content')
<div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
    <div>
        <h1 class="font-headline-lg text-headline-lg text-on-surface">Orders</h1>
        <nav class="flex items-center gap-2 text-body-md text-on-surface-variant mt-1">
            <a href="{{ route('dashboard') }}" class="text-primary hover:underline">Dashboard</a>
            <span class="material-symbols-outlined text-[16px]">chevron_right</span>
            <span>Orders</span>
        </nav>
    </div>
    <button class="flex items-center gap-2 px-4 py-2 bg-primary text-white rounded-lg font-label-md text-label-md hover:opacity-90 transition-all shadow-sm">
        <span class="material-symbols-outlined text-[18px]">add</span>
        Create Order
    </button>
</div>

<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    <div class="bg-white rounded-xl shadow-sm border border-outline-variant/30 p-5">
        <p class="text-label-sm text-on-surface-variant mb-1">Total Orders</p>
        <h3 class="font-metric-display text-metric-display text-on-surface">{{ number_format($stats['total']) }}</h3>
    </div>
    <div class="bg-white rounded-xl shadow-sm border border-outline-variant/30 p-5">
        <p class="text-label-sm text-on-surface-variant mb-1">Draft</p>
        <h3 class="font-metric-display text-metric-display text-gray-500">{{ number_format($stats[0]) }}</h3>
    </div>
    <div class="bg-white rounded-xl shadow-sm border border-outline-variant/30 p-5">
        <p class="text-label-sm text-on-surface-variant mb-1">Pending Approval</p>
        <h3 class="font-metric-display text-metric-display text-warning">{{ number_format($stats[1]) }}</h3>
    </div>
    <div class="bg-white rounded-xl shadow-sm border border-outline-variant/30 p-5">
        <p class="text-label-sm text-on-surface-variant mb-1">Confirmed</p>
        <h3 class="font-metric-display text-metric-display text-blue-500">{{ number_format($stats[2]) }}</h3>
    </div>
    <div class="bg-white rounded-xl shadow-sm border border-outline-variant/30 p-5">
        <p class="text-label-sm text-on-surface-variant mb-1">Processing</p>
        <h3 class="font-metric-display text-metric-display text-indigo-500">{{ number_format($stats[3]) }}</h3>
    </div>
    <div class="bg-white rounded-xl shadow-sm border border-outline-variant/30 p-5">
        <p class="text-label-sm text-on-surface-variant mb-1">Shipped</p>
        <h3 class="font-metric-display text-metric-display text-purple-500">{{ number_format($stats[4]) }}</h3>
    </div>
    <div class="bg-white rounded-xl shadow-sm border border-outline-variant/30 p-5">
        <p class="text-label-sm text-on-surface-variant mb-1">Delivered</p>
        <h3 class="font-metric-display text-metric-display text-success">{{ number_format($stats[5]) }}</h3>
    </div>
    <div class="bg-white rounded-xl shadow-sm border border-outline-variant/30 p-5">
        <p class="text-label-sm text-on-surface-variant mb-1">Cancelled</p>
        <h3 class="font-metric-display text-metric-display text-danger">{{ number_format($stats[6]) }}</h3>
    </div>
    <div class="bg-white rounded-xl shadow-sm border border-outline-variant/30 p-5">
        <p class="text-label-sm text-on-surface-variant mb-1">Returned</p>
        <h3 class="font-metric-display text-metric-display text-orange-500">{{ number_format($stats[7]) }}</h3>
    </div>
</div>

<div class="bg-white rounded-xl shadow-sm border border-outline-variant/30 overflow-hidden">
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
                        <td class="px-gutter py-4 font-body-md text-body-md text-on-surface font-medium">Rp{{ number_format($order->total, 2, ',', '.') }}</td>
                        <td class="px-gutter py-4">
                            <div class="flex gap-2">
                                <a href="{{ route('orders.show', $order->id) }}" class="text-on-surface-variant hover:text-primary" title="View"><span class="material-symbols-outlined text-[18px]">visibility</span></a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-gutter py-8 text-center text-on-surface-variant">No orders found</td>
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
@endsection
