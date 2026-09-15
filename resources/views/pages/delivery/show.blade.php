@extends('layouts.app')

@section('title', 'Delivery Detail')

@section('content')
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
        <div>
            <div class="flex items-center gap-3">
                <h1 class="font-headline-lg text-headline-lg text-on-surface">Delivery #{{ substr($delivery->id, 0, 8) }}</h1>
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
            </div>
            <nav class="flex items-center gap-2 text-label-sm text-on-surface-variant mt-1 font-medium">
                <a href="{{ route('dashboard') }}" class="hover:text-primary transition-colors">eCommerce</a>
                <span class="material-symbols-outlined text-[14px]">chevron_right</span>
                <span>Pick & Pack</span>
                <span class="material-symbols-outlined text-[14px]">chevron_right</span>
                <a href="{{ route('delivery.index') }}" class="hover:text-primary transition-colors">Delivery</a>
                <span class="material-symbols-outlined text-[14px]">chevron_right</span>
                <span class="text-on-surface">#{{ substr($delivery->id, 0, 8) }}</span>
            </nav>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('delivery.index') }}" class="inline-flex items-center gap-1.5 px-4 py-2 border border-outline-variant text-on-surface-variant hover:bg-surface-container rounded-xl text-xs font-semibold transition-colors">
                <span class="material-symbols-outlined text-[16px]">arrow_back</span>
                <span>Kembali</span>
            </a>
            @if($delivery->status !== 'delivered')
                <form action="{{ route('delivery.update-status', $delivery->id) }}" method="POST" class="inline">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="status" value="delivered">
                    <button type="submit" class="btn-save inline-flex items-center gap-2 px-5 py-2 bg-success text-white hover:opacity-90 rounded-xl text-xs font-bold transition-all shadow-sm active:scale-95" onclick="return confirm('Tandai pengiriman ini telah selesai (delivered)?');">
                        <span class="material-symbols-outlined text-[18px]">check_circle</span>
                        <span>Mark Delivered</span>
                    </button>
                </form>
            @endif
        </div>
    </div>

    @include('layouts.partials.pick-pack-submenu')

    <div class="space-y-6">
        <div class="bg-white rounded-2xl shadow-sm border border-outline-variant/30 p-6">
            <h3 class="text-base font-bold text-on-surface mb-4 pb-2 border-b border-outline-variant/30 flex items-center gap-2">
                <span class="material-symbols-outlined text-primary text-[20px]">local_shipping</span>
                Informasi Pengiriman
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <p class="text-label-sm text-on-surface-variant mb-1 font-medium">Order</p>
                    @if($delivery->order)
                        <a href="{{ route('orders.show', $delivery->order->id) }}" class="font-headline-md text-headline-md text-primary font-bold hover:underline font-mono">
                            {{ $delivery->order->order_number ?? '#' . substr($delivery->order->id, 0, 8) }}
                        </a>
                    @else
                        <p class="font-body-md text-body-md text-on-surface-variant">-</p>
                    @endif
                </div>
                <div>
                    <p class="text-label-sm text-on-surface-variant mb-1 font-medium">Customer</p>
                    <p class="font-body-md text-body-md text-on-surface font-semibold">{{ $delivery->order?->customer?->name ?? 'N/A' }}</p>
                </div>
                <div>
                    <p class="text-label-sm text-on-surface-variant mb-1 font-medium">Courier</p>
                    <p class="font-body-md text-body-md text-on-surface">{{ $delivery->courier->name ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-label-sm text-on-surface-variant mb-1 font-medium">Tracking Number</p>
                    <p class="font-body-md text-body-md text-on-surface font-mono font-semibold">{{ $delivery->tracking_number ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-label-sm text-on-surface-variant mb-1 font-medium">Driver</p>
                    <p class="font-body-md text-body-md text-on-surface">{{ $delivery->driver_name ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-label-sm text-on-surface-variant mb-1 font-medium">Status Pengiriman</p>
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold border {{ $statusBadge }}">
                        <span class="w-1.5 h-1.5 rounded-full bg-current"></span> {{ ucfirst(str_replace('_', ' ', $delivery->status)) }}
                    </span>
                </div>
            </div>
        </div>

        @if($delivery->order && $delivery->order->items && $delivery->order->items->isNotEmpty())
            <div class="bg-white rounded-2xl shadow-sm border border-outline-variant/30 p-6">
                <h3 class="text-base font-bold text-on-surface mb-4 pb-2 border-b border-outline-variant/30 flex items-center gap-2">
                    <span class="material-symbols-outlined text-primary text-[20px]">inventory_2</span>
                    Daftar Produk yang Dikirim
                </h3>
                <div class="divide-y divide-outline-variant/20">
                    @foreach($delivery->order->items as $item)
                        <div class="py-3 flex items-center justify-between">
                            <div>
                                <h4 class="font-bold text-on-surface text-body-md">{{ $item->product?->name ?? 'Product' }}</h4>
                                <p class="text-label-sm text-on-surface-variant">Qty: {{ $item->quantity }}</p>
                            </div>
                            <span class="text-body-md font-bold text-on-surface">Rp{{ number_format($item->price ?? 0, 0, ',', '.') }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
@endsection