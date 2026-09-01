@extends('layouts.app')

@section('title', 'Banner Management')

@section('content')
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="font-headline-lg text-headline-lg text-on-surface">Banner Management</h1>
            <nav class="flex items-center gap-2 text-label-sm text-on-surface-variant mt-1 font-medium">
                <a href="{{ route('dashboard') }}" class="hover:text-primary transition-colors">Dashboard</a>
                <span class="material-symbols-outlined text-[14px]">chevron_right</span>
                <span class="text-on-surface">Banners</span>
            </nav>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('content.banners.create') }}" class="flex items-center gap-2 px-5 py-2 bg-primary text-white font-label-md hover:opacity-90 transition-all">
                <span class="material-symbols-outlined text-[18px]">add</span>
                Create Banner
            </a>
        </div>
    </div>

    @include('layouts.partials.content-submenu')

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
                        <th class="px-6 py-4 font-label-md text-label-md text-on-surface-variant">Banner Title</th>
                        <th class="px-6 py-4 font-label-md text-label-md text-on-surface-variant">Type / Size</th>
                        <th class="px-6 py-4 font-label-md text-label-md text-on-surface-variant">Device</th>
                        <th class="px-6 py-4 font-label-md text-label-md text-on-surface-variant">Content Type</th>
                        <th class="px-6 py-4 font-label-md text-label-md text-on-surface-variant">Status</th>
                        <th class="px-6 py-4 font-label-md text-label-md text-on-surface-variant text-center">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-outline-variant/20">
                    @forelse($banners ?? [] as $banner)
                        <tr class="hover:bg-surface-container/30 transition-colors group">
                            <td class="px-6 py-4 text-center">
                                <div class="flex items-center gap-3 justify-center">
                                    <div class="w-16 h-10 bg-surface-gray rounded overflow-hidden flex-shrink-0 border border-outline-variant/20 flex items-center justify-center bg-white">
                                        @if($banner->content_type == 1 && $banner->image_web_url)
                                            <img class="w-full h-full object-cover" src="{{ media_url($banner->image_web_url) }}" alt="{{ $banner->title }}">
                                        @else
                                            <span class="text-[10px] font-bold text-primary font-mono">&lt;/&gt; Embed</span>
                                        @endif
                                    </div>
                                    <div>
                                        <span class="font-headline-md text-[14px] font-semibold text-on-surface">{{ $banner->title }}</span>
                                        <p class="text-label-sm text-on-surface-variant font-medium mt-0.5">{{ $banner->link_url ?: 'No Action Link' }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-body-md text-on-surface font-semibold">
                                    {{ $banner->type == 1 ? 'Home Slider' : 'Running Banner' }}
                                </div>
                                <div class="text-label-sm text-on-surface-variant">
                                    {{ $banner->placement_size == 1 ? 'Main 1920x500' : ($banner->placement_size == 2 ? 'Square 300x300' : 'Custom') }}
                                </div>
                            </td>
                            <td class="px-6 py-4 text-body-md text-on-surface">
                                {{ $banner->device_flag == 1 ? 'All Devices' : ($banner->device_flag == 2 ? 'Web Only' : 'Mobile Only') }}
                            </td>
                            <td class="px-6 py-4 text-body-md text-on-surface">
                                {{ $banner->content_type == 1 ? 'Image Upload' : 'Embed Code / URL' }}
                            </td>
                            <td class="px-6 py-4">
                                @if($banner->is_active)
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
                                <div class="flex items-center gap-1.5">
                                    <a href="{{ route('content.banners.edit', $banner->id) }}" class="text-on-surface-variant hover:text-secondary transition-colors" title="Edit"><span class="material-symbols-outlined text-[18px]">edit</span></a>
                                    <form action="{{ route('content.banners.destroy', $banner->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this banner?')" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-on-surface-variant hover:text-danger transition-colors" title="Delete"><span class="material-symbols-outlined text-[18px]">delete</span></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-8 text-center text-on-surface-variant">No banners found</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
