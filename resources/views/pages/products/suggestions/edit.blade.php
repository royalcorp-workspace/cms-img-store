@extends('layouts.app')

@section('title', 'Manage Product Suggestions')

@section('content')
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="font-headline-lg text-headline-lg text-on-surface">Manage Suggestions</h1>
            <nav class="flex items-center gap-2 text-label-sm text-on-surface-variant mt-1 font-medium">
                <a href="{{ route('dashboard') }}" class="hover:text-primary transition-colors">eCommerce</a>
                <span class="material-symbols-outlined text-[14px]">chevron_right</span>
                <a href="{{ route('product-suggestions.index') }}" class="hover:text-primary transition-colors">Suggestions</a>
                <span class="material-symbols-outlined text-[14px]">chevron_right</span>
                <span class="text-on-surface">Edit: {{ $product->name }}</span>
            </nav>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('product-suggestions.index') }}" class="inline-flex items-center gap-1.5 px-4 py-2 border border-outline-variant text-on-surface-variant hover:bg-surface-container rounded-xl text-xs font-semibold transition-colors">
                <span class="material-symbols-outlined text-[16px]">arrow_back</span>
                <span>Batal</span>
            </a>
            <button type="submit" form="suggestionForm" class="btn-save inline-flex items-center gap-2 px-5 py-2 bg-primary text-white hover:opacity-90 rounded-xl text-xs font-bold transition-all shadow-sm active:scale-95">
                <span class="material-symbols-outlined text-[18px]">save</span>
                <span>Simpan Saran</span>
            </button>
        </div>
    </div>

    @include('layouts.partials.product-submenu')

    <div class="bg-white rounded-2xl shadow-sm border border-outline-variant/30 mb-6 overflow-hidden">
        <div class="p-4 bg-surface-container-lowest border-b border-outline-variant/30">
            <h3 class="text-label-md font-bold text-on-surface">Target Product</h3>
        </div>
        <div class="p-6 flex items-start gap-4">
            <div class="w-20 h-20 rounded-xl bg-surface-gray border border-outline-variant/30 overflow-hidden flex-shrink-0">
                @if($product->images->isNotEmpty())
                    <img src="{{ $product->images->first()->url }}" alt="Image" class="w-full h-full object-cover">
                @else
                    <div class="w-full h-full flex items-center justify-center text-on-surface-variant">
                        <span class="material-symbols-outlined text-[24px]">image</span>
                    </div>
                @endif
            </div>
            <div>
                <h2 class="text-headline-md font-bold text-on-surface mb-1">{{ $product->name }}</h2>
                <div class="text-label-md text-on-surface-variant">Code: <span class="font-bold">{{ $product->code ?? '-' }}</span></div>
            </div>
        </div>
    </div>

    <form id="suggestionForm" method="POST" action="{{ route('product-suggestions.update', $product->id) }}">
        @csrf
        @method('PUT')
        
        <div class="bg-white rounded-2xl shadow-sm border border-outline-variant/30 p-6" x-data="{ search: '' }">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-end mb-6 gap-3">
                <div class="space-y-1">
                    <h3 class="font-headline-md text-headline-md text-on-surface">Select Suggested Products</h3>
                    <p class="text-label-sm text-on-surface-variant">Pilih produk yang akan direkomendasikan saat pelanggan melihat produk target di atas.</p>
                </div>
                <div class="relative w-full sm:w-80">
                    <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-on-surface-variant text-[18px]">search</span>
                    <input type="text" x-model="search" placeholder="Cari kode atau nama produk..." class="w-full pl-9 pr-3 py-2 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary/20 focus:outline-none bg-surface-container-lowest">
                </div>
            </div>
            
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 max-h-[600px] overflow-y-auto p-4 border border-outline-variant/50 rounded-xl bg-surface-gray/30 custom-scrollbar">
                @php 
                    $suggestedProductIds = $product->suggestedProducts->pluck('id')->toArray();
                @endphp
                @if(isset($allProducts) && count($allProducts) > 0)
                    @foreach($allProducts as $prod)
                        <label class="bg-white rounded-xl border {{ in_array($prod->id, $suggestedProductIds) ? 'border-primary/60 shadow-md bg-primary/5' : 'border-outline-variant/50 shadow-sm' }} flex items-center p-3 cursor-pointer hover:border-primary/50 hover:shadow-md transition-all gap-3" x-show="search === '' || '{{ strtolower(addslashes($prod->code . ' ' . $prod->name)) }}'.includes(search.toLowerCase())">
                            <input type="checkbox" name="suggested_products[]" value="{{ $prod->id }}" {{ in_array($prod->id, $suggestedProductIds) ? 'checked' : '' }} class="w-5 h-5 text-primary border-outline-variant rounded focus:ring-primary">
                            <div class="w-12 h-12 rounded-lg overflow-hidden bg-surface-gray flex-shrink-0 border border-outline-variant/30">
                                @if($prod->images->isNotEmpty())
                                    <img src="{{ $prod->images->first()->url }}" alt="{{ $prod->name }}" class="w-full h-full object-cover">
                                @else
                                    <div class="w-full h-full flex items-center justify-center text-on-surface-variant">
                                        <span class="material-symbols-outlined text-[20px]">image</span>
                                    </div>
                                @endif
                            </div>
                            <div class="flex-1 min-w-0">
                                <h4 class="text-label-md text-on-surface font-semibold truncate block">{{ $prod->code }}</h4>
                                <p class="text-label-xs text-on-surface-variant truncate mt-0.5">{{ $prod->name }}</p>
                            </div>
                        </label>
                    @endforeach
                @else
                    <div class="col-span-full py-12 text-center text-on-surface-variant">
                        <p class="text-body-md">Belum ada produk lain yang tersedia untuk disarankan.</p>
                    </div>
                @endif
            </div>

            <div class="flex justify-end gap-3 mt-8 pt-6 border-t border-outline-variant/30">
                <a href="{{ route('product-suggestions.index') }}" class="inline-flex items-center gap-1.5 px-4 py-2.5 border border-outline-variant text-on-surface-variant hover:bg-surface-container rounded-xl text-xs font-semibold transition-colors">Batal</a>
                <button type="submit" class="btn-save inline-flex items-center gap-2 px-6 py-2.5 bg-primary text-white hover:opacity-90 rounded-xl text-xs font-bold transition-all shadow-sm active:scale-95">Simpan Saran</button>
            </div>
        </div>
    </form>
@endsection
