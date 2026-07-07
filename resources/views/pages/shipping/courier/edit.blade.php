@extends('layouts.app')

@section('title', 'Edit Courier')

@section('content')
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="font-headline-lg text-headline-lg text-on-surface">Edit Courier</h1>
            <nav class="flex items-center gap-2 text-body-md text-on-surface-variant mt-1">
                <a href="{{ route('dashboard') }}" class="text-primary hover:underline">Dashboard</a>
                <span class="material-symbols-outlined text-[16px]">chevron_right</span>
                <a href="{{ route('couriers.index') }}" class="text-primary hover:underline">Couriers</a>
                <span class="material-symbols-outlined text-[16px]">chevron_right</span>
                <span>Edit</span>
            </nav>
        </div>
        <a href="{{ route('couriers.index') }}" class="flex items-center gap-2 px-4 py-2 bg-surface-container-lowest border border-outline-variant text-secondary rounded-lg font-label-md hover:bg-surface-container transition-all">
            <span class="material-symbols-outlined text-[18px]">arrow_back</span> Back
        </a>
    </div>

    <form method="POST" action="{{ route('couriers.update', $courier->id) }}" class="space-y-6">
        @csrf
        @method('PUT')
        <div class="bg-white rounded-xl shadow-sm border border-outline-variant/30 p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="space-y-1.5">
                    <label class="block text-label-sm font-medium text-on-surface-variant">Code <span class="text-danger">*</span></label>
                    <input type="text" name="code" class="w-full px-3 py-2 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary/20 focus:outline-none" placeholder="e.g., JNE" required value="{{ old('code', $courier->code) }}">
                    @error('code')<p class="text-danger text-sm">{{ $message }}</p>@enderror
                </div>
                <div class="space-y-1.5">
                    <label class="block text-label-sm font-medium text-on-surface-variant">Name <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="w-full px-3 py-2 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary/20 focus:outline-none" placeholder="e.g., JNE Express" required value="{{ old('name', $courier->name) }}">
                    @error('name')<p class="text-danger text-sm">{{ $message }}</p>@enderror
                </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                <div class="space-y-1.5">
                    <label class="block text-label-sm font-medium text-on-surface-variant">Type <span class="text-danger">*</span></label>
                    <select name="type" class="w-full px-3 py-2 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary/20 focus:outline-none bg-white" required>
                        <option value="1" {{ old('type', $courier->type ?? '1') == '1' ? 'selected' : '' }}>Regular</option>
                        <option value="2" {{ old('type', $courier->type ?? '1') == '2' ? 'selected' : '' }}>Express</option>
                        <option value="3" {{ old('type', $courier->type ?? '1') == '3' ? 'selected' : '' }}>Same Day</option>
                        <option value="4" {{ old('type', $courier->type ?? '1') == '4' ? 'selected' : '' }}>Instant</option>
                    </select>
                    @error('type')<p class="text-danger text-sm">{{ $message }}</p>@enderror
                </div>
                <div class="space-y-1.5">
                    <label class="block text-label-sm font-medium text-on-surface-variant">Sort Order</label>
                    <input type="number" name="sort_order" value="{{ old('sort_order', $courier->sort_order) }}" min="0" class="w-full px-3 py-2 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary/20 focus:outline-none">
                    @error('sort_order')<p class="text-danger text-sm">{{ $message }}</p>@enderror
                </div>
            </div>
            <div class="mt-4">
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', $courier->is_active ?? true) ? 'checked' : '' }} class="w-4 h-4 text-primary border-outline-variant rounded focus:ring-primary">
                    <span class="text-label-sm font-medium text-on-surface-variant">Active</span>
                </label>
                @error('is_active')<p class="text-danger text-sm">{{ $message }}</p>@enderror
            </div>
        </div>
        <div class="flex justify-end gap-4">
            <a href="{{ route('couriers.index') }}" class="px-8 py-3 border border-outline-variant text-primary font-bold rounded-lg hover:bg-surface-container transition-colors">Cancel</a>
            <button type="submit" class="px-10 py-3 bg-primary text-white rounded-lg font-label-md hover:opacity-90 transition-all shadow-sm">Update Courier</button>
        </div>
    </form>
@endsection
