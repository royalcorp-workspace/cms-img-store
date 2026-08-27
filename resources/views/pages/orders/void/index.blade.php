@extends('layouts.app')

@section('title', 'Void Orders')

@section('content')
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="font-headline-lg text-headline-lg text-on-surface">Void Orders</h1>
            <nav class="flex items-center gap-2 text-body-md text-on-surface-variant mt-1">
                <a href="{{ route('dashboard') }}" class="text-primary hover:underline">Dashboard</a>
                <span class="material-symbols-outlined text-[16px]">chevron_right</span>
                <a href="{{ route('orders.index') }}" class="text-primary hover:underline">Orders</a>
                <span class="material-symbols-outlined text-[16px]">chevron_right</span>
                <span>Void</span>
            </nav>
        </div>
    </div>

    @if(session('success'))
        <div class="bg-green-100 text-green-800 p-4 rounded-lg mb-4">
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="bg-red-100 text-red-800 p-4 rounded-lg mb-4">
            {{ session('error') }}
        </div>
    @endif

    <form action="{{ route('orders.void.restore') }}" method="POST">
        @csrf
        <div class="bg-white rounded-xl shadow-sm border border-outline-variant/30 overflow-hidden">
            <div class="p-4 border-b border-outline-variant flex justify-between items-center">
                <h2 class="font-bold">Daftar Pesanan Dibatalkan (Void)</h2>
                <button type="submit" class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary/90 flex items-center gap-2" onclick="return confirm('Yakin ingin merestore order yang dipilih?');">
                    <span class="material-symbols-outlined">restore</span> Restore Terpilih
                </button>
            </div>
            
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-surface-container-low text-on-surface-variant border-b border-outline-variant">
                            <th class="p-4 font-medium text-body-md w-12 text-center">
                                <input type="checkbox" id="check-all" class="rounded border-gray-300 text-primary focus:ring-primary">
                            </th>
                            <th class="p-4 font-medium text-body-md">Order Number</th>
                            <th class="p-4 font-medium text-body-md">Void Reason</th>
                            <th class="p-4 font-medium text-body-md">Voided At</th>
                            <th class="p-4 font-medium text-body-md text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($voidOrders as $order)
                            <tr class="border-b border-outline-variant hover:bg-surface-container-low/50">
                                <td class="p-4 text-center">
                                    <input type="checkbox" name="ids[]" value="{{ $order->id }}" class="order-checkbox rounded border-gray-300 text-primary focus:ring-primary">
                                </td>
                                <td class="p-4 text-body-md font-bold text-on-surface">{{ $order->order_number }}</td>
                                <td class="p-4 text-body-md text-on-surface-variant">{{ $order->void_reason }}</td>
                                <td class="p-4 text-body-md text-on-surface-variant">{{ $order->voided_at->format('d M Y H:i') }}</td>
                                <td class="p-4 text-right">
                                    <a href="{{ route('orders.void.show', $order->id) }}" class="inline-flex items-center gap-1 px-3 py-1.5 bg-secondary-container text-on-secondary-container rounded-md hover:bg-secondary hover:text-white transition-colors text-sm font-medium">
                                        <span class="material-symbols-outlined text-[16px]">visibility</span>
                                        Detail
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="p-8 text-center text-on-surface-variant">Tidak ada order yang dibatalkan.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            <div class="p-4 border-t border-outline-variant">
                {{ $voidOrders->links() }}
            </div>
        </div>
    </form>

    <script>
        document.getElementById('check-all').addEventListener('change', function(e) {
            const checkboxes = document.querySelectorAll('.order-checkbox');
            checkboxes.forEach(cb => {
                cb.checked = e.target.checked;
            });
        });
    </script>
@endsection
