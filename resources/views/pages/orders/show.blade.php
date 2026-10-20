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
    <div class="flex flex-wrap items-center justify-end gap-2.5">
        <!-- Integrasi JDE (Aktif untuk pesanan yang sudah bayar / dimulai) -->
        <div id="jdeTopActionContainer" class="{{ ($order->isPaidOrStarted() || (int)$order->jde_push_status > 0) ? 'inline-flex' : 'hidden' }} items-center gap-1.5">
            @if((int)$order->jde_push_status === \App\Models\Order\Order::JDE_STATUS_SUCCESS)
                <span class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-surface-container text-on-surface rounded-lg text-xs font-medium border border-outline-variant/60" title="Pesanan sudah berhasil di-push ke JDE{{ $order->jde_push_date ? ' pada ' . $order->jde_push_date->format('d/m/Y') : '' }}">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                    <span>Sudah di Push</span>
                </span>
            @elseif((int)$order->jde_push_status === \App\Models\Order\Order::JDE_STATUS_PROCESSING)
                <span class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-surface-container text-on-surface rounded-lg text-xs font-medium border border-outline-variant/60" title="Sedang diproses oleh Queue Worker">
                    <span class="w-1.5 h-1.5 rounded-full bg-blue-500 animate-pulse"></span>
                    <span>Dalam Antrian</span>
                </span>
            @elseif((int)$order->jde_push_status === \App\Models\Order\Order::JDE_STATUS_FAILED)
                <button type="button" id="btnPushToJdeTop" onclick="confirmPushToJde('{{ $order->id }}', '{{ $order->order_number }}')" class="inline-flex items-center gap-1.5 px-4 py-2 bg-primary text-white hover:opacity-90 rounded-lg text-xs font-semibold shadow-xs transition-all" title="Gagal terkirim ke JDE. Klik untuk Retry manual.">
                    <span class="material-symbols-outlined text-[16px]">refresh</span>
                    <span id="btnPushToJdeTopText">Retry JDE</span>
                </button>
            @else
                <button type="button" id="btnPushToJdeTop" onclick="confirmPushToJde('{{ $order->id }}', '{{ $order->order_number }}')" class="inline-flex items-center gap-1.5 px-4 py-2 bg-primary text-white hover:opacity-90 rounded-lg text-xs font-semibold shadow-xs transition-all" title="Pengiriman otomatis ditangani Queue Worker. Klik untuk kirim manual sekarang.">
                    <span class="material-symbols-outlined text-[16px]">cloud_upload</span>
                    <span id="btnPushToJdeTopText">Push ke JDE</span>
                </button>
            @endif
        </div>

        @if($order->resi || $order->delivery_status)
            <button type="button" onclick='openResiModal(@json($resiModalData), "tracking")' class="inline-flex items-center gap-1.5 px-4 py-2 bg-primary text-white hover:opacity-90 rounded-lg text-xs font-semibold shadow-xs transition-all">
                <span class="material-symbols-outlined text-[16px]">radar</span>
                <span>Lacak Pengiriman</span>
            </button>
        @endif

        @if(empty($resiModalData['is_kurir_toko']) && empty($resiModalData['has_biteship_resi']) && $order->status < \App\Models\Order\Order::STATUS_SHIPPED)
            <button type="button" onclick='openResiModal(@json($resiModalData), "biteship")' class="inline-flex items-center gap-1.5 px-4 py-2 bg-primary text-white hover:opacity-90 rounded-lg text-xs font-semibold shadow-xs transition-all">
                <span class="material-symbols-outlined text-[16px]">cloud_sync</span>
                <span>Get Resi Otomatis</span>
            </button>
        @endif

        @if($order->status < \App\Models\Order\Order::STATUS_SHIPPED)
            <button type="button" onclick='openResiModal(@json($resiModalData), "manual")' class="inline-flex items-center gap-1.5 px-4 py-2 bg-primary text-white hover:opacity-90 rounded-lg text-xs font-semibold shadow-xs transition-all">
                <span class="material-symbols-outlined text-[16px]">edit_document</span>
                <span id="show-header-resi-text">{{ $order->resi ? 'Ubah Resi' : 'Input Resi' }}</span>
            </button>
        @endif

        @if((int)$order->status === \App\Models\Order\Order::STATUS_SHIPPED)
            <button type="button" id="btnCompleteOrderTop" onclick="confirmCompleteOrder('{{ $order->id }}', '{{ $order->order_number }}')" class="inline-flex items-center gap-1.5 px-4 py-2 bg-primary text-white hover:opacity-90 rounded-lg text-xs font-semibold shadow-xs transition-all" title="Pesanan telah sampai dan diterima dengan baik oleh pelanggan">
                <span class="material-symbols-outlined text-[16px]">task_alt</span>
                <span>Selesaikan Pesanan</span>
            </button>
        @endif

        <a href="{{ route('orders.index') }}" class="inline-flex items-center gap-1.5 px-4 py-2 border border-outline-variant bg-white text-on-surface hover:bg-surface-container rounded-lg text-xs font-semibold transition-all">
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
                        <span class="text-[10px] text-on-surface-variant font-semibold block uppercase">Nomor Pesanan</span>
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

                @if($order->notes)
                    <div class="p-2.5 bg-surface-container-low border border-outline-variant/40 rounded-lg text-xs text-on-surface">
                        <span class="text-[10px] font-semibold uppercase block text-on-surface-variant">Catatan Order:</span>
                        <p class="mt-0.5 italic text-on-surface">{{ $order->notes }}</p>
                    </div>
                @endif
            </div>

            <!-- Card: Kurir & Nomor Resi -->
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
                        <div class="grid grid-cols-2 gap-2 pt-1">
                            <button type="button" onclick='openResiModal(@json($resiModalData), "manual")' class="py-2 px-3 bg-primary text-white hover:opacity-90 rounded-lg text-xs font-semibold shadow-xs transition-all flex items-center justify-center gap-1.5">
                                <span class="material-symbols-outlined text-[16px]">edit_document</span>
                                <span>{{ $order->resi ? 'Ubah Resi' : 'Input Resi' }}</span>
                            </button>
                            @if(empty($resiModalData['is_kurir_toko']) && empty($resiModalData['has_biteship_resi']))
                                <button type="button" onclick='openResiModal(@json($resiModalData), "biteship")' class="py-2 px-3 bg-primary text-white hover:opacity-90 rounded-lg text-xs font-semibold shadow-xs transition-all flex items-center justify-center gap-1.5">
                                    <span class="material-symbols-outlined text-[16px]">cloud_sync</span>
                                    <span>Get Resi Otomatis</span>
                                </button>
                            @endif
                        </div>
                    @endif

                    @if($order->resi || $order->delivery_status)
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

                    <!-- Integrasi JDE (Aktif untuk pesanan yang sudah bayar / dimulai) -->
                    <div id="jdeCardSection" class="{{ ($order->isPaidOrStarted() || (int)$order->jde_push_status > 0) ? 'block' : 'hidden' }} pt-2 border-t border-outline-variant/40 space-y-2">
                        <div class="flex items-center justify-between">
                            <span class="text-[10px] text-on-surface-variant font-semibold flex items-center gap-1">
                                <span class="material-symbols-outlined text-[14px] text-on-surface-variant">sync_alt</span>
                                Integrasi JDE
                            </span>
                            <span id="show-jde-status-badge" class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-medium bg-surface-container text-on-surface border border-outline-variant/60">
                                <span class="w-1.5 h-1.5 rounded-full {{ (int)$order->jde_push_status === \App\Models\Order\Order::JDE_STATUS_SUCCESS ? 'bg-emerald-500' : ((int)$order->jde_push_status === \App\Models\Order\Order::JDE_STATUS_PROCESSING ? 'bg-blue-500 animate-pulse' : ((int)$order->jde_push_status === \App\Models\Order\Order::JDE_STATUS_FAILED ? 'bg-rose-500' : 'bg-amber-500')) }}"></span>
                                <span>{{ $order->jdePushStatusLabel() }}</span>
                            </span>
                        </div>
                        <div class="p-2.5 bg-surface-container-low rounded-lg border border-outline-variant/40 flex items-center justify-between gap-2">
                            <div class="text-[11px] text-on-surface">
                                <div class="font-medium text-on-surface" id="show-jde-status-title">
                                    @if((int)$order->jde_push_status === \App\Models\Order\Order::JDE_STATUS_SUCCESS)
                                        Sudah di Push
                                    @elseif((int)$order->jde_push_status === \App\Models\Order\Order::JDE_STATUS_PROCESSING)
                                        Dalam Antrian
                                    @elseif((int)$order->jde_push_status === \App\Models\Order\Order::JDE_STATUS_FAILED)
                                        Gagal di Push
                                    @else
                                        Belum Di Push
                                    @endif
                                </div>
                                <div class="text-[10px] text-on-surface-variant" id="show-jde-status-sub">
                                    @if($order->jde_push_date)
                                        Terkirim pada {{ $order->jde_push_date->format('d M Y') }}
                                    @elseif((int)$order->jde_push_status === \App\Models\Order\Order::JDE_STATUS_FAILED)
                                        Percobaan gagal: {{ $order->meta['jde_push_retry_count'] ?? 1 }}x
                                    @else
                                        Queue worker otomatis memproses pesanan ini
                                    @endif
                                </div>
                            </div>
                            <div id="jdeCardButtonContainer">
                                @if((int)$order->jde_push_status === \App\Models\Order\Order::JDE_STATUS_SUCCESS)
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 bg-surface-container text-on-surface rounded-md text-[11px] font-medium border border-outline-variant/60">
                                        <span class="material-symbols-outlined text-[13px] text-emerald-600">check</span>
                                        Sudah di Push
                                    </span>
                                @elseif((int)$order->jde_push_status === \App\Models\Order\Order::JDE_STATUS_PROCESSING)
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 bg-surface-container text-on-surface rounded-md text-[11px] font-medium border border-outline-variant/60">
                                        <span class="material-symbols-outlined text-[13px] text-blue-600 animate-spin">sync</span>
                                        Dalam Antrian
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
                        </div>
                    </div>
                </div>
            </div>

            <!-- Card: Alamat Tujuan & Kontak Pelanggan -->
            <div class="bg-white rounded-xl border border-outline-variant/60 shadow-xs p-4 space-y-3">
                <div class="flex items-center justify-between border-b border-outline-variant/40 pb-2.5">
                    <span class="font-bold text-xs text-on-surface uppercase tracking-wider flex items-center gap-1.5">
                        <span class="material-symbols-outlined text-[16px] text-on-surface-variant">location_on</span>
                        Alamat Pengiriman
                    </span>
                    @if(!empty($shippingMeta['postal_code']))
                        <span class="text-[10px] font-mono font-medium px-2 py-0.5 rounded bg-surface-container text-on-surface border border-outline-variant/40">
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
                                <a href="https://wa.me/{{ $waPhone }}" target="_blank" class="inline-flex items-center gap-1 px-2.5 py-1 bg-white hover:bg-surface-container border border-outline-variant text-on-surface rounded text-[11px] font-medium transition-colors">
                                    <span class="material-symbols-outlined text-[13px] text-emerald-600">chat</span>
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
                            <input type="hidden" name="status" value="2">
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
                    <span class="font-bold text-xs text-on-surface uppercase tracking-wider flex items-center gap-1.5">
                        <span class="material-symbols-outlined text-[17px] text-on-surface-variant">inventory_2</span>
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

                                    <!-- Unit Price (Harga Asli) -->
                                    <td class="py-3 px-3 text-right font-medium text-on-surface align-top whitespace-nowrap">
                                        <div>Rp {{ number_format($price, 0, ',', '.') }}</div>
                                        @if($qty > 1)
                                            <div class="text-[10px] text-on-surface-variant font-normal mt-0.5">Total Asli: Rp {{ number_format($sub, 0, ',', '.') }}</div>
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

        <!-- Order Activity Highlights Strip -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-3 p-4 bg-surface-container-low/30 border-b border-outline-variant/40 text-xs">
            <!-- 1. Status Pesanan -->
            <div class="bg-white p-3 rounded-lg border border-outline-variant/40 space-y-1">
                <div class="text-[10px] uppercase font-semibold text-on-surface-variant flex items-center gap-1">
                    <span class="material-symbols-outlined text-[14px]">order_approve</span>
                    Status Pesanan
                </div>
                <div class="flex items-center gap-2">
                    <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded text-[11px] font-medium bg-surface-container text-on-surface border border-outline-variant/60">
                        <span class="w-1.5 h-1.5 rounded-full {{ in_array((int)$order->status, [4, 5]) ? 'bg-emerald-500' : (in_array((int)$order->status, [6, 7]) ? 'bg-rose-500' : ((int)$order->status === 0 ? 'bg-slate-400' : 'bg-blue-500')) }}"></span>
                        <span>{{ $order->statusLabel() }}</span>
                    </span>
                    <span class="text-[10px] text-on-surface-variant">
                        Update: {{ $order->updated_at?->format('d/m/Y H:i') }}
                    </span>
                </div>
            </div>

            <!-- 2. Status Pembayaran -->
            <div class="bg-white p-3 rounded-lg border border-outline-variant/40 space-y-1">
                <div class="text-[10px] uppercase font-semibold text-on-surface-variant flex items-center gap-1">
                    <span class="material-symbols-outlined text-[14px]">payments</span>
                    Pembayaran
                </div>
                <div class="flex items-center gap-2 flex-wrap">
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[11px] font-medium bg-surface-container text-on-surface border border-outline-variant/60">
                        <span class="w-1.5 h-1.5 rounded-full {{ $order->isPaymentPaid() ? 'bg-emerald-500' : 'bg-amber-500' }}"></span>
                        <span>{{ $order->paymentStatusLabel() }}</span>
                    </span>
                    <span class="text-[11px] font-medium text-on-surface">
                        {{ $order->payment_method_name }}
                    </span>
                </div>
            </div>

            <!-- 3. Status JDE -->
            <div class="bg-white p-3 rounded-lg border border-outline-variant/40 space-y-1">
                <div class="text-[10px] uppercase font-semibold text-on-surface-variant flex items-center gap-1">
                    <span class="material-symbols-outlined text-[14px]">cloud_sync</span>
                    Integrasi JDE
                </div>
                <div class="flex items-center gap-2 flex-wrap">
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[11px] font-medium bg-surface-container text-on-surface border border-outline-variant/60">
                        <span class="w-1.5 h-1.5 rounded-full {{ (int)$order->jde_push_status === \App\Models\Order\Order::JDE_STATUS_SUCCESS ? 'bg-emerald-500' : ((int)$order->jde_push_status === \App\Models\Order\Order::JDE_STATUS_PROCESSING ? 'bg-blue-500 animate-pulse' : ((int)$order->jde_push_status === \App\Models\Order\Order::JDE_STATUS_FAILED ? 'bg-rose-500' : 'bg-amber-500')) }}"></span>
                        <span>{{ $order->jdePushStatusLabel }}</span>
                    </span>
                    @if($order->jde_push_date)
                        <span class="text-[10px] text-on-surface-variant">
                            {{ $order->jde_push_date->format('d/m/Y') }}
                        </span>
                    @endif
                </div>
                @if((int)$order->jde_push_status === \App\Models\Order\Order::JDE_STATUS_FAILED && !empty($order->getJdeErrorMessage()))
                    <p class="text-[10px] text-rose-600 line-clamp-1 mt-0.5" title="{{ $order->getJdeErrorMessage() }}">
                        {{ $order->getJdeErrorMessage() }}
                    </p>
                @endif
            </div>

            <!-- 4. Pengiriman & Resi -->
            <div class="bg-white p-3 rounded-lg border border-outline-variant/40 space-y-1">
                <div class="text-[10px] uppercase font-semibold text-on-surface-variant flex items-center gap-1">
                    <span class="material-symbols-outlined text-[14px]">local_shipping</span>
                    Pengiriman
                </div>
                <div class="text-[11px] text-on-surface flex items-center gap-1.5 flex-wrap">
                    <span class="font-medium">{{ $order->courier_name ?? 'Kurir Belum Ditentukan' }}</span>
                    @if($order->resi)
                        <span class="font-mono px-1.5 py-0.2 bg-surface-container rounded text-[10px] text-on-surface font-medium border border-outline-variant/40">
                            {{ $order->resi }}
                        </span>
                    @endif
                </div>
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
</script>
@endsection
