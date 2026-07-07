@extends('layouts.app')

@section('title', 'Create Store')

@section('content')
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="font-headline-lg text-headline-lg text-on-surface">Create Store</h1>
            <nav class="flex items-center gap-2 text-body-md text-on-surface-variant mt-1">
                <a href="{{ route('dashboard') }}" class="text-primary hover:underline">Dashboard</a>
                <span class="material-symbols-outlined text-[16px]">chevron_right</span>
                <a href="{{ route('stores.index') }}" class="text-primary hover:underline">Stores</a>
                <span class="material-symbols-outlined text-[16px]">chevron_right</span>
                <span>Create</span>
            </nav>
        </div>
        <a href="{{ route('stores.index') }}" class="flex items-center gap-2 px-4 py-2 bg-surface-container-lowest border border-outline-variant text-secondary rounded-lg font-label-md hover:bg-surface-container transition-all">
            <span class="material-symbols-outlined text-[18px]">arrow_back</span> Back
        </a>
    </div>

    <form method="POST" action="{{ route('stores.store') }}" class="space-y-6">
        @csrf
        <div class="bg-white rounded-xl shadow-sm border border-outline-variant/30 p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="space-y-1.5">
                    <label class="block text-label-sm font-medium text-on-surface-variant">Store Group <span class="text-danger">*</span></label>
                    <select name="store_group_id" class="w-full px-3 py-2 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary/20 focus:outline-none bg-white" required>
                        <option value="">Select Group</option>
                        @foreach($storeGroups as $group)
                            <option value="{{ $group->id }}" {{ old('store_group_id') == $group->id ? 'selected' : '' }}>{{ $group->name }} ({{ $group->code }})</option>
                        @endforeach
                    </select>
                    @error('store_group_id')<p class="text-danger text-sm">{{ $message }}</p>@enderror
                </div>
                <div class="space-y-1.5">
                    <label class="block text-label-sm font-medium text-on-surface-variant">Code <span class="text-danger">*</span></label>
                    <input type="text" name="code" class="w-full px-3 py-2 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary/20 focus:outline-none" placeholder="e.g., TOKO_A" required value="{{ old('code') }}">
                    @error('code')<p class="text-danger text-sm">{{ $message }}</p>@enderror
                </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                <div class="space-y-1.5">
                    <label class="block text-label-sm font-medium text-on-surface-variant">Name <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="w-full px-3 py-2 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary/20 focus:outline-none" placeholder="e.g., Toko A" required value="{{ old('name') }}">
                    @error('name')<p class="text-danger text-sm">{{ $message }}</p>@enderror
                </div>
                <div class="space-y-1.5">
                    <label class="block text-label-sm font-medium text-on-surface-variant">Tier</label>
                    <select name="tier_id" class="w-full px-3 py-2 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary/20 focus:outline-none bg-white">
                        <option value="">Select Tier</option>
                        @foreach($tiers as $tier)
                            <option value="{{ $tier->id }}" {{ old('tier_id') == $tier->id ? 'selected' : '' }}>{{ $tier->name }} (Level {{ $tier->level }})</option>
                        @endforeach
                    </select>
                    @error('tier_id')<p class="text-danger text-sm">{{ $message }}</p>@enderror
                </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                <div class="space-y-1.5">
                    <label class="block text-label-sm font-medium text-on-surface-variant">Owner User</label>
                    <select name="owner_user_id" class="w-full px-3 py-2 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary/20 focus:outline-none bg-white">
                        <option value="">Select Owner</option>
                        @foreach(\App\Models\Admin::all() as $admin)
                            <option value="{{ $admin->id }}" {{ old('owner_user_id') == $admin->id ? 'selected' : '' }}>{{ $admin->name }} ({{ $admin->email }})</option>
                        @endforeach
                    </select>
                    @error('owner_user_id')<p class="text-danger text-sm">{{ $message }}</p>@enderror
                </div>
                <div class="space-y-1.5">
                    <label class="block text-label-sm font-medium text-on-surface-variant">Credit Limit</label>
                    <input type="number" name="credit_limit" value="{{ old('credit_limit', 0) }}" min="0" step="0.01" class="w-full px-3 py-2 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary/20 focus:outline-none">
                    @error('credit_limit')<p class="text-danger text-sm">{{ $message }}</p>@enderror
                </div>
            </div>
            <div class="space-y-1.5 mt-4">
                <label class="block text-label-sm font-medium text-on-surface-variant">Address</label>
                <textarea name="address" rows="2" class="w-full px-3 py-2 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary/20 focus:outline-none" placeholder="Store address">{{ old('address') }}</textarea>
                @error('address')<p class="text-danger text-sm">{{ $message }}</p>@enderror
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4">
                <div class="space-y-1.5">
                    <label class="block text-label-sm font-medium text-on-surface-variant">Phone</label>
                    <input type="text" name="phone" value="{{ old('phone') }}" maxlength="50" class="w-full px-3 py-2 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary/20 focus:outline-none">
                    @error('phone')<p class="text-danger text-sm">{{ $message }}</p>@enderror
                </div>
                <div class="space-y-1.5">
                    <label class="block text-label-sm font-medium text-on-surface-variant">Email</label>
                    <input type="email" name="email" value="{{ old('email') }}" maxlength="255" class="w-full px-3 py-2 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary/20 focus:outline-none">
                    @error('email')<p class="text-danger text-sm">{{ $message }}</p>@enderror
                </div>
                <div class="space-y-1.5">
                    <label class="block text-label-sm font-medium text-on-surface-variant">Payment Term (days)</label>
                    <input type="number" name="payment_term" value="{{ old('payment_term', 0) }}" min="0" class="w-full px-3 py-2 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary/20 focus:outline-none">
                    <p class="text-label-xs text-on-surface-variant">0 = cash, 30 = net 30, etc</p>
                    @error('payment_term')<p class="text-danger text-sm">{{ $message }}</p>@enderror
                </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                <div class="space-y-1.5">
                    <label class="block text-label-sm font-medium text-on-surface-variant">Sort Order</label>
                    <input type="number" name="sort_order" value="{{ old('sort_order', 0) }}" min="0" class="w-full px-3 py-2 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary/20 focus:outline-none">
                    @error('sort_order')<p class="text-danger text-sm">{{ $message }}</p>@enderror
                </div>
                <div class="space-y-1.5">
                    <label class="block text-label-sm font-medium text-on-surface-variant">Status</label>
                    <select name="status" class="w-full px-3 py-2 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary/20 focus:outline-none bg-white">
                        <option value="1" {{ old('status', '1') == '1' ? 'selected' : '' }}>Active</option>
                        <option value="0" {{ old('status') == '0' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>
            </div>
        </div>
        <div class="flex justify-end gap-4">
            <a href="{{ route('stores.index') }}" class="px-8 py-3 border border-outline-variant text-primary font-bold rounded-lg hover:bg-surface-container transition-colors">Cancel</a>
            <button type="submit" class="px-10 py-3 bg-primary text-white rounded-lg font-label-md hover:opacity-90 transition-all shadow-sm">Save Store</button>
        </div>
    </form>
@endsection
