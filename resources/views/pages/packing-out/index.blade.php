@extends('layouts.app')

@section('title', 'Packing Out')

@section('content')
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="font-headline-lg text-headline-lg text-on-surface">Packing Out</h1>
            <nav class="flex items-center gap-2 text-body-md text-on-surface-variant mt-1">
                <a href="{{ route('dashboard') }}" class="text-primary hover:underline">Dashboard</a>
                <span class="material-symbols-outlined text-[16px]">chevron_right</span>
                <span>Pick & Pack</span>
                <span class="material-symbols-outlined text-[16px]">chevron_right</span>
                <span>Packing Out</span>
            </nav>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-outline-variant/30 overflow-hidden mb-8">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-surface-gray">
                        <th class="px-6 py-4 font-label-md text-label-md text-on-surface-variant uppercase tracking-wider">Out ID</th>
                        <th class="px-6 py-4 font-label-md text-label-md text-on-surface-variant uppercase tracking-wider">Order ID</th>
                        <th class="px-6 py-4 font-label-md text-label-md text-on-surface-variant uppercase tracking-wider">Customer</th>
                        <th class="px-6 py-4 font-label-md text-label-md text-on-surface-variant uppercase tracking-wider">Warehouse</th>
                        <th class="px-6 py-4 font-label-md text-label-md text-on-surface-variant uppercase tracking-wider text-center">Status</th>
                        <th class="px-6 py-4 font-label-md text-label-md text-on-surface-variant uppercase tracking-wider text-center">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-outline-variant/20">
                    @forelse($packingOuts as $out)
                        <tr class="hover:bg-surface-container/30 transition-colors group">
                            <td class="px-6 py-4">
                                <span class="font-headline-md text-headline-md text-primary font-semibold">#{{ substr($out->id, 0, 8) }}</span>
                            </td>
                            <td class="px-6 py-4 font-body-md text-body-md text-on-surface">{{ substr($out->packingSlip->order->id ?? '-', 0, 8) }}</td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-full bg-primary flex items-center justify-center text-white text-label-sm font-bold">
                                        {{ strtoupper(substr($out->packingSlip->order->customer->name ?? 'N/A', 0, 2)) }}
                                    </div>
                                    <span class="font-body-md text-body-md text-on-surface">{{ $out->packingSlip->order->customer->name ?? 'N/A' }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 font-body-md text-body-md text-on-surface-variant">{{ $out->warehouse->name ?? '-' }}</td>
                            <td class="px-6 py-4 text-center">
                                @php
                                    $statusMap = ['draft' => 'gray', 'ready' => 'indigo', 'out' => 'success', 'cancelled' => 'danger'];
                                    $statusClass = 'bg-' . ($statusMap[$out->status] ?? 'gray') . '-100 text-' . ($statusMap[$out->status] ?? 'gray') . '-700';
                                @endphp
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-label-sm font-label-sm {{ $statusClass }}">
                                    <span class="w-1.5 h-1.5 rounded-full bg-current"></span> {{ ucfirst($out->status) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-on-surface-variant">
                                <div class="flex items-center gap-2">
                                    <a href="{{ route('packing-out.show', $out->id) }}" class="p-1.5 hover:text-primary transition-colors" title="View"><span class="material-symbols-outlined text-[20px]">visibility</span></a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-8 text-center text-on-surface-variant">No packing outs found</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($packingOuts->hasPages())
            <div class="px-6 py-4 border-t border-outline-variant bg-surface-container-low/30 flex justify-between items-center">
                <p class="font-body-md text-body-md text-on-surface-variant">Showing {{ $packingOuts->firstItem() }} to {{ $packingOuts->lastItem() }} of {{ $packingOuts->total() }} packing outs</p>
                <div class="flex gap-2">{{ $packingOuts->links() }}</div>
            </div>
        @endif
    </div>
@endsection