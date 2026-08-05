@extends('layouts.app')

@section('title', 'Products')

@section('content')
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="font-headline-lg text-headline-lg text-on-surface">Products</h1>
            <nav class="flex items-center gap-2 text-label-sm text-on-surface-variant mt-1 font-medium">
                <a href="{{ route('dashboard') }}" class="hover:text-primary transition-colors">eCommerce</a>
                <span class="material-symbols-outlined text-[14px]">chevron_right</span>
                <span class="text-on-surface">Products</span>
            </nav>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('products.import.form') }}" class="flex items-center gap-2 px-4 py-2 bg-surface-container text-on-surface hover:bg-surface-container-high rounded-md font-label-md text-label-md transition-all border border-outline-variant/30">
                <span class="material-symbols-outlined text-[18px]">cloud_upload</span>
                Bulk Import
            </a>
            <a href="{{ route('products.create') }}" class="flex items-center gap-2 px-4 py-2 bg-primary text-white rounded-md font-label-md text-label-md hover:opacity-90 transition-all shadow-sm">
                <span class="material-symbols-outlined text-[18px]">add</span>
                Create Product
            </a>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow-sm border border-outline-variant/30 overflow-hidden mb-8">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr>
                        <th class="px-6 py-4">Product</th>
                        <th class="px-6 py-4">Category</th>
                        <th class="px-6 py-4">Price</th>
                        <th class="px-6 py-4">Stock</th>
                        <th class="px-6 py-4">Status</th>
                        <th class="px-6 py-4">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-outline-variant/20">
                    @forelse($products ?? [] as $product)
                        <tr class="hover:bg-surface-container/30 transition-colors group">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 bg-surface-gray rounded-md overflow-hidden flex-shrink-0 border border-outline-variant/20">
                                        <img class="w-full h-full object-cover" src="{{ $product->images->first()?->url ?? '' }}" alt="{{ $product->name }}">
                                    </div>
                                    <div>
                                        <a href="{{ route('products.show', $product->id) }}" class="font-headline-md text-[14px] font-semibold text-on-surface hover:text-primary transition-colors">{{ $product->name }}</a>
                                        <p class="text-label-sm text-on-surface-variant font-medium mt-0.5">SKU: {{ $product->sku ?? $product->id }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-body-md text-secondary font-medium">{{ $product->category->name ?? '-' }}</td>
                            <td class="px-6 py-4 font-headline-md text-[14px] font-bold text-on-surface">Rp{{ number_format($product->price ?? 0, 2) }}</td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-2">
                                    <div class="flex-1 h-1.5 bg-surface-container rounded-full overflow-hidden max-w-[80px]">
                                        <div class="h-full bg-success rounded-full" style="width: {{ min(($product->stock ?? 0) / 10, 100) }}%;"></div>
                                    </div>
                                    <span class="text-label-sm font-bold text-on-surface-variant">{{ $product->stock ?? 0 }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                @if(($product->stock ?? 0) > 0)
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 bg-success/10 text-success border border-success/20 rounded-full text-[11px] font-semibold uppercase tracking-wider">
                                        In Stock
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 bg-danger/10 text-danger border border-danger/20 rounded-full text-[11px] font-semibold uppercase tracking-wider">
                                        Out of Stock
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-on-surface-variant">
                                <div class="flex items-center gap-1.5">
                                    <a href="{{ route('products.show', $product->id) }}" class="w-8 h-8 rounded-md bg-surface-container/50 hover:bg-primary/10 hover:text-primary transition-colors flex items-center justify-center" title="View"><span class="material-symbols-outlined text-[18px]">visibility</span></a>
                                    <a href="{{ route('products.edit', $product->id) }}" class="w-8 h-8 rounded-md bg-surface-container/50 hover:bg-secondary/10 hover:text-secondary transition-colors flex items-center justify-center" title="Edit"><span class="material-symbols-outlined text-[18px]">edit</span></a>
                                    <button class="w-8 h-8 rounded-md bg-surface-container/50 hover:bg-danger/10 hover:text-danger transition-colors flex items-center justify-center" title="Delete"><span class="material-symbols-outlined text-[18px]">delete</span></button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-8 text-center text-on-surface-variant">No products found</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if(isset($products) && $products->hasPages())
            <div class="px-6 py-4 border-t border-outline-variant flex items-center justify-between">
                <p class="text-label-sm text-on-surface-variant font-medium">Showing {{ $products->firstItem() }} to {{ $products->lastItem() }} of {{ $products->total() }} products</p>
                <div class="flex gap-1">
                    {{ $products->links() }}
                </div>
            </div>
        @endif
    </div>
@endsection