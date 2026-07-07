@extends('layouts.app')

@section('title', 'Edit Privacy Policy')

@section('content')
<div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
    <div>
        <h1 class="font-headline-lg text-headline-lg text-on-surface">Edit Privacy Policy</h1>
        <nav class="flex items-center gap-2 text-body-md text-on-surface-variant mt-1">
            <a href="{{ route('dashboard') }}" class="text-primary hover:underline">Dashboard</a>
            <span class="material-symbols-outlined text-[16px]">chevron_right</span>
            <a href="{{ route('content.privacy.index') }}" class="text-primary hover:underline">Privacy Policy</a>
            <span class="material-symbols-outlined text-[16px]">chevron_right</span>
            <span>Edit</span>
        </nav>
    </div>
    <a href="{{ route('content.privacy.index') }}" class="flex items-center gap-2 px-4 py-2 bg-surface-container-lowest border border-outline-variant text-secondary rounded-lg font-label-md hover:bg-surface-container transition-all">
        <span class="material-symbols-outlined text-[18px]">arrow_back</span> Back
    </a>
</div>

<form method="POST" action="{{ route('content.privacy.update', $item->id) }}" class="space-y-6">
    @csrf
    @method('PUT')
    <div class="bg-white rounded-xl shadow-sm border border-outline-variant/30 p-6">
        <div class="space-y-1.5">
            <label class="block text-label-sm font-medium text-on-surface-variant">Title <span class="text-danger">*</span></label>
            <input type="text" name="title" class="w-full px-3 py-2 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary/20 focus:outline-none" placeholder="Policy title..." required value="{{ old('title', $item->title) }}">
            @error('title')<p class="text-danger text-sm">{{ $message }}</p>@enderror
        </div>
        <div class="space-y-1.5 mt-4">
            <label class="block text-label-sm font-medium text-on-surface-variant">Slug <span class="text-danger">*</span></label>
            <input type="text" name="slug" class="w-full px-3 py-2 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary/20 focus:outline-none" placeholder="e.g., kebijakan-privasi" required value="{{ old('slug', $item->slug) }}">
            @error('slug')<p class="text-danger text-sm">{{ $message }}</p>@enderror
        </div>
        <div class="space-y-1.5 mt-4">
            <label class="block text-label-sm font-medium text-on-surface-variant">Content <span class="text-danger">*</span></label>
            <textarea name="content" rows="10" class="w-full px-3 py-2 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary/20 focus:outline-none" placeholder="Full policy content..." required>{{ old('content', $item->content) }}</textarea>
            @error('content')<p class="text-danger text-sm">{{ $message }}</p>@enderror
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4">
            <div class="space-y-1.5">
                <label class="block text-label-sm font-medium text-on-surface-variant">Version</label>
                <input type="text" name="version" maxlength="50" class="w-full px-3 py-2 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary/20 focus:outline-none" placeholder="1.0" value="{{ old('version', $item->version) }}">
                @error('version')<p class="text-danger text-sm">{{ $message }}</p>@enderror
            </div>
            <div class="space-y-1.5">
                <label class="block text-label-sm font-medium text-on-surface-variant">Effective Date</label>
                <input type="date" name="effective_date" class="w-full px-3 py-2 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary/20 focus:outline-none" value="{{ old('effective_date', $item->effective_date?->format('Y-m-d')) }}">
                @error('effective_date')<p class="text-danger text-sm">{{ $message }}</p>@enderror
            </div>
            <div class="space-y-1.5">
                <label class="block text-label-sm font-medium text-on-surface-variant">Sort Order</label>
                <input type="number" name="sort_order" value="{{ old('sort_order', $item->sort_order) }}" min="0" class="w-full px-3 py-2 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary/20 focus:outline-none">
                @error('sort_order')<p class="text-danger text-sm">{{ $message }}</p>@enderror
            </div>
        </div>
        <div class="flex items-center gap-6 mt-4">
            <label class="flex items-center gap-2">
                <input type="checkbox" name="is_published" value="1" {{ old('is_published', $item->is_published) ? 'checked' : '' }} class="w-4 h-4 rounded border-outline-variant text-primary focus:ring-primary">
                <span class="text-label-sm font-medium text-on-surface-variant">Published</span>
            </label>
        </div>
    </div>
    <div class="flex justify-end gap-4">
        <a href="{{ route('content.privacy.index') }}" class="px-8 py-3 border border-outline-variant text-primary font-bold rounded-lg hover:bg-surface-container transition-colors">Cancel</a>
        <button type="submit" class="px-10 py-3 bg-primary text-white rounded-lg font-label-md hover:opacity-90 transition-all shadow-sm">Update Policy</button>
    </div>
</form>
@endsection
