@extends('layouts.app')

@section('title', 'Delivery Detail')

@section('content')
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="font-headline-lg text-headline-lg text-on-surface">Delivery #{{ substr($delivery->id, 0, 8) }}</h1>
            <nav class="flex items-center gap-2 text-body-md text-on-surface-variant mt-1">
                <a href="{{ route('dashboard') }}" class="text-primary hover:underline">Dashboard</a>
                <span class="material-symbols-outlined text-[16px]">chevron_right</span>
                <span>Pick & Pack</span>
                <span class="material-symbols-outlined text-[16px]">chevron_right</span>
                <a href="{{ route('delivery.index') }}" class="text-primary hover:underline">Delivery</a>
                <span class="material-symbols-outlined text-[16px]">chevron_right</span>
                <span>Detail</span>
            </nav>
        </div>
        <a href="{{ route('delivery.index') }}" class="flex items-center gap-2 px-4 py-2 bg-surface-container text-on-surface rounded-lg font-label-md text-label-md hover:opacity-90 transition-all">
            <span class="material-symbols-outlined text-[18px]">arrow_back</span> Back
        </a>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-outline-variant/30 p-6 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div>
                <p class="text-label-sm text-on-surface-variant mb-1">Order</p>
                <p class="font-headline-md text-headline-md text-on-surface">#{{ substr($delivery->order->id, 0, 8) }}</p>
            </div>
            <div>
                <p class="text-label-sm text-on-surface-variant mb-1">Customer</p>
                <p class="font-body-md text-body-md text-on-surface">{{ $delivery->order->customer->name ?? 'N/A' }}</p>
            </div>
            <div>
                <p class="text-label-sm text-on-surface-variant mb-1">Courier</p>
                <p class="font-body-md text-body-md text-on-surface">{{ $delivery->courier->name ?? '-' }}</p>
            </div>
            <div>
                <p class="text-label-sm text-on-surface-variant mb-1">Tracking Number</p>
                <p class="font-body-md text-body-md text-on-surface">{{ $delivery->tracking_number ?? '-' }}</p>
            </div>
            <div>
                <p class="text-label-sm text-on-surface-variant mb-1">Driver</p>
                <p class="font-body-md text-body-md text-on-surface">{{ $delivery->driver_name ?? '-' }}</p>
            </div>
            <div>
                <p class="text-label-sm text-on-surface-variant mb-1">Status</p>
                <p class="font-body-md text-body-md text-on-surface">{{ ucfirst(str_replace('_', ' ', $delivery->status)) }}</p>
            </div>
        </div>
    </div>

    @if($delivery->status !== 'delivered')
    <form action="{{ route('delivery.update-status', $delivery->id) }}" method="POST" class="inline">
        @csrf
        @method('PUT')
        <input type="hidden" name="status" value="delivered">
        <button type="submit" class="inline-flex items-center gap-2 px-4 py-2 bg-success text-white rounded-lg font-label-md text-label-md hover:opacity-90 transition-all mb-6">
            <span class="material-symbols-outlined text-[18px]">check_circle</span> Mark Delivered
        </button>
    </form>
    @endif
@endsection