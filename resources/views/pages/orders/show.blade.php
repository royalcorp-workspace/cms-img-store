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
    <!-- Action Header Bar (Clean, Non-Redundant Navigation & Status) -->
    <div class="flex flex-wrap items-center justify-between gap-3 bg-white p-3.5 rounded-xl border border-outline-variant/60 shadow-2xs">
        <div class="flex items-center gap-3">
            <a href="{{ route('orders.index') }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 border border-outline-variant bg-white text-on-surface hover:bg-surface-container rounded-lg text-xs font-semibold transition-all shadow-2xs">
                <span class="material-symbols-outlined text-[16px]">arrow_back</span>
                <span>Kembali</span>
            </a>
            <div class="flex items-center gap-2">
                <span class="font-bold text-sm text-on-surface">Pesanan #{{ $order->order_number }}</span>
                @if($order->source_platform === 'mobile')
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200" title="Platform: Mobile App">
                        <span class="material-symbols-outlined text-[12px]">smartphone</span> Mobile
                    </span>
                @elseif($order->source_platform === 'pos')
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-bold bg-purple-50 text-purple-700 border border-purple-200" title="Platform: POS Toko">
                        <span class="material-symbols-outlined text-[12px]">storefront</span> POS
                    </span>
                @else
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-bold bg-blue-50 text-blue-700 border border-blue-200" title="Platform: Web Store">
                        <span class="material-symbols-outlined text-[12px]">language</span> Web
                    </span>
                @endif
            </div>
        </div>

        <div class="flex items-center gap-2">
            @if((int)$order->status === \App\Models\Order\Order::STATUS_SHIPPED)
                <button type="button" id="btnCompleteOrderTop" onclick="confirmCompleteOrder('{{ $order->id }}', '{{ $order->order_number }}')" class="inline-flex items-center gap-1.5 px-4 py-2 bg-primary text-white hover:opacity-90 rounded-lg text-xs font-semibold shadow-xs transition-all" title="Pesanan telah sampai dan diterima dengan baik oleh pelanggan">
                    <span class="material-symbols-outlined text-[16px]">task_alt</span>
                    <span>Selesaikan Pesanan</span>
                </button>
            @endif

            <span class="inline-flex items-center gap-1.5 px-2.5 py-1.5 rounded-lg text-xs font-semibold bg-surface-container text-on-surface border border-outline-variant/60" title="Status Pesanan">
                <span class="w-2 h-2 rounded-full {{ in_array((int)$order->status, [4, 5]) ? 'bg-emerald-500' : (in_array((int)$order->status, [6, 7]) ? 'bg-rose-500' : ((int)$order->status === 0 ? 'bg-slate-400' : 'bg-blue-500')) }}"></span>
                <span>{{ $order->statusLabel() }}</span>
            </span>

            <span class="inline-flex items-center gap-1.5 px-2.5 py-1.5 rounded-lg text-xs font-semibold bg-surface-container text-on-surface border border-outline-variant/60" title="Status Pembayaran">
                <span class="w-2 h-2 rounded-full {{ $order->isPaymentPaid() ? 'bg-emerald-500' : 'bg-amber-500' }}"></span>
                <span>{{ $order->paymentStatusLabel() }}</span>
            </span>
        </div>
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
                        <span class="material-symbols-outlined text-[16px] text-on-surface-variant">info</span>
                        Status & Data Order
                    </span>
                    <span id="show-order-status-badge" class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded text-[11px] font-medium bg-surface-container text-on-surface border border-outline-variant/60">
                        <span class="w-1.5 h-1.5 rounded-full {{ in_array((int)$order->status, [4, 5]) ? 'bg-emerald-500' : (in_array((int)$order->status, [6, 7]) ? 'bg-rose-500' : ((int)$order->status === 0 ? 'bg-slate-400' : 'bg-blue-500')) }}"></span>
                        <span>{{ $order->statusLabel() }}</span>
                    </span>
                </div>

                <!-- Nomor Order -->
                <div class="p-2.5 bg-surface-container-low rounded-lg border border-outline-variant/30 flex items-center justify-between">
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="text-[10px] text-on-surface-variant font-semibold block uppercase">Nomor Pesanan</span>
                            @if($order->source_platform === 'mobile')
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200" title="Platform: Mobile App">
                                    <span class="material-symbols-outlined text-[12px]">smartphone</span> Mobile
                                </span>
                            @elseif($order->source_platform === 'pos')
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-bold bg-purple-50 text-purple-700 border border-purple-200" title="Platform: POS Toko">
                                    <span class="material-symbols-outlined text-[12px]">storefront</span> POS
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-bold bg-blue-50 text-blue-700 border border-blue-200" title="Platform: Web Store">
                                    <span class="material-symbols-outlined text-[12px]">language</span> Web
                                </span>
                            @endif
                        </div>
                        <div class="flex items-center gap-2 mt-0.5">
                            <span class="font-mono font-bold text-sm text-on-surface">#{{ $order->order_number }}</span>
                            <span id="order-copy-label" class="hidden text-[10px] font-medium text-on-surface bg-surface-container border border-outline-variant/60 px-1.5 py-0.5 rounded transition-all items-center gap-0.5">
                                <span class="material-symbols-outlined text-[12px] text-emerald-600">check</span>
                                Disalin
                            </span>
                        </div>
                    </div>
                    <button type="button" id="order-copy-btn" onclick="copyOrderNumber('{{ $order->order_number }}')" class="p-1.5 text-on-surface-variant hover:text-on-surface rounded-md hover:bg-white transition-colors" title="Salin Nomor Order">
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
                        <span class="font-medium text-[11px] flex items-center gap-1.5 text-on-surface mt-0.5">
                            <span class="w-1.5 h-1.5 rounded-full {{ $order->isPaymentPaid() ? 'bg-emerald-500' : 'bg-amber-500' }}"></span>
                            <span>{{ $order->paymentStatusLabel() }}</span>
                        </span>
                    </div>
                </div>

                <!-- Status Pesanan (By Process) -->
                <div class="pt-2 border-t border-outline-variant/40 space-y-2">
                    <div class="flex items-center justify-between">
                        <span class="block text-[11px] font-semibold text-on-surface">
                            Alur Status Pesanan:
                        </span>
                        <span class="text-[10px] font-medium text-on-surface-variant bg-surface-container border border-outline-variant/60 px-1.5 py-0.5 rounded flex items-center gap-1" title="Perubahan status berjalan otomatis mengikuti proses pembayaran dan logistik pengiriman">
                            <span class="material-symbols-outlined text-[12px] text-primary">autorenew</span>
                            By Proses
                        </span>
                    </div>

                    @if((int)$order->status === \App\Models\Order\Order::STATUS_SHIPPED)
                        {{-- Status: Shipped (Sedang Dikirim) -> Tampilkan Notifikasi & Tombol Selesaikan Pesanan --}}
                        <div class="p-3 bg-blue-50/70 dark:bg-blue-950/20 rounded-lg border border-blue-200 dark:border-blue-900/40 space-y-2.5">
                            <div class="flex items-center justify-between">
                                <span class="inline-flex items-center gap-1.5 text-blue-800 dark:text-blue-300 font-semibold text-xs">
                                    <span class="w-2 h-2 rounded-full bg-blue-500 animate-pulse"></span>
                                    <span>Sedang Dikirim (Shipped)</span>
                                </span>
                                @if($order->resi)
                                    <span class="font-mono text-[10px] text-blue-700 bg-white/80 px-1.5 py-0.5 rounded border border-blue-200">
                                        {{ $order->resi }}
                                    </span>
                                @endif
                            </div>
                            <p class="text-[11px] text-blue-800/80 dark:text-blue-200/80 leading-relaxed">
                                Pesanan sedang dalam perjalanan pengiriman. Bila barang telah sampai dan diterima dengan baik oleh pelanggan, silakan selesaikan pesanan ini.
                            </p>
                            <button type="button" id="btnCompleteOrderSidebar" onclick="confirmCompleteOrder('{{ $order->id }}', '{{ $order->order_number }}')" class="w-full py-2 px-3 bg-primary text-white hover:opacity-90 rounded-lg text-xs font-semibold shadow-xs transition-all flex items-center justify-center gap-1.5">
                                <span class="material-symbols-outlined text-[16px]">task_alt</span>
                                <span>Selesaikan Pesanan (Tandai Diterima)</span>
                            </button>
                        </div>
                    @elseif((int)$order->status === \App\Models\Order\Order::STATUS_DELIVERED)
                        {{-- Status: Delivered (Selesai) --}}
                        <div class="p-2.5 bg-emerald-50/80 dark:bg-emerald-950/20 rounded-lg border border-emerald-200 dark:border-emerald-800/40 text-xs flex items-start gap-2">
                            <span class="material-symbols-outlined text-[18px] text-emerald-600 shrink-0 mt-0.5">verified</span>
                            <div class="space-y-0.5">
                                <span class="font-semibold text-emerald-900 dark:text-emerald-200 block text-[11px]">Pesanan Selesai (Delivered)</span>
                                <p class="text-[10px] text-emerald-700 dark:text-emerald-300 leading-normal">
                                    Seluruh proses pemesanan dan pengiriman barang telah selesai diterima oleh pelanggan.
                                </p>
                            </div>
                        </div>
                    @elseif((int)$order->status === \App\Models\Order\Order::STATUS_CANCELLED)
                        {{-- Status: Batal --}}
                        <div class="p-2.5 bg-rose-50/80 rounded-lg border border-rose-200 text-xs flex items-center gap-2">
                            <span class="material-symbols-outlined text-[18px] text-rose-600">cancel</span>
                            <span class="text-[11px] text-rose-800 font-medium">Pesanan Dibatalkan (Cancelled)</span>
                        </div>
                    @else
                        {{-- Status: Menunggu / Dikonfirmasi / Diproses (status < 4) --}}
                        <div class="p-2.5 bg-surface-container-low rounded-lg border border-outline-variant/30 text-xs space-y-1">
                            <div class="flex items-center gap-1.5 text-on-surface font-semibold text-[11px]">
                                <span class="w-1.5 h-1.5 rounded-full bg-blue-500"></span>
                                <span>{{ $order->statusLabel() }}</span>
                            </div>
                            <p class="text-[10px] text-on-surface-variant leading-relaxed">
                                Status pesanan akan otomatis berubah menjadi <strong class="text-on-surface font-semibold">Dikirim (Shipped)</strong> ketika nomor resi pengiriman dimasukkan melalui tombol <em>Input Resi</em> atau <em>Get Resi Otomatis</em>.
                            </p>
                        </div>
                    @endif
                </div>

                @php
                    $customerOrderNote = $order->notes ?: ($order->meta['notes'] ?? ($order->meta['customer_notes'] ?? null));
                @endphp
                @if($customerOrderNote)
                    <div class="p-2.5 bg-amber-50/70 border border-amber-200/80 rounded-lg text-xs text-amber-950">
                        <span class="text-[10px] font-bold uppercase flex items-center gap-1 text-amber-800">
                            <span class="material-symbols-outlined text-[13px] text-amber-600">sticky_note_2</span>
                            Catatan Pembeli / Pesanan:
                        </span>
                        <p class="mt-0.5 italic leading-relaxed font-medium">"{{ $customerOrderNote }}"</p>
                    </div>
                @endif
            </div>

            <!-- Card: Pelanggan & Tujuan Pengiriman -->
            <div class="bg-white rounded-xl border border-outline-variant/60 shadow-xs p-4 space-y-3">
                <div class="flex items-center justify-between border-b border-outline-variant/40 pb-2.5">
                    <span class="font-bold text-xs text-on-surface uppercase tracking-wider flex items-center gap-1.5">
                        <span class="material-symbols-outlined text-[16px] text-on-surface-variant">person</span>
                        Pelanggan & Tujuan
                    </span>
                    @if(!empty($shippingMeta['postal_code']))
                        <span class="text-[10px] font-mono font-medium px-2 py-0.5 rounded bg-surface-container text-on-surface border border-outline-variant/40">
                            {{ $shippingMeta['postal_code'] }}
                        </span>
                    @endif
                </div>

                <div class="space-y-3 text-xs">
                    <!-- Akun Pemesan -->
                    <div class="p-2.5 bg-surface-container-low rounded-lg border border-outline-variant/30 space-y-1">
                        <span class="text-[10px] text-on-surface-variant uppercase font-semibold block">Akun Pemesan</span>
                        <div class="font-bold text-on-surface text-xs flex items-center justify-between">
                            <span>{{ $order->customer?->name ?? 'Tamu (Guest)' }}</span>
                            @if($order->customer?->customer_code)
                                <span class="font-mono text-[10px] font-normal text-on-surface-variant">{{ $order->customer->customer_code }}</span>
                            @endif
                        </div>
                        @if($order->customer?->email)
                            <div class="text-[11px] text-on-surface-variant flex items-center gap-1">
                                <span class="material-symbols-outlined text-[13px]">mail</span>
                                <span>{{ $order->customer->email }}</span>
                            </div>
                        @endif
                        @if($order->customer?->phone)
                            <div class="text-[11px] text-on-surface-variant flex items-center gap-1">
                                <span class="material-symbols-outlined text-[13px]">call</span>
                                <span class="font-mono">{{ $order->customer->phone }}</span>
                            </div>
                        @endif
                    </div>

                    <!-- Penerima Pengiriman -->
                    <div class="space-y-2">
                        <div>
                            <span class="text-[10px] text-on-surface-variant font-semibold block">Nama Penerima</span>
                            <div class="font-bold text-on-surface text-sm">
                                {{ $shippingMeta['recipient_name'] ?? ($order->customer?->name ?? 'Pelanggan') }}
                            </div>
                        </div>

                        <div>
                            <span class="text-[10px] text-on-surface-variant font-semibold block">No. Telepon Penerima</span>
                            <div class="flex items-center justify-between mt-0.5">
                                <span class="font-mono font-medium text-on-surface">{{ $customerPhone ?? '-' }}</span>
                                @if($waPhone)
                                    <a href="https://wa.me/{{ $waPhone }}" target="_blank" class="inline-flex items-center gap-1 px-2.5 py-1 bg-white hover:bg-surface-container border border-outline-variant text-on-surface rounded text-[11px] font-medium transition-colors">
                                        <span class="material-symbols-outlined text-[13px] text-emerald-600">chat</span>
                                        <span>WhatsApp</span>
                                    </a>
                                @endif
                            </div>
                        </div>

                        <div class="pt-0.5">
                            <span class="text-[10px] text-on-surface-variant font-semibold block">Alamat Lengkap</span>
                            <p class="text-on-surface text-xs leading-relaxed mt-0.5 bg-surface-container-low p-2 rounded-lg border border-outline-variant/30">
                                @if(!empty($shippingMeta['address']))
                                    {{ $shippingMeta['address'] }}
                                    @if(!empty($shippingMeta['sub_district'])), Kec. {{ $shippingMeta['sub_district'] }}@endif
                                    @if(!empty($shippingMeta['city'])), {{ $shippingMeta['city'] }}@endif
                                    @if(!empty($shippingMeta['province'])), {{ $shippingMeta['province'] }}@endif
                                @elseif($order->customer && method_exists($order->customer, 'addresses') && $order->customer->addresses->isNotEmpty())
                                    {{ $order->customer->addresses->first()->address }}
                                @else
                                    Alamat belum tercatat lengkap.
                                @endif
                            </p>
                        </div>

                        @if($customerOrderNote)
                            <div class="pt-1.5">
                                <span class="text-[10px] text-on-surface-variant font-semibold block mb-0.5 flex items-center gap-1">
                                    <span class="material-symbols-outlined text-[13px] text-amber-600">note</span>
                                    Catatan Pembeli / Pengiriman
                                </span>
                                <div class="p-2.5 bg-amber-50/70 border border-amber-200/80 rounded-lg text-xs text-amber-950 font-medium italic leading-relaxed">
                                    "{{ $customerOrderNote }}"
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Card: Kurir & Logistik Pengiriman -->
            <div class="bg-white rounded-xl border border-outline-variant/60 shadow-xs p-4 space-y-3">
                <div class="flex items-center justify-between border-b border-outline-variant/40 pb-2.5">
                    <span class="font-bold text-xs text-on-surface uppercase tracking-wider flex items-center gap-1.5">
                        <span class="material-symbols-outlined text-[16px] text-on-surface-variant">local_shipping</span>
                        Pengiriman & Resi
                    </span>
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-medium bg-surface-container text-on-surface border border-outline-variant/60">
                        <span class="w-1.5 h-1.5 rounded-full {{ $order->resi ? 'bg-emerald-500' : 'bg-amber-500' }}"></span>
                        <span>{{ $order->resi ? 'Resi Ada' : 'Belum Ada Resi' }}</span>
                    </span>
                </div>

                <div class="space-y-2 text-xs">
                    <div>
                        <span class="text-[10px] text-on-surface-variant font-semibold block mb-0.5">Kurir Pesanan</span>
                        <div class="flex items-center justify-between p-2 bg-surface-container-low rounded-lg border border-outline-variant/40">
                            <span class="font-semibold text-on-surface" id="show-order-courier-name">{{ $order->courier_name ?? 'Kurir Belum Diatur' }}</span>
                            @if(!empty($order->meta['fulfillment_type']) || $order->isKurirToko())
                                <span class="text-[10px] px-2 py-0.5 rounded font-medium bg-surface-container text-on-surface-variant border border-outline-variant/50">
                                    {{ $order->isKurirToko() ? 'Kurir Toko' : (($order->meta['fulfillment_type'] ?? '') === 'biteship' ? 'Ekspedisi Otomatis' : 'Input Manual') }}
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
                                <span id="resi-copy-label" class="hidden text-[10px] font-medium text-on-surface bg-surface-container border border-outline-variant/60 px-1.5 py-0.5 rounded transition-all items-center gap-0.5">
                                    <span class="material-symbols-outlined text-[12px] text-emerald-600">check</span>
                                    Disalin
                                </span>
                            </div>
                            @if($order->resi)
                                <button type="button" id="resi-copy-btn" onclick="copyResiCodeText('{{ $order->resi }}')" class="p-1 hover:text-on-surface rounded text-on-surface-variant transition-colors" title="Salin Resi">
                                    <span id="resi-copy-icon" class="material-symbols-outlined text-[15px]">content_copy</span>
                                </button>
                            @endif
                        </div>
                    </div>

                    <div>
                        <span class="text-[10px] text-on-surface-variant font-semibold block mb-0.5">Status Pengiriman</span>
                        <div class="p-2 bg-surface-container-low rounded-lg border border-outline-variant/40 flex items-center justify-between">
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded text-xs font-medium bg-surface-container text-on-surface border border-outline-variant/60">
                                <span class="w-1.5 h-1.5 rounded-full {{ str_contains(strtolower($order->delivery_status_label), 'deliver') || str_contains(strtolower($order->delivery_status_label), 'selesai') ? 'bg-emerald-500' : 'bg-blue-500' }}"></span>
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

                    <!-- Estimasi Tiba (ETA) -->
                    @php
                        $deliveryObj = $order->delivery;
                        $etaLabel = $deliveryObj?->eta_label 
                            ?? ($order->meta['biteship_eta']['formatted_label'] ?? ($order->meta['shipping_eta_label'] ?? ($order->meta['shipping_duration'] ?? null)));
                        $etaSourceLabel = $deliveryObj?->eta_source_label 
                            ?? ($order->isKurirToko() ? 'Ditentukan Toko (Kurir Toko)' : (!empty($order->meta['biteship_order_id']) ? 'Vendor Ekspedisi (Biteship)' : 'Estimasi Pengiriman'));
                    @endphp
                    <div>
                        <span class="text-[10px] text-on-surface-variant font-semibold block mb-0.5">Estimasi Tiba (ETA)</span>
                        <div class="p-2.5 bg-surface-container-low rounded-lg border border-outline-variant/40 flex flex-col gap-1">
                            <div class="flex items-center justify-between">
                                <span class="font-medium text-xs text-on-surface flex items-center gap-1.5" id="show-order-eta-label">
                                    <span class="material-symbols-outlined text-[15px] text-on-surface-variant">schedule</span>
                                    <span>{{ $etaLabel ?: 'Belum ditentukan' }}</span>
                                </span>
                                <span class="text-[10px] px-2 py-0.5 rounded font-medium bg-surface-container text-on-surface-variant border border-outline-variant/50">
                                    {{ $etaSourceLabel }}
                                </span>
                            </div>
                            @if($deliveryObj?->eta_notes)
                                <p class="text-[11px] text-on-surface-variant italic">{{ $deliveryObj->eta_notes }}</p>
                            @endif
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    @if($order->status < \App\Models\Order\Order::STATUS_SHIPPED)
                        <div class="{{ $order->isOnlineRetail() && empty($resiModalData['is_kurir_toko']) && empty($resiModalData['has_biteship_resi']) ? 'grid grid-cols-2 gap-2' : 'flex' }} pt-1">
                            <button type="button" onclick='openResiModal(@json($resiModalData), "manual")' class="w-full py-2 px-3 bg-primary text-white hover:opacity-90 rounded-lg text-xs font-semibold shadow-xs transition-all flex items-center justify-center gap-1.5">
                                <span class="material-symbols-outlined text-[16px]">edit_document</span>
                                <span>{{ $order->resi ? 'Ubah Resi / Surat Jalan' : 'Input Resi / Surat Jalan' }}</span>
                            </button>
                            @if($order->isOnlineRetail() && empty($resiModalData['is_kurir_toko']) && empty($resiModalData['has_biteship_resi']))
                                <button type="button" onclick='openResiModal(@json($resiModalData), "biteship")' class="py-2 px-3 bg-primary text-white hover:opacity-90 rounded-lg text-xs font-semibold shadow-xs transition-all flex items-center justify-center gap-1.5">
                                    <span class="material-symbols-outlined text-[16px]">cloud_sync</span>
                                    <span>Get Resi Otomatis</span>
                                </button>
                            @endif
                        </div>
                    @endif

                    @if($order->isOnlineRetail() && ($order->resi || $order->delivery_status))
                        <button type="button" onclick='openResiModal(@json($resiModalData), "tracking")' class="w-full py-2 px-3 bg-primary text-white hover:opacity-90 rounded-lg text-xs font-semibold shadow-xs transition-all flex items-center justify-center gap-1.5">
                            <span class="material-symbols-outlined text-[16px]">radar</span>
                            <span>Lacak Pesanan (Log Pengiriman)</span>
                        </button>
                    @endif

                    @if($order->delivery)
                        <a href="{{ route('delivery.show', $order->delivery->id) }}" class="w-full py-2 px-3 border border-outline-variant bg-white text-on-surface hover:bg-surface-container rounded-lg text-xs font-semibold transition-all flex items-center justify-center gap-1.5">
                            <span class="material-symbols-outlined text-[16px] text-on-surface-variant">local_shipping</span>
                            <span>Detail Pengiriman & Log Ekspedisi</span>
                        </a>
                    @endif
                </div>
            </div>

            <!-- Card: Integrasi JDE (ERP) (Dedicated Card) -->
            <div id="jdeCardSection" class="{{ ($order->isPaidOrStarted() || (int)$order->jde_push_status > 0) ? 'block' : 'hidden' }} bg-white rounded-xl border border-outline-variant/60 shadow-xs p-4 space-y-3">
                <div class="flex items-center justify-between border-b border-outline-variant/40 pb-2.5">
                    <span class="font-bold text-xs text-on-surface uppercase tracking-wider flex items-center gap-1.5">
                        <span class="material-symbols-outlined text-[16px] text-on-surface-variant">sync_alt</span>
                        Integrasi JDE (ERP)
                    </span>
                    <span id="show-jde-status-badge" class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-medium bg-surface-container text-on-surface border border-outline-variant/60">
                        <span class="w-1.5 h-1.5 rounded-full {{ (int)$order->jde_push_status === \App\Models\Order\Order::JDE_STATUS_SUCCESS ? 'bg-emerald-500' : ((int)$order->jde_push_status === \App\Models\Order\Order::JDE_STATUS_PROCESSING ? 'bg-blue-500 animate-pulse' : ((int)$order->jde_push_status === \App\Models\Order\Order::JDE_STATUS_FAILED ? 'bg-rose-500' : 'bg-amber-500')) }}"></span>
                        <span>{{ $order->jdePushStatusLabel() }}</span>
                    </span>
                </div>

                <div class="space-y-2.5 text-xs">
                    <div class="p-2.5 bg-surface-container-low rounded-lg border border-outline-variant/40 flex items-center justify-between gap-2">
                        <div class="text-[11px] text-on-surface">
                            <div class="font-medium text-on-surface" id="show-jde-status-title">
                                @if((int)$order->jde_push_status === \App\Models\Order\Order::JDE_STATUS_SUCCESS)
                                    Success
                                @elseif((int)$order->jde_push_status === \App\Models\Order\Order::JDE_STATUS_PROCESSING)
                                    Queued
                                @elseif((int)$order->jde_push_status === \App\Models\Order\Order::JDE_STATUS_FAILED)
                                    Failed
                                @else
                                    Pending
                                @endif
                            </div>
                            <div class="text-[10px] text-on-surface-variant" id="show-jde-status-sub">
                                @if($order->jde_push_date)
                                    Pushed on {{ $order->jde_push_date->format('d M Y') }}
                                @elseif((int)$order->jde_push_status === \App\Models\Order\Order::JDE_STATUS_FAILED)
                                    Failed attempts: {{ $order->meta['jde_push_retry_count'] ?? 1 }}x
                                @else
                                    Queue worker automatically processes this order
                                @endif
                            </div>
                        </div>
                        <div id="jdeCardButtonContainer" class="flex items-center gap-1.5">
                            @if((int)$order->jde_push_status === \App\Models\Order\Order::JDE_STATUS_SUCCESS)
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 bg-surface-container text-on-surface rounded-md text-[11px] font-medium border border-outline-variant/60">
                                    <span class="material-symbols-outlined text-[13px] text-emerald-600">check</span>
                                    Success
                                </span>
                            @elseif((int)$order->jde_push_status === \App\Models\Order\Order::JDE_STATUS_PROCESSING)
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 bg-surface-container text-on-surface rounded-md text-[11px] font-medium border border-outline-variant/60">
                                    <span class="material-symbols-outlined text-[13px] text-blue-600 animate-spin">sync</span>
                                    Queued
                                </span>
                            @elseif((int)$order->jde_push_status === \App\Models\Order\Order::JDE_STATUS_FAILED)
                                <button type="button" id="btnPushToJdeCard" onclick="confirmPushToJde('{{ $order->id }}', '{{ $order->order_number }}')" class="px-3.5 py-1.5 bg-primary text-white hover:opacity-90 rounded-lg text-xs font-semibold shadow-xs transition-all flex items-center gap-1.5">
                                    <span class="material-symbols-outlined text-[15px]">refresh</span>
                                    <span>Retry</span>
                                </button>
                            @else
                                <button type="button" id="btnPushToJdeCard" onclick="confirmPushToJde('{{ $order->id }}', '{{ $order->order_number }}')" class="px-3.5 py-1.5 bg-primary text-white hover:opacity-90 rounded-lg text-xs font-semibold shadow-xs transition-all flex items-center gap-1.5">
                                    <span class="material-symbols-outlined text-[15px]">cloud_upload</span>
                                    <span>Push JDE</span>
                                </button>
                            @endif
                            <button type="button" onclick="adjustJdeStatus('{{ $order->id }}', '{{ $order->order_number }}', {{ (int)$order->jde_push_status }})" class="px-2.5 py-1.5 bg-surface-container hover:bg-surface-container-high text-on-surface border border-outline-variant/60 rounded-lg text-xs font-medium transition-all flex items-center gap-1" title="Sesuaikan Status JDE Manual">
                                <span class="material-symbols-outlined text-[14px]">tune</span>
                                <span>Sesuaikan</span>
                            </button>
                        </div>
                    </div>

                    @if((int)$order->jde_push_status === \App\Models\Order\Order::JDE_STATUS_FAILED && !empty($order->getJdeErrorMessage()))
                        <div class="p-2.5 bg-surface-container-low text-on-surface text-[11px] rounded-lg border border-rose-300/70 space-y-1">
                            <div class="font-medium flex items-center gap-1 text-rose-700">
                                <span class="material-symbols-outlined text-[14px]">error</span>
                                <span>Penyebab Kegagalan JDE:</span>
                            </div>
                            <div class="text-[11px] font-medium leading-relaxed bg-white p-2 rounded border border-rose-200 text-rose-800">
                                {{ $order->getJdeErrorMessage() }}
                            </div>
                            <div class="text-[9px] text-on-surface-variant flex justify-between pt-0.5">
                                <span>Percobaan gagal: {{ $order->meta['jde_push_retry_count'] ?? 1 }}x</span>
                                <span>Waktu: {{ $order->meta['jde_push_failed_at'] ?? '-' }}</span>
                            </div>
                        </div>
                    @endif

                    <!-- Info Business Unit & Address Number -->
                    <div class="grid grid-cols-2 gap-1.5 text-[11px] bg-surface-container-low p-2 rounded-lg border border-outline-variant/40">
                        <div>
                            <span class="text-[10px] text-on-surface-variant block font-medium">Business Unit (Store):</span>
                            <span class="font-bold text-on-surface font-mono text-xs">{{ $order->business_unit ?? ($order->store?->code ?? ($order->storeChannel?->code ?? config('services.jde.business_unit', '18011'))) }}</span>
                            @if($order->store || $order->storeChannel)
                                <span class="text-[10px] text-on-surface-variant block truncate" title="{{ $order->store?->name ?? $order->storeChannel?->name }}">{{ $order->store?->name ?? $order->storeChannel?->name }}</span>
                            @endif
                        </div>
                        <div>
                            <span class="text-[10px] text-on-surface-variant block font-medium">Address Number (Store):</span>
                            <span class="font-bold text-on-surface font-mono text-xs">{{ $order->address_number ?? ($order->store?->address_number ?? ($order->storeChannel?->address_number ?? config('services.jde.address_number', 100228))) }}</span>
                            @if($order->customer?->customer_code)
                                <span class="text-[10px] text-on-surface-variant block font-mono">Customer: {{ $order->customer->customer_code }}</span>
                            @endif
                        </div>
                        <div class="col-span-2 pt-1.5 mt-1 border-t border-outline-variant/30 flex items-center justify-between text-[10px] text-on-surface-variant">
                            <span class="flex items-center gap-1">
                                <span class="material-symbols-outlined text-[13px] text-emerald-600">verified</span>
                                <span>Harga Push JDE:</span>
                            </span>
                            @php
                                $jdePushRate = (float) ($order->ppn_rate ?? 0);
                                $isJdePpnActive = ($order->is_ppn_active ?? true) && $jdePushRate > 0;
                                $jdeRateLabel = (int)$jdePushRate == $jdePushRate ? (int)$jdePushRate : number_format($jdePushRate, 1);
                            @endphp
                            @if($isJdePpnActive)
                                <span class="font-medium text-on-surface">Tanpa PPN (Exclude PPN)</span>
                            @else
                                <span class="font-medium text-on-surface">Tanpa PPN (Bebas PPN)</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Card: Pembayaran & Bukti Transfer -->
            <div class="bg-white rounded-xl border border-outline-variant/60 shadow-xs p-4 space-y-3">
                <div class="flex items-center justify-between border-b border-outline-variant/40 pb-2.5">
                    <span class="font-bold text-xs text-on-surface uppercase tracking-wider flex items-center gap-1.5">
                        <span class="material-symbols-outlined text-[16px] text-on-surface-variant">payments</span>
                        Pembayaran
                    </span>
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-medium bg-surface-container text-on-surface border border-outline-variant/60">
                        <span class="w-1.5 h-1.5 rounded-full {{ $order->isPaymentPaid() ? 'bg-emerald-500' : 'bg-amber-500' }}"></span>
                        <span>{{ $order->paymentStatusLabel() }}</span>
                    </span>
                </div>

                <div class="space-y-2 text-xs">
                    <div class="flex justify-between py-1 border-b border-outline-variant/20">
                        <span class="text-on-surface-variant">Metode</span>
                        <span class="font-medium text-on-surface">{{ $order->payment_method_name }}</span>
                    </div>

                    @if($order->invoice)
                        <div class="flex justify-between py-1 border-b border-outline-variant/20">
                            <span class="text-on-surface-variant">No. Faktur</span>
                            <span class="font-mono font-medium text-on-surface">{{ $order->invoice->invoice_number }}</span>
                        </div>
                    @endif

                    @if(!empty($order->meta['payment_proof']))
                        <div class="pt-2 space-y-1.5">
                            <span class="text-[10px] font-semibold uppercase text-on-surface-variant block">Bukti Transfer Pelanggan</span>
                            <a href="{{ media_url($order->meta['payment_proof']) }}" target="_blank" class="block w-full py-1.5 text-center text-xs font-medium text-on-surface bg-white hover:bg-surface-container border border-outline-variant rounded-lg transition-colors">
                                Lihat Bukti Transfer
                            </a>
                        </div>
                    @endif

                    @if($order->isManualTransfer())
                        <form method="POST" action="{{ route('orders.verify-payment', $order->id) }}" class="pt-2 border-t border-outline-variant/40 space-y-2">
                            @csrf
                            <input type="hidden" name="status" value="3">
                            <input type="hidden" name="payment_status" value="1">
                            <button type="submit" class="w-full py-2 px-4 bg-primary hover:opacity-90 text-white rounded-lg text-xs font-semibold shadow-xs transition-all flex items-center justify-center gap-1.5">
                                <span class="material-symbols-outlined text-[16px]">check_circle</span>
                                <span>Setujui Pembayaran (Mark Paid)</span>
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
                    <div class="flex items-center gap-2">
                        <span class="font-bold text-xs text-on-surface uppercase tracking-wider flex items-center gap-1.5">
                            <span class="material-symbols-outlined text-[17px] text-on-surface-variant">inventory_2</span>
                            Rincian Item Produk ({{ $order->items->count() }} item)
                        </span>
                        @php
                            $headerOrderRate = (float) ($order->ppn_rate ?? 0);
                            $headerPpnActive = ($order->is_ppn_active ?? true) && $headerOrderRate > 0;
                            $headerRateLabel = (int)$headerOrderRate == $headerOrderRate ? (int)$headerOrderRate : number_format($headerOrderRate, 1);
                        @endphp
                        @if($headerPpnActive)
                            <span class="inline-flex items-center gap-1 text-[10px] font-medium text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded border border-emerald-200" title="Harga produk sudah termasuk PPN">
                                <span class="material-symbols-outlined text-[12px]">verified</span>
                                Harga + PPN
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1 text-[10px] font-medium text-gray-700 bg-gray-50 px-2 py-0.5 rounded border border-gray-200" title="Harga produk bebas PPN">
                                <span class="material-symbols-outlined text-[12px]">verified</span>
                                Bebas PPN
                            </span>
                        @endif
                    </div>
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
                                <th class="py-2.5 px-3 text-right whitespace-nowrap">Total Item</th>
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
                                                        <span class="text-[10px] text-on-surface-variant font-medium">[Bundling]</span>
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

                                    <!-- Unit Price (Harga Asli Termasuk PPN) -->
                                    <td class="py-3 px-3 text-right font-medium text-on-surface align-top whitespace-nowrap">
                                        <div class="font-bold">Rp {{ number_format($price, 0, ',', '.') }}</div>
                                        @php
                                            $unitRate = (float) ($item->ppn_rate ?? ($order->ppn_rate ?? 0.0));
                                            $unitPpnActive = ($item->is_ppn_active ?? ($order->is_ppn_active ?? true)) && $unitRate > 0;
                                        @endphp
                                        @if($unitPpnActive)
                                            <div class="text-[10px] text-on-surface-variant font-normal mt-0.5" title="Harga Sebelum PPN">
                                                Sebelum PPN: Rp {{ number_format($item->dpp_price ?: round($price / (1 + ($unitRate / 100)), 2), 0, ',', '.') }}
                                            </div>
                                        @else
                                            <div class="text-[10px] text-on-surface-variant font-normal mt-0.5">
                                                <span class="text-[9px] text-gray-500 font-semibold">Bebas PPN</span>
                                            </div>
                                        @endif
                                        @if($qty > 1)
                                            <div class="text-[10px] text-on-surface-variant font-normal opacity-75 mt-0.5">Total: Rp {{ number_format($sub, 0, ',', '.') }}</div>
                                        @endif
                                    </td>

                                    <!-- Qty -->
                                    <td class="py-3 px-3 text-center font-medium text-on-surface align-top whitespace-nowrap">
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

                                    <!-- Total Item (Termasuk PPN) -->
                                    <td class="py-3 px-3 text-right align-top whitespace-nowrap">
                                        <div class="font-bold text-xs text-on-surface">Rp {{ number_format($item->total, 0, ',', '.') }}</div>
                                        @if($item->getVoucherDiscountShare() > 0)
                                            <div class="text-[10px] text-emerald-700 font-medium">Voucher: -Rp {{ number_format($item->getVoucherDiscountShare(), 0, ',', '.') }}</div>
                                            <div class="text-[10px] font-semibold text-on-surface">Net: Rp {{ number_format($item->getNetTotal(), 0, ',', '.') }}</div>
                                        @endif
                                        @php
                                            $itemRate = (float) ($item->ppn_rate ?? ($order->ppn_rate ?? 0.0));
                                            $itemPpnActive = ($item->is_ppn_active ?? ($order->is_ppn_active ?? true)) && $itemRate > 0;
                                            $itemRateLabel = (int)$itemRate == $itemRate ? (int)$itemRate : number_format($itemRate, 1);
                                        @endphp
                                        @if($itemPpnActive)
                                            <div class="text-[10px] text-on-surface-variant font-normal mt-0.5" title="Harga Sebelum PPN untuk Total Item">
                                                Sebelum PPN: Rp {{ number_format($item->getDppTotal(), 0, ',', '.') }}
                                            </div>
                                            <div class="text-[9px] text-emerald-700 font-medium" title="Nilai PPN Termasuk">
                                                PPN: Rp {{ number_format($item->getPpnTotal(), 0, ',', '.') }}
                                            </div>
                                        @else
                                            <div class="text-[10px] text-on-surface-variant font-normal mt-0.5">
                                                <span class="px-1.5 py-0.5 rounded bg-gray-100 text-gray-600 text-[9px] font-semibold">Bebas PPN</span>
                                            </div>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Financial Breakdown Panel (Sinkron dengan Thank You Pages) -->
                @php
                    $orderMeta = $order->meta ?? [];

                    // 1. Gross Cart Subtotal (Original price before promo / voucher discounts)
                    $originalCartTotal = (float) ($orderMeta['original_cart_total'] ?? 0);
                    if ($originalCartTotal <= 0 && $order->items && count($order->items) > 0) {
                        $originalCartTotal = (float) collect($order->items)->sum(function($it) {
                            $meta = is_array($it->meta) ? $it->meta : (json_decode($it->meta ?? '[]', true) ?: []);
                            $unitP = (float) ($it->unit_price ?? $meta['original_price'] ?? $meta['base_price'] ?? $it->sell_price ?? 0);
                            return $unitP * (int) ($it->quantity ?? 1);
                        });
                    }
                    if ($originalCartTotal <= 0) {
                        $originalCartTotal = (float) ($order->subtotal ?? 0);
                    }

                    // 2. Catalog Promo Discounts & Tier Volume Discounts
                    $priceProductSettingDiscount = (float) ($orderMeta['price_product_setting_discount'] ?? 0);
                    $staticPromoDiscount = (float) ($orderMeta['total_static_discount'] ?? 0);
                    if ($staticPromoDiscount <= 0 && !empty($orderMeta['promo_discount'])) {
                        $staticPromoDiscount = max(0, (float) $orderMeta['promo_discount'] - $priceProductSettingDiscount);
                    }
                    if ($staticPromoDiscount <= 0 && $order->catalog_promo_discount > 0) {
                        $staticPromoDiscount = (float) $order->catalog_promo_discount;
                    }

                    // 3. Shipping Cost & Subsidy
                    $shippingCost = (float) ($order->shipping_cost ?? ($orderMeta['shipping_cost'] ?? 0));
                    $shippingSubsidy = 0.0;
                    if (!empty($orderMeta['shipping_voucher_discount']) && (float)$orderMeta['shipping_voucher_discount'] > 0) {
                        $shippingSubsidy = (float)$orderMeta['shipping_voucher_discount'];
                    } elseif (!empty($order->shipping_cost_subsidy) && (float)$order->shipping_cost_subsidy > 0) {
                        $shippingSubsidy = (float)$order->shipping_cost_subsidy;
                    } elseif (!empty($orderMeta['shipping_cost_subsidy']) && (float)$orderMeta['shipping_cost_subsidy'] > 0) {
                        $shippingSubsidy = (float)$orderMeta['shipping_cost_subsidy'];
                    } elseif (!empty($orderMeta['shipping_subsidy']) && (float)$orderMeta['shipping_subsidy'] > 0) {
                        $shippingSubsidy = (float)$orderMeta['shipping_subsidy'];
                    } elseif (!empty($orderMeta['shipping_discount']) && (float)$orderMeta['shipping_discount'] > 0) {
                        $shippingSubsidy = (float)$orderMeta['shipping_discount'];
                    }
                    if ($order->shipping_voucher_discount > 0 && $shippingSubsidy <= 0) {
                        $shippingSubsidy = (float) $order->shipping_voucher_discount;
                    }

                    // 4. Product Voucher Discount & Voucher Nominal
                    $productVoucherDiscount = (float) ($orderMeta['product_voucher_discount'] ?? 0);
                    $voucherNominal = (float) ($order->voucher_nominal ?? ($orderMeta['voucher_discount'] ?? 0));
                    if ($productVoucherDiscount <= 0 && $voucherNominal > 0) {
                        $productVoucherDiscount = max(0, $voucherNominal - $shippingSubsidy);
                    }
                    if ($order->product_voucher_discount > 0 && $productVoucherDiscount <= 0) {
                        $productVoucherDiscount = (float) $order->product_voucher_discount;
                    }

                    // 5. Resolve Voucher Codes
                    $shippingVoucherCode = null;
                    $productVoucherCode = null;
                    if (!empty($orderMeta['applied_vouchers'])) {
                        foreach ($orderMeta['applied_vouchers'] as $av) {
                            if (!empty($av['is_shipping'])) {
                                $shippingVoucherCode = $av['code'] ?? ($av['voucher']['code'] ?? null);
                            } else {
                                $productVoucherCode = $av['code'] ?? ($av['voucher']['code'] ?? null);
                            }
                        }
                    }
                    if (!$shippingVoucherCode && !empty($orderMeta['voucher_codes'])) {
                        foreach ($orderMeta['voucher_codes'] as $vc) {
                            if (str_contains(strtoupper($vc), 'ONGKIR') || str_contains(strtoupper($vc), 'SHIP') || str_contains(strtoupper($vc), 'FREE')) {
                                $shippingVoucherCode = $vc;
                                break;
                            }
                        }
                    }
                    if (empty($productVoucherCode) && !empty($orderMeta['voucher_codes'])) {
                        $productVoucherCode = collect($orderMeta['voucher_codes'])->filter(fn($c) => !str_contains(strtoupper($c), 'ONGKIR') && !str_contains(strtoupper($c), 'SHIP'))->first();
                    }
                    if (empty($productVoucherCode) && !empty($orderMeta['voucher_code'])) {
                        $productVoucherCode = $orderMeta['voucher_code'];
                    }
                    if (empty($productVoucherCode) && $order->voucher && (int)$order->voucher->type !== 3) {
                        $productVoucherCode = $order->voucher->code;
                    }
                    if (empty($shippingVoucherCode) && $order->voucher && (int)$order->voucher->type === 3) {
                        $shippingVoucherCode = $order->voucher->code;
                    }

                    $courierDisplayName = strtoupper($order->courier_name ?? ($orderMeta['courier'] ?? 'Kurir'));
                    $shippingServiceName = $orderMeta['shipping_service_name'] ?? null;
                    if ($shippingServiceName && !str_contains($courierDisplayName, $shippingServiceName)) {
                        $courierDisplayName .= ' (' . $shippingServiceName . ')';
                    }

                    $transactionFee = (float) ($order->transaction_fee ?? 0);
                    $orderFinalTotal = (float) $order->total;
                @endphp

                <div class="p-4 bg-surface-container-lowest border-t border-outline-variant/40 flex justify-end">
                    <div class="w-full max-w-sm space-y-2 text-xs">
                        {{-- 1. Harga Produk (Subtotal Produk sebelum diskon) --}}
                        <div class="flex justify-between items-center py-0.5 text-on-surface-variant">
                            <span>Harga Produk</span>
                            <span class="font-semibold text-on-surface">Rp {{ number_format($originalCartTotal, 0, ',', '.') }}</span>
                        </div>

                        {{-- 2. Diskon Promo Katalog --}}
                        @if($staticPromoDiscount > 0)
                            <div class="flex justify-between items-center py-0.5 text-danger font-medium">
                                <span class="flex items-center gap-1">
                                    <span class="material-symbols-outlined text-[14px]">local_offer</span>
                                    <span>Diskon Promo</span>
                                </span>
                                <span>-Rp {{ number_format($staticPromoDiscount, 0, ',', '.') }}</span>
                            </div>
                        @endif

                        {{-- Diskon Volume Tier (jika ada) --}}
                        @if($priceProductSettingDiscount > 0)
                            <div class="flex justify-between items-center py-0.5 text-danger font-medium">
                                <span class="flex items-center gap-1">
                                    <span class="material-symbols-outlined text-[14px]">inventory</span>
                                    <span>Diskon Volume</span>
                                </span>
                                <span>-Rp {{ number_format($priceProductSettingDiscount, 0, ',', '.') }}</span>
                            </div>
                        @endif

                        {{-- 3. Voucher Diskon Produk --}}
                        @if($productVoucherDiscount > 0)
                            <div class="flex justify-between items-center py-1 px-2.5 bg-emerald-50 text-emerald-800 rounded-lg border border-emerald-200/80 font-medium">
                                <span class="flex items-center gap-1">
                                    <span class="material-symbols-outlined text-[14px] text-emerald-600">confirmation_number</span>
                                    <span>Voucher Diskon{{ $productVoucherCode ? ' (' . $productVoucherCode . ')' : '' }}</span>
                                </span>
                                <span class="font-bold">-Rp {{ number_format($productVoucherDiscount, 0, ',', '.') }}</span>
                            </div>
                        @endif

                        {{-- 4. Sub Total (Produk Bersih setelah Diskon & Voucher) & Rincian PPN Dinamis --}}
                        @php
                            $orderPpnRate = $order->ppn_rate !== null ? (float) $order->ppn_rate : 0.0;
                            $isOrderPpnActive = ($order->is_ppn_active ?? true) && $orderPpnRate > 0;
                            $orderRateLabel = (int)$orderPpnRate == $orderPpnRate ? (int)$orderPpnRate : number_format($orderPpnRate, 1);
                            $netSubtotal = max(0, $originalCartTotal - $staticPromoDiscount - $priceProductSettingDiscount - $productVoucherDiscount);
                            if ($isOrderPpnActive) {
                                $orderDppAmount = round($netSubtotal / (1 + ($orderPpnRate / 100)), 2);
                                $orderPpnAmount = round($netSubtotal - $orderDppAmount, 2);
                            } else {
                                $orderDppAmount = $netSubtotal;
                                $orderPpnAmount = 0.0;
                            }
                        @endphp
                        <div class="pt-1.5 border-t border-outline-variant/30">
                            <div class="flex justify-between items-center py-1 text-on-surface font-bold">
                                <span>Sub Total</span>
                                <span class="font-mono text-sm">Rp {{ number_format($netSubtotal, 0, ',', '.') }}</span>
                            </div>
                            <div class="p-2 bg-surface-container-low rounded-lg border border-outline-variant/30 text-[11px] space-y-1 text-on-surface-variant">
                                @if($isOrderPpnActive)
                                    <div class="flex justify-between items-center">
                                        <span>├─ Sebelum PPN:</span>
                                        <span class="font-mono font-medium text-on-surface">Rp {{ number_format($orderDppAmount, 0, ',', '.') }}</span>
                                    </div>
                                    <div class="flex justify-between items-center text-primary font-medium">
                                        <span class="flex items-center gap-1">
                                            <span>└─ PPN:</span>
                                        </span>
                                        <span class="font-mono">Rp {{ number_format($orderPpnAmount, 0, ',', '.') }}</span>
                                    </div>
                                @else
                                    <div class="flex justify-between items-center text-on-surface-variant font-medium">
                                        <span class="flex items-center gap-1">
                                            <span class="material-symbols-outlined text-[14px] text-gray-500">info</span>
                                            <span>Status Pajak:</span>
                                        </span>
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-gray-100 text-gray-700 border border-gray-300">Bebas PPN (Nonaktif)</span>
                                    </div>
                                @endif
                            </div>
                        </div>

                        {{-- 5. Ongkir (Biaya Pengiriman) --}}
                        <div class="flex justify-between items-center py-0.5 text-on-surface-variant">
                            <span>Ongkir <span class="text-[11px] opacity-75">({{ $courierDisplayName }})</span></span>
                            <span class="font-semibold text-on-surface">Rp {{ number_format($shippingCost, 0, ',', '.') }}</span>
                        </div>

                        {{-- 6. Diskon Ongkir (Subsidi Ongkir) --}}
                        @if($shippingSubsidy > 0)
                            <div class="flex justify-between items-center py-1 px-2.5 bg-emerald-50 text-emerald-800 rounded-lg border border-emerald-200/80 font-medium">
                                <span class="flex items-center gap-1">
                                    <span class="material-symbols-outlined text-[14px] text-emerald-600">local_shipping</span>
                                    <span>Diskon Ongkir{{ $shippingVoucherCode ? ' (' . $shippingVoucherCode . ')' : '' }}</span>
                                </span>
                                <span class="font-bold">-Rp {{ number_format($shippingSubsidy, 0, ',', '.') }}</span>
                            </div>
                        @endif

                        {{-- 7. Biaya Layanan / Transaksi jika ada --}}
                        @if($transactionFee > 0)
                            <div class="flex justify-between items-center py-0.5 text-on-surface-variant">
                                <span>Biaya Layanan</span>
                                <span class="font-semibold text-on-surface">Rp {{ number_format($transactionFee, 0, ',', '.') }}</span>
                            </div>
                        @endif

                        {{-- 8. Total Bayar (Total Tagihan Akhir) --}}
                        <div class="border-t border-outline-variant/60 pt-2.5 flex justify-between items-center text-sm font-bold text-on-surface">
                            <div>
                                <span class="block">Total Bayar</span>
                                <span class="text-[10px] text-on-surface-variant font-normal">Sudah termasuk PPN, diskon & ongkir</span>
                            </div>
                            <span class="text-base text-primary font-mono">Rp {{ number_format($orderFinalTotal, 0, ',', '.') }}</span>
                        </div>
                    </div>
                </div>
            </div>

        </div>

    </div>

    <!-- Card: Riwayat Aktivitas & Order Logs (Full Width) -->
    <div class="bg-white rounded-xl border border-outline-variant/60 shadow-xs overflow-hidden">
        <!-- Header -->
        <div class="p-4 border-b border-outline-variant/40 flex flex-wrap items-center justify-between gap-3 bg-surface-container-lowest">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-lg bg-surface-container text-on-surface-variant flex items-center justify-center">
                    <span class="material-symbols-outlined text-[19px]">history</span>
                </div>
                <div>
                    <h3 class="font-bold text-sm text-on-surface">Riwayat Aktivitas & Log Pesanan</h3>
                    <p class="text-[11px] text-on-surface-variant">Catatan audit trail seluruh aktivitas, perubahan status, pembayaran, resi, dan integrasi JDE</p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <span class="px-2.5 py-1 rounded-full text-xs font-medium bg-surface-container text-on-surface-variant border border-outline-variant/50">
                    {{ $order->logs->count() }} Aktivitas
                </span>
            </div>
        </div>

        <!-- Tabel Log Aktivitas -->
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-surface-container-low/70 border-b border-outline-variant/40 text-[11px] font-bold text-on-surface-variant uppercase tracking-wider">
                        <th class="py-2.5 px-3 w-10 text-center">No</th>
                        <th class="py-2.5 px-3 min-w-[150px]">Waktu</th>
                        <th class="py-2.5 px-3 min-w-[160px]">Aktivitas</th>
                        <th class="py-2.5 px-3 min-w-[200px]">Perubahan Status</th>
                        <th class="py-2.5 px-3 min-w-[280px]">Catatan / Keterangan</th>
                        <th class="py-2.5 px-3 min-w-[130px]">Creator</th>
                        <th class="py-2.5 px-3 min-w-[130px] text-right">Editor</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-outline-variant/20">
                    @forelse($order->logs as $index => $log)
                        <tr class="hover:bg-surface-container-low/40 transition-colors">
                            <!-- No -->
                            <td class="py-3 px-3 text-center text-on-surface-variant font-medium align-top">
                                {{ $index + 1 }}
                            </td>

                            <!-- Waktu -->
                            <td class="py-3 px-3 align-top whitespace-nowrap">
                                <div class="font-bold text-on-surface text-xs">
                                    {{ $log->created_at ? $log->created_at->format('d/m/Y H:i:s') : '-' }}
                                </div>
                                <div class="text-[10px] text-on-surface-variant mt-0.5">
                                    {{ $log->created_at ? $log->created_at->diffForHumans() : '' }}
                                </div>
                            </td>

                            <!-- Aktivitas / Kategori -->
                            <td class="py-3 px-3 align-top whitespace-nowrap">
                                <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded text-[11px] font-medium bg-surface-container text-on-surface border border-outline-variant/60">
                                    <span class="material-symbols-outlined text-[14px] text-on-surface-variant">{{ $log->action_icon }}</span>
                                    <span>{{ $log->action_label }}</span>
                                </span>
                            </td>

                            <!-- Perubahan Status (From -> To) -->
                            <td class="py-3 px-3 align-top">
                                @if($log->human_status_from || $log->human_status_to)
                                    <div class="flex items-center gap-1.5 text-xs flex-wrap">
                                        @if($log->human_status_from)
                                            <span class="px-2 py-0.5 rounded bg-surface-container text-on-surface-variant font-medium text-[10px] border border-outline-variant/40">
                                                {{ $log->human_status_from }}
                                            </span>
                                            <span class="material-symbols-outlined text-[13px] text-outline">arrow_forward</span>
                                        @endif
                                        <span class="px-2 py-0.5 rounded bg-surface-container text-on-surface font-medium text-[10px] border border-outline-variant/50">
                                            {{ $log->human_status_to ?: '-' }}
                                        </span>
                                    </div>
                                @else
                                    <span class="text-on-surface-variant opacity-40">-</span>
                                @endif
                            </td>

                            <!-- Catatan / Keterangan -->
                            <td class="py-3 px-3 align-top">
                                <div class="text-xs text-on-surface leading-relaxed">
                                    {{ $log->notes ?: '-' }}
                                </div>
                            </td>

                            <!-- Creator -->
                            <td class="py-3 px-3 align-top whitespace-nowrap">
                                <div class="font-bold text-xs text-on-surface">
                                    {{ $log->creator ?: '-' }}
                                </div>
                                @if(($log->creator && in_array(strtolower($log->creator), ['system', 'queue_worker', 'cron_scheduler'])))
                                    <span class="text-[10px] text-on-surface-variant opacity-80 block">
                                        Automated Process
                                    </span>
                                @endif
                            </td>

                            <!-- Editor -->
                            <td class="py-3 px-3 text-right align-top whitespace-nowrap">
                                <div class="font-bold text-xs text-on-surface">
                                    {{ $log->editor ?: '-' }}
                                </div>
                                @if(($log->editor && in_array(strtolower($log->editor), ['system', 'queue_worker', 'cron_scheduler'])))
                                    <span class="text-[10px] text-on-surface-variant opacity-80 block">
                                        Automated Process
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-8 text-center text-on-surface-variant">
                                <div class="flex flex-col items-center justify-center gap-2">
                                    <span class="material-symbols-outlined text-4xl text-outline-variant">history_toggle_off</span>
                                    <p class="text-xs font-semibold">Belum ada riwayat aktivitas tercatat untuk pesanan ini.</p>
                                    <p class="text-[11px] text-outline">Setiap perubahan status, pembayaran, dan integrasi JDE akan terekam secara otomatis di sini.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
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

