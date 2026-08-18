@extends('layouts.app')

@section('title', 'Create Customer')

@section('content')
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="font-headline-lg text-headline-lg text-on-surface">Create Customer</h1>
            <nav class="flex items-center gap-2 text-body-md text-on-surface-variant mt-1">
                <a href="{{ route('dashboard') }}" class="text-primary hover:underline">Dashboard</a>
                <span class="material-symbols-outlined text-[16px]">chevron_right</span>
                <a href="{{ route('customers.index') }}" class="text-primary hover:underline">Customers</a>
                <span class="material-symbols-outlined text-[16px]">chevron_right</span>
                <span>Create</span>
            </nav>
        </div>
        <a href="{{ route('customers.index') }}" class="flex items-center gap-2 px-4 py-2 bg-surface-container-lowest border border-outline-variant text-secondary rounded-lg font-label-md hover:bg-surface-container transition-all">
            <span class="material-symbols-outlined text-[18px]">arrow_back</span> Back
        </a>
    </div>

    @include('layouts.partials.customer-submenu')

    <div class="bg-white rounded-xl shadow-sm border border-outline-variant/30">
        <div class="p-6">
            <form method="POST" action="{{ route('customers.store') }}">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="space-y-1.5">
                        <label class="block text-label-sm font-medium text-on-surface-variant">Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" value="{{ old('name') }}" class="w-full px-3 py-2 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary/20 focus:outline-none" placeholder="Customer name" required>
                        @error('name')<p class="text-danger text-sm">{{ $message }}</p>@enderror
                    </div>
                    <div class="space-y-1.5">
                        <label class="block text-label-sm font-medium text-on-surface-variant">Email <span class="text-danger">*</span></label>
                        <input type="email" name="email" value="{{ old('email') }}" class="w-full px-3 py-2 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary/20 focus:outline-none" placeholder="email@example.com" required>
                        @error('email')<p class="text-danger text-sm">{{ $message }}</p>@enderror
                    </div>
                    <div class="space-y-1.5">
                        <label class="block text-label-sm font-medium text-on-surface-variant">Phone</label>
                        <input type="text" name="phone" value="{{ old('phone') }}" class="w-full px-3 py-2 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary/20 focus:outline-none" placeholder="+62 xxx">
                        @error('phone')<p class="text-danger text-sm">{{ $message }}</p>@enderror
                    </div>
                    <div class="space-y-1.5">
                        <label class="block text-label-sm font-medium text-on-surface-variant">User (optional)</label>
                        <select name="user_id" class="w-full px-3 py-2 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary/20 focus:outline-none select2-enable">
                            <option value="">None</option>
                            @foreach(\App\Models\User::all() as $user)
                                <option value="{{ $user->id }}" {{ old('user_id') == $user->id ? 'selected' : '' }}>{{ $user->name }} ({{ $user->email }})</option>
                            @endforeach
                        </select>
                        @error('user_id')<p class="text-danger text-sm">{{ $message }}</p>@enderror
                    </div>
                    <div class="space-y-1.5">
                        <label class="block text-label-sm font-medium text-on-surface-variant">Customer Type <span class="text-danger">*</span></label>
                        <select name="customer_type" class="w-full px-3 py-2 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary/20 focus:outline-none bg-white">
                            <option value="1" {{ old('customer_type') == '1' ? 'selected' : '' }}>Customer Biasa</option>
                            <option value="2" {{ old('customer_type') == '2' ? 'selected' : '' }}>Reseller</option>
                        </select>
                        @error('customer_type')<p class="text-danger text-sm">{{ $message }}</p>@enderror
                    </div>
                </div>

                <hr class="my-6 border-outline-variant">
                <h3 class="font-headline-sm text-headline-sm text-on-surface mb-4">Initial Address (Optional)</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="space-y-1.5">
                        <label class="block text-label-sm font-medium text-on-surface-variant">Address Label</label>
                        <input type="text" name="label" value="{{ old('label') }}" class="w-full px-3 py-2 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary/20 focus:outline-none" placeholder="e.g. Rumah, Kantor">
                    </div>
                    <div class="space-y-1.5">
                        <label class="block text-label-sm font-medium text-on-surface-variant">Postal Code</label>
                        <input type="text" name="postal_code" value="{{ old('postal_code') }}" class="w-full px-3 py-2 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary/20 focus:outline-none" placeholder="e.g. 12345">
                    </div>
                    <div class="space-y-1.5 md:col-span-2">
                        <label class="block text-label-sm font-medium text-on-surface-variant">Sub-District & City</label>
                        <select name="sub_district_id" class="w-full px-3 py-2 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary/20 focus:outline-none bg-white select2-enable">
                            <option value="">Pilih Kecamatan / Kota</option>
                            @foreach($subDistricts ?? [] as $sub)
                                <option value="{{ $sub->id }}" {{ old('sub_district_id') == $sub->id ? 'selected' : '' }}>{{ $sub->sub_district }}, {{ $sub->city->name ?? '' }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="space-y-1.5 md:col-span-2">
                        <label class="block text-label-sm font-medium text-on-surface-variant">Full Address</label>
                        <textarea name="address" rows="3" class="w-full px-3 py-2 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary/20 focus:outline-none" placeholder="Nama jalan, nomor rumah, RT/RW">{{ old('address') }}</textarea>
                    </div>
                    <div class="md:col-span-2">
                        <label class="flex items-center gap-2 cursor-pointer mt-2">
                            <input type="checkbox" name="is_primary" value="1" {{ old('is_primary', true) ? 'checked' : '' }} class="w-4 h-4 text-primary border-outline-variant rounded focus:ring-primary">
                            <span class="text-label-sm font-medium text-on-surface-variant">Set as Primary Address</span>
                        </label>
                    </div>
                </div>
                <hr class="my-6 border-outline-variant">
                <div class="flex justify-end gap-3">
                    <a href="{{ route('customers.index') }}" class="px-5 py-2 border border-outline-variant text-on-surface-variant rounded-lg font-label-md hover:bg-surface-container transition-colors">Cancel</a>
                    <button type="submit" class="px-5 py-2 bg-primary text-white rounded-lg font-label-md hover:opacity-90 transition-all shadow-sm">Create Customer</button>
                </div>
            </form>
        </div>
    </div>
@endsection
