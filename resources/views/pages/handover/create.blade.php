@extends('layouts.app')

@section('title', 'Create Handover')

@section('content')
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="font-headline-lg text-headline-lg text-on-surface">Create Handover</h1>
            <nav class="flex items-center gap-2 text-body-md text-on-surface-variant mt-1">
                <a href="{{ route('dashboard') }}" class="text-primary hover:underline">Dashboard</a>
                <span class="material-symbols-outlined text-[16px]">chevron_right</span>
                <span>Pick & Pack</span>
                <span class="material-symbols-outlined text-[16px]">chevron_right</span>
                <a href="{{ route('handover.index') }}" class="text-primary hover:underline">Handovers</a>
                <span class="material-symbols-outlined text-[16px]">chevron_right</span>
                <span>Create</span>
            </nav>
        </div>
        <a href="{{ route('packing-out.show', $packingOut->id) }}" class="flex items-center gap-2 px-4 py-2 bg-surface-container text-on-surface rounded-lg font-label-md text-label-md hover:opacity-90 transition-all">
            <span class="material-symbols-outlined text-[18px]">arrow_back</span> Back
        </a>
    </div>

    <form method="POST" action="{{ route('handover.store') }}">
        @csrf
        <input type="hidden" name="packing_out_id" value="{{ $packingOut->id }}">

        <div class="bg-white rounded-xl shadow-sm border border-outline-variant/30 p-6 mb-6">
            <h2 class="font-headline-md text-headline-md text-on-surface mb-4">Handover Details</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="space-y-1.5">
                    <label class="block text-label-sm font-medium text-on-surface-variant">Courier</label>
                    <select name="courier_id" class="w-full px-3 py-2 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary/20 focus:outline-none bg-white">
                        <option value="">Select Courier</option>
                        @foreach($couriers as $courier)
                            <option value="{{ $courier->id }}" {{ old('courier_id') == $courier->id ? 'selected' : '' }}>
                                {{ $courier->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="space-y-1.5">
                    <label class="block text-label-sm font-medium text-on-surface-variant">Tracking Number</label>
                    <input type="text" name="tracking_number" value="{{ old('tracking_number') }}" class="w-full px-3 py-2 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary/20 focus:outline-none" placeholder="Enter tracking number">
                </div>
                <div class="space-y-1.5">
                    <label class="block text-label-sm font-medium text-on-surface-variant">Driver Name</label>
                    <input type="text" name="driver_name" value="{{ old('driver_name') }}" class="w-full px-3 py-2 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary/20 focus:outline-none" placeholder="Enter driver name">
                </div>
                <div class="space-y-1.5">
                    <label class="block text-label-sm font-medium text-on-surface-variant">Driver Phone</label>
                    <input type="text" name="driver_phone" value="{{ old('driver_phone') }}" class="w-full px-3 py-2 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary/20 focus:outline-none" placeholder="Enter driver phone">
                </div>
                <div class="md:col-span-2 space-y-1.5">
                    <label class="block text-label-sm font-medium text-on-surface-variant">Notes</label>
                    <textarea name="notes" rows="3" class="w-full px-3 py-2 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary/20 focus:outline-none" placeholder="Additional notes">{{ old('notes') }}</textarea>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-outline-variant/30 overflow-hidden mb-6">
            <div class="px-6 py-4 border-b border-outline-variant">
                <h2 class="font-headline-md text-headline-md text-on-surface">Order Items</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-surface-gray">
                            <th class="px-6 py-4 font-label-md text-label-md text-on-surface-variant uppercase tracking-wider">Product</th>
                            <th class="px-6 py-4 font-label-md text-label-md text-on-surface-variant uppercase tracking-wider">Variant</th>
                            <th class="px-6 py-4 font-label-md text-label-md text-on-surface-variant uppercase tracking-wider">Qty Ordered</th>
                            <th class="px-6 py-4 font-label-md text-label-md text-on-surface-variant uppercase tracking-wider">Qty Packed</th>
                            <th class="px-6 py-4 font-label-md text-label-md text-on-surface-variant uppercase tracking-wider">Location</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-outline-variant/20">
                        @foreach($packingOut->packingSlip->items ?? [] as $item)
                            <tr class="hover:bg-surface-container/30 transition-colors">
                                <td class="px-6 py-4 font-body-md text-body-md text-on-surface">{{ $item->product->name ?? '-' }}</td>
                                <td class="px-6 py-4 font-body-md text-body-md text-on-surface-variant">{{ $item->variant->variant_name ?? '-' }}</td>
                                <td class="px-6 py-4 font-body-md text-body-md text-on-surface-variant">{{ $item->quantity_ordered }}</td>
                                <td class="px-6 py-4 font-body-md text-body-md text-on-surface-variant">{{ $item->quantity_packed }}</td>
                                <td class="px-6 py-4 font-body-md text-body-md text-on-surface-variant">{{ $item->location->name ?? '-' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="flex justify-end gap-3">
            <a href="{{ route('packing-out.show', $packingOut->id) }}" class="px-5 py-2 border border-outline-variant text-on-surface-variant rounded-lg font-label-md hover:bg-surface-container transition-colors">Cancel</a>
            <button type="submit" class="px-5 py-2 bg-primary text-white rounded-lg font-label-md hover:opacity-90 transition-all shadow-sm">Create Handover</button>
        </div>
    </form>
@endsection