async function confirmCompleteOrder(orderId, orderNumber) {
    const doComplete = async () => {
        const topBtn = document.getElementById('btnCompleteOrderTop');
        const sidebarBtn = document.getElementById('btnCompleteOrderSidebar');
        if (topBtn) {
            topBtn.disabled = true;
            topBtn.innerHTML = '<div class="inline-block animate-spin w-3 h-3 border-2 border-white border-t-transparent rounded-full mr-1.5"></div> Menyelesaikan...';
        }
        if (sidebarBtn) {
            sidebarBtn.disabled = true;
            sidebarBtn.innerHTML = '<div class="inline-block animate-spin w-3 h-3 border-2 border-white border-t-transparent rounded-full mr-1.5"></div> Menyelesaikan...';
        }

        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}';

        try {
            const response = await fetch(`/orders/${orderId}/update-status`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({
                    status: 5 // STATUS_DELIVERED
                })
            });

            const data = await response.json();
            if (response.ok && data.success) {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'success',
                        title: 'Pesanan Selesai!',
                        text: data.message || `Pesanan #${orderNumber} berhasil ditandai sebagai Selesai (Delivered).`,
                        confirmButtonColor: '#4f46e5'
                    }).then(() => {
                        window.location.reload();
                    });
                } else {
                    alert(data.message || `Pesanan #${orderNumber} berhasil ditandai sebagai Selesai (Delivered).`);
                    window.location.reload();
                }
            } else {
                const errMsg = data.message || 'Gagal menyelesaikan pesanan.';
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal',
                        text: errMsg,
                        confirmButtonColor: '#4f46e5'
                    });
                } else {
                    alert(errMsg);
                }
            }
        } catch (err) {
            console.error(err);
            const netErr = 'Terjadi gangguan jaringan saat menyelesaikan pesanan.';
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'error',
                    title: 'Gangguan Koneksi',
                    text: netErr,
                    confirmButtonColor: '#4f46e5'
                });
            } else {
                alert(netErr);
            }
        } finally {
            if (topBtn) {
                topBtn.disabled = false;
                topBtn.innerHTML = '<span class="material-symbols-outlined text-[16px]">task_alt</span><span>Selesaikan Pesanan</span>';
            }
            if (sidebarBtn) {
                sidebarBtn.disabled = false;
                sidebarBtn.innerHTML = '<span class="material-symbols-outlined text-[16px]">task_alt</span><span>Selesaikan Pesanan (Tandai Diterima)</span>';
            }
        }
    };

    if (typeof Swal !== 'undefined') {
        Swal.fire({
            icon: 'question',
            title: 'Selesaikan Pesanan?',
            text: `Apakah barang pesanan #${orderNumber} sudah sampai dan diterima dengan baik oleh pelanggan? Status akan diubah menjadi Selesai (Delivered).`,
            showCancelButton: true,
            confirmButtonColor: '#4f46e5',
            cancelButtonColor: '#64748b',
            confirmButtonText: 'Ya, Selesaikan Pesanan',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                doComplete();
            }
        });
    } else if (confirm(`Apakah barang pesanan #${orderNumber} sudah sampai dan diterima oleh pelanggan? Status akan diubah menjadi Selesai (Delivered).`)) {
        doComplete();
    }
}

