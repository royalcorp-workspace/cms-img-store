@extends('layouts.app')

@section('title', 'Order Details')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-outline-variant/50 pb-4">
        <h1 class="font-headline-lg text-headline-lg text-on-surface uppercase tracking-wider">
            ORDER DETAIL #{{ $order->order_number }}
        </h1>
    </div>

    @include('layouts.partials.sales-submenu')

    <div class="grid grid-cols-1 xl:grid-cols-12 gap-6">
        <!-- Left Column -->
        <div class="xl:col-span-4 space-y-6">
            
            <!-- Order Info Card -->
            <div class="bg-white rounded-xl shadow-sm border border-outline-variant/30 p-4">
                <h2 class="text-base font-bold text-primary mb-4 flex items-center gap-2">
                    <span class="material-symbols-outlined text-[18px]">assignment</span> Order Info
                </h2>
                <div class="space-y-3">
                    <div class="relative border border-outline-variant rounded-lg p-2.5">
                        <span class="absolute -top-2 left-2.5 bg-white px-1 text-[9px] font-bold text-on-surface-variant uppercase tracking-wider">Order Number</span>
                        <div class="text-xs font-medium text-on-surface">#{{ $order->order_number }}</div>
                    </div>
                    <div class="relative border border-outline-variant rounded-lg p-2.5">
                        <span class="absolute -top-2 left-2.5 bg-white px-1 text-[9px] font-bold text-on-surface-variant uppercase tracking-wider">Date</span>
                        <div class="text-xs font-medium text-on-surface">{{ $order->created_at->format('d M Y') }} - {{ $order->created_at->format('H:i') }}</div>
                    </div>
                    <div class="relative border border-outline-variant rounded-lg p-2.5">
                        <span class="absolute -top-2 left-2.5 bg-white px-1 text-[9px] font-bold text-on-surface-variant uppercase tracking-wider">Order Status</span>
                        <div class="text-xs font-medium text-on-surface">
                            <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-[10px] font-bold {{ $order->statusBadgeClass }}">
                                {{ $order->statusLabel() }}
                            </span>
                        </div>
                    </div>
                    @if($order->notes)
                    <div class="relative border border-outline-variant rounded-lg p-2.5">
                        <span class="absolute -top-2 left-2.5 bg-white px-1 text-[9px] font-bold text-on-surface-variant uppercase tracking-wider">Notes</span>
                        <div class="text-xs font-medium text-on-surface italic">{{ $order->notes }}</div>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Customer Card -->
            <div class="bg-white rounded-xl shadow-sm border border-outline-variant/30 p-4">
                <h2 class="text-base font-bold text-primary mb-4 flex items-center gap-2">
                    <span class="material-symbols-outlined text-[18px]">person</span> Customer
                </h2>
                <div class="space-y-3">
                    <div class="relative border border-primary rounded-lg p-2.5 bg-primary/5">
                        <span class="absolute -top-2 left-2.5 bg-white px-1 text-[9px] font-bold text-primary uppercase tracking-wider">Customer</span>
                        <div class="text-sm text-primary font-bold">{{ $order->customer->name ?? 'Guest' }}</div>
                    </div>
                    <div class="relative border border-outline-variant rounded-lg p-2.5">
                        <span class="absolute -top-2 left-2.5 bg-white px-1 text-[9px] font-bold text-on-surface-variant uppercase tracking-wider">Payment Method</span>
                        <div class="text-xs font-medium text-on-surface">{{ ucfirst(str_replace('_', ' ', $order->payment_method ?? 'Transfer')) }}</div>
                    </div>
                    <div class="relative border border-outline-variant rounded-lg p-2.5">
                        <span class="absolute -top-2 left-2.5 bg-white px-1 text-[9px] font-bold text-on-surface-variant uppercase tracking-wider">Payment Status</span>
                        <div class="text-xs font-medium text-on-surface">
                            <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-[10px] font-bold {{ $order->paymentStatusBadgeClass }}">
                                {{ $order->paymentStatusLabel() }}
                            </span>
                        </div>
                    </div>
                    <div class="relative border border-outline-variant rounded-lg p-2.5">
                        <span class="absolute -top-2 left-2.5 bg-white px-1 text-[9px] font-bold text-on-surface-variant uppercase tracking-wider">Email</span>
                        <div class="text-xs font-medium text-on-surface">{{ $order->customer->email ?? '-' }}</div>
                    </div>
                    <div class="relative border border-outline-variant rounded-lg p-2.5">
                        <span class="absolute -top-2 left-2.5 bg-white px-1 text-[9px] font-bold text-on-surface-variant uppercase tracking-wider">Phone</span>
                        <div class="text-xs font-medium text-on-surface">{{ $order->customer->phone ?? ($order->meta['shipping_address']['phone'] ?? '-') }}</div>
                    </div>
                    <div class="relative border border-outline-variant rounded-lg p-2.5">
                        <span class="absolute -top-2 left-2.5 bg-white px-1 text-[9px] font-bold text-on-surface-variant uppercase tracking-wider">Address</span>
                        <div class="text-[11px] font-medium text-on-surface leading-tight">
                            @if(!empty($order->meta['shipping_address']))
                                {{ $order->meta['shipping_address']['address'] ?? '' }}, {{ $order->meta['shipping_address']['sub_district'] ?? '' }}
                            @elseif($order->customer && method_exists($order->customer, 'addresses') && $order->customer->addresses->isNotEmpty())
                                {{ $order->customer->addresses->first()->address }}
                            @else
                                -
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Verification Card -->
            @if(str_contains(strtolower($order->payment_method), 'transfer_manual') || str_contains(strtolower($order->payment_method), 'manual'))
            <div class="bg-primary/5 rounded-xl shadow-sm border border-primary/20 p-4">
                <h2 class="text-base font-bold text-primary mb-3 flex items-center gap-2">
                    <span class="material-symbols-outlined text-[18px]">gavel</span> Manual Verification
                </h2>
                @if($order->meta['payment_proof'] ?? false)
                    <a href="{{ env('FRONTEND_URL', 'http://127.0.0.1:81') }}/storage/{{ $order->meta['payment_proof'] }}" target="_blank" class="block text-center w-full bg-surface-container-highest border border-outline-variant py-2 rounded-lg text-xs font-medium mb-3">View Payment Proof</a>
                @endif
                <form method="POST" action="{{ route('orders.verify-payment', $order->id) }}" class="space-y-3 border-t border-primary/20 pt-3">
                    @csrf
                    <div>
                        <label class="block text-[10px] font-bold mb-1 text-on-surface-variant uppercase tracking-wider">Update Status</label>
                        <select name="status" class="w-full px-2 py-1.5 border border-outline-variant rounded-lg bg-white text-xs">
                            <option value="1" {{ $order->status == 1 ? 'selected' : '' }}>Pending</option>
                            <option value="2" {{ $order->status == 2 ? 'selected' : '' }}>Confirmed</option>
                            <option value="3" {{ $order->status == 3 ? 'selected' : '' }}>Processing</option>
                            <option value="6" {{ $order->status == 6 ? 'selected' : '' }}>Cancelled</option>
                        </select>
                    </div>
                    <input type="hidden" name="payment_status" value="1">
                    <button type="submit" class="block text-center w-full bg-primary text-white py-2 rounded-lg text-xs font-medium hover:opacity-90">Approve & Mark as Paid</button>
                </form>
            </div>
            @endif

        </div>

        <!-- Right Column -->
        <div class="xl:col-span-8">
            <div class="bg-white rounded-xl shadow-sm border border-outline-variant/30 flex flex-col h-full">
                <div class="p-3 border-b border-outline-variant/50 flex justify-between items-center">
                    <h2 class="text-sm font-bold text-primary flex items-center gap-1.5">
                        <span class="material-symbols-outlined text-[16px]">inventory_2</span> Items
                    </h2>
                    <button class="px-2 py-0.5 border border-outline-variant rounded-full text-[9px] font-bold text-on-surface-variant flex items-center gap-1 hover:bg-surface-container transition-colors uppercase tracking-wider">
                        <span class="material-symbols-outlined text-[12px]">help</span> Help
                    </button>
                </div>
                
                <div class="flex-1 overflow-x-auto pb-4">
                    <table class="w-full text-left border-collapse leading-tight" style="font-size: 11px;">
                        <thead>
                            <tr class="border-b-2 border-outline-variant/50">
                                <th class="px-1.5 py-2 font-bold text-on-surface-variant whitespace-nowrap">NO.</th>
                                <th class="px-1.5 py-2 font-bold text-on-surface-variant min-w-[150px]">PRODUCT NAME</th>
                                <th class="px-1.5 py-2 font-bold text-on-surface-variant min-w-[80px]">VARIANTS</th>
                                <th class="px-1.5 py-2 font-bold text-on-surface-variant text-right whitespace-nowrap">QTY</th>
                                <th class="px-1.5 py-2 font-bold text-on-surface-variant whitespace-nowrap">UOM</th>
                                <th class="px-1.5 py-2 font-bold text-on-surface-variant text-right whitespace-nowrap">PRICE</th>
                                <th class="px-1.5 py-2 font-bold text-on-surface-variant text-right whitespace-nowrap">SUB TOTAL</th>
                                <th class="px-1.5 py-2 font-bold text-on-surface-variant text-right whitespace-nowrap">DISC %</th>
                                <th class="px-1.5 py-2 font-bold text-on-surface-variant text-right whitespace-nowrap">DISC NOM</th>
                                <th class="px-1.5 py-2 font-bold text-on-surface-variant text-right whitespace-nowrap">SALES</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-outline-variant/30">
                            @php $originalSubtotal = 0; $totalDiscount = 0; @endphp
                            @foreach($order->items as $index => $item)
                            @php 
                                $price = $item->unit_price;
                                $sub = $price * $item->quantity;
                                $originalSubtotal += $sub;
                                
                                $disc_nom = $item->discount_nominal > 0 ? $item->discount_nominal : 0;
                                $disc_pct = $item->discount_percent > 0 ? $item->discount_percent : 0;
                                
                                if($disc_pct > 0) {
                                    $disc_nom = ($price * $disc_pct / 100);
                                }
                                $disc_total = $disc_nom * $item->quantity;
                                $totalDiscount += $disc_total;
                            @endphp
                            <tr class="hover:bg-surface-container-low transition-colors text-on-surface">
                                <td class="px-1.5 py-2 text-on-surface-variant align-top whitespace-nowrap">{{ $index + 1 }}</td>
                                <td class="px-1.5 py-2 font-medium align-top">
                                    {{ $item->name }}
                                </td>
                                <td class="px-1.5 py-2 text-on-surface-variant align-top">
                                    {{ $item->variant->variant_name ?? '-' }}
                                    <div class="opacity-70 mt-0.5" style="font-size: 10px;">
                                        {{ $item->variant->sku ?? $item->product->code ?? '-' }}
                                    </div>
                                </td>
                                <td class="px-1.5 py-2 text-right font-medium align-top whitespace-nowrap">{{ $item->quantity }}</td>
                                <td class="px-1.5 py-2 text-on-surface-variant align-top whitespace-nowrap">Pcs</td>
                                <td class="px-1.5 py-2 text-right align-top whitespace-nowrap">{{ number_format($price, 0, ',', '.') }}</td>
                                <td class="px-1.5 py-2 text-right align-top whitespace-nowrap">{{ number_format($sub, 0, ',', '.') }}</td>
                                <td class="px-1.5 py-2 text-right text-danger align-top whitespace-nowrap">{{ $disc_pct > 0 ? $disc_pct . '%' : '-' }}</td>
                                <td class="px-1.5 py-2 text-right text-danger align-top whitespace-nowrap">{{ number_format($disc_total, 0, ',', '.') }}</td>
                                <td class="px-1.5 py-2 text-right font-medium align-top whitespace-nowrap">{{ number_format($item->total, 0, ',', '.') }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Summary Panel -->
                <div class="p-3 bg-surface-gray border-t border-outline-variant/50 flex justify-end">
                    <table class="w-full max-w-sm text-[9px] lg:text-[10px] text-on-surface">
                        <tbody class="divide-y divide-outline-variant/30">
                            <tr>
                                <td class="py-1 text-on-surface-variant">Subtotal</td>
                                <td class="py-1 text-right font-medium">{{ number_format($originalSubtotal, 0, ',', '.') }}</td>
                            </tr>
                            
                            @php
                                $discount = $order->discount ?? 0;
                                $voucher = $order->voucher;
                                $voucherValue = $voucher->value ?? 0;
                                
                                $totalDiscountNominal = 0;
                                foreach ($order->items as $item) {
                                    $itemPrice = $item->unit_price ?? $item->total;
                                    if ($item->discount_nominal > 0) {
                                        $totalDiscountNominal += $item->discount_nominal * $item->quantity;
                                    } elseif ($item->discount_percent > 0) {
                                        $totalDiscountNominal += ($itemPrice * $item->discount_percent / 100) * $item->quantity;
                                    }
                                }
                                $discount = max($discount, $totalDiscountNominal);
                            @endphp

                            @if($discount > 0)
                            <tr>
                                <td class="py-1 text-danger">Discount</td>
                                <td class="py-1 text-right font-medium text-danger">-Rp{{ number_format($discount, 0, ',', '.') }}</td>
                            </tr>
                            @endif

                            @if($voucher)
                            <tr>
                                <td class="py-1 text-danger">Voucher ({{ $voucher->code ?? '' }})</td>
                                <td class="py-1 text-right font-medium text-danger">
                                    @if($voucher->type == 1)
                                        -{{ $voucher->value }}%
                                    @else
                                        -Rp{{ number_format($voucher->value, 0, ',', '.') }}
                                    @endif
                                </td>
                            </tr>
                            @endif

                            <tr class="bg-primary/5">
                                <td class="py-1.5 px-2 font-medium text-primary rounded-l-lg">Net Sales</td>
                                <td class="py-1.5 px-2 text-right font-bold text-primary rounded-r-lg">{{ number_format($order->subtotal ?? $order->items->sum('total'), 0, ',', '.') }}</td>
                            </tr>
                            
                            @if($order->shipping_cost > 0)
                            <tr>
                                <td class="py-1 text-on-surface-variant">Shipping Cost</td>
                                <td class="py-1 text-right font-medium">{{ number_format($order->shipping_cost, 0, ',', '.') }}</td>
                            </tr>
                            @endif
                            
                            @if($order->transaction_fee > 0)
                            <tr>
                                <td class="py-1 text-on-surface-variant">Transaction Fee</td>
                                <td class="py-1 text-right font-medium">{{ number_format($order->transaction_fee, 0, ',', '.') }}</td>
                            </tr>
                            @endif

                            <tr class="border-t border-outline-variant">
                                <td class="py-2 text-on-surface text-xs font-bold">Total Bill</td>
                                <td class="py-2 text-right text-sm font-bold text-primary">Rp{{ number_format($order->total, 0, ',', '.') }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
