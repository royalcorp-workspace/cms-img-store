@extends('layouts.app')

@section('title', 'Daily Payment Reconciliation')

@section('content')
    <!-- Dashboard Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
        <div>
            <h1 class="font-headline-lg text-headline-lg text-on-surface">Payment Reconciliation</h1>
            <nav class="flex items-center gap-2 text-body-md text-on-surface-variant mt-1">
                <a href="{{ route('dashboard') }}" class="text-primary hover:underline">Dashboard</a>
                <span class="material-symbols-outlined text-[16px]">chevron_right</span>
                <span>Payment Reconciliation</span>
            </nav>
        </div>
        
        <!-- Date Selector -->
        <form method="GET" action="{{ route('reconciliation.index') }}" class="flex items-center gap-2 bg-white rounded-xl shadow-sm border border-outline-variant/30 px-3 py-1.5">
            <span class="material-symbols-outlined text-secondary text-[20px]">calendar_today</span>
            <input type="date" name="date" value="{{ $date }}" onchange="this.form.submit()" class="border-0 p-0 text-body-md font-medium text-on-surface focus:ring-0 cursor-pointer bg-transparent">
        </form>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-container-gap mb-8">
        <!-- Total Orders Today -->
        <div class="bg-white rounded-xl shadow-sm p-card-padding flex items-center justify-between">
            <div>
                <p class="font-label-sm text-label-sm text-secondary uppercase tracking-wider">Total Orders Today</p>
                <h3 class="font-metric-display text-metric-display text-on-surface mt-1">{{ $totalOrdersCount }}</h3>
            </div>
            <div class="w-12 h-12 bg-primary-container/10 rounded-xl flex items-center justify-center text-primary">
                <span class="material-symbols-outlined text-[28px]">shopping_bag</span>
            </div>
        </div>

        <!-- Pending Verification -->
        <div class="bg-white rounded-xl shadow-sm p-card-padding flex items-center justify-between border-l-4 border-warning">
            <div>
                <p class="font-label-sm text-label-sm text-secondary uppercase tracking-wider">Pending Verification</p>
                <h3 class="font-metric-display text-metric-display text-warning mt-1">{{ $totalPendingCount }}</h3>
            </div>
            <div class="w-12 h-12 bg-warning/10 rounded-xl flex items-center justify-center text-warning">
                <span class="material-symbols-outlined text-[28px]">hourglass_empty</span>
            </div>
        </div>

        <!-- Reconciled Today -->
        <div class="bg-white rounded-xl shadow-sm p-card-padding flex items-center justify-between border-l-4 border-success">
            <div>
                <p class="font-label-sm text-label-sm text-secondary uppercase tracking-wider">Reconciled Today</p>
                <h3 class="font-metric-display text-metric-display text-success mt-1">{{ $reconciledCount }}</h3>
            </div>
            <div class="w-12 h-12 bg-success/10 rounded-xl flex items-center justify-center text-success">
                <span class="material-symbols-outlined text-[28px]">done_all</span>
            </div>
        </div>

        <!-- Reconciled Amount Today -->
        <div class="bg-white rounded-xl shadow-sm p-card-padding flex items-center justify-between">
            <div>
                <p class="font-label-sm text-label-sm text-secondary uppercase tracking-wider">Reconciled Amount</p>
                <h3 class="font-headline-lg text-headline-lg text-primary mt-1">Rp{{ number_format($reconciledAmount, 0, ',', '.') }}</h3>
            </div>
            <div class="w-12 h-12 bg-primary/10 rounded-xl flex items-center justify-center text-primary">
                <span class="material-symbols-outlined text-[28px]">payments</span>
            </div>
        </div>
    </div>

    <!-- Main Grid -->
    <div class="grid grid-cols-1 xl:grid-cols-12 gap-container-gap">
        
        <!-- Left Panel: Pending Verification (Backlog List) -->
        <div class="xl:col-span-7 bg-white rounded-xl shadow-sm overflow-hidden flex flex-col min-h-[400px]">
            <div class="p-card-padding border-b border-outline-variant/30 flex justify-between items-center bg-surface-container/10">
                <h3 class="font-headline-md text-headline-md text-on-surface flex items-center gap-2">
                    <span class="material-symbols-outlined text-warning">hourglass_top</span> Pending Payment Approvals
                </h3>
                <span class="px-2.5 py-0.5 bg-warning/10 text-warning text-label-sm font-label-sm rounded-full">{{ $pendingOrdersList->count() }} Orders</span>
            </div>
            
            <div class="flex-1 overflow-x-auto">
                @if($pendingOrdersList->isEmpty())
                    <div class="flex flex-col items-center justify-center h-full py-16 text-center">
                        <span class="material-symbols-outlined text-[64px] text-outline-variant/40 mb-3">task_alt</span>
                        <h4 class="font-headline-md text-headline-md text-on-surface">Kerja Bagus!</h4>
                        <p class="font-body-md text-body-md text-on-surface-variant max-w-sm mt-1">Tidak ada antrean pembayaran manual yang perlu direkonsiliasi saat ini.</p>
                    </div>
                @else
                    <table class="w-full text-left">
                        <thead>
                            <tr class="bg-surface-gray border-b border-outline-variant/30">
                                <th class="px-6 py-4 font-headline-md text-label-md text-on-surface-variant">Order / Customer</th>
                                <th class="px-6 py-4 font-headline-md text-label-md text-on-surface-variant">Date</th>
                                <th class="px-6 py-4 font-headline-md text-label-md text-on-surface-variant text-right">Amount</th>
                                <th class="px-6 py-4 font-headline-md text-label-md text-on-surface-variant text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-outline-variant/20">
                            @foreach($pendingOrdersList as $order)
                                <tr class="hover:bg-surface-container-low transition-colors duration-150">
                                    <td class="px-6 py-4">
                                        <div class="font-headline-md text-headline-md text-on-surface font-semibold">#{{ $order->order_number }}</div>
                                        <div class="font-body-sm text-body-sm text-on-surface-variant mt-0.5">{{ $order->customer->name ?? 'Guest' }}</div>
                                    </td>
                                    <td class="px-6 py-4 font-body-md text-body-md text-on-surface-variant">
                                        {{ $order->created_at->format('d M Y') }}
                                        <div class="text-[11px] text-outline-variant">{{ $order->created_at->format('H:i') }}</div>
                                    </td>
                                    <td class="px-6 py-4 text-right font-headline-md text-headline-md text-on-surface font-bold">
                                        Rp{{ number_format($order->total, 0, ',', '.') }}
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <a href="{{ route('orders.show', $order->id) }}" class="inline-flex items-center gap-1 px-3 py-1.5 bg-primary text-white rounded-lg font-label-sm text-label-sm hover:opacity-90 transition-all shadow-sm">
                                            <span class="material-symbols-outlined text-[16px]">gavel</span> Reconcile
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>
        </div>

        <!-- Right Panel: Reconciled List for Selected Date -->
        <div class="xl:col-span-5 bg-white rounded-xl shadow-sm overflow-hidden flex flex-col min-h-[400px]">
            <div class="p-card-padding border-b border-outline-variant/30 flex justify-between items-center bg-surface-container/10">
                <h3 class="font-headline-md text-headline-md text-on-surface flex items-center gap-2">
                    <span class="material-symbols-outlined text-success">verified</span> Reconciled on {{ Carbon\Carbon::parse($date)->format('d M Y') }}
                </h3>
                <span class="px-2.5 py-0.5 bg-success/10 text-success text-label-sm font-label-sm rounded-full">{{ $reconciledOrdersList->count() }} Reconciled</span>
            </div>
            
            <div class="flex-1 overflow-x-auto">
                @if($reconciledOrdersList->isEmpty())
                    <div class="flex flex-col items-center justify-center h-full py-16 text-center">
                        <span class="material-symbols-outlined text-[64px] text-outline-variant/40 mb-3">payments</span>
                        <h4 class="font-headline-md text-headline-md text-on-surface">Belum Ada Rekonsiliasi</h4>
                        <p class="font-body-md text-body-md text-on-surface-variant max-w-xs mt-1">Belum ada pembayaran manual yang berhasil direkonsiliasi pada tanggal ini.</p>
                    </div>
                @else
                    <table class="w-full text-left">
                        <thead>
                            <tr class="bg-surface-gray border-b border-outline-variant/30">
                                <th class="px-6 py-4 font-headline-md text-label-md text-on-surface-variant">Order</th>
                                <th class="px-6 py-4 font-headline-md text-label-md text-on-surface-variant text-right">Amount</th>
                                <th class="px-6 py-4 font-headline-md text-label-md text-on-surface-variant">Verification Note</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-outline-variant/20">
                            @foreach($reconciledOrdersList as $order)
                                <tr class="hover:bg-surface-container-low transition-colors duration-150">
                                    <td class="px-6 py-4">
                                        <a href="{{ route('orders.show', $order->id) }}" class="font-headline-md text-headline-md text-primary font-semibold hover:underline">
                                            #{{ $order->order_number }}
                                        </a>
                                        <div class="font-body-sm text-body-sm text-on-surface-variant mt-0.5">{{ $order->customer->name ?? 'Guest' }}</div>
                                    </td>
                                    <td class="px-6 py-4 text-right font-headline-md text-headline-md text-on-surface font-bold">
                                        Rp{{ number_format($order->total, 0, ',', '.') }}
                                    </td>
                                    <td class="px-6 py-4">
                                        @if(!empty($order->meta['reconciliation_notes']))
                                            <p class="font-body-sm text-body-sm text-on-surface-variant italic truncate max-w-[200px]" title="{{ $order->meta['reconciliation_notes'] }}">
                                                "{{ $order->meta['reconciliation_notes'] }}"
                                            </p>
                                        @else
                                            <span class="text-xs text-outline-variant font-medium">Reconciled</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>
        </div>
        
    </div>
@endsection