async function confirmPushToJde(orderId, orderNumber) {
    const doPush = async () => {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}';
        const topBtn = document.getElementById('btnPushToJdeTop');
        const cardBtn = document.getElementById('btnPushToJdeCard');
        if (topBtn) {
            topBtn.disabled = true;
            topBtn.innerHTML = '<div class="inline-block animate-spin w-3 h-3 border-2 border-white border-t-transparent rounded-full mr-1.5"></div> Mengirim...';
        }
        if (cardBtn) {
            cardBtn.disabled = true;
            cardBtn.innerHTML = '<div class="inline-block animate-spin w-3 h-3 border-2 border-white border-t-transparent rounded-full mr-1.5"></div> Mengirim...';
        }

        try {
            const response = await fetch(`/orders/${orderId}/push-to-jde`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                }
            });

            const data = await response.json();
            if (response.ok && data.success) {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil Push ke JDE!',
                        text: data.message || `Pesanan #${orderNumber} berhasil dikirim ke JDE.`,
                        confirmButtonColor: '#4f46e5'
                    }).then(() => {
                        window.location.reload();
                    });
                } else {
                    alert(data.message || `Pesanan #${orderNumber} berhasil dikirim ke JDE.`);
                    window.location.reload();
                }
            } else {
                const errMsg = data.message || 'Gagal melakukan push order ke JDE.';
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal Push ke JDE',
                        text: errMsg,
                        confirmButtonColor: '#4f46e5'
                    });
                } else {
                    alert(errMsg);
                }
            }
        } catch (err) {
            console.error(err);
            const errNet = 'Terjadi kesalahan jaringan saat menghubungi endpoint JDE.';
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'error',
                    title: 'Gangguan Koneksi',
                    text: errNet,
                    confirmButtonColor: '#4f46e5'
                });
            } else {
                alert(errNet);
            }
        } finally {
            if (topBtn) {
                topBtn.disabled = false;
                topBtn.innerHTML = '<span class="material-symbols-outlined text-[16px]">cloud_upload</span><span id="btnPushToJdeTopText">Push ke JDE</span>';
            }
            if (cardBtn) {
                cardBtn.disabled = false;
                cardBtn.innerHTML = '<span class="material-symbols-outlined text-[14px]">cloud_upload</span><span>Push JDE</span>';
            }
        }
    };

    if (typeof Swal !== 'undefined') {
        Swal.fire({
            icon: 'question',
            title: 'Kirim / Retry ke JDE?',
            html: `Pengiriman otomatis ditangani oleh Queue Worker. Jalankan pengiriman manual/retry sekarang untuk pesanan <b>#${orderNumber}</b>?`,
            showCancelButton: true,
            confirmButtonColor: '#4f46e5',
            cancelButtonColor: '#64748b',
            confirmButtonText: 'Ya, Kirim Sekarang',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                doPush();
            }
        });
    } else if (confirm(`Jalankan pengiriman manual/retry pesanan #${orderNumber} ke JDE sekarang?`)) {
        doPush();
    }
}

