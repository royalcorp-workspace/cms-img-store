@extends('layouts.app')

@section('title', 'Create Voucher')

@section('content')
<div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
    <div>
        <h1 class="font-headline-lg text-headline-lg text-on-surface">Create Voucher</h1>
        <nav class="flex items-center gap-2 text-body-md text-on-surface-variant mt-1">
            <a href="{{ route('dashboard') }}" class="text-primary hover:underline">Dashboard</a>
            <span class="material-symbols-outlined text-[16px]">chevron_right</span>
            <a href="{{ route('vouchers.index') }}" class="text-primary hover:underline">Vouchers</a>
            <span class="material-symbols-outlined text-[16px]">chevron_right</span>
            <span>Create</span>
        </nav>
    </div>
    <a href="{{ route('vouchers.index') }}" class="flex items-center gap-2 px-4 py-2 bg-surface-container-lowest border border-outline-variant text-secondary rounded-lg font-label-md hover:bg-surface-container transition-all">
        <span class="material-symbols-outlined text-[18px]">arrow_back</span> Back
    </a>
</div>

<div class="bg-white rounded-xl shadow-sm border border-outline-variant/30">
    <div class="p-6">
        <form id="voucherForm" method="POST" action="{{ route('vouchers.store') }}">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="space-y-1.5">
                    <label class="block text-label-sm font-medium text-on-surface-variant">Voucher Code <span class="text-danger">*</span> <span class="inline-flex items-center cursor-help text-on-surface-variant relative group"><span class="material-symbols-outlined text-[18px]">info</span><span class="absolute right-0 top-full mt-2 w-80 bg-surface-container-highest rounded-lg shadow-lg border border-outline-variant p-4 text-body-xs text-on-surface-variant hidden group-hover:block z-50">Kode unik voucher yang akan digunakan oleh customer saat checkout transaksi.</span></span></label>
                    <input type="text" name="code" class="w-full px-3 py-2 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary/20 focus:outline-none" placeholder="e.g., SUMMER2024" required>
                    @error('code')<p class="text-danger text-sm">{{ $message }}</p>@enderror
                </div>
                <div class="space-y-1.5">
                    <label class="block text-label-sm font-medium text-on-surface-variant">Title <span class="inline-flex items-center cursor-help text-on-surface-variant relative group"><span class="material-symbols-outlined text-[18px]">info</span><span class="absolute right-0 top-full mt-2 w-80 bg-surface-container-highest rounded-lg shadow-lg border border-outline-variant p-4 text-body-xs text-on-surface-variant hidden group-hover:block z-50">Nama/internal label voucher untuk keperluan admin, tidak ditampilkan ke customer.</span></span></label>
                    <input type="text" name="title" class="w-full px-3 py-2 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary/20 focus:outline-none" placeholder="Voucher title" value="{{ old('title') }}">
                    @error('title')<p class="text-danger text-sm">{{ $message }}</p>@enderror
                </div>
            </div>

            <div class="space-y-1.5 mt-4">
                <label class="block text-label-sm font-medium text-on-surface-variant">Description <span class="inline-flex items-center cursor-help text-on-surface-variant relative group"><span class="material-symbols-outlined text-[18px]">info</span><span class="absolute right-0 top-full mt-2 w-80 bg-surface-container-highest rounded-lg shadow-lg border border-outline-variant p-4 text-body-xs text-on-surface-variant hidden group-hover:block z-50">Keterangan detail syarat & ketentuan penggunaan voucher. Bisa ditampilkan di halaman checkout.</span></span></label>
                <textarea name="description" rows="2" class="w-full px-3 py-2 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary/20 focus:outline-none" placeholder="Voucher description">{{ old('description') }}</textarea>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                <div class="space-y-1.5">
                    <label class="block text-label-sm font-medium text-on-surface-variant">Discount Type <span class="text-danger">*</span> <span class="inline-flex items-center cursor-help text-on-surface-variant relative group"><span class="material-symbols-outlined text-[18px]">info</span><span class="absolute right-0 top-full mt-2 w-80 bg-surface-container-highest rounded-lg shadow-lg border border-outline-variant p-4 text-body-xs text-on-surface-variant hidden group-hover:block z-50">Tipe diskon yang diberikan: persentase (%), nominal tetap (Rp), potongan ongkir (Rp), atau bonus produk (pcs).</span></span></label>
                    <select name="type" id="typeSelect" class="w-full px-3 py-2 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary/20 focus:outline-none">
                        <option value="1">Percentage (%)</option>
                        <option value="2">Fixed Amount (Rp)</option>
                        <option value="3">Shipping Discount (Rp)</option>
                        <option value="4">Bonus Product (pcs)</option>
                    </select>
                </div>
                <div class="space-y-1.5">
                    <label class="block text-label-sm font-medium text-on-surface-variant">Value <span class="text-danger">*</span> <span class="inline-flex items-center cursor-help text-on-surface-variant relative group"><span class="material-symbols-outlined text-[18px]">info</span><span class="absolute right-0 top-full mt-2 w-80 bg-surface-container-highest rounded-lg shadow-lg border border-outline-variant p-4 text-body-xs text-on-surface-variant hidden group-hover:block z-50">Nilai diskon sesuai tipe yang dipilih: masukkan persentase (%), nominal Rp, biaya ongkir, atau jumlah pcs bonus.</span></span></label>
                    <input type="number" name="value" step="0.01" class="w-full px-3 py-2 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary/20 focus:outline-none" placeholder="0" required>
                    @error('value')<p class="text-danger text-sm">{{ $message }}</p>@enderror
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                <div class="space-y-1.5">
                    <label class="block text-label-sm font-medium text-on-surface-variant">Min Purchase (Rp) <span class="inline-flex items-center cursor-help text-on-surface-variant relative group"><span class="material-symbols-outlined text-[18px]">info</span><span class="absolute right-0 top-full mt-2 w-80 bg-surface-container-highest rounded-lg shadow-lg border border-outline-variant p-4 text-body-xs text-on-surface-variant hidden group-hover:block z-50">Total belanja minimum agar voucher bisa digunakan. Isi 0 jika tidak ada minimum.</span></span></label>
                    <input type="number" name="min_purchase" step="0.01" class="w-full px-3 py-2 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary/20 focus:outline-none" placeholder="0" value="{{ old('min_purchase') }}">
                </div>
                <div class="space-y-1.5" id="maxDiscountContainer">
                    <label class="block text-label-sm font-medium text-on-surface-variant">Max Discount (Rp) <span class="inline-flex items-center cursor-help text-on-surface-variant relative group"><span class="material-symbols-outlined text-[18px]">info</span><span class="absolute right-0 top-full mt-2 w-80 bg-surface-container-highest rounded-lg shadow-lg border border-outline-variant p-4 text-body-xs text-on-surface-variant hidden group-hover:block z-50">Batas maksimum diskon yang diberikan (khusus tipe persentase). Isi 0 jika tidak ada batas.</span></span></label>
                    <input type="number" name="max_discount" step="0.01" class="w-full px-3 py-2 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary/20 focus:outline-none" placeholder="0" value="{{ old('max_discount') }}">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                <div class="space-y-1.5">
                    <label class="block text-label-sm font-medium text-on-surface-variant">Start Date <span class="inline-flex items-center cursor-help text-on-surface-variant relative group"><span class="material-symbols-outlined text-[18px]">info</span><span class="absolute right-0 top-full mt-2 w-80 bg-surface-container-highest rounded-lg shadow-lg border border-outline-variant p-4 text-body-xs text-on-surface-variant hidden group-hover:block z-50">Tanggal mulai voucher aktif. Kosongkan jika voucher langsung aktif setelah dibuat.</span></span></label>
                    <input type="date" name="start_date" class="w-full px-3 py-2 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary/20 focus:outline-none" value="{{ old('start_date') }}">
                </div>
                <div class="space-y-1.5">
                    <label class="block text-label-sm font-medium text-on-surface-variant">End Date <span class="inline-flex items-center cursor-help text-on-surface-variant relative group"><span class="material-symbols-outlined text-[18px]">info</span><span class="absolute right-0 top-full mt-2 w-80 bg-surface-container-highest rounded-lg shadow-lg border border-outline-variant p-4 text-body-xs text-on-surface-variant hidden group-hover:block z-50">Tanggal kadaluarsa voucher. Kosongkan jika tidak ada batas waktu (voucher berlaku terus menerus).</span></span></label>
                    <input type="date" name="end_date" class="w-full px-3 py-2 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary/20 focus:outline-none" value="{{ old('end_date') }}">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                <div class="space-y-1.5">
                    <label class="block text-label-sm font-medium text-on-surface-variant">Usage Limit <span class="inline-flex items-center cursor-help text-on-surface-variant relative group"><span class="material-symbols-outlined text-[18px]">info</span><span class="absolute right-0 top-full mt-2 w-80 bg-surface-container-highest rounded-lg shadow-lg border border-outline-variant p-4 text-body-xs text-on-surface-variant hidden group-hover:block z-50">Banyaknya voucher ini bisa ditukarkan secara keseluruhan oleh semua customer. Isi 0 jika unlimited.</span></span></label>
                    <input type="number" name="usage_limit" class="w-full px-3 py-2 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary/20 focus:outline-none" placeholder="0 = unlimited" value="{{ old('usage_limit') }}">
                </div>
                <div class="space-y-1.5">
                    <label class="block text-label-sm font-medium text-on-surface-variant">Per User Limit <span class="inline-flex items-center cursor-help text-on-surface-variant relative group"><span class="material-symbols-outlined text-[18px]">info</span><span class="absolute right-0 top-full mt-2 w-80 bg-surface-container-highest rounded-lg shadow-lg border border-outline-variant p-4 text-body-xs text-on-surface-variant hidden group-hover:block z-50">Batas pemakaian voucher per satu akun customer. Isi 0 jika unlimited per user.</span></span></label>
                    <input type="number" name="usage_limit_per_user" class="w-full px-3 py-2 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary/20 focus:outline-none" placeholder="0 = unlimited" value="{{ old('usage_limit_per_user') }}">
                </div>
            </div>

            <div class="space-y-1.5 mt-4">
                <label class="block text-label-sm font-medium text-on-surface-variant">Scope <span class="text-danger">*</span> <span class="inline-flex items-center cursor-help text-on-surface-variant relative group"><span class="material-symbols-outlined text-[18px]">info</span><span class="absolute right-0 top-full mt-2 w-80 bg-surface-container-highest rounded-lg shadow-lg border border-outline-variant p-4 text-body-xs text-on-surface-variant hidden group-hover:block z-50">Jenis produk yang bisa menggunakan voucher ini. Pilih 'Semua Produk' untuk voucher umum, atau batasi pada produk/kategori tertentu.</span></span></label>
                <select name="scope" id="scopeSelect" class="w-full px-3 py-2 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary/20 focus:outline-none">
                    <option value="1">Semua Produk</option>
                    <option value="2">Produk Tertentu</option>
                    <option value="3">Kategori Tertentu</option>
                </select>
            </div>

            <div id="productSelect" class="space-y-1.5 mt-4 hidden">
                <label id="productSelectLabel" class="block text-label-sm font-medium text-on-surface-variant">Products</label>
                <select name="product_ids[]" id="productsSelectInput" multiple class="select2 w-full px-3 py-2 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary/20 focus:outline-none bg-white">
                    @foreach(\App\Models\Product\Product::all() as $p)
                        <option value="{{ $p->id }}">{{ $p->name }}</option>
                    @endforeach
                </select>
            </div>

            <div id="categorySelect" class="space-y-1.5 mt-4 hidden">
                <label class="block text-label-sm font-medium text-on-surface-variant">Categories</label>
                <select name="category_ids[]" multiple class="w-full px-3 py-2 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary/20 focus:outline-none bg-white h-32">
                    @foreach(\App\Models\Product\Category::all() as $c)
                        <option value="{{ $c->id }}">{{ $c->name }}</option>
                    @endforeach
                </select>
                <p class="text-label-sm text-on-surface-variant">Hold Ctrl/Cmd to select multiple</p>
            </div>

            <div class="flex items-center gap-2 mt-4">
                <input type="checkbox" name="allow_stacking" id="allowStacking" class="w-4 h-4 rounded border-outline-variant text-primary focus:ring-primary" value="1">
                <label for="allowStacking" class="text-label-sm font-medium text-on-surface-variant">Allow Stacking (bisa dipakai bersamaan dengan voucher lain) <span class="inline-flex items-center cursor-help text-on-surface-variant relative group"><span class="material-symbols-outlined text-[18px]">info</span><span class="absolute right-0 top-full mt-2 w-80 bg-surface-container-highest rounded-lg shadow-lg border border-outline-variant p-4 text-body-xs text-on-surface-variant hidden group-hover:block z-50">Jika dicentang, voucher ini bisa digabung dengan voucher lain dalam satu transaksi. Jika tidak, hanya satu voucher yang berlaku per transaksi.</span></span></label>
            </div>

            <div class="flex items-center gap-2 mt-4">
                <input type="checkbox" name="valid_for_new_customer" id="validForNewCustomer" class="w-4 h-4 rounded border-outline-variant text-primary focus:ring-primary" value="1">
                <label for="validForNewCustomer" class="text-label-sm font-medium text-on-surface-variant">Valid for New Customer Only <span class="inline-flex items-center cursor-help text-on-surface-variant relative group"><span class="material-symbols-outlined text-[18px]">info</span><span class="absolute right-0 top-full mt-2 w-80 bg-surface-container-highest rounded-lg shadow-lg border border-outline-variant p-4 text-body-xs text-on-surface-variant hidden group-hover:block z-50">Jika dicentang, voucher hanya bisa digunakan oleh customer yang pertama kali bertransaksi di toko.</span></span></label>
            </div>

            <div class="space-y-1.5 mt-4">
                <label class="block text-label-sm font-medium text-on-surface-variant">Status <span class="inline-flex items-center cursor-help text-on-surface-variant relative group"><span class="material-symbols-outlined text-[18px]">info</span><span class="absolute right-0 top-full mt-2 w-80 bg-surface-container-highest rounded-lg shadow-lg border border-outline-variant p-4 text-body-xs text-on-surface-variant hidden group-hover:block z-50">Set Active agar voucher dapat digunakan di checkout. Pilih Inactive untuk menonaktifkan sementara tanpa menghapus data.</span></span></label>
                <select name="is_active" class="w-full px-3 py-2 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary/20 focus:outline-none">
                    <option value="1">Active</option>
                    <option value="0">Inactive</option>
                </select>
            </div>

            <hr class="my-6 border-outline-variant">
        </form>
    </div>
