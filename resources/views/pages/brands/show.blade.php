@extends('layouts.app')

@section('title', $brand->name . ' - Brand Details')

@section('content')
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
        <div>
            <div class="flex items-center gap-3">
                <h1 class="font-headline-lg text-headline-lg text-on-surface">{{ $brand->name }}</h1>
                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $brand->status ? 'bg-success/10 text-success' : 'bg-danger/10 text-danger' }}">
                    <span class="w-1.5 h-1.5 rounded-full bg-current"></span> {{ $brand->status ? 'Aktif' : 'Nonaktif' }}
                </span>
                @if($brand->is_featured)
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-primary/10 text-primary border border-primary/20 uppercase tracking-wider text-[10px]">
                        Featured
                    </span>
                @endif
            </div>
            <nav class="flex items-center gap-2 text-label-sm text-on-surface-variant mt-1 font-medium">
                <a href="{{ route('dashboard') }}" class="hover:text-primary transition-colors">eCommerce</a>
                <span class="material-symbols-outlined text-[14px]">chevron_right</span>
                <a href="{{ route('brands.index') }}" class="hover:text-primary transition-colors">Brands</a>
                <span class="material-symbols-outlined text-[14px]">chevron_right</span>
                <span class="text-on-surface">{{ $brand->name }}</span>
            </nav>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('brands.index') }}" class="inline-flex items-center gap-1.5 px-4 py-2 border border-outline-variant text-on-surface-variant hover:bg-surface-container rounded-xl text-xs font-semibold transition-colors">
                <span class="material-symbols-outlined text-[16px]">arrow_back</span>
                <span>Kembali</span>
            </a>
            <a href="{{ route('brands.edit', $brand->id) }}" class="flex items-center gap-2 px-4 py-2 bg-primary text-white rounded-xl font-label-md text-label-md hover:opacity-90 transition-all shadow-sm">
                <span class="material-symbols-outlined text-[18px]">edit</span>
                <span>Edit Brand</span>
            </a>
        </div>
    </div>

    @include('layouts.partials.product-submenu')

    <div class="space-y-6">
        <!-- Brand Info Card -->
        <div class="bg-white rounded-2xl shadow-sm border border-outline-variant/30 p-6">
            <div class="flex flex-col md:flex-row items-start gap-6">
                <div class="w-28 h-28 bg-surface-gray rounded-xl overflow-hidden border border-outline-variant/30 flex items-center justify-center p-2 flex-shrink-0 bg-white shadow-sm">
                    @if($brand->logo)
                        <img class="max-w-full max-h-full object-contain" src="{{ media_url($brand->logo) }}" alt="{{ $brand->name }}">
                    @else
                        <span class="text-2xl font-bold text-on-surface-variant uppercase">{{ substr($brand->name, 0, 2) }}</span>
                    @endif
                </div>
                <div class="flex-1 space-y-4">
                    <div>
                        <h2 class="text-xl font-bold text-on-surface">{{ $brand->name }}</h2>
                        <p class="text-sm font-mono text-on-surface-variant mt-0.5">{{ $brand->slug }}</p>
                    </div>
                    <p class="text-sm text-on-surface leading-relaxed">{{ $brand->description ?: 'Tidak ada deskripsi untuk brand ini.' }}</p>

                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 pt-2">
                        <div class="border border-outline-variant/20 rounded-xl p-3 bg-surface-container/20">
                            <span class="text-xs text-on-surface-variant font-medium block">Sort Order</span>
                            <span class="text-base font-bold text-on-surface">{{ $brand->sort_order ?? 0 }}</span>
                        </div>
                        <div class="border border-outline-variant/20 rounded-xl p-3 bg-surface-container/20">
                            <span class="text-xs text-on-surface-variant font-medium block">Status</span>
                            <span class="text-base font-bold {{ $brand->status ? 'text-success' : 'text-danger' }}">
                                {{ $brand->status ? 'Aktif' : 'Nonaktif' }}
                            </span>
                        </div>
                        <div class="border border-outline-variant/20 rounded-xl p-3 bg-surface-container/20">
                            <span class="text-xs text-on-surface-variant font-medium block">Featured</span>
                            <span class="text-base font-bold text-on-surface">
                                {{ $brand->is_featured ? 'Ya' : 'Tidak' }}
                            </span>
                        </div>
                        <div class="border border-outline-variant/20 rounded-xl p-3 bg-surface-container/20">
                            <span class="text-xs text-on-surface-variant font-medium block">Total Produk</span>
                            <span class="text-base font-bold text-primary">{{ $brand->products->count() }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Banner Preview Card -->
        <div class="bg-white rounded-2xl shadow-sm border border-outline-variant/30 p-6 space-y-4">
            <div class="border-b border-outline-variant/20 pb-3">
                <h3 class="text-base font-bold text-on-surface flex items-center gap-2">
                    <span class="material-symbols-outlined text-primary text-[20px]">panorama</span>
                    Brand Banner
                </h3>
                <p class="text-xs text-on-surface-variant mt-0.5">Preview banner shop web dan mobile untuk brand ini.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Banner Web -->
                <div class="space-y-2">
                    <span class="text-xs font-semibold text-on-surface-variant uppercase tracking-wider">Banner Web</span>
                    <div class="w-full h-44 rounded-xl border border-outline-variant/30 overflow-hidden bg-surface-container/20 flex items-center justify-center">
                        @if($brand->banner_type == 2 && $brand->embed_web)
                            <img src="{{ $brand->embed_web }}" alt="Banner Web" class="w-full h-full object-cover">
                        @elseif($brand->banner_web)
                            <img src="{{ media_url($brand->banner_web) }}" alt="Banner Web" class="w-full h-full object-cover">
                        @else
                            <div class="text-center text-on-surface-variant text-xs">
                                <span class="material-symbols-outlined text-[32px] mb-1 block">image_not_supported</span>
                                Belum ada banner web
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Banner Mobile -->
                <div class="space-y-2">
                    <span class="text-xs font-semibold text-on-surface-variant uppercase tracking-wider">Banner Mobile</span>
                    <div class="w-full h-44 rounded-xl border border-outline-variant/30 overflow-hidden bg-surface-container/20 flex items-center justify-center">
                        @if($brand->banner_type == 2 && $brand->embed_mobile)
                            <img src="{{ $brand->embed_mobile }}" alt="Banner Mobile" class="w-full h-full object-contain">
                        @elseif($brand->banner_mobile)
                            <img src="{{ media_url($brand->banner_mobile) }}" alt="Banner Mobile" class="w-full h-full object-contain">
                        @else
                            <div class="text-center text-on-surface-variant text-xs">
                                <span class="material-symbols-outlined text-[32px] mb-1 block">smartphone</span>
                                Belum ada banner mobile
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            @if($brand->banner_link)
                <div class="pt-2">
                    <span class="text-xs text-on-surface-variant font-medium">Redirect URL:</span>
                    <a href="{{ $brand->banner_link }}" target="_blank" class="text-xs text-primary underline ml-1 hover:opacity-80">{{ $brand->banner_link }}</a>
                </div>
            @endif
        </div>

        <!-- Products Under Brand Card -->
        <div class="bg-white rounded-2xl shadow-sm border border-outline-variant/30 overflow-hidden">
            <div class="p-4 border-b border-outline-variant/30 bg-surface-container-lowest flex items-center justify-between">
                <div>
                    <h3 class="text-base font-bold text-on-surface">Daftar Produk ({{ $brand->products->count() }})</h3>
                    <p class="text-xs text-on-surface-variant">Produk yang terhubung dengan brand {{ $brand->name }}</p>
                </div>
                <a href="{{ route('products.create') }}" class="flex items-center gap-1.5 px-3 py-1.5 bg-primary text-white rounded-lg text-xs font-medium hover:opacity-90 transition-all">
                    <span class="material-symbols-outlined text-[16px]">add</span>
                    Tambah Produk
                </a>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-outline-variant/20 bg-surface-container/20">
                            <th class="px-6 py-3 font-label-md text-label-md text-on-surface-variant">Produk</th>
                            <th class="px-6 py-3 font-label-md text-label-md text-on-surface-variant">Kode</th>
                            <th class="px-6 py-3 font-label-md text-label-md text-on-surface-variant">Kategori</th>
                            <th class="px-6 py-3 font-label-md text-label-md text-on-surface-variant">Status</th>
                            <th class="px-6 py-3 font-label-md text-label-md text-on-surface-variant text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-outline-variant/20">
                        @forelse($brand->products as $product)
                            <tr class="hover:bg-surface-container/30 transition-colors group">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 bg-surface-gray rounded-md overflow-hidden flex-shrink-0 border border-outline-variant/20">
                                            <img class="w-full h-full object-cover" src="{{ $product->thumbnail_url ?: ($product->images->first()?->url ?? '') }}" alt="{{ $product->name }}">
                                        </div>
                                        <div>
                                            <a href="{{ route('products.show', $product->id) }}" class="font-headline-md text-[14px] font-semibold text-on-surface hover:text-primary transition-colors">{{ $product->name }}</a>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-sm font-mono text-on-surface">{{ $product->code ?? '-' }}</td>
                                <td class="px-6 py-4 text-sm text-secondary">{{ $product->category->name ?? '-' }}</td>
                                <td class="px-6 py-4">
                                    @if($product->status)
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-semibold bg-success/10 text-success">Aktif</span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-semibold bg-danger/10 text-danger">Nonaktif</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <a href="{{ route('products.show', $product->id) }}" class="text-on-surface-variant hover:text-primary transition-colors p-1" title="Lihat Produk">
                                        <span class="material-symbols-outlined text-[18px]">visibility</span>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-8 text-center text-on-surface-variant text-sm">
                                    Belum ada produk untuk brand ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
