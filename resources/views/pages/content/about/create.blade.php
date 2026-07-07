@extends('layouts.app')

@section('title', 'Create About Us')

@section('content')
<div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
    <div>
        <h1 class="font-headline-lg text-headline-lg text-on-surface">Create About Us</h1>
        <nav class="flex items-center gap-2 text-body-md text-on-surface-variant mt-1">
            <a href="{{ route('dashboard') }}" class="text-primary hover:underline">Dashboard</a>
            <span class="material-symbols-outlined text-[16px]">chevron_right</span>
            <a href="{{ route('content.about.index') }}" class="text-primary hover:underline">About Us</a>
            <span class="material-symbols-outlined text-[16px]">chevron_right</span>
            <span>Create</span>
        </nav>
    </div>
    <a href="{{ route('content.about.index') }}" class="flex items-center gap-2 px-4 py-2 bg-surface-container-lowest border border-outline-variant text-secondary rounded-lg font-label-md hover:bg-surface-container transition-all">
        <span class="material-symbols-outlined text-[18px]">arrow_back</span> Back
    </a>
</div>

<form method="POST" action="{{ route('content.about.store') }}" class="space-y-6">
    @csrf
    <div class="bg-white rounded-xl shadow-sm border border-outline-variant/30 p-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="space-y-1.5">
                <label class="block text-label-sm font-medium text-on-surface-variant">Company Name <span class="text-danger">*</span></label>
                <input type="text" name="company_name" class="w-full px-3 py-2 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary/20 focus:outline-none" placeholder="Company name" required value="{{ old('company_name') }}">
                @error('company_name')<p class="text-danger text-sm">{{ $message }}</p>@enderror
            </div>
            <div class="space-y-1.5">
                <label class="block text-label-sm font-medium text-on-surface-variant">Tagline</label>
                <input type="text" name="tagline" class="w-full px-3 py-2 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary/20 focus:outline-none" placeholder="Short tagline" value="{{ old('tagline') }}">
                @error('tagline')<p class="text-danger text-sm">{{ $message }}</p>@enderror
            </div>
        </div>
        <div class="space-y-1.5 mt-4">
            <label class="block text-label-sm font-medium text-on-surface-variant">Description</label>
            <textarea name="description" rows="4" class="w-full px-3 py-2 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary/20 focus:outline-none" placeholder="Company description...">{{ old('description') }}</textarea>
            @error('description')<p class="text-danger text-sm">{{ $message }}</p>@enderror
        </div>
        <div class="space-y-1.5 mt-4">
            <label class="block text-label-sm font-medium text-on-surface-variant">Vision</label>
            <textarea name="vision" rows="3" class="w-full px-3 py-2 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary/20 focus:outline-none" placeholder="Vision statement...">{{ old('vision') }}</textarea>
            @error('vision')<p class="text-danger text-sm">{{ $message }}</p>@enderror
        </div>
        <div class="space-y-1.5 mt-4">
            <label class="block text-label-sm font-medium text-on-surface-variant">Mission</label>
            <textarea name="mission" rows="3" class="w-full px-3 py-2 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary/20 focus:outline-none" placeholder="Mission statement...">{{ old('mission') }}</textarea>
            @error('mission')<p class="text-danger text-sm">{{ $message }}</p>@enderror
        </div>
        <div class="space-y-1.5 mt-4">
            <label class="block text-label-sm font-medium text-on-surface-variant">Values</label>
            <textarea name="values" rows="3" class="w-full px-3 py-2 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary/20 focus:outline-none" placeholder="Core values...">{{ old('values') }}</textarea>
            @error('values')<p class="text-danger text-sm">{{ $message }}</p>@enderror
            <div class="space-y-1.5 mt-4">
                <label class="block text-label-sm font-medium text-on-surface-variant">Address</label>
                <textarea name="address" rows="2" class="w-full px-3 py-2 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary/20 focus:outline-none" placeholder="Company address...">{{ old('address') }}</textarea>
                @error('address')<p class="text-danger text-sm">{{ $message }}</p>@enderror
            </div>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4">
            <div class="space-y-1.5">
                <label class="block text-label-sm font-medium text-on-surface-variant">Established Year</label>
                <input type="number" name="established_year" value="{{ old('established_year') }}" min="1800" max="{{ date('Y') }}" class="w-full px-3 py-2 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary/20 focus:outline-none">
                @error('established_year')<p class="text-danger text-sm">{{ $message }}</p>@enderror
            </div>
            <div class="space-y-1.5">
                <label class="block text-label-sm font-medium text-on-surface-variant">Phone</label>
                <input type="text" name="phone" maxlength="50" class="w-full px-3 py-2 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary/20 focus:outline-none" placeholder="Phone number" value="{{ old('phone') }}">
                @error('phone')<p class="text-danger text-sm">{{ $message }}</p>@enderror
            </div>
            <div class="space-y-1.5">
                <label class="block text-label-sm font-medium text-on-surface-variant">Email</label>
                <input type="email" name="email" maxlength="255" class="w-full px-3 py-2 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary/20 focus:outline-none" placeholder="email@example.com" value="{{ old('email') }}">
                @error('email')<p class="text-danger text-sm">{{ $message }}</p>@enderror
            </div>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
            <div class="space-y-1.5">
                <label class="block text-label-sm font-medium text-on-surface-variant">Instagram</label>
                <input type="text" name="social_media[instagram]" class="w-full px-3 py-2 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary/20 focus:outline-none" placeholder="https://instagram.com/..." value="{{ old('social_media.instagram') }}">
            </div>
            <div class="space-y-1.5">
                <label class="block text-label-sm font-medium text-on-surface-variant">Facebook</label>
                <input type="text" name="social_media[facebook]" class="w-full px-3 py-2 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary/20 focus:outline-none" placeholder="https://facebook.com/..." value="{{ old('social_media.facebook') }}">
            </div>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
            <div class="space-y-1.5">
                <label class="block text-label-sm font-medium text-on-surface-variant">Twitter / X</label>
                <input type="text" name="social_media[twitter]" class="w-full px-3 py-2 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary/20 focus:outline-none" placeholder="https://x.com/..." value="{{ old('social_media.twitter') }}">
            </div>
            <div class="space-y-1.5">
                <label class="block text-label-sm font-medium text-on-surface-variant">TikTok</label>
                <input type="text" name="social_media[tiktok]" class="w-full px-3 py-2 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary/20 focus:outline-none" placeholder="https://tiktok.com/..." value="{{ old('social_media.tiktok') }}">
            </div>
        </div>
    </div>
    <div class="flex justify-end gap-4">
        <a href="{{ route('content.about.index') }}" class="px-8 py-3 border border-outline-variant text-primary font-bold rounded-lg hover:bg-surface-container transition-colors">Cancel</a>
        <button type="submit" class="px-10 py-3 bg-primary text-white rounded-lg font-label-md hover:opacity-90 transition-all shadow-sm">Save About Us</button>
    </div>
</form>
@endsection
