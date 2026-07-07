@extends('layouts.app')

@section('title', 'Price Product Settings')

@section('content')
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
        <div>
            <h1 class="font-headline-lg text-headline-lg text-on-surface">Price Product Settings</h1>
            <p class="text-body-md text-on-surface-variant mt-1">Manage bulk discounts and special pricing rules for your products.</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('price-settings.create') }}" class="flex items-center gap-2 px-5 py-2 bg-primary text-white rounded-lg font-label-md hover:opacity-90 transition-all shadow-sm">
                <span class="material-symbols-outlined text-[18px]">add</span>
                Create Price Setting
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-xl shadow-sm border border-outline-variant/30 p-5">
            <p class="text-label-sm text-on-surface-variant mb-1">Total Rules</p>
            <h3 class="font-metric-display text-metric-display text-on-surface">{{ number_format($stats['total']) }}</h3>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-outline-variant/30 p-5">
            <p class="text-label-sm text-on-surface-variant mb-1">Active</p>
            <h3 class="font-metric-display text-metric-display text-success">{{ number_format($stats['active']) }}</h3>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-outline-variant/30 p-5">
            <p class="text-label-sm text-on-surface-variant mb-1">Featured</p>
            <h3 class="font-metric-display text-metric-display text-warning">{{ number_format($stats['featured']) }}</h3>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-outline-variant/30 p-5">
            <p class="text-label-sm text-on-surface-variant mb-1">Volume Discount</p>
            <h3 class="font-metric-display text-metric-display text-tertiary">{{ number_format($stats['volume']) }}</h3>
        </div>
    </div>

    <form method="GET" class="flex flex-col sm:flex-row gap-3 justify-between mb-6">
        <div class="relative flex-1 max-w-md">
            <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-on-surface-variant text-[18px]">search</span>
            <input name="search" value="{{ request('search') }}" type="text" placeholder="Search price settings..." class="w-full pl-9 pr-4 py-2 border border-outline-variant rounded-lg text-body-md focus:ring-2 focus:ring-primary/20 focus:outline-none">
        </div>
        <div class="flex items-center gap-2">
            <select name="type" class="px-3 py-2 border border-outline-variant rounded-lg text-body-md focus:ring-2 focus:ring-primary/20 focus:outline-none bg-white" onchange="this.form.submit()">
                <option value="">All Types</option>
                <option value="1" {{ request('type') == '1' ? 'selected' : '' }}>Direct Discount</option>
                <option value="2" {{ request('type') == '2' ? 'selected' : '' }}>Volume Discount</option>
            </select>
            <select name="status" class="px-3 py-2 border border-outline-variant rounded-lg text-body-md focus:ring-2 focus:ring-primary/20 focus:outline-none bg-white" onchange="this.form.submit()">
                <option value="">All Status</option>
                <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                <option value="expired" {{ request('status') === 'expired' ? 'selected' : '' }}>Expired</option>
            </select>
            @if(request('search') || request('type') || request('status'))
            <a href="{{ route('price-settings.index') }}" class="px-3 py-2 border border-outline-variant rounded-lg text-body-md text-secondary hover:bg-surface-container transition-colors">Clear</a>
            @endif
        </div>
    </form>

    <div class="bg-white rounded-xl shadow-sm border border-outline-variant/30 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-surface-gray">
                        <th class="px-gutter py-4 font-label-md text-label-md text-on-surface-variant uppercase tracking-wider">Name</th>
                        <th class="px-gutter py-4 font-label-md text-label-md text-on-surface-variant uppercase tracking-wider">Type</th>
                        <th class="px-gutter py-4 font-label-md text-label-md text-on-surface-variant uppercase tracking-wider">Discount</th>
                        <th class="px-gutter py-4 font-label-md text-label-md text-on-surface-variant uppercase tracking-wider">Scope</th>
                        <th class="px-gutter py-4 font-label-md text-label-md text-on-surface-variant uppercase tracking-wider">Products</th>
                        <th class="px-gutter py-4 font-label-md text-label-md text-on-surface-variant uppercase tracking-wider text-center">Status</th>
                        <th class="px-gutter py-4 font-label-md text-label-md text-on-surface-variant uppercase tracking-wider">Period</th>
                        <th class="px-gutter py-4 font-label-md text-label-md text-on-surface-variant uppercase tracking-wider text-center">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-outline-variant/20">
                    @forelse($settings as $setting)
                    @php
                        $isExpired = $setting->end_date && $setting->end_date->isPast();
                        $isActive = !$isExpired && $setting->is_active && !$setting->deleted;
                        $productCount = $setting->products->count();
                        $typeLabel = $setting->typeLabel();
                        $discountDisplay = '';

                        if ((int)$setting->discount_type === 1) {
                            $discountDisplay = $setting->discount_value . '% OFF';
                        } elseif ((int)$setting->discount_type === 2) {
                            $discountDisplay = 'Rp' . number_format($setting->discount_value, 0, ',', '.') . ' OFF';
                        } elseif ((int)$setting->discount_type === 3) {
                            $discountDisplay = 'Rp' . number_format($setting->discount_value, 0, ',', '.') . ' Shipping';
                        }

                        $scopeLabel = match ((int)$setting->scope) {
                            2 => 'Specific products',
                            3 => 'Category',
                            default => 'All products',
                        };

                        $storeScopeLabel = $setting->scopeStoreTypeLabel();
                        if ($setting->scope_store_type > 0 && $setting->scope_store_id) {
                            $scopeLabel .= ' · ' . $storeScopeLabel;
                        }

                        $period = 'Ongoing';
                        if ($setting->start_date && $setting->end_date) {
                            $period = $setting->start_date->format('M j') . ' – ' . $setting->end_date->format('M j, Y');
                        } elseif ($setting->end_date) {
                            $period = 'Until ' . $setting->end_date->format('M j, Y');
                        } elseif ($setting->start_date) {
                            $period = 'From ' . $setting->start_date->format('M j, Y');
                        }

                        $statusBadge = match(true) {
                            $isActive => 'bg-success/10 text-success',
                            $isExpired => 'bg-warning/10 text-warning',
                            default => 'bg-danger/10 text-danger',
                        };
                        $statusLabel = match(true) {
                            $isActive => 'Active',
                            $isExpired => 'Expired',
                            default => 'Inactive',
                        };
                    @endphp
                    <tr class="hover:bg-surface-container/30 transition-colors">
                        <td class="px-gutter py-4">
                            <div>
                                <span class="font-body-md text-body-md text-on-surface font-semibold">{{ $setting->title }}</span>
                                <p class="text-label-sm text-on-surface-variant">{{ $setting->code ?? '-' }}</p>
                            </div>
                        </td>
                        <td class="px-gutter py-4 font-body-md text-body-md text-on-surface-variant">{{ $typeLabel }}</td>
                        <td class="px-gutter py-4 font-body-md text-body-md text-on-surface font-medium">
                            {!! $setting->isVolumeDiscount() ? 'Volume tiers' : $discountDisplay !!}
                        </td>
                        <td class="px-gutter py-4 font-body-md text-body-md text-on-surface-variant">{{ $scopeLabel }}</td>
                        <td class="px-gutter py-4 font-body-md text-body-md text-on-surface-variant">
                            {{ $productCount > 0 ? $productCount : 'All' }}
                        </td>
                        <td class="px-gutter py-4 text-center">
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-label-sm font-label-sm {{ $statusBadge }}">
                                <span class="w-1.5 h-1.5 rounded-full bg-current"></span> {{ $statusLabel }}
                            </span>
                        </td>
                        <td class="px-gutter py-4 font-body-md text-body-md text-on-surface-variant">{{ $period }}</td>
                        <td class="px-gutter py-4">
                            <div class="flex gap-2">
                                <a href="{{ route('price-settings.edit', $setting->id) }}" class="text-on-surface-variant hover:text-primary" title="Edit"><span class="material-symbols-outlined text-[18px]">edit</span></a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-gutter py-8 text-center text-on-surface-variant">No price settings found</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-gutter py-4 border-t border-outline-variant bg-surface-container-low/30 flex justify-between items-center">
            <p class="font-body-md text-body-md text-on-surface-variant">Showing {{ $settings->firstItem() ?? 0 }}-{{ $settings->lastItem() ?? 0 }} of {{ number_format($settings->total()) }} rules</p>
            <div class="flex gap-2">
                {{ $settings->links() }}
            </div>
        </div>
    </div>
@endsection
