@extends('layouts.app')

@section('title', 'Create FAQ')

@section('content')
<div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
    <div>
        <h1 class="font-headline-lg text-headline-lg text-on-surface">Create FAQ</h1>
        <nav class="flex items-center gap-2 text-body-md text-on-surface-variant mt-1">
            <a href="{{ route('dashboard') }}" class="text-primary hover:underline">Dashboard</a>
            <span class="material-symbols-outlined text-[16px]">chevron_right</span>
            <a href="{{ route('content.faq.index') }}" class="text-primary hover:underline">FAQ</a>
            <span class="material-symbols-outlined text-[16px]">chevron_right</span>
            <span>Create</span>
        </nav>
    </div>
    <a href="{{ route('content.faq.index') }}" class="flex items-center gap-2 px-4 py-2 bg-surface-container-lowest border border-outline-variant text-secondary rounded-lg font-label-md hover:bg-surface-container transition-all">
        <span class="material-symbols-outlined text-[18px]">arrow_back</span> Back
    </a>
</div>

<form method="POST" action="{{ route('content.faq.store') }}" class="space-y-6">
    @csrf
    <div class="bg-white rounded-xl shadow-sm border border-outline-variant/30 p-6">
        <div class="space-y-1.5">
            <label class="block text-label-sm font-medium text-on-surface-variant">Question <span class="text-danger">*</span></label>
            <input type="text" name="question" class="w-full px-3 py-2 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary/20 focus:outline-none" placeholder="Enter question..." required value="{{ old('question') }}">
            @error('question')<p class="text-danger text-sm">{{ $message }}</p>@enderror
        </div>
        <div class="space-y-1.5 mt-4">
            <label class="block text-label-sm font-medium text-on-surface-variant">Answer <span class="text-danger">*</span></label>
            <textarea name="answer" rows="6" class="w-full px-3 py-2 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary/20 focus:outline-none" placeholder="Enter answer..." required>{{ old('answer') }}</textarea>
            @error('answer')<p class="text-danger text-sm">{{ $message }}</p>@enderror
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
            <div class="space-y-1.5">
                <label class="block text-label-sm font-medium text-on-surface-variant">Sort Order</label>
                <input type="number" name="sort_order" value="{{ old('sort_order', 0) }}" min="0" class="w-full px-3 py-2 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary/20 focus:outline-none">
                @error('sort_order')<p class="text-danger text-sm">{{ $message }}</p>@enderror
            </div>
            <div class="space-y-1.5">
                <label class="block text-label-sm font-medium text-on-surface-variant">Status</label>
                <select name="is_published" class="w-full px-3 py-2 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary/20 focus:outline-none bg-white">
                    <option value="1" {{ old('is_published', '1') == '1' ? 'selected' : '' }}>Published</option>
                    <option value="0" {{ old('is_published') == '0' ? 'selected' : '' }}>Draft</option>
                </select>
            </div>
        </div>
    </div>
    <div class="flex justify-end gap-4">
        <a href="{{ route('content.faq.index') }}" class="px-8 py-3 border border-outline-variant text-primary font-bold rounded-lg hover:bg-surface-container transition-colors">Cancel</a>
        <button type="submit" class="px-10 py-3 bg-primary text-white rounded-lg font-label-md hover:opacity-90 transition-all shadow-sm">Save FAQ</button>
    </div>
</form>
@endsection
