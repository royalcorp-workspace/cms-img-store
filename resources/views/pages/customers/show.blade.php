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
                <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <p class="text-label-sm text-on-surface-variant uppercase tracking-wider mb-1">Phone</p>
                        <p class="font-body-md text-body-md text-on-surface">{{ $customer->phone ?? '-' }}</p>
                    </div>
                    <div>
                        <p class="text-label-sm text-on-surface-variant uppercase tracking-wider mb-1">User Account</p>
                        <p class="font-body-md text-body-md text-on-surface">{{ $customer->user->name ?? 'Not linked' }}</p>
                    </div>
                    <div>
                        <p class="text-label-sm text-on-surface-variant uppercase tracking-wider mb-1">Total Orders</p>
                        <p class="font-body-md text-body-md text-on-surface">{{ $customer->orders->count() }}</p>
                    </div>
                    <div>
                        <p class="text-label-sm text-on-surface-variant uppercase tracking-wider mb-1">Created At</p>
                        <p class="font-body-md text-body-md text-on-surface">{{ $customer->created_at?->format('d M Y H:i') ?? '-' }}</p>
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
@endsection
