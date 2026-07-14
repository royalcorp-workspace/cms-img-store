@extends('layouts.app')

@section('title', 'Shipping Address Rates')

@section('content')
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="font-headline-lg text-headline-lg text-on-surface">Shipping Address Rates</h1>
            <nav class="flex items-center gap-2 text-body-md text-on-surface-variant mt-1">
                <a href="{{ route('dashboard') }}" class="text-primary hover:underline">Dashboard</a>
                <span class="material-symbols-outlined text-[16px]">chevron_right</span>
                <span>Shipping & Payment</span>
                <span class="material-symbols-outlined text-[16px]">chevron_right</span>
                <span>Shipping Address Rates</span>
            </nav>
        </div>
    </div>

    <!-- Location Selector -->
    <div class="bg-white rounded-xl shadow-sm border border-outline-variant/30 p-6 mb-6">
        <div class="flex items-center gap-2 mb-4">
            <span class="material-symbols-outlined text-primary text-[24px]">pin_drop</span>
            <h2 class="font-headline-md text-headline-md text-on-surface font-semibold">Select Location / Sub District</h2>
        </div>
        <div class="w-full md:w-1/2">
            <select id="select-sub-district" class="w-full px-3 py-2 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary/20 focus:outline-none bg-white">
                <option value="">All Sub Districts (Global / Default Rate)</option>
                @foreach($subDistricts as $subDistrict)
                    <option value="{{ $subDistrict->id }}" {{ $subDistrictId == $subDistrict->id ? 'selected' : '' }}>{{ $subDistrict->sub_district }} ({{ $subDistrict->district }} - {{ $subDistrict->province }})</option>
                @endforeach
            </select>
        </div>
    </div>

    <!-- Rates Editor Table -->
    <div class="bg-white rounded-xl shadow-sm border border-outline-variant/30 overflow-hidden">
        <div class="p-4 border-b border-outline-variant flex flex-col sm:flex-row gap-4 justify-between items-center bg-surface-gray">
            <h2 class="font-headline-md text-headline-md text-on-surface font-semibold flex items-center gap-2">
                <span class="material-symbols-outlined text-primary text-[22px]">edit_note</span> 
                Rates for: <span class="text-primary font-bold">{{ $subDistrictId ? 'Selected Sub District' : 'Global / Default' }}</span>
            </h2>
            <div class="flex items-center gap-3">
                <span class="text-label-sm text-on-surface-variant bg-white px-3 py-1.5 rounded-lg border border-outline-variant/30 flex items-center gap-1">
                    <span class="w-2 h-2 rounded-full bg-success"></span> Saves automatically on blur / change
                </span>
                <button type="button" onclick="openGuideModal()" class="flex items-center gap-1.5 px-3 py-1.5 bg-primary/10 hover:bg-primary text-primary hover:text-white rounded-lg text-label-sm font-semibold transition-all border-0 cursor-pointer">
                    <span class="material-symbols-outlined text-[16px]">help</span> Petunjuk
                </button>
            </div>
        </div>
        
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-surface-gray border-b border-outline-variant/30">
                        <th class="px-gutter py-3 font-label-md text-label-md text-on-surface-variant uppercase tracking-wider w-1/4">Courier</th>
                        <th class="px-gutter py-3 font-label-md text-label-md text-on-surface-variant uppercase tracking-wider w-1/4">Service Type</th>
                        <th class="px-gutter py-3 font-label-md text-label-md text-on-surface-variant uppercase tracking-wider w-1/4">Shipping Fee (Rp)</th>
                        <th class="px-gutter py-3 font-label-md text-label-md text-on-surface-variant uppercase tracking-wider w-20 text-center">Active</th>
                        <th class="px-4 py-3 font-label-md text-label-md text-on-surface-variant uppercase tracking-wider w-12 text-center"></th>
                        <th class="px-gutter py-3 font-label-md text-label-md text-on-surface-variant uppercase tracking-wider text-center">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-outline-variant/20">
                    @foreach($courierRates as $rate)
                    <tr class="hover:bg-surface-container/30 transition-colors {{ !$rate['is_active'] || $rate['is_new'] ? 'opacity-60' : '' }}" 
                        data-rate-id="{{ $rate['id'] }}" 
                        data-courier-id="{{ $rate['courier_id'] }}">
                        
                        <!-- Courier Column with Add button -->
                        <td class="px-gutter py-4 font-body-md text-body-md text-on-surface font-semibold">
                            <div class="flex items-center justify-between gap-2 pr-4">
                                <span>{{ $rate['courier_name'] }}</span>
                                <button type="button" 
                                        onclick="addNewServiceRow(this, '{{ $rate['courier_id'] }}', '{{ $rate['courier_name'] }}')"
                                        class="px-2 py-1 bg-primary/10 hover:bg-primary text-primary hover:text-white rounded-lg text-[10px] font-semibold flex items-center gap-1 transition-all cursor-pointer border-0">
                                    <span class="material-symbols-outlined text-[12px] font-bold">add</span> Service
                                </button>
                            </div>
                        </td>

                        <!-- Service Type Dropdown -->
                        <td class="px-gutter py-4">
                            <select class="input-type w-full px-2 py-1.5 border border-outline-variant rounded-lg text-body-md bg-white focus:ring-2 focus:ring-primary/20 focus:outline-none">
                                <option value="1" {{ $rate['type'] == 1 ? 'selected' : '' }}>Regular</option>
                                <option value="2" {{ $rate['type'] == 2 ? 'selected' : '' }}>Express</option>
                                <option value="3" {{ $rate['type'] == 3 ? 'selected' : '' }}>Same Day</option>
                                <option value="4" {{ $rate['type'] == 4 ? 'selected' : '' }}>Instant</option>
                            </select>
                        </td>

                        <!-- Fee Price Input -->
                        <td class="px-gutter py-4">
                            <input type="number" class="input-price w-full px-3 py-1.5 border border-outline-variant rounded-lg text-body-md bg-white focus:ring-2 focus:ring-primary/20 focus:outline-none" 
                                   value="{{ $rate['price'] }}" 
                                   min="0" 
                                   placeholder="enter fee (leave blank to delete)">
                        </td>



                        <!-- Active Status Checkbox -->
                        <td class="px-gutter py-4 text-center">
                            <input type="checkbox" class="input-is-active w-4 h-4 text-primary border-outline-variant rounded focus:ring-primary" {{ $rate['is_active'] ? 'checked' : '' }}>
                        </td>

                        <!-- AJAX Indicator Cell -->
                        <td class="px-4 py-4 text-center status-indicator h-[38px] flex items-center justify-center">
                            @if($rate['id'] && !$rate['is_active'])
                                <span class="material-symbols-outlined text-neutral-400 text-[18px]" title="Inactive">block</span>
                            @endif
                        </td>

                        <!-- Action Button Cell -->
                        <td class="px-gutter py-4 text-center action-cell">
                            @if($rate['id'])
                                <form method="POST" action="{{ route('shipping-addresses.destroy', $rate['id']) }}" class="inline-block" onsubmit="return confirm('Are you sure you want to delete this shipping rate?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-1 text-danger hover:bg-danger/5 rounded transition-all border-0 bg-transparent cursor-pointer">
                                        <span class="material-symbols-outlined text-[20px]">delete</span>
                                    </button>
                                </form>
                            @else
                                <span class="text-label-xs text-on-surface-variant opacity-60">Not Saved</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Guide Modal -->
    <div id="guideModal" class="fixed inset-0 z-[9999] hidden flex items-center justify-center bg-black/50 backdrop-blur-sm transition-all duration-300 opacity-0">
        <div class="bg-white dark:bg-surface-container rounded-2xl max-w-md w-full mx-4 shadow-xl border border-outline-variant/30 overflow-hidden transform scale-95 transition-all duration-300">
            <div class="p-6 border-b border-outline-variant/30 flex justify-between items-center bg-surface-gray">
                <div class="flex items-center gap-2">
                    <span class="material-symbols-outlined text-primary text-[24px]">help</span>
                    <h3 class="font-headline-md text-headline-md text-on-surface font-bold">Petunjuk Pengisian</h3>
                </div>
                <button type="button" onclick="closeGuideModal()" class="text-on-surface-variant hover:text-on-surface p-1 rounded-full hover:bg-surface-container/50 transition-colors border-0 bg-transparent cursor-pointer">
                    <span class="material-symbols-outlined text-[20px]">close</span>
                </button>
            </div>
            <div class="p-6 space-y-4 text-body-md text-on-surface-variant">
                <div class="flex gap-3">
                    <span class="material-symbols-outlined text-success shrink-0 mt-0.5">check_circle</span>
                    <div>
                        <h4 class="font-bold text-on-surface">Penyimpanan Otomatis</h4>
                        <p class="mt-0.5">Perubahan jenis layanan, tarif, atau status aktif akan tersimpan secara otomatis ketika Anda keluar dari kolom input (blur) atau menekan tombol <strong>Enter</strong>.</p>
                    </div>
                </div>
                <div class="flex gap-3">
                    <span class="material-symbols-outlined text-primary shrink-0 mt-0.5">add_circle</span>
                    <div>
                        <h4 class="font-bold text-on-surface">Menambah Layanan Baru</h4>
                        <p class="mt-0.5">Klik tombol <strong>+ Service</strong> di sebelah nama kurir untuk menambah baris baru. Ini memungkinkan Anda mengisi beberapa jenis layanan (seperti Regular & Express) untuk kurir yang sama.</p>
                    </div>
                </div>
                <div class="flex gap-3">
                    <span class="material-symbols-outlined text-danger shrink-0 mt-0.5">delete_sweep</span>
                    <div>
                        <h4 class="font-bold text-on-surface">Menghapus Tarif</h4>
                        <p class="mt-0.5">Untuk menghapus tarif, Anda dapat mengklik tombol sampah di ujung kanan baris, atau <strong>cukup kosongkan angka ongkos kirim</strong> lalu klik di luar kolom input. Baris tersebut akan otomatis terhapus dari sistem.</p>
                    </div>
                </div>
            </div>
            <div class="p-4 border-t border-outline-variant/30 flex justify-end bg-surface-gray">
                <button type="button" onclick="closeGuideModal()" class="px-6 py-2 bg-primary text-white rounded-lg font-label-md hover:opacity-90 transition-all border-0 cursor-pointer">Mengerti</button>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        // Initialize Select2 search for Sub Districts
        $('#select-sub-district').select2({
            placeholder: 'All Sub Districts (Global / Default Rate)',
            allowClear: true
        });

        // Trigger page reload when Sub District changes
        $('#select-sub-district').on('change', function() {
            const subDistrictId = $(this).val() || '';
            window.location.href = `{{ route("shipping-addresses.index") }}?sub_district_id=${subDistrictId}`;
        });

        const tableBody = document.querySelector('tbody');

        // Delegate input price blur event
        tableBody.addEventListener('focusout', function(e) {
            if (e.target.classList.contains('input-price')) {
                saveRateInline(e.target);
            }
        });

        // Delegate enter key down
        tableBody.addEventListener('keydown', function(e) {
            if (e.target.classList.contains('input-price') && e.key === 'Enter') {
                e.preventDefault();
                e.target.blur();
            }
        });

        // Delegate select type / checkbox change event
        tableBody.addEventListener('change', function(e) {
            if (e.target.classList.contains('input-type') || e.target.classList.contains('input-is-active')) {
                saveRateInline(e.target);
            }
        });

        // Delegate delete row button for unsaved rows
        tableBody.addEventListener('click', function(e) {
            const removeBtn = e.target.closest('.btn-remove-row');
            if (removeBtn) {
                const row = removeBtn.closest('tr');
                row.remove();
            }
        });
    });

    // Function to add a new service type row for a courier in the DOM
    function addNewServiceRow(button, courierId, courierName) {
        const clickedRow = button.closest('tr');
        
        const newRow = document.createElement('tr');
        newRow.className = 'hover:bg-surface-container/30 transition-colors opacity-60';
        newRow.setAttribute('data-courier-id', courierId);
        newRow.setAttribute('data-rate-id', ''); // new row starts with no ID
        
        newRow.innerHTML = `
            <td class="px-gutter py-4 font-body-md text-body-md text-on-surface font-semibold">
                <div class="flex items-center justify-between gap-2 pr-4">
                    <span class="opacity-50">${courierName}</span>
                    <span class="text-label-xs bg-primary/10 text-primary px-1.5 py-0.5 rounded font-normal">New</span>
                </div>
            </td>
            <td class="px-gutter py-4">
                <select class="input-type w-full px-2 py-1.5 border border-outline-variant rounded-lg text-body-md bg-white focus:ring-2 focus:ring-primary/20 focus:outline-none">
                    <option value="1">Regular</option>
                    <option value="2">Express</option>
                    <option value="3">Same Day</option>
                    <option value="4">Instant</option>
                </select>
            </td>
            <td class="px-gutter py-4">
                <input type="number" class="input-price w-full px-3 py-1.5 border border-outline-variant rounded-lg text-body-md bg-white focus:ring-2 focus:ring-primary/20 focus:outline-none" placeholder="enter fee to save">
            </td>

            <td class="px-gutter py-4 text-center">
                <input type="checkbox" checked class="input-is-active w-4 h-4 text-primary border-outline-variant rounded focus:ring-primary">
            </td>
            <td class="px-4 py-4 text-center status-indicator h-[38px] flex items-center justify-center">
            </td>
            <td class="px-gutter py-4 text-center action-cell">
                <button type="button" class="btn-remove-row p-1 text-danger hover:bg-danger/5 rounded transition-all border-0 bg-transparent cursor-pointer">
                    <span class="material-symbols-outlined text-[20px]">delete</span>
                </button>
            </td>
        `;
        
        clickedRow.after(newRow);
        
        // Focus on the price input of the new row immediately for speed
        newRow.querySelector('.input-price').focus();
    }

    // Function to submit AJAX request to save rates inline on blur/change
    function saveRateInline(element) {
        const parentRow = element.closest('tr');
        let rateId = parentRow.getAttribute('data-rate-id') || null;
        const courierId = parentRow.getAttribute('data-courier-id');
        const subDistrictId = $('#select-sub-district').val() || null;

        const typeSelect = parentRow.querySelector('.input-type');
        const priceInput = parentRow.querySelector('.input-price');
        const isActiveCheckbox = parentRow.querySelector('.input-is-active');
        const statusIndicator = parentRow.querySelector('.status-indicator');

        const type = typeSelect.value;
        const price = priceInput.value;
        const sortOrder = 0; // automatically set to 0
        const isActive = isActiveCheckbox.checked ? 1 : 0;

        // If it's a new row and price is empty, do nothing
        if (!rateId && (price === null || price === '')) {
            return;
        }

        // Show a micro-spinner
        statusIndicator.innerHTML = `
            <div class="w-4 h-4 border-2 border-primary border-t-transparent rounded-full animate-spin"></div>
        `;

        fetch('{{ route("shipping-addresses.save-inline") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                id: rateId,
                sub_district_id: subDistrictId,
                courier_id: courierId,
                type: type,
                price: price,
                sort_order: sortOrder,
                is_active: isActive
            })
        })
        .then(response => {
            if (!response.ok) {
                return response.json().then(err => { throw err; });
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                if (data.deleted) {
                    parentRow.style.opacity = '0.5';
                    statusIndicator.innerHTML = `
                        <span class="material-symbols-outlined text-danger text-[18px]" title="Deleted (price cleared)">delete_sweep</span>
                    `;
                    parentRow.setAttribute('data-rate-id', '');
                    
                    // Replace action cell back to 'Not Saved' / empty state
                    const actionCell = parentRow.querySelector('.action-cell');
                    if (actionCell) {
                        actionCell.innerHTML = `<span class="text-label-xs text-on-surface-variant opacity-60">Not Saved</span>`;
                    }
                } else {
                    parentRow.style.opacity = isActive ? '1' : '0.6';
                    parentRow.classList.remove('opacity-60');
                    
                    // Save the new ID if it was created
                    if (data.data && data.data.id) {
                        parentRow.setAttribute('data-rate-id', data.data.id);
                        
                        // Replace action cell with standard server-side delete form
                        const actionCell = parentRow.querySelector('.action-cell');
                        if (actionCell) {
                            actionCell.innerHTML = `
                                <form method="POST" action="/shipping-addresses/${data.data.id}" class="inline-block" onsubmit="return confirm('Are you sure you want to delete this shipping rate?')">
                                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                    <input type="hidden" name="_method" value="DELETE">
                                    <button type="submit" class="p-1 text-danger hover:bg-danger/5 rounded transition-all border-0 bg-transparent cursor-pointer">
                                        <span class="material-symbols-outlined text-[20px]">delete</span>
                                    </button>
                                </form>
                            `;
                        }
                    }
                    
                    // Show a brief checkmark icon and then fade it out
                    statusIndicator.innerHTML = `
                        <span class="material-symbols-outlined text-success text-[18px]">check_circle</span>
                    `;
                    setTimeout(() => {
                        statusIndicator.innerHTML = '';
                    }, 2000);
                }
            } else {
                statusIndicator.innerHTML = `
                    <span class="material-symbols-outlined text-danger text-[18px]" title="Error: ${data.message}">error</span>
                `;
            }
        })
        .catch(error => {
            console.error('Error saving inline rate:', error);
            let errorMsg = 'Error saving rate. Please check input.';
            if (error && error.message) {
                errorMsg = error.message;
            }
            statusIndicator.innerHTML = `
                <span class="material-symbols-outlined text-danger text-[18px]" title="${errorMsg}">error</span>
            `;
        });
    }

    // Guide Modal Functions
    function openGuideModal() {
        const modal = document.getElementById('guideModal');
        modal.classList.remove('hidden');
        setTimeout(() => {
            modal.classList.remove('opacity-0');
            modal.querySelector('.transform').classList.remove('scale-95');
        }, 10);
    }

    function closeGuideModal() {
        const modal = document.getElementById('guideModal');
        modal.classList.add('opacity-0');
        modal.querySelector('.transform').classList.add('scale-95');
        setTimeout(() => {
            modal.classList.add('hidden');
        }, 300);
    }
</script>
@endpush
