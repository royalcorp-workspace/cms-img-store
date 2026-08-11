@extends('layouts.app')

@section('title', 'Edit Brand')

@section('content')
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="font-headline-lg text-headline-lg text-on-surface">Edit Brand</h1>
            <nav class="flex items-center gap-2 text-label-sm text-on-surface-variant mt-1 font-medium">
                <a href="{{ route('dashboard') }}" class="hover:text-primary transition-colors">eCommerce</a>
                <span class="material-symbols-outlined text-[14px]">chevron_right</span>
                <a href="{{ route('brands.index') }}" class="hover:text-primary transition-colors">Brands</a>
                <span class="material-symbols-outlined text-[14px]">chevron_right</span>
                <span class="text-on-surface">Edit: {{ $brand->name }}</span>
            </nav>
        </div>
    </div>

    @include('layouts.partials.product-submenu')

    <div class="p-6 max-w-2xl">
        <form action="{{ route('brands.update', $brand->id) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="space-y-1.5">
                    <label class="block text-label-sm font-medium text-on-surface-variant">Brand Name <span class="text-danger">*</span></label>
                    <input type="text" name="name" value="{{ old('name', $brand->name) }}" class="w-full px-3 py-2 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary/20 focus:outline-none" placeholder="e.g. Lady Americana" required>
                    @error('name') <span class="text-danger text-xs font-semibold">{{ $message }}</span> @enderror
                </div>

                <div class="space-y-1.5">
                    <label class="block text-label-sm font-medium text-on-surface-variant">Slug (URL Name)</label>
                    <input type="text" name="slug" value="{{ old('slug', $brand->slug) }}" class="w-full px-3 py-2 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary/20 focus:outline-none" placeholder="e.g. lady-americana">
                    @error('slug') <span class="text-danger text-xs font-semibold">{{ $message }}</span> @enderror
                </div>
            </div>

            <div class="space-y-1.5">
                <label class="block text-label-sm font-medium text-on-surface-variant">Description</label>
                <textarea name="description" rows="4" class="w-full px-3 py-2 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary/20 focus:outline-none" placeholder="Describe the brand...">{{ old('description', $brand->description) }}</textarea>
                @error('description') <span class="text-danger text-xs font-semibold">{{ $message }}</span> @enderror
            </div>

            <div class="grid grid-cols-1 gap-6">
                <!-- Logo File Input & Preview -->
                <div class="space-y-1.5 md:w-1/3">
                    <label class="block text-label-sm font-medium text-on-surface-variant">Brand Logo</label>
                    @if($brand->logo)
                        <div class="mb-2 w-20 h-20 bg-surface-container rounded-md overflow-hidden border border-outline-variant/30 p-1 flex items-center justify-center bg-white">
                            <img class="max-w-full max-h-full object-contain" src="{{ asset('storage/' . $brand->logo) }}" alt="Logo">
                        </div>
                    @endif
                    <input type="file" name="logo" accept="image/*" class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-primary file:text-white hover:file:opacity-90">
                    @error('logo') <span class="text-danger text-xs font-semibold">{{ $message }}</span> @enderror
                </div>
            </div>

            <!-- Banner Section -->
            <div class="col-span-1 md:col-span-3 mt-2 border-t border-outline-variant/30 pt-4">
                <h3 class="text-title-md font-bold text-on-surface mb-4">Brand Banner (Shop Page)</h3>
                
                <div class="flex flex-col gap-4">
                    <div class="space-y-1.5 md:w-1/3">
                        <label class="block text-label-sm font-medium text-on-surface-variant">Banner Type</label>
                        <select name="banner_type" id="banner_type" class="w-full px-3 py-2 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary/20 focus:outline-none" onchange="toggleBannerInputs()">
                            <option value="1" {{ old('banner_type', $brand->banner_type ?? 1) == 1 ? 'selected' : '' }}>Image Upload</option>
                            <option value="2" {{ old('banner_type', $brand->banner_type ?? 1) == 2 ? 'selected' : '' }}>Image URL (Hotlink)</option>
                        </select>
                    </div>

                    <!-- Image Upload Inputs -->
                    <div id="banner_image_inputs" class="grid grid-cols-1 md:grid-cols-2 gap-6 p-4 border border-outline-variant/30 rounded-xl bg-surface-container/30">
                        <div class="space-y-1.5">
                            <label class="block text-label-sm font-medium text-on-surface-variant">Banner Web (Upload)</label>
                            @if($brand->banner_web)
                                <div class="mb-2 w-32 h-16 bg-surface-container rounded-md overflow-hidden border border-outline-variant/30 flex items-center justify-center bg-white">
                                    <img class="w-full h-full object-cover" src="{{ asset('storage/' . $brand->banner_web) }}" alt="Web Banner">
                                </div>
                            @endif
                            <input type="file" name="banner_web" accept="image/*" class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-primary file:text-white hover:file:opacity-90">
                            @error('banner_web') <span class="text-danger text-xs font-semibold">{{ $message }}</span> @enderror
                        </div>

                        <div class="space-y-1.5">
                            <label class="block text-label-sm font-medium text-on-surface-variant">Banner Mobile (Upload)</label>
                            @if($brand->banner_mobile)
                                <div class="mb-2 w-20 h-20 bg-surface-container rounded-md overflow-hidden border border-outline-variant/30 flex items-center justify-center bg-white">
                                    <img class="w-full h-full object-cover" src="{{ asset('storage/' . $brand->banner_mobile) }}" alt="Mobile Banner">
                                </div>
                            @endif
                            <input type="file" name="banner_mobile" accept="image/*" class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-primary file:text-white hover:file:opacity-90">
                            @error('banner_mobile') <span class="text-danger text-xs font-semibold">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <!-- Image URL Inputs -->
                    <div id="banner_url_inputs" class="grid grid-cols-1 md:grid-cols-2 gap-6 p-4 border border-outline-variant/30 rounded-xl bg-surface-container/30" style="display: none;">
                        <div class="space-y-1.5">
                            <label class="block text-label-sm font-medium text-on-surface-variant">Image URL Web</label>
                            <input type="url" name="embed_web" value="{{ old('embed_web', $brand->embed_web) }}" class="w-full px-3 py-2 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary/20 focus:outline-none" placeholder="https://example.com/image-web.png">
                            @error('embed_web') <span class="text-danger text-xs font-semibold">{{ $message }}</span> @enderror
                        </div>

                        <div class="space-y-1.5">
                            <label class="block text-label-sm font-medium text-on-surface-variant">Image URL Mobile</label>
                            <input type="url" name="embed_mobile" value="{{ old('embed_mobile', $brand->embed_mobile) }}" class="w-full px-3 py-2 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary/20 focus:outline-none" placeholder="https://example.com/image-mobile.png">
                            @error('embed_mobile') <span class="text-danger text-xs font-semibold">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <!-- Click Redirect Link -->
                    <div class="space-y-1.5 p-4 border border-outline-variant/30 rounded-xl bg-surface-container/30">
                        <label class="block text-label-sm font-medium text-on-surface-variant">Redirect Banner Link (URL)</label>
                        <input type="url" name="banner_link" value="{{ old('banner_link', $brand->banner_link) }}" class="w-full px-3 py-2 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary/20 focus:outline-none" placeholder="https://example.com/promo">
                        <p class="text-xs text-on-surface-variant mt-1">If filled, clicking the banner will redirect to this link.</p>
                        @error('banner_link') <span class="text-danger text-xs font-semibold">{{ $message }}</span> @enderror
                    </div>
                </div>
            </div>

            <script>
                function toggleBannerInputs() {
                    const type = document.getElementById('banner_type').value;
                    if (type == '1') {
                        document.getElementById('banner_image_inputs').style.display = 'grid';
                        document.getElementById('banner_url_inputs').style.display = 'none';
                    } else {
                        document.getElementById('banner_image_inputs').style.display = 'none';
                        document.getElementById('banner_url_inputs').style.display = 'grid';
                    }
                }
                document.addEventListener('DOMContentLoaded', toggleBannerInputs);
            </script>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 pt-4 border-t border-outline-variant/30">
                <div class="space-y-1.5">
                    <label class="block text-label-sm font-medium text-on-surface-variant">Sort Order</label>
                    <input type="number" name="sort_order" value="{{ old('sort_order', $brand->sort_order ?? 0) }}" min="0" class="w-full px-3 py-2 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary/20 focus:outline-none">
                    @error('sort_order') <span class="text-danger text-xs font-semibold">{{ $message }}</span> @enderror
                </div>

                <div class="flex items-center pt-6">
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" name="status" value="1" {{ old('status', $brand->status) ? 'checked' : '' }} class="sr-only peer">
                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-primary/20 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-success"></div>
                        <span class="ml-3 text-label-md font-medium text-on-surface-variant">Active</span>
                    </label>
                </div>

                <div class="flex items-center pt-6">
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" name="is_featured" value="1" {{ old('is_featured', $brand->is_featured) ? 'checked' : '' }} class="sr-only peer">
                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-primary/20 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary"></div>
                        <span class="ml-3 text-label-md font-medium text-on-surface-variant">Featured Brand</span>
                    </label>
                </div>
            </div>

            <div class="flex items-center gap-3 pt-6 border-t border-outline-variant/30">
                <button type="submit" class="px-6 py-2.5 bg-primary text-white font-label-md hover:opacity-90 transition-all">Update Brand</button>
                <a href="{{ route('brands.index') }}" class="px-6 py-2.5 border border-outline-variant text-on-surface rounded-lg font-label-md hover:bg-surface-container transition-colors">Cancel</a>
            </div>
        </form>
    </div>
@endsection
