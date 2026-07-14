@extends('layouts.app')

@section('title', 'Create Shipping Address Rate')

@section('content')
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="font-headline-lg text-headline-lg text-on-surface">Create Shipping Rate</h1>
            <nav class="flex items-center gap-2 text-body-md text-on-surface-variant mt-1">
                <a href="{{ route('dashboard') }}" class="text-primary hover:underline">Dashboard</a>
                <span class="material-symbols-outlined text-[16px]">chevron_right</span>
                <a href="{{ route('shipping-addresses.index') }}" class="text-primary hover:underline">Shipping Address Rates</a>
                <span class="material-symbols-outlined text-[16px]">chevron_right</span>
                <span>Create</span>
            </nav>
        </div>
        <a href="{{ route('shipping-addresses.index') }}" class="flex items-center gap-2 px-4 py-2 bg-surface-container-lowest border border-outline-variant text-secondary rounded-lg font-label-md hover:bg-surface-container transition-all">
            <span class="material-symbols-outlined text-[18px]">arrow_back</span> Back
        </a>
    </div>

    <form method="POST" action="{{ route('shipping-addresses.store') }}" class="space-y-6">
        @csrf
        <div class="bg-white rounded-xl shadow-sm border border-outline-variant/30 p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div class="space-y-1.5">
                    <label class="block text-label-sm font-medium text-on-surface-variant">Sub District <span class="text-label-xs text-on-surface-variant/70">(Optional, empty for global rate)</span></label>
                    <select name="sub_district_id" class="w-full px-3 py-2 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary/20 focus:outline-none bg-white">
                        <option value="">All Sub Districts (Global)</option>
                        @foreach($subDistricts as $subDistrict)
                            <option value="{{ $subDistrict->id }}" {{ old('sub_district_id') == $subDistrict->id ? 'selected' : '' }}>{{ $subDistrict->sub_district }} ({{ $subDistrict->district }})</option>
                        @endforeach
                    </select>
                    @error('sub_district_id')<p class="text-danger text-sm">{{ $message }}</p>@enderror
                </div>
                <div class="space-y-1.5">
                    <label class="block text-label-sm font-medium text-on-surface-variant">Service Type <span class="text-danger">*</span></label>
                    <select name="type" class="w-full px-3 py-2 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary/20 focus:outline-none bg-white" required>
                        <option value="1" {{ old('type') == '1' ? 'selected' : '' }}>Regular</option>
                        <option value="2" {{ old('type') == '2' ? 'selected' : '' }}>Express</option>
                        <option value="3" {{ old('type') == '3' ? 'selected' : '' }}>Same Day</option>
                        <option value="4" {{ old('type') == '4' ? 'selected' : '' }}>Instant</option>
                    </select>
                    @error('type')<p class="text-danger text-sm">{{ $message }}</p>@enderror
                </div>
            </div>

            <div class="border-t border-outline-variant/30 pt-6">
                <h3 class="font-headline-md text-headline-md text-on-surface mb-4">Courier Rates</h3>
                <div class="overflow-x-auto border border-outline-variant/30 rounded-xl">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-surface-gray border-b border-outline-variant/30">
                                <th class="px-gutter py-3 font-label-md text-label-md text-on-surface-variant uppercase tracking-wider w-1/3">Courier</th>
                                <th class="px-gutter py-3 font-label-md text-label-md text-on-surface-variant uppercase tracking-wider w-1/4">Shipping Fee (Rp)</th>
                                <th class="px-gutter py-3 font-label-md text-label-md text-on-surface-variant uppercase tracking-wider w-1/6">Sort Order</th>
                                <th class="px-gutter py-3 font-label-md text-label-md text-on-surface-variant uppercase tracking-wider w-1/6 text-center">Active</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-outline-variant/20">
                            @foreach($couriers as $index => $courier)
                                <tr class="hover:bg-surface-container/30 transition-colors">
                                    <td class="px-gutter py-4 font-body-md text-body-md text-on-surface font-semibold">
                                        <input type="hidden" name="rates[{{ $index }}][courier_id]" value="{{ $courier->id }}">
                                        {{ $courier->name }}
                                    </td>
                                    <td class="px-gutter py-4">
                                        <input type="number" name="rates[{{ $index }}][price]" value="{{ old("rates.{$index}.price") }}" min="0" placeholder="e.g. 10000 (leave blank to skip)" class="w-full px-3 py-1.5 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary/20 focus:outline-none">
                                    </td>
                                    <td class="px-gutter py-4">
                                        <input type="number" name="rates[{{ $index }}][sort_order]" value="{{ old("rates.{$index}.sort_order", 0) }}" min="0" class="w-full px-3 py-1.5 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary/20 focus:outline-none">
                                    </td>
                                    <td class="px-gutter py-4 text-center">
                                        <input type="hidden" name="rates[{{ $index }}][is_active]" value="0">
                                        <input type="checkbox" name="rates[{{ $index }}][is_active]" value="1" {{ old("rates.{$index}.is_active", '1') == '1' ? 'checked' : '' }} class="w-4 h-4 text-primary border-outline-variant rounded focus:ring-primary">
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="flex justify-end gap-4">
            <a href="{{ route('shipping-addresses.index') }}" class="px-8 py-3 border border-outline-variant text-primary font-bold rounded-lg hover:bg-surface-container transition-colors">Cancel</a>
            <button type="submit" class="px-10 py-3 bg-primary text-white rounded-lg font-label-md hover:opacity-90 transition-all shadow-sm">Save Rates</button>
        </div>
    </form>
@endsection
