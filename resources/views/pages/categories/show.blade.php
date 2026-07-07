@extends('layouts.app')

@section('title', $category->name)

@section('content')
    <div class="mb-8">
        <nav class="flex items-center gap-2 text-body-md text-on-surface-variant mb-4">
            <a href="{{ route('home') }}" class="text-primary hover:underline">Home</a>
            <span class="material-symbols-outlined text-[16px]">chevron_right</span>
            <span>{{ $category->name }}</span>
        </nav>
        <h1 class="font-headline-lg text-headline-lg text-on-surface">{{ $category->name }}</h1>
        @if($category->description)
            <p class="text-body-md text-on-surface-variant mt-2">{{ $category->description }}</p>
        @endif
    </div>

    @if($products->isEmpty())
        <div class="bg-white rounded-xl shadow-sm border border-outline-variant/30 p-12 text-center">
            <span class="material-symbols-outlined text-[48px] text-on-surface-variant mb-4">inventory_2</span>
            <p class="text-body-md text-on-surface-variant">No products found in this category.</p>
        </div>
    @else
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-5">
            @foreach($products as $product)
                @php
                    $displayPrice = $product->final_price ?? $product->base_price ?? 0;
                    $thumbnail = $product->thumbnail_url;
                @endphp
                <div class="bg-white rounded-xl border border-outline-variant/60 overflow-hidden shadow-sm flex flex-col h-full hover:shadow-md transition-shadow">
                    <div class="p-4 flex items-center gap-3 border-b border-outline-variant/20">
                        <div class="w-12 h-12 rounded-lg overflow-hidden bg-surface-gray flex-shrink-0 border border-outline-variant/20">
                            @if($thumbnail)
                                <img src="{{ $thumbnail }}" alt="{{ $product->name }}" class="w-full h-full object-cover">
                            @else
                                <div class="w-full h-full flex items-center justify-center text-on-surface-variant">
                                    <span class="material-symbols-outlined text-[24px]">image</span>
                                </div>
                            @endif
                        </div>
                        <div class="flex-1 min-w-0">
                            <h4 class="font-body-md text-body-md text-on-surface font-semibold truncate">{{ $product->name }}</h4>
                            <p class="text-label-xs text-on-surface-variant mt-0.5">Rp{{ number_format($displayPrice, 2, ',', '.') }}</p>
                        </div>
                    </div>
                    <div class="p-3 flex-1">
                        @if($product->variants->count() > 0)
                            <div class="space-y-1.5">
                                @foreach($product->variants as $variant)
                                    @php $stock = $variant->stock_qty ?? 0; @endphp
                                    <div class="flex items-center justify-between py-1.5 px-2 rounded-lg {{ $stock > 0 ? 'bg-surface-container-low/50' : 'bg-surface-container-low/30 opacity-60' }}">
                                        <span class="text-label-sm text-on-surface font-medium truncate pr-2">{{ $variant->variant_name ?? ($variant->sku ?? 'Default') }}</span>
                                        <span class="text-label-xs text-on-surface-variant whitespace-nowrap">{{ $stock > 0 ? 'Stok: ' . $stock : 'Habis' }}</span>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="text-label-xs text-on-surface-variant text-center py-4">No variants</p>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    @endif
@endsection
