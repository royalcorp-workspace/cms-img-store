@extends('layouts.app')

@section('title', 'Order Details')

@section('content')
    @if(session('success'))
        <div class="flex items-center gap-3 p-4 mb-6 bg-green-50 border border-green-200 text-green-800 rounded-xl shadow-sm">
            <span class="material-symbols-outlined text-green-600">check_circle</span>
            <p class="font-body-md text-body-md font-medium">{{ session('success') }}</p>
        </div>
    @endif

    @if(session('error'))
        <div class="flex items-center gap-3 p-4 mb-6 bg-red-50 border border-red-200 text-red-800 rounded-xl shadow-sm">
            <span class="material-symbols-outlined text-red-600">error</span>
            <p class="font-body-md text-body-md font-medium">{{ session('error') }}</p>
        </div>
    @endif

    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
        <div>
            <h1 class="font-headline-lg text-headline-lg text-on-surface">Order Details</h1>
            <div class="inline-flex items-center gap-2 text-label-sm text-on-surface-variant mt-1">
                <span>#{{ $order->order_number }}</span>
                <span class="text-outline-variant">|</span>
                <span>{{ $order->created_at->format('d M Y') }}</span>
                <span class="text-outline-variant">|</span>
                <span>{{ $order->created_at->format('H:i') }}</span>
            </div>
        </div>
        <button class="flex items-center gap-2 px-4 py-2 bg-primary text-on-primary rounded-lg font-label-md text-label-md hover:bg-primary-container transition-colors">
            <span class="material-symbols-outlined text-[18px]">print</span>
            Print Invoice
        </button>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-container-gap mb-8">
        <!-- Customer Info Card -->
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

        <!-- Shipping Address Card -->
        <div class="bg-white rounded-xl shadow-sm p-card-padding">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 bg-primary-container/10 rounded-lg flex items-center justify-center text-primary">
                    <span class="material-symbols-outlined">local_shipping</span>
                </div>
                <h3 class="font-headline-md text-headline-md text-on-surface">Shipping Address</h3>
            </div>
            @if(!empty($order->meta['shipping_address']))
                @php
                    $shipAddr = $order->meta['shipping_address'];
                @endphp
                <div class="space-y-2">
                    <p class="font-headline-md text-headline-md text-on-surface font-semibold">{{ $shipAddr['recipient_name'] ?? 'N/A' }}</p>
                    <p class="font-body-md text-body-md text-on-surface-variant">{{ $shipAddr['phone'] ?? 'N/A' }}</p>
                    <div class="pt-2 border-t border-outline-variant/30">
                        <p class="font-body-md text-body-md text-on-surface">{{ $shipAddr['address'] ?? 'N/A' }}</p>
                        <p class="font-body-sm text-body-sm text-on-surface-variant mt-1">
                            {{ $shipAddr['sub_district'] ?? '' }}{{ !empty($shipAddr['city']) ? ', ' . $shipAddr['city'] : '' }}
                        </p>
                        <p class="font-body-sm text-body-sm text-on-surface-variant">
                            {{ $shipAddr['province'] ?? '' }} {{ $shipAddr['postal_code'] ?? '' }}
                        </p>
                    </div>
                </div>
            @elseif($order->customer && method_exists($order->customer, 'addresses') && $order->customer->addresses->isNotEmpty())
                @php
                    $primaryAddr = $order->customer->addresses->where('is_primary', true)->first() ?? $order->customer->addresses->first();
                @endphp
                <div class="space-y-2">
                    <p class="font-headline-md text-headline-md text-on-surface font-semibold">{{ $primaryAddr->recipient_name }}</p>
                    <p class="font-body-md text-body-md text-on-surface-variant">{{ $primaryAddr->phone }}</p>
                    <div class="pt-2 border-t border-outline-variant/30">
                        <p class="font-body-md text-body-md text-on-surface">{{ $primaryAddr->address }}</p>
                        <p class="font-body-sm text-body-sm text-on-surface-variant mt-1">
                            {{ $primaryAddr->subDistrict->sub_district ?? '' }}{{ !empty($primaryAddr->subDistrict->city->name) ? ', ' . $primaryAddr->subDistrict->city->name : '' }}
                        </p>
                        <p class="font-body-sm text-body-sm text-on-surface-variant">
                            {{ $primaryAddr->subDistrict->city->province->name ?? '' }} {{ $primaryAddr->postal_code }}
                        </p>
                    </div>
                </div>
            @else
                <p class="font-body-md text-body-md text-on-surface-variant italic">No shipping address recorded.</p>
            @endif
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
                $shipping = $order->shipping_cost ?? 0;
                $transactionFee = $order->transaction_fee ?? 0;

                // Calculate original subtotal and total discount from items
                $originalSubtotal = 0;
                $totalDiscountNominal = 0;
                foreach ($order->items as $item) {
                    $itemPrice = $item->unit_price ?? $item->total;
                    $originalSubtotal += $itemPrice * $item->quantity;
                    
                    if ($item->discount_nominal > 0) {
                        $totalDiscountNominal += $item->discount_nominal * $item->quantity;
                    } elseif ($item->discount_percent > 0) {
                        $totalDiscountNominal += ($itemPrice * $item->discount_percent / 100) * $item->quantity;
                    }
                }

                // Fallback / merge order-level discount
                $discount = max($discount, $totalDiscountNominal);
                if ($discount == 0 && $originalSubtotal > $subtotal) {
                    $discount = $originalSubtotal - $subtotal;
                }

                $discountPercent = $order->discount_percent ?? ($order->items->where('discount_percent', '>', 0)->first()?->discount_percent ?? 0);
                if ($discountPercent == 0 && $discount > 0 && $originalSubtotal > 0) {
                    $discountPercent = ($discount / $originalSubtotal) * 100;
                }

                $voucher = $order->voucher;
                $voucherValue = $voucher->value ?? 0;
                $voucherLabel = $voucher->code ?? '';
                if ($voucher && $voucher->type == 1) {
                    $voucherLabel .= ' (-' . $voucherValue . '%)';
                } elseif ($voucher && $voucher->type == 2) {
                    $voucherLabel .= ' (-Rp' . number_format($voucherValue, 0, ',', '.') . ')';
                }
                
                $hasDiscount = $discount > 0 || $voucher;
            @endphp
            <div class="space-y-3">
                <div class="flex justify-between font-body-md text-body-md items-center">
                    <span class="text-secondary">Subtotal</span>
                    <div class="flex items-center gap-2">
                        @if($originalSubtotal > $subtotal)
                            <span class="line-through text-on-surface-variant/60">Rp{{ number_format($originalSubtotal, 2, ',', '.') }}</span>
                        @endif
                        <span class="text-on-surface">Rp{{ number_format($subtotal, 2, ',', '.') }}</span>
                    </div>
                </div>
                @if($hasDiscount)
                <div class="flex justify-between font-body-md text-body-md text-danger">
                    <span>
                        @if($voucher)
                            Voucher {{ $voucherLabel }}
                        @elseif($discountPercent > 0)
                            Discount ({{ number_format($discountPercent, 2, ',', '.') }}%)
                        @else
                            Discount
                        @endif
                    </span>
                    <span>-Rp{{ number_format($discount, 2, ',', '.') }}</span>
                </div>
                @endif
                @if($shipping > 0)
                <div class="flex justify-between font-body-md text-body-md">
                    <span class="text-secondary">Shipping</span>
                    <span class="text-on-surface">Rp{{ number_format($shipping, 2, ',', '.') }}</span>
                </div>
                @endif
                @if($transactionFee > 0)
                <div class="flex justify-between font-body-md text-body-md">
                    <span class="text-secondary">Transaction Fee</span>
                    <span class="text-on-surface">Rp{{ number_format($transactionFee, 2, ',', '.') }}</span>
                </div>
                @endif
                @if($tax > 0)
                <div class="flex justify-between font-body-md text-body-md">
                    <span class="text-secondary">Tax</span>
                    <span class="text-on-surface">Rp{{ number_format($tax, 2, ',', '.') }}</span>
                </div>
                @endif
                <div class="pt-3 border-t border-outline-variant flex justify-between items-center">
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
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-label-sm font-label-sm {{ $order->paymentStatusBadgeClass }}">
                        {{ $order->paymentStatusLabel() }}
                    </span>
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

    @if(str_contains(strtolower($order->payment_method), 'transfer_manual') || str_contains(strtolower($order->payment_method), 'manual'))
        @php
            $paymentProof = $order->meta['payment_proof'] ?? null;
        @endphp
        <div class="bg-white rounded-xl shadow-sm p-6 mb-8 border border-primary/20 bg-primary/5">
            <h3 class="font-headline-md text-headline-md text-on-surface mb-4 flex items-center gap-2">
                <span class="material-symbols-outlined text-primary">gavel</span> Manual Payment Reconciliation
            </h3>
            
            <div class="grid grid-cols-1 md:grid-cols-12 gap-6">
                <!-- Payment Proof Column -->
                <div class="md:col-span-5 flex flex-col justify-center items-center bg-surface-container rounded-xl p-4 border border-outline-variant/30 min-h-[250px]">
                    @if($paymentProof)
                        <p class="font-label-sm text-label-sm text-secondary mb-2">BUKTI TRANSFER YANG DIUNGGAH</p>
                        <a href="{{ env('FRONTEND_URL', 'http://127.0.0.1:81') }}/storage/{{ $paymentProof }}" target="_blank" class="group relative block overflow-hidden rounded-lg border border-outline-variant max-h-[300px]">
                            <img src="{{ env('FRONTEND_URL', 'http://127.0.0.1:81') }}/storage/{{ $paymentProof }}" alt="Bukti Transfer" class="object-contain max-h-[300px] hover:scale-105 transition-all duration-300">
                            <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center text-white font-label-md">
                                <span class="material-symbols-outlined mr-1">zoom_in</span> Buka Ukuran Penuh
                            </div>
                        </a>
                    @else
                        <div class="text-center py-8">
                            <span class="material-symbols-outlined text-outline-variant text-[48px] mb-2">no_photography</span>
                            <p class="font-body-md text-body-md text-on-surface-variant font-medium">Belum ada bukti transfer yang diunggah oleh pelanggan.</p>
                        </div>
                    @endif
                </div>
                
                <!-- Action / Verification Column -->
                <div class="md:col-span-7 flex flex-col justify-between">
                    <div>
                        @if((int)$order->payment_status === \App\Models\Order\Order::PAYMENT_PAID)
                            <div class="bg-green-50 border border-green-200 rounded-xl p-5 text-green-800 space-y-3">
                                <div class="flex items-center gap-2">
                                    <span class="material-symbols-outlined text-[24px] text-green-600">verified</span>
                                    <h4 class="font-headline-md text-headline-md font-bold text-green-950">Pembayaran Terverifikasi</h4>
                                </div>
                                <p class="font-body-md text-body-md text-green-700">
                                    Pembayaran untuk order ini telah berhasil diverifikasi lunas (Paid) dan direkonsiliasi.
                                </p>
                                <div class="bg-white rounded-lg p-3 text-sm text-on-surface border border-green-100">
                                    <div class="grid grid-cols-2 gap-2 mb-2 font-medium text-xs text-secondary border-b pb-1">
                                        <div>VERIFIKATOR</div>
                                        <div>TANGGAL VERIFIKASI</div>
                                    </div>
                                    <div class="grid grid-cols-2 gap-2 mb-3 text-xs text-on-surface-variant font-mono">
                                        <div>
                                            @php
                                                $reconciledBy = \DB::table('users')->find($order->meta['reconciled_by'] ?? null);
                                            @endphp
                                            {{ $reconciledBy->name ?? 'Admin System' }}
                                        </div>
                                        <div>{{ !empty($order->meta['reconciled_at']) ? Carbon\Carbon::parse($order->meta['reconciled_at'])->format('d M Y H:i') : '-' }}</div>
                                    </div>
                                    <div class="text-xs">
                                        <div class="font-medium text-secondary mb-1">CATATAN REKONSILISASI</div>
                                        <div class="italic text-on-surface-variant">"{{ $order->meta['reconciliation_notes'] ?? 'Tidak ada catatan.' }}"</div>
                                    </div>
                                </div>
                            </div>
                        @else
                            <p class="font-body-md text-body-md text-on-surface-variant mb-4">
                                Periksa bukti transfer di sebelah kiri. Jika nominal transfer dan nama rekening pengirim sudah sesuai dengan total tagihan order ini, silakan lakukan verifikasi pembayaran di bawah.
                            </p>
                            
                            <form method="POST" action="{{ route('orders.verify-payment', $order->id) }}" class="space-y-4">
                                @csrf
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div class="space-y-1.5">
                                        <label class="block text-label-sm font-medium text-on-surface-variant">Update Status Pembayaran</label>
                                        <select name="payment_status" class="w-full px-3 py-2 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary/20 focus:outline-none bg-white">
                                            <option value="0" {{ $order->payment_status == 0 ? 'selected' : '' }}>Unpaid (Belum Bayar)</option>
                                            <option value="1" {{ $order->payment_status == 1 ? 'selected' : '' }}>Paid (Lunas)</option>
                                            <option value="2" {{ $order->payment_status == 2 ? 'selected' : '' }}>Failed (Gagal / Ditolak)</option>
                                        </select>
                                    </div>
                                    <div class="space-y-1.5">
                                        <label class="block text-label-sm font-medium text-on-surface-variant">Update Status Order</label>
                                        <select name="status" class="w-full px-3 py-2 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary/20 focus:outline-none bg-white">
                                            <option value="1" {{ $order->status == 1 ? 'selected' : '' }}>Pending Approval</option>
                                            <option value="2" {{ $order->status == 2 ? 'selected' : '' }}>Confirmed (Diterima)</option>
                                            <option value="3" {{ $order->status == 3 ? 'selected' : '' }}>Processing (Diproses)</option>
                                            <option value="6" {{ $order->status == 6 ? 'selected' : '' }}>Cancelled (Dibatalkan)</option>
                                        </select>
                                    </div>
                                </div>
                                
                                <div class="space-y-1.5">
                                    <label class="block text-label-sm font-medium text-on-surface-variant">Catatan Rekonsiliasi (Internal)</label>
                                    <textarea name="reconciliation_notes" rows="2" class="w-full px-3 py-2 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary/20 focus:outline-none" placeholder="e.g., Transfer Mandiri a/n Budi sesuai nominal. Pembayaran disetujui.">{{ $order->meta['reconciliation_notes'] ?? '' }}</textarea>
                                </div>
                                
                                <div class="flex justify-end pt-2">
                                    <button type="submit" class="px-6 py-2.5 bg-primary text-white rounded-lg font-label-md hover:opacity-90 transition-all shadow-sm flex items-center gap-1.5">
                                        <span class="material-symbols-outlined">verified</span> Simpan Verifikasi & Update Status
                                    </button>
                                </div>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endif

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
                        <td class="px-6 py-4 font-body-md text-body-md text-on-surface text-right">Rp{{ number_format($item->unit_price, 2, ',', '.') }}</td>
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
