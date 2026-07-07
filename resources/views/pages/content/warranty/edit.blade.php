@extends('layouts.app')

@section('title', 'Edit Warranty Claim')

@section('content')
<div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
    <div>
        <h1 class="font-headline-lg text-headline-lg text-on-surface">Edit Warranty Claim</h1>
        <nav class="flex items-center gap-2 text-body-md text-on-surface-variant mt-1">
            <a href="{{ route('dashboard') }}" class="text-primary hover:underline">Dashboard</a>
            <span class="material-symbols-outlined text-[16px]">chevron_right</span>
            <a href="{{ route('content.warranty.index') }}" class="text-primary hover:underline">Warranty Claims</a>
            <span class="material-symbols-outlined text-[16px]">chevron_right</span>
            <span>Edit</span>
        </nav>
    </div>
    <a href="{{ route('content.warranty.index') }}" class="flex items-center gap-2 px-4 py-2 bg-surface-container-lowest border border-outline-variant text-secondary rounded-lg font-label-md hover:bg-surface-container transition-all">
        <span class="material-symbols-outlined text-[18px]">arrow_back</span> Back
    </a>
</div>

<form method="POST" action="{{ route('content.warranty.update', $item->id) }}" id="warrantyForm" class="space-y-6">
    @csrf
    @method('PUT')
    <div class="bg-white rounded-xl shadow-sm border border-outline-variant/30 p-6">
        <div class="space-y-1.5">
            <label class="block text-label-sm font-medium text-on-surface-variant">Title <span class="text-danger">*</span></label>
            <input type="text" name="title" class="w-full px-3 py-2 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary/20 focus:outline-none" placeholder="Guide title..." required value="{{ old('title', $item->title) }}">
            @error('title')<p class="text-danger text-sm">{{ $message }}</p>@enderror
        </div>
        <div class="space-y-1.5 mt-4">
            <label class="block text-label-sm font-medium text-on-surface-variant">Slug <span class="text-danger">*</span></label>
            <input type="text" name="slug" class="w-full px-3 py-2 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary/20 focus:outline-none" placeholder="e.g., cara-klaim-garansi" required value="{{ old('slug', $item->slug) }}">
            @error('slug')<p class="text-danger text-sm">{{ $message }}</p>@enderror
        </div>
        <div class="space-y-1.5 mt-4">
            <label class="block text-label-sm font-medium text-on-surface-variant">Content <span class="text-danger">*</span></label>
            <textarea name="content" rows="6" class="w-full px-3 py-2 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary/20 focus:outline-none" placeholder="Full warranty guide content..." required>{{ old('content', $item->content) }}</textarea>
            @error('content')<p class="text-danger text-sm">{{ $message }}</p>@enderror
        </div>
        <div class="space-y-1.5 mt-4">
            <label class="block text-label-sm font-medium text-on-surface-variant">Steps</label>
            <div id="steps-container" class="space-y-3">
                @php $steps = old('steps', $item->steps ?? []); @endphp
                @if(!empty($steps) && is_array($steps))
                    @foreach($steps as $index => $step)
                        <div class="step-item border border-outline-variant rounded-lg p-4 bg-surface-gray/30 space-y-2" data-index="{{ $index }}">
                            <div class="flex items-center justify-between">
                                <span class="step-number-display text-label-sm font-semibold text-primary">Step #{{ $index + 1 }}</span>
                                <button type="button" class="remove-step text-danger hover:text-danger/80 text-label-sm">Remove</button>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-label-xs text-on-surface-variant mb-1">Step Number</label>
                                    <input type="number" name="step_number[]" value="{{ $step['step'] ?? ($index + 1) }}" min="1" class="step-number w-full px-3 py-2 border border-outline-variant rounded-lg text-body-sm focus:ring-2 focus:ring-primary/20 focus:outline-none">
                                </div>
                                <div>
                                    <label class="block text-label-xs text-on-surface-variant mb-1">Title</label>
                                    <input type="text" name="step_title[]" value="{{ $step['title'] ?? '' }}" class="step-title w-full px-3 py-2 border border-outline-variant rounded-lg text-body-sm focus:ring-2 focus:ring-primary/20 focus:outline-none" placeholder="Step title...">
                                </div>
                            </div>
                            <div>
                                <label class="block text-label-xs text-on-surface-variant mb-1">Description</label>
                                <textarea name="step_description[]" rows="2" class="step-description w-full px-3 py-2 border border-outline-variant rounded-lg text-body-sm focus:ring-2 focus:ring-primary/20 focus:outline-none" placeholder="Step description...">{{ $step['description'] ?? '' }}</textarea>
                            </div>
                        </div>
                    @endforeach
                @endif
            </div>
            <button type="button" id="add-step" class="mt-3 inline-flex items-center gap-2 px-4 py-2 bg-surface-container-high text-primary rounded-lg hover:bg-surface-variant transition-colors text-label-sm">
                <span class="material-symbols-outlined text-[18px]">add</span> Add Step
            </button>
            <p class="text-label-xs text-on-surface-variant mt-1">Optional: Add steps for this guide</p>
        </div>
        <div class="space-y-1.5 mt-4">
            <label class="block text-label-sm font-medium text-on-surface-variant">Required Documents</label>
            <div id="documents-container" class="space-y-2">
                @php $docs = old('required_documents', $item->required_documents ?? []); @endphp
                @if(!empty($docs) && is_array($docs))
                    @foreach($docs as $index => $doc)
                        <div class="document-item flex items-center gap-2" data-index="{{ $index }}">
                            @php
                                if (is_string($doc)) {
                                    $docName = $doc;
                                } elseif (is_array($doc)) {
                                    $docName = $doc['name'] ?? '';
                                } else {
                                    $docName = '';
                                }
                            @endphp
                            <input type="text" name="document_name[]" value="{{ old('required_documents.' . $index . '.name', $docName) }}" class="document-name flex-1 px-3 py-2 border border-outline-variant rounded-lg text-body-sm focus:ring-2 focus:ring-primary/20 focus:outline-none" placeholder="Document name...">
                            <button type="button" class="remove-document text-danger hover:text-danger/80 text-label-sm px-2">Remove</button>
                        </div>
                    @endforeach
                @endif
            </div>
            <button type="button" id="add-document" class="mt-2 inline-flex items-center gap-2 px-4 py-2 bg-surface-container-high text-primary rounded-lg hover:bg-surface-variant transition-colors text-label-sm">
                <span class="material-symbols-outlined text-[18px]">add</span> Add Document
            </button>
            <p class="text-label-xs text-on-surface-variant mt-1">Optional: Add required documents</p>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4">
            <div class="space-y-1.5">
                <label class="block text-label-sm font-medium text-on-surface-variant">Processing Time (days)</label>
                <input type="number" name="processing_time_days" value="{{ old('processing_time_days', $item->processing_time_days) }}" min="1" class="w-full px-3 py-2 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary/20 focus:outline-none">
                @error('processing_time_days')<p class="text-danger text-sm">{{ $message }}</p>@enderror
            </div>
            <div class="space-y-1.5">
                <label class="block text-label-sm font-medium text-on-surface-variant">Sort Order</label>
                <input type="number" name="sort_order" value="{{ old('sort_order', $item->sort_order) }}" min="0" class="w-full px-3 py-2 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary/20 focus:outline-none">
                @error('sort_order')<p class="text-danger text-sm">{{ $message }}</p>@enderror
            </div>
            <div class="space-y-1.5">
                <label class="block text-label-sm font-medium text-on-surface-variant">Status</label>
                <select name="is_published" class="w-full px-3 py-2 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary/20 focus:outline-none bg-white">
                    <option value="1" {{ old('is_published', $item->is_published ?? '1') == '1' ? 'selected' : '' }}>Published</option>
                    <option value="0" {{ old('is_published', $item->is_published ?? '1') == '0' ? 'selected' : '' }}>Draft</option>
                </select>
            </div>
        </div>
    </div>
    <div class="flex justify-end gap-4">
        <a href="{{ route('content.warranty.index') }}" class="px-8 py-3 border border-outline-variant text-primary font-bold rounded-lg hover:bg-surface-container transition-colors">Cancel</a>
        <button type="submit" class="px-10 py-3 bg-primary text-white rounded-lg font-label-md hover:opacity-90 transition-all shadow-sm">Update Guide</button>
    </div>
