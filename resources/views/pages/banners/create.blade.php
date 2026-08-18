@extends('layouts.app')

@section('title', 'Create Banner')

@section('content')
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="font-headline-lg text-headline-lg text-on-surface">Create Banner</h1>
            <nav class="flex items-center gap-2 text-label-sm text-on-surface-variant mt-1 font-medium">
                <a href="{{ route('dashboard') }}" class="hover:text-primary transition-colors">Dashboard</a>
                <span class="material-symbols-outlined text-[14px]">chevron_right</span>
                <a href="{{ route('content.banners.index') }}" class="hover:text-primary transition-colors">Banners</a>
                <span class="material-symbols-outlined text-[14px]">chevron_right</span>
                <span class="text-on-surface">Create</span>
            </nav>
        </div>
    </div>

    @include('layouts.partials.content-submenu')

    <div class="p-6 max-w-3xl">
        <form action="{{ route('content.banners.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6" id="banner-form">
            @csrf

            {{-- Basic Info --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="space-y-1.5">
                    <label class="block text-label-sm font-medium text-on-surface-variant">Banner Title <span class="text-danger">*</span></label>
                    <input type="text" name="title" value="{{ old('title') }}" class="w-full px-3 py-2 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary/20 focus:outline-none" required>
                </div>
                <div class="space-y-1.5">
                    <label class="block text-label-sm font-medium text-on-surface-variant">Global Link URL (Optional)</label>
                    <input type="text" name="link_url" value="{{ old('link_url') }}" placeholder="https://..." class="w-full px-3 py-2 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary/20 focus:outline-none">
                </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-6" x-data="{ bannerType: '1' }">
                <div class="space-y-1.5">
                    <label class="block text-label-sm font-medium text-on-surface-variant">Banner Type <span class="text-danger">*</span></label>
                    <select name="type" x-model="bannerType" class="w-full px-3 py-2 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary/20 focus:outline-none select2-enable" required>
                        <option value="1">Home Slider</option>
                        <option value="3">Brand Slider</option>
                        <option value="4">Category Slider</option>
                    </select>
                </div>
                <div class="space-y-1.5" x-show="bannerType === '3'" style="display: none;">
                    <label class="block text-label-sm font-medium text-on-surface-variant">Select Brand <span class="text-danger">*</span></label>
                    <select name="target_id" class="w-full px-3 py-2 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary/20 focus:outline-none select2-enable" :required="bannerType === '3'" :disabled="bannerType !== '3'">
                        <option value="">-- Choose Brand --</option>
                        @foreach($brands as $brand)
                            <option value="{{ $brand->id }}">{{ $brand->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="space-y-1.5" x-show="bannerType === '4'" style="display: none;">
                    <label class="block text-label-sm font-medium text-on-surface-variant">Select Category <span class="text-danger">*</span></label>
                    <select name="target_id" class="w-full px-3 py-2 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary/20 focus:outline-none select2-enable" :required="bannerType === '4'" :disabled="bannerType !== '4'">
                        <option value="">-- Choose Category --</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="space-y-1.5">
                    <label class="block text-label-sm font-medium text-on-surface-variant">Device</label>
                    <select name="device_flag" class="w-full px-3 py-2 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary/20 focus:outline-none">
                        <option value="1">All Devices</option>
                        <option value="2">Web (Desktop Only)</option>
                        <option value="3">Mobile Only</option>
                    </select>
                </div>
                <div class="space-y-1.5">
                    <label class="block text-label-sm font-medium text-on-surface-variant">Placement Size</label>
                    <select name="placement_size" class="w-full px-3 py-2 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary/20 focus:outline-none">
                        <option value="1">Main Banner (1920x500)</option>
                        <option value="2">Square Promo (300x300)</option>
                        <option value="3">Custom Size</option>
                    </select>
                </div>
            </div>

            {{-- Multiple Image Uploads --}}
            <div class="pt-4 border-t border-outline-variant/30">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="font-semibold text-on-surface">Banner Images</h3>
                    <button type="button" id="add-image-btn"
                        class="flex items-center gap-1.5 px-3 py-1.5 bg-primary/10 hover:bg-primary/20 text-primary rounded-lg text-label-sm font-semibold transition-colors">
                        <span class="material-symbols-outlined text-[16px]">add_photo_alternate</span>
                        Tambah Gambar
                    </button>
                </div>

                <div id="images-container" class="space-y-4">
                    {{-- First image row (required) --}}
                    <div class="image-row border border-outline-variant/40 rounded-xl p-4 bg-surface-container/20 relative">
                        <div class="absolute top-3 right-3">
                            <button type="button" class="remove-image-btn hidden w-7 h-7 rounded-full bg-danger/10 hover:bg-danger/20 text-danger flex items-center justify-center transition-colors">
                                <span class="material-symbols-outlined text-[16px]">close</span>
                            </button>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="space-y-1.5">
                                <label class="block text-label-sm font-medium text-on-surface-variant">
                                    Gambar Desktop (Web) <span class="text-danger">*</span>
                                </label>
                                <input type="file" name="images_web[]" accept="image/*"
                                    class="image-web-input w-full text-sm text-gray-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-primary file:text-white hover:file:opacity-90">
                                <div class="preview-web mt-2 hidden">
                                    <img class="h-20 w-auto rounded-lg object-cover border border-outline-variant/30" src="" alt="preview">
                                </div>
                            </div>
                            <div class="space-y-1.5">
                                <label class="block text-label-sm font-medium text-on-surface-variant">
                                    Gambar Mobile <span class="text-xs text-on-surface-variant font-normal">(opsional, fallback ke Desktop)</span>
                                </label>
                                <input type="file" name="images_mobile[]" accept="image/*"
                                    class="image-mobile-input w-full text-sm text-gray-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-primary file:text-white hover:file:opacity-90">
                                <div class="preview-mobile mt-2 hidden">
                                    <img class="h-20 w-auto rounded-lg object-cover border border-outline-variant/30" src="" alt="preview">
                                </div>
                            </div>
                        </div>
                        <div class="mt-3 space-y-1.5">
                            <label class="block text-label-sm font-medium text-on-surface-variant">Link URL untuk Gambar Ini (Opsional)</label>
                            <input type="text" name="image_links[]" placeholder="https://..."
                                class="w-full px-3 py-2 border border-outline-variant rounded-lg text-sm focus:ring-2 focus:ring-primary/20 focus:outline-none">
                        </div>
                    </div>
                </div>

                <p class="mt-3 text-label-sm text-on-surface-variant">
                    <span class="material-symbols-outlined text-[14px] align-middle">info</span>
                    Maksimal ukuran per file: 5MB. Format: JPG, PNG, WEBP.
                </p>
            </div>

            {{-- Sort & Status --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 pt-4 border-t border-outline-variant/30">
                <div class="space-y-1.5">
                    <label class="block text-label-sm font-medium text-on-surface-variant">Sort Order</label>
                    <input type="number" name="sort_order" value="0" min="0" class="w-full px-3 py-2 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary/20">
                </div>
                <div class="flex items-center pt-6">
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" name="is_active" value="1" checked class="sr-only peer">
                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-primary/20 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-success"></div>
                        <span class="ml-3 text-label-md font-medium text-on-surface-variant">Active</span>
                    </label>
                </div>
            </div>

            <div class="flex items-center gap-3 pt-6 border-t border-outline-variant/30">
                <button type="submit" class="px-6 py-2.5 bg-primary text-white font-label-md hover:opacity-90 transition-all">Save Banner</button>
                <a href="{{ route('content.banners.index') }}" class="px-6 py-2.5 border border-outline-variant text-on-surface rounded-lg font-label-md hover:bg-surface-container transition-colors">Cancel</a>
            </div>
        </form>
    </div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
const MAX_SIZE = 5 * 1024 * 1024;

function makeImageRow() {
    const div = document.createElement('div');
    div.className = 'image-row border border-outline-variant/40 rounded-xl p-4 bg-surface-container/20 relative';
    div.innerHTML = `
        <div class="absolute top-3 right-3">
            <button type="button" class="remove-image-btn w-7 h-7 rounded-full bg-danger/10 hover:bg-danger/20 text-danger flex items-center justify-center transition-colors">
                <span class="material-symbols-outlined text-[16px]">close</span>
            </button>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="space-y-1.5">
                <label class="block text-label-sm font-medium text-on-surface-variant">Gambar Desktop (Web) <span class="text-danger">*</span></label>
                <div class="flex flex-col gap-2">
                    <input type="file" name="images_web[]" accept="image/*"
                        class="image-web-input w-full text-sm text-gray-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-primary file:text-white hover:file:opacity-90">
                    <span class="text-[10px] text-on-surface-variant text-center font-bold uppercase">ATAU</span>
                    <input type="text" name="images_web_url_text[]" placeholder="https://... (URL Gambar)"
                        class="w-full text-sm px-3 py-2 border border-outline-variant rounded-md focus:outline-none focus:ring-1 focus:ring-primary/20">
                </div>
                <div class="preview-web mt-2 hidden">
                    <img class="h-20 w-auto rounded-lg object-cover border border-outline-variant/30" src="" alt="preview">
                </div>
            </div>
            <div class="space-y-1.5">
                <label class="block text-label-sm font-medium text-on-surface-variant">Gambar Mobile <span class="text-xs font-normal">(opsional)</span></label>
                <input type="file" name="images_mobile[]" accept="image/*"
                    class="image-mobile-input w-full text-sm text-gray-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-primary file:text-white hover:file:opacity-90">
                <div class="preview-mobile mt-2 hidden">
                    <img class="h-20 w-auto rounded-lg object-cover border border-outline-variant/30" src="" alt="preview">
                </div>
            </div>
        </div>
        <div class="mt-3 space-y-1.5">
            <label class="block text-label-sm font-medium text-on-surface-variant">Link URL untuk Gambar Ini (Opsional)</label>
            <input type="text" name="image_links[]" placeholder="https://..."
                class="w-full px-3 py-2 border border-outline-variant rounded-lg text-sm focus:ring-2 focus:ring-primary/20 focus:outline-none">
        </div>`;
    attachRowEvents(div);
    return div;
}

function attachRowEvents(row) {
    row.querySelector('.remove-image-btn')?.addEventListener('click', function() {
        row.remove();
        updateRemoveButtons();
    });
    const webInput = row.querySelector('.image-web-input');
    const mobileInput = row.querySelector('.image-mobile-input');
    if (webInput) {
        webInput.addEventListener('change', function() { handleFileChange(this, row.querySelector('.preview-web')); });
    }
    if (mobileInput) {
        mobileInput.addEventListener('change', function() { handleFileChange(this, row.querySelector('.preview-mobile')); });
    }
}

function handleFileChange(input, previewEl) {
    if (!input.files.length) return;
    const file = input.files[0];
    if (file.size > MAX_SIZE) {
        Swal.fire({ icon: 'warning', title: 'File Terlalu Besar', text: 'Ukuran file melebihi batas 5MB.', confirmButtonColor: '#1e3a8a' });
        input.value = '';
        previewEl.classList.add('hidden');
        return;
    }
    const reader = new FileReader();
    reader.onload = e => {
        previewEl.querySelector('img').src = e.target.result;
        previewEl.classList.remove('hidden');
    };
    reader.readAsDataURL(file);
}

function updateRemoveButtons() {
    const rows = document.querySelectorAll('.image-row');
    rows.forEach((row, i) => {
        const btn = row.querySelector('.remove-image-btn');
        if (btn) btn.classList.toggle('hidden', rows.length === 1);
    });
}

document.getElementById('add-image-btn').addEventListener('click', function() {
    document.getElementById('images-container').appendChild(makeImageRow());
    updateRemoveButtons();
});

// Attach events to initial row
document.querySelectorAll('.image-row').forEach(row => attachRowEvents(row));
updateRemoveButtons();

// Submit validation
document.getElementById('banner-form').addEventListener('submit', function(e) {
    const webInputs = document.querySelectorAll('input[name="images_web[]"]');
    let hasFile = false;
    let oversized = false;
    webInputs.forEach(input => {
        if (input.files.length > 0) {
            hasFile = true;
            if (input.files[0].size > MAX_SIZE) oversized = true;
        }
    });
    if (!hasFile) {
        e.preventDefault();
        Swal.fire({ icon: 'warning', title: 'Gambar Diperlukan', text: 'Harap upload minimal satu gambar banner.', confirmButtonColor: '#1e3a8a' });
        return;
    }
    if (oversized) {
        e.preventDefault();
        Swal.fire({ icon: 'error', title: 'File Terlalu Besar', text: 'Terdapat file yang melebihi batas 5MB.', confirmButtonColor: '#1e3a8a' });
    }
});
</script>
@endpush
