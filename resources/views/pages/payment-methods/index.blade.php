@extends('layouts.app')

@section('title', 'Payment Methods')

@section('content')
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="font-headline-lg text-headline-lg text-on-surface">Payment Methods</h1>
            <nav class="flex items-center gap-2 text-body-md text-on-surface-variant mt-1">
                <a href="{{ route('dashboard') }}" class="text-primary hover:underline">Dashboard</a>
                <span class="material-symbols-outlined text-[16px]">chevron_right</span>
                <span>Shipping & Payment</span>
                <span class="material-symbols-outlined text-[16px]">chevron_right</span>
                <span>Payment Methods</span>
            </nav>
        </div>
        <a href="{{ route('payment-methods.create') }}" class="flex items-center gap-2 px-4 py-2 bg-primary text-white rounded-lg font-label-md hover:opacity-90 transition-all shadow-sm">
            <span class="material-symbols-outlined text-[18px]">add</span> Create Payment Method
        </a>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-outline-variant/30 overflow-hidden">
        <div class="p-4 border-b border-outline-variant flex flex-col sm:flex-row gap-3 justify-between">
            <form method="GET" class="flex items-center gap-2">
                <div class="relative">
                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-on-surface-variant text-[18px] material-symbols-outlined">search</span>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search payment methods..." class="pl-9 pr-4 py-2 border border-outline-variant rounded-lg text-body-md focus:ring-2 focus:ring-primary/20 focus:outline-none">
                </div>
                <select name="type" class="px-3 py-2 border border-outline-variant rounded-lg text-body-md bg-white">
                    <option value="">All Types</option>
                    <option value="1" {{ request('type') == '1' ? 'selected' : '' }}>Bank Transfer</option>
                    <option value="2" {{ request('type') == '2' ? 'selected' : '' }}>Virtual Account</option>
                    <option value="3" {{ request('type') == '3' ? 'selected' : '' }}>E-Wallet</option>
                    <option value="4" {{ request('type') == '4' ? 'selected' : '' }}>QRIS</option>
                    <option value="5" {{ request('type') == '5' ? 'selected' : '' }}>Credit Card</option>
                    <option value="6" {{ request('type') == '6' ? 'selected' : '' }}>Debit Card</option>
                    <option value="7" {{ request('type') == '7' ? 'selected' : '' }}>COD</option>
                    <option value="8" {{ request('type') == '8' ? 'selected' : '' }}>PayLater</option>
                </select>
                <select name="status" class="px-3 py-2 border border-outline-variant rounded-lg text-body-md bg-white">
                    <option value="">All Status</option>
                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                </select>
                <button type="submit" class="px-4 py-2 bg-primary text-white rounded-lg font-label-md hover:opacity-90 transition-all shadow-sm">Filter</button>
            </form>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-surface-gray">
                        <th class="px-gutter py-4 font-label-md text-label-md text-on-surface-variant uppercase tracking-wider">Code</th>
                        <th class="px-gutter py-4 font-label-md text-label-md text-on-surface-variant uppercase tracking-wider">Name</th>
                        <th class="px-gutter py-4 font-label-md text-label-md text-on-surface-variant uppercase tracking-wider">Type</th>
                        <th class="px-gutter py-4 font-label-md text-label-md text-on-surface-variant uppercase tracking-wider">Charge</th>
                        <th class="px-gutter py-4 font-label-md text-label-md text-on-surface-variant uppercase tracking-wider">Status</th>
                        <th class="px-gutter py-4 font-label-md text-label-md text-on-surface-variant uppercase tracking-wider text-center">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-outline-variant/20">
                    @forelse($paymentMethods as $method)
                    @php
                        $typeLabel = match($method->type) {
                            1 => 'Bank Transfer',
                            2 => 'Virtual Account',
                            3 => 'E-Wallet',
                            4 => 'QRIS',
                            5 => 'Credit Card',
                            6 => 'Debit Card',
                            7 => 'COD',
                            8 => 'PayLater',
                            default => 'Unknown'
                        };
                        $chargeText = '-';
                        if ($method->has_charge && $method->charge_value) {
                            if ($method->charge_type == 1) {
                                $chargeText = $method->charge_value . '%';
                            } else {
                                $chargeText = 'Rp ' . number_format($method->charge_value, 0, ',', '.');
                            }
                        }
                    @endphp
                    <tr class="hover:bg-surface-container/30 transition-colors {{ !$method->status || $method->deleted ? 'opacity-60' : '' }}">
                        <td class="px-gutter py-4 font-body-md text-body-md text-on-surface font-semibold">{{ $method->code }}</td>
                        <td class="px-gutter py-4">
                            <div class="flex items-center gap-2">
                                @if($method->image)
                                    <img src="{{ $method->image }}" class="w-8 h-8 rounded object-cover" alt="">
                                @endif
                                <span class="font-body-md text-body-md text-on-surface">{{ $method->name }}</span>
                            </div>
                        </td>
                        <td class="px-gutter py-4">
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-label-sm bg-primary/10 text-primary">{{ $typeLabel }}</span>
                        </td>
                        <td class="px-gutter py-4 font-body-md text-body-md text-on-surface">{{ $chargeText }}</td>
                        <td class="px-gutter py-4">
                            @if($method->deleted)
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-label-sm bg-danger/10 text-danger">
                                    <span class="w-1.5 h-1.5 rounded-full bg-current"></span> Deleted
                                </span>
                            @elseif($method->status)
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-label-sm bg-success/10 text-success">
                                    <span class="w-1.5 h-1.5 rounded-full bg-current"></span> Active
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-label-sm bg-danger/10 text-danger">
                                    <span class="w-1.5 h-1.5 rounded-full bg-current"></span> Inactive
                                </span>
                            @endif
                        </td>
                        <td class="px-gutter py-4">
                            <div class="flex gap-2">
                                <a href="{{ route('payment-methods.edit', $method->id) }}" class="text-on-surface-variant hover:text-primary" title="Edit"><span class="material-symbols-outlined text-[18px]">edit</span></a>
                                <form action="{{ route('payment-methods.destroy', $method->id) }}" method="POST" class="inline" onsubmit="return confirm('Delete payment method {{ $method->name }}?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-on-surface-variant hover:text-danger" title="Delete"><span class="material-symbols-outlined text-[18px]">delete</span></button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-gutter py-8 text-center text-on-surface-variant">No payment methods found</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-gutter py-4 border-t border-outline-variant bg-surface-container-low/30 flex justify-between items-center">
            <p class="font-body-md text-body-md text-on-surface-variant">Showing {{ $paymentMethods->firstItem() ?? 0 }}-{{ $paymentMethods->lastItem() ?? 0 }} of {{ number_format($paymentMethods->total()) }} entries</p>
            <div class="flex gap-2">{{ $paymentMethods->links() }}</div>
        </div>
    </div>
@endsection
