@extends('layouts.app')

@section('title', 'Create Packing Slip')

@section('content')
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="font-headline-lg text-headline-lg text-on-surface">Create Packing Slip</h1>
            <nav class="flex items-center gap-2 text-body-md text-on-surface-variant mt-1">
                <a href="{{ route('dashboard') }}" class="text-primary hover:underline">Dashboard</a>
                <span class="material-symbols-outlined text-[16px]">chevron_right</span>
                <span>Pick & Pack</span>
                <span class="material-symbols-outlined text-[16px]">chevron_right</span>
                <a href="{{ route('packing-slip.index') }}" class="text-primary hover:underline">Packing Slip</a>
                <span class="material-symbols-outlined text-[16px]">chevron_right</span>
                <span>Create</span>
            </nav>
        </div>
        <a href="{{ route('packing-slip.index') }}" class="flex items-center gap-2 px-4 py-2 bg-surface-container text-on-surface rounded-lg font-label-md text-label-md hover:opacity-90 transition-all">
            <span class="material-symbols-outlined text-[18px]">arrow_back</span> Back
        </a>
    </div>

    @if ($errors->any())
        <div class="bg-danger-100 border border-danger-200 text-danger-700 px-4 py-3 rounded-lg mb-6">
            <ul class="list-disc list-inside">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('packing-slip.store') }}" method="POST">
        @csrf
        <input type="hidden" name="order_id" value="{{ $order->id }}">

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
            <!-- Order Summary & Packing Details (Left/Top) -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Packing Details -->
                <div class="bg-white rounded-xl shadow-sm border border-outline-variant/30 p-6">
                    <h2 class="font-headline-md text-headline-md text-on-surface mb-4">Packing Details</h2>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="box_count" class="block font-label-md text-label-md text-on-surface-variant mb-2">Total Box Count</label>
                            <input type="number" name="box_count" id="box_count" min="1" value="1" 
                                class="w-full px-4 py-2.5 border border-outline/30 rounded-lg focus:outline-none focus:border-primary transition-colors text-body-md" required>
                        </div>
                        <div>
                            <label for="weight" class="block font-label-md text-label-md text-on-surface-variant mb-2">Total Weight (Kg)</label>
                            <input type="number" name="weight" id="weight" step="0.01" min="0" value="0.00" 
                                class="w-full px-4 py-2.5 border border-outline/30 rounded-lg focus:outline-none focus:border-primary transition-colors text-body-md" required>
                        </div>
                    </div>
                </div>

                <!-- Product Items -->
                <div class="bg-white rounded-xl shadow-sm border border-outline-variant/30 overflow-hidden">
                    <div class="px-6 py-4 border-b border-outline-variant/20 bg-surface-container-low/30">
                        <h2 class="font-headline-md text-headline-md text-on-surface">Items to Pack</h2>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-surface-gray border-b border-outline-variant/20">
                                    <th class="px-6 py-4 font-label-md text-label-md text-on-surface-variant uppercase tracking-wider">Product</th>
                                    <th class="px-6 py-4 font-label-md text-label-md text-on-surface-variant uppercase tracking-wider w-32">Qty Ordered</th>
                                    <th class="px-6 py-4 font-label-md text-label-md text-on-surface-variant uppercase tracking-wider w-36">Qty Packed</th>
                                    <th class="px-6 py-4 font-label-md text-label-md text-on-surface-variant uppercase tracking-wider w-32">Box Number</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-outline-variant/10">
                                @foreach($order->items as $item)
                                    <tr class="hover:bg-surface-container/10 transition-colors">
                                        <td class="px-6 py-4">
                                            <div class="font-body-md text-body-md text-on-surface font-semibold">{{ $item->product->name ?? '-' }}</div>
                                            @if($item->variant)
                                                <div class="font-body-sm text-body-sm text-on-surface-variant mt-0.5">Varian: {{ $item->variant->variant_name }}</div>
                                            @endif
                                            <input type="hidden" name="items[{{ $loop->index }}][order_item_id]" value="{{ $item->id }}">
                                        </td>
                                        <td class="px-6 py-4 font-body-md text-body-md text-on-surface">
                                            <span class="bg-surface-container px-2.5 py-1 rounded-md">{{ $item->quantity }}</span>
                                        </td>
                                        <td class="px-6 py-4">
                                            <input type="number" name="items[{{ $loop->index }}][quantity_packed]" 
                                                min="0" max="{{ $item->quantity }}" value="{{ $item->quantity }}"
                                                class="w-24 px-3 py-1.5 border border-outline/30 rounded-lg focus:outline-none focus:border-primary transition-colors text-body-md" required>
                                        </td>
                                        <td class="px-6 py-4">
                                            <input type="number" name="items[{{ $loop->index }}][box_number]" 
                                                min="1" value="1"
                                                class="w-20 px-3 py-1.5 border border-outline/30 rounded-lg focus:outline-none focus:border-primary transition-colors text-body-md" required>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Order Metadata & Submit (Right/Bottom) -->
            <div class="space-y-6">
                <div class="bg-white rounded-xl shadow-sm border border-outline-variant/30 p-6">
                    <h2 class="font-headline-md text-headline-md text-on-surface mb-4">Order Summary</h2>
                    <div class="space-y-4">
                        <div>
                            <p class="text-label-sm text-on-surface-variant mb-1">Order Number</p>
                            <p class="font-body-md text-body-md text-on-surface font-semibold">{{ $order->order_number }}</p>
                        </div>
                        <div>
                            <p class="text-label-sm text-on-surface-variant mb-1">Customer</p>
                            <p class="font-body-md text-body-md text-on-surface font-semibold">{{ $order->customer->name ?? 'N/A' }}</p>
                        </div>
                        <div>
                            <p class="text-label-sm text-on-surface-variant mb-1">Order Date</p>
                            <p class="font-body-md text-body-md text-on-surface">{{ $order->created_at->format('d M Y H:i') }}</p>
                        </div>
                        <div class="pt-4 border-t border-outline-variant/20">
                            <button type="submit" class="w-full flex items-center justify-center gap-2 px-4 py-2.5 bg-primary text-white rounded-lg font-label-md text-label-md hover:opacity-90 transition-all shadow-sm">
                                <span class="material-symbols-outlined text-[18px]">done</span> Create Packing Slip
                            </button>
                            <a href="{{ route('packing-slip.index') }}" class="w-full mt-2 flex items-center justify-center gap-2 px-4 py-2.5 bg-surface-container text-on-surface rounded-lg font-label-md text-label-md hover:bg-surface-container-high transition-all">
                                Cancel
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
@endsection
