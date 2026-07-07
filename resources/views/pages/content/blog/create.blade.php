@extends('layouts.app')

@section('title', 'Create Blog Post')

@section('content')
<div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
    <div>
        <h1 class="font-headline-lg text-headline-lg text-on-surface">Create Blog Post</h1>
        <nav class="flex items-center gap-2 text-body-md text-on-surface-variant mt-1">
            <a href="{{ route('dashboard') }}" class="text-primary hover:underline">Dashboard</a>
            <span class="material-symbols-outlined text-[16px]">chevron_right</span>
            <a href="{{ route('content.blog.index') }}" class="text-primary hover:underline">Blog</a>
            <span class="material-symbols-outlined text-[16px]">chevron_right</span>
            <span>Create</span>
        </nav>
    </div>
    <a href="{{ route('content.blog.index') }}" class="flex items-center gap-2 px-4 py-2 bg-surface-container-lowest border border-outline-variant text-secondary rounded-lg font-label-md hover:bg-surface-container transition-all">
        <span class="material-symbols-outlined text-[18px]">arrow_back</span> Back
    </a>
</div>

<form method="POST" action="{{ route('content.blog.store') }}" class="space-y-6">
    @csrf
    <div class="bg-white rounded-xl shadow-sm border border-outline-variant/30 p-6">
        <div class="space-y-1.5">
            <label class="block text-label-sm font-medium text-on-surface-variant">Title <span class="text-danger">*</span></label>
            <input type="text" name="title" class="w-full px-3 py-2 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary/20 focus:outline-none" placeholder="Enter post title..." required value="{{ old('title') }}">
            @error('title')<p class="text-danger text-sm">{{ $message }}</p>@enderror
        </div>
        <div class="space-y-1.5 mt-4">
            <label class="block text-label-sm font-medium text-on-surface-variant">Slug <span class="text-danger">*</span></label>
            <input type="text" name="slug" class="w-full px-3 py-2 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary/20 focus:outline-none" placeholder="e.g., tips-tidur-sehat" required value="{{ old('slug') }}">
            @error('slug')<p class="text-danger text-sm">{{ $message }}</p>@enderror
        </div>
        <div class="space-y-1.5 mt-4">
            <label class="block text-label-sm font-medium text-on-surface-variant">Excerpt</label>
            <textarea name="excerpt" rows="2" class="w-full px-3 py-2 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary/20 focus:outline-none" placeholder="Short excerpt...">{{ old('excerpt') }}</textarea>
            @error('excerpt')<p class="text-danger text-sm">{{ $message }}</p>@enderror
        </div>
        <div class="space-y-1.5 mt-4">
            <label class="block text-label-sm font-medium text-on-surface-variant">Content <span class="text-danger">*</span></label>
            <div id="quill-editor" style="min-height: 250px; font-size: 14px;">{!! old('content') !!}</div>
            <textarea name="content" id="quill-content" class="opacity-0 absolute pointer-events-none h-0 w-0 -z-10">{{ old('content') }}</textarea>
            @error('content')<p class="text-danger text-sm">{{ $message }}</p>@enderror
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
            <div class="space-y-1.5">
                <label class="block text-label-sm font-medium text-on-surface-variant">Featured Image</label>
                <div class="flex items-center gap-3">
                    <input type="text" name="featured_image" id="featuredImageUrl" class="flex-1 px-3 py-2 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary/20 focus:outline-none" placeholder="https://..." value="{{ old('featured_image') }}">
                    <button type="button" onclick="previewImage()" class="px-3 py-2 bg-surface-container-high text-primary rounded-lg hover:bg-surface-variant transition-colors text-label-sm">Preview</button>
                </div>
                <div id="imagePreview" class="mt-2 hidden">
                    <img src="" alt="Preview" class="max-h-40 rounded-lg border border-outline-variant">
                </div>
                <!-- @error('featured_image')<p class="text-danger text-sm">{{ $message }}</p>@enderror -->
            </div>
            <div class="space-y-1.5">
                <label class="block text-label-sm font-medium text-on-surface-variant">Author Name</label>
                <input type="text" name="author_name" class="w-full px-3 py-2 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary/20 focus:outline-none" placeholder="Author name" value="{{ old('author_name') }}">
                @error('author_name')<p class="text-danger text-sm">{{ $message }}</p>@enderror
            </div>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
            <div class="space-y-1.5">
                <label class="block text-label-sm font-medium text-on-surface-variant">Published At</label>
                <input type="date" name="published_at" class="w-full px-3 py-2 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary/20 focus:outline-none" value="{{ old('published_at') }}">
                @error('published_at')<p class="text-danger text-sm">{{ $message }}</p>@enderror
            </div>
            <div class="space-y-1.5">
                <label class="block text-label-sm font-medium text-on-surface-variant">Sort Order</label>
                <input type="number" name="sort_order" value="{{ old('sort_order', 0) }}" min="0" class="w-full px-3 py-2 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary/20 focus:outline-none">
                @error('sort_order')<p class="text-danger text-sm">{{ $message }}</p>@enderror
            </div>
        </div>
        <div class="flex items-center gap-6 mt-4">
            <label class="flex items-center gap-2">
                <input type="checkbox" name="is_published" value="1" {{ old('is_published') ? 'checked' : '' }} class="w-4 h-4 rounded border-outline-variant text-primary focus:ring-primary">
                <span class="text-label-sm font-medium text-on-surface-variant">Published</span>
            </label>
            <label class="flex items-center gap-2">
                <input type="checkbox" name="is_featured" value="1" {{ old('is_featured') ? 'checked' : '' }} class="w-4 h-4 rounded border-outline-variant text-primary focus:ring-primary">
                <span class="text-label-sm font-medium text-on-surface-variant">Featured</span>
            </label>
        </div>
    </div>
    <div class="flex justify-end gap-4">
        <a href="{{ route('content.blog.index') }}" class="px-8 py-3 border border-outline-variant text-primary font-bold rounded-lg hover:bg-surface-container transition-colors">Cancel</a>
        <button type="submit" class="px-10 py-3 bg-primary text-white rounded-lg font-label-md hover:opacity-90 transition-all shadow-sm">Save Post</button>
    </div>
</form>

<script src="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.min.js"></script>
<script>
let quill;
document.addEventListener('DOMContentLoaded', function() {
    quill = new Quill('#quill-editor', {
        theme: 'snow',
        placeholder: 'Write your blog content here...',
        modules: {
            toolbar: [
                [{ 'header': [1, 2, 3, false] }],
                ['bold', 'italic', 'underline', 'strike'],
                [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                [{ 'color': [] }, { 'background': [] }],
                ['link', 'image', 'video'],
                ['clean']
            ]
        }
    });

    const form = document.getElementById('quill-editor').closest('form');
    form.addEventListener('submit', function() {
        const content = document.getElementById('quill-content');
        content.value = quill.root.innerHTML;
    });
});
</script>

<script>
function previewImage() {
    const url = document.getElementById('featuredImageUrl').value;
    const preview = document.getElementById('imagePreview');
    const img = preview.querySelector('img');
    if (url) {
        img.src = url;
        preview.classList.remove('hidden');
    } else {
        preview.classList.add('hidden');
    }
}
</script>
@endsection
