@extends('layouts.app')

@section('title', 'Void Order Detail - ' . $voidOrder->order_number)

@section('content')
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
        <div>
            <div class="flex items-center gap-3">
                <h1 class="font-headline-lg text-headline-lg text-on-surface">Void Order #{{ $voidOrder->order_number }}</h1>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-danger/10 text-danger border border-danger/20">
                    VOID
                </span>
            </div>
            <nav class="flex items-center gap-2 text-label-sm text-on-surface-variant mt-1 font-medium">
                <a href="{{ route('dashboard') }}" class="hover:text-primary transition-colors">eCommerce</a>
                <span class="material-symbols-outlined text-[14px]">chevron_right</span>
                <a href="{{ route('orders.index') }}" class="hover:text-primary transition-colors">Orders</a>
                <span class="material-symbols-outlined text-[14px]">chevron_right</span>
                <a href="{{ route('orders.void.index') }}" class="hover:text-primary transition-colors">Void Orders</a>
                <span class="material-symbols-outlined text-[14px]">chevron_right</span>
                <span class="text-on-surface">#{{ $voidOrder->order_number }}</span>
            </nav>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('orders.void.index') }}" class="inline-flex items-center gap-1.5 px-4 py-2 border border-outline-variant text-on-surface-variant hover:bg-surface-container rounded-xl text-xs font-semibold transition-colors">
                <span class="material-symbols-outlined text-[16px]">arrow_back</span>
                <span>Kembali</span>
            </a>
            <form action="{{ route('orders.void.restore') }}" method="POST" class="inline-block">
                @csrf
                <input type="hidden" name="ids[]" value="{{ $voidOrder->id }}">
                <button type="submit" class="btn-save inline-flex items-center gap-2 px-5 py-2 bg-primary text-white hover:opacity-90 rounded-xl text-xs font-bold transition-all shadow-sm active:scale-95" onclick="return confirm('Yakin ingin merestore order ini?');">
                    <span class="material-symbols-outlined text-[18px]">restore</span>
                    <span>Restore Order</span>
                </button>
            </form>
        </div>
    </div>

    @include('layouts.partials.sales-submenu')

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Order Info -->
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white rounded-2xl shadow-sm border border-outline-variant/30 overflow-hidden p-6">
                <div class="flex justify-between items-start mb-6">
                    <div>
                        <h2 class="text-xl font-bold text-on-surface mb-1">Order #{{ $voidOrder->order_number }}</h2>
                        <p class="text-body-md text-on-surface-variant">Dibuat pada: {{ \Carbon\Carbon::parse($orderData['created_at'])->format('d M Y H:i') }}</p>
                    </div>
                    <span class="px-3 py-1 bg-error-container text-on-error-container rounded-full text-sm font-bold">VOID</span>
                </div>

                <div class="bg-error/10 border-l-4 border-error p-4 mb-6 rounded-r-lg">
                    <h3 class="font-bold text-error mb-1">Alasan Void</h3>
                    <p class="text-on-surface-variant">{{ $voidOrder->void_reason }}</p>
                    <p class="text-sm text-on-surface-variant mt-2">Dibatalkan pada: {{ $voidOrder->voided_at->format('d M Y H:i') }}</p>
                </div>

                <h3 class="font-bold text-lg mb-4 border-b border-outline-variant pb-2">Detail Produk</h3>
                <div class="space-y-4">
                    @forelse($itemsData as $item)
                        <div class="flex items-center gap-4 py-2 border-b border-outline-variant/30 last:border-0">
                            <div class="flex-1">
                                <h4 class="font-bold text-on-surface">{{ $item['name'] ?? 'Unknown Item' }}</h4>
                                <p class="text-sm text-on-surface-variant">
                                    Qty: {{ $item['quantity'] ?? 1 }} x Rp {{ number_format($item['unit_price'] ?? 0, 0, ',', '.') }}
                                </p>
                            </div>
                            <div class="font-bold text-on-surface">
                                Rp {{ number_format($item['total'] ?? 0, 0, ',', '.') }}
                            </div>
                        </div>
                    @empty
                        <p class="text-on-surface-variant text-center py-4">Tidak ada data item.</p>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Summary & Customer -->
        <div class="space-y-6">
            <!-- Summary -->
            <div class="bg-white rounded-2xl shadow-sm border border-outline-variant/30 overflow-hidden p-6">
                <h3 class="font-bold text-lg mb-4 border-b border-outline-variant/30 pb-2">Ringkasan Harga</h3>
                <div class="space-y-3 text-body-md">
                    <div class="flex justify-between">
                        <span class="text-on-surface-variant">Subtotal</span>
                        <span class="font-medium">Rp {{ number_format($orderData['subtotal'] ?? 0, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-on-surface-variant">Ongkos Kirim</span>
                        <span class="font-medium">Rp {{ number_format($orderData['shipping_cost'] ?? 0, 0, ',', '.') }}</span>
                    </div>
                    @if(($orderData['discount'] ?? 0) > 0)
                        <div class="flex justify-between text-success">
                            <span>Diskon</span>
                            <span class="font-medium">- Rp {{ number_format($orderData['discount'], 0, ',', '.') }}</span>
                        </div>
                    @endif
                    <div class="flex justify-between border-t border-outline-variant/30 pt-3 mt-3">
                        <span class="font-bold text-on-surface">Total Akhir</span>
                        <span class="font-bold text-primary text-lg">Rp {{ number_format($orderData['total'] ?? 0, 0, ',', '.') }}</span>
                    </div>
                </div>
            </div>

            <!-- Meta Data -->
            <div class="bg-white rounded-2xl shadow-sm border border-outline-variant/30 overflow-hidden p-6">
                <h3 class="font-bold text-lg mb-4 border-b border-outline-variant pb-2">Informasi Tambahan</h3>
                <div class="space-y-3 text-body-md">
                    <div>
                        <p class="text-on-surface-variant text-sm">Metode Pembayaran</p>
                        <p class="font-medium text-on-surface">{{ $orderData['payment_method'] ?? 'Belum dipilih' }}</p>
                    </div>
                    <div>
                        <p class="text-on-surface-variant text-sm">Dibuat Oleh</p>
                        <p class="font-medium text-on-surface">{{ $orderData['creator'] ?? 'Sistem' }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
