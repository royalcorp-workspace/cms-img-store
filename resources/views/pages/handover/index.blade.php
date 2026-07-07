@extends('layouts.app')

@section('title', 'Handovers')

@section('content')
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="font-headline-lg text-headline-lg text-on-surface">Handovers</h1>
            <nav class="flex items-center gap-2 text-body-md text-on-surface-variant mt-1">
                <a href="{{ route('dashboard') }}" class="text-primary hover:underline">Dashboard</a>
                <span class="material-symbols-outlined text-[16px]">chevron_right</span>
                <span>Pick & Pack</span>
                <span class="material-symbols-outlined text-[16px]">chevron_right</span>
                <span>Handovers</span>
            </nav>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-outline-variant/30 overflow-hidden mb-8">
        <div class="p-4 border-b border-outline-variant flex flex-col sm:flex-row gap-3 justify-between">
            <form method="GET" class="flex items-center gap-2">
                <div class="relative">
                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-on-surface-variant text-[18px] material-symbols-outlined">search</span>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search handovers..." class="pl-9 pr-4 py-2 border border-outline-variant rounded-lg text-body-md focus:ring-2 focus:ring-primary/20 focus:outline-none">
                </div>
                <button type="submit" class="px-4 py-2 bg-primary text-white rounded-lg font-label-md hover:opacity-90 transition-all shadow-sm">Filter</button>
                <a href="{{ route('handover.index') }}" class="px-4 py-2 border border-outline-variant text-on-surface rounded-lg font-label-md hover:bg-surface-container transition-colors">Clear</a>
            </form>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-surface-gray">
                        <th class="px-6 py-4 font-label-md text-label-md text-on-surface-variant uppercase tracking-wider">Handover ID</th>
                        <th class="px-6 py-4 font-label-md text-label-md text-on-surface-variant uppercase tracking-wider">Order</th>
                        <th class="px-6 py-4 font-label-md text-label-md text-on-surface-variant uppercase tracking-wider">Warehouse</th>
                        <th class="px-6 py-4 font-label-md text-label-md text-on-surface-variant uppercase tracking-wider">Courier</th>
                        <th class="px-6 py-4 font-label-md text-label-md text-on-surface-variant uppercase tracking-wider text-center">Status</th>
                        <th class="px-6 py-4 font-label-md text-label-md text-on-surface-variant uppercase tracking-wider text-center">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-outline-variant/20">
                    @forelse($handovers as $handover)
                        <tr class="hover:bg-surface-container/30 transition-colors group">
                            <td class="px-6 py-4">
                                <span class="font-headline-md text-headline-md text-primary font-semibold">#{{ substr($handover->id, 0, 8) }}</span>
                            </td>
                            <td class="px-6 py-4 font-body-md text-body-md text-on-surface">{{ $handover->order->order_number ?? substr($handover->order_id, 0, 8) }}</td>
                            <td class="px-6 py-4 font-body-md text-body-md text-on-surface-variant">{{ $handover->warehouse->name ?? '-' }}</td>
                            <td class="px-6 py-4 font-body-md text-body-md text-on-surface-variant">{{ $handover->courier->name ?? '-' }}</td>
                            <td class="px-6 py-4 text-center">
                                @php
                                    $statusClass = match($handover->status) {
                                        'pending' => 'bg-gray-100 text-gray-600',
                                        'in_transit' => 'bg-indigo-100 text-indigo-700',
                                        'completed' => 'bg-success/10 text-success',
                                        'failed' => 'bg-danger/10 text-danger',
                                        default => 'bg-gray-100 text-gray-600',
                                    };
                                @endphp
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-label-sm font-label-sm {{ $statusClass }}">
                                    <span class="w-1.5 h-1.5 rounded-full bg-current"></span> {{ ucfirst(str_replace('_', ' ', $handover->status)) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-on-surface-variant">
                                <div class="flex items-center gap-2">
                                    <a href="{{ route('handover.show', $handover->id) }}" class="p-1.5 hover:text-primary transition-colors" title="View"><span class="material-symbols-outlined text-[20px]">visibility</span></a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-8 text-center text-on-surface-variant">No handovers found</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($handovers->hasPages())
            <div class="px-6 py-4 border-t border-outline-variant bg-surface-container-low/30 flex justify-between items-center">
                <p class="font-body-md text-body-md text-on-surface-variant">Showing {{ $handovers->firstItem() }} to {{ $handovers->lastItem() }} of {{ $handovers->total() }} handovers</p>
                <div class="flex gap-2">{{ $handovers->links() }}</div>
            </div>
        @endif
    </div>
@endsection
