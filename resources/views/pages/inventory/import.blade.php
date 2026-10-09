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
                            <select name="mode" id="importMode" required class="w-full h-11 px-3 border border-outline-variant rounded-lg text-xs bg-white focus:ring-2 focus:ring-primary/20 focus:border-primary focus:outline-none">
                                <option value="set" selected>Ganti Nilai (Set Nilai Baru)</option>
                                <option value="add">Tambah ke Stok Saat Ini (+)</option>
                            </select>
                            <p class="text-[10px] text-on-surface-variant mt-1">Data incoming akan langsung menambahkan On Stock produk (contoh: On Stock 10 + incoming 5 = On Stock 15).</p>
                        </div>

                        <!-- Default Warehouse -->
                        <div>
                            <label class="block text-xs font-semibold text-on-surface mb-1.5">Default Warehouse</label>
                            <select name="warehouse_id" id="warehouseSelect" class="w-full h-11 px-3 border border-outline-variant rounded-lg text-xs bg-white focus:ring-2 focus:ring-primary/20 focus:border-primary focus:outline-none">
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

                    <!-- Spreadsheet Preview Section (Dynamic) -->
                    <div id="previewCard" class="hidden border border-outline-variant/40 rounded-xl p-4 bg-surface-gray/30 space-y-4">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-outline-variant/30 pb-3">
                            <div class="flex items-center gap-2">
                                <span class="material-symbols-outlined text-primary text-[22px]">visibility</span>
                                <div>
                                    <h3 class="text-xs font-bold text-on-surface">Preview Validasi Spreadsheet</h3>
                                    <p class="text-[11px] text-on-surface-variant" id="previewMetaText">Memeriksa struktur baris &amp; SKU...</p>
                                </div>
                            </div>
                            <button type="button" id="btnRefreshPreview" class="text-xs text-primary font-semibold hover:underline flex items-center gap-1 self-start sm:self-auto">
                                <span class="material-symbols-outlined text-[16px]">refresh</span>
                                Muat Ulang Preview
                            </button>
                        </div>

                        <!-- Preview Metric Badges -->
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3" id="previewMetrics">
                            <div class="bg-success/10 border border-success/20 rounded-lg p-3 flex items-center gap-3">
                                <span class="material-symbols-outlined text-success text-[24px]">add_task</span>
                                <div>
                                    <div class="text-[10px] uppercase font-bold text-success tracking-wider">Siap Masuk Stok (>0)</div>
                                    <div class="text-lg font-bold text-success" id="previewReadyCount">0 baris</div>
                                </div>
                            </div>
                            <div class="bg-warning/10 border border-warning/20 rounded-lg p-3 flex items-center gap-3">
                                <span class="material-symbols-outlined text-warning text-[24px]">forward</span>
                                <div>
                                    <div class="text-[10px] uppercase font-bold text-warning tracking-wider">Dilewati (Stok 0)</div>
                                    <div class="text-lg font-bold text-warning" id="previewSkippedCount">0 baris</div>
                                </div>
                            </div>
                            <div class="bg-danger/10 border border-danger/20 rounded-lg p-3 flex items-center gap-3">
                                <span class="material-symbols-outlined text-danger text-[24px]">block</span>
                                <div>
                                    <div class="text-[10px] uppercase font-bold text-danger tracking-wider">Ditolak (Non-Angka / Invalid)</div>
                                    <div class="text-lg font-bold text-danger" id="previewRejectedCount">0 baris</div>
                                </div>
                            </div>
                        </div>

                        <!-- Rejected Warning Banner -->
                        <div id="previewRejectedAlert" class="hidden p-3 bg-danger/10 border border-danger/20 rounded-lg text-xs text-danger flex items-start gap-2">
                            <span class="material-symbols-outlined text-[18px] shrink-0 mt-0.5">warning</span>
                            <div>
                                <strong>Perhatian:</strong> Terdapat baris yang ditolak karena nilai stock bukan angka atau SKU tidak terdaftar. Baris tersebut akan diabaikan/ditolak saat import.
                            </div>
                        </div>

                        <!-- Table Sample Preview -->
                        <div class="overflow-x-auto border border-outline-variant/30 rounded-lg max-h-60 bg-white">
                            <table class="w-full text-left border-collapse text-xs">
                                <thead class="bg-surface-gray border-b border-outline-variant/30 sticky top-0">
                                    <tr>
                                        <th class="px-3 py-2 font-semibold text-on-surface-variant">Baris</th>
                                        <th class="px-3 py-2 font-semibold text-on-surface-variant">SKU</th>
                                        <th class="px-3 py-2 font-semibold text-on-surface-variant">Nama Produk</th>
                                        <th class="px-3 py-2 font-semibold text-on-surface-variant text-right">Incoming</th>
                                        <th class="px-3 py-2 font-semibold text-on-surface-variant text-right">Outgoing</th>
                                        <th class="px-3 py-2 font-semibold text-on-surface-variant">Status</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-outline-variant/20" id="previewTableBody">
                                    <!-- Populated dynamically via JS -->
                                </tbody>
                            </table>
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
                        <p><strong class="text-on-surface">Kolom `sku` (Mandatory/Wajib):</strong> SKU varian produk yang terdaftar di katalog. Satu-satunya kolom yang wajib ada di file.</p>
                    </div>
                    
                    <div class="flex gap-3">
                        <div class="w-6 h-6 rounded-full bg-primary/10 text-primary flex items-center justify-center shrink-0 font-bold text-xs">2</div>
                        <p><strong class="text-on-surface">Kolom `stock` / `incoming` (Opsional):</strong> Jumlah unit produk masuk (default template bernilai 0). Jika diisi <strong>&gt; 0</strong>, sistem otomatis menambahkannya ke incoming stock. Baris bernilai 0 akan dilewati. Jika diisi <strong>bukan angka</strong>, baris akan ditolak.</p>
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
                <a href="{{ route('inventory.import.template') }}" data-no-loader download="template_import_incoming_sku.xlsx" class="w-full flex items-center justify-center gap-2 px-4 py-3 bg-primary/10 text-primary hover:bg-primary/20 transition-colors rounded-xl text-sm font-semibold border border-primary/20">
                    <span class="material-symbols-outlined text-[20px]">download</span>
                    Download Template Excel
                </a>
            </div>
        </div>
    </div>

    <!-- Modern Progress Bar Modal Overlay -->
    <div id="progressModal" class="fixed inset-0 bg-black/60 backdrop-blur-xs z-[100] flex items-center justify-center opacity-0 pointer-events-none transition-all duration-300">
        <div class="bg-white rounded-2xl shadow-2xl p-6 sm:p-8 max-w-md w-full mx-4 border border-outline-variant/30 flex flex-col items-center text-center">
            <!-- Icon State -->
            <div id="progressIconContainer" class="w-16 h-16 rounded-2xl bg-primary/10 flex items-center justify-center text-primary mb-4 transition-transform duration-300">
                <span class="material-symbols-outlined text-[36px] animate-pulse">cloud_upload</span>
            </div>
            
            <h3 class="font-headline-md text-headline-md text-on-surface mb-1 font-bold" id="progressTitle">Memproses Import Incoming Stok</h3>
            <p class="text-xs text-on-surface-variant mb-5" id="progressSubtitle">Mohon tidak menutup jendela browser selama proses penyimpanan inventaris.</p>
            
            <!-- Progress Bar -->
            <div class="w-full mb-3">
                <div class="w-full bg-surface-container-high rounded-full h-3.5 overflow-hidden p-0.5 border border-outline-variant/30">
                    <div id="progressBarFill" class="bg-primary h-full rounded-full transition-all duration-300 w-0 flex items-center justify-end pr-1 shadow-xs"></div>
                </div>
            </div>

            <!-- Progress Details -->
            <div class="w-full flex items-center justify-between text-xs font-semibold mb-2">
                <span id="progressStatusText" class="text-on-surface-variant text-[11px]">Menyiapkan data...</span>
                <span id="progressPercent" class="text-primary font-bold">0%</span>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const fileInput = document.getElementById('fileInput');
            const filePreview = document.getElementById('filePreview');
            const fileName = document.getElementById('fileName');
            const clearFile = document.getElementById('clearFile');
            const importForm = document.getElementById('importForm');
            const submitBtn = document.getElementById('submitBtn');

            // Preview elements
            const previewCard = document.getElementById('previewCard');
            const previewMetaText = document.getElementById('previewMetaText');
            const previewReadyCount = document.getElementById('previewReadyCount');
            const previewSkippedCount = document.getElementById('previewSkippedCount');
            const previewRejectedCount = document.getElementById('previewRejectedCount');
            const previewRejectedAlert = document.getElementById('previewRejectedAlert');
            const previewTableBody = document.getElementById('previewTableBody');
            const btnRefreshPreview = document.getElementById('btnRefreshPreview');

            // Progress modal elements
            const progressModal = document.getElementById('progressModal');
            const progressBarFill = document.getElementById('progressBarFill');
            const progressPercent = document.getElementById('progressPercent');
            const progressStatusText = document.getElementById('progressStatusText');
            const progressTitle = document.getElementById('progressTitle');
            const progressSubtitle = document.getElementById('progressSubtitle');
            const progressIconContainer = document.getElementById('progressIconContainer');

            // File selection
            fileInput.addEventListener('change', function() {
                if (this.files && this.files[0]) {
                    fileName.textContent = this.files[0].name;
                    filePreview.classList.remove('hidden');
                    loadSpreadsheetPreview();
                }
            });

            clearFile.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                fileInput.value = '';
                filePreview.classList.add('hidden');
                previewCard.classList.add('hidden');
            });

            if (btnRefreshPreview) {
                btnRefreshPreview.addEventListener('click', function(e) {
                    e.preventDefault();
                    loadSpreadsheetPreview();
                });
            }

            // Function to trigger spreadsheet preview via AJAX
            async function loadSpreadsheetPreview() {
                if (!fileInput.files || !fileInput.files[0]) return;

                previewCard.classList.remove('hidden');
                previewMetaText.textContent = 'Membaca dan memvalidasi file spreadsheet...';
                previewTableBody.innerHTML = `<tr><td colspan="5" class="py-6 text-center text-xs text-on-surface-variant"><span class="material-symbols-outlined animate-spin text-[20px] align-middle mr-1">progress_activity</span> Menganalisis baris spreadsheet...</td></tr>`;

                const formData = new FormData();
                formData.append('file', fileInput.files[0]);
                formData.append('_token', '{{ csrf_token() }}');

                const whSelect = document.getElementById('warehouseSelect');
                if (whSelect && whSelect.value) formData.append('warehouse_id', whSelect.value);

                const chSelect = document.getElementById('storeChannelSelect');
                if (chSelect && chSelect.value) formData.append('store_channel_id', chSelect.value);

                try {
                    const response = await fetch('{{ route('inventory.import.preview') }}', {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });

                    const data = await response.json();

                    if (!response.ok || !data.success) {
                        previewMetaText.textContent = 'Gagal memvalidasi spreadsheet: ' + (data.message || 'File tidak valid.');
                        previewTableBody.innerHTML = `<tr><td colspan="5" class="py-4 text-center text-xs text-danger font-medium">${data.message || 'Gagal membaca spreadsheet.'}</td></tr>`;
                        return;
                    }

                    // Render Stats
                    previewMetaText.textContent = `Total ${data.total_rows} baris SKU terdeteksi pada file.`;
                    previewReadyCount.textContent = `${data.ready_count} baris`;
                    previewSkippedCount.textContent = `${data.skipped_count} baris`;
                    previewRejectedCount.textContent = `${data.rejected_count} baris`;

                    if (data.rejected_count > 0) {
                        previewRejectedAlert.classList.remove('hidden');
                    } else {
                        previewRejectedAlert.classList.add('hidden');
                    }

                    // Render Sample Rows
                    if (data.preview_rows && data.preview_rows.length > 0) {
                        let html = '';
                        data.preview_rows.forEach(row => {
                            let badgeHtml = '';
                            if (row.status === 'ready') {
                                badgeHtml = `<span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-semibold bg-success/15 text-success">
                                    <span class="w-1.5 h-1.5 rounded-full bg-success"></span> Siap Masuk (+${row.stock_qty})
                                </span>`;
                            } else if (row.status === 'skipped') {
                                badgeHtml = `<span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-semibold bg-gray-100 text-gray-600">
                                    <span class="w-1.5 h-1.5 rounded-full bg-gray-400"></span> Dilewati (Stok 0)
                                </span>`;
                            } else {
                                badgeHtml = `<span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-semibold bg-danger/15 text-danger" title="${row.message || ''}">
                                    <span class="w-1.5 h-1.5 rounded-full bg-danger"></span> ${row.status_label || 'Ditolak'}
                                </span>`;
                            }

                            html += `
                                <tr class="hover:bg-surface-gray/50 ${row.status === 'rejected' ? 'bg-danger/5' : ''}">
                                    <td class="px-3 py-2 text-[11px] font-mono text-on-surface-variant font-bold">#${row.row_number}</td>
                                    <td class="px-3 py-2 text-[11px] font-mono font-semibold text-on-surface">${row.sku}</td>
                                    <td class="px-3 py-2 text-[11px] text-on-surface-variant truncate max-w-[200px]" title="${row.product_name}">${row.product_name}</td>
                                    <td class="px-3 py-2 text-[11px] text-right font-mono font-semibold ${row.stock_qty > 0 ? 'text-success' : 'text-on-surface-variant'}">${row.stock_val}</td>
                                    <td class="px-3 py-2 text-[11px] text-right font-mono font-semibold ${row.outgoing_qty > 0 ? 'text-purple-700' : 'text-on-surface-variant'}">${row.outgoing_val || '-'}</td>
                                    <td class="px-3 py-2 text-[11px]">${badgeHtml}</td>
                                </tr>
                            `;
                        });
                        previewTableBody.innerHTML = html;
                    } else {
                        previewTableBody.innerHTML = `<tr><td colspan="6" class="py-4 text-center text-xs text-on-surface-variant">Tidak ada baris data untuk ditampilkan.</td></tr>`;
                    }

                } catch (err) {
                    previewMetaText.textContent = 'Gagal menghubungi server untuk preview: ' + err.message;
                    previewTableBody.innerHTML = `<tr><td colspan="6" class="py-4 text-center text-xs text-danger">Koneksi gagal saat membaca preview file.</td></tr>`;
                }
            }

            // Form Submit with Progress Bar
            importForm.addEventListener('submit', async function(e) {
                e.preventDefault();

                if (!fileInput.files || !fileInput.files[0]) {
                    if (typeof showWarningPopup === 'function') {
                        showWarningPopup('Silakan pilih file spreadsheet terlebih dahulu.');
                    } else if (typeof showToast === 'function') {
                        showToast('warning', 'Silakan pilih file spreadsheet terlebih dahulu.');
                    } else {
                        alert('Silakan pilih file spreadsheet terlebih dahulu.');
                    }
                    return;
                }

                // Show Progress Modal
                progressModal.classList.remove('opacity-0', 'pointer-events-none');
                submitBtn.disabled = true;
                submitBtn.classList.add('opacity-70');

                // Animate Progress
                let currentProgress = 0;
                function setProgress(val, statusText) {
                    currentProgress = val;
                    progressBarFill.style.width = val + '%';
                    progressPercent.textContent = val + '%';
                    if (statusText) progressStatusText.textContent = statusText;
                }

                setProgress(15, 'Mengunggah file spreadsheet...');

                const progressInterval = setInterval(() => {
                    if (currentProgress < 75) {
                        setProgress(currentProgress + 8, currentProgress > 40 ? 'Memverifikasi SKU dan memproses stok...' : 'Membaca baris data spreadsheet...');
                    }
                }, 250);

                const formData = new FormData(importForm);

                try {
                    const response = await fetch(importForm.action, {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });

                    clearInterval(progressInterval);

                    const data = await response.json();

                    if (!response.ok || !data.success) {
                        setProgress(100, 'Gagal memproses data.');
                        progressTitle.textContent = 'Import Gagal';
                        progressSubtitle.textContent = data.message || 'Terjadi kesalahan saat memproses spreadsheet.';
                        progressIconContainer.className = 'w-16 h-16 rounded-2xl bg-danger/10 flex items-center justify-center text-danger mb-4';
                        progressIconContainer.innerHTML = '<span class="material-symbols-outlined text-[36px]">error</span>';

                        setTimeout(() => {
                            progressModal.classList.add('opacity-0', 'pointer-events-none');
                            submitBtn.disabled = false;
                            submitBtn.classList.remove('opacity-70');
                            alert(data.message || 'Gagal memproses import.');
                        }, 2000);
                        return;
                    }

                    // Success
                    setProgress(100, 'Import selesai! Menyimpan perubahan...');
                    progressTitle.textContent = 'Import Berhasil!';
                    progressSubtitle.textContent = data.message || 'Stok incoming berhasil diperbarui.';
                    progressIconContainer.className = 'w-16 h-16 rounded-2xl bg-success/10 flex items-center justify-center text-success mb-4';
                    progressIconContainer.innerHTML = '<span class="material-symbols-outlined text-[36px]">check_circle</span>';

                    setTimeout(() => {
                        window.location.reload();
                    }, 1200);

                } catch (err) {
                    clearInterval(progressInterval);
                    setProgress(100, 'Kesalahan koneksi');
                    progressTitle.textContent = 'Koneksi Terputus';
                    progressSubtitle.textContent = err.message || 'Gagal menghubungi server.';
                    
                    setTimeout(() => {
                        progressModal.classList.add('opacity-0', 'pointer-events-none');
                        submitBtn.disabled = false;
                        submitBtn.classList.remove('opacity-70');
                        alert('Terjadi kesalahan koneksi saat import: ' + err.message);
                    }, 2000);
                }
            });
        });
    </script>
    @endpush
@endsection
