@extends('layouts.app')

@section('title', 'Voucher Management')

@section('content')
<div class="flex flex-col md:flex-row md:items-end justify-between gap-4 mb-8">
    <div>
        <h3 class="font-headline-xl text-headline-xl text-on-surface">Voucher Management</h3>
        <p class="text-body-md text-secondary mt-1">Manage, create, and track your promotional campaign discounts.</p>
    </div>
    <a href="{{ route('vouchers.create') }}" class="bg-primary hover:bg-primary-container text-white px-6 py-3 rounded-xl font-headline-md text-headline-md flex items-center gap-2 transition-all shadow-md active:scale-95">
        <span class="material-symbols-outlined">add_circle</span> Create Voucher
    </a>
</div>

<div class="grid grid-cols-1 md:grid-cols-4 gap-container-gap mb-container-gap">
    <div class="bg-white p-card-padding rounded-xl shadow-sm border border-outline-variant/30 flex flex-col justify-between">
        <div class="flex justify-between items-start">
            <div class="p-3 bg-primary-fixed rounded-lg">
                <span class="material-symbols-outlined text-primary">confirmation_number</span>
            </div>
        </div>
        <div class="mt-4">
            <p class="text-label-md font-label-md text-secondary uppercase tracking-wider">Active Vouchers</p>
            <h4 class="text-metric-display font-metric-display text-on-surface">{{ number_format($stats['active']) }}</h4>
        </div>
    </div>
    <div class="bg-white p-card-padding rounded-xl shadow-sm border border-outline-variant/30 flex flex-col justify-between">
        <div class="flex justify-between items-start">
            <div class="p-3 bg-tertiary-fixed rounded-lg">
                <span class="material-symbols-outlined text-tertiary">shopping_bag</span>
            </div>
        </div>
        <div class="mt-4">
            <p class="text-label-md font-label-md text-secondary uppercase tracking-wider">Total Redemptions</p>
            <h4 class="text-metric-display font-metric-display text-on-surface">{{ number_format($stats['total_redemptions']) }}</h4>
        </div>
    </div>
    <div class="bg-white p-card-padding rounded-xl shadow-sm border border-outline-variant/30 flex flex-col justify-between">
        <div class="flex justify-between items-start">
            <div class="p-3 bg-surface-container-highest rounded-lg">
                <span class="material-symbols-outlined text-primary">timer</span>
            </div>
        </div>
        <div class="mt-4">
            <p class="text-label-md font-label-md text-secondary uppercase tracking-wider">Expiring (7 Days)</p>
            <h4 class="text-metric-display font-metric-display text-on-surface">{{ number_format($stats['expiring_soon']) }}</h4>
        </div>
    </div>
    <div class="bg-white p-card-padding rounded-xl shadow-sm border border-outline-variant/30 flex flex-col justify-between">
        <div class="flex justify-between items-start">
            <div class="p-3 bg-secondary-fixed rounded-lg">
                <span class="material-symbols-outlined text-secondary">payments</span>
            </div>
        </div>
        <div class="mt-4">
            <p class="text-label-md font-label-md text-secondary uppercase tracking-wider">Total Vouchers</p>
            <h4 class="text-metric-display font-metric-display text-on-surface">{{ number_format($stats['total']) }}</h4>
        </div>
    </div>
</div>

