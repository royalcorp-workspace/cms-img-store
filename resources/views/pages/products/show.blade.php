@extends('layouts.app')

@section('title', 'Product Details')

@section('content')
<div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
    <div>
        <h1 class="font-headline-lg text-headline-lg text-on-surface">Product Details</h1>
        <nav class="flex items-center gap-2 text-body-md text-on-surface-variant mt-1">
            <a href="{{ route('dashboard') }}" class="text-primary hover:underline">Dashboard</a>
            <span class="material-symbols-outlined text-[16px]">chevron_right</span>
            <a href="{{ route('products.index') }}" class="text-primary hover:underline">Products</a>
            <span class="material-symbols-outlined text-[16px]">chevron_right</span>
            <span>{{ $product->name ?? 'Product' }}</span>
        </nav>
    </div>
    <div class="flex items-center gap-3">
        <a href="{{ route('products.edit', $product->id) }}" class="flex items-center gap-2 px-4 py-2 bg-primary text-white rounded-lg font-label-md text-label-md hover:opacity-90 transition-all shadow-sm">
            <span class="material-symbols-outlined text-[18px]">edit</span> Edit
        </a>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <div class="bg-white rounded-xl shadow-sm border border-outline-variant/30">
        <div class="p-6">
            <div class="flex items-center justify-center mb-4">
                <div class="w-48 h-48 bg-surface-container-low rounded-xl flex items-center justify-center overflow-hidden">
                    <img id="mainProductImage" class="w-full h-full object-cover" src="{{ $product->thumbnail_url ?? ($product->images->first()?->url ?? '') }}" alt="{{ $product->name }}">
                </div>
            </div>
            @if($product->images->count() > 1 || $product->thumbnail_url)
            <div class="flex items-center justify-center gap-2 mt-4">
                @if($product->thumbnail_url)
                <button type="button" onclick="document.getElementById('mainProductImage').src='{{ $product->thumbnail_url }}'" class="w-16 h-16 rounded-lg overflow-hidden border-2 border-primary flex-shrink-0">
                    <img src="{{ $product->thumbnail_url }}" alt="Thumbnail" class="w-full h-full object-cover">
                </button>
                @endif
                @foreach($product->images as $img)
                <button type="button" onclick="document.getElementById('mainProductImage').src='{{ $img->url }}'" class="w-16 h-16 rounded-lg overflow-hidden border-2 border-outline-variant hover:border-primary transition-colors flex-shrink-0">
                    <img src="{{ $img->url }}" alt="{{ $img->alt_text ?? '' }}" class="w-full h-full object-cover">
                </button>
                @endforeach
            </div>
            @endif
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-outline-variant/30">
        <div class="p-6">
            <div class="flex items-start justify-between mb-4">
                <div>
                    <h2 class="font-headline-md text-headline-md text-on-surface mb-1">{{ $product->name ?? 'Product Name' }}</h2>
                    <div class="flex items-center gap-2 text-body-md text-on-surface-variant">
                        <span>#SKU-{{ $product->sku ?? $product->id }}</span>
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 bg-success/10 text-success rounded-full text-label-sm font-label-sm">
                            <span class="w-1.5 h-1.5 rounded-full bg-success"></span> {{ $product->status ?? 'Active' }}
                        </span>
                    </div>
                </div>
                <span class="font-metric-display text-metric-display text-primary">Rp{{ number_format($product->price ?? 0, 2) }}</span>
            </div>

            <p class="text-body-md text-on-surface-variant mb-6">{{ $product->description ?? 'No description available.' }}</p>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="border border-outline-variant/30 rounded-lg p-4">
                    <p class="text-label-sm text-on-surface-variant uppercase tracking-wider mb-1">Category</p>
                    <p class="font-body-md text-body-md text-on-surface font-medium">{{ $product->category->name ?? '-' }}</p>
                </div>
                <div class="border border-outline-variant/30 rounded-lg p-4">
                    <p class="text-label-sm text-on-surface-variant uppercase tracking-wider mb-1">Stock</p>
                    <p class="font-body-md text-body-md text-on-surface font-medium">{{ $product->stock ?? 0 }} Available</p>
                </div>
                <div class="border border-outline-variant/30 rounded-lg p-4">
                    <p class="text-label-sm text-on-surface-variant uppercase tracking-wider mb-1">Created Date</p>
                    <p class="font-body-md text-body-md text-on-surface font-medium">{{ $product->created_at?->format('d M Y') ?? '-' }}</p>
                </div>
                <div class="border border-outline-variant/30 rounded-lg p-4">
                    <p class="text-label-sm text-on-surface-variant uppercase tracking-wider mb-1">Rating</p>
                    <p class="font-body-md text-body-md text-on-surface font-medium flex items-center gap-1">
                        {{ $product->rating ?? '4.5' }}
                        <span class="material-symbols-outlined text-[18px] text-warning">star</span>
                    </p>
                </div>
            </div>
        </div>
    </div>

    @if($product->colors->count() > 0)
    <div class="bg-white rounded-xl shadow-sm border border-outline-variant/30">
        <div class="p-6 border-b border-outline-variant">
            <h3 class="font-headline-md text-headline-md text-on-surface">Product Colors</h3>
            <p class="text-body-sm text-on-surface-variant mt-1">{{ $product->colors->count() }} colors available</p>
        </div>
        <div class="p-6">
            <div class="flex flex-wrap gap-4">
                @foreach($product->colors as $color)
                <div class="flex items-center gap-3 border border-outline-variant rounded-lg p-3">
                    <div class="w-10 h-10 rounded-full border border-outline-variant shadow-sm" style="background-color: {{ $color->color_code }}"></div>
                    <div>
                        <p class="font-body-md text-body-md text-on-surface font-medium">{{ $color->color_name ?? 'Unnamed' }}</p>
                        <p class="text-label-sm text-on-surface-variant font-mono">{{ $color->color_code }}</p>
                    </div>
                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-label-sm font-label-sm {{ ($color->status ?? 1) ? 'bg-success/10 text-success' : 'bg-danger/10 text-danger' }}">
                        <span class="w-1.5 h-1.5 rounded-full bg-current"></span> {{ ($color->status ?? 1) ? 'Active' : 'Inactive' }}
                    </span>
                </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif

    @if(!empty($product->segments))
    <div class="bg-white rounded-xl shadow-sm border border-outline-variant/30">
        <div class="p-6 border-b border-outline-variant">
            <h3 class="font-headline-md text-headline-md text-on-surface">Product Segments</h3>
        </div>
        <div class="p-6">
            <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
                @foreach($product->segments as $key => $value)
                <div class="border border-outline-variant/30 rounded-lg p-3">
                    <p class="text-label-xs text-on-surface-variant uppercase tracking-wider mb-1">{{ $key }}</p>
                    <p class="font-body-md text-body-md text-on-surface font-medium">{{ $value ?: '-' }}</p>
                </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif

    @if($product->variants->count() > 0)
    <div class="bg-white rounded-xl shadow-sm border border-outline-variant/30">
        <div class="p-6 border-b border-outline-variant">
            <h3 class="font-headline-md text-headline-md text-on-surface">Product Variations</h3>
            <p class="text-body-sm text-on-surface-variant mt-1">{{ $product->variants->count() }} variations available</p>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-surface-gray">
                        <th class="px-gutter py-3 font-label-md text-label-md text-on-surface-variant uppercase tracking-wider">Variant</th>
                        <th class="px-gutter py-3 font-label-md text-label-md text-on-surface-variant uppercase tracking-wider">SKU</th>
                        <th class="px-gutter py-3 font-label-md text-label-md text-on-surface-variant uppercase tracking-wider">Price</th>
                        <th class="px-gutter py-3 font-label-md text-label-md text-on-surface-variant uppercase tracking-wider">Stock</th>
                        <th class="px-gutter py-3 font-label-md text-label-md text-on-surface-variant uppercase tracking-wider">Dimensions</th>
                        <th class="px-gutter py-3 font-label-md text-label-md text-on-surface-variant uppercase tracking-wider">Weight</th>
                        <th class="px-gutter py-3 font-label-md text-label-md text-on-surface-variant uppercase tracking-wider text-center">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-outline-variant/20">
                    @foreach($product->variants as $variant)
                    <tr class="hover:bg-surface-container/30 transition-colors">
                        <td class="px-gutter py-4 font-body-md text-body-md text-on-surface font-medium">{{ $variant->variant_name ?? ($variant->sku ?? 'Default') }}</td>
                        <td class="px-gutter py-4 font-body-md text-body-md text-on-surface-variant">{{ $variant->sku ?? '-' }}</td>
                        <td class="px-gutter py-4 font-body-md text-body-md text-on-surface font-medium">Rp{{ number_format($variant->price ?? 0, 2, ',', '.') }}</td>
                        <td class="px-gutter py-4 font-body-md text-body-md text-on-surface-variant">{{ $variant->stock_qty ?? 0 }}</td>
                        <td class="px-gutter py-4 font-body-md text-body-md text-on-surface-variant">{{ $variant->width ?? 0 }}x{{ $variant->length ?? 0 }}x{{ $variant->height ?? 0 }} cm</td>
                        <td class="px-gutter py-4 font-body-md text-body-md text-on-surface-variant">{{ $variant->weight ?? 0 }} kg</td>
                        <td class="px-gutter py-4 text-center">
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-label-sm font-label-sm {{ ($variant->status ?? 1) ? 'bg-success/10 text-success' : 'bg-danger/10 text-danger' }}">
                                <span class="w-1.5 h-1.5 rounded-full bg-current"></span> {{ ($variant->status ?? 1) ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif
</div>
@endsection