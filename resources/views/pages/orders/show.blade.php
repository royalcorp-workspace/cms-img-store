@extends('layouts.app')

@section('title', 'Order Details')

@section('content')
<div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
    <div>
        <h1 class="font-headline-lg text-headline-lg text-on-surface">Order Details</h1>
        <div class="inline-flex items-center gap-2 text-label-sm text-on-surface-variant mt-1">
            <span>#{{ $order->order_number }}</span>
            <span class="text-outline-variant">|</span>
            <span>{{ $order->created_at->format('d M Y') }}</span>
            <span class="text-outline-variant">|</span>
            <span>{{ $order->created_at->format('h:i A') }}</span>
        </div>
    </div>
    <button class="flex items-center gap-2 px-4 py-2 bg-primary text-on-primary rounded-lg font-label-md text-label-md hover:bg-primary-container transition-colors">
        <span class="material-symbols-outlined text-[18px]">print</span>
        Print Invoice
    </button>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-container-gap mb-8">
    <div class="bg-white rounded-xl shadow-sm p-card-padding">
        <div class="flex items-center gap-3 mb-4">
            <div class="w-10 h-10 bg-primary-container/10 rounded-lg flex items-center justify-center text-primary">
                <span class="material-symbols-outlined">person</span>
            </div>
            <h3 class="font-headline-md text-headline-md text-on-surface">Customer</h3>
        </div>
        <div class="space-y-2">
            @if($order->customer)
                <p class="font-headline-md text-headline-md text-on-surface">{{ $order->customer->name }}</p>
                <p class="font-body-md text-body-md text-on-surface-variant">{{ $order->customer->email ?? 'N/A' }}</p>
                @if($order->customer->phone)
                <div class="pt-2">
                    <p class="font-label-sm text-label-sm text-secondary uppercase tracking-wider">Phone</p>
                    <p class="font-body-md text-body-md text-on-surface">{{ $order->customer->phone }}</p>
                </div>
                @endif
            @else
                <p class="font-body-md text-body-md text-on-surface-variant">Guest</p>
            @endif
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm p-card-padding">
        <div class="flex items-center gap-3 mb-4">
            <div class="w-10 h-10 bg-primary-container/10 rounded-lg flex items-center justify-center text-primary">
                <span class="material-symbols-outlined">payments</span>
            </div>
            <h3 class="font-headline-md text-headline-md text-on-surface">Payment Summary</h3>
        </div>
        @php
            $subtotal = $order->subtotal ?? $order->items->sum('total');
            $tax = $order->tax ?? 0;
            $discount = $order->discount ?? 0;
            $total = $order->total ?? 0;
            $shipping = max(0, $total - $subtotal - $tax + $discount);
        @endphp
        <div class="space-y-3">
            <div class="flex justify-between font-body-md text-body-md">
                <span class="text-secondary">Subtotal</span>
                <span class="text-on-surface">Rp{{ number_format($subtotal, 2, ',', '.') }}</span>
            </div>
            @if($shipping > 0)
            <div class="flex justify-between font-body-md text-body-md">
                <span class="text-secondary">Shipping</span>
                <span class="text-on-surface">Rp{{ number_format($shipping, 2, ',', '.') }}</span>
            </div>
            @endif
            @if($tax > 0)
            <div class="flex justify-between font-body-md text-body-md">
                <span class="text-secondary">Tax</span>
                <span class="text-on-surface">Rp{{ number_format($tax, 2, ',', '.') }}</span>
            </div>
            @endif
            <div class="flex justify-between font-body-md text-body-md">
                <span class="text-secondary">Discount Nominal</span>
                <span class="text-on-surface">Rp{{ number_format($order->discount_nominal ?? 0, 2, ',', '.') }}</span>
            </div>
            <div class="flex justify-between font-body-md text-body-md">
                <span class="text-secondary">Discount Percent</span>
                <span class="text-on-surface">{{ $order->discount_percent ?? 0 }}%</span>
            </div>
            @if($discount > 0)
            <div class="flex justify-between font-body-md text-body-md text-danger">
                <span>Discount</span>
                <span>-Rp{{ number_format($discount, 2, ',', '.') }}</span>
            </div>
            @endif
            <div class="pt-3 border-t border-outline-variant flex justify-between">
                <span class="font-headline-md text-headline-md text-on-surface">Total</span>
                <span class="font-headline-md text-headline-md text-primary">Rp{{ number_format($total, 2, ',', '.') }}</span>
            </div>
            <div class="mt-4 flex items-center gap-2 px-3 py-2 bg-surface-gray rounded-lg">
                <span class="material-symbols-outlined text-primary">credit_card</span>
                <p class="font-label-md text-label-md text-on-surface">Paid via {{ ucfirst(str_replace('_', ' ', $order->payment_method ?? 'N/A')) }}</p>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm p-card-padding">
        <div class="flex items-center gap-3 mb-4">
            <div class="w-10 h-10 bg-primary-container/10 rounded-lg flex items-center justify-center text-primary">
                <span class="material-symbols-outlined">info</span>
            </div>
            <h3 class="font-headline-md text-headline-md text-on-surface">Order Info</h3>
        </div>
        <div class="space-y-3">
            <div class="flex justify-between font-body-md text-body-md">
                <span class="text-secondary">Order ID</span>
                <span class="text-on-surface font-mono">#{{ $order->order_number }}</span>
            </div>
            <div class="flex justify-between font-body-md text-body-md">
                <span class="text-secondary">Date</span>
                <span class="text-on-surface">{{ $order->created_at->format('d M Y') }}</span>
            </div>
            <div class="flex justify-between font-body-md text-body-md">
                <span class="text-secondary">Status</span>
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-label-sm font-label-sm {{ $order->statusBadgeClass }}">
                    {{ $order->statusLabel() }}
                </span>
            </div>
            <div class="flex justify-between font-body-md text-body-md">
                <span class="text-secondary">Payment</span>
                <span class="text-on-surface">{{ ucfirst(str_replace('_', ' ', $order->payment_method ?? 'N/A')) }}</span>
            </div>
            <div class="flex justify-between font-body-md text-body-md">
                <span class="text-secondary">Payment Status</span>
                <span class="text-on-surface">{{ ucfirst(str_replace('_', ' ', $order->payment_status ?? 'N/A')) }}</span>
            </div>
            @if($order->notes)
            <div class="pt-3 border-t border-outline-variant">
                <p class="font-label-sm text-label-sm text-secondary uppercase tracking-wider mb-1">Notes</p>
                <p class="font-body-md text-body-md text-on-surface-variant italic">{{ $order->notes }}</p>
            </div>
            @endif
        </div>
    </div>