async function adjustJdeStatus(orderId, orderNumber, currentStatus) {
    const statusOptions = {
        0: '0 - Pending',
        1: '1 - Queued',
        2: '2 - Success',
        3: '3 - Failed'
    };

    let selectHtml = `<select id="swalAdjustJdeStatus" class="swal2-input" style="display:flex; width:80%; margin:10px auto;">`;
    for (const [val, label] of Object.entries(statusOptions)) {
        const selected = parseInt(val) === parseInt(currentStatus) ? 'selected' : '';
        selectHtml += `<option value="${val}" ${selected}>${label}</option>`;
    }
    selectHtml += `</select>`;
    selectHtml += `<textarea id="swalAdjustJdeNotes" class="swal2-textarea" placeholder="Catatan perubahan status JDE (opsional)" style="width:80%; margin:10px auto;"></textarea>`;

    if (typeof Swal !== 'undefined') {
        Swal.fire({
            title: 'Sesuaikan Status JDE',
            html: `<p class="text-sm text-gray-600 mb-2">Pilih status JDE baru untuk pesanan <b>#${orderNumber}</b>:</p>${selectHtml}`,
            showCancelButton: true,
            confirmButtonColor: '#0B2044',
            cancelButtonColor: '#64748b',
            confirmButtonText: 'Simpan Status',
            cancelButtonText: 'Batal',
            preConfirm: () => {
                const newStatus = document.getElementById('swalAdjustJdeStatus').value;
                const notes = document.getElementById('swalAdjustJdeNotes').value;
                return { newStatus, notes };
            }
        }).then(async (result) => {
            if (result.isConfirmed) {
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}';
                try {
                    const response = await fetch(`/orders/${orderId}/update-jde-status`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrfToken
                        },
                        body: JSON.stringify({
                            jde_push_status: result.value.newStatus,
                            notes: result.value.notes
                        })
                    });

                    const data = await response.json();
                    if (response.ok && data.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Status Diperbarui',
                            text: data.message,
                            confirmButtonColor: '#0B2044'
                        }).then(() => {
                            window.location.reload();
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal Memperbarui',
                            text: data.message || 'Gagal memperbarui status JDE.',
                            confirmButtonColor: '#0B2044'
                        });
                    }
                } catch (err) {
                    console.error(err);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Terjadi kesalahan sistem saat menghubungi server.',
                        confirmButtonColor: '#0B2044'
                    });
                }
            }
        });
    } else {
        const newStatus = prompt(`Pilih status JDE baru untuk #${orderNumber} (0: Belum Di Push, 1: Antrian, 2: Berhasil, 3: Gagal):`, currentStatus);
        if (newStatus !== null) {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}';
            fetch(`/orders/${orderId}/update-jde-status`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({
                    jde_push_status: parseInt(newStatus)
                })
            }).then(() => window.location.reload());
        }
    }
}
</script>
@endsection
