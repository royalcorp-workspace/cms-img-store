@extends('layouts.app')

@section('title', 'Edit Customer Group')

@section('content')
<div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
    <div>
        <h1 class="font-headline-lg text-headline-lg text-on-surface">Edit Customer Group</h1>
        <nav class="flex items-center gap-2 text-body-md text-on-surface-variant mt-1">
            <a href="{{ route('dashboard') }}" class="text-primary hover:underline">Dashboard</a>
            <span class="material-symbols-outlined text-[16px]">chevron_right</span>
            <a href="{{ route('customers.index') }}" class="text-primary hover:underline">Customers</a>
            <span class="material-symbols-outlined text-[16px]">chevron_right</span>
            <a href="{{ route('customer-groups.index') }}" class="text-primary hover:underline">Customer Groups</a>
            <span class="material-symbols-outlined text-[16px]">chevron_right</span>
            <span>Edit</span>
        </nav>
    </div>
    <a href="{{ route('customer-groups.index') }}" class="flex items-center gap-2 px-4 py-2 bg-surface-container-lowest border border-outline-variant text-secondary rounded-lg font-label-md hover:bg-surface-container transition-all">
        <span class="material-symbols-outlined text-[18px]">arrow_back</span> Kembali
    </a>
</div>

@include('layouts.partials.customer-submenu')

@php
    $selectedMemberIds = old('member_ids', $group->members->pluck('id')->toArray());
@endphp

<div class="bg-white rounded-xl shadow-sm border border-outline-variant/30">
    <div class="p-6">
        <form id="groupForm" method="POST" action="{{ route('customer-groups.update', $group->id) }}">
            @csrf
            @method('PUT')
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="space-y-1.5">
                    <label class="block text-label-sm font-medium text-on-surface-variant">Nama Group <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="w-full px-3 py-2 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary/20 focus:outline-none" placeholder="e.g., Karyawan Royal / Reseller Gold" value="{{ old('name', $group->name) }}" required>
                    @error('name')<p class="text-danger text-sm">{{ $message }}</p>@enderror
                </div>
                <div class="space-y-1.5">
                    <label class="block text-label-sm font-medium text-on-surface-variant">Diskon Default Group (%)</label>
                    <input type="number" name="discount_percent" step="0.01" class="w-full px-3 py-2 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary/20 focus:outline-none" placeholder="0" value="{{ old('discount_percent', floatval($group->discount_percent)) }}">
                    @error('discount_percent')<p class="text-danger text-sm">{{ $message }}</p>@enderror
                </div>
            </div>

            <div class="space-y-1.5 mt-4">
                <label class="block text-label-sm font-medium text-on-surface-variant">Keterangan</label>
                <textarea name="description" rows="2" class="w-full px-3 py-2 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary/20 focus:outline-none" placeholder="Keterangan group customer">{{ old('description', $group->description) }}</textarea>
            </div>

            <div class="space-y-2.5 mt-4 p-4 rounded-xl border border-outline-variant/60 bg-surface-container-lowest shadow-sm">
                <div class="flex items-center justify-between">
                    <label class="block text-label-sm font-semibold text-on-surface">
                        Pilih Anggota Customer
                    </label>
                    <div class="flex items-center gap-2 text-xs">
                        <span id="memberCountBadge" class="px-2.5 py-0.5 rounded-full bg-primary/10 text-primary font-semibold text-[11px]">0 anggota dipilih</span>
                        <span class="text-outline-variant">|</span>
                        <button type="button" id="selectAllMembers" class="text-primary hover:underline font-medium">Pilih Semua</button>
                        <span class="text-outline-variant">|</span>
                        <button type="button" id="clearAllMembers" class="text-secondary hover:text-danger font-medium">Hapus Semua</button>
                    </div>
                </div>
                <select name="member_ids[]" id="membersSelectInput" multiple class="w-full">
                    @foreach($customers as $c)
                        <option value="{{ $c->id }}" 
                            data-name="{{ $c->name }}"
                            data-email="{{ $c->email ?? '' }}"
                            data-phone="{{ $c->phone ?? '' }}"
                            data-type="{{ $c->customer_type == 2 ? 'Reseller' : 'Customer' }}"
                            data-initials="{{ strtoupper(substr($c->name, 0, 2)) }}"
                            {{ in_array($c->id, $selectedMemberIds) ? 'selected' : '' }}>
                            {{ $c->name }} ({{ $c->email ?? $c->phone ?? 'No contact' }})
                        </option>
                    @endforeach
                </select>
                <p class="text-label-xs text-on-surface-variant">Cari pelanggan berdasarkan nama, email, atau nomor HP untuk ditambahkan ke group ini.</p>
                @error('member_ids')<p class="text-danger text-sm">{{ $message }}</p>@enderror
            </div>

            <div class="space-y-1.5 mt-4">
                <label class="block text-label-sm font-medium text-on-surface-variant">Status</label>
                <select name="is_active" id="statusSelect" class="w-full px-3 py-2 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary/20 focus:outline-none">
                    <option value="1" {{ old('is_active', $group->is_active) ? 'selected' : '' }}>Active</option>
                    <option value="0" {{ old('is_active', $group->is_active) ? '' : 'selected' }}>Inactive</option>
                </select>
            </div>

            <hr class="my-6 border-outline-variant">
            <div class="flex justify-end gap-3">
                <a href="{{ route('customer-groups.index') }}" class="px-5 py-2 border border-outline-variant text-on-surface-variant rounded-lg font-label-md hover:bg-surface-container transition-colors">Batal</a>
                <button type="submit" class="px-5 py-2 bg-primary text-white rounded-lg font-label-md hover:opacity-90 transition-all shadow-sm">Perbarui Group</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<style>
