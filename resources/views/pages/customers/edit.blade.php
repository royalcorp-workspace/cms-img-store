@extends('layouts.app')

@section('title', 'Edit Customer - ' . $customer->name)

@section('content')
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="font-headline-lg text-headline-lg text-on-surface">Edit Customer</h1>
            <nav class="flex items-center gap-2 text-body-md text-on-surface-variant mt-1">
                <a href="{{ route('dashboard') }}" class="text-primary hover:underline">Dashboard</a>
                <span class="material-symbols-outlined text-[16px]">chevron_right</span>
                <a href="{{ route('customers.index') }}" class="text-primary hover:underline">Customers</a>
                <span class="material-symbols-outlined text-[16px]">chevron_right</span>
                <span>{{ $customer->name }}</span>
                <span class="material-symbols-outlined text-[16px]">chevron_right</span>
                <span>Edit</span>
            </nav>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('customers.show', $customer->id) }}" class="flex items-center gap-2 px-4 py-2 bg-surface-container-lowest border border-outline-variant text-secondary rounded-lg font-label-md hover:bg-surface-container transition-all">
                <span class="material-symbols-outlined text-[18px]">history</span> Lihat Riwayat
            </a>
            <a href="{{ route('customers.index') }}" class="flex items-center gap-2 px-4 py-2 bg-surface-container-lowest border border-outline-variant text-secondary rounded-lg font-label-md hover:bg-surface-container transition-all">
                <span class="material-symbols-outlined text-[18px]">arrow_back</span> Kembali
            </a>
        </div>
    </div>

    @include('layouts.partials.customer-submenu')

    @if(session('success'))
        <div class="mb-6 p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl flex items-center gap-2 text-body-md">
            <span class="material-symbols-outlined text-emerald-600 text-[20px]">check_circle</span>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if(session('error'))
        <div class="mb-6 p-4 bg-rose-50 border border-rose-200 text-rose-800 rounded-xl flex items-center gap-2 text-body-md">
            <span class="material-symbols-outlined text-rose-600 text-[20px]">error</span>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    <div class="space-y-6">
        <!-- 1. Form Informasi Customer -->
        <div class="bg-white rounded-xl shadow-sm border border-outline-variant/30 overflow-hidden">
            <div class="p-5 border-b border-outline-variant/30 flex items-center gap-2 bg-surface-gray">
                <span class="material-symbols-outlined text-primary text-[22px]">person</span>
                <h2 class="font-headline-md text-headline-md text-on-surface font-bold">Informasi Pelanggan</h2>
            </div>
            <div class="p-6">
                <form method="POST" action="{{ route('customers.update', $customer->id) }}">
                    @csrf 
                    @method('PUT')
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="space-y-1.5">
                            <label class="block text-label-sm font-medium text-on-surface-variant">Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" value="{{ old('name', $customer->name) }}" class="w-full px-3 py-2 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary/20 focus:outline-none" placeholder="Customer name" required>
                            @error('name')<p class="text-danger text-sm">{{ $message }}</p>@enderror
                        </div>
                        <div class="space-y-1.5">
                            <label class="block text-label-sm font-medium text-on-surface-variant">Email <span class="text-danger">*</span></label>
                            <input type="email" name="email" value="{{ old('email', $customer->email) }}" class="w-full px-3 py-2 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary/20 focus:outline-none" placeholder="email@example.com" required>
                            @error('email')<p class="text-danger text-sm">{{ $message }}</p>@enderror
                        </div>
                        <div class="space-y-1.5">
                            <label class="block text-label-sm font-medium text-on-surface-variant">Phone</label>
                            <input type="text" name="phone" value="{{ old('phone', $customer->phone) }}" class="w-full px-3 py-2 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary/20 focus:outline-none" placeholder="+62 xxx">
                            @error('phone')<p class="text-danger text-sm">{{ $message }}</p>@enderror
                        </div>
                        <div class="space-y-1.5">
                            <label class="block text-label-sm font-medium text-on-surface-variant">User (optional)</label>
                            <select name="user_id" class="w-full px-3 py-2 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary/20 focus:outline-none select2-enable">
                                <option value="">None</option>
                                @foreach(\App\Models\User::all() as $user)
                                    <option value="{{ $user->id }}" {{ old('user_id', $customer->user_id) == $user->id ? 'selected' : '' }}>{{ $user->name }} ({{ $user->email }})</option>
                                @endforeach
                            </select>
                            @error('user_id')<p class="text-danger text-sm">{{ $message }}</p>@enderror
                        </div>
                        <div class="space-y-1.5">
                            <label class="block text-label-sm font-medium text-on-surface-variant">Customer Code</label>
                            <input type="text" name="customer_code" value="{{ old('customer_code', $customer->customer_code) }}" class="w-full px-3 py-2 border border-outline-variant rounded-lg font-mono font-bold focus:ring-2 focus:ring-primary/20 focus:outline-none uppercase" placeholder="CUST.YYYYMMDD.xxxx">
                            @error('customer_code')<p class="text-danger text-sm">{{ $message }}</p>@enderror
                        </div>
                        <div class="space-y-1.5">
                            <label class="block text-label-sm font-medium text-on-surface-variant">Gender</label>
                            <select name="gender" class="w-full px-3 py-2 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary/20 focus:outline-none bg-white">
                                <option value="">Pilih Gender</option>
                                <option value="male" {{ old('gender', $customer->gender) == 'male' ? 'selected' : '' }}>Laki-laki (Male)</option>
                                <option value="female" {{ old('gender', $customer->gender) == 'female' ? 'selected' : '' }}>Perempuan (Female)</option>
                            </select>
                            @error('gender')<p class="text-danger text-sm">{{ $message }}</p>@enderror
                        </div>
                        <div class="space-y-1.5">
                            <label class="block text-label-sm font-medium text-on-surface-variant">Tanggal Lahir</label>
                            <input type="date" name="birth_date" value="{{ old('birth_date', $customer->birth_date?->format('Y-m-d')) }}" class="w-full px-3 py-2 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary/20 focus:outline-none">
                            @error('birth_date')<p class="text-danger text-sm">{{ $message }}</p>@enderror
                        </div>
                    </div>
                    <hr class="my-6 border-outline-variant/30">
                    <div class="flex justify-end gap-3">
                        <a href="{{ route('customers.index') }}" class="px-5 py-2 border border-outline-variant text-on-surface-variant rounded-lg font-label-md hover:bg-surface-container transition-colors">Batal</a>
                        <button type="submit" class="px-5 py-2 bg-primary text-white rounded-lg font-label-md hover:opacity-90 transition-all shadow-sm">Update Customer</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- 2. Section Daftar Alamat Pelanggan -->
        <div class="bg-white rounded-xl shadow-sm border border-outline-variant/30 overflow-hidden">
            <div class="p-5 border-b border-outline-variant/30 flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-surface-gray">
                <div class="flex items-center gap-2.5">
                    <span class="material-symbols-outlined text-primary text-[24px]">home_pin</span>
                    <h2 class="font-headline-md text-headline-md text-on-surface font-bold">Daftar Alamat Pelanggan</h2>
                    <span class="px-2.5 py-0.5 text-xs font-semibold rounded-full bg-primary/10 text-primary border border-primary/20">
                        {{ $addresses->count() }} Alamat
                    </span>
                </div>
                <button type="button" onclick="openAddAddressModal()" class="inline-flex items-center gap-2 px-4 py-2 bg-primary text-white rounded-lg font-label-md hover:opacity-90 transition-all shadow-sm cursor-pointer">
                    <span class="material-symbols-outlined text-[18px]">add</span> Tambah Alamat
                </button>
            </div>

            <div class="p-6">
                @if($addresses->isEmpty())
                    <div class="py-12 text-center">
                        <div class="w-16 h-16 mx-auto mb-3 rounded-full bg-surface-container-low flex items-center justify-center text-on-surface-variant">
                            <span class="material-symbols-outlined text-3xl">location_off</span>
                        </div>
                        <p class="font-headline-sm text-on-surface font-semibold mb-1">Belum Ada Alamat Tersimpan</p>
                        <p class="text-body-sm text-on-surface-variant mb-4">Customer ini belum memiliki alamat pengiriman yang terdaftar.</p>
                        <button type="button" onclick="openAddAddressModal()" class="inline-flex items-center gap-2 px-4 py-2 bg-primary text-white rounded-lg font-label-md hover:opacity-90 transition-all shadow-sm cursor-pointer">
                            <span class="material-symbols-outlined text-[18px]">add_location</span> Tambah Alamat Pertama
                        </button>
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="border-b border-outline-variant/30 bg-surface-container-low/40">
                                    <th class="px-4 py-3 text-label-sm font-bold text-on-surface uppercase tracking-wider">Label</th>
                                    <th class="px-4 py-3 text-label-sm font-bold text-on-surface uppercase tracking-wider">Penerima & Kontak</th>
                                    <th class="px-4 py-3 text-label-sm font-bold text-on-surface uppercase tracking-wider">Alamat Lengkap</th>
                                    <th class="px-4 py-3 text-label-sm font-bold text-on-surface uppercase tracking-wider">Status</th>
                                    <th class="px-4 py-3 text-label-sm font-bold text-on-surface uppercase tracking-wider text-right w-[120px]">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-outline-variant/30">
                                @foreach($addresses as $address)
                                    <tr class="hover:bg-surface-container-low/30 transition-colors">
                                        <td class="px-4 py-4 align-top">
                                            <div class="flex items-center gap-1.5">
                                                <span class="font-bold text-on-surface text-body-md">{{ $address->label }}</span>
                                            </div>
                                        </td>
                                        <td class="px-4 py-4 align-top">
                                            <div class="font-medium text-on-surface text-body-md">{{ $address->recipient_name }}</div>
                                            <div class="text-body-sm text-on-surface-variant mt-0.5 flex items-center gap-1">
                                                <span class="material-symbols-outlined text-[15px]">phone</span>
                                                <span>{{ $address->phone }}</span>
                                            </div>
                                        </td>
                                        <td class="px-4 py-4 align-top">
                                            <p class="text-body-md text-on-surface">{{ $address->address }}</p>
                                            <p class="text-body-sm text-on-surface-variant mt-1">
                                                {{ $address->subDistrict ? $address->subDistrict->sub_district : '-' }}, 
                                                {{ $address->city ? $address->city->name : '-' }}
                                                @if($address->postal_code)
                                                    <span class="text-on-surface-variant font-mono">({{ $address->postal_code }})</span>
                                                @endif
                                            </p>
                                        </td>
                                        <td class="px-4 py-4 align-top">
                                            @if($address->is_primary)
                                                <span class="inline-flex items-center gap-1 px-2.5 py-1 bg-emerald-50 text-emerald-700 border border-emerald-200 text-xs font-bold rounded-full">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-600"></span> Alamat Utama
                                                </span>
                                            @else
                                                <span class="text-on-surface-variant text-xs font-medium">Opsional</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-4 align-top text-right space-x-1.5">
                                            <button type="button" 
                                                    onclick='openEditAddressModal(@json($address))' 
                                                    class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-primary/10 text-primary hover:bg-primary hover:text-white transition-colors cursor-pointer" 
                                                    title="Edit Alamat">
                                                <span class="material-symbols-outlined text-[18px]">edit</span>
                                            </button>
                                            <form action="{{ route('customers.addresses.destroy', [$customer->id, $address->id]) }}" method="POST" class="inline-block" onsubmit="return confirm('Apakah Anda yakin ingin menghapus alamat ini?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" 
                                                        class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-danger/10 text-danger hover:bg-danger hover:text-white transition-colors cursor-pointer" 
                                                        title="Hapus Alamat">
                                                    <span class="material-symbols-outlined text-[18px]">delete</span>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- 3. Modal Tambah / Edit Alamat Pelanggan -->
    <div id="addressModal" class="fixed inset-0 z-[9999] hidden flex items-center justify-center bg-black/50 backdrop-blur-sm transition-all duration-300 opacity-0">
        <div class="bg-white rounded-2xl max-w-xl w-full mx-4 shadow-2xl border border-outline-variant/30 overflow-hidden transform scale-95 transition-all duration-300 max-h-[90vh] flex flex-col">
            <form id="addressForm" method="POST" action="{{ route('customers.addresses.store', $customer->id) }}" class="flex flex-col h-full overflow-hidden">
                @csrf
                <input type="hidden" name="_method" id="addressModalMethod" value="POST">

                <div class="p-5 border-b border-outline-variant/30 flex justify-between items-center bg-surface-gray shrink-0">
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-outlined text-primary text-[24px]">location_on</span>
                        <h3 id="addressModalTitle" class="font-headline-md text-headline-md text-on-surface font-bold">Tambah Alamat Baru</h3>
                    </div>
                    <button type="button" onclick="closeAddressModal()" class="text-on-surface-variant hover:text-on-surface p-1 rounded-full hover:bg-surface-container/50 transition-colors border-0 bg-transparent cursor-pointer">
                        <span class="material-symbols-outlined text-[20px]">close</span>
                    </button>
                </div>

                <div class="p-6 space-y-4 overflow-y-auto">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <!-- Label Alamat -->
                        <div class="space-y-1.5">
                            <label class="block text-label-sm font-medium text-on-surface-variant">Label Alamat <span class="text-danger">*</span></label>
                            <input type="text" name="label" id="modalLabel" class="w-full px-3 py-2 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary/20 focus:outline-none" placeholder="Contoh: Rumah, Kantor, Toko" required>
                        </div>

                        <!-- Jadikan Alamat Utama -->
                        <div class="space-y-1.5 flex items-end pb-2">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" name="is_primary" id="modalIsPrimary" value="1" class="w-4 h-4 text-primary rounded border-outline-variant focus:ring-primary">
                                <span class="text-label-md font-medium text-on-surface">Jadikan Alamat Utama</span>
                            </label>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <!-- Nama Penerima -->
                        <div class="space-y-1.5">
                            <label class="block text-label-sm font-medium text-on-surface-variant">Nama Penerima <span class="text-danger">*</span></label>
                            <input type="text" name="recipient_name" id="modalRecipientName" value="{{ $customer->name }}" class="w-full px-3 py-2 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary/20 focus:outline-none" required>
                        </div>

                        <!-- Nomor Telepon -->
                        <div class="space-y-1.5">
                            <label class="block text-label-sm font-medium text-on-surface-variant">Nomor Telepon <span class="text-danger">*</span></label>
                            <input type="text" name="phone" id="modalPhone" value="{{ $customer->phone }}" class="w-full px-3 py-2 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary/20 focus:outline-none" required>
                        </div>
                    </div>

                    <!-- Kecamatan & Kota -->
                    <div class="space-y-1.5">
                        <label class="block text-label-sm font-medium text-on-surface-variant">Kecamatan & Kota <span class="text-danger">*</span></label>
                        <select name="sub_district_id" id="modalSubDistrictId" class="w-full px-3 py-2 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary/20 focus:outline-none bg-white" required>
                            <option value="">-- Pilih Kecamatan / Kota --</option>
                            @foreach($subDistricts ?? [] as $sub)
                                <option value="{{ $sub->id }}">{{ $sub->sub_district }}, {{ $sub->city->name ?? '' }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <!-- Alamat Lengkap -->
                        <div class="space-y-1.5 sm:col-span-2">
                            <label class="block text-label-sm font-medium text-on-surface-variant">Alamat Lengkap <span class="text-danger">*</span></label>
                            <textarea name="address" id="modalAddress" rows="3" class="w-full px-3 py-2 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary/20 focus:outline-none" placeholder="Nama jalan, nomor rumah, RT/RW, gedung, dsb." required></textarea>
                        </div>

                        <!-- Kode Pos -->
                        <div class="space-y-1.5 sm:col-span-1">
                            <label class="block text-label-sm font-medium text-on-surface-variant">Kode Pos</label>
                            <input type="text" name="postal_code" id="modalPostalCode" class="w-full px-3 py-2 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary/20 focus:outline-none" placeholder="Contoh: 12345">
                        </div>
                    </div>
                </div>

                <div class="p-4 border-t border-outline-variant/30 flex justify-end gap-2 bg-surface-gray shrink-0">
                    <button type="button" onclick="closeAddressModal()" class="px-4 py-2 bg-surface-container-lowest border border-outline-variant text-secondary rounded-lg font-label-md hover:bg-surface-container transition-all cursor-pointer">
                        Batal
                    </button>
                    <button type="submit" id="modalSubmitBtn" class="px-5 py-2 bg-primary text-white rounded-lg font-label-md hover:opacity-90 transition-all cursor-pointer shadow-sm">
                        Simpan Alamat
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        if (typeof $ !== 'undefined' && $.fn.select2) {
            $('#modalSubDistrictId').select2({
                placeholder: '-- Pilih Kecamatan / Kota --',
                allowClear: true,
                dropdownParent: $('#addressModal')
            });
        }
    });

    function openAddAddressModal() {
        document.getElementById('addressModalTitle').textContent = 'Tambah Alamat Baru';
        document.getElementById('addressForm').action = '{{ route("customers.addresses.store", $customer->id) }}';
        document.getElementById('addressModalMethod').value = 'POST';
        document.getElementById('modalSubmitBtn').textContent = 'Simpan Alamat';

        document.getElementById('modalLabel').value = '';
        document.getElementById('modalRecipientName').value = @json($customer->name);
        document.getElementById('modalPhone').value = @json($customer->phone ?? '');
        document.getElementById('modalAddress').value = '';
        document.getElementById('modalPostalCode').value = '';
        document.getElementById('modalIsPrimary').checked = {{ $addresses->isEmpty() ? 'true' : 'false' }};

        if (typeof $ !== 'undefined' && $.fn.select2) {
            $('#modalSubDistrictId').val('').trigger('change');
        }

        const modal = document.getElementById('addressModal');
        modal.classList.remove('hidden');
        setTimeout(() => {
            modal.classList.remove('opacity-0');
            modal.querySelector('.transform').classList.remove('scale-95');
        }, 10);
    }

    function openEditAddressModal(addr) {
        if (!addr) return;

        document.getElementById('addressModalTitle').textContent = 'Edit Alamat';
        document.getElementById('addressForm').action = '/customers/{{ $customer->id }}/addresses/' + addr.id;
        document.getElementById('addressModalMethod').value = 'PUT';
        document.getElementById('modalSubmitBtn').textContent = 'Simpan Perubahan';

        document.getElementById('modalLabel').value = addr.label || '';
        document.getElementById('modalRecipientName').value = addr.recipient_name || @json($customer->name);
        document.getElementById('modalPhone').value = addr.phone || @json($customer->phone ?? '');
        document.getElementById('modalAddress').value = addr.address || '';
        document.getElementById('modalPostalCode').value = addr.postal_code || '';
        document.getElementById('modalIsPrimary').checked = Boolean(addr.is_primary);

        if (typeof $ !== 'undefined' && $.fn.select2) {
            $('#modalSubDistrictId').val(addr.sub_district_id || '').trigger('change');
        } else {
            document.getElementById('modalSubDistrictId').value = addr.sub_district_id || '';
        }

        const modal = document.getElementById('addressModal');
        modal.classList.remove('hidden');
        setTimeout(() => {
            modal.classList.remove('opacity-0');
            modal.querySelector('.transform').classList.remove('scale-95');
        }, 10);
    }

    function closeAddressModal() {
        const modal = document.getElementById('addressModal');
        modal.classList.add('opacity-0');
        modal.querySelector('.transform').classList.add('scale-95');
        setTimeout(() => {
            modal.classList.add('hidden');
        }, 300);
    }

    // Close on backdrop click
    document.getElementById('addressModal')?.addEventListener('click', function(e) {
        if (e.target === this) {
            closeAddressModal();
        }
    });

    // Close on Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            const modal = document.getElementById('addressModal');
            if (modal && !modal.classList.contains('hidden')) {
                closeAddressModal();
            }
        }
    });
</script>
@endpush
