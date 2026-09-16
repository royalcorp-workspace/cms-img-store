@extends('layouts.app')

@section('title', 'Import Incoming Stok by SKU')

@section('content')
    <!-- Header Section -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="font-headline-lg text-headline-lg text-on-surface">Import Incoming Stok by SKU</h1>
            <nav class="flex items-center gap-2 text-label-sm text-on-surface-variant mt-1 font-medium">
                <a href="{{ route('dashboard') }}" class="hover:text-primary transition-colors">eCommerce</a>
                <span class="material-symbols-outlined text-[14px]">chevron_right</span>
                <a href="{{ route('inventory.index') }}" class="hover:text-primary transition-colors">Inventory</a>
                <span class="material-symbols-outlined text-[14px]">chevron_right</span>
                <span class="text-on-surface">Import Incoming</span>
            </nav>
        </div>
        <div>
            <a href="{{ route('inventory.index') }}" class="flex items-center gap-2 px-4 py-2 border border-outline-variant bg-white text-on-surface hover:bg-surface-container-high rounded-lg text-sm font-medium transition-all">
                <span class="material-symbols-outlined text-[18px]">arrow_back</span>
                Kembali ke Inventory
            </a>
        </div>
    </div>

    <!-- Standard Submenu Tabs -->
    @include('layouts.partials.inventory-submenu')

    <!-- Flash Error -->
    @if(session('error'))
        <div class="mb-6 p-4 bg-danger/10 border border-danger/20 text-danger rounded-xl flex items-start gap-3">
            <span class="material-symbols-outlined text-[20px] shrink-0 mt-0.5">error</span>
            <div class="text-sm font-medium">{{ session('error') }}</div>
        </div>
    @endif

    <!-- Import Results Summary -->
    @if(session('import_result'))
        @php
            $result = session('import_result');
        @endphp
        <div class="bg-white rounded-xl shadow-sm border border-outline-variant/30 overflow-hidden mb-6 p-6">
            <h2 class="font-headline-md text-headline-md text-on-surface mb-4 flex items-center gap-2">
                <span class="material-symbols-outlined text-primary text-[24px]">analytics</span>
                Ringkasan Hasil Import
            </h2>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
                <!-- Success -->
                <div class="bg-success/10 border border-success/20 rounded-xl p-4 flex items-center gap-4">
                    <div class="w-12 h-12 rounded-full bg-success/20 flex items-center justify-center text-success">
                        <span class="material-symbols-outlined text-[28px]">check_circle</span>
                    </div>
                    <div>
                        <div class="text-xs text-on-surface-variant uppercase tracking-wider font-semibold">Berhasil</div>
                        <div class="text-2xl font-bold text-success">{{ $result['success'] }} baris</div>
                    </div>
                </div>
                
                <!-- Skipped -->
                <div class="bg-warning/10 border border-warning/20 rounded-xl p-4 flex items-center gap-4">
                    <div class="w-12 h-12 rounded-full bg-warning/20 flex items-center justify-center text-warning">
                        <span class="material-symbols-outlined text-[28px]">info</span>
                    </div>
                    <div>
                        <div class="text-xs text-on-surface-variant uppercase tracking-wider font-semibold">Dilewati (Kosong)</div>
                        <div class="text-2xl font-bold text-warning">{{ $result['skipped'] }} baris</div>
                    </div>
                </div>

                <!-- Failed -->
                <div class="bg-danger/10 border border-danger/20 rounded-xl p-4 flex items-center gap-4">
                    <div class="w-12 h-12 rounded-full bg-danger/20 flex items-center justify-center text-danger">
                        <span class="material-symbols-outlined text-[28px]">cancel</span>
                    </div>
                    <div>
                        <div class="text-xs text-on-surface-variant uppercase tracking-wider font-semibold">Gagal</div>
                        <div class="text-2xl font-bold text-danger">{{ $result['failed'] }} baris</div>
                    </div>
                </div>
            </div>

            @if(!empty($result['errors']))
                <div class="border-t border-outline-variant/20 pt-4">
                    <h3 class="font-semibold text-sm text-on-surface mb-3 flex items-center gap-2">
                        <span class="material-symbols-outlined text-danger text-[20px]">list_alt</span>
                        Rincian Log Error
                    </h3>
                    <div class="overflow-x-auto border border-outline-variant/30 rounded-xl">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-surface-gray border-b border-outline-variant/30">
                                    <th class="px-4 py-3 text-xs font-semibold text-on-surface-variant uppercase">Baris File</th>
                                    <th class="px-4 py-3 text-xs font-semibold text-on-surface-variant uppercase">SKU</th>
                                    <th class="px-4 py-3 text-xs font-semibold text-on-surface-variant uppercase">Keterangan Error</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-outline-variant/20">
                                @foreach($result['errors'] as $error)
                                    <tr class="hover:bg-surface-container/20">
                                        <td class="px-4 py-3 text-xs font-bold text-on-surface">Baris {{ $error['row'] }}</td>
                                        <td class="px-4 py-3 text-xs font-mono text-secondary">{{ $error['sku'] }}</td>
                                        <td class="px-4 py-3 text-xs text-danger font-medium">{{ $error['message'] }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Import Form Card -->
        <div class="lg:col-span-2 bg-white rounded-xl shadow-sm border border-outline-variant/30 p-6 flex flex-col justify-between">
            <div>
                <h2 class="font-headline-md text-headline-md text-on-surface mb-2">Upload File Spreadsheet</h2>
                <p class="text-body-md text-on-surface-variant mb-6">Pilih file Excel (.xlsx, .xls) atau CSV untuk mengisi stok incoming berdasarkan SKU varian produk.</p>
                
                <form action="{{ route('inventory.import.store') }}" method="POST" enctype="multipart/form-data" id="importForm" class="space-y-5" data-no-direct-upload="true">
                    @csrf
                    
                    <!-- File Dropzone -->
                    <div>
                        <label class="block text-xs font-semibold text-on-surface mb-2">File Spreadsheet (.xlsx, .xls, .csv) <span class="text-danger">*</span></label>
                        
                        <div id="dropzone" class="border-2 border-dashed border-outline-variant/60 hover:border-primary/80 transition-all rounded-xl p-8 flex flex-col items-center justify-center text-center cursor-pointer bg-surface-gray/50 hover:bg-primary/5 group relative">
                            <input type="file" name="file" id="fileInput" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer" accept=".xlsx,.xls,.csv,.txt" required>
                            
                            <span class="material-symbols-outlined text-[48px] text-on-surface-variant group-hover:text-primary transition-colors mb-3">upload_file</span>
                            <div class="text-sm font-semibold text-on-surface mb-1">Klik untuk memilih file atau seret & lepas disini</div>
                            <div class="text-xs text-on-surface-variant">Mendukung format .xlsx, .xls, .csv (maks. 10MB)</div>
                            
                            <!-- Selected file name preview -->
                            <div id="filePreview" class="hidden mt-4 p-3 bg-primary/10 border border-primary/20 rounded-lg flex items-center gap-2 max-w-full">
                                <span class="material-symbols-outlined text-primary text-[20px]">description</span>
                                <span id="fileName" class="text-xs font-medium text-primary truncate max-w-[280px]">file_name.xlsx</span>
                                <button type="button" id="clearFile" class="text-on-surface-variant hover:text-danger p-0.5 rounded-full hover:bg-surface-gray/50">
                                    <span class="material-symbols-outlined text-[16px] block">close</span>
                                </button>
                            </div>
                        </div>
                        @error('file')
                            <p class="text-danger text-xs mt-1 font-medium">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Options Grid -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 pt-2">
                        <!-- Mode -->
                        <div>
                            <label class="block text-xs font-semibold text-on-surface mb-1.5">Metode Pengisian <span class="text-danger">*</span></label>
                            <select name="mode" required class="w-full h-11 px-3 border border-outline-variant rounded-lg text-xs bg-white focus:ring-2 focus:ring-primary/20 focus:border-primary focus:outline-none">
                                <option value="set" selected>Ganti Nilai (Set Nilai Baru)</option>
                                <option value="add">Tambah ke Stok Saat Ini (+)</option>
                            </select>
                            <p class="text-[10px] text-on-surface-variant mt-1">"Ganti Nilai" mengganti incoming langsung. "Tambah" menambahkan jumlah baru ke stok incoming lama.</p>
                        </div>

                        <!-- Default Warehouse -->
                        <div>
                            <label class="block text-xs font-semibold text-on-surface mb-1.5">Default Warehouse</label>
                            <select name="warehouse_id" class="w-full h-11 px-3 border border-outline-variant rounded-lg text-xs bg-white focus:ring-2 focus:ring-primary/20 focus:border-primary focus:outline-none">
                                @foreach($warehouses as $wh)
                                    <option value="{{ $wh->id }}" {{ ($defaultWarehouse && $defaultWarehouse->id == $wh->id) ? 'selected' : '' }}>
                                        {{ $wh->name }} ({{ $wh->code }})
                                    </option>
                                @endforeach
                            </select>
                            <p class="text-[10px] text-on-surface-variant mt-1">Digunakan jika kolom 'warehouse_code' pada file tidak diisi.</p>
                        </div>

                        <!-- Store Channel -->
                        <div>
                            <label class="block text-xs font-semibold text-on-surface mb-1.5">Store Channel</label>
                            <select name="store_channel_id" id="storeChannelSelect" class="w-full select2-channel" data-placeholder="Pilih atau cari Store Channel..." data-ajax-url="{{ route('inventory.channels.search') }}">
                                <option value="">-- Pilih atau Cari Channel Toko --</option>
                                @foreach($channels as $ch)
                                    <option value="{{ $ch->id }}" 
                                            data-store="{{ $ch->store->name ?? '-' }}" 
                                            data-code="{{ $ch->code }}"
                                            {{ ($defaultChannel && $defaultChannel->id == $ch->id) ? 'selected' : '' }}>
                                        {{ $ch->name }} ({{ $ch->store->name ?? '-' }})
                                    </option>
                                @endforeach
                            </select>
                            <p class="text-[10px] text-on-surface-variant mt-1">Kanal alokasi stok penjualan.</p>
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-outline-variant/20">
                        <a href="{{ route('inventory.index') }}" class="px-5 py-2.5 border border-outline-variant text-on-surface hover:bg-surface-container-high rounded-lg text-sm font-medium transition-colors">
                            Batal
                        </a>
                        <button type="submit" id="submitBtn" class="flex items-center gap-2 px-6 py-2.5 bg-primary text-white rounded-lg text-sm font-medium hover:opacity-95 transition-all shadow-sm">
                            <span class="material-symbols-outlined text-[18px]">cloud_upload</span>
                            Mulai Import Incoming
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Instructions & Template Card -->
        <div class="bg-white rounded-xl shadow-sm border border-outline-variant/30 p-6 flex flex-col justify-between">
            <div>
                <h2 class="font-headline-md text-headline-md text-on-surface mb-2">Panduan Import</h2>
                <p class="text-xs text-on-surface-variant mb-4">Gunakan template Excel resmi agar format kolom terdeteksi otomatis oleh sistem.</p>
                
                <div class="space-y-4 mb-6 text-xs text-on-surface-variant">
                    <div class="flex gap-3">
                        <div class="w-6 h-6 rounded-full bg-primary/10 text-primary flex items-center justify-center shrink-0 font-bold text-xs">1</div>
                        <p><strong class="text-on-surface">Kolom `sku` (Wajib):</strong> SKU varian produk yang terdaftar di katalog. Pencarian bersifat case-sensitive.</p>
                    </div>
                    
                    <div class="flex gap-3">
                        <div class="w-6 h-6 rounded-full bg-primary/10 text-primary flex items-center justify-center shrink-0 font-bold text-xs">2</div>
                        <p><strong class="text-on-surface">Kolom `incoming` (Wajib):</strong> Jumlah unit produk yang sedang dalam pengiriman/akan masuk (angka bulat >= 0).</p>
                    </div>

                    <div class="flex gap-3">
                        <div class="w-6 h-6 rounded-full bg-primary/10 text-primary flex items-center justify-center shrink-0 font-bold text-xs">3</div>
                        <p><strong class="text-on-surface">Kolom `warehouse_code` (Opsional):</strong> Kode gudang tujuan (contoh: <code class="bg-surface-container-low px-1 py-0.5 rounded font-mono text-primary">GD-JKT01</code>). Jika dikosongkan, otomatis menggunakan gudang yang dipilih pada form.</p>
                    </div>

                    <div class="flex gap-3">
                        <div class="w-6 h-6 rounded-full bg-primary/10 text-primary flex items-center justify-center shrink-0 font-bold text-xs">4</div>
                        <p><strong class="text-on-surface">Kolom `reference_product_name` (Referensi):</strong> Hanya membantu Anda melihat nama barang saat mengisi spreadsheet, tidak disimpan ke database.</p>
                    </div>
                </div>
            </div>
            
            <div class="pt-4 border-t border-outline-variant/20 mt-4">
                <a href="{{ route('inventory.import.template') }}" class="w-full flex items-center justify-center gap-2 px-4 py-3 bg-primary/10 text-primary hover:bg-primary/20 transition-colors rounded-xl text-sm font-semibold border border-primary/20">
                    <span class="material-symbols-outlined text-[20px]">download</span>
                    Download Template Excel
                </a>
            </div>
        </div>
    </div>

    <!-- Loading Overlay -->
    <div id="loadingOverlay" class="fixed inset-0 bg-black/60 backdrop-blur-xs z-[100] flex items-center justify-center opacity-0 pointer-events-none transition-all duration-300">
        <div class="bg-white rounded-2xl shadow-xl p-8 max-w-sm w-full mx-4 flex flex-col items-center text-center">
            <div class="relative w-16 h-16 mb-4">
                <div class="absolute inset-0 rounded-full border-4 border-primary/20"></div>
                <div class="absolute inset-0 rounded-full border-4 border-primary border-t-transparent animate-spin"></div>
            </div>
            <h3 class="font-semibold text-base text-on-surface mb-1">Memproses Import Incoming...</h3>
            <p class="text-xs text-on-surface-variant">Mohon tidak menutup atau merefresh halaman ini saat data sedang diproses.</p>
        </div>
    </div>

    @push('scripts')
    <script>
        const fileInput = document.getElementById('fileInput');
        const filePreview = document.getElementById('filePreview');
        const fileName = document.getElementById('fileName');
        const clearFile = document.getElementById('clearFile');
        const importForm = document.getElementById('importForm');
        const submitBtn = document.getElementById('submitBtn');
        const loadingOverlay = document.getElementById('loadingOverlay');

        fileInput.addEventListener('change', function(e) {
            if (this.files && this.files[0]) {
                fileName.textContent = this.files[0].name;
                filePreview.classList.remove('hidden');
            }
        });

        clearFile.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            fileInput.value = '';
            filePreview.classList.add('hidden');
        });

        importForm.addEventListener('submit', function(e) {
            if (!fileInput.files || !fileInput.files[0]) {
                e.preventDefault();
                alert('Silakan pilih file spreadsheet terlebih dahulu.');
                return;
            }

            loadingOverlay.classList.remove('opacity-0', 'pointer-events-none');
            submitBtn.disabled = true;
            submitBtn.classList.add('opacity-70');
            submitBtn.innerHTML = '<span class="material-symbols-outlined animate-spin text-[18px]">progress_activity</span> Mengimpor...';
        });
    </script>
    @endpush
@endsection
