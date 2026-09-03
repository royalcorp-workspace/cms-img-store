@extends('layouts.app')

@section('title', 'Event Management')

@section('content')
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="font-headline-lg text-headline-lg text-on-surface">Event Campaigns</h1>
            <nav class="flex items-center gap-2 text-label-sm text-on-surface-variant mt-1 font-medium">
                <a href="{{ route('dashboard') }}" class="flex items-center gap-2 px-5 py-2 bg-primary text-white font-label-md hover:opacity-90 transition-all">eCommerce</a>
                <span class="material-symbols-outlined text-[14px]">chevron_right</span>
                <span class="text-on-surface">Events</span>
            </nav>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('events.create') }}" class="flex items-center gap-2 px-5 py-2 bg-primary text-white font-label-md hover:opacity-90 transition-all">
                <span class="material-symbols-outlined text-[18px]">add</span>
                Create Event
            </a>
        </div>
    </div>

    @include('layouts.partials.promotions-submenu')

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
                        <th class="px-6 py-4 font-label-md text-label-md text-on-surface-variant">Event details</th>
                        <th class="px-6 py-4 font-label-md text-label-md text-on-surface-variant">Date Range</th>
                        <th class="px-6 py-4 font-label-md text-label-md text-on-surface-variant">Popup Status</th>
                        <th class="px-6 py-4 font-label-md text-label-md text-on-surface-variant">Promo Code</th>
                        <th class="px-6 py-4 font-label-md text-label-md text-on-surface-variant">Status</th>
                        <th class="px-6 py-4 font-label-md text-label-md text-on-surface-variant text-center">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-outline-variant/20">
                    @forelse($events ?? [] as $event)
                        @php
                            $promo = $event->priceProductSettings->first();
                            $popup = $event->popup;
                        @endphp
                        <tr class="hover:bg-surface-container/30 transition-colors group">
                            <td class="px-6 py-4">
                                <span class="font-headline-md text-[14px] font-semibold text-on-surface">{{ $event->title }}</span>
                                <p class="text-label-sm text-on-surface-variant font-medium mt-0.5">{{ $event->slug }}</p>
                            </td>
                            <td class="px-6 py-4 text-body-md text-on-surface-variant font-medium">
                                <div>{{ $event->start_date->format('d M Y') }}</div>
                                <div class="text-xs text-gray-400">to {{ $event->end_date->format('d M Y') }}</div>
                            </td>
                            <td class="px-6 py-4">
                                @if($popup)
                                    <div class="flex items-center gap-2 justify-center">
                                        @if($popup->image_url)
                                            <div class="w-10 h-10 rounded overflow-hidden border border-outline-variant/20 flex-shrink-0">
                                                <img class="w-full h-full object-cover" src="{{ media_url($popup->image_url) }}" alt="Popup">
                                            </div>
                                        @endif
                                        <div>
                                            <span class="text-xs font-semibold {{ $popup->is_active ? 'text-success' : 'text-danger' }}">
                                                {{ $popup->is_active ? 'Active Popup' : 'Inactive Popup' }}
                                            </span>
                                            <div class="text-[10px] text-gray-400 font-mono">{{ $popup->button_text }}</div>
                                        </div>
                                    </div>
                                @else
                                    <span class="text-label-sm text-on-surface-variant">No Popup</span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                @if($promo)
                                    <span class="font-mono bg-surface-container px-2 py-1 rounded text-primary text-xs font-semibold">{{ $promo->code }}</span>
                                    <div class="text-[10px] text-on-surface-variant font-medium mt-1">
                                        Diskon: {{ $promo->discount_type == 2 ? $promo->discount_value . '%' : 'Rp' . number_format($promo->discount_value, 0) }}
                                    </div>
                                @else
                                    <span class="text-label-sm text-on-surface-variant">-</span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                @if($event->is_active)
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
                                    <a href="{{ route('events.edit', $event->id) }}" class="text-on-surface-variant hover:text-secondary transition-colors" title="Edit"><span class="material-symbols-outlined text-[18px]">edit</span></a>
                                    <form action="{{ route('events.destroy', $event->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this event?')" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-on-surface-variant hover:text-danger transition-colors" title="Delete"><span class="material-symbols-outlined text-[18px]">delete</span></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-8 text-center text-on-surface-variant">No events found</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
