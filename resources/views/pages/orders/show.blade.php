@extends('layouts.app')

@section('title', 'Order #' . $order->order_number)

@section('content')
@php
    $resiModalData = $order->resi_modal_data;
    $shippingMeta = $order->meta['shipping_address'] ?? [];
    $customerPhone = $order->customer->phone ?? ($shippingMeta['phone'] ?? null);
    $cleanPhone = $customerPhone ? preg_replace('/[^0-9]/', '', $customerPhone) : '';
    if ($cleanPhone && str_starts_with($cleanPhone, '0')) {
        $waPhone = '62' . substr($cleanPhone, 1);
    } else {
        $waPhone = $cleanPhone;
    }
@endphp

<div class="space-y-4 pb-10">
    <!-- Action Buttons (Diatas Sub Menu) -->
    <div class="flex flex-wrap items-center justify-end gap-2">
        @if($order->resi || $order->delivery_status)
            <button type="button" onclick='openResiModal(@json($resiModalData), "tracking")' class="inline-flex items-center gap-1.5 px-3 py-2 bg-emerald-700 hover:bg-emerald-800 text-white rounded-lg text-xs font-semibold shadow-xs transition-colors">
                <span class="material-symbols-outlined text-[16px]">radar</span>
                <span>Lacak Pengiriman</span>
            </button>
        @endif

        @if(!empty($resiModalData['has_biteship_resi']))
            <button type="button" disabled title="Pesanan sudah diproses di Biteship (Resi: {{ $order->resi }}). Tombol dinonaktifkan untuk mencegah pemesanan ganda." class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-slate-100 text-slate-400 dark:bg-slate-800 dark:text-slate-500 rounded-lg text-xs font-bold border border-slate-200 dark:border-slate-700 cursor-not-allowed">
                <span class="material-symbols-outlined text-[16px] text-slate-400">check_circle</span>
                <span>Biteship Aktif</span>
            </button>
        @else
            <button type="button" onclick='openResiModal(@json($resiModalData), "biteship")' class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-amber-600 hover:bg-amber-700 text-white rounded-lg text-xs font-bold shadow-xs transition-colors">
                <span class="material-symbols-outlined text-[16px]">rocket_launch</span>
                <span>Hit Biteship</span>
            </button>
        @endif

        @if($order->status >= \App\Models\Order\Order::STATUS_SHIPPED)
            <button type="button" disabled title="Pesanan sudah berstatus {{ $order->statusLabel() }}. Nomor resi terkunci dan tidak dapat diubah lagi." class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-slate-100 text-slate-400 dark:bg-slate-800 dark:text-slate-500 rounded-lg text-xs font-bold border border-slate-200 dark:border-slate-700 cursor-not-allowed">
                <span class="material-symbols-outlined text-[16px]">lock</span>
                <span>Resi Terkunci</span>
            </button>
        @else
            <button type="button" onclick='openResiModal(@json($resiModalData), "manual")' class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-primary hover:opacity-90 text-white rounded-lg text-xs font-bold shadow-xs transition-opacity">
                <span class="material-symbols-outlined text-[16px]">edit_document</span>
                <span id="show-header-resi-text">{{ $order->resi ? 'Ubah Resi' : 'Input Resi' }}</span>
            </button>
        @endif

        <a href="{{ route('orders.index') }}" class="inline-flex items-center gap-1 px-3 py-2 border border-outline-variant text-on-surface-variant hover:bg-surface-container rounded-lg text-xs font-medium transition-colors">
            <span class="material-symbols-outlined text-[16px]">arrow_back</span>
            <span>Kembali</span>
        </a>
    </div>

    @include('layouts.partials.sales-submenu')

    <!-- Main Grid Layout -->
    <div class="grid grid-cols-1 xl:grid-cols-12 gap-5 items-start">

        <!-- LEFT SIDEBAR: Order Status, Courier/Resi, Customer & Payment (4 Cols) -->
        <div class="xl:col-span-4 space-y-4">

            <!-- Card: Status & Data Order -->
            <div class="bg-white rounded-xl border border-outline-variant/60 shadow-xs p-4 space-y-3">
                <div class="flex items-center justify-between border-b border-outline-variant/40 pb-2.5">
                    <span class="font-bold text-xs text-on-surface uppercase tracking-wider flex items-center gap-1.5">
                        <span class="material-symbols-outlined text-[16px] text-primary">info</span>
                        Status & Data Order
                    </span>
                    <span id="show-order-status-badge" class="px-2.5 py-0.5 rounded text-[11px] font-bold {{ $order->statusBadgeClass }}">
                        {{ $order->statusLabel() }}
                    </span>
                </div>

                <!-- Nomor Order -->
                <div class="p-2.5 bg-surface-container-low rounded-lg border border-outline-variant/30 flex items-center justify-between">
                    <div>
                        <span class="text-[10px] text-on-surface-variant font-semibold block uppercase">Nomor Pesanan</span>
                        <div class="flex items-center gap-2 mt-0.5">
                            <span class="font-mono font-bold text-sm text-primary">#{{ $order->order_number }}</span>
                            <span id="order-copy-label" class="hidden text-[10px] font-semibold text-emerald-700 bg-emerald-50 border border-emerald-200 px-1.5 py-0.5 rounded transition-all items-center gap-0.5">
                                <span class="material-symbols-outlined text-[12px]">check</span>
                                Disalin
                            </span>
                        </div>
                    </div>
                    <button type="button" id="order-copy-btn" onclick="copyOrderNumber('{{ $order->order_number }}')" class="p-1.5 text-on-surface-variant hover:text-primary rounded-md hover:bg-white transition-colors" title="Salin Nomor Order">
                        <span id="order-copy-icon" class="material-symbols-outlined text-[16px]">content_copy</span>
                    </button>
                </div>

                <div class="grid grid-cols-2 gap-2 text-xs">
                    <div class="p-2 bg-surface-container-low rounded-lg border border-outline-variant/30">
                        <span class="text-[10px] text-on-surface-variant block">Waktu Pesanan</span>
                        <span class="font-semibold text-on-surface text-[11px]">{{ $order->created_at->format('d/m/Y H:i') }} WIB</span>
                    </div>
                    <div class="p-2 bg-surface-container-low rounded-lg border border-outline-variant/30">
                        <span class="text-[10px] text-on-surface-variant block">Status Bayar</span>
                        <span class="font-bold text-[11px] {{ $order->payment_status == 1 ? 'text-emerald-700' : 'text-amber-700' }}">
                            {{ $order->paymentStatusLabel() }}
                        </span>
                    </div>
                </div>

                @php
                    $isStatusLocked = in_array((int)$order->status, [\App\Models\Order\Order::STATUS_SHIPPED, \App\Models\Order\Order::STATUS_DELIVERED]);
                @endphp

                <!-- Quick Status Change Form -->
                <form id="quickStatusForm" onsubmit="handleQuickStatusSubmit(event)" class="pt-2 border-t border-outline-variant/40 space-y-2">
                    <div class="flex items-center justify-between">
                        <label for="quickStatusSelect" class="block text-[11px] font-semibold text-on-surface">
                            Ubah Status Pesanan:
                        </label>
                        @if($isStatusLocked)
                            <span class="text-[10px] font-semibold text-amber-700 bg-amber-50 border border-amber-200 px-1.5 py-0.5 rounded flex items-center gap-1">
                                <span class="material-symbols-outlined text-[12px]">lock</span>
                                Terkunci
                            </span>
                        @endif
                    </div>
                    <div class="flex gap-2">
                        <select id="quickStatusSelect" {{ $isStatusLocked ? 'disabled' : '' }} class="flex-1 px-2.5 py-1.5 border border-outline-variant rounded-lg text-xs {{ $isStatusLocked ? 'bg-surface-container-low text-on-surface-variant cursor-not-allowed opacity-75' : 'bg-white focus:ring-1 focus:ring-primary focus:outline-none' }}">
                            <option value="0" {{ $order->status == 0 ? 'selected' : '' }}>Draft</option>
                            <option value="1" {{ $order->status == 1 ? 'selected' : '' }}>Ordered (Menunggu)</option>
                            <option value="2" {{ $order->status == 2 ? 'selected' : '' }}>Confirmed (Dikonfirmasi)</option>
                            <option value="3" {{ $order->status == 3 ? 'selected' : '' }}>Processing (Diproses)</option>
                            <option value="4" {{ $order->status == 4 ? 'selected' : '' }}>Shipped (Dikirim)</option>
                            <option value="5" {{ $order->status == 5 ? 'selected' : '' }}>Delivered (Selesai)</option>
                            <option value="6" {{ $order->status == 6 ? 'selected' : '' }}>Cancelled (Batal)</option>
                            <option value="7" {{ $order->status == 7 ? 'selected' : '' }}>Returned (Retur)</option>
                        </select>
                        <button type="submit" id="quickStatusBtn" {{ $isStatusLocked ? 'disabled' : '' }} class="px-3 py-1.5 {{ $isStatusLocked ? 'bg-surface-container text-on-surface-variant/40 border-outline-variant/40 cursor-not-allowed' : 'bg-surface-container-high hover:bg-surface-container-highest border-outline-variant text-on-surface' }} border rounded-lg text-xs font-bold transition-colors shrink-0 flex items-center gap-1">
                            <span class="material-symbols-outlined text-[14px]">save</span>
                            <span>Ubah</span>
                        </button>
                    </div>
                    @if($isStatusLocked)
                        <p class="text-[10px] text-on-surface-variant italic">
                            Status {{ $order->status == 4 ? 'Shipped (Dikirim)' : 'Delivered (Selesai)' }} sudah terkunci dan tidak dapat diubah lagi.
                        </p>
                    @endif
                </form>

                @if($order->notes)
                    <div class="p-2.5 bg-amber-50/70 border border-amber-200 rounded-lg text-xs text-amber-900">
                        <span class="text-[10px] font-bold uppercase block text-amber-800">Catatan Order:</span>
                        <p class="mt-0.5 italic">{{ $order->notes }}</p>
                    </div>
                @endif
            </div>

            <!-- Card: Kurir & Nomor Resi -->
            <div class="bg-white rounded-xl border border-outline-variant/60 shadow-xs p-4 space-y-3">
                <div class="flex items-center justify-between border-b border-outline-variant/40 pb-2.5">
                    <span class="font-bold text-xs text-on-surface uppercase tracking-wider flex items-center gap-1.5">
                        <span class="material-symbols-outlined text-[16px] text-primary">local_shipping</span>
                        Pengiriman & Resi
                    </span>
                    @if($order->resi)
                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800">
                            Resi Ada
                        </span>
                    @else
                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-800">
                            Belum Ada Resi
                        </span>
                    @endif
                </div>

                <div class="space-y-2 text-xs">
                    <div>
                        <span class="text-[10px] text-on-surface-variant font-semibold block mb-0.5">Kurir Pesanan</span>
                        <div class="flex items-center justify-between p-2 bg-surface-container-low rounded-lg border border-outline-variant/40">
                            <span class="font-bold text-primary" id="show-order-courier-name">{{ $order->courier_name ?? 'Kurir Belum Diatur' }}</span>
                            @if(!empty($order->meta['fulfillment_type']))
                                <span class="text-[10px] px-1.5 py-0.5 rounded font-bold uppercase {{ $order->meta['fulfillment_type'] === 'biteship' ? 'bg-purple-100 text-purple-800' : 'bg-blue-100 text-blue-800' }}">
                                    {{ $order->meta['fulfillment_type'] }}
                                </span>
                            @endif
                        </div>
                    </div>

                    <div>
                        <span class="text-[10px] text-on-surface-variant font-semibold block mb-0.5">Nomor Resi (AWB)</span>
                        <div class="flex items-center justify-between p-2 bg-surface-container-low rounded-lg border border-outline-variant/40">
                            <div class="flex items-center gap-2">
                                <span class="font-mono font-bold text-xs text-on-surface select-all" id="show-order-resi-code">
                                    {{ $order->resi ?? 'Belum ada nomor resi' }}
                                </span>
                                <span id="resi-copy-label" class="hidden text-[10px] font-semibold text-emerald-700 bg-emerald-50 border border-emerald-200 px-1.5 py-0.5 rounded transition-all items-center gap-0.5">
                                    <span class="material-symbols-outlined text-[12px]">check</span>
                                    Disalin
                                </span>
                            </div>
                            @if($order->resi)
                                <button type="button" id="resi-copy-btn" onclick="copyResiCodeText('{{ $order->resi }}')" class="p-1 hover:text-primary rounded text-on-surface-variant transition-colors" title="Salin Resi">
                                    <span id="resi-copy-icon" class="material-symbols-outlined text-[15px]">content_copy</span>
                                </button>
                            @endif
                        </div>
                    </div>

                    <div>
                        <span class="text-[10px] text-on-surface-variant font-semibold block mb-0.5">Status Pengiriman</span>
                        <div class="p-2 bg-surface-container-low rounded-lg border border-outline-variant/40 flex items-center justify-between">
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-bold border {{ $order->delivery_status_badge_class }}">
                                <span class="material-symbols-outlined text-[14px]">local_shipping</span>
                                <span id="show-order-delivery-status">{{ $order->delivery_status_label }}</span>
                            </span>
                            @if($order->latest_delivery_log?->created_at)
                                <span class="text-[10px] text-on-surface-variant font-mono">{{ $order->latest_delivery_log->created_at->format('d M H:i') }}</span>
                            @endif
                        </div>
                        @if($order->latest_delivery_log?->note)
                            <p class="text-[11px] text-on-surface-variant mt-1 italic pl-1 leading-tight">{{ $order->latest_delivery_log->note }}</p>
                        @endif
                    </div>

                    <!-- Action Buttons -->
                    <div class="grid grid-cols-2 gap-2 pt-1">
                        @if($order->status >= \App\Models\Order\Order::STATUS_SHIPPED)
                            <button type="button" disabled title="Nomor resi terkunci karena pesanan sudah {{ $order->statusLabel() }}" class="py-2 px-2 bg-slate-100 dark:bg-slate-800 text-slate-400 dark:text-slate-500 border border-slate-200 dark:border-slate-700 rounded-lg text-xs font-bold cursor-not-allowed flex items-center justify-center gap-1">
                                <span class="material-symbols-outlined text-[15px]">lock</span>
                                <span>Resi Terkunci</span>
                            </button>
                        @else
                            <button type="button" onclick='openResiModal(@json($resiModalData), "manual")' class="py-2 px-2 bg-surface-container-high hover:bg-surface-container-highest border border-outline-variant text-on-surface rounded-lg text-xs font-semibold transition-colors flex items-center justify-center gap-1">
                                <span class="material-symbols-outlined text-[15px] text-primary">edit_document</span>
                                <span>{{ $order->resi ? 'Ubah Resi' : 'Input Resi' }}</span>
                            </button>
                        @endif
                        @if(!empty($resiModalData['has_biteship_resi']))
                            <button type="button" disabled title="Pesanan sudah diproses di Biteship" class="py-2 px-2 bg-slate-100 dark:bg-slate-800 text-slate-400 dark:text-slate-500 rounded-lg text-xs font-bold flex items-center justify-center gap-1 border border-slate-200 dark:border-slate-700 cursor-not-allowed">
                                <span class="material-symbols-outlined text-[15px] text-slate-400">check_circle</span>
                                <span>Biteship Aktif</span>
                            </button>
                        @else
                            <button type="button" onclick='openResiModal(@json($resiModalData), "biteship")' class="py-2 px-2 bg-amber-600 hover:bg-amber-700 text-white rounded-lg text-xs font-bold transition-colors flex items-center justify-center gap-1 shadow-xs">
                                <span class="material-symbols-outlined text-[15px]">rocket_launch</span>
                                <span>Hit Biteship</span>
                            </button>
                        @endif
                    </div>

                    @if($order->resi || $order->delivery_status)
                        <button type="button" onclick='openResiModal(@json($resiModalData), "tracking")' class="w-full py-2 bg-emerald-50 hover:bg-emerald-100 border border-emerald-200 text-emerald-800 rounded-lg text-xs font-bold transition-colors flex items-center justify-center gap-1.5 shadow-xs">
                            <span class="material-symbols-outlined text-[16px] text-emerald-600">radar</span>
                            <span>Lacak Pesanan (Log Pengiriman)</span>
                        </button>
                    @endif

                    @if($order->delivery)
                        <a href="{{ route('delivery.show', $order->delivery->id) }}" class="w-full py-2 bg-purple-50 hover:bg-purple-100 border border-purple-200 text-purple-800 rounded-lg text-xs font-bold transition-colors flex items-center justify-center gap-1.5 shadow-xs">
                            <span class="material-symbols-outlined text-[16px] text-purple-600">terminal</span>
                            <span>Delivery Order & Payload Biteship</span>
                        </a>
                    @endif
                </div>
            </div>

            <!-- Card: Alamat Tujuan & Kontak Pelanggan -->
            <div class="bg-white rounded-xl border border-outline-variant/60 shadow-xs p-4 space-y-3">
                <div class="flex items-center justify-between border-b border-outline-variant/40 pb-2.5">
                    <span class="font-bold text-xs text-on-surface uppercase tracking-wider flex items-center gap-1.5">
                        <span class="material-symbols-outlined text-[16px] text-primary">location_on</span>
                        Alamat Pengiriman
                    </span>
                    @if(!empty($shippingMeta['postal_code']))
                        <span class="text-[10px] font-mono font-bold px-1.5 py-0.5 rounded bg-surface-container text-on-surface">
                            {{ $shippingMeta['postal_code'] }}
                        </span>
                    @endif
                </div>

                <div class="space-y-2 text-xs">
                    <div>
                        <span class="text-[10px] text-on-surface-variant block">Nama Penerima</span>
                        <div class="font-bold text-on-surface text-sm">
                            {{ $shippingMeta['recipient_name'] ?? ($order->customer->name ?? 'Pelanggan') }}
                        </div>
                    </div>

                    <div>
                        <span class="text-[10px] text-on-surface-variant block">No. Telepon / HP</span>
                        <div class="flex items-center justify-between mt-0.5">
                            <span class="font-mono font-medium text-on-surface">{{ $customerPhone ?? '-' }}</span>
                            @if($waPhone)
                                <a href="https://wa.me/{{ $waPhone }}" target="_blank" class="inline-flex items-center gap-1 px-2 py-0.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded text-[11px] font-semibold transition-colors">
                                    <span class="material-symbols-outlined text-[13px]">chat</span>
                                    <span>WhatsApp</span>
                                </a>
                            @endif
                        </div>
                    </div>

                    <div class="pt-1">
                        <span class="text-[10px] text-on-surface-variant block">Alamat Lengkap</span>
                        <p class="text-on-surface text-xs leading-relaxed mt-0.5 bg-surface-container-low p-2 rounded-lg border border-outline-variant/30">
                            @if(!empty($shippingMeta['address']))
                                {{ $shippingMeta['address'] }}
                                @if(!empty($shippingMeta['sub_district'])), {{ $shippingMeta['sub_district'] }}@endif
                                @if(!empty($shippingMeta['city'])), {{ $shippingMeta['city'] }}@endif
                                @if(!empty($shippingMeta['province'])), {{ $shippingMeta['province'] }}@endif
                            @elseif($order->customer && method_exists($order->customer, 'addresses') && $order->customer->addresses->isNotEmpty())
                                {{ $order->customer->addresses->first()->address }}
                            @else
                                Alamat belum tercatat lengkap.
                            @endif
                        </p>
                    </div>
                </div>
            </div>

            <!-- Card: Pembayaran & Bukti Transfer -->
            <div class="bg-white rounded-xl border border-outline-variant/60 shadow-xs p-4 space-y-3">
                <div class="flex items-center justify-between border-b border-outline-variant/40 pb-2.5">
                    <span class="font-bold text-xs text-on-surface uppercase tracking-wider flex items-center gap-1.5">
                        <span class="material-symbols-outlined text-[16px] text-primary">payments</span>
                        Pembayaran
                    </span>
                    <span class="px-2 py-0.5 rounded text-[10px] font-bold {{ $order->paymentStatusBadgeClass }}">
                        {{ $order->paymentStatusLabel() }}
                    </span>
                </div>

                <div class="space-y-2 text-xs">
                    <div class="flex justify-between py-1 border-b border-outline-variant/20">
                        <span class="text-on-surface-variant">Metode</span>
                        <span class="font-bold text-on-surface">{{ ucfirst(str_replace('_', ' ', $order->payment_method ?? 'Transfer')) }}</span>
                    </div>

                    @if($order->invoice)
                        <div class="flex justify-between py-1 border-b border-outline-variant/20">
                            <span class="text-on-surface-variant">No. Faktur</span>
                            <span class="font-mono font-bold text-primary">{{ $order->invoice->invoice_number }}</span>
                        </div>
                    @endif

                    @if(!empty($order->meta['payment_proof']))
                        <div class="pt-2 space-y-1.5">
                            <span class="text-[10px] font-bold uppercase text-on-surface-variant block">Bukti Transfer Pelanggan</span>
                            <a href="{{ media_url($order->meta['payment_proof']) }}" target="_blank" class="block w-full py-1.5 text-center text-xs font-bold text-primary bg-primary/10 hover:bg-primary/20 rounded-lg transition-colors">
                                Lihat Bukti Transfer
                            </a>
                        </div>
                    @endif

                    @if(str_contains(strtolower((string)($order->payment_method ?? '')), 'transfer_manual') || str_contains(strtolower((string)($order->payment_method ?? '')), 'manual'))
                        <form method="POST" action="{{ route('orders.verify-payment', $order->id) }}" class="pt-2 border-t border-outline-variant/40 space-y-2">
                            @csrf
                            <input type="hidden" name="status" value="2">
                            <input type="hidden" name="payment_status" value="1">
                            <button type="submit" class="w-full py-1.5 bg-emerald-700 hover:bg-emerald-800 text-white rounded-lg text-xs font-bold transition-colors">
                                Setujui Pembayaran (Mark Paid)
                            </button>
                        </form>
                    @endif
                </div>
            </div>

        </div>

        <!-- RIGHT MAIN AREA: Products Table & Summary (8 Cols) -->
        <div class="xl:col-span-8 space-y-4">

            <!-- Card: Item Produk Pesanan -->
            <div class="bg-white rounded-xl border border-outline-variant/60 shadow-xs overflow-hidden">
                <div class="p-3.5 md:p-4 border-b border-outline-variant/40 flex items-center justify-between bg-surface-container-lowest">
                    <span class="font-bold text-xs text-on-surface uppercase tracking-wider flex items-center gap-1.5">
                        <span class="material-symbols-outlined text-[17px] text-primary">inventory_2</span>
                        Rincian Item Produk ({{ $order->items->count() }} item)
                    </span>
                    <span class="text-xs text-on-surface-variant font-medium">
                        Total: <strong>{{ $order->items->sum('quantity') }} Pcs</strong>
                    </span>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-xs">
                        <thead>
                            <tr class="bg-surface-container-low/70 border-b border-outline-variant/40 text-[11px] font-bold text-on-surface-variant uppercase tracking-wider">
                                <th class="py-2.5 px-3 w-10 text-center">No</th>
                                <th class="py-2.5 px-3 min-w-[200px]">Produk & Varian</th>
                                <th class="py-2.5 px-3 text-right whitespace-nowrap">Harga Satuan</th>
                                <th class="py-2.5 px-3 text-center whitespace-nowrap">Qty</th>
                                <th class="py-2.5 px-3 text-right whitespace-nowrap">Diskon</th>
                                <th class="py-2.5 px-3 text-right whitespace-nowrap">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-outline-variant/20">
                            @php
                                $originalSubtotal = 0;
                                $runningNo = 1;
                            @endphp

                            @foreach($order->items as $index => $item)
                                @php
                                    $meta = is_string($item->meta) ? json_decode($item->meta, true) : ($item->meta ?? []);
                                    $isBundleItem = $meta['is_bundle_item'] ?? false;

                                    $price = (float) ($item->unit_price ?? $meta['original_price'] ?? 0);
                                    $qty = max(1, (int) $item->quantity);
                                    $sub = $price * $qty;
                                    $originalSubtotal += $sub;

                                    $disc_pct = (float) ($item->discount_percent ?? ($meta['discount_percent'] ?? 0));
                                    $disc_nom = (float) ($item->discount_nominal ?? ($meta['discount_nominal'] ?? 0));
                                    if ($disc_pct > 0 && $disc_nom <= 0) {
                                        $disc_nom = ($sub * $disc_pct) / 100;
                                    }
                                    if ($disc_nom > 0 && $disc_pct <= 0 && $sub > 0) {
                                        $disc_pct = round(($disc_nom / $sub) * 100, 1);
                                    }

                                    $product = $item->product;
                                    $variant = $item->variant;
                                    $imgUrl = $product?->thumbnail_url ?? null;
                                @endphp

                                <tr class="hover:bg-surface-container-low/40 transition-colors {{ $isBundleItem ? 'bg-surface-container-lowest/50' : '' }}">
                                    <!-- No -->
                                    <td class="py-3 px-3 text-center text-on-surface-variant font-medium align-top">
                                        @if(!$isBundleItem)
                                            {{ $runningNo++ }}
                                        @else
                                            <span class="material-symbols-outlined text-[13px] text-outline">subdirectory_arrow_right</span>
                                        @endif
                                    </td>

                                    <!-- Product Info & Thumb -->
                                    <td class="py-3 px-3 align-top">
                                        <div class="flex items-start gap-2.5">
                                            @if($imgUrl)
                                                <img src="{{ $imgUrl }}" alt="{{ $item->name }}" class="w-10 h-10 object-cover rounded-lg border border-outline-variant/40 shrink-0 bg-surface-container" onerror="this.style.display='none'">
                                            @endif
                                            <div class="space-y-0.5">
                                                <div class="font-bold text-on-surface leading-tight text-xs">
                                                    @if($isBundleItem)
                                                        <span class="text-[10px] text-primary font-semibold">[Bundling]</span>
                                                    @endif
                                                    {{ $item->name }}
                                                </div>
                                                <div class="flex flex-wrap items-center gap-1 text-[11px] text-on-surface-variant">
                                                    @if($variant && !empty($variant->variant_name))
                                                        <span class="px-1.5 py-0.2 bg-surface-container rounded text-on-surface font-medium">
                                                            {{ $variant->variant_name }}
                                                        </span>
                                                    @endif
                                                    @if($variant?->sku || $product?->code)
                                                        <span class="font-mono text-[10px] opacity-80">
                                                            SKU: {{ $variant->sku ?? $product->code }}
                                                        </span>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </td>

                                    <!-- Unit Price (Harga Asli) -->
                                    <td class="py-3 px-3 text-right font-medium text-on-surface align-top whitespace-nowrap">
                                        <div>Rp {{ number_format($price, 0, ',', '.') }}</div>
                                        @if($qty > 1)
                                            <div class="text-[10px] text-on-surface-variant font-normal mt-0.5">Total Asli: Rp {{ number_format($sub, 0, ',', '.') }}</div>
                                        @endif
                                    </td>

                                    <!-- Qty -->
                                    <td class="py-3 px-3 text-center font-bold text-primary align-top whitespace-nowrap">
                                        {{ $item->quantity }}
                                    </td>

                                    <!-- Discount -->
                                    <td class="py-3 px-3 text-right text-danger align-top whitespace-nowrap">
                                        @if($disc_nom > 0)
                                            <span class="font-bold">-Rp {{ number_format($disc_nom, 0, ',', '.') }}</span>
                                            @if($disc_pct > 0)
                                                <span class="text-[10px] block font-semibold text-danger/80">({{ (float) $disc_pct }}%)</span>
                                            @endif
                                        @else
                                            <span class="text-on-surface-variant opacity-30">-</span>
                                        @endif
                                    </td>

                                    <!-- Subtotal -->
                                    <td class="py-3 px-3 text-right font-bold text-on-surface align-top whitespace-nowrap">
                                        Rp {{ number_format($item->total, 0, ',', '.') }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Financial Breakdown Panel -->
                <div class="p-4 bg-surface-container-lowest border-t border-outline-variant/40 flex justify-end">
                    <div class="w-full max-w-sm space-y-2 text-xs">
                        <div class="flex justify-between py-0.5 text-on-surface-variant">
                            <span>Subtotal Produk</span>
                            <span class="font-semibold text-on-surface">Rp {{ number_format($originalSubtotal, 0, ',', '.') }}</span>
                        </div>

                        @php
                            $pureDiscount = (float) $order->pure_discount;
                            $voucherNominal = (float) $order->effective_voucher_nominal;
                            $voucher = $order->voucher;
                        @endphp

                        @if($pureDiscount > 0)
                            <div class="flex justify-between py-0.5 text-danger font-medium">
                                <span>Diskon</span>
                                <span>-Rp {{ number_format($pureDiscount, 0, ',', '.') }}</span>
                            </div>
                        @endif

                        @if($voucherNominal > 0 || $voucher)
                            <div class="flex justify-between py-0.5 text-danger font-medium">
                                <span>Voucher {{ $voucher ? '(' . $voucher->code . ')' : '' }}</span>
                                <span>
                                    -Rp {{ number_format($voucherNominal, 0, ',', '.') }}
                                    @if($voucher && (int) $voucher->type === 1 && $voucher->value > 0)
                                        <span class="text-[10px] font-normal opacity-75">({{ $voucher->value }}%)</span>
                                    @endif
                                </span>
                            </div>
                        @endif

                        @if($order->shipping_cost > 0)
                            <div class="flex justify-between py-0.5 text-on-surface-variant">
                                <span>Ongkos Kirim</span>
                                <span class="font-semibold text-on-surface">Rp {{ number_format($order->shipping_cost, 0, ',', '.') }}</span>
                            </div>
                        @endif

                        @if($order->shipping_cost_subsidy > 0)
                            <div class="flex justify-between py-0.5 text-emerald-700 font-medium">
                                <span>Subsidi Ongkir</span>
                                <span>-Rp {{ number_format($order->shipping_cost_subsidy, 0, ',', '.') }}</span>
                            </div>
                        @endif

                        @if($order->transaction_fee > 0)
                            <div class="flex justify-between py-0.5 text-on-surface-variant">
                                <span>Biaya Layanan</span>
                                <span class="font-semibold text-on-surface">Rp {{ number_format($order->transaction_fee, 0, ',', '.') }}</span>
                            </div>
                        @endif

                        <div class="pt-2 border-t border-outline-variant flex justify-between items-center">
                            <span class="font-bold text-sm text-on-surface">Total Tagihan</span>
                            <span class="text-base font-bold text-primary font-mono">
                                Rp {{ number_format($order->total, 0, ',', '.') }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

        </div>

    </div>
</div>

@include('pages.orders.partials.resi-modal')

<script>
let orderCopyTimeout = null;
function copyOrderNumber(orderNumber) {
    if (!orderNumber) return;
    const doShowLabel = () => {
        const label = document.getElementById('order-copy-label');
        const icon = document.getElementById('order-copy-icon');
        if (label) {
            label.classList.remove('hidden');
            label.classList.add('inline-flex');
        }
        if (icon) {
            icon.textContent = 'check';
            icon.classList.add('text-emerald-600');
        }
        if (orderCopyTimeout) clearTimeout(orderCopyTimeout);
        orderCopyTimeout = setTimeout(() => {
            if (label) {
                label.classList.add('hidden');
                label.classList.remove('inline-flex');
            }
            if (icon) {
                icon.textContent = 'content_copy';
                icon.classList.remove('text-emerald-600');
            }
        }, 2000);
    };

    if (navigator.clipboard && window.isSecureContext) {
        navigator.clipboard.writeText(orderNumber).then(doShowLabel).catch(() => {
            fallbackCopyText(orderNumber, doShowLabel);
        });
    } else {
        fallbackCopyText(orderNumber, doShowLabel);
    }
}

let resiCopyTimeout = null;
function copyResiCodeText(resi) {
    if (!resi) return;
    const doShowLabel = () => {
        const label = document.getElementById('resi-copy-label');
        const icon = document.getElementById('resi-copy-icon');
        if (label) {
            label.classList.remove('hidden');
            label.classList.add('inline-flex');
        }
        if (icon) {
            icon.textContent = 'check';
            icon.classList.add('text-emerald-600');
        }
        if (resiCopyTimeout) clearTimeout(resiCopyTimeout);
        resiCopyTimeout = setTimeout(() => {
            if (label) {
                label.classList.add('hidden');
                label.classList.remove('inline-flex');
            }
            if (icon) {
                icon.textContent = 'content_copy';
                icon.classList.remove('text-emerald-600');
            }
        }, 2000);
    };

    if (navigator.clipboard && window.isSecureContext) {
        navigator.clipboard.writeText(resi).then(doShowLabel).catch(() => {
            fallbackCopyText(resi, doShowLabel);
        });
    } else {
        fallbackCopyText(resi, doShowLabel);
    }
}

function fallbackCopyText(text, callback) {
    const textArea = document.createElement('textarea');
    textArea.value = text;
    textArea.style.position = 'fixed';
    textArea.style.left = '-999999px';
    textArea.style.top = '-999999px';
    document.body.appendChild(textArea);
    textArea.focus();
    textArea.select();
    try {
        document.execCommand('copy');
        if (callback) callback();
    } catch (e) {
        console.error('Fallback copy error:', e);
    }
    textArea.remove();
}

async function handleQuickStatusSubmit(e) {
    e.preventDefault();
    const isStatusLocked = {{ in_array((int)$order->status, [\App\Models\Order\Order::STATUS_SHIPPED, \App\Models\Order\Order::STATUS_DELIVERED]) ? 'true' : 'false' }};
    if (isStatusLocked) {
        if (typeof showToast === 'function') {
            showToast('error', 'Status pesanan sudah terkunci dan tidak dapat diubah lagi.');
        } else {
            alert('Status pesanan sudah terkunci dan tidak dapat diubah lagi.');
        }
        return;
    }

    const select = document.getElementById('quickStatusSelect');
    const targetStatus = parseInt(select.value);
    const orderData = @json($resiModalData);

    // If changing to Shipped (4) or Delivered (5) and there's no resi yet, open the modal to choose Manual or Biteship
    if ((targetStatus === 4 || targetStatus === 5) && !orderData.resi) {
        const statusName = targetStatus === 5 ? 'Selesai (Delivered)' : 'Dikirim (Shipped)';
        if (confirm('Pesanan ini belum memiliki resi. Untuk mengubah status menjadi ' + statusName + ', pilih opsi Input Resi Manual atau Hit Biteship. Buka form pengiriman sekarang?')) {
            const formStatusTarget = document.getElementById('formStatusTarget');
            const biteshipFinalStatus = document.getElementById('biteshipFinalStatus');
            if (formStatusTarget) formStatusTarget.value = targetStatus;
            if (biteshipFinalStatus) biteshipFinalStatus.value = targetStatus;
            openResiModal(orderData);
            return;
        }
    }

    const btn = document.getElementById('quickStatusBtn');
    btn.disabled = true;
    btn.innerHTML = '<div class="inline-block animate-spin w-3 h-3 border-2 border-primary border-t-transparent rounded-full mr-1"></div>';

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}';

    try {
        const response = await fetch('/orders/{{ $order->id }}/update-status', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify({
                status: targetStatus
            })
        });

        const data = await response.json();
        if (response.ok && data.success) {
            if (typeof showToast === 'function') {
                showToast('success', data.message || 'Status pesanan berhasil diperbarui.');
            }
            setTimeout(() => window.location.reload(), 600);
        } else {
            if (typeof showToast === 'function') {
                showToast('error', data.message || 'Gagal memperbarui status pesanan.');
            } else {
                alert(data.message || 'Gagal memperbarui status pesanan.');
            }
        }
    } catch (err) {
        console.error(err);
        if (typeof showToast === 'function') {
            showToast('error', 'Terjadi gangguan sistem saat memperbarui status.');
        } else {
            alert('Terjadi gangguan sistem saat memperbarui status.');
        }
    } finally {
        btn.disabled = false;
        btn.innerHTML = '<span class="material-symbols-outlined text-[14px]">save</span><span>Ubah</span>';
    }
}
</script>
@endsection