<div class="bg-white rounded-xl shadow-sm border border-outline-variant/30 overflow-hidden">
    <div class="px-6 py-4 border-b border-outline-variant/50 flex flex-col sm:flex-row justify-between items-center gap-4">
        <h4 class="font-headline-md text-headline-md text-on-surface">Voucher List</h4>
        <form method="GET" class="flex items-center gap-2 w-full sm:w-auto">
            <input name="search" value="{{ request('search') }}" class="px-4 py-2 border border-outline-variant rounded-lg text-body-md focus:ring-1 focus:ring-primary focus:border-primary" placeholder="Search code or title..."/>
            <select name="status" class="px-4 py-2 border border-outline-variant rounded-lg text-body-md">
                <option value="">All Status</option>
                <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                <option value="expired" {{ request('status') === 'expired' ? 'selected' : '' }}>Expired</option>
            </select>
            <button type="submit" class="px-4 py-2 bg-primary text-white rounded-lg font-label-md hover:opacity-90 transition-all shadow-sm">Filter</button>
            <a href="{{ route('vouchers.index') }}" class="px-4 py-2 border border-outline-variant text-on-surface rounded-lg font-label-md hover:bg-surface-container transition-colors">Clear</a>
        </form>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-left">
            <thead class="bg-surface-gray">
                <tr>
                    <th class="px-6 py-4 text-label-sm font-label-sm text-secondary uppercase tracking-widest">Voucher Code</th>
                    <th class="px-6 py-4 text-label-sm font-label-sm text-secondary uppercase tracking-widest">Discount Details</th>
                    <th class="px-6 py-4 text-label-sm font-label-sm text-secondary uppercase tracking-widest">Scope / Stack</th>
                    <th class="px-6 py-4 text-label-sm font-label-sm text-secondary uppercase tracking-widest">Usage / Limit</th>
                    <th class="px-6 py-4 text-label-sm font-label-sm text-secondary uppercase tracking-widest">Expiry Date</th>
                    <th class="px-6 py-4 text-label-sm font-label-sm text-secondary uppercase tracking-widest text-center">Status</th>
                    <th class="px-6 py-4 text-label-sm font-label-sm text-secondary uppercase tracking-widest text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-outline-variant/20">
                @forelse($vouchers as $voucher)
                @php
                    $isExpired = $voucher->end_date && $voucher->end_date->isPast();
                    $isActive = !$isExpired && $voucher->is_active && !$voucher->deleted;
                    $percent = $voucher->usage_limit ? min(($voucher->used_count / $voucher->usage_limit) * 100, 100) : 0;
                @endphp
                <tr class="hover:bg-surface-container-low transition-colors group {{ !$isActive ? 'opacity-60' : '' }}">
                    <td class="px-6 py-5">
                        <div class="flex flex-col">
                            <span class="font-headline-md text-headline-md text-primary user-select-all">{{ $voucher->code }}</span>
                            <span class="text-label-sm text-secondary">{{ $voucher->title }}</span>
                        </div>
                    </td>
                    <td class="px-6 py-5">
                        <div class="flex items-center gap-2">
                            <span class="px-2 py-1 bg-primary-fixed text-primary font-bold rounded text-label-md">
                                @if((int)$voucher->type === 1)
                                    {{ $voucher->value }}% OFF
                                @elseif((int)$voucher->type === 2)
                                    Rp{{ number_format($voucher->value, 0, ',', '.') }} OFF
                                @elseif((int)$voucher->type === 3)
                                    Rp{{ number_format($voucher->value, 0, ',', '.') }} Shipping
                                @elseif((int)$voucher->type === 4)
                                    {{ $voucher->value }} pcs
                                @endif
                            </span>
                        </div>
                    </td>
                    <td class="px-6 py-5">
                        <div class="flex flex-col gap-1">
                            <span class="text-label-sm text-on-surface">{{ $voucher->scopeLabel() }}</span>
                            @if($voucher->allow_stacking)
                            <span class="inline-flex items-center gap-1 text-label-sm text-success">
                                <span class="material-symbols-outlined text-[14px]">check_circle</span> Stackable
                            </span>
                            @else
                            <span class="inline-flex items-center gap-1 text-label-sm text-secondary">
                                <span class="material-symbols-outlined text-[14px]">block</span> Non-stackable
                            </span>
                            @endif
                        </div>
                    </td>
                    <td class="px-6 py-5">
                        <div class="w-full max-w-[120px]">
                            <div class="flex justify-between text-label-sm text-secondary mb-1">
                                <span>{{ $voucher->used_count ?? 0 }} / {{ $voucher->usage_limit ?? '∞' }}</span>
                                <span>{{ round($percent) }}%</span>
                            </div>
                            <div class="w-full bg-surface-gray rounded-full h-1.5">
                                <div class="bg-primary h-1.5 rounded-full" style="width: {{ $percent }}%"></div>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-5 text-body-md text-on-surface">{{ $voucher->end_date?->format('d M Y') ?? '-' }}</td>
                    <td class="px-6 py-5">
                        <div class="flex justify-center">
                            @if($isActive)
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-success/10 text-success rounded-full text-label-md font-label-md">
                                <span class="w-1.5 h-1.5 rounded-full bg-success"></span> Active
                            </span>
                            @elseif($isExpired)
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-secondary/10 text-secondary rounded-full text-label-md font-label-md">
                                <span class="w-1.5 h-1.5 rounded-full bg-secondary"></span> Expired
                            </span>
                            @else
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-danger/10 text-danger rounded-full text-label-md font-label-md">
                                <span class="w-1.5 h-1.5 rounded-full bg-danger"></span> Inactive
                            </span>
                            @endif
                        </div>
                    </td>
                    <td class="px-6 py-5 text-right">
                        <div class="flex justify-end gap-2">
                            <a href="{{ route('vouchers.edit', $voucher->id) }}" class="p-2 text-secondary hover:text-primary transition-colors" title="Edit">
                                <span class="material-symbols-outlined">edit</span>
                            </a>
                            <form action="{{ route('vouchers.destroy', $voucher->id) }}" method="POST" class="inline" onsubmit="return confirm('Delete voucher {{ $voucher->code }}?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="p-2 text-secondary hover:text-danger transition-colors" title="Delete">
                                    <span class="material-symbols-outlined">delete</span>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-6 py-8 text-center text-on-surface-variant">No vouchers found</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="px-6 py-4 bg-surface-container-low border-t border-outline-variant/30 flex flex-col md:flex-row justify-between items-center gap-4">
        <span class="text-label-md text-secondary">Showing {{ $vouchers->firstItem() ?? 0 }}-{{ $vouchers->lastItem() ?? 0 }} of {{ $vouchers->total() }} results</span>
        <div class="flex items-center gap-1">
            {{ $vouchers->links() }}
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$('#voucherScope').on('change', function() {
    const val = $(this).val();
    $('#productSelect').toggleClass('hidden', val !== '2');
    $('#categorySelect').toggleClass('hidden', val !== '3');
});
</script>
@endpush
