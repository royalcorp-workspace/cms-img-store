@extends('layouts.app')

@section('title', 'Products List')

@section('content')
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="font-headline-lg text-headline-lg text-on-surface">Products List</h1>
            <nav class="flex items-center gap-2 text-body-md text-on-surface-variant mt-1">
                <a href="{{ route('dashboard') }}" class="text-primary hover:underline">Dashboard</a>
                <span class="material-symbols-outlined text-[16px]">chevron_right</span>
                <span>Products</span>
            </nav>
        </div>
        <a href="{{ route('products.create') }}" class="flex items-center gap-2 px-4 py-2 bg-primary text-white rounded-lg font-label-md text-label-md hover:opacity-90 transition-all shadow-sm">
            <span class="material-symbols-outlined text-[18px]">add</span>
            Create Product
        </a>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-outline-variant/30 overflow-hidden mb-8">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-surface-gray">
                        <th class="px-6 py-4 font-label-md text-label-md text-on-surface-variant uppercase tracking-wider">Product</th>
                        <th class="px-6 py-4 font-label-md text-label-md text-on-surface-variant uppercase tracking-wider">Category</th>
                        <th class="px-6 py-4 font-label-md text-label-md text-on-surface-variant uppercase tracking-wider">Price</th>
                        <th class="px-6 py-4 font-label-md text-label-md text-on-surface-variant uppercase tracking-wider">Stock</th>
                        <th class="px-6 py-4 font-label-md text-label-md text-on-surface-variant uppercase tracking-wider">Status</th>
                        <th class="px-6 py-4 font-label-md text-label-md text-on-surface-variant uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-outline-variant/20">
                    @forelse($products ?? [] as $product)
                        <tr class="hover:bg-surface-container/30 transition-colors group">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-4">
                                    <div class="w-12 h-12 bg-surface-gray rounded-lg overflow-hidden flex-shrink-0 border border-outline-variant/20">
                                        <img class="w-full h-full object-cover" src="{{ $product->images->first()?->url ?? '' }}" alt="{{ $product->name }}">
                                    </div>
                                    <div>
                                        <p class="font-headline-md text-headline-md text-on-surface group-hover:text-primary transition-colors">{{ $product->name }}</p>
                                        <p class="text-label-sm text-on-surface-variant">SKU: {{ $product->sku ?? $product->id }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-body-md text-secondary">{{ $product->category->name ?? '-' }}</td>
                            <td class="px-6 py-4 font-headline-md text-headline-md text-on-surface">Rp{{ number_format($product->price ?? 0, 2) }}</td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-2">
                                    <div class="flex-1 h-1.5 bg-surface-gray rounded-full overflow-hidden max-w-[80px]">
                                        <div class="h-full bg-success rounded-full" style="width: {{ min(($product->stock ?? 0) / 10, 100) }}%;"></div>
                                    </div>
                                    <span class="text-label-sm font-bold">{{ $product->stock ?? 0 }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-success/10 text-success rounded-full text-label-sm">
                                    <span class="w-1.5 h-1.5 rounded-full bg-success"></span> {{ $product->stock > 0 ? 'In Stock' : 'Out of Stock' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-on-surface-variant">
                                <div class="flex items-center gap-2">
                                    <a href="{{ route('products.show', $product->id) }}" class="p-1.5 hover:text-primary transition-colors" title="View"><span class="material-symbols-outlined text-[20px]">visibility</span></a>
                                    <a href="{{ route('products.edit', $product->id) }}" class="p-1.5 hover:text-primary transition-colors" title="Edit"><span class="material-symbols-outlined text-[20px]">edit</span></a>
                                    <button class="p-1.5 hover:text-danger transition-colors" title="Delete"><span class="material-symbols-outlined text-[20px]">delete</span></button>
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
                <p class="text-label-sm text-on-surface-variant">Showing {{ $products->firstItem() }} to {{ $products->lastItem() }} of {{ $products->total() }} products</p>
                <div class="flex gap-1">
                    {{ $products->links() }}
                </div>
            </div>
        @endif
    </div>
@endsection