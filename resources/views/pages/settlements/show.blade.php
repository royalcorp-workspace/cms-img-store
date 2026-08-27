@extends('layouts.app')

@section('title', 'Settlement Detail')

@section('content')
    <!-- Dashboard Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
        <div>
            <h1 class="font-headline-lg text-headline-lg text-on-surface">Settlement Detail</h1>
            <nav class="flex items-center gap-2 text-body-md text-on-surface-variant mt-1">
                <a href="{{ route('dashboard') }}" class="text-primary hover:underline">Dashboard</a>
                <span class="material-symbols-outlined text-[16px]">chevron_right</span>
                <a href="{{ route('settlements.index') }}" class="text-primary hover:underline">Settlements</a>
                <span class="material-symbols-outlined text-[16px]">chevron_right</span>
                <span>{{ $settlement->reference_id }}</span>
            </nav>
        </div>
        <span class="px-3 py-1 inline-flex text-sm leading-5 font-semibold rounded-full {{ $settlement->status == 'success' ? 'bg-success/10 text-success' : 'bg-warning/10 text-warning' }}">
            {{ ucfirst($settlement->status) }}
        </span>
    </div>

    @include('layouts.partials.sales-submenu')

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
        <div class="bg-white rounded-lg shadow p-6 border-t-4 border-gray-400">
            <p class="text-sm font-medium text-gray-500 mb-1">Gross Amount</p>
            <p class="text-2xl font-bold text-gray-900">Rp {{ number_format($settlement->gross_amount, 0, ',', '.') }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-6 border-t-4 border-red-500">
            <p class="text-sm font-medium text-gray-500 mb-1">Espay Fee (MDR)</p>
            <p class="text-2xl font-bold text-red-600">- Rp {{ number_format($settlement->fee_amount, 0, ',', '.') }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-6 border-t-4 border-green-500">
            <p class="text-sm font-medium text-gray-500 mb-1">Net Amount Received</p>
            <p class="text-2xl font-bold text-green-600">Rp {{ number_format($settlement->net_amount, 0, ',', '.') }}</p>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">Orders in this Settlement</h3>
        </div>
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Order ID</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Customer</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Payment Method</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($settlement->orders as $order)
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-primary">
                        <a href="{{ route('orders.show', $order->id) }}">{{ $order->id }}</a>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        {{ $order->created_at->format('d M Y H:i') }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                        {{ $order->customer_name ?? 'N/A' }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        {{ $order->payment_method ?? 'Espay' }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium text-gray-900">
                        Rp {{ number_format($order->grand_total ?? $order->total_amount, 0, ',', '.') }}
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">No orders linked to this settlement yet.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
@endsection
