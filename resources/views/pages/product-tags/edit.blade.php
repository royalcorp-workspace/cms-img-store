@extends('layouts.app')

@section('title', 'Edit Product Tag')

@section('content')
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="font-headline-lg text-headline-lg text-on-surface">Edit Product Tag</h1>
            <nav class="flex items-center gap-2 text-label-sm text-on-surface-variant mt-1 font-medium">
                <a href="{{ route('dashboard') }}" class="hover:text-primary transition-colors">eCommerce</a>
                <span class="material-symbols-outlined text-[14px]">chevron_right</span>
                <a href="{{ route('tags.index') }}" class="hover:text-primary transition-colors">Product Tags</a>
                <span class="material-symbols-outlined text-[14px]">chevron_right</span>
                <span class="text-on-surface">Edit</span>
            </nav>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('tags.index') }}" class="inline-flex items-center gap-1.5 px-4 py-2 border border-outline-variant text-on-surface-variant hover:bg-surface-container rounded-xl text-xs font-semibold transition-colors">
                <span class="material-symbols-outlined text-[16px]">arrow_back</span>
                <span>Batal</span>
            </a>
            <button type="submit" form="tagForm" class="btn-save inline-flex items-center gap-2 px-5 py-2 bg-primary text-white hover:opacity-90 rounded-xl text-xs font-bold transition-all shadow-sm active:scale-95 cursor-pointer">
                <span class="material-symbols-outlined text-[18px]">save</span>
                <span>Simpan Perubahan</span>
            </button>
        </div>
    </div>

    @include('layouts.partials.product-submenu')

    <form id="tagForm" action="{{ route('tags.update', $tag->id) }}" method="POST" class="w-full space-y-6">
        @csrf
        @method('PUT')

        <div class="w-full max-w-3xl bg-white rounded-2xl shadow-sm border border-outline-variant/30 p-6 space-y-6">
            <div class="border-b border-outline-variant/20 pb-3">
                <h2 class="text-base font-bold text-on-surface flex items-center gap-2">
                    <span class="material-symbols-outlined text-primary text-[20px]">label</span>
                    Informasi Product Tag
                </h2>
                <p class="text-xs text-on-surface-variant mt-0.5">Nama label tag, slug URL otomatis, dan urutan tampilan.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="space-y-1.5">
                    <label class="block text-label-sm font-medium text-on-surface-variant">Nama Tag <span class="text-danger">*</span></label>
                    <input type="text" id="tagName" name="name" value="{{ old('name', $tag->name) }}" class="w-full px-3 py-2 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary/20 focus:outline-none text-sm" placeholder="e.g. Best Seller, New Arrival, Sale" required>
                    @error('name') <span class="text-danger text-xs font-semibold">{{ $message }}</span> @enderror
                </div>

                <div class="space-y-1.5">
                    <label class="block text-label-sm font-medium text-on-surface-variant">Slug (URL) <span class="text-xs text-secondary font-normal">(Otomatis)</span></label>
                    <input type="text" id="tagSlug" name="slug" value="{{ old('slug', $tag->slug) }}" readonly class="w-full px-3 py-2 border border-outline-variant rounded-lg bg-surface-container-low/60 cursor-not-allowed text-on-surface-variant focus:outline-none text-sm" placeholder="Otomatis terisi dari nama tag">
                    @error('slug') <span class="text-danger text-xs font-semibold">{{ $message }}</span> @enderror
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="space-y-1.5">
                    <label class="block text-label-sm font-medium text-on-surface-variant">Sort Order</label>
                    <input type="number" name="sort_order" value="{{ old('sort_order', $tag->sort_order ?? 0) }}" class="w-full px-3 py-2 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary/20 focus:outline-none text-sm" placeholder="0">
                    <p class="text-[11px] text-on-surface-variant">Urutan prioritas tampilan tag pada filter dan halaman produk.</p>
                    @error('sort_order') <span class="text-danger text-xs font-semibold">{{ $message }}</span> @enderror
                </div>
            </div>
        </div>
    </form>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const nameInput = document.getElementById('tagName');
        const slugInput = document.getElementById('tagSlug');

        function generateSlug(text) {
            return text
                .toString()
                .toLowerCase()
                .trim()
                .replace(/[\s\W-]+/g, '-')
                .replace(/^-+|-+$/g, '');
        }

        if (nameInput && slugInput) {
            nameInput.addEventListener('input', function () {
                slugInput.value = generateSlug(this.value);
            });
        }
    });
</script>
@endpush