</form>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const stepsContainer = document.getElementById('steps-container');
    const docsContainer = document.getElementById('documents-container');
    const form = document.getElementById('warrantyForm');

    function buildStepHTML(index, data) {
        return `
            <div class="step-item border border-outline-variant rounded-lg p-4 bg-surface-gray/30 space-y-2" data-index="${index}">
                <div class="flex items-center justify-between">
                    <span class="step-number-display text-label-sm font-semibold text-primary">Step #${index + 1}</span>
                    <button type="button" class="remove-step text-danger hover:text-danger/80 text-label-sm">Remove</button>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-label-xs text-on-surface-variant mb-1">Step Number</label>
                        <input type="number" name="step_number[]" value="${data.step ?? (index + 1)}" min="1" class="step-number w-full px-3 py-2 border border-outline-variant rounded-lg text-body-sm focus:ring-2 focus:ring-primary/20 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-label-xs text-on-surface-variant mb-1">Title</label>
                        <input type="text" name="step_title[]" value="${data.title ?? ''}" class="step-title w-full px-3 py-2 border border-outline-variant rounded-lg text-body-sm focus:ring-2 focus:ring-primary/20 focus:outline-none" placeholder="Step title...">
                    </div>
                </div>
                <div>
                    <label class="block text-label-xs text-on-surface-variant mb-1">Description</label>
                    <textarea name="step_description[]" rows="2" class="step-description w-full px-3 py-2 border border-outline-variant rounded-lg text-body-sm focus:ring-2 focus:ring-primary/20 focus:outline-none" placeholder="Step description...">${data.description ?? ''}</textarea>
                </div>
            </div>
        `;
    }

    function addStep(data) {
        const currentCount = stepsContainer.querySelectorAll('.step-item').length;
        const html = buildStepHTML(currentCount, data || {});
        const temp = document.createElement('div');
        temp.innerHTML = html.trim();
        stepsContainer.appendChild(temp.firstElementChild);
    }

    document.getElementById('add-step').addEventListener('click', function() {
        addStep();
    });

    stepsContainer.addEventListener('click', function(e) {
        if (e.target.classList.contains('remove-step')) {
            const item = e.target.closest('.step-item');
            item.remove();
            updateStepNumbers();
        }
    });

    function updateStepNumbers() {
        const items = stepsContainer.querySelectorAll('.step-item');
        items.forEach((item, i) => {
            const display = item.querySelector('.step-number-display');
            display.textContent = 'Step #' + (i + 1);
        });
    }

    function buildDocHTML(name) {
        return `
            <div class="document-item flex items-center gap-2" data-index="">
                <input type="text" name="document_name[]" value="${name ?? ''}" class="document-name flex-1 px-3 py-2 border border-outline-variant rounded-lg text-body-sm focus:ring-2 focus:ring-primary/20 focus:outline-none" placeholder="Document name...">
                <button type="button" class="remove-document text-danger hover:text-danger/80 text-label-sm px-2">Remove</button>
            </div>
        `;
    }

    function addDocument(name) {
        const html = buildDocHTML(name || '');
        const temp = document.createElement('div');
        temp.innerHTML = html.trim();
        docsContainer.appendChild(temp.firstElementChild);
    }

    document.getElementById('add-document').addEventListener('click', function() {
        addDocument();
    });

    docsContainer.addEventListener('click', function(e) {
        if (e.target.classList.contains('remove-document')) {
            const item = e.target.closest('.document-item');
            item.remove();
        }
    });

    form.addEventListener('submit', function(e) {
        const steps = [];
        stepsContainer.querySelectorAll('.step-item').forEach(function(item) {
            const stepNumber = item.querySelector('.step-number').value;
            const title = item.querySelector('.step-title').value.trim();
            const description = item.querySelector('.step-description').value.trim();
            if (title || description) {
                steps.push({
                    step: parseInt(stepNumber) || (steps.length + 1),
                    title: title,
                    description: description
                });
            }
        });

        let stepHidden = form.querySelector('input[name="steps"]');
        if (!stepHidden) {
            stepHidden = document.createElement('input');
            stepHidden.type = 'hidden';
            stepHidden.name = 'steps';
            form.appendChild(stepHidden);
        }
        stepHidden.value = JSON.stringify(steps);

        const documents = [];
        docsContainer.querySelectorAll('.document-item').forEach(function(item) {
            const name = item.querySelector('.document-name').value.trim();
            if (name) {
                documents.push({ name: name });
            }
        });

        let docHidden = form.querySelector('input[name="required_documents"]');
        if (!docHidden) {
            docHidden = document.createElement('input');
            docHidden.type = 'hidden';
            docHidden.name = 'required_documents';
            form.appendChild(docHidden);
        }
        docHidden.value = JSON.stringify(documents);
    });
});
</script>
@endsection
