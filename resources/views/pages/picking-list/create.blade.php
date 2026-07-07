@extends('layouts.app')

@section('title', 'Create Picking List')

@section('content')
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="font-headline-lg text-headline-lg text-on-surface">Create Picking List</h1>
            <nav class="flex items-center gap-2 text-body-md text-on-surface-variant mt-1">
                <a href="{{ route('dashboard') }}" class="text-primary hover:underline">Dashboard</a>
                <span class="material-symbols-outlined text-[16px]">chevron_right</span>
                <span>Pick & Pack</span>
                <span class="material-symbols-outlined text-[16px]">chevron_right</span>
                <a href="{{ route('picking-list.index') }}" class="text-primary hover:underline">Picking List</a>
                <span class="material-symbols-outlined text-[16px]">chevron_right</span>
                <span>Create</span>
            </nav>
        </div>
        <a href="{{ route('picking-list.index') }}" class="flex items-center gap-2 px-4 py-2 bg-surface-container text-on-surface rounded-lg font-label-md text-label-md hover:opacity-90 transition-all">
            <span class="material-symbols-outlined text-[18px]">arrow_back</span> Back
        </a>
    </div>

    <form id="pickingListForm" method="POST" action="{{ route('picking-list.store') }}">
        @csrf
        <div class="bg-white rounded-xl shadow-sm border border-outline-variant/30 p-6 mb-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="space-y-1.5">
                    <label class="block text-label-sm font-medium text-on-surface-variant">Order <span class="text-danger">*</span></label>
                    <select name="order_id" id="orderSelect" class="w-full px-3 py-2 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary/20 focus:outline-none bg-white" required>
                        <option value="">Select Order</option>
                        @foreach($orders as $o)
                            <option value="{{ $o->id }}" {{ (old('order_id', $order->id ?? '') == $o->id) ? 'selected' : '' }}>
                                #{{ $o->order_number }} - {{ $o->customer->name ?? 'N/A' }}
                            </option>
                        @endforeach
                    </select>
                    @error('order_id')<p class="text-danger text-label-sm">{{ $message }}</p>@enderror
                </div>
                <div class="space-y-1.5">
                    <label class="block text-label-sm font-medium text-on-surface-variant">Warehouse <span class="text-danger">*</span></label>
                    <select name="warehouse_id" id="warehouseSelect" class="w-full px-3 py-2 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary/20 focus:outline-none bg-white" required>
                        <option value="">Select Warehouse</option>
                        @foreach($warehouses as $warehouse)
                            <option value="{{ $warehouse->id }}" {{ old('warehouse_id') == $warehouse->id ? 'selected' : '' }}>
                                {{ $warehouse->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('warehouse_id')<p class="text-danger text-label-sm">{{ $message }}</p>@enderror
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-outline-variant/30 overflow-hidden">
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
                            <th class="px-6 py-4 font-label-md text-label-md text-on-surface-variant uppercase tracking-wider">Location</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-outline-variant/20" id="itemsTableBody">
                        @if($order && $order->items->count() > 0)
                            @foreach($order->items as $item)
                                <tr class="hover:bg-surface-container/30 transition-colors">
                                    <td class="px-6 py-4 font-body-md text-body-md text-on-surface">{{ $item->product->name ?? '-' }}</td>
                                    <td class="px-6 py-4 font-body-md text-body-md text-on-surface-variant">{{ $item->variant->variant_name ?? '-' }}</td>
                                    <td class="px-6 py-4 font-body-md text-body-md text-on-surface-variant">{{ $item->quantity }}</td>
                                    <td class="px-6 py-4">
                                        <select name="items[{{ $loop->index }}][warehouse_location_id]" class="location-select px-3 py-2 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary/20 focus:outline-none bg-white text-body-md">
                                            <option value="">Select Location</option>
                                            @foreach($warehouseLocations as $location)
                                                <option value="{{ $location->id }}" data-warehouse="{{ $location->warehouse_id }}" {{ old("items.{$loop->index}.warehouse_location_id") == $location->id ? 'selected' : '' }}>
                                                    {{ $location->code }} - {{ $location->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <input type="hidden" name="items[{{ $loop->index }}][order_item_id]" value="{{ $item->id }}">
                                        <input type="hidden" name="items[{{ $loop->index }}][variant_id]" value="{{ $item->product_variant_id ?? '' }}">
                                    </td>
                                </tr>
                            @endforeach
                        @else
                            <tr>
                                <td colspan="4" class="px-6 py-8 text-center text-on-surface-variant">Select an order to view items</td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>
            @error('items')<p class="text-danger text-label-sm px-6 py-2">{{ $message }}</p>@enderror
            <div class="px-6 py-4 border-t border-outline-variant bg-surface-container-low/30 flex justify-end gap-3">
                <a href="{{ route('picking-list.index') }}" class="px-5 py-2 border border-outline-variant text-on-surface-variant rounded-lg font-label-md hover:bg-surface-container transition-colors">Cancel</a>
                <button type="submit" class="px-5 py-2 bg-primary text-white rounded-lg font-label-md hover:opacity-90 transition-all shadow-sm">Create Picking List</button>
            </div>
        </div>
    </form>
@endsection

@push('scripts')
<script>
const allLocations = @json($warehouseLocations);

function filterLocations() {
    const warehouseId = document.getElementById('warehouseSelect').value;
    document.querySelectorAll('.location-select').forEach(select => {
        const currentVal = select.value;
        select.innerHTML = '<option value="">Select Location</option>';
        allLocations.filter(loc => String(loc.warehouse_id) === String(warehouseId)).forEach(loc => {
            const option = document.createElement('option');
            option.value = loc.id;
            option.textContent = loc.code + ' - ' + loc.name;
            if (loc.id === currentVal) option.selected = true;
            select.appendChild(option);
        });
    });
}

document.getElementById('orderSelect')?.addEventListener('change', function() {
    const orderId = this.value;
    if (!orderId) {
        location.reload();
        return;
    }
    location.href = '{{ route('picking-list.create') }}/' + orderId;
});

document.getElementById('warehouseSelect')?.addEventListener('change', filterLocations);
</script>
@endpush
