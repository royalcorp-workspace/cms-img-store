@extends('layouts.app')

@section('title', 'Product Suggestions')

@section('content')
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="font-headline-lg text-headline-lg text-on-surface">Product Suggestions</h1>
            <nav class="flex items-center gap-2 text-label-sm text-on-surface-variant mt-1 font-medium">
                <a href="{{ route('dashboard') }}" class="hover:text-primary transition-colors">eCommerce</a>
                <span class="material-symbols-outlined text-[14px]">chevron_right</span>
                <span class="text-on-surface">Suggestions</span>
            </nav>
        </div>
    </div>

    @include('layouts.partials.product-submenu')

    <div class="bg-white rounded-2xl shadow-sm border border-outline-variant/30 overflow-hidden">
        <div class="p-6">
            <div class="flex flex-col sm:flex-row justify-between items-center gap-4 mb-6">
                <form method="GET" action="{{ route('product-suggestions.index') }}" class="relative w-full sm:w-96">
                    <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-on-surface-variant text-[20px]">search</span>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search products..." class="w-full pl-10 pr-4 py-2 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary/20 focus:outline-none bg-surface-container-lowest text-body-md">
                </form>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-surface-container-lowest border-y border-outline-variant/50">
                            <th class="py-3 px-4 text-label-sm font-medium text-on-surface-variant w-16">Image</th>
                            <th class="py-3 px-4 text-label-sm font-medium text-on-surface-variant">Product Info</th>
                            <th class="py-3 px-4 text-label-sm font-medium text-on-surface-variant">Suggestions Count</th>
                            <th class="py-3 px-4 text-label-sm font-medium text-on-surface-variant text-right w-32">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-outline-variant/30">
                        @forelse($products as $product)
                            <tr class="hover:bg-surface-container-lowest transition-colors">
                                <td class="py-3 px-4">
                                    <div class="w-12 h-12 rounded-lg bg-surface-gray border border-outline-variant/30 overflow-hidden flex items-center justify-center flex-shrink-0">
                                        @if($product->images->isNotEmpty())
                                            <img src="{{ $product->images->first()->url }}" alt="Image" class="w-full h-full object-cover">
                                        @else
                                            <span class="material-symbols-outlined text-[20px] text-on-surface-variant">image</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="py-3 px-4">
                                    <div class="font-body-md text-on-surface font-semibold">{{ $product->name }}</div>
                                    <div class="text-label-xs text-on-surface-variant mt-0.5">Code: {{ $product->code ?? '-' }}</div>
                                </td>
                                <td class="py-3 px-4">
                                    @if($product->suggested_products_count > 0)
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-primary/10 text-primary text-label-xs font-bold border border-primary/20">
                                            <span class="material-symbols-outlined text-[14px]">link</span>
                                            {{ $product->suggested_products_count }} Products
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-surface-container text-on-surface-variant text-label-xs font-bold">
                                            0 Products
                                        </span>
                                    @endif
                                </td>
                                <td class="py-3 px-4 text-right">
                                    <a href="{{ route('product-suggestions.edit', $product->id) }}" class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-surface-container-lowest border border-outline-variant text-primary hover:bg-primary/5 transition-colors" title="Manage Suggestions">
                                        <span class="material-symbols-outlined text-[18px]">edit</span>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="py-8 text-center text-on-surface-variant">
                                    <div class="flex flex-col items-center justify-center">
                                        <span class="material-symbols-outlined text-[48px] mb-2 opacity-50">inventory_2</span>
                                        <p class="font-label-md">No products found</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-6">
                {{ $products->links() }}
            </div>
        </div>
    </div>
@endsection