</div>

<section class="bg-white rounded-xl shadow-sm overflow-hidden mb-8">
    <div class="p-card-padding border-b border-outline-variant flex justify-between items-center">
        <h3 class="font-headline-md text-headline-md text-on-surface">Order Items</h3>
        <span class="font-label-md text-label-md text-secondary">{{ $order->items->count() }} items total</span>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-left">
            <thead>
                <tr class="bg-surface-gray">
                    <th class="px-6 py-4 font-headline-md text-label-md text-on-surface-variant">Product</th>
                    <th class="px-6 py-4 font-headline-md text-label-md text-on-surface-variant text-right">Price</th>
                    <th class="px-6 py-4 font-headline-md text-label-md text-on-surface-variant text-center">Qty</th>
                    <th class="px-6 py-4 font-headline-md text-label-md text-on-surface-variant text-right">Discount</th>
                    <th class="px-6 py-4 font-headline-md text-label-md text-on-surface-variant text-right">Total</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-outline-variant">
                @foreach($order->items as $item)
                <tr class="hover:bg-surface-container-low transition-colors duration-150">
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-4">
                            <div class="w-16 h-16 rounded-lg bg-surface-gray flex-shrink-0 overflow-hidden border border-outline-variant">
                                @if($item->product && $item->product->thumbnail)
                                    <img class="w-full h-full object-cover" src="{{ asset('storage/' . $item->product->thumbnail) }}" alt="{{ $item->name }}">
                                @else
                                    <div class="w-full h-full flex items-center justify-center text-on-surface-variant">
                                        <span class="material-symbols-outlined text-[24px]">image</span>
                                    </div>
                                @endif
                            </div>
                            <div>
                                <p class="font-headline-md text-headline-md text-on-surface">{{ $item->name }}</p>
                                @if($item->variant)
                                    <p class="font-body-md text-body-md text-secondary">{{ $item->variant->variant_name }}</p>
                                @endif
                                @if($item->item_notes)
                                    <p class="font-body-sm text-body-sm text-secondary mt-1">{{ $item->item_notes }}</p>
                                @endif
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4 font-body-md text-body-md text-on-surface">Rp{{ number_format($item->unit_price, 2, ',', '.') }}</td>
                    <td class="px-6 py-4 text-center">
                        <span class="font-label-md text-label-md px-3 py-1 bg-surface-gray rounded text-on-surface">{{ $item->quantity }}</span>
                    </td>
                    <td class="px-6 py-4 text-right font-body-md text-body-md text-on-surface">
                        @if($item->discount_nominal > 0)
                            -Rp{{ number_format($item->discount_nominal, 2, ',', '.') }}
                        @elseif($item->discount_percent > 0)
                            -{{ $item->discount_percent }}%
                        @else
                            -
                        @endif
                    </td>
                    <td class="px-6 py-4 text-right font-headline-md text-headline-md text-on-surface">Rp{{ number_format($item->total, 2, ',', '.') }}</td>
                </tr>
                @endforeach
            </tbody>
            <tfoot class="bg-surface-container-low/50">
                <tr>
                    <td colspan="4" class="px-6 py-4 text-right font-headline-md text-headline-md text-on-surface">Total</td>
                    <td class="px-6 py-4 text-right font-headline-md text-headline-md text-primary">Rp{{ number_format($order->items->sum('total'), 2, ',', '.') }}</td>
                </tr>
            </tfoot>
        </table>
    </div>
</section>

@endsection
