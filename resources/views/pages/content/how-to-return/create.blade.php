@extends('layouts.app')

@section('title', 'Create How To Return')

@section('content')
<div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
    <div>
        <h1 class="font-headline-lg text-headline-lg text-on-surface">Create How To Return</h1>
        <nav class="flex items-center gap-2 text-body-md text-on-surface-variant mt-1">
            <a href="{{ route('dashboard') }}" class="text-primary hover:underline">Dashboard</a>
            <span class="material-symbols-outlined text-[16px]">chevron_right</span>
            <a href="{{ route('content.how-to-return.index') }}" class="text-primary hover:underline">How To Return</a>
            <span class="material-symbols-outlined text-[16px]">chevron_right</span>
            <span>Create</span>
        </nav>
    </div>
    <a href="{{ route('content.how-to-return.index') }}" class="flex items-center gap-2 px-4 py-2 bg-surface-container-lowest border border-outline-variant text-secondary rounded-lg font-label-md hover:bg-surface-container transition-all">
        <span class="material-symbols-outlined text-[18px]">arrow_back</span> Back
    </a>
</div>

<form method="POST" action="{{ route('content.how-to-return.store') }}" id="howToReturnForm" class="space-y-6">
    @csrf
    <div class="bg-white rounded-xl shadow-sm border border-outline-variant/30 p-6">
        <div class="space-y-1.5">
            <label class="block text-label-sm font-medium text-on-surface-variant">Title <span class="text-danger">*</span></label>
            <input type="text" name="title" class="w-full px-3 py-2 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary/20 focus:outline-none" placeholder="Guide title..." required value="{{ old('title') }}">
            @error('title')<p class="text-danger text-sm">{{ $message }}</p>@enderror
        </div>
        <div class="space-y-1.5 mt-4">
            <label class="block text-label-sm font-medium text-on-surface-variant">Slug <span class="text-danger">*</span></label>
            <input type="text" name="slug" class="w-full px-3 py-2 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary/20 focus:outline-none" placeholder="e.g., cara-pengembalian-barang" required value="{{ old('slug') }}">
            @error('slug')<p class="text-danger text-sm">{{ $message }}</p>@enderror
        </div>
        <div class="space-y-1.5 mt-4">
            <label class="block text-label-sm font-medium text-on-surface-variant">Content <span class="text-danger">*</span></label>
            <textarea name="content" rows="6" class="w-full px-3 py-2 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary/20 focus:outline-none" placeholder="Full guide content..." required>{{ old('content') }}</textarea>
            @error('content')<p class="text-danger text-sm">{{ $message }}</p>@enderror
        </div>
        <div class="space-y-1.5 mt-4">
            <label class="block text-label-sm font-medium text-on-surface-variant">Steps</label>
            <div id="steps-container" class="space-y-3">
                @php $initialSteps = old('steps') ? json_decode(old('steps'), true) : []; @endphp
                @if(!empty($initialSteps))
                    @foreach($initialSteps as $index => $step)
                        <div class="step-item border border-outline-variant rounded-lg p-4 bg-surface-gray/30 space-y-2" data-index="{{ $index }}">
                            <div class="flex items-center justify-between">
                                <span class="text-label-sm font-semibold text-primary">Step #{{ $index + 1 }}</span>
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
        <a href="{{ route('content.how-to-return.index') }}" class="px-8 py-3 border border-outline-variant text-primary font-bold rounded-lg hover:bg-surface-container transition-colors">Cancel</a>
        <button type="submit" class="px-10 py-3 bg-primary text-white rounded-lg font-label-md hover:opacity-90 transition-all shadow-sm">Save Guide</button>
    </div>
</form>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const container = document.getElementById('steps-container');
    const form = document.getElementById('howToReturnForm');
    const addBtn = document.getElementById('add-step');

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
        const currentCount = container.querySelectorAll('.step-item').length;
        const html = buildStepHTML(currentCount, data || {});
        const temp = document.createElement('div');
        temp.innerHTML = html.trim();
        container.appendChild(temp.firstElementChild);
    }

    addBtn.addEventListener('click', function() {
        addStep();
    });

    container.addEventListener('click', function(e) {
        if (e.target.classList.contains('remove-step')) {
            const item = e.target.closest('.step-item');
            item.remove();
            updateStepNumbers();
        }
    });

    function updateStepNumbers() {
        const items = container.querySelectorAll('.step-item');
        items.forEach((item, i) => {
            const display = item.querySelector('.step-number-display');
            display.textContent = 'Step #' + (i + 1);
        });
    }

    form.addEventListener('submit', function(e) {
        const items = container.querySelectorAll('.step-item');
        const steps = [];
        items.forEach(function(item) {
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

        let hidden = form.querySelector('input[name="steps"]');
        if (!hidden) {
            hidden = document.createElement('input');
            hidden.type = 'hidden';
            hidden.name = 'steps';
            form.appendChild(hidden);
        }
        hidden.value = JSON.stringify(steps);
    });
});
</script>
@endsection
