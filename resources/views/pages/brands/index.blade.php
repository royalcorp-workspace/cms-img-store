@extends('layouts.app')

@section('title', 'Brands')

@section('content')
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="font-headline-lg text-headline-lg text-on-surface">Brands</h1>
            <nav class="flex items-center gap-2 text-label-sm text-on-surface-variant mt-1 font-medium">
                <a href="{{ route('dashboard') }}" class="flex items-center gap-2 px-5 py-2 bg-primary text-white font-label-md hover:opacity-90 transition-all">eCommerce</a>
                <span class="material-symbols-outlined text-[14px]">chevron_right</span>
                <span class="text-on-surface">Brands</span>
            </nav>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('brands.create') }}" class="flex items-center gap-2 px-5 py-2 bg-primary text-white font-label-md hover:opacity-90 transition-all">
                <span class="material-symbols-outlined text-[18px]">add</span>
                Create Brand
            </a>
        </div>
    </div>

    @include('layouts.partials.product-submenu')

    @if(session('success'))
        <div class="mb-6 p-4 bg-success/10 border border-success/20 text-success rounded-lg font-body-md text-body-md">
            {{ session('success') }}
        </div>
    @endif

    <div class="overflow-hidden mb-8">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-outline-variant/20 bg-surface-container/20">
                        <th class="px-6 py-4 font-label-md text-label-md text-on-surface-variant">Brand</th>
                        <th class="px-6 py-4 font-label-md text-label-md text-on-surface-variant">Featured</th>
                        <th class="px-6 py-4 font-label-md text-label-md text-on-surface-variant">Sort Order</th>
                        <th class="px-6 py-4 font-label-md text-label-md text-on-surface-variant">Status</th>
                        <th class="px-6 py-4 font-label-md text-label-md text-on-surface-variant text-center">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-outline-variant/20">
                    @forelse($brands ?? [] as $brand)
                        <tr class="hover:bg-surface-container/30 transition-colors group">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-12 h-12 bg-surface-gray rounded-md overflow-hidden flex-shrink-0 border border-outline-variant/20 flex items-center justify-center p-1 bg-white">
                                        @if($brand->logo)
                                            <img class="max-w-full max-h-full object-contain" src="{{ asset('storage/' . $brand->logo) }}" alt="{{ $brand->name }}">
                                        @else
                                            <span class="text-[10px] font-bold text-on-surface-variant uppercase">{{ substr($brand->name, 0, 2) }}</span>
                                        @endif
                                    </div>
                                    <div>
                                        <span class="font-headline-md text-[14px] font-semibold text-on-surface">{{ $brand->name }}</span>
                                        <p class="text-label-sm text-on-surface-variant font-medium mt-0.5">{{ $brand->slug }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                @if($brand->is_featured)
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 bg-primary/10 text-primary border border-primary/20 rounded-full text-[11px] font-semibold uppercase tracking-wider">
                                        Featured
                                    </span>
                                @else
                                    <span class="text-label-sm text-on-surface-variant">-</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-body-md text-on-surface font-medium">{{ $brand->sort_order }}</td>
                            <td class="px-6 py-4">
                                @if($brand->status)
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 bg-success/10 text-success border border-success/20 rounded-full text-[11px] font-semibold uppercase tracking-wider">
                                        Active
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 bg-danger/10 text-danger border border-danger/20 rounded-full text-[11px] font-semibold uppercase tracking-wider">
                                        Inactive
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-on-surface-variant">
                                <div class="flex items-center gap-1.5 justify-center">
                                    <a href="{{ route('brands.edit', $brand->id) }}" class="text-on-surface-variant hover:text-secondary transition-colors" title="Edit"><span class="material-symbols-outlined text-[18px]">edit</span></a>
                                    <form action="{{ route('brands.destroy', $brand->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this brand?')" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-on-surface-variant hover:text-danger transition-colors" title="Delete"><span class="material-symbols-outlined text-[18px]">delete</span></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-8 text-center text-on-surface-variant">No brands found</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
