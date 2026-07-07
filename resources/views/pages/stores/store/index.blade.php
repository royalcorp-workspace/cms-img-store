@extends('layouts.app')

@section('title', 'Stores')

@section('content')
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="font-headline-lg text-headline-lg text-on-surface">Stores</h1>
            <nav class="flex items-center gap-2 text-body-md text-on-surface-variant mt-1">
                <a href="{{ route('dashboard') }}" class="text-primary hover:underline">Dashboard</a>
                <span class="material-symbols-outlined text-[16px]">chevron_right</span>
                <span>Stores</span>
            </nav>
        </div>
        <a href="{{ route('stores.create') }}" class="flex items-center gap-2 px-4 py-2 bg-primary text-white rounded-lg font-label-md hover:opacity-90 transition-all shadow-sm">
            <span class="material-symbols-outlined text-[18px]">add</span> Create Store
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
            <div class="flex items-center gap-2">
                <select name="store_group_id" class="px-3 py-2 border border-outline-variant rounded-lg text-body-md focus:ring-2 focus:ring-primary/20 focus:outline-none bg-white" onchange="this.form.submit()">
                    <option value="">All Groups</option>
                    @foreach($storeGroups as $group)
                        <option value="{{ $group->id }}" {{ request('store_group_id') == $group->id ? 'selected' : '' }}>{{ $group->name }}</option>
                    @endforeach
                </select>
                <select name="tier_id" class="px-3 py-2 border border-outline-variant rounded-lg text-body-md focus:ring-2 focus:ring-primary/20 focus:outline-none bg-white" onchange="this.form.submit()">
                    <option value="">All Tiers</option>
                    @foreach($tiers as $tier)
                        <option value="{{ $tier->id }}" {{ request('tier_id') == $tier->id ? 'selected' : '' }}>{{ $tier->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-surface-gray">
                        <th class="px-gutter py-4 font-label-md text-label-md text-on-surface-variant uppercase tracking-wider">Code</th>
                        <th class="px-gutter py-4 font-label-md text-label-md text-on-surface-variant uppercase tracking-wider">Name</th>
                        <th class="px-gutter py-4 font-label-md text-label-md text-on-surface-variant uppercase tracking-wider">Group</th>
                        <th class="px-gutter py-4 font-label-md text-label-md text-on-surface-variant uppercase tracking-wider">Tier</th>
                        <th class="px-gutter py-4 font-label-md text-label-md text-on-surface-variant uppercase tracking-wider">Credit Limit</th>
                        <th class="px-gutter py-4 font-label-md text-label-md text-on-surface-variant uppercase tracking-wider">Status</th>
                        <th class="px-gutter py-4 font-label-md text-label-md text-on-surface-variant uppercase tracking-wider text-center">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-outline-variant/20">
                    @forelse($stores as $store)
                    <tr class="hover:bg-surface-container/30 transition-colors">
                        <td class="px-gutter py-4 font-body-md text-body-md text-on-surface font-semibold">{{ $store->code }}</td>
                        <td class="px-gutter py-4 font-body-md text-body-md text-on-surface">{{ $store->name }}</td>
                        <td class="px-gutter py-4 font-body-md text-body-md text-on-surface-variant">{{ $store->group->name ?? '-' }}</td>
                        <td class="px-gutter py-4 font-body-md text-body-md text-on-surface-variant">{{ $store->tier->name ?? '-' }}</td>
                        <td class="px-gutter py-4 font-body-md text-body-md text-on-surface-variant">Rp{{ number_format($store->credit_limit, 2, ',', '.') }}</td>
                        <td class="px-gutter py-4">
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-label-sm {{ $store->status ? 'bg-success/10 text-success' : 'bg-danger/10 text-danger' }}">
                                <span class="w-1.5 h-1.5 rounded-full bg-current"></span> {{ $store->status ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td class="px-gutter py-4">
                            <div class="flex gap-2">
                                <a href="{{ route('stores.edit', $store->id) }}" class="text-on-surface-variant hover:text-primary" title="Edit"><span class="material-symbols-outlined text-[18px]">edit</span></a>
                                <form action="{{ route('stores.destroy', $store->id) }}" method="POST" class="inline" onsubmit="return confirm('Delete this store?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-on-surface-variant hover:text-danger" title="Delete"><span class="material-symbols-outlined text-[18px]">delete</span></button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-gutter py-8 text-center text-on-surface-variant">No stores found</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-gutter py-4 border-t border-outline-variant bg-surface-container-low/30 flex justify-between items-center">
            <p class="font-body-md text-body-md text-on-surface-variant">Showing {{ $stores->firstItem() ?? 0 }}-{{ $stores->lastItem() ?? 0 }} of {{ number_format($stores->total()) }} entries</p>
            <div class="flex gap-2">{{ $stores->links() }}</div>
        </div>
    </div>
@endsection
