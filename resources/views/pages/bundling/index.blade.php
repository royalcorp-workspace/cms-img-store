@extends('layouts.app')

@section('title', 'Product Bundling')

@section('content')
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="font-headline-lg text-headline-lg text-on-surface">Product Bundling</h1>
            <nav class="flex items-center gap-2 text-label-sm text-on-surface-variant mt-1 font-medium">
                <a href="{{ route('dashboard') }}" class="hover:text-primary transition-colors">eCommerce</a>
                <span class="material-symbols-outlined text-[14px]">chevron_right</span>
                <span class="text-on-surface">Bundling</span>
            </nav>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('bundlings.create') }}" class="btn-save flex items-center gap-2 px-5 py-2 bg-primary text-white rounded-xl text-xs font-bold hover:opacity-90 transition-all shadow-sm active:scale-95">
                <span class="material-symbols-outlined text-[18px]">add</span>
                <span>Create Bundle</span>
            </a>
        </div>
    </div>

    @include('layouts.partials.promotions-submenu')

    @if(session('success'))
        <div class="mb-6 p-4 rounded-xl bg-success/10 border border-success/20 text-success text-body-md flex items-center gap-2">
            <span class="material-symbols-outlined text-[20px]">check_circle</span>
            <span>{{ session('success') }}</span>
        </div>
    @endif
    @if(session('error'))
        <div class="mb-6 p-4 rounded-xl bg-danger/10 border border-danger/20 text-danger text-body-md flex items-center gap-2">
            <span class="material-symbols-outlined text-[20px]">error</span>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    <div class="bg-white rounded-2xl shadow-sm border border-outline-variant/30 overflow-hidden mb-8">
        <div class="p-4 border-b border-outline-variant/30 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <form action="{{ route('bundlings.index') }}" method="GET" class="flex flex-wrap items-center gap-3">
                <div class="relative">
                    <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-on-surface-variant text-[18px]">search</span>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama bundling..." class="pl-9 pr-4 py-2 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary/20 focus:outline-none w-64 text-label-sm bg-surface-container-lowest">
                </div>
                <button type="submit" class="px-4 py-2 bg-secondary text-white rounded-lg font-label-md text-xs hover:opacity-90 transition-all">Filter</button>
                @if(request('search'))
                    <a href="{{ route('bundlings.index') }}" class="px-3 py-2 border border-outline-variant text-on-surface-variant rounded-lg font-label-md text-xs hover:bg-surface-container transition-all">Reset</a>
                @endif
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-surface-gray border-b border-outline-variant/50">
                        <th class="px-6 py-4 font-label-md text-label-md text-on-surface-variant uppercase tracking-wider">Bundle Name</th>
                        <th class="px-6 py-4 font-label-md text-label-md text-on-surface-variant uppercase tracking-wider">Price</th>
                        <th class="px-6 py-4 font-label-md text-label-md text-on-surface-variant uppercase tracking-wider">Included Products</th>
                        <th class="px-6 py-4 font-label-md text-label-md text-on-surface-variant uppercase tracking-wider text-center">Status</th>
                        <th class="px-6 py-4 font-label-md text-label-md text-on-surface-variant uppercase tracking-wider text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-outline-variant/20">
                    @forelse($bundlings ?? [] as $bundle)
                        <tr class="hover:bg-surface-container/30 transition-colors group">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-12 h-12 rounded-xl bg-surface-gray border border-outline-variant/30 overflow-hidden flex-shrink-0 flex items-center justify-center">
                                        @if($bundle->image_full_url)
                                            <img src="{{ $bundle->image_full_url }}" alt="{{ $bundle->name }}" class="w-full h-full object-cover">
                                        @else
                                            <span class="material-symbols-outlined text-on-surface-variant text-[20px]">package_2</span>
                                        @endif
                                    </div>
                                    <div>
                                        <span class="font-headline-md text-[14px] font-semibold text-on-surface block">{{ $bundle->name }}</span>
                                        <p class="text-label-sm text-on-surface-variant font-medium mt-0.5 font-mono">{{ $bundle->slug }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 font-headline-md text-[14px] font-bold text-on-surface">
                                Rp{{ number_format($bundle->price, 0, ',', '.') }}
                            </td>
                            <td class="px-6 py-4 text-body-md text-on-surface-variant font-medium">
                                <ul class="space-y-1">
                                    @foreach($bundle->items as $item)
                                        <li class="flex items-center gap-1.5 text-xs text-on-surface-variant">
                                            <span class="w-1.5 h-1.5 rounded-full bg-primary flex-shrink-0"></span>
                                            <span>{{ $item->product?->name ?? 'Unknown Product' }}</span>
                                            <span class="font-bold text-primary">(x{{ $item->quantity }})</span>
                                        </li>
                                    @endforeach
                                </ul>
                            </td>
                            <td class="px-6 py-4 text-center">
                                @if($bundle->is_active)
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 bg-success/10 text-success border border-success/20 rounded-full text-[11px] font-semibold uppercase tracking-wider">
                                        <span class="w-1.5 h-1.5 rounded-full bg-success"></span> Aktif
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 bg-danger/10 text-danger border border-danger/20 rounded-full text-[11px] font-semibold uppercase tracking-wider">
                                        <span class="w-1.5 h-1.5 rounded-full bg-danger"></span> Nonaktif
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('bundlings.edit', $bundle->id) }}" class="inline-flex items-center justify-center w-8 h-8 rounded-xl bg-surface-container-high hover:bg-surface-container-highest text-on-surface transition-colors" title="Edit">
                                        <span class="material-symbols-outlined text-[18px]">edit</span>
                                    </a>
                                    <form action="{{ route('bundlings.destroy', $bundle->id) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus bundling ini?')" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="inline-flex items-center justify-center w-8 h-8 rounded-xl bg-danger/10 hover:bg-danger/20 text-danger transition-colors" title="Hapus">
                                            <span class="material-symbols-outlined text-[18px]">delete</span>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-12 text-center text-on-surface-variant">
                                <div class="flex flex-col items-center justify-center">
                                    <span class="material-symbols-outlined text-[48px] mb-2 opacity-40">package_2</span>
                                    <p class="font-label-md">Tidak ada paket bundling yang ditemukan.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if(method_exists($bundlings, 'hasPages') && $bundlings->hasPages())
            <div class="px-6 py-4 border-t border-outline-variant/30 bg-surface-container-low/30 flex flex-col sm:flex-row justify-between items-center gap-3">
                <p class="font-body-md text-body-md text-on-surface-variant">Showing {{ $bundlings->firstItem() }} to {{ $bundlings->lastItem() }} of {{ $bundlings->total() }} bundles</p>
                <div class="flex gap-2">{{ $bundlings->links() }}</div>
            </div>
        @endif
    </div>
@endsection
