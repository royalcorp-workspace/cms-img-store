@extends('layouts.app')

@section('title', 'Couriers')

@section('content')
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="font-headline-lg text-headline-lg text-on-surface">Couriers</h1>
            <nav class="flex items-center gap-2 text-body-md text-on-surface-variant mt-1">
                <a href="{{ route('dashboard') }}" class="text-primary hover:underline">Dashboard</a>
                <span class="material-symbols-outlined text-[16px]">chevron_right</span>
                <span>Shipping & Payment</span>
                <span class="material-symbols-outlined text-[16px]">chevron_right</span>
                <span>Couriers</span>
            </nav>
        </div>
        <a href="{{ route('couriers.create') }}" class="flex items-center gap-2 px-4 py-2 bg-primary text-white rounded-lg font-label-md hover:opacity-90 transition-all shadow-sm">
            <span class="material-symbols-outlined text-[18px]">add</span> Create Courier
        </a>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-outline-variant/30 overflow-hidden">
        <div class="p-4 border-b border-outline-variant flex flex-col sm:flex-row gap-3 justify-between">
            <form method="GET" class="flex items-center gap-2">
                <div class="relative">
                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-on-surface-variant text-[18px] material-symbols-outlined">search</span>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search couriers..." class="pl-9 pr-4 py-2 border border-outline-variant rounded-lg text-body-md focus:ring-2 focus:ring-primary/20 focus:outline-none">
                </div>
                <select name="type" class="px-3 py-2 border border-outline-variant rounded-lg text-body-md bg-white">
                    <option value="">All Types</option>
                    <option value="1" {{ request('type') == '1' ? 'selected' : '' }}>Regular</option>
                    <option value="2" {{ request('type') == '2' ? 'selected' : '' }}>Express</option>
                    <option value="3" {{ request('type') == '3' ? 'selected' : '' }}>Same Day</option>
                    <option value="4" {{ request('type') == '4' ? 'selected' : '' }}>Instant</option>
                </select>
                <select name="status" class="px-3 py-2 border border-outline-variant rounded-lg text-body-md bg-white">
                    <option value="">All Status</option>
                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                </select>
                <button type="submit" class="px-4 py-2 bg-primary text-white rounded-lg font-label-md hover:opacity-90 transition-all shadow-sm">Filter</button>
            </form>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-surface-gray">
                        <th class="px-gutter py-4 font-label-md text-label-md text-on-surface-variant uppercase tracking-wider">Code</th>
                        <th class="px-gutter py-4 font-label-md text-label-md text-on-surface-variant uppercase tracking-wider">Name</th>
                        <th class="px-gutter py-4 font-label-md text-label-md text-on-surface-variant uppercase tracking-wider">Type</th>
                        <th class="px-gutter py-4 font-label-md text-label-md text-on-surface-variant uppercase tracking-wider">Status</th>
                        <th class="px-gutter py-4 font-label-md text-label-md text-on-surface-variant uppercase tracking-wider text-center">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-outline-variant/20">
                    @forelse($couriers as $courier)
                    <tr class="hover:bg-surface-container/30 transition-colors {{ !$courier->is_active || $courier->deleted ? 'opacity-60' : '' }}">
                        <td class="px-gutter py-4 font-body-md text-body-md text-on-surface font-semibold">{{ $courier->code }}</td>
                        <td class="px-gutter py-4 font-body-md text-body-md text-on-surface">{{ $courier->name }}</td>
                        <td class="px-gutter py-4">
                            @php
                                $typeLabel = match($courier->type) {
                                    1 => 'Regular',
                                    2 => 'Express',
                                    3 => 'Same Day',
                                    4 => 'Instant',
                                    default => 'Unknown'
                                };
                            @endphp
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-label-sm bg-primary/10 text-primary">{{ $typeLabel }}</span>
                        </td>
                        <td class="px-gutter py-4">
                            @if($courier->deleted)
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-label-sm bg-danger/10 text-danger">
                                    <span class="w-1.5 h-1.5 rounded-full bg-current"></span> Deleted
                                </span>
                            @elseif($courier->is_active)
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-label-sm bg-success/10 text-success">
                                    <span class="w-1.5 h-1.5 rounded-full bg-current"></span> Active
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-label-sm bg-danger/10 text-danger">
                                    <span class="w-1.5 h-1.5 rounded-full bg-current"></span> Inactive
                                </span>
                            @endif
                        </td>
                        <td class="px-gutter py-4">
                            <div class="flex gap-2">
                                <a href="{{ route('couriers.edit', $courier->id) }}" class="text-on-surface-variant hover:text-primary" title="Edit"><span class="material-symbols-outlined text-[18px]">edit</span></a>
                                <form action="{{ route('couriers.destroy', $courier->id) }}" method="POST" class="inline" onsubmit="return confirm('Delete courier {{ $courier->name }}?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-on-surface-variant hover:text-danger" title="Delete"><span class="material-symbols-outlined text-[18px]">delete</span></button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-gutter py-8 text-center text-on-surface-variant">No couriers found</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-gutter py-4 border-t border-outline-variant bg-surface-container-low/30 flex justify-between items-center">
            <p class="font-body-md text-body-md text-on-surface-variant">Showing {{ $couriers->firstItem() ?? 0 }}-{{ $couriers->lastItem() ?? 0 }} of {{ number_format($couriers->total()) }} entries</p>
            <div class="flex gap-2">{{ $couriers->links() }}</div>
        </div>
    </div>
@endsection
