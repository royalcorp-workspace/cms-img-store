@extends('layouts.app')

@section('title', 'Picking List Detail')

@section('content')
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="font-headline-lg text-headline-lg text-on-surface">Picking List #{{ substr($picklist->id, 0, 8) }}</h1>
            <nav class="flex items-center gap-2 text-body-md text-on-surface-variant mt-1">
                <a href="{{ route('dashboard') }}" class="text-primary hover:underline">Dashboard</a>
                <span class="material-symbols-outlined text-[16px]">chevron_right</span>
                <span>Pick & Pack</span>
                <span class="material-symbols-outlined text-[16px]">chevron_right</span>
                <a href="{{ route('picking-list.index') }}" class="text-primary hover:underline">Picking List</a>
                <span class="material-symbols-outlined text-[16px]">chevron_right</span>
                <span>Detail</span>
            </nav>
        </div>
        <a href="{{ route('picking-list.index') }}" class="flex items-center gap-2 px-4 py-2 bg-surface-container text-on-surface rounded-lg font-label-md text-label-md hover:opacity-90 transition-all">
            <span class="material-symbols-outlined text-[18px]">arrow_back</span> Back
        </a>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-outline-variant/30 p-6 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
            <div>
                <p class="text-label-sm text-on-surface-variant mb-1">Order</p>
                <p class="font-headline-md text-headline-md text-on-surface">#{{ substr($picklist->order->id, 0, 8) }}</p>
            </div>
            <div>
                <p class="text-label-sm text-on-surface-variant mb-1">Customer</p>
                <p class="font-body-md text-body-md text-on-surface">{{ $picklist->order->customer->name ?? 'N/A' }}</p>
            </div>
            <div>
                <p class="text-label-sm text-on-surface-variant mb-1">Warehouse</p>
                <p class="font-body-md text-body-md text-on-surface">{{ $picklist->warehouse->name ?? '-' }}</p>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-outline-variant/30 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-surface-gray">
                        <th class="px-6 py-4 font-label-md text-label-md text-on-surface-variant uppercase tracking-wider">Product</th>
                        <th class="px-6 py-4 font-label-md text-label-md text-on-surface-variant uppercase tracking-wider">Qty Ordered</th>
                        <th class="px-6 py-4 font-label-md text-label-md text-on-surface-variant uppercase tracking-wider">Qty Picked</th>
                        <th class="px-6 py-4 font-label-md text-label-md text-on-surface-variant uppercase tracking-wider">Location</th>
                        <th class="px-6 py-4 font-label-md text-label-md text-on-surface-variant uppercase tracking-wider text-center">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-outline-variant/20">
                    @foreach($picklist->items as $item)
                        <tr class="hover:bg-surface-container/30 transition-colors">
                            <td class="px-6 py-4 font-body-md text-body-md text-on-surface">{{ $item->product->name ?? '-' }}</td>
                            <td class="px-6 py-4 font-body-md text-body-md text-on-surface-variant">{{ $item->quantity_ordered }}</td>
                            <td class="px-6 py-4 font-body-md text-body-md text-on-surface-variant">{{ $item->quantity_picked }}</td>
                            <td class="px-6 py-4 font-body-md text-body-md text-on-surface-variant">{{ $item->location->name ?? '-' }}</td>
                            <td class="px-6 py-4 text-center">
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-label-sm">
                                    {{ ucfirst($item->status) }}
                                </span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="px-6 py-4 border-t border-outline-variant bg-surface-container-low/30">
            <a href="{{ route('packing-slip.create', $picklist->order->id) }}" class="inline-flex items-center gap-2 px-4 py-2 bg-primary text-white rounded-lg font-label-md text-label-md hover:opacity-90 transition-all">
                <span class="material-symbols-outlined text-[18px]">add</span> Create Packing Slip
            </a>
        </div>
    </div>
@endsection