@extends('layouts.app')

@section('title', 'Add New Stock')

@section('content')
<div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
    <div>
        <h1 class="font-headline-lg text-headline-lg text-on-surface">Add New Stock</h1>
        <p class="text-body-md text-on-surface-variant mt-1">Record incoming inventory to your warehouse.

        </p>
    </div>
    <a href="{{ route('inventory.index') }}" class="flex items-center gap-2 px-4 py-2 border border-outline-variant text-on-surface-variant rounded-lg font-label-md hover:bg-surface-container transition-colors">
        <span class="material-symbols-outlined text-[18px]">arrow_back</span>
        Back to Inventory
    </a>
</div>

<div class="bg-white rounded-xl shadow-sm border border-outline-variant/30 overflow-hidden">
    <div class="p-gutter border-b border-outline-variant">
        <h3 class="font-headline-md text-headline-md text-on-surface">Stock Information</h3>
        <p class="text-body-md text-secondary mt-1">Enter the details for the new stock entry.</p>
    </div>
    <div class="p-gutter">
        <form class="space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="space-y-1.5">
                    <label class="block text-label-sm font-bold text-on-surface-variant uppercase tracking-wider">Product Name</label>
                    <select class="select2 w-full bg-surface-gray border border-outline-variant rounded-lg px-4 py-2.5 text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all">
                        <option value="">Select Product</option>
                        <option value="1">CMS Pro X1</option>
                        <option value="2">Zenith Book Air</option>
                        <option value="3">Sonic Blast Elite</option>
                        <option value="4">CMS Watch Ultra</option>
                    </select>
                </div>
                <div class="space-y-1.5">
                    <label class="block text-label-sm font-bold text-on-surface-variant uppercase tracking-wider">SKU</label>
                    <input type="text" value="LPX-2024-001" readonly class="w-full bg-surface-container-low border border-outline-variant rounded-lg px-4 py-2.5 text-body-md text-on-surface-variant">
                </div>
                <div class="space-y-1.5">
                    <label class="block text-label-sm font-bold text-on-surface-variant uppercase tracking-wider">Warehouse Location</label>
                    <select class="select2 w-full bg-surface-gray border border-outline-variant rounded-lg px-4 py-2.5 text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all">
                        <option value="">Select Warehouse</option>
                        <option value="1">NA-01 (California)</option>
                        <option value="2">EU-04 (Berlin)</option>
                        <option value="3">AP-09 (Singapore)</option>
                        <option value="4">AU-01 (Sydney)</option>
                    </select>
                </div>
                <div class="space-y-1.5">
                    <label class="block text-label-sm font-bold text-on-surface-variant uppercase tracking-wider">Quantity Added</label>
                    <input type="number" placeholder="Enter quantity" class="w-full bg-surface-gray border border-outline-variant rounded-lg px-4 py-2.5 text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all">
                </div>
                <div class="space-y-1.5">
                    <label class="block text-label-sm font-bold text-on-surface-variant uppercase tracking-wider">Supplier</label>
                    <input type="text" placeholder="Supplier name" class="w-full bg-surface-gray border border-outline-variant rounded-lg px-4 py-2.5 text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all">
                </div>
                <div class="space-y-1.5">
                    <label class="block text-label-sm font-bold text-on-surface-variant uppercase tracking-wider">Purchase Order No.</label>
                    <input type="text" placeholder="PO-XXXXX" class="w-full bg-surface-gray border border-outline-variant rounded-lg px-4 py-2.5 text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all">
                </div>
            </div>
            <div class="space-y-1.5">
                <label class="block text-label-sm font-bold text-on-surface-variant uppercase tracking-wider">Notes</label>
                <textarea rows="4" placeholder="Optional notes about this stock entry..." class="w-full bg-surface-gray border border-outline-variant rounded-lg px-4 py-2.5 text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all resize-none"></textarea>
            </div>
        </form>
    </div>
    <div class="px-gutter py-4 border-t border-outline-variant bg-surface-container-low/30 flex justify-end gap-3">
        <a href="{{ route('inventory.index') }}" class="flex items-center gap-2 px-5 py-2.5 border border-outline-variant text-on-surface-variant rounded-lg font-label-md hover:bg-surface-container transition-colors">
            Cancel
        </a>
        <button type="submit" class="flex items-center gap-2 px-6 py-2.5 bg-primary text-white rounded-lg font-label-md hover:opacity-90 transition-all shadow-sm">
            <span class="material-symbols-outlined text-[18px]">save</span>
            Save Stock Entry
        </button>
    </div>
</div>

@push('scripts')
<script>
    $(document).ready(function() {
        $('.select2').select2({
            width: 'resolve',
            theme: 'classic',
            dropdownCssClass: 'border-outline-variant bg-white rounded-lg shadow-lg'
        });
    });
</script>
<style>
    .select2-container--classic .select2-selection--single {
        border: 1px solid #d4c4a8 !important;
        border-radius: 0.5rem !important;
        padding: 0.5rem 1rem !important;
        height: auto !important;
        min-height: 42px !important;
        background-color: #f2ebd9 !important;
    }
    .select2-container--classic.select2-container--open .select2-selection--single {
        border-color: #c09d6b !important;
    }
    .select2-container--classic .select2-selection--single .select2-selection__rendered {
        color: #2b1d12 !important;
        line-height: 1.5 !important;
        padding-left: 0 !important;
    }
    .select2-container--classic .select2-selection--single .select2-selection__arrow {
        height: 40px !important;
        right: 8px !important;
    }
    .select2-container--classic .select2-selection--single .select2-selection__placeholder {
        color: #8b7355 !important;
    }
    .dark .select2-container--classic .select2-selection--single {
        background-color: var(--color-surface-container) !important;
        border-color: #fff !important;
    }
    .dark .select2-container--classic .select2-selection--single .select2-selection__rendered {
        color: #fff !important;
    }
    .dark .select2-container--classic .select2-selection--single .select2-selection__placeholder {
        color: #aaa !important;
    }
    .dark .select2-dropdown {
        border-color: #fff !important;
    }
    .dark .select2-container--classic .select2-results__option--highlighted[aria-selected] {
        background-color: #000 !important;
        color: #fff !important;
    }
    .dark .select2-container--classic .select2-results__option[aria-selected=true] {
        background-color: #222 !important;
        color: #fff !important;
    }
    .select2-dropdown {
        border: 1px solid #d4c4a8 !important;
        border-radius: 0.5rem !important;
        overflow: hidden !important;
    }
    .select2-container--classic .select2-results__option--highlighted[aria-selected] {
        background-color: #f2ebd9 !important;
        color: #2b1d12 !important;
    }
    .select2-container--classic .select2-results__option[aria-selected=true] {
        background-color: #e8dfcc !important;
        color: #1a1009 !important;
    }
</style>
@endpush
@endsection
