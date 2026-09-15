@extends('layouts.app')

@section('title', 'Void Orders')

@section('content')
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="font-headline-lg text-headline-lg text-on-surface">Void Orders</h1>
            <nav class="flex items-center gap-2 text-label-sm text-on-surface-variant mt-1 font-medium">
                <a href="{{ route('dashboard') }}" class="hover:text-primary transition-colors">eCommerce</a>
                <span class="material-symbols-outlined text-[14px]">chevron_right</span>
                <a href="{{ route('orders.index') }}" class="hover:text-primary transition-colors">Orders</a>
                <span class="material-symbols-outlined text-[14px]">chevron_right</span>
                <span class="text-on-surface">Void Orders</span>
            </nav>
        </div>
        <div class="flex items-center gap-3">
            <button type="submit" form="restoreForm" class="btn-save inline-flex items-center gap-2 px-5 py-2 bg-primary text-white hover:opacity-90 rounded-xl text-xs font-bold transition-all shadow-sm active:scale-95" onclick="return confirm('Yakin ingin merestore order yang dipilih?');">
                <span class="material-symbols-outlined text-[18px]">restore</span>
                <span>Restore Terpilih</span>
            </button>
        </div>
    </div>

    @include('layouts.partials.sales-submenu')

    @if(session('success'))
        <div class="mb-6 p-4 rounded-xl bg-success/10 border border-success/20 text-success text-body-md flex items-center gap-2">
            <span class="material-symbols-outlined text-[20px]">check_circle</span>
            <span>{{ session('success') }}</span>
        </div>
    @endif
    @if(session('error'))
        <div class="mb-6 p-4 rounded-xl bg-danger/10 border border-danger/20 text-danger text-body-md flex items-center gap-2">
            <span class="material-symbols-outlined text-[20px]">error</span>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    <div class="bg-white rounded-2xl shadow-sm border border-outline-variant/30 overflow-hidden">
        <div class="p-4 border-b border-outline-variant/30 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <form action="{{ route('orders.void.index') }}" method="GET" class="flex flex-wrap items-center gap-3">
                <div class="relative">
                    <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-on-surface-variant text-[18px]">search</span>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nomor order / alasan..." class="pl-9 pr-4 py-2 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary/20 focus:outline-none w-64 text-label-sm bg-surface-container-lowest">
                </div>
                <button type="submit" class="px-4 py-2 bg-secondary text-white rounded-lg font-label-md text-xs hover:opacity-90 transition-all">Filter</button>
                @if(request('search'))
                    <a href="{{ route('orders.void.index') }}" class="px-3 py-2 border border-outline-variant text-on-surface-variant rounded-lg font-label-md text-xs hover:bg-surface-container transition-all">Reset</a>
                @endif
            </form>
            <div class="flex items-center gap-2">
                <button type="submit" form="restoreForm" class="btn-save inline-flex items-center gap-2 px-4 py-2 bg-primary text-white rounded-xl hover:opacity-90 transition-all shadow-sm active:scale-95 text-xs font-bold" onclick="return confirm('Yakin ingin merestore order yang dipilih?');">
                    <span class="material-symbols-outlined text-[16px]">restore</span>
                    <span>Restore Terpilih</span>
                </button>
            </div>
        </div>

        <form id="restoreForm" action="{{ route('orders.void.restore') }}" method="POST">
            @csrf
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-surface-gray border-b border-outline-variant/50">
                            <th class="p-4 font-label-md text-label-md text-on-surface-variant uppercase tracking-wider w-12 text-center">
                                <input type="checkbox" id="check-all" class="rounded border-outline-variant text-primary focus:ring-primary cursor-pointer">
                            </th>
                            <th class="p-4 font-label-md text-label-md text-on-surface-variant uppercase tracking-wider">Order Number</th>
                            <th class="p-4 font-label-md text-label-md text-on-surface-variant uppercase tracking-wider">Void Reason</th>
                            <th class="p-4 font-label-md text-label-md text-on-surface-variant uppercase tracking-wider">Voided At</th>
                            <th class="p-4 font-label-md text-label-md text-on-surface-variant uppercase tracking-wider text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-outline-variant/30">
                        @forelse($voidOrders as $order)
                            <tr class="hover:bg-surface-container-lowest transition-colors">
                                <td class="p-4 text-center">
                                    <input type="checkbox" name="ids[]" value="{{ $order->id }}" class="order-checkbox rounded border-outline-variant text-primary focus:ring-primary cursor-pointer">
                                </td>
                                <td class="p-4 text-body-md font-bold text-on-surface">
                                    <a href="{{ route('orders.void.show', $order->id) }}" class="text-primary hover:underline font-mono">
                                        #{{ $order->order_number }}
                                    </a>
                                </td>
                                <td class="p-4 text-body-md text-on-surface-variant">
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-danger/10 text-danger border border-danger/20">
                                        <span class="w-1.5 h-1.5 rounded-full bg-danger"></span>
                                        {{ $order->void_reason }}
                                    </span>
                                </td>
                                <td class="p-4 text-body-md text-on-surface-variant">
                                    {{ $order->voided_at ? $order->voided_at->format('d M Y, H:i') : '-' }}
                                </td>
                                <td class="p-4 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="{{ route('orders.void.show', $order->id) }}" class="inline-flex items-center gap-1 px-3 py-1.5 bg-surface-container-high hover:bg-surface-container-highest text-on-surface rounded-xl text-xs font-semibold transition-colors" title="Lihat Detail">
                                            <span class="material-symbols-outlined text-[16px]">visibility</span>
                                            <span>Detail</span>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="py-12 text-center text-on-surface-variant">
                                    <div class="flex flex-col items-center justify-center">
                                        <span class="material-symbols-outlined text-[48px] mb-2 opacity-40">remove_shopping_cart</span>
                                        <p class="font-label-md">Tidak ada pesanan yang dibatalkan (void).</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            @if($voidOrders->hasPages())
                <div class="p-4 border-t border-outline-variant/30">
                    {{ $voidOrders->links() }}
                </div>
            @endif
        </form>
    </div>

    <script>
        document.getElementById('check-all')?.addEventListener('change', function(e) {
            const checkboxes = document.querySelectorAll('.order-checkbox');
            checkboxes.forEach(cb => {
                cb.checked = e.target.checked;
            });
        });
    </script>
@endsection
