@extends('layouts.app')

@section('title', 'Product Tags')

@section('content')
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="font-headline-lg text-headline-lg text-on-surface">Product Tags</h1>
            <nav class="flex items-center gap-2 text-label-sm text-on-surface-variant mt-1 font-medium">
                <a href="{{ route('dashboard') }}" class="hover:text-primary transition-colors">eCommerce</a>
                <span class="material-symbols-outlined text-[14px]">chevron_right</span>
                <span class="text-on-surface">Product Tags</span>
            </nav>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('tags.create') }}" class="flex items-center gap-2 px-5 py-2 bg-primary text-white font-label-md hover:opacity-90 transition-all rounded-xl shadow-sm">
                <span class="material-symbols-outlined text-[18px]">add</span>
                Create Tag
            </a>
        </div>
    </div>

    @include('layouts.partials.product-submenu')

    @if(session('success'))
        <div class="mb-6 p-4 bg-success/10 border border-success/20 text-success rounded-xl font-body-md text-body-md flex items-center gap-2">
            <span class="material-symbols-outlined text-[20px]">check_circle</span>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    <div class="bg-white rounded-xl shadow-sm border border-outline-variant/30 overflow-hidden mb-8">
        <div class="p-4 border-b border-outline-variant/30 bg-surface-container-lowest">
            <form method="GET" action="{{ route('tags.index') }}" class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div class="relative flex-1 max-w-md">
                    <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-on-surface-variant text-[18px]">search</span>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama atau slug tag..." class="w-full h-10 pl-9 pr-3 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary/20 focus:border-primary focus:outline-none text-sm bg-white transition-all">
                </div>
                <div class="flex items-center gap-2">
                    <button type="submit" class="h-10 px-4 bg-primary text-white hover:bg-primary/90 rounded-lg text-sm font-medium transition-colors shadow-sm inline-flex items-center gap-1.5 cursor-pointer">
                        <span class="material-symbols-outlined text-[18px]">filter_list</span>
                        <span>Filter</span>
                    </button>
                    @if(request()->filled('search'))
                        <a href="{{ route('tags.index') }}" class="h-10 px-3 bg-danger/10 text-danger hover:bg-danger/20 rounded-lg text-sm font-medium transition-colors inline-flex items-center gap-1" title="Reset Filter">
                            <span class="material-symbols-outlined text-[18px]">restart_alt</span>
                            <span>Reset</span>
                        </a>
                    @endif
                </div>
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-outline-variant/20 bg-surface-container/20">
                        <th class="px-6 py-4 font-label-md text-label-md text-on-surface-variant">Tag Name</th>
                        <th class="px-6 py-4 font-label-md text-label-md text-on-surface-variant">Slug</th>
                        <th class="px-6 py-4 font-label-md text-label-md text-on-surface-variant">Associated Products</th>
                        <th class="px-6 py-4 font-label-md text-label-md text-on-surface-variant">Sort Order</th>
                        <th class="px-6 py-4 font-label-md text-label-md text-on-surface-variant text-center">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-outline-variant/20">
                    @forelse($tags as $tag)
                        <tr class="hover:bg-surface-container/30 transition-colors group">
                            <td class="px-6 py-4 font-bold text-on-surface text-sm">
                                <span class="inline-flex items-center gap-2">
                                    <span class="material-symbols-outlined text-primary text-[18px]">label</span>
                                    <span>{{ $tag->name }}</span>
                                </span>
                            </td>
                            <td class="px-6 py-4 font-mono text-xs text-on-surface-variant">
                                <span class="bg-surface-container-low px-2.5 py-1 rounded-md border border-outline-variant/40">{{ $tag->slug }}</span>
                            </td>
                            <td class="px-6 py-4 text-sm text-on-surface-variant">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-primary/10 text-primary">
                                    {{ $tag->products_count ?? 0 }} Produk
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm text-on-surface-variant">
                                {{ $tag->sort_order ?? 0 }}
                            </td>
                            <td class="px-6 py-4 text-center">
                                <div class="flex items-center justify-center gap-1">
                                    <a href="{{ route('tags.edit', $tag->id) }}" class="p-1.5 text-on-surface-variant hover:text-primary rounded-lg hover:bg-surface-container transition-colors" title="Edit Tag">
                                        <span class="material-symbols-outlined text-[18px]">edit</span>
                                    </a>
                                    <form action="{{ route('tags.destroy', $tag->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus tag ini?')" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-1.5 text-on-surface-variant hover:text-danger rounded-lg hover:bg-surface-container transition-colors cursor-pointer" title="Hapus Tag">
                                            <span class="material-symbols-outlined text-[18px]">delete</span>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center text-on-surface-variant text-sm">
                                <span class="material-symbols-outlined text-[40px] text-outline-variant mb-2">label_off</span>
                                <p>Belum ada data Product Tag yang ditemukan.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($tags->hasPages())
            <div class="p-4 border-t border-outline-variant/30">
                {{ $tags->links() }}
            </div>
        @endif
    </div>
@endsection