</div>

<div id="infoModal" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center">
    <div class="bg-white rounded-xl shadow-lg border border-outline-variant w-full max-w-sm mx-4">
        <div class="p-6 border-b border-outline-variant flex items-center gap-3">
            <span class="material-symbols-outlined text-primary text-[24px]">info</span>
            <h3 id="infoModalTitle" class="font-headline-md text-headline-md text-on-surface">Information</h3>
        </div>
        <div class="p-6">
            <p id="infoModalBody" class="text-body-md text-on-surface-variant"></p>
        </div>
        <div class="p-6 border-t border-outline-variant flex justify-end">
            <button type="button" onclick="closeInfoModal()" class="px-4 py-2 bg-primary text-white rounded-lg font-label-md hover:opacity-90 transition-all shadow-sm">Got it</button>
        </div>
    </div>
</div>
@endsection

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
    const scopeSelect = document.getElementById('scopeSelect');
    const scope = scopeSelect.value;

    const productSelect = document.getElementById('productSelect');
    const productSelectLabel = document.getElementById('productSelectLabel');
    const categorySelect = document.getElementById('categorySelect');
    const maxDiscountContainer = document.getElementById('maxDiscountContainer');
    
    // Get scope option for value 2
    const optionScopeSpecificProduct = scopeSelect.querySelector('option[value="2"]');

    if (type === '4') { // Bonus Product
        // Disable and hide scope 2 (Produk Tertentu) to avoid pivot collision
        if (optionScopeSpecificProduct) {
            optionScopeSpecificProduct.disabled = true;
            if (scope === '2') {
                scopeSelect.value = '1';
            }
        }
        
        // Max discount is irrelevant for bonus products
        if (maxDiscountContainer) maxDiscountContainer.classList.add('hidden');
        
        // Show Products select box as "Bonus Products"
        if (productSelect) productSelect.classList.remove('hidden');
        if (productSelectLabel) productSelectLabel.textContent = 'Bonus Products';
        
        // Show/hide category select based on scope (scope 3 is eligible categories)
        if (categorySelect) {
            categorySelect.classList.toggle('hidden', scopeSelect.value !== '3');
        }
    } else {
        // Enable scope 2
        if (optionScopeSpecificProduct) {
            optionScopeSpecificProduct.disabled = false;
        }
        
        // Show Max Discount for other types (especially percentage)
        if (maxDiscountContainer) maxDiscountContainer.classList.remove('hidden');
        
        // Restore label
        if (productSelectLabel) productSelectLabel.textContent = 'Products';
        
        // Normal scope visibility
        if (productSelect) {
            productSelect.classList.toggle('hidden', scope !== '2');
        }
        if (categorySelect) {
            categorySelect.classList.toggle('hidden', scope !== '3');
        }
    }
}

document.getElementById('scopeSelect').addEventListener('change', adjustFormFields);
document.getElementById('typeSelect').addEventListener('change', adjustFormFields);

// Adjust fields on page load
adjustFormFields();

$(document).ready(function() {
    $('#productsSelectInput').select2({
        placeholder: "Select products...",
        allowClear: true,
        width: '100%',
        theme: 'classic'
    });
});
</script>
@endpush
