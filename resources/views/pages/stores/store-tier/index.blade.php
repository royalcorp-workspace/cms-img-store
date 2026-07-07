@extends('layouts.app')

@section('title', 'Store Tiers')

@section('content')
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="font-headline-lg text-headline-lg text-on-surface">Store Tiers</h1>
            <nav class="flex items-center gap-2 text-body-md text-on-surface-variant mt-1">
                <a href="{{ route('dashboard') }}" class="text-primary hover:underline">Dashboard</a>
                <span class="material-symbols-outlined text-[16px]">chevron_right</span>
                <span>Store Tiers</span>
            </nav>
        </div>
        <a href="{{ route('store-tiers.create') }}" class="flex items-center gap-2 px-4 py-2 bg-primary text-white rounded-lg font-label-md hover:opacity-90 transition-all shadow-sm">
            <span class="material-symbols-outlined text-[18px]">add</span> Create Tier
        </a>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-outline-variant/30 overflow-hidden">
        <div class="p-4 border-b border-outline-variant flex flex-col sm:flex-row gap-3 justify-between">
            <form method="GET" class="flex items-center gap-2">
                <div class="relative">
                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-on-surface-variant text-[18px] material-symbols-outlined">search</span>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search..." class="pl-9 pr-4 py-2 border border-outline-variant rounded-lg text-body-md focus:ring-2 focus:ring-primary/20 focus:outline-none">
                </div>
                <button type="submit" class="px-4 py-2 bg-primary text-white rounded-lg font-label-md hover:opacity-90 transition-all shadow-sm">Search</button>
            </form>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-surface-gray">
                        <th class="px-gutter py-4 font-label-md text-label-md text-on-surface-variant uppercase tracking-wider">Code</th>
                        <th class="px-gutter py-4 font-label-md text-label-md text-on-surface-variant uppercase tracking-wider">Name</th>
                        <th class="px-gutter py-4 font-label-md text-label-md text-on-surface-variant uppercase tracking-wider">Level</th>
                        <th class="px-gutter py-4 font-label-md text-label-md text-on-surface-variant uppercase tracking-wider">Credit Limit</th>
                        <th class="px-gutter py-4 font-label-md text-label-md text-on-surface-variant uppercase tracking-wider">Status</th>
                        <th class="px-gutter py-4 font-label-md text-label-md text-on-surface-variant uppercase tracking-wider text-center">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-outline-variant/20">
                    @forelse($tiers as $tier)
                    <tr class="hover:bg-surface-container/30 transition-colors">
                        <td class="px-gutter py-4 font-body-md text-body-md text-on-surface font-semibold">{{ $tier->code }}</td>
                        <td class="px-gutter py-4 font-body-md text-body-md text-on-surface">{{ $tier->name }}</td>
                        <td class="px-gutter py-4 font-body-md text-body-md text-on-surface-variant">{{ $tier->level }}</td>
                        <td class="px-gutter py-4 font-body-md text-body-md text-on-surface-variant">Rp{{ number_format($tier->credit_limit, 2, ',', '.') }}</td>
                        <td class="px-gutter py-4">
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-label-sm {{ $tier->status ? 'bg-success/10 text-success' : 'bg-danger/10 text-danger' }}">
                                <span class="w-1.5 h-1.5 rounded-full bg-current"></span> {{ $tier->status ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td class="px-gutter py-4">
                            <div class="flex gap-2">
                                <a href="{{ route('store-tiers.edit', $tier->id) }}" class="text-on-surface-variant hover:text-primary" title="Edit"><span class="material-symbols-outlined text-[18px]">edit</span></a>
                                <form action="{{ route('store-tiers.destroy', $tier->id) }}" method="POST" class="inline" onsubmit="return confirm('Delete this tier?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-on-surface-variant hover:text-danger" title="Delete"><span class="material-symbols-outlined text-[18px]">delete</span></button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-gutter py-8 text-center text-on-surface-variant">No store tiers found</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-gutter py-4 border-t border-outline-variant bg-surface-container-low/30 flex justify-between items-center">
            <p class="font-body-md text-body-md text-on-surface-variant">Showing {{ $tiers->firstItem() ?? 0 }}-{{ $tiers->lastItem() ?? 0 }} of {{ number_format($tiers->total()) }} entries</p>
            <div class="flex gap-2">{{ $tiers->links() }}</div>
        </div>
    </div>
@endsection
