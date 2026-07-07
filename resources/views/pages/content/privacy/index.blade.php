@extends('layouts.app')

@section('title', 'Privacy Policy')

@section('content')
<div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
    <div>
        <h1 class="font-headline-lg text-headline-lg text-on-surface">Privacy Policy</h1>
        <nav class="flex items-center gap-2 text-body-md text-on-surface-variant mt-1">
            <a href="{{ route('dashboard') }}" class="text-primary hover:underline">Dashboard</a>
            <span class="material-symbols-outlined text-[16px]">chevron_right</span>
            <span>Privacy Policy</span>
        </nav>
    </div>
    <a href="{{ route('content.privacy.create') }}" class="flex items-center gap-2 px-4 py-2 bg-primary text-white rounded-lg font-label-md hover:opacity-90 transition-all shadow-sm">
        <span class="material-symbols-outlined text-[18px]">add</span> Create Policy
    </a>
</div>

<div class="bg-white rounded-xl shadow-sm border border-outline-variant/30 overflow-hidden">
    <div class="p-4 border-b border-outline-variant flex flex-col sm:flex-row gap-3 justify-between">
        <form method="GET" class="flex items-center gap-2">
            <div class="relative">
                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-on-surface-variant text-[18px] material-symbols-outlined">search</span>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search policies..." class="pl-9 pr-4 py-2 border border-outline-variant rounded-lg text-body-md focus:ring-2 focus:ring-primary/20 focus:outline-none">
            </div>
            <button type="submit" class="px-4 py-2 bg-primary text-white rounded-lg font-label-md hover:opacity-90 transition-all shadow-sm">Search</button>
        </form>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-surface-gray">
                    <th class="px-gutter py-4 font-label-md text-label-md text-on-surface-variant uppercase tracking-wider">Title</th>
                    <th class="px-gutter py-4 font-label-md text-label-md text-on-surface-variant uppercase tracking-wider">Version</th>
                    <th class="px-gutter py-4 font-label-md text-label-md text-on-surface-variant uppercase tracking-wider">Effective Date</th>
                    <th class="px-gutter py-4 font-label-md text-label-md text-on-surface-variant uppercase tracking-wider">Status</th>
                    <th class="px-gutter py-4 font-label-md text-label-md text-on-surface-variant uppercase tracking-wider text-center">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-outline-variant/20">
                @forelse($items as $item)
                <tr class="hover:bg-surface-container/30 transition-colors">
                    <td class="px-gutter py-4 font-body-md text-body-md text-on-surface font-semibold">{{ $item->title }}</td>
                    <td class="px-gutter py-4 font-body-md text-body-md text-on-surface-variant">{{ $item->version ?? '-' }}</td>
                    <td class="px-gutter py-4 font-body-md text-body-md text-on-surface-variant">{{ $item->effective_date?->format('M j, Y') ?? '-' }}</td>
                    <td class="px-gutter py-4">
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-label-sm {{ $item->is_published ? 'bg-success/10 text-success' : 'bg-warning/10 text-warning' }}">
                            <span class="w-1.5 h-1.5 rounded-full bg-current"></span> {{ $item->is_published ? 'Published' : 'Draft' }}
                        </span>
                    </td>
                    <td class="px-gutter py-4">
                        <div class="flex gap-2">
                            <a href="{{ route('content.privacy.edit', $item->id) }}" class="text-on-surface-variant hover:text-primary" title="Edit"><span class="material-symbols-outlined text-[18px]">edit</span></a>
                            <form action="{{ route('content.privacy.destroy', $item->id) }}" method="POST" class="inline" onsubmit="return confirm('Delete this policy?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-on-surface-variant hover:text-danger" title="Delete"><span class="material-symbols-outlined text-[18px]">delete</span></button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-gutter py-8 text-center text-on-surface-variant">No policies found</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="px-gutter py-4 border-t border-outline-variant bg-surface-container-low/30 flex justify-between items-center">
        <p class="font-body-md text-body-md text-on-surface-variant">Showing {{ $items->firstItem() ?? 0 }}-{{ $items->lastItem() ?? 0 }} of {{ number_format($items->total()) }} entries</p>
        <div class="flex gap-2">{{ $items->links() }}</div>
    </div>
</div>
@endsection
