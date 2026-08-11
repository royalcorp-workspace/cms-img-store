@extends('layouts.app')

@section('title', 'Add Homepage Section')

@section('content')
<div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
    <div>
        <h1 class="font-headline-lg text-headline-lg text-on-surface">Add Homepage Section</h1>
        <nav class="flex items-center gap-2 text-body-md text-on-surface-variant mt-1">
            <a href="{{ route('dashboard') }}" class="hover:text-primary transition-colors">Dashboard</a>
            <span class="material-symbols-outlined text-[16px]">chevron_right</span>
            <a href="{{ route('content.homepage.index') }}" class="text-primary hover:underline">Homepages</a>
            <span class="material-symbols-outlined text-[16px]">chevron_right</span>
            <span class="material-symbols-outlined text-[18px]">add</span>
        </nav>
    </div>
    <a href="{{ route('content.homepage.index') }}" class="flex items-center gap-2 px-4 py-2 bg-surface-container-lowest border border-outline-variant text-secondary rounded-lg font-label-md hover:bg-surface-container transition-all">
        <span class="material-symbols-outlined text-[18px]">arrow_back</span> Back
    </a>
</div>

@include('layouts.partials.content-submenu')

<form method="POST" action="{{ route('content.homepage.store') }}" class="bg-surface-container-lowest border border-outline-variant rounded-xl p-6">
    @csrf
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="space-y-1.5">
            <label class="block text-label-sm font-medium text-on-surface-variant">Section Title <span class="text-danger">*</span></label>
            <input type="text" name="title" required value="{{ old('title') }}" class="w-full px-3 py-2 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary/20 focus:outline-none" placeholder="e.g. Promo Brand">
            @error('title')<p class="text-danger text-sm">{{ $message }}</p>@enderror
        </div>
        
        <div class="space-y-1.5">
            <label class="block text-label-sm font-medium text-on-surface-variant">Section Key (Unique ID) <span class="text-danger">*</span></label>
            <input type="text" name="section_key" required value="{{ old('section_key') }}" class="w-full px-3 py-2 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary/20 focus:outline-none" placeholder="e.g. promo_brand">
            @error('section_key')<p class="text-danger text-sm">{{ $message }}</p>@enderror
        </div>

        <div class="space-y-1.5">
            <label class="block text-label-sm font-medium text-on-surface-variant">Sort Order <span class="text-danger">*</span></label>
            <input type="number" name="sort_order" required value="{{ old('sort_order', 0) }}" class="w-full px-3 py-2 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary/20 focus:outline-none">
            <p class="text-xs text-on-surface-variant mt-1">Lower numbers appear first (e.g. 1, 2, 3...)</p>
            @error('sort_order')<p class="text-danger text-sm">{{ $message }}</p>@enderror
        </div>
        
        <div class="space-y-1.5 flex flex-col justify-center mt-6">
            <label class="flex items-center gap-2 cursor-pointer">
                <input type="checkbox" name="is_visible" value="1" {{ old('is_visible', true) ? 'checked' : '' }} class="w-5 h-5 text-primary border-outline-variant rounded focus:ring-primary/20">
                <span class="text-label-md font-medium text-on-surface-variant">Show this section on Homepage</span>
            </label>
            @error('is_visible')<p class="text-danger text-sm">{{ $message }}</p>@enderror
        </div>
    </div>
    
    <div class="mt-8 flex justify-end gap-4">
        <a href="{{ route('content.homepage.index') }}" class="px-8 py-3 border border-outline-variant text-primary font-bold rounded-lg hover:bg-surface-container transition-colors">Cancel</a>
        <button type="submit" class="px-10 py-3 bg-primary text-white font-label-md hover:opacity-90 transition-all">Save Section</button>
    </div>
</form>
@endsection
