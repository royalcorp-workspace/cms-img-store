@extends('layouts.app')

@section('title', 'Edit Voucher')

@section('content')
<div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
    <div>
        <h1 class="font-headline-lg text-headline-lg text-on-surface">Edit Voucher</h1>
        <nav class="flex items-center gap-2 text-body-md text-on-surface-variant mt-1">
            <a href="{{ route('dashboard') }}" class="text-primary hover:underline">Dashboard</a>
            <span class="material-symbols-outlined text-[16px]">chevron_right</span>
            <a href="{{ route('vouchers.index') }}" class="text-primary hover:underline">Vouchers</a>
            <span class="material-symbols-outlined text-[16px]">chevron_right</span>
            <span>Edit</span>
        </nav>
    </div>
    <a href="{{ route('vouchers.index') }}" class="flex items-center gap-2 px-4 py-2 bg-surface-container-lowest border border-outline-variant text-secondary rounded-lg font-label-md hover:bg-surface-container transition-all">
        <span class="material-symbols-outlined text-[18px]">arrow_back</span> Back
    </a>
</div>

    @include('layouts.partials.promotions-submenu')

<div class="bg-white rounded-xl shadow-sm border border-outline-variant/30">
    <div class="p-6">
        <form id="voucherForm" method="POST" action="{{ route('vouchers.update', $voucher->id) }}">
            @csrf @method('PUT')
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="space-y-1.5">
                    <label class="block text-label-sm font-medium text-on-surface-variant">Voucher Code <span class="text-danger">*</span> <span class="inline-flex items-center cursor-help text-on-surface-variant relative group"><span class="material-symbols-outlined text-[18px]">info</span><span class="absolute right-0 top-full mt-2 w-80 bg-surface-container-highest rounded-lg shadow-lg border border-outline-variant p-4 text-body-xs text-on-surface-variant hidden group-hover:block z-50">Kode unik voucher yang akan digunakan oleh customer saat checkout transaksi.</span></span></label>
                    <input type="text" name="code" class="w-full px-3 py-2 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary/20 focus:outline-none" placeholder="e.g., SUMMER2024" required value="{{ old('code', $voucher->code) }}">
                    @error('code')<p class="text-danger text-sm">{{ $message }}</p>@enderror
                </div>
                <div class="space-y-1.5">
                    <label class="block text-label-sm font-medium text-on-surface-variant">Title <span class="inline-flex items-center cursor-help text-on-surface-variant relative group"><span class="material-symbols-outlined text-[18px]">info</span><span class="absolute right-0 top-full mt-2 w-80 bg-surface-container-highest rounded-lg shadow-lg border border-outline-variant p-4 text-body-xs text-on-surface-variant hidden group-hover:block z-50">Nama/internal label voucher untuk keperluan admin, tidak ditampilkan ke customer.</span></span></label>
                    <input type="text" name="title" class="w-full px-3 py-2 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary/20 focus:outline-none" placeholder="Voucher title" value="{{ old('title', $voucher->title) }}">
                    @error('title')<p class="text-danger text-sm">{{ $message }}</p>@enderror
                </div>
            </div>

            <div class="space-y-1.5 mt-4">
                <label class="block text-label-sm font-medium text-on-surface-variant">Description <span class="inline-flex items-center cursor-help text-on-surface-variant relative group"><span class="material-symbols-outlined text-[18px]">info</span><span class="absolute right-0 top-full mt-2 w-80 bg-surface-container-highest rounded-lg shadow-lg border border-outline-variant p-4 text-body-xs text-on-surface-variant hidden group-hover:block z-50">Keterangan detail syarat & ketentuan penggunaan voucher. Bisa ditampilkan di halaman checkout.</span></span></label>
                <textarea name="description" rows="2" class="w-full px-3 py-2 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary/20 focus:outline-none" placeholder="Voucher description">{{ old('description', $voucher->description) }}</textarea>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                <div class="space-y-1.5">
                    <label class="block text-label-sm font-medium text-on-surface-variant">Discount Type <span class="text-danger">*</span> <span class="inline-flex items-center cursor-help text-on-surface-variant relative group"><span class="material-symbols-outlined text-[18px]">info</span><span class="absolute right-0 top-full mt-2 w-80 bg-surface-container-highest rounded-lg shadow-lg border border-outline-variant p-4 text-body-xs text-on-surface-variant hidden group-hover:block z-50">Tipe diskon yang diberikan: persentase (%), nominal tetap (Rp), potongan ongkir (Rp), atau bonus produk (pcs).</span></span></label>
                    <select name="type" id="typeSelect" class="w-full px-3 py-2 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary/20 focus:outline-none select2-enable">
                        <option value="1" {{ old('type', $voucher->type) == 1 ? 'selected' : '' }}>Percentage (%)</option>
                        <option value="2" {{ old('type', $voucher->type) == 2 ? 'selected' : '' }}>Fixed Amount (Rp)</option>
                        <option value="3" {{ old('type', $voucher->type) == 3 ? 'selected' : '' }}>Shipping Discount (Rp)</option>
                        <option value="4" {{ old('type', $voucher->type) == 4 ? 'selected' : '' }}>Bonus Product (pcs)</option>
                    </select>
                </div>
                <div class="space-y-1.5">
                    <label class="block text-label-sm font-medium text-on-surface-variant">Value <span class="text-danger">*</span> <span class="inline-flex items-center cursor-help text-on-surface-variant relative group"><span class="material-symbols-outlined text-[18px]">info</span><span class="absolute right-0 top-full mt-2 w-80 bg-surface-container-highest rounded-lg shadow-lg border border-outline-variant p-4 text-body-xs text-on-surface-variant hidden group-hover:block z-50">Nilai diskon sesuai tipe yang dipilih: masukkan persentase (%), nominal Rp, biaya ongkir, atau jumlah pcs bonus.</span></span></label>
                    <input type="number" name="value" step="0.01" class="w-full px-3 py-2 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary/20 focus:outline-none" placeholder="0" required value="{{ old('value', $voucher->value) }}">
                    @error('value')<p class="text-danger text-sm">{{ $message }}</p>@enderror
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                <div class="space-y-1.5">
                    <label class="block text-label-sm font-medium text-on-surface-variant">Min Purchase (Rp) <span class="inline-flex items-center cursor-help text-on-surface-variant relative group"><span class="material-symbols-outlined text-[18px]">info</span><span class="absolute right-0 top-full mt-2 w-80 bg-surface-container-highest rounded-lg shadow-lg border border-outline-variant p-4 text-body-xs text-on-surface-variant hidden group-hover:block z-50">Total belanja minimum agar voucher bisa digunakan. Isi 0 jika tidak ada minimum.</span></span></label>
                    <input type="number" name="min_purchase" step="0.01" class="w-full px-3 py-2 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary/20 focus:outline-none" placeholder="0" value="{{ old('min_purchase', $voucher->min_purchase) }}">
                </div>
                <div class="space-y-1.5" id="maxDiscountContainer">
                    <label class="block text-label-sm font-medium text-on-surface-variant">Max Discount (Rp) <span class="inline-flex items-center cursor-help text-on-surface-variant relative group"><span class="material-symbols-outlined text-[18px]">info</span><span class="absolute right-0 top-full mt-2 w-80 bg-surface-container-highest rounded-lg shadow-lg border border-outline-variant p-4 text-body-xs text-on-surface-variant hidden group-hover:block z-50">Batas maksimum diskon yang diberikan (khusus tipe persentase). Isi 0 jika tidak ada batas.</span></span></label>
                    <input type="number" name="max_discount" step="0.01" class="w-full px-3 py-2 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary/20 focus:outline-none" placeholder="0" value="{{ old('max_discount', $voucher->max_discount) }}">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                <div class="space-y-1.5">
                    <label class="block text-label-sm font-medium text-on-surface-variant">Start Date <span class="inline-flex items-center cursor-help text-on-surface-variant relative group"><span class="material-symbols-outlined text-[18px]">info</span><span class="absolute right-0 top-full mt-2 w-80 bg-surface-container-highest rounded-lg shadow-lg border border-outline-variant p-4 text-body-xs text-on-surface-variant hidden group-hover:block z-50">Tanggal mulai voucher aktif. Kosongkan jika voucher langsung aktif setelah dibuat.</span></span></label>
                    <input type="date" name="start_date" class="w-full px-3 py-2 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary/20 focus:outline-none" value="{{ old('start_date', $voucher->start_date?->format('Y-m-d')) }}">
                </div>
                <div class="space-y-1.5">
                    <label class="block text-label-sm font-medium text-on-surface-variant">End Date <span class="inline-flex items-center cursor-help text-on-surface-variant relative group"><span class="material-symbols-outlined text-[18px]">info</span><span class="absolute right-0 top-full mt-2 w-80 bg-surface-container-highest rounded-lg shadow-lg border border-outline-variant p-4 text-body-xs text-on-surface-variant hidden group-hover:block z-50">Tanggal kadaluarsa voucher. Kosongkan jika tidak ada batas waktu (voucher berlaku terus menerus).</span></span></label>
                    <input type="date" name="end_date" class="w-full px-3 py-2 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary/20 focus:outline-none" value="{{ old('end_date', $voucher->end_date?->format('Y-m-d')) }}">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                <div class="space-y-1.5">
                    <label class="block text-label-sm font-medium text-on-surface-variant">Usage Limit <span class="inline-flex items-center cursor-help text-on-surface-variant relative group"><span class="material-symbols-outlined text-[18px]">info</span><span class="absolute right-0 top-full mt-2 w-80 bg-surface-container-highest rounded-lg shadow-lg border border-outline-variant p-4 text-body-xs text-on-surface-variant hidden group-hover:block z-50">Banyaknya voucher ini bisa ditukarkan secara keseluruhan oleh semua customer. Isi 0 jika unlimited.</span></span></label>
                    <input type="number" name="usage_limit" class="w-full px-3 py-2 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary/20 focus:outline-none" placeholder="0 = unlimited" value="{{ old('usage_limit', $voucher->usage_limit) }}">
                </div>
                <div class="space-y-1.5">
                    <label class="block text-label-sm font-medium text-on-surface-variant">Per User Limit <span class="inline-flex items-center cursor-help text-on-surface-variant relative group"><span class="material-symbols-outlined text-[18px]">info</span><span class="absolute right-0 top-full mt-2 w-80 bg-surface-container-highest rounded-lg shadow-lg border border-outline-variant p-4 text-body-xs text-on-surface-variant hidden group-hover:block z-50">Batas pemakaian voucher per satu akun customer. Isi 0 jika unlimited per user.</span></span></label>
                    <input type="number" name="usage_limit_per_user" class="w-full px-3 py-2 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary/20 focus:outline-none" placeholder="0 = unlimited" value="{{ old('usage_limit_per_user', $voucher->usage_limit_per_user) }}">
                </div>
            </div>

            <div class="space-y-1.5 mt-4">
                <label class="block text-label-sm font-medium text-on-surface-variant">Scope <span class="text-danger">*</span> <span class="inline-flex items-center cursor-help text-on-surface-variant relative group"><span class="material-symbols-outlined text-[18px]">info</span><span class="absolute right-0 top-full mt-2 w-80 bg-surface-container-highest rounded-lg shadow-lg border border-outline-variant p-4 text-body-xs text-on-surface-variant hidden group-hover:block z-50">Cakupan penggunaan voucher. Pilih 'Semua Customer' untuk voucher umum, atau 'Customer Tertentu' untuk voucher khusus pelanggan tertentu.</span></span></label>
                <select name="scope" id="scopeSelect" class="w-full px-3 py-2 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary/20 focus:outline-none">
                    <option value="1" {{ old('scope', $voucher->scope) == 1 ? 'selected' : '' }}>Semua Customer</option>
                    <option value="2" {{ old('scope', $voucher->scope) == 2 ? 'selected' : '' }}>Customer Tertentu</option>
                    <option value="3" {{ old('scope', $voucher->scope) == 3 ? 'selected' : '' }}>Kategori Tertentu</option>
                </select>
            </div>

            <div id="customerSelect" class="space-y-2.5 mt-4 {{ old('scope', $voucher->scope) == 2 ? '' : 'hidden' }} p-4 rounded-xl border border-outline-variant/60 bg-surface-container-lowest shadow-sm">
                <div class="flex items-center justify-between">
                    <label class="block text-label-sm font-semibold text-on-surface">
                        Pilih Customer <span class="text-danger">*</span>
                        <span class="inline-flex items-center cursor-help text-on-surface-variant relative group ml-1">
                            <span class="material-symbols-outlined text-[16px]">info</span>
                            <span class="absolute left-0 top-full mt-2 w-80 bg-surface-container-highest rounded-lg shadow-lg border border-outline-variant p-4 text-body-xs text-on-surface-variant hidden group-hover:block z-50 font-normal">
                                Pilih satu atau beberapa customer yang berhak menggunakan voucher ini. Anda dapat mencari berdasarkan nama, email, atau nomor HP.
                            </span>
                        </span>
                    </label>
                    <div class="flex items-center gap-2 text-xs">
                        <span id="customerCountBadge" class="px-2.5 py-0.5 rounded-full bg-primary/10 text-primary font-semibold text-[11px]">0 customer dipilih</span>
                        <span class="text-outline-variant">|</span>
                        <button type="button" id="selectAllCustomers" class="text-primary hover:underline font-medium">Pilih Semua</button>
                        <span class="text-outline-variant">|</span>
                        <button type="button" id="clearAllCustomers" class="text-secondary hover:text-danger font-medium">Hapus Semua</button>
                    </div>
                </div>
                <select name="customer_ids[]" id="customersSelectInput" multiple class="w-full">
                    @foreach(\App\Models\Customer\Customer::orderBy('name')->get() as $c)
                        <option value="{{ $c->id }}" 
                            data-name="{{ $c->name }}"
                            data-email="{{ $c->email ?? '' }}"
                            data-phone="{{ $c->phone ?? '' }}"
                            data-type="{{ $c->customer_type == 2 ? 'Reseller' : 'Customer' }}"
                            data-initials="{{ strtoupper(substr($c->name, 0, 2)) }}"
                            {{ (is_array(old('customer_ids')) ? in_array($c->id, old('customer_ids')) : $voucher->customers->contains('id', $c->id)) ? 'selected' : '' }}>
                            {{ $c->name }} ({{ $c->email ?? $c->phone ?? 'No contact' }})
                        </option>
                    @endforeach
                </select>
                <p class="text-label-xs text-on-surface-variant">Ketik untuk mencari customer berdasarkan nama, email, atau nomor HP.</p>
                @error('customer_ids')<p class="text-danger text-sm">{{ $message }}</p>@enderror
            </div>


            <div id="categorySelect" class="space-y-1.5 mt-4 {{ old('scope', $voucher->scope) == 3 ? '' : 'hidden' }}">
                <label class="block text-label-sm font-medium text-on-surface-variant">Categories <span class="text-danger">*</span></label>
                <select name="category_ids[]" multiple class="w-full px-3 py-2 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary/20 focus:outline-none bg-white h-32">
                    @foreach(\App\Models\Product\Category::all() as $c)
                        <option value="{{ $c->id }}" {{ (is_array(old('category_ids')) ? in_array($c->id, old('category_ids')) : $voucher->categories->contains('id', $c->id)) ? 'selected' : '' }}>{{ $c->name }}</option>
                    @endforeach
                </select>
                <p class="text-label-sm text-on-surface-variant">Hold Ctrl/Cmd to select multiple</p>
                @error('category_ids')<p class="text-danger text-sm">{{ $message }}</p>@enderror
            </div>

            <div class="flex items-center gap-2 mt-4">
                <input type="checkbox" name="show_on_web" id="showOnWeb" class="w-4 h-4 rounded border-outline-variant text-primary focus:ring-primary" value="1" {{ old('show_on_web', $voucher->show_on_web ?? 1) ? 'checked' : '' }}>
                <label for="showOnWeb" class="text-label-sm font-medium text-on-surface-variant">Show on Web (Tampilkan di Website) <span class="inline-flex items-center cursor-help text-on-surface-variant relative group"><span class="material-symbols-outlined text-[18px]">info</span><span class="absolute right-0 top-full mt-2 w-80 bg-surface-container-highest rounded-lg shadow-lg border border-outline-variant p-4 text-body-xs text-on-surface-variant hidden group-hover:block z-50">Jika dicentang, voucher akan ditampilkan di website untuk pelanggan. Nonaktifkan jika voucher bersifat khusus/rahasia.</span></span></label>
            </div>

            <div id="allowStackingContainer" class="flex items-center gap-2 mt-4 {{ old('type', $voucher->type) == 3 ? '' : 'hidden' }}">
                <input type="checkbox" name="allow_stacking" id="allowStacking" class="w-4 h-4 rounded border-outline-variant text-primary focus:ring-primary" value="1" {{ old('allow_stacking', $voucher->allow_stacking) ? 'checked' : '' }}>
                <label for="allowStacking" class="text-label-sm font-medium text-on-surface-variant">Allow Stacking (bisa digabungkan dengan voucher belanja lain) <span class="inline-flex items-center cursor-help text-on-surface-variant relative group"><span class="material-symbols-outlined text-[18px]">info</span><span class="absolute right-0 top-full mt-2 w-80 bg-surface-container-highest rounded-lg shadow-lg border border-outline-variant p-4 text-body-xs text-on-surface-variant hidden group-hover:block z-50">Hanya voucher diskon ongkir yang dapat digabungkan dengan voucher belanja (persen / nominal). Jika dicentang, customer dapat memakai voucher ongkir ini bersamaan dengan voucher diskon belanja.</span></span></label>
            </div>

            <div class="flex items-center gap-2 mt-4">
                <input type="checkbox" name="valid_for_new_customer" id="validForNewCustomer" class="w-4 h-4 rounded border-outline-variant text-primary focus:ring-primary" value="1" {{ old('valid_for_new_customer', $voucher->valid_for_new_customer) ? 'checked' : '' }}>
                <label for="validForNewCustomer" class="text-label-sm font-medium text-on-surface-variant">Valid for New Customer Only <span class="inline-flex items-center cursor-help text-on-surface-variant relative group"><span class="material-symbols-outlined text-[18px]">info</span><span class="absolute right-0 top-full mt-2 w-80 bg-surface-container-highest rounded-lg shadow-lg border border-outline-variant p-4 text-body-xs text-on-surface-variant hidden group-hover:block z-50">Jika dicentang, voucher hanya bisa digunakan oleh customer yang pertama kali bertransaksi di toko.</span></span></label>
            </div>

            <div class="space-y-1.5 mt-4">
                <label class="block text-label-sm font-medium text-on-surface-variant">Status <span class="inline-flex items-center cursor-help text-on-surface-variant relative group"><span class="material-symbols-outlined text-[18px]">info</span><span class="absolute right-0 top-full mt-2 w-80 bg-surface-container-highest rounded-lg shadow-lg border border-outline-variant p-4 text-body-xs text-on-surface-variant hidden group-hover:block z-50">Set Active agar voucher dapat digunakan di checkout. Pilih Inactive untuk menonaktifkan sementara tanpa menghapus data.</span></span></label>
                <select name="is_active" class="w-full px-3 py-2 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary/20 focus:outline-none">
                    <option value="1" {{ old('is_active', $voucher->is_active) == 1 ? 'selected' : '' }}>Active</option>
                    <option value="0" {{ old('is_active', $voucher->is_active) == 0 ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>

            <hr class="my-6 border-outline-variant">
            <div class="flex justify-end gap-3">
                <a href="{{ route('vouchers.index') }}" class="px-5 py-2 border border-outline-variant text-on-surface-variant rounded-lg font-label-md hover:bg-surface-container transition-colors">Cancel</a>
                <button type="submit" class="px-5 py-2 bg-primary text-white rounded-lg font-label-md hover:opacity-90 transition-all shadow-sm">Update Voucher</button>
            </div>
        </form>
    </div>
</div>
@push('scripts')
<style>
    /* Light Mode Styles */
    .select2-container--classic .select2-selection--multiple {
        background-color: #fff !important;
        border: 1px solid #d1d5db !important;
        border-radius: 0.5rem !important;
        padding: 4px 8px !important;
        min-height: 42px !important;
        display: flex !important;
        align-items: center !important;
        flex-wrap: wrap !important;
        gap: 4px !important;
    }
    .select2-container--classic.select2-container--focus .select2-selection--multiple {
        border-color: #3b82f6 !important;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.15) !important;
    }
    .select2-container--classic .select2-selection--multiple .select2-selection__choice {
        background-color: #f3f4f6 !important;
        border: 1px solid #e5e7eb !important;
        border-radius: 0.375rem !important;
        color: #1f2937 !important;
        padding: 2px 8px !important;
        font-size: 13px !important;
        font-weight: 500 !important;
        display: inline-flex !important;
        align-items: center !important;
        gap: 6px !important;
        margin: 0 !important;
    }
    .select2-container--classic .select2-selection--multiple .select2-selection__choice__remove {
        color: #ef4444 !important;
        font-weight: bold !important;
        margin-right: 0 !important;
        border: none !important;
        background: transparent !important;
        cursor: pointer !important;
    }
    .select2-container--classic .select2-selection--multiple .select2-selection__choice__remove:hover {
        background: transparent !important;
        color: #b91c1c !important;
    }
    .select2-container--classic .select2-selection--multiple .select2-search--inline .select2-search__field {
        color: #1f2937 !important;
        font-size: 14px !important;
        margin: 0 !important;
        height: auto !important;
        background: transparent !important;
    }
    .select2-dropdown {
        border: 1px solid #e5e7eb !important;
        border-radius: 0.5rem !important;
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1) !important;
        overflow: hidden !important;
        z-index: 9999 !important;
    }
    .select2-container--classic .select2-results__option {
        padding: 8px 12px !important;
        font-size: 14px !important;
    }
    .select2-container--classic .select2-results__option--highlighted[aria-selected] {
        background-color: #3b82f6 !important;
        color: #fff !important;
    }
    .select2-container--classic .select2-results__option[aria-selected=true] {
        background-color: #eff6ff !important;
        color: #1e40af !important;
    }

    /* Dark Mode Styles */
    .dark .select2-container--classic .select2-selection--multiple {
        background-color: #1e1e2d !important;
        border: 1px solid #3f3f46 !important;
    }
    .dark .select2-container--classic.select2-container--focus .select2-selection--multiple {
        border-color: #3b82f6 !important;
    }
    .dark .select2-container--classic .select2-selection--multiple .select2-selection__choice {
        background-color: #27272a !important;
        border-color: #3f3f46 !important;
        color: #e4e4e7 !important;
    }
    .dark .select2-container--classic .select2-selection--multiple .select2-search--inline .select2-search__field {
        color: #fff !important;
    }
    .dark .select2-dropdown {
        background-color: #1e1e2d !important;
        border-color: #3f3f46 !important;
        color: #fff !important;
    }
    .dark .select2-container--classic .select2-results__option[aria-selected=true] {
        background-color: #27272a !important;
        color: #fff !important;
    }
    .dark .select2-container--classic .select2-results__option--highlighted[aria-selected] {
        background-color: #3b82f6 !important;
        color: #fff !important;
    }
</style>
<script>
function adjustFormFields() {
    const type = document.getElementById('typeSelect').value;
    const scope = document.getElementById('scopeSelect').value;

    const customerSelect = document.getElementById('customerSelect');
    const categorySelect = document.getElementById('categorySelect');
    const maxDiscountContainer = document.getElementById('maxDiscountContainer');

    // Customer selection is shown when scope is 2 (Customer Tertentu)
    if (customerSelect) {
        customerSelect.classList.toggle('hidden', scope !== '2');
    }

    // Category selection is shown when scope is 3 (Kategori Tertentu)
    if (categorySelect) {
        categorySelect.classList.toggle('hidden', scope !== '3');
    }

    // Max discount is relevant for percentage discount
    if (maxDiscountContainer) {
        maxDiscountContainer.classList.toggle('hidden', type !== '1');
    }

    // Allow stacking ONLY available for Shipping Discount (type 3)
    const allowStackingContainer = document.getElementById('allowStackingContainer');
    const allowStackingInput = document.getElementById('allowStacking');
    if (allowStackingContainer) {
        const isShipping = type === '3';
        allowStackingContainer.classList.toggle('hidden', !isShipping);
        if (!isShipping && allowStackingInput) {
            allowStackingInput.checked = false;
        }
    }
}

document.getElementById('scopeSelect').addEventListener('change', adjustFormFields);
document.getElementById('typeSelect').addEventListener('change', adjustFormFields);

// Adjust fields on page load
adjustFormFields();

function formatCustomerOption(state) {
    if (!state.id) {
        return state.text;
    }
    const element = state.element;
    if (!element) {
        return state.text;
    }
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

function updateCustomerCount() {
    const selectedCount = $('#customersSelectInput').val()?.length || 0;
    const badge = document.getElementById('customerCountBadge');
    if (badge) {
        badge.textContent = `${selectedCount} customer dipilih`;
    }
}

$(document).ready(function() {
    const $customerSelect = $('#customersSelectInput');
    
    $customerSelect.select2({
        placeholder: "Cari nama, email, atau nomor HP customer...",
        allowClear: true,
        width: '100%',
        templateResult: formatCustomerOption,
        templateSelection: formatCustomerSelection,
        matcher: function(params, data) {
            if ($.trim(params.term) === '') {
                return data;
            }
            if (!data.id) {
                return null;
            }
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
    }).on('change', updateCustomerCount);

    updateCustomerCount();

    $('#selectAllCustomers').on('click', function() {
        const allIds = $customerSelect.find('option').map(function() { return $(this).val(); }).get();
        $customerSelect.val(allIds).trigger('change');
    });

    $('#clearAllCustomers').on('click', function() {
        $customerSelect.val(null).trigger('change');
    });
});
</script>
@endpush

@push('styles')
<style>
/* Select2 Modern Tailwind Styling for Customer Multi-select */
.select2-container--default .select2-selection--multiple {
    border: 1px solid #d1d5db !important;
    border-radius: 0.5rem !important;
    min-height: 44px !important;
    padding: 4px 8px !important;
    background-color: #ffffff !important;
    display: flex !important;
    flex-wrap: wrap !important;
    align-items: center !important;
    gap: 4px !important;
    transition: all 0.2s ease !important;
}

.select2-container--default.select2-container--focus .select2-selection--multiple,
.select2-container--default.select2-container--open .select2-selection--multiple {
    border-color: #2563eb !important;
    box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.15) !important;
    outline: none !important;
}

.select2-container--default .select2-selection--multiple .select2-selection__rendered {
    display: flex !important;
    flex-wrap: wrap !important;
    align-items: center !important;
    gap: 6px !important;
    padding: 0 !important;
    margin: 0 !important;
    width: 100% !important;
}

.select2-container--default .select2-selection--multiple .select2-selection__choice {
    background-color: #eff6ff !important;
    border: 1px solid #bfdbfe !important;
    color: #1d4ed8 !important;
    border-radius: 9999px !important;
    padding: 3px 12px 3px 10px !important;
    margin: 2px 0 !important;
    font-size: 0.8125rem !important;
    font-weight: 500 !important;
    display: inline-flex !important;
    align-items: center !important;
    gap: 6px !important;
    box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05) !important;
}

.select2-container--default .select2-selection--multiple .select2-selection__choice__remove {
    color: #3b82f6 !important;
    font-weight: bold !important;
    margin-right: 2px !important;
    padding: 0 2px !important;
    border: none !important;
    background: transparent !important;
    cursor: pointer !important;
    transition: color 0.15s ease !important;
}

.select2-container--default .select2-selection--multiple .select2-selection__choice__remove:hover {
    color: #dc2626 !important;
    background: transparent !important;
}

.select2-container--default .select2-search--inline .select2-search__field {
    margin: 4px 0 !important;
    font-size: 0.875rem !important;
    font-family: inherit !important;
    color: #1e293b !important;
    padding: 2px 4px !important;
    width: 100% !important;
}

/* Dropdown menu styling */
.select2-dropdown {
    border: 1px solid #e2e8f0 !important;
    border-radius: 0.75rem !important;
    box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.05) !important;
    overflow: hidden !important;
    z-index: 9999 !important;
    margin-top: 4px !important;
    background-color: #ffffff !important;
}

.select2-results__options {
    max-height: 280px !important;
    padding: 6px !important;
}

.select2-container--default .select2-results__option {
    padding: 6px 10px !important;
    border-radius: 0.5rem !important;
    margin-bottom: 2px !important;
    transition: background-color 0.15s ease !important;
    cursor: pointer !important;
}

.select2-container--default .select2-results__option--highlighted[aria-selected] {
    background-color: #f8fafc !important;
    color: #0f172a !important;
}

.select2-container--default .select2-results__option[aria-selected=true] {
    background-color: #eff6ff !important;
    color: #1d4ed8 !important;
}

.select2-container--default .select2-results__option[aria-selected=true] .bg-primary\/10 {
    background-color: #dbeafe !important;
}
</style>
@endpush
@endsection
