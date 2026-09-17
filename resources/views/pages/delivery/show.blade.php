@extends('layouts.app')

@section('title', 'Delivery Detail')

@section('content')
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
        <div>
            <div class="flex items-center gap-3">
                <h1 class="font-headline-lg text-headline-lg text-on-surface">Delivery #{{ substr($delivery->id, 0, 8) }}</h1>
                @php
                    $statusMap = [
                        'pending' => 'bg-surface-container text-on-surface-variant border-outline-variant/30',
                        'in_transit' => 'bg-primary/10 text-primary border-primary/20',
                        'delivered' => 'bg-success/10 text-success border-success/20',
                        'failed' => 'bg-danger/10 text-danger border-danger/20',
                        'returned' => 'bg-warning/10 text-warning border-warning/20'
                    ];
                    $statusBadge = $statusMap[$delivery->status] ?? 'bg-surface-container text-on-surface-variant border-outline-variant/30';
                @endphp
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold border {{ $statusBadge }}">
                    <span class="w-1.5 h-1.5 rounded-full bg-current"></span> {{ ucfirst(str_replace('_', ' ', $delivery->status)) }}
                </span>
            </div>
            <nav class="flex items-center gap-2 text-label-sm text-on-surface-variant mt-1 font-medium">
                <a href="{{ route('dashboard') }}" class="hover:text-primary transition-colors">eCommerce</a>
                <span class="material-symbols-outlined text-[14px]">chevron_right</span>
                <span>Pick & Pack</span>
                <span class="material-symbols-outlined text-[14px]">chevron_right</span>
                <a href="{{ route('delivery.index') }}" class="hover:text-primary transition-colors">Delivery</a>
                <span class="material-symbols-outlined text-[14px]">chevron_right</span>
                <span class="text-on-surface">#{{ substr($delivery->id, 0, 8) }}</span>
            </nav>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('delivery.index') }}" class="inline-flex items-center gap-1.5 px-4 py-2 border border-outline-variant text-on-surface-variant hover:bg-surface-container rounded-xl text-xs font-semibold transition-colors">
                <span class="material-symbols-outlined text-[16px]">arrow_back</span>
                <span>Kembali</span>
            </a>
            @if($delivery->status !== 'delivered')
                <form action="{{ route('delivery.update-status', $delivery->id) }}" method="POST" class="inline">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="status" value="delivered">
                    <button type="submit" class="btn-save inline-flex items-center gap-2 px-5 py-2 bg-success text-white hover:opacity-90 rounded-xl text-xs font-bold transition-all shadow-sm active:scale-95" onclick="return confirm('Tandai pengiriman ini telah selesai (delivered)?');">
                        <span class="material-symbols-outlined text-[18px]">check_circle</span>
                        <span>Mark Delivered</span>
                    </button>
                </form>
            @endif
        </div>
    </div>

    @include('layouts.partials.pick-pack-submenu')

    <div class="space-y-6">
        <div class="bg-white rounded-2xl shadow-sm border border-outline-variant/30 p-6">
            <h3 class="text-base font-bold text-on-surface mb-4 pb-2 border-b border-outline-variant/30 flex items-center gap-2">
                <span class="material-symbols-outlined text-primary text-[20px]">local_shipping</span>
                Informasi Pengiriman
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <p class="text-label-sm text-on-surface-variant mb-1 font-medium">Order</p>
                    @if($delivery->order)
                        <a href="{{ route('orders.show', $delivery->order->id) }}" class="font-headline-md text-headline-md text-primary font-bold hover:underline font-mono">
                            {{ $delivery->order->order_number ?? '#' . substr($delivery->order->id, 0, 8) }}
                        </a>
                    @else
                        <p class="font-body-md text-body-md text-on-surface-variant">-</p>
                    @endif
                </div>
                <div>
                    <p class="text-label-sm text-on-surface-variant mb-1 font-medium">Customer</p>
                    <p class="font-body-md text-body-md text-on-surface font-semibold">{{ $delivery->order?->customer?->name ?? 'N/A' }}</p>
                </div>
                <div>
                    <p class="text-label-sm text-on-surface-variant mb-1 font-medium">Courier</p>
                    <p class="font-body-md text-body-md text-on-surface">{{ $delivery->courier->name ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-label-sm text-on-surface-variant mb-1 font-medium">Tracking Number</p>
                    <p class="font-body-md text-body-md text-on-surface font-mono font-semibold">{{ $delivery->tracking_number ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-label-sm text-on-surface-variant mb-1 font-medium">Driver</p>
                    <p class="font-body-md text-body-md text-on-surface">{{ $delivery->driver_name ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-label-sm text-on-surface-variant mb-1 font-medium">Status Pengiriman</p>
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold border {{ $statusBadge }}">
                        <span class="w-1.5 h-1.5 rounded-full bg-current"></span> {{ ucfirst(str_replace('_', ' ', $delivery->status)) }}
                    </span>
                </div>
            </div>
        </div>

        @if($delivery->order && $delivery->order->items && $delivery->order->items->isNotEmpty())
            <div class="bg-white rounded-2xl shadow-sm border border-outline-variant/30 p-6">
                <h3 class="text-base font-bold text-on-surface mb-4 pb-2 border-b border-outline-variant/30 flex items-center gap-2">
                    <span class="material-symbols-outlined text-primary text-[20px]">inventory_2</span>
                    Daftar Produk yang Dikirim
                </h3>
                <div class="divide-y divide-outline-variant/20">
                    @foreach($delivery->order->items as $item)
                        <div class="py-3 flex items-center justify-between">
                            <div>
                                <h4 class="font-bold text-on-surface text-body-md">{{ $item->product?->name ?? 'Product' }}</h4>
                                <p class="text-label-sm text-on-surface-variant">Qty: {{ $item->quantity }}</p>
                            </div>
                            <span class="text-body-md font-bold text-on-surface">Rp{{ number_format($item->unit_price ?? $item->price ?? 0, 0, ',', '.') }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        @php
            $order = $delivery->order;
            $biteshipPayload = $order?->meta['biteship_payload'] 
                ?? $order?->meta['biteship_request_payload'] 
                ?? null;
            $biteshipShipment = $order?->meta['biteship_shipment'] ?? null;
            $biteshipOrderId = $order?->meta['biteship_order_id'] ?? ($biteshipShipment['id'] ?? null);

            if (!$biteshipPayload && !empty($deliveryLogs)) {
                $createdLog = $deliveryLogs->first(function ($l) {
                    return ($l->event === 'order.created' || !empty($l->biteship_order_id)) 
                        && is_array($l->payload) 
                        && (isset($l->payload['items']) || isset($l->payload['courier_company']));
                });
                if ($createdLog) {
                    $biteshipPayload = $createdLog->payload;
                }
            }

            $isBiteship = !empty($biteshipOrderId) 
                || ($order?->meta['fulfillment_type'] ?? '') === 'biteship'
                || !empty($biteshipShipment)
                || !empty($biteshipPayload);

            if (!$biteshipPayload && $isBiteship && $order) {
                try {
                    $biteshipService = app(\App\Services\BiteshipService::class);
                    $courierCompany = $order->courier?->code ?? $delivery->courier?->code;
                    $biteshipPayload = $biteshipService->buildOrderPayload($order, $courierCompany);
                } catch (\Throwable $e) {}
            }
        @endphp

        <!-- BITESHIP OUTGOING PAYLOAD CARD -->
        <div class="bg-white rounded-2xl shadow-sm border border-outline-variant/30 p-6">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-4 pb-3 border-b border-outline-variant/30">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-xl bg-purple-50 text-purple-700 flex items-center justify-center font-bold">
                        <span class="material-symbols-outlined text-[20px]">terminal</span>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-on-surface">Payload Keluar untuk Biteship</h3>
                        <p class="text-xs text-on-surface-variant">Data request body yang dikirimkan ke Biteship API untuk pembuatan resi & order pengiriman</p>
                    </div>
                </div>
                @if($biteshipPayload)
                    <button type="button" onclick="copyBiteshipPayload()" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-purple-50 hover:bg-purple-100 text-purple-700 border border-purple-200 rounded-lg text-xs font-bold transition-colors">
                        <span class="material-symbols-outlined text-[15px]" id="copyPayloadIcon">content_copy</span>
                        <span id="copyPayloadText">Salin Payload JSON</span>
                    </button>
                @endif
            </div>

            @if($biteshipPayload)
                <!-- Payload Metadata Badges -->
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-4">
                    <div class="p-3 bg-surface-container-low rounded-xl border border-outline-variant/30">
                        <span class="text-[10px] text-on-surface-variant font-bold uppercase tracking-wider block">Kurir Biteship</span>
                        <span class="text-xs font-bold text-purple-700 font-mono mt-0.5 block uppercase">
                            {{ $biteshipPayload['courier_company'] ?? ($order?->courier?->code ?? '-') }}
                        </span>
                    </div>
                    <div class="p-3 bg-surface-container-low rounded-xl border border-outline-variant/30">
                        <span class="text-[10px] text-on-surface-variant font-bold uppercase tracking-wider block">Layanan</span>
                        <span class="text-xs font-bold text-on-surface font-mono mt-0.5 block uppercase">
                            {{ $biteshipPayload['courier_type'] ?? 'Standard' }}
                        </span>
                    </div>
                    <div class="p-3 bg-surface-container-low rounded-xl border border-outline-variant/30">
                        <span class="text-[10px] text-on-surface-variant font-bold uppercase tracking-wider block">Kode Pos Tujuan</span>
                        <span class="text-xs font-bold text-primary font-mono mt-0.5 block">
                            {{ $biteshipPayload['destination_postal_code'] ?? '-' }}
                        </span>
                    </div>
                    <div class="p-3 bg-surface-container-low rounded-xl border border-outline-variant/30">
                        <span class="text-[10px] text-on-surface-variant font-bold uppercase tracking-wider block">Total Item Paket</span>
                        <span class="text-xs font-bold text-on-surface font-mono mt-0.5 block">
                            {{ isset($biteshipPayload['items']) ? count($biteshipPayload['items']) : 0 }} item
                        </span>
                    </div>
                </div>

                <!-- Origin & Destination Brief -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4 text-xs">
                    <div class="p-3.5 bg-gray-50/70 rounded-xl border border-gray-200/80 space-y-1">
                        <div class="text-[10px] font-bold uppercase tracking-wider text-gray-500 flex items-center gap-1">
                            <span class="material-symbols-outlined text-[13px] text-amber-600">store</span>
                            <span>Asal Pengiriman (Origin)</span>
                        </div>
                        <p class="font-bold text-on-surface">{{ $biteshipPayload['origin_contact_name'] ?? 'Gudang' }} ({{ $biteshipPayload['origin_contact_phone'] ?? '-' }})</p>
                        <p class="text-on-surface-variant leading-relaxed text-[11px]">{{ $biteshipPayload['origin_address'] ?? '-' }}</p>
                        @if(!empty($biteshipPayload['origin_postal_code']))
                            <p class="text-[11px] font-mono text-gray-500">Kode Pos: {{ $biteshipPayload['origin_postal_code'] }}</p>
                        @endif
                    </div>
                    <div class="p-3.5 bg-gray-50/70 rounded-xl border border-gray-200/80 space-y-1">
                        <div class="text-[10px] font-bold uppercase tracking-wider text-gray-500 flex items-center gap-1">
                            <span class="material-symbols-outlined text-[13px] text-emerald-600">location_on</span>
                            <span>Tujuan Pengiriman (Destination)</span>
                        </div>
                        <p class="font-bold text-on-surface">{{ $biteshipPayload['destination_contact_name'] ?? '-' }} ({{ $biteshipPayload['destination_contact_phone'] ?? '-' }})</p>
                        <p class="text-on-surface-variant leading-relaxed text-[11px]">{{ $biteshipPayload['destination_address'] ?? '-' }}</p>
                        @if(!empty($biteshipPayload['destination_postal_code']))
                            <p class="text-[11px] font-mono text-gray-500">Kode Pos: {{ $biteshipPayload['destination_postal_code'] }}</p>
                        @endif
                        @if(!empty($biteshipPayload['destination_note']))
                            <p class="text-[11px] italic text-gray-500">Catatan: {{ $biteshipPayload['destination_note'] }}</p>
                        @endif
                    </div>
                </div>

                <!-- Raw JSON Accordion -->
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-[11px] font-bold text-on-surface-variant uppercase tracking-wider">Raw JSON Request Body</span>
                        <span class="text-[10px] text-on-surface-variant font-mono">application/json</span>
                    </div>
                    <div class="relative">
                        <pre id="rawBiteshipPayloadCode" class="p-4 bg-gray-900 text-gray-100 rounded-xl text-xs font-mono overflow-x-auto leading-relaxed max-h-96 selection:bg-purple-600">{{ json_encode($biteshipPayload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) }}</pre>
                    </div>
                </div>
            @else
                <div class="py-8 text-center bg-surface-container-lowest rounded-xl border border-dashed border-outline-variant/60">
                    <span class="material-symbols-outlined text-[32px] text-on-surface-variant opacity-40 mb-1">local_shipping</span>
                    <p class="text-xs font-bold text-on-surface">Tidak Ada Payload Biteship</p>
                    <p class="text-[11px] text-on-surface-variant mt-1">Pengiriman ini dikelola secara manual atau belum pernah di-hit ke API Biteship.</p>
                </div>
            @endif
        </div>

        <!-- DELIVERY & WEBHOOK LOGS TIMELINE -->
        @if(!empty($deliveryLogs) && $deliveryLogs->isNotEmpty())
            <div class="bg-white rounded-2xl shadow-sm border border-outline-variant/30 p-6">
                <div class="flex items-center justify-between gap-3 mb-4 pb-3 border-b border-outline-variant/30">
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-outlined text-primary text-[20px]">history</span>
                        <h3 class="text-base font-bold text-on-surface">Riwayat & Webhook Logs Pengiriman</h3>
                    </div>
                    <span class="text-xs font-mono text-on-surface-variant">{{ $deliveryLogs->count() }} log tercatat</span>
                </div>

                <div class="space-y-4 relative pl-6 border-l-2 border-primary/20 ml-3">
                    @foreach($deliveryLogs as $index => $log)
                        @php
                            $isLatest = $index === 0;
                            $info = \App\Models\Order\Order::deliveryStatusInfo($log->status, $log->event);
                        @endphp
                        <div class="relative pb-4 last:pb-0">
                            <div class="absolute -left-[31px] top-0.5 w-3.5 h-3.5 rounded-full {{ $isLatest ? 'bg-primary ring-4 ring-primary/20' : 'bg-outline' }}"></div>
                            <div class="flex items-center justify-between gap-2 flex-wrap">
                                <div class="text-[12px] {{ $isLatest ? 'text-primary font-extrabold' : 'text-on-surface font-bold' }}">
                                    {{ $info['label'] ?? ($log->note ?: ($log->status ?: 'Log Checkpoint')) }}
                                </div>
                                <span class="px-2 py-0.5 bg-surface-container text-on-surface-variant rounded text-[10px] font-mono">
                                    {{ $log->event ?: 'webhook' }}
                                </span>
                            </div>
                            <div class="text-[10px] text-on-surface-variant mt-0.5 font-mono">
                                {{ $log->created_at ? $log->created_at->format('d M Y H:i:s') : '-' }}
                                @if($log->location)
                                    &bull; <span class="text-on-surface">{{ $log->location }}</span>
                                @endif
                            </div>
                            @if($log->note)
                                <div class="text-xs text-on-surface mt-1 leading-snug">{{ $log->note }}</div>
                            @endif
                            @if(!empty($log->payload) && is_array($log->payload))
                                <details class="mt-2 text-[10px] text-on-surface-variant font-mono group">
                                    <summary class="cursor-pointer hover:underline text-primary inline-flex items-center gap-1">
                                        <span class="material-symbols-outlined text-[12px] group-open:rotate-90 transition-transform">chevron_right</span>
                                        <span>Lihat Raw Payload Log</span>
                                    </summary>
                                    <pre class="p-2.5 bg-surface-container-lowest border border-outline-variant/30 rounded-lg mt-1 overflow-x-auto text-[11px] leading-relaxed max-h-40">{{ json_encode($log->payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) }}</pre>
                                </details>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>

    @if($biteshipPayload)
        <script>
            function copyBiteshipPayload() {
                const el = document.getElementById('rawBiteshipPayloadCode');
                if (!el) return;
                navigator.clipboard.writeText(el.innerText).then(() => {
                    const btnText = document.getElementById('copyPayloadText');
                    const btnIcon = document.getElementById('copyPayloadIcon');
                    if (btnText) btnText.textContent = 'Tersalin ke Clipboard!';
                    if (btnIcon) btnIcon.textContent = 'check';
                    setTimeout(() => {
                        if (btnText) btnText.textContent = 'Salin Payload JSON';
                        if (btnIcon) btnIcon.textContent = 'content_copy';
                    }, 2500);
                });
            }
        </script>
    @endif
@endsection