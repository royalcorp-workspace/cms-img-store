@extends('layouts.app')

@section('title', 'Create Delivery')

@section('content')
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="font-headline-lg text-headline-lg text-on-surface">Create Delivery</h1>
            <nav class="flex items-center gap-2 text-label-sm text-on-surface-variant mt-1 font-medium">
                <a href="{{ route('dashboard') }}" class="hover:text-primary transition-colors">eCommerce</a>
                <span class="material-symbols-outlined text-[14px]">chevron_right</span>
                <span>Pick & Pack</span>
                <span class="material-symbols-outlined text-[14px]">chevron_right</span>
                <a href="{{ route('delivery.index') }}" class="hover:text-primary transition-colors">Delivery</a>
                <span class="material-symbols-outlined text-[14px]">chevron_right</span>
                <span class="text-on-surface">Create</span>
            </nav>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('packing-out.show', $packingOut->id) }}" class="inline-flex items-center gap-1.5 px-4 py-2 border border-outline-variant text-on-surface-variant hover:bg-surface-container rounded-xl text-xs font-semibold transition-colors">
                <span class="material-symbols-outlined text-[16px]">arrow_back</span>
                <span>Batal</span>
            </a>
            <button type="submit" form="deliveryForm" class="btn-save inline-flex items-center gap-2 px-5 py-2 bg-primary text-white hover:opacity-90 rounded-xl text-xs font-bold transition-all shadow-sm active:scale-95">
                <span class="material-symbols-outlined text-[18px]">local_shipping</span>
                <span>Ship / Transit Delivery</span>
            </button>
        </div>
    </div>

    @include('layouts.partials.pick-pack-submenu')

    @if ($errors->any())
        <div class="bg-danger-100 border border-danger-200 text-danger-700 px-4 py-3 rounded-xl mb-6">
            <ul class="list-disc list-inside">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form id="deliveryForm" action="{{ route('delivery.store') }}" method="POST">
        @csrf
        <input type="hidden" name="packing_out_id" value="{{ $packingOut->id }}">

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
            <!-- Delivery Setup Form -->
            <div class="lg:col-span-2 space-y-6">
                <div class="bg-white rounded-2xl shadow-sm border border-outline-variant/30 p-6">
                    <h2 class="font-headline-md text-headline-md text-on-surface mb-4 font-semibold">Delivery Details</h2>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                        <div>
                            <label for="courier_id" class="block font-label-md text-label-md text-on-surface-variant mb-2">Courier / Shipping Service</label>
                            <select name="courier_id" id="courier_id" 
                                class="w-full px-4 py-2.5 border border-outline/30 rounded-lg focus:outline-none focus:border-primary transition-colors text-body-md bg-white select2-enable">
                                <option value="" selected>-- Local Delivery / Self pickup --</option>
                                @foreach($couriers as $courier)
                                    <option value="{{ $courier->id }}">{{ $courier->name }} ({{ $courier->code }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label for="tracking_number" class="block font-label-md text-label-md text-on-surface-variant mb-2">Tracking / AWB Number</label>
                            <input type="text" name="tracking_number" id="tracking_number" placeholder="Enter tracking or AWB code..."
                                class="w-full px-4 py-2.5 border border-outline/30 rounded-lg focus:outline-none focus:border-primary transition-colors text-body-md">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="driver_name" class="block font-label-md text-label-md text-on-surface-variant mb-2">Driver / Courier Name</label>
                            <input type="text" name="driver_name" id="driver_name" placeholder="Driver's full name..."
                                class="w-full px-4 py-2.5 border border-outline/30 rounded-lg focus:outline-none focus:border-primary transition-colors text-body-md">
                        </div>
                        <div>
                            <label for="driver_phone" class="block font-label-md text-label-md text-on-surface-variant mb-2">Driver Phone Number</label>
                            <input type="text" name="driver_phone" id="driver_phone" placeholder="Driver's contact phone..."
                                class="w-full px-4 py-2.5 border border-outline/30 rounded-lg focus:outline-none focus:border-primary transition-colors text-body-md">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Packing Out details and Submit -->
            <div class="space-y-6">
                <div class="bg-white rounded-2xl shadow-sm border border-outline-variant/30 p-6">
                    <h2 class="font-headline-md text-headline-md text-on-surface mb-4 font-semibold">Summary Details</h2>
                    <div class="space-y-4">
                        <div>
                            <p class="text-label-sm text-on-surface-variant mb-1">Packing Out ID</p>
                            <p class="font-body-md text-body-md text-on-surface font-semibold">#{{ substr($packingOut->id, 0, 8) }}</p>
                        </div>
                        <div>
                            <p class="text-label-sm text-on-surface-variant mb-1">Order Ref</p>
                            <p class="font-body-md text-body-md text-on-surface font-mono font-semibold">
                                @if($packingOut->packingSlip?->order)
                                    {{ $packingOut->packingSlip->order->order_number ?? '#' . substr($packingOut->packingSlip->order->id, 0, 8) }}
                                @else
                                    -
                                @endif
                            </p>
                        </div>
                        <div>
                            <p class="text-label-sm text-on-surface-variant mb-1">Customer</p>
                            <p class="font-body-md text-body-md text-on-surface font-semibold">{{ $packingOut->packingSlip?->order?->customer?->name ?? 'N/A' }}</p>
                        </div>
                        <div class="pt-4 border-t border-outline-variant/20 space-y-2">
                            <button type="submit" class="btn-save w-full flex items-center justify-center gap-2 px-4 py-2.5 bg-primary text-white rounded-xl font-label-md text-xs font-bold hover:opacity-90 transition-all shadow-sm active:scale-95">
                                <span class="material-symbols-outlined text-[18px]">local_shipping</span> Ship / Transit Delivery
                            </button>
                            <a href="{{ route('packing-out.show', $packingOut->id) }}" class="w-full flex items-center justify-center gap-2 px-4 py-2.5 border border-outline-variant text-on-surface-variant rounded-xl text-xs font-semibold hover:bg-surface-container transition-colors">
                                Batal
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
@endsection
