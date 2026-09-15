@extends('layouts.app')

@section('title', 'Delivery')

@section('content')
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="font-headline-lg text-headline-lg text-on-surface">Delivery</h1>
            <nav class="flex items-center gap-2 text-label-sm text-on-surface-variant mt-1 font-medium">
                <a href="{{ route('dashboard') }}" class="hover:text-primary transition-colors">eCommerce</a>
                <span class="material-symbols-outlined text-[14px]">chevron_right</span>
                <span>Pick & Pack</span>
                <span class="material-symbols-outlined text-[14px]">chevron_right</span>
                <span class="text-on-surface">Delivery</span>
            </nav>
        </div>
    </div>

    @include('layouts.partials.pick-pack-submenu')

    <div class="bg-white rounded-2xl shadow-sm border border-outline-variant/30 overflow-hidden mb-8">
        <div class="p-4 border-b border-outline-variant/30 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <form action="{{ route('delivery.index') }}" method="GET" class="flex flex-wrap items-center gap-3">
                <div class="relative">
                    <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-on-surface-variant text-[18px]">search</span>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari delivery / order / resi..." class="pl-9 pr-4 py-2 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary/20 focus:outline-none w-64 text-label-sm bg-surface-container-lowest">
                </div>
                <select name="status" class="px-3 py-2 border border-outline-variant rounded-lg text-label-sm focus:ring-2 focus:ring-primary/20 focus:outline-none bg-surface-container-lowest">
                    <option value="">Semua Status</option>
                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="in_transit" {{ request('status') === 'in_transit' ? 'selected' : '' }}>In Transit</option>
                    <option value="delivered" {{ request('status') === 'delivered' ? 'selected' : '' }}>Delivered</option>
                    <option value="failed" {{ request('status') === 'failed' ? 'selected' : '' }}>Failed</option>
                    <option value="returned" {{ request('status') === 'returned' ? 'selected' : '' }}>Returned</option>
                </select>
                <button type="submit" class="px-4 py-2 bg-secondary text-white rounded-lg font-label-md text-xs hover:opacity-90 transition-all">Filter</button>
                @if(request('search') || request('status'))
                    <a href="{{ route('delivery.index') }}" class="px-3 py-2 border border-outline-variant text-on-surface-variant rounded-lg font-label-md text-xs hover:bg-surface-container transition-all">Reset</a>
                @endif
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-surface-gray border-b border-outline-variant/50">
                        <th class="px-6 py-4 font-label-md text-label-md text-on-surface-variant uppercase tracking-wider">Delivery ID</th>
                        <th class="px-6 py-4 font-label-md text-label-md text-on-surface-variant uppercase tracking-wider">Order ID</th>
                        <th class="px-6 py-4 font-label-md text-label-md text-on-surface-variant uppercase tracking-wider">Customer</th>
                        <th class="px-6 py-4 font-label-md text-label-md text-on-surface-variant uppercase tracking-wider">Courier</th>
                        <th class="px-6 py-4 font-label-md text-label-md text-on-surface-variant uppercase tracking-wider">Tracking</th>
                        <th class="px-6 py-4 font-label-md text-label-md text-on-surface-variant uppercase tracking-wider text-center">Status</th>
                        <th class="px-6 py-4 font-label-md text-label-md text-on-surface-variant uppercase tracking-wider text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-outline-variant/20">
                    @forelse($deliveries as $delivery)
                        <tr class="hover:bg-surface-container/30 transition-colors group">
                            <td class="px-6 py-4">
                                <span class="font-headline-md text-headline-md text-primary font-semibold font-mono">#{{ substr($delivery->id, 0, 8) }}</span>
                            </td>
                            <td class="px-6 py-4 font-body-md text-body-md text-on-surface">
                                @if($delivery->order)
                                    <a href="{{ route('orders.show', $delivery->order->id) }}" class="text-primary hover:underline font-mono">
                                        {{ $delivery->order->order_number ?? '#' . substr($delivery->order->id, 0, 8) }}
                                    </a>
                                @else
                                    <span class="text-on-surface-variant">-</span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-full bg-primary/10 text-primary flex items-center justify-center text-label-sm font-bold border border-primary/20">
                                        {{ strtoupper(substr($delivery->order?->customer?->name ?? 'NA', 0, 2)) }}
                                    </div>
                                    <span class="font-body-md text-body-md text-on-surface">{{ $delivery->order?->customer?->name ?? 'N/A' }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 font-body-md text-body-md text-on-surface-variant">{{ $delivery->courier->name ?? '-' }}</td>
                            <td class="px-6 py-4 font-body-md text-body-md text-on-surface-variant">{{ $delivery->tracking_number ?? '-' }}</td>
                            <td class="px-6 py-4 text-center">
                                @php
                                    $statusMap = [
                                        'pending' => 'bg-surface-container text-on-surface-variant border-outline-variant/30',
                                        'in_transit' => 'bg-primary/10 text-primary border-primary/20',
                                        'delivered' => 'bg-success/10 text-success border-success/20',
                                        'failed' => 'bg-danger/10 text-danger border-danger/20',
                                        'returned' => 'bg-warning/10 text-warning border-warning/20'
                                    ];
                                    $statusBadge = $statusMap[$delivery->status] ?? 'bg-surface-container text-on-surface-variant border-outline-variant/30';
                                @endphp
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold border {{ $statusBadge }}">
                                    <span class="w-1.5 h-1.5 rounded-full bg-current"></span> {{ ucfirst(str_replace('_', ' ', $delivery->status)) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('delivery.show', $delivery->id) }}" class="inline-flex items-center gap-1 px-3 py-1.5 bg-surface-container-high hover:bg-surface-container-highest text-on-surface rounded-xl text-xs font-semibold transition-colors" title="Lihat Detail">
                                        <span class="material-symbols-outlined text-[16px]">visibility</span>
                                        <span>Detail</span>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-12 text-center text-on-surface-variant">
                                <div class="flex flex-col items-center justify-center">
                                    <span class="material-symbols-outlined text-[48px] mb-2 opacity-40">local_shipping</span>
                                    <p class="font-label-md">Tidak ada data delivery ditemukan.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($deliveries->hasPages())
            <div class="px-6 py-4 border-t border-outline-variant/30 bg-surface-container-low/30 flex flex-col sm:flex-row justify-between items-center gap-3">
                <p class="font-body-md text-body-md text-on-surface-variant">Showing {{ $deliveries->firstItem() }} to {{ $deliveries->lastItem() }} of {{ $deliveries->total() }} deliveries</p>
                <div class="flex gap-2">{{ $deliveries->links() }}</div>
            </div>
        @endif
    </div>
@endsection