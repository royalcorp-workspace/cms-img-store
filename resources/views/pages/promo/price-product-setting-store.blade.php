@extends('layouts.app')

@section('title', 'Store Pricing Settings')

@section('content')
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
        <div>
            <h1 class="font-headline-lg text-headline-lg text-on-surface">Store Pricing Settings</h1>
            <p class="text-body-md text-on-surface-variant mt-1">Manage pricing per store with rowset adjustments.</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('price-product-setting-store.create') }}" class="flex items-center gap-2 px-5 py-2 bg-primary text-white rounded-lg font-label-md hover:opacity-90 transition-all shadow-sm">
                <span class="material-symbols-outlined text-[18px]">add</span>
                Create Store Pricing
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 mb-6">
        <div class="bg-white rounded-xl shadow-sm border border-outline-variant/30 p-5">
            <p class="text-label-sm text-on-surface-variant mb-1">Total Entries</p>
            <h3 class="font-metric-display text-metric-display text-on-surface">{{ number_format($stats['total']) }}</h3>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-outline-variant/30 p-5">
            <p class="text-label-sm text-on-surface-variant mb-1">Product-based</p>
            <h3 class="font-metric-display text-metric-display text-tertiary">{{ number_format($stats['products']) }}</h3>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-outline-variant/30 p-5">
            <p class="text-label-sm text-on-surface-variant mb-1">Variant-based</p>
            <h3 class="font-metric-display text-metric-display text-secondary">{{ number_format($stats['variants']) }}</h3>
        </div>
    </div>

    <form method="GET" class="flex flex-col sm:flex-row gap-3 justify-between mb-6">
        <div class="relative flex-1 max-w-md">
            <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-on-surface-variant text-[18px]">search</span>
            <input name="search" value="{{ request('search') }}" type="text" placeholder="Search by store, product, or variant..." class="w-full pl-9 pr-4 py-2 border border-outline-variant rounded-lg text-body-md focus:ring-2 focus:ring-primary/20 focus:outline-none">
        </div>
        <div class="flex items-center gap-2">
            <select name="store_id" class="px-3 py-2 border border-outline-variant rounded-lg text-body-md focus:ring-2 focus:ring-primary/20 focus:outline-none bg-white" onchange="this.form.submit()">
                <option value="">All Stores</option>
                @foreach($stores as $s)
                    <option value="{{ $s->id }}" {{ request('store_id') == $s->id ? 'selected' : '' }}>{{ $s->name }} ({{ $s->code }})</option>
                @endforeach
            </select>
            @if(request('search') || request('store_id'))
            <a href="{{ route('price-product-setting-store.index') }}" class="px-3 py-2 border border-outline-variant rounded-lg text-body-md text-secondary hover:bg-surface-container transition-colors">Clear</a>
            @endif
        </div>
    </form>

    <div class="bg-white rounded-xl shadow-sm border border-outline-variant/30 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-surface-gray">
                        <th class="px-gutter py-4 font-label-md text-label-md text-on-surface-variant uppercase tracking-wider">Store</th>
                        <th class="px-gutter py-4 font-label-md text-label-md text-on-surface-variant uppercase tracking-wider">Target</th>
                        <th class="px-gutter py-4 font-label-md text-label-md text-on-surface-variant uppercase tracking-wider">Adjustments</th>
                        <th class="px-gutter py-4 font-label-md text-label-md text-on-surface-variant uppercase tracking-wider">Base Price</th>
                        <th class="px-gutter py-4 font-label-md text-label-md text-on-surface-variant uppercase tracking-wider">Final Price</th>
                        <th class="px-gutter py-4 font-label-md text-label-md text-on-surface-variant uppercase tracking-wider text-center">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-outline-variant/20">
                    @forelse($pricings as $pricing)
                    @php
                        $target = $pricing->variant ?? $pricing->product;
                        $targetName = $target?->variant_name ?? $target?->name ?? '-';
                        $targetType = $pricing->variant ? 'Variant' : 'Product';
                        $adjCount = is_array($pricing->adjustments) ? count($pricing->adjustments) : 0;
                    @endphp
                    <tr class="hover:bg-surface-container/30 transition-colors">
                        <td class="px-gutter py-4 font-body-md text-body-md text-on-surface font-semibold">{{ $pricing->store->name ?? '-' }}</td>
                        <td class="px-gutter py-4">
                            <div>
                                <span class="font-body-md text-body-md text-on-surface">{{ $targetName }}</span>
                                <p class="text-label-xs text-on-surface-variant">{{ $targetType }} ID: {{ $pricing->product_id ?? $pricing->variant_id }}</p>
                            </div>
                        </td>
                        <td class="px-gutter py-4 font-body-md text-body-md text-on-surface-variant">{{ $adjCount }} rows</td>
                        <td class="px-gutter py-4 font-body-md text-body-md text-on-surface-variant">Rp{{ number_format($pricing->base_price, 2, ',', '.') }}</td>
                        <td class="px-gutter py-4 font-body-md text-body-md text-on-surface font-medium">Rp{{ number_format($pricing->final_price, 2, ',', '.') }}</td>
                        <td class="px-gutter py-4">
                            <div class="flex gap-2">
                                <a href="{{ route('price-product-setting-store.edit', $pricing->id) }}" class="text-on-surface-variant hover:text-primary" title="Edit"><span class="material-symbols-outlined text-[18px]">edit</span></a>
                                <form action="{{ route('price-product-setting-store.destroy', $pricing->id) }}" method="POST" class="inline" onsubmit="return confirm('Delete this store pricing?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-on-surface-variant hover:text-danger" title="Delete"><span class="material-symbols-outlined text-[18px]">delete</span></button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-gutter py-8 text-center text-on-surface-variant">No store pricing entries found</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-gutter py-4 border-t border-outline-variant bg-surface-container-low/30 flex justify-between items-center">
            <p class="font-body-md text-body-md text-on-surface-variant">Showing {{ $pricings->firstItem() ?? 0 }}-{{ $pricings->lastItem() ?? 0 }} of {{ number_format($pricings->total()) }} entries</p>
            <div class="flex gap-2">
                {{ $pricings->links() }}
            </div>
        </div>
    </div>
@endsection
