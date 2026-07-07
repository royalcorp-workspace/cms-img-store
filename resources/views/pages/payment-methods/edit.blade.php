@extends('layouts.app')

@section('title', 'Edit Payment Method')

@section('content')
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="font-headline-lg text-headline-lg text-on-surface">Edit Payment Method</h1>
            <nav class="flex items-center gap-2 text-body-md text-on-surface-variant mt-1">
                <a href="{{ route('dashboard') }}" class="text-primary hover:underline">Dashboard</a>
                <span class="material-symbols-outlined text-[16px]">chevron_right</span>
                <a href="{{ route('payment-methods.index') }}" class="text-primary hover:underline">Payment Methods</a>
                <span class="material-symbols-outlined text-[16px]">chevron_right</span>
                <span>Edit</span>
            </nav>
        </div>
        <a href="{{ route('payment-methods.index') }}" class="flex items-center gap-2 px-4 py-2 bg-surface-container-lowest border border-outline-variant text-secondary rounded-lg font-label-md hover:bg-surface-container transition-all">
            <span class="material-symbols-outlined text-[18px]">arrow_back</span> Back
        </a>
    </div>

    <form method="POST" action="{{ route('payment-methods.update', $paymentMethod->id) }}" class="space-y-6">
        @csrf
        @method('PUT')
        <div class="bg-white rounded-xl shadow-sm border border-outline-variant/30 p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="space-y-1.5">
                    <label class="block text-label-sm font-medium text-on-surface-variant">Code <span class="text-danger">*</span></label>
                    <input type="text" name="code" class="w-full px-3 py-2 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary/20 focus:outline-none" placeholder="e.g., BCA_TRANSFER" required value="{{ old('code', $paymentMethod->code) }}">
                    @error('code')<p class="text-danger text-sm">{{ $message }}</p>@enderror
                </div>
                <div class="space-y-1.5">
                    <label class="block text-label-sm font-medium text-on-surface-variant">Name <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="w-full px-3 py-2 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary/20 focus:outline-none" placeholder="e.g., BCA Virtual Account" required value="{{ old('name', $paymentMethod->name) }}">
                    @error('name')<p class="text-danger text-sm">{{ $message }}</p>@enderror
                </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                <div class="space-y-1.5">
                    <label class="block text-label-sm font-medium text-on-surface-variant">Type <span class="text-danger">*</span></label>
                    <select name="type" class="w-full px-3 py-2 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary/20 focus:outline-none bg-white" required>
                        <option value="1" {{ old('type', $paymentMethod->type ?? '1') == '1' ? 'selected' : '' }}>Bank Transfer</option>
                        <option value="2" {{ old('type', $paymentMethod->type ?? '1') == '2' ? 'selected' : '' }}>Virtual Account</option>
                        <option value="3" {{ old('type', $paymentMethod->type ?? '1') == '3' ? 'selected' : '' }}>E-Wallet</option>
                        <option value="4" {{ old('type', $paymentMethod->type ?? '1') == '4' ? 'selected' : '' }}>QRIS</option>
                        <option value="5" {{ old('type', $paymentMethod->type ?? '1') == '5' ? 'selected' : '' }}>Credit Card</option>
                        <option value="6" {{ old('type', $paymentMethod->type ?? '1') == '6' ? 'selected' : '' }}>Debit Card</option>
                        <option value="7" {{ old('type', $paymentMethod->type ?? '1') == '7' ? 'selected' : '' }}>COD</option>
                        <option value="8" {{ old('type', $paymentMethod->type ?? '1') == '8' ? 'selected' : '' }}>PayLater</option>
                    </select>
                    @error('type')<p class="text-danger text-sm">{{ $message }}</p>@enderror
                </div>
                <div class="space-y-1.5">
                    <label class="block text-label-sm font-medium text-on-surface-variant">Provider</label>
                    <input type="text" name="provider" class="w-full px-3 py-2 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary/20 focus:outline-none" placeholder="e.g., BCA, Xendit" value="{{ old('provider', $paymentMethod->provider) }}">
                    @error('provider')<p class="text-danger text-sm">{{ $message }}</p>@enderror
                </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                <div class="space-y-1.5">
                    <label class="block text-label-sm font-medium text-on-surface-variant">Image URL</label>
                    <input type="text" name="image" class="w-full px-3 py-2 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary/20 focus:outline-none" placeholder="https://..." value="{{ old('image', $paymentMethod->image) }}">
                    @error('image')<p class="text-danger text-sm">{{ $message }}</p>@enderror
                </div>
                <div class="space-y-1.5">
                    <label class="block text-label-sm font-medium text-on-surface-variant">Sort Order</label>
                    <input type="number" name="sort_order" value="{{ old('sort_order', $paymentMethod->sort_order) }}" min="0" class="w-full px-3 py-2 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary/20 focus:outline-none">
                    @error('sort_order')<p class="text-danger text-sm">{{ $message }}</p>@enderror
                </div>
            </div>
            <div class="mt-4">
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="has_charge" value="1" {{ old('has_charge', $paymentMethod->has_charge) ? 'checked' : '' }} class="w-4 h-4 text-primary border-outline-variant rounded focus:ring-primary" id="hasChargeCheckbox">
                    <span class="text-label-sm font-medium text-on-surface-variant">Has Additional Charge</span>
                </label>
            </div>
            <div id="chargeFields" class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4 {{ old('has_charge', $paymentMethod->has_charge) ? '' : 'hidden' }}">
                <div class="space-y-1.5">
                    <label class="block text-label-sm font-medium text-on-surface-variant">Charge Type</label>
                    <select name="charge_type" class="w-full px-3 py-2 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary/20 focus:outline-none bg-white">
                        <option value="1" {{ (old('charge_type', $paymentMethod->charge_type) == '1') ? 'selected' : '' }}>Percentage (%)</option>
                        <option value="2" {{ (old('charge_type', $paymentMethod->charge_type) == '2') ? 'selected' : '' }}>Fixed Amount (Rp)</option>
                    </select>
                    @error('charge_type')<p class="text-danger text-sm">{{ $message }}</p>@enderror
                </div>
                <div class="space-y-1.5">
                    <label class="block text-label-sm font-medium text-on-surface-variant">Charge Value</label>
                    <input type="number" name="charge_value" step="0.01" min="0" class="w-full px-3 py-2 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary/20 focus:outline-none" placeholder="0.00" value="{{ old('charge_value', $paymentMethod->charge_value) }}">
                    @error('charge_value')<p class="text-danger text-sm">{{ $message }}</p>@enderror
                </div>
                <div class="space-y-1.5">
                    <label class="block text-label-sm font-medium text-on-surface-variant">Charge Bearer</label>
                    <input type="text" name="charge_bearer" class="w-full px-3 py-2 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary/20 focus:outline-none" placeholder="e.g., merchant" value="{{ old('charge_bearer', $paymentMethod->charge_bearer) }}">
                    @error('charge_bearer')<p class="text-danger text-sm">{{ $message }}</p>@enderror
                </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                <div class="space-y-1.5">
                    <label class="block text-label-sm font-medium text-on-surface-variant">Minimum Amount</label>
                    <input type="number" name="minimum_amount" step="0.01" min="0" class="w-full px-3 py-2 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary/20 focus:outline-none" placeholder="0.00" value="{{ old('minimum_amount', $paymentMethod->minimum_amount) }}">
                    @error('minimum_amount')<p class="text-danger text-sm">{{ $message }}</p>@enderror
                </div>
                <div class="space-y-1.5">
                    <label class="block text-label-sm font-medium text-on-surface-variant">Maximum Amount</label>
                    <input type="number" name="maximum_amount" step="0.01" min="0" class="w-full px-3 py-2 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary/20 focus:outline-none" placeholder="0.00" value="{{ old('maximum_amount', $paymentMethod->maximum_amount) }}">
                    @error('maximum_amount')<p class="text-danger text-sm">{{ $message }}</p>@enderror
                </div>
            </div>
        </div>
        <div class="flex justify-end gap-4">
            <a href="{{ route('payment-methods.index') }}" class="px-8 py-3 border border-outline-variant text-primary font-bold rounded-lg hover:bg-surface-container transition-colors">Cancel</a>
            <button type="submit" class="px-10 py-3 bg-primary text-white rounded-lg font-label-md hover:opacity-90 transition-all shadow-sm">Update Payment Method</button>
        </div>
    </form>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const checkbox = document.getElementById('hasChargeCheckbox');
    const chargeFields = document.getElementById('chargeFields');
    if (checkbox && chargeFields) {
        checkbox.addEventListener('change', function() {
            chargeFields.classList.toggle('hidden', !this.checked);
        });
    }
});
</script>
@endpush
