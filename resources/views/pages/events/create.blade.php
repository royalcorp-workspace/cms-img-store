@extends('layouts.app')

@section('title', 'Create Campaign Event')

@section('content')
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="font-headline-lg text-headline-lg text-on-surface">Create Campaign Event</h1>
            <nav class="flex items-center gap-2 text-label-sm text-on-surface-variant mt-1 font-medium">
                <a href="{{ route('dashboard') }}" class="hover:text-primary transition-colors">eCommerce</a>
                <span class="material-symbols-outlined text-[14px]">chevron_right</span>
                <a href="{{ route('events.index') }}" class="hover:text-primary transition-colors">Events</a>
                <span class="material-symbols-outlined text-[14px]">chevron_right</span>
                <span class="text-on-surface">Create</span>
            </nav>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('events.index') }}" class="inline-flex items-center gap-1.5 px-4 py-2 border border-outline-variant text-on-surface-variant hover:bg-surface-container rounded-xl text-xs font-semibold transition-colors">
                <span class="material-symbols-outlined text-[16px]">arrow_back</span>
                <span>Batal</span>
            </a>
            <button type="submit" form="eventForm" class="btn-save inline-flex items-center gap-2 px-5 py-2 bg-primary text-white hover:opacity-90 rounded-xl text-xs font-bold transition-all shadow-sm active:scale-95">
                <span class="material-symbols-outlined text-[18px]">save</span>
                <span>Simpan Event</span>
            </button>
        </div>
    </div>

    @include('layouts.partials.promotions-submenu')

    <form id="eventForm" action="{{ route('events.store') }}" method="POST" enctype="multipart/form-data" class="w-full space-y-6">
        @csrf

        <div class="w-full bg-white rounded-2xl shadow-sm border border-outline-variant/30 p-6 space-y-6">
            <!-- 1. Event Details Block -->
            <div>
                <div class="border-b border-outline-variant/20 pb-3 mb-5">
                    <h3 class="font-headline-md text-headline-md text-on-surface flex items-center gap-2">
                        <span class="material-symbols-outlined text-primary">event</span>
                        1. Event Details
                    </h3>
                    <p class="text-xs text-on-surface-variant mt-0.5">Informasi dasar nama event, tipe event, slug URL, tanggal periode, dan banner.</p>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-1.5">
                        <label class="block text-label-sm font-medium text-on-surface-variant">Event Name <span class="text-danger">*</span></label>
                        <input type="text" name="title" value="{{ old('title') }}" class="w-full px-3 py-2 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary/20 focus:outline-none text-body-md" placeholder="e.g. Promo Merdeka Hemat" required>
                        @error('title') <span class="text-danger text-xs font-semibold">{{ $message }}</span> @enderror
                    </div>

                    <div class="space-y-1.5">
                        <label class="block text-label-sm font-medium text-on-surface-variant">Event Type <span class="text-danger">*</span></label>
                        <select name="event_type" class="w-full px-3 py-2 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary/20 focus:outline-none text-body-md" required>
                            <option value="mega_campaign" {{ old('event_type') == 'mega_campaign' ? 'selected' : '' }}>Mega Campaign / Spesial</option>
                            <option value="flash_sale" {{ old('event_type') == 'flash_sale' ? 'selected' : '' }}>Flash Sale (Terbatas Jam)</option>
                            <option value="clearance" {{ old('event_type') == 'clearance' ? 'selected' : '' }}>Cuci Gudang (Clearance)</option>
                            <option value="new_arrival" {{ old('event_type') == 'new_arrival' ? 'selected' : '' }}>Rilis Baru (New Arrival)</option>
                        </select>
                        @error('event_type') <span class="text-danger text-xs font-semibold">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 pt-4">
                    <div class="space-y-1.5">
                        <label class="block text-label-sm font-medium text-on-surface-variant">Slug (URL Name)</label>
                        <input type="text" name="slug" id="slug-input" value="{{ old('slug') }}" readonly class="w-full px-3 py-2 border border-outline-variant rounded-lg bg-surface-container/40 cursor-not-allowed focus:outline-none text-body-md text-on-surface-variant" placeholder="Will be auto-generated">
                        @error('slug') <span class="text-danger text-xs font-semibold">{{ $message }}</span> @enderror
                    </div>

                    <div class="space-y-1.5">
                        <label class="block text-label-sm font-medium text-on-surface-variant">Event Banner Image</label>
                        <input type="file" name="banner_image" accept="image/*" class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-primary file:text-white hover:file:opacity-90 cursor-pointer">
                        <p class="text-[11px] text-on-surface-variant mt-1">Banner landscape untuk ditampilkan di halaman depan web.</p>
                        @error('banner_image') <span class="text-danger text-xs font-semibold">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 pt-4">
                    <div class="space-y-1.5">
                        <label class="block text-label-sm font-medium text-on-surface-variant">Start Date <span class="text-danger">*</span></label>
                        <input type="date" name="start_date" value="{{ old('start_date') }}" class="w-full px-3 py-2 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary/20 focus:outline-none text-body-md" required>
                        @error('start_date') <span class="text-danger text-xs font-semibold">{{ $message }}</span> @enderror
                    </div>

                    <div class="space-y-1.5">
                        <label class="block text-label-sm font-medium text-on-surface-variant">End Date <span class="text-danger">*</span></label>
                        <input type="date" name="end_date" value="{{ old('end_date') }}" class="w-full px-3 py-2 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary/20 focus:outline-none text-body-md" required>
                        @error('end_date') <span class="text-danger text-xs font-semibold">{{ $message }}</span> @enderror
                    </div>

                    <div class="flex items-center pt-6">
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="is_active" value="1" checked class="sr-only peer">
                            <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-primary/20 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-success"></div>
                            <span class="ml-3 text-label-md font-medium text-on-surface-variant">Active Campaign</span>
                        </label>
                    </div>
                </div>
            </div>

            <!-- 2. Promo / Discount Code Block -->
            <div class="pt-6 border-t border-outline-variant/30">
                <div class="border-b border-outline-variant/20 pb-3 mb-5">
                    <h3 class="font-headline-md text-headline-md text-on-surface flex items-center gap-2">
                        <span class="material-symbols-outlined text-primary">local_offer</span>
                        2. Price Product Setting (Discount Rule)
                    </h3>
                    <p class="text-xs text-on-surface-variant mt-0.5">Pilih skema diskon / potongan harga yang dikaitkan ke event ini.</p>
                </div>

                <div class="space-y-1.5">
                    <label class="block text-label-sm font-medium text-on-surface-variant">Select Price Product Setting <span class="text-danger">*</span></label>
                    <select name="price_product_setting_id" required class="w-full px-3 py-2 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary/20 text-body-md bg-white">
                        <option value="">-- Choose Price Product Setting --</option>
                        @foreach($priceProductSettings as $setting)
                            <option value="{{ $setting->id }}" {{ old('price_product_setting_id') == $setting->id ? 'selected' : '' }}>
                                {{ $setting->title }} (Code: {{ $setting->code }})
                            </option>
                        @endforeach
                    </select>
                    @error('price_product_setting_id') <span class="text-danger text-xs font-semibold">{{ $message }}</span> @enderror
                </div>
            </div>

            <!-- 3. Frontend Event Popup Setup Block -->
            <div class="pt-6 border-t border-outline-variant/30">
                <div class="border-b border-outline-variant/20 pb-3 mb-5">
                    <h3 class="font-headline-md text-headline-md text-on-surface flex items-center gap-2">
                        <span class="material-symbols-outlined text-primary">featured_video</span>
                        3. Frontend Event Popup Configuration
                    </h3>
                    <p class="text-xs text-on-surface-variant mt-0.5">Pengaturan popup modal promo di tampilan depan web store.</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-1.5">
                        <label class="block text-label-sm font-medium text-on-surface-variant">Popup Header Title</label>
                        <input type="text" name="popup_title" value="{{ old('popup_title') }}" class="w-full px-3 py-2 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary/20 focus:outline-none text-body-md" placeholder="If left blank, will use Event Name">
                    </div>

                    <div class="space-y-1.5">
                        <label class="block text-label-sm font-medium text-on-surface-variant">Action Redirect Link URL (CTA)</label>
                        <input type="text" name="popup_link" value="{{ old('popup_link') }}" class="w-full px-3 py-2 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary/20 focus:outline-none text-body-md" placeholder="e.g. /promos/merdeka-45">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 pt-4">
                    <div class="space-y-1.5 md:col-span-2">
                        <label class="block text-label-sm font-medium text-on-surface-variant">Popup Banner Image</label>
                        <input type="file" name="popup_image" accept="image/*" class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-primary file:text-white hover:file:opacity-90 cursor-pointer">
                    </div>

                    <div class="space-y-1.5">
                        <label class="block text-label-sm font-medium text-on-surface-variant">CTA Button Text</label>
                        <input type="text" name="popup_button_text" value="Lihat Promo" class="w-full px-3 py-2 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary/20 focus:outline-none text-body-md">
                    </div>
                </div>

                <div class="pt-4 flex items-center">
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" name="popup_active" value="1" checked class="sr-only peer">
                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-primary/20 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-success"></div>
                        <span class="ml-3 text-label-md font-medium text-on-surface-variant">Show Popup Modal in Frontend</span>
                    </label>
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 pt-6 border-t border-outline-variant/30">
                <a href="{{ route('events.index') }}" class="px-6 py-2.5 border border-outline-variant text-on-surface-variant rounded-xl text-xs font-semibold hover:bg-surface-container transition-colors">Batal</a>
                <button type="submit" class="btn-save px-6 py-2.5 bg-primary text-white rounded-xl text-xs font-bold hover:opacity-90 transition-all shadow-sm active:scale-95">Simpan Event</button>
            </div>
        </div>
    </form>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const titleInput = document.querySelector('input[name="title"]');
        const slugInput = document.getElementById('slug-input');
        if (titleInput && slugInput) {
            titleInput.addEventListener('input', function() {
                slugInput.value = this.value
                    .toLowerCase()
                    .trim()
                    .replace(/[^\w\s-]/g, '')
                    .replace(/[\s_-]+/g, '-')
                    .replace(/^-+|-+$/g, '');
            });
        }
    });
    </script>
@endsection
