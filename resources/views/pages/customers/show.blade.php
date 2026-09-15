@extends('layouts.app')

@section('title', 'Customer Details')

@section('content')
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="font-headline-lg text-headline-lg text-on-surface">Customer Details</h1>
            <nav class="flex items-center gap-2 text-body-md text-on-surface-variant mt-1">
                <a href="{{ route('dashboard') }}" class="text-primary hover:underline">Dashboard</a>
                <span class="material-symbols-outlined text-[16px]">chevron_right</span>
                <a href="{{ route('customers.index') }}" class="text-primary hover:underline">Customers</a>
                <span class="material-symbols-outlined text-[16px]">chevron_right</span>
                <span>{{ $customer->name }}</span>
            </nav>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('customers.edit', $customer->id) }}" class="flex items-center gap-2 px-4 py-2 bg-primary text-white rounded-lg font-label-md hover:opacity-90 transition-all shadow-sm">
                <span class="material-symbols-outlined text-[18px]">edit</span> Edit
            </a>
        </div>
    </div>

    @include('layouts.partials.customer-submenu')

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="bg-white rounded-xl shadow-sm border border-outline-variant/30">
            <div class="p-6">
                <div class="flex items-center justify-center mb-4">
                    <div class="w-24 h-24 bg-surface-container-low rounded-full flex items-center justify-center overflow-hidden">
                        <span class="font-metric-display text-metric-display text-primary">{{ substr($customer->name, 0, 1) }}</span>
                    </div>
                </div>
                <h2 class="font-headline-md text-headline-md text-on-surface text-center mb-1">{{ $customer->name }}</h2>
                <p class="text-body-md text-on-surface-variant text-center mb-4">{{ $customer->email }}</p>
                <div class="flex items-center justify-center gap-2">
                    @if($customer->deleted)
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 bg-danger/10 text-danger rounded-full text-label-sm font-label-sm">
                        <span class="w-1.5 h-1.5 rounded-full bg-danger"></span> Inactive
                    </span>
                    @else
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 bg-success/10 text-success rounded-full text-label-sm font-label-sm">
                        <span class="w-1.5 h-1.5 rounded-full bg-success"></span> Active
                    </span>
                    @endif
                </div>
            </div>
        </div>

        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white rounded-xl shadow-sm border border-outline-variant/30">
                <div class="p-6 border-b border-outline-variant/30">
                    <h3 class="font-headline-md text-headline-md text-on-surface">Customer Information</h3>
                </div>
                <div class="p-6 grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <p class="text-label-sm text-on-surface-variant uppercase tracking-wider mb-1">Phone</p>
                        <p class="font-body-md text-body-md text-on-surface">{{ $customer->phone ?? '-' }}</p>
                    </div>
                    <div>
                        <p class="text-label-sm text-on-surface-variant uppercase tracking-wider mb-1">User Account</p>
                        <p class="font-body-md text-body-md text-on-surface">{{ $customer->user->name ?? 'Not linked' }}</p>
                    </div>
                    <div>
                        <p class="text-label-sm text-on-surface-variant uppercase tracking-wider mb-1">Created At</p>
                        <p class="font-body-md text-body-md text-on-surface">{{ $customer->created_at?->format('d M Y H:i') ?? '-' }}</p>
                    </div>
                    <div>
                        <p class="text-label-sm text-on-surface-variant uppercase tracking-wider mb-1">Total Orders</p>
                        <p class="font-body-md text-body-md text-on-surface">{{ $customer->orders->count() }}</p>
                    </div>
                    <div class="md:col-span-2">
                        <p class="text-label-sm text-on-surface-variant uppercase tracking-wider mb-1">Total Belanja Nominal</p>
                        <p class="font-headline-sm text-headline-sm text-primary font-bold">
                            Rp {{ number_format($customer->orders->where('status', '!=', \App\Models\Order\Order::STATUS_CANCELLED)->sum('total'), 0, ',', '.') }}
                        </p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-outline-variant/30">
                <div class="p-6 border-b border-outline-variant/30">
                    <h3 class="font-headline-md text-headline-md text-on-surface">Addresses</h3>
                </div>
                <div class="p-6">
                    @if($customer->addresses->count() > 0)
                    <div class="space-y-4">
                        @foreach($customer->addresses as $address)
                        <div class="border border-outline-variant/30 rounded-lg p-4">
                            <p class="font-body-md text-body-md text-on-surface font-medium">{{ $address->label ?? 'Address' }}</p>
                            <p class="text-body-md text-on-surface-variant">{{ $address->recipient_name ?? $customer->name }}</p>
                            <p class="text-body-md text-on-surface-variant">{{ $address->address ?? '-' }}</p>
                            <p class="text-body-md text-on-surface-variant">{{ $address->city?->name ?? '' }} {{ $address->subDistrict?->name ?? '' }} {{ $address->postal_code ?? '' }}</p>
                            <p class="text-body-md text-on-surface-variant">{{ $address->phone ?? '-' }}</p>
                            @if($address->is_primary)
                            <span class="inline-flex items-center px-2 py-0.5 bg-primary/10 text-primary rounded text-label-sm mt-2">Primary</span>
                            @endif
                        </div>
                        @endforeach
                    </div>
                    @else
                    <p class="text-body-md text-on-surface-variant">No addresses found</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Order History Section -->
    <div class="mt-6">
        <div class="bg-white rounded-xl shadow-sm border border-outline-variant/30">
            <div class="p-6 border-b border-outline-variant/30">
                <h3 class="font-headline-md text-headline-md text-on-surface">Order History</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead class="bg-surface-gray">
                        <tr>
                            <th class="px-gutter py-4 font-headline-md text-[13px]">Order Number</th>
                            <th class="px-gutter py-4 font-headline-md text-[13px]">Date</th>
                            <th class="px-gutter py-4 font-headline-md text-[13px]">Total</th>
                            <th class="px-gutter py-4 font-headline-md text-[13px]">Status Pesanan</th>
                            <th class="px-gutter py-4 font-headline-md text-[13px]">Resi & Status Pengiriman</th>
                            <th class="px-gutter py-4 font-headline-md text-[13px] text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-outline-variant">
                        @forelse($customer->orders->sortByDesc('created_at') as $order)
                        <tr class="hover:bg-surface-container-low transition-colors">
                            <td class="px-gutter py-4 text-body-md font-medium text-primary">
                                <a href="{{ route('orders.show', $order->id) }}">#{{ strtoupper(substr($order->id, 0, 8)) }}</a>
                            </td>
                            <td class="px-gutter py-4 text-body-md">{{ $order->created_at->format('d M Y') }}</td>
                            <td class="px-gutter py-4 text-body-md font-medium">
                                Rp {{ number_format($order->total ?? 0, 0, ',', '.') }}
                                <br><span class="text-xs text-on-surface-variant font-normal">{{ $order->items ? $order->items->count() : 0 }} items</span>
                            </td>
                            <td class="px-gutter py-4">
                                @if($order->status == \App\Models\Order\Order::STATUS_DRAFT)
                                <span class="px-2 py-1 bg-surface-gray text-on-surface-variant text-xs font-bold rounded">DRAFT</span>
                                @elseif($order->status == \App\Models\Order\Order::STATUS_PENDING_APPROVAL)
                                <span class="px-2 py-1 bg-warning/20 text-warning text-xs font-bold rounded">PENDING</span>
                                @elseif($order->status == \App\Models\Order\Order::STATUS_CONFIRMED)
                                <span class="px-2 py-1 bg-primary/20 text-primary text-xs font-bold rounded">CONFIRMED</span>
                                @elseif($order->status == \App\Models\Order\Order::STATUS_PROCESSING)
                                <span class="px-2 py-1 bg-primary/20 text-primary text-xs font-bold rounded">PROCESSING</span>
                                @elseif($order->status == \App\Models\Order\Order::STATUS_SHIPPED)
                                <span class="px-2 py-1 bg-success/20 text-success text-xs font-bold rounded">SHIPPED</span>
                                @elseif($order->status == \App\Models\Order\Order::STATUS_DELIVERED)
                                <span class="px-2 py-1 bg-success/20 text-success text-xs font-bold rounded">DELIVERED</span>
                                @elseif($order->status == \App\Models\Order\Order::STATUS_CANCELLED)
                                <span class="px-2 py-1 bg-danger/20 text-danger text-xs font-bold rounded">CANCELLED</span>
                                @elseif($order->status == \App\Models\Order\Order::STATUS_RETURNED)
                                <span class="px-2 py-1 bg-danger/20 text-danger text-xs font-bold rounded">RETURNED</span>
                                @else
                                <span class="px-2 py-1 bg-surface-gray text-on-surface-variant text-xs font-bold rounded">UNKNOWN</span>
                                @endif
                            </td>
                            <td class="px-gutter py-4">
                                @if($order->resi)
                                    <div class="space-y-1">
                                        <div class="flex items-center gap-1.5">
                                            <span class="font-mono text-xs font-bold text-primary bg-primary/5 px-2 py-0.5 rounded border border-primary/20 select-all">
                                                {{ $order->resi }}
                                            </span>
                                        </div>
                                        <div class="flex items-center gap-1 text-[11px] text-on-surface-variant flex-wrap">
                                            <span class="font-semibold">{{ $order->courier_name ?? 'Kurir' }}</span>
                                            <span>&bull;</span>
                                            <span class="inline-flex items-center gap-0.5 px-1.5 py-0.2 rounded text-[10px] font-bold border {{ $order->delivery_status_badge_class }}">
                                                {{ $order->delivery_status_label }}
                                            </span>
                                        </div>
                                    </div>
                                @else
                                    <span class="text-xs text-on-surface-variant italic">Belum ada resi</span>
                                @endif
                            </td>
                            <td class="px-gutter py-4 text-center">
                                <a href="{{ route('orders.show', $order->id) }}" class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg border border-outline-variant text-xs font-semibold text-primary hover:bg-primary/5 transition-colors">
                                    <span class="material-symbols-outlined text-[14px]">visibility</span>
                                    <span>Detail</span>
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="px-gutter py-8 text-center text-on-surface-variant">Belum ada riwayat pesanan.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
