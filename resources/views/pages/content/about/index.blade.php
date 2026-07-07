@extends('layouts.app')

@section('title', 'About Us')

@section('content')
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="font-headline-lg text-headline-lg text-on-surface">About Us</h1>
            <nav class="flex items-center gap-2 text-body-md text-on-surface-variant mt-1">
                <a href="{{ route('dashboard') }}" class="text-primary hover:underline">Dashboard</a>
                <span class="material-symbols-outlined text-[16px]">chevron_right</span>
                <span>About Us</span>
            </nav>
        </div>
        @if($about)
        <a href="{{ route('content.about.edit', $about->id) }}" class="flex items-center gap-2 px-4 py-2 bg-primary text-white rounded-lg font-label-md hover:opacity-90 transition-all shadow-sm">
            <span class="material-symbols-outlined text-[18px]">edit</span> Edit About Us
        </a>
        @endif
    </div>

    @if($about)
        <div class="bg-white rounded-xl shadow-sm border border-outline-variant/30 p-6 mb-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <h2 class="font-headline-md text-headline-md text-on-surface mb-2">{{ $about->company_name }}</h2>
                    @if($about->tagline)
                    <p class="text-body-md text-secondary mb-4">{{ $about->tagline }}</p>
                    @endif
                    @if($about->description)
                    <p class="text-body-md text-on-surface-variant mb-4">{{ $about->description }}</p>
                    @endif
                    @if($about->address)
                    <div class="flex items-start gap-2 text-body-sm text-on-surface-variant mb-2">
                        <span class="material-symbols-outlined text-[18px]">location_on</span>
                        <span>{{ $about->address }}</span>
                    </div>
                    @endif
                    @if($about->phone)
                    <div class="flex items-start gap-2 text-body-sm text-on-surface-variant mb-2">
                        <span class="material-symbols-outlined text-[18px]">phone</span>
                        <span>{{ $about->phone }}</span>
                    </div>
                    @endif
                    @if($about->email)
                    <div class="flex items-start gap-2 text-body-sm text-on-surface-variant mb-2">
                        <span class="material-symbols-outlined text-[18px]">email</span>
                        <span>{{ $about->email }}</span>
                    </div>
                    @endif
                    @if($about->established_year)
                    <div class="flex items-start gap-2 text-body-sm text-on-surface-variant mb-2">
                        <span class="material-symbols-outlined text-[18px]">calendar_today</span>
                        <span>Established {{ $about->established_year }}</span>
                    </div>
                    @endif
                    @if($about->social_media && count(array_filter($about->social_media)) > 0)
                    <div class="flex items-start gap-2 text-body-sm text-on-surface-variant mb-2">
                        <span class="material-symbols-outlined text-[18px]">share</span>
                        <div class="flex flex-wrap gap-2">
                            @if(!empty($about->social_media['instagram']))
                                <a href="{{ $about->social_media['instagram'] }}" target="_blank" class="text-primary hover:underline">Instagram</a>
                            @endif
                            @if(!empty($about->social_media['facebook']))
                                <a href="{{ $about->social_media['facebook'] }}" target="_blank" class="text-primary hover:underline">Facebook</a>
                            @endif
                            @if(!empty($about->social_media['twitter']))
                                <a href="{{ $about->social_media['twitter'] }}" target="_blank" class="text-primary hover:underline">Twitter</a>
                            @endif
                            @if(!empty($about->social_media['whatsapp']))
                                <a href="{{ $about->social_media['whatsapp'] }}" target="_blank" class="text-primary hover:underline">WhatsApp</a>
                            @endif
                            @if(!empty($about->social_media['tiktok']))
                                <a href="{{ $about->social_media['tiktok'] }}" target="_blank" class="text-primary hover:underline">TikTok</a>
                            @endif
                            @if(!empty($about->social_media['youtube']))
                                <a href="{{ $about->social_media['youtube'] }}" target="_blank" class="text-primary hover:underline">YouTube</a>
                            @endif
                            @if(!empty($about->social_media['linkedin']))
                                <a href="{{ $about->social_media['linkedin'] }}" target="_blank" class="text-primary hover:underline">LinkedIn</a>
                            @endif
                        </div>
                    </div>
                    @endif
                </div>
                <div>
                    @if($about->vision)
                    <div class="mb-4">
                        <h3 class="font-label-md text-label-md text-on-surface mb-2">Vision</h3>
                        <p class="text-body-sm text-on-surface-variant">{{ $about->vision }}</p>
                    </div>
                    @endif
                    @if($about->mission)
                    <div class="mb-4">
                        <h3 class="font-label-md text-label-md text-on-surface mb-2">Mission</h3>
                        <p class="text-body-sm text-on-surface-variant">{{ $about->mission }}</p>
                    </div>
                    @endif
                    @if($about->values)
                    <div>
                        <h3 class="font-label-md text-label-md text-on-surface mb-2">Values</h3>
                        <p class="text-body-sm text-on-surface-variant">{{ $about->values }}</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    @else
        <div class="bg-white rounded-xl shadow-sm border border-outline-variant/30 p-12 text-center">
            <span class="material-symbols-outlined text-[48px] text-on-surface-variant mb-4">info</span>
            <p class="text-body-md text-on-surface-variant mb-4">No About Us content yet.</p>
            <a href="{{ route('content.about.create') }}" class="inline-flex items-center gap-2 px-5 py-2 bg-primary text-white rounded-lg font-label-md hover:opacity-90 transition-all shadow-sm">
                <span class="material-symbols-outlined text-[18px]">add</span> Create About Us
            </a>
        </div>
    @endif
@endsection