.select2-container {
    width: 100% !important;
}
.select2-container--default .select2-selection--single {
    height: 42px !important;
    border: 1px solid var(--color-outline-variant, #cbd5e1) !important;
    border-radius: 0.5rem !important;
    padding: 0 12px !important;
    display: flex !important;
    align-items: center !important;
}
.select2-container--default .select2-selection--multiple {
    min-height: 42px !important;
    border: 1px solid var(--color-outline-variant, #cbd5e1) !important;
    border-radius: 0.5rem !important;
    padding: 4px 34px 4px 10px !important;
}
.select2-dropdown {
    border: 1px solid var(--color-outline-variant, #cbd5e1) !important;
    border-radius: 0.5rem !important;
    box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1) !important;
    z-index: 9999 !important;
}
</style>
<script>
function formatCustomerOption(state) {
    if (!state.id) return state.text;
    const element = state.element;
    if (!element) return state.text;
    const name = element.dataset.name || state.text;
    const email = element.dataset.email || '';
    const phone = element.dataset.phone || '';
    const type = element.dataset.type || '';
    const initials = element.dataset.initials || name.substring(0, 2).toUpperCase();
    
    const contactParts = [];
    if (email) contactParts.push(email);
    if (phone) contactParts.push(phone);
    const contactInfo = contactParts.join(' • ');

    const badge = type === 'Reseller' 
        ? '<span class="px-2 py-0.5 text-[10px] font-semibold bg-amber-100 text-amber-800 rounded-full flex-shrink-0">Reseller</span>'
        : '';

    return $(`
        <div class="flex items-center gap-3 py-1">
            <div class="w-8 h-8 rounded-full bg-primary/10 text-primary flex items-center justify-center font-bold text-xs flex-shrink-0 border border-primary/20">
                ${initials}
            </div>
            <div class="flex-1 min-w-0">
                <div class="flex items-center gap-2">
                    <span class="font-medium text-sm text-on-surface truncate">${name}</span>
                    ${badge}
                </div>
                ${contactInfo ? `<div class="text-xs text-on-surface-variant truncate mt-0.5">${contactInfo}</div>` : ''}
            </div>
        </div>
    `);
}

function formatCustomerSelection(state) {
    if (!state.id) return state.text;
    return state.element?.dataset.name || state.text;
}

function updateMemberCount() {
    const selectedCount = $('#membersSelectInput').val()?.length || 0;
    const badge = document.getElementById('memberCountBadge');
    if (badge) {
        badge.textContent = `${selectedCount} anggota dipilih`;
    }
}

$(document).ready(function() {
    $('#statusSelect').select2({
        width: '100%',
        minimumResultsForSearch: Infinity
    });

    const $memberSelect = $('#membersSelectInput');
    
    $memberSelect.select2({
        placeholder: "Cari nama, email, atau nomor HP customer...",
        allowClear: true,
        width: '100%',
        closeOnSelect: false,
        templateResult: formatCustomerOption,
        templateSelection: formatCustomerSelection,
        matcher: function(params, data) {
            if ($.trim(params.term) === '') return data;
            if (!data.id) return null;
            const term = params.term.toLowerCase();
            const element = data.element;
            const name = (element?.dataset.name || data.text || '').toLowerCase();
            const email = (element?.dataset.email || '').toLowerCase();
            const phone = (element?.dataset.phone || '').toLowerCase();

            if (name.includes(term) || email.includes(term) || phone.includes(term)) {
                return data;
            }
            return null;
        }
    }).on('change', updateMemberCount);

    updateMemberCount();

    $('#selectAllMembers').on('click', function() {
        const allIds = $memberSelect.find('option').map(function() { return $(this).val(); }).get();
        $memberSelect.val(allIds).trigger('change');
    });

    $('#clearAllMembers').on('click', function() {
        $memberSelect.val(null).trigger('change');
    });
});
</script>
@endpush
