<div id="resiModal" class="fixed inset-0 bg-black/60 z-50 hidden items-center justify-center p-3 sm:p-4 backdrop-blur-xs">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-2xl overflow-hidden border border-outline-variant/40 flex flex-col max-h-[92vh] animate-in fade-in duration-200">
        <!-- Header -->
        <div class="px-6 py-4 border-b border-outline-variant/30 flex items-center justify-between bg-surface-container-lowest">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-primary/10 text-primary flex items-center justify-center">
                    <span class="material-symbols-outlined text-[22px]">local_shipping</span>
                </div>
                <div>
                    <h3 class="font-bold text-sm text-on-surface flex items-center gap-2">
                        <span>Penyelesaian & Pengiriman Pesanan</span>
                        <span id="modalOrderBadge" class="text-[11px] font-semibold px-2 py-0.5 rounded-md bg-primary/10 text-primary">#ORD-</span>
                    </h3>
                    <p class="text-xs text-on-surface-variant mt-0.5">Lengkapi pengiriman: input resi manual untuk armada toko atau ambil resi otomatis melalui vendor ekspedisi</p>
                </div>
            </div>
            <button type="button" onclick="closeResiModal()" class="w-8 h-8 rounded-lg flex items-center justify-center text-on-surface-variant hover:bg-surface-container transition-colors">
                <span class="material-symbols-outlined text-[20px]">close</span>
            </button>
        </div>

        <!-- Navigation Tabs -->
        <div class="px-6 pt-3 pb-0 bg-surface-container-low border-b border-outline-variant/30 flex items-center gap-2">
            <button type="button" id="tabBtnManual" onclick="switchFulfillmentTab('manual')" class="px-4 py-2.5 text-xs font-bold rounded-t-xl border-b-2 border-primary text-primary bg-white flex items-center gap-1.5 transition-all">
                <span class="material-symbols-outlined text-[16px]">edit_document</span>
                <span>1. Input Resi Manual</span>
                <span id="tabBadgeKurirToko" class="hidden text-[10px] px-1.5 py-0.2 rounded font-bold bg-amber-100 text-amber-800">Kurir Toko</span>
            </button>
            <button type="button" id="tabBtnBiteship" onclick="switchFulfillmentTab('biteship')" class="px-4 py-2.5 text-xs font-bold rounded-t-xl border-b-2 border-transparent text-on-surface-variant hover:text-primary hover:bg-white/50 flex items-center gap-1.5 transition-all">
                <span class="material-symbols-outlined text-[16px]">cloud_sync</span>
                <span>2. Ambil Resi Otomatis (Get Resi)</span>
                <span class="text-[10px] px-1.5 py-0.2 rounded font-bold bg-blue-100 text-blue-800">Vendor Ekspedisi</span>
            </button>
            <button type="button" id="tabBtnTracking" onclick="switchFulfillmentTab('tracking')" class="px-4 py-2.5 text-xs font-bold rounded-t-xl border-b-2 border-transparent text-on-surface-variant hover:text-primary hover:bg-white/50 flex items-center gap-1.5 transition-all hidden">
                <span class="material-symbols-outlined text-[16px]">radar</span>
                <span>3. Lacak Pengiriman</span>
            </button>
        </div>

        <!-- Modal Body Content -->
        <div class="p-6 overflow-y-auto flex-1 space-y-4">
            
            <!-- TAB 1: MANUAL RESI -->
            <div id="panelManual" class="space-y-4">
                <!-- Notice: Khusus Kurir Toko -->
                <div id="kurirTokoNotice" class="hidden p-3.5 bg-amber-50/90 border border-amber-300 rounded-xl flex items-start gap-2.5 text-xs text-amber-900">
                    <span class="material-symbols-outlined text-[20px] text-amber-600 mt-0.5 shrink-0">storefront</span>
                    <div class="space-y-0.5">
                        <div class="font-bold text-amber-950 flex items-center gap-1.5">
                            <span>Pengiriman Menggunakan Kurir Toko</span>
                            <span class="text-[10px] px-2 py-0.5 rounded-full bg-amber-200 text-amber-900 font-bold">Armada Internal</span>
                        </div>
                        <p class="text-amber-800">Pesanan ini menggunakan kurir armada internal toko. Pengiriman ini <strong>hanya dapat menggunakan Input Resi Manual</strong> (silakan masukkan nomor surat jalan atau resi pengiriman armada).</p>
                    </div>
                </div>

                <!-- Notice: Resi Terkunci jika pesanan sudah Shipped / Delivered -->
                <div id="manualResiLockedNotice" class="hidden p-3.5 bg-amber-50 border border-amber-200 rounded-xl flex items-start gap-2.5 text-xs text-amber-900">
                    <span class="material-symbols-outlined text-[20px] text-amber-600 mt-0.5 shrink-0">lock</span>
                    <div class="space-y-0.5">
                        <div class="font-bold">Nomor Resi & Pengiriman Terkunci</div>
                        <p class="text-amber-800">Pesanan ini sudah berstatus <strong>Dikirim (Shipped)</strong> atau <strong>Diterima (Delivered)</strong>. Nomor resi dan data pengiriman sudah terkunci dan tidak dapat diubah atau ditambah lagi.</p>
                    </div>
                </div>

                <div class="p-3 bg-blue-50/70 border border-blue-200/80 rounded-xl flex items-start gap-2.5 text-xs text-blue-900">
                    <span class="material-symbols-outlined text-[18px] text-blue-600 mt-0.5 shrink-0">info</span>
                    <div>
                        <span class="font-bold">Mode Manual:</span> Masukkan nomor resi (AWB) ekspedisi atau surat jalan kurir toko. Status pesanan akan diupdate dan sistem otomatis menyelaraskan dokumen dari <strong>Picking List</strong>, <strong>Packing Slip</strong>, hingga <strong>Handover</strong> kurir.
                    </div>
                </div>

                <form id="editResiForm" onsubmit="submitResiForm(event)" class="space-y-4">
                    <input type="hidden" id="formOrderId" value="">

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="space-y-1.5">
                            <label for="formCourierId" class="block text-xs font-semibold text-on-surface flex items-center justify-between">
                                <span>Kurir Ekspedisi</span>
                                <span class="text-[10px] text-on-surface-variant font-normal">Data Kurir Aktif</span>
                            </label>
                            <select id="formCourierId" name="courier_id" class="w-full px-3.5 py-2.5 border border-outline-variant rounded-xl text-xs font-bold text-primary focus:ring-2 focus:ring-primary/20 focus:outline-none bg-white">
                                <!-- Opsi kurir akan diisi otomatis dari database -->
                            </select>
                        </div>

                        <div class="space-y-1.5">
                            <label for="formStatusTarget" class="block text-xs font-semibold text-on-surface">
                                Update Status Pesanan Menjadi
                            </label>
                            <select id="formStatusTarget" class="w-full px-3 py-2 border border-outline-variant rounded-xl text-xs focus:ring-2 focus:ring-primary/20 focus:outline-none bg-white">
                                <option value="4" selected>Shipped (Pesanan Dikirim)</option>
                                <option value="5">Delivered (Pesanan Selesai)</option>
                            </select>
                        </div>
                    </div>

                    <div class="space-y-1.5">
                        <label for="formTrackingNumber" class="block text-xs font-semibold text-on-surface">
                            Nomor Resi Pengiriman (AWB) <span class="text-danger">*</span>
                        </label>
                        <div class="flex gap-2">
                            <input type="text" id="formTrackingNumber" required placeholder="Contoh: JP1234567890 / SOCAG000123" class="w-full px-3.5 py-2.5 border border-outline-variant rounded-xl text-xs font-mono font-medium focus:ring-2 focus:ring-primary/20 focus:outline-none bg-white">
                            <button type="button" id="copyResiBtn" onclick="copyResiCode()" class="px-3.5 py-2 border border-outline-variant rounded-xl text-xs font-medium text-on-surface hover:bg-surface-container flex items-center gap-1 shrink-0" title="Salin Resi">
                                <span class="material-symbols-outlined text-[16px]" id="copyResiIcon">content_copy</span>
                                <span id="copyResiText">Salin</span>
                            </button>
                        </div>
                    </div>

                    <div id="formResiError" class="hidden p-3 bg-red-50 border border-red-200 rounded-xl text-xs text-danger font-medium flex items-center gap-2">
                        <span class="material-symbols-outlined text-[16px]">error</span>
                        <span id="formResiErrorText">Nomor resi wajib diisi.</span>
                    </div>

                    <div class="flex justify-end gap-2 pt-3 border-t border-outline-variant/20">
                        <button type="button" onclick="closeResiModal()" class="px-4 py-2 border border-outline-variant rounded-xl text-xs font-medium text-on-surface-variant hover:bg-surface-container transition-colors">
                            Batal
                        </button>
                        <button type="submit" id="saveResiBtn" class="px-5 py-2 bg-primary text-white rounded-xl text-xs font-bold hover:opacity-90 transition-opacity shadow-sm flex items-center gap-1.5">
                            <span class="material-symbols-outlined text-[16px]">save</span>
                            <span>Simpan Resi Manual</span>
                        </button>
                    </div>
                </form>
            </div>

            <!-- TAB 2: AMBIL RESI OTOMATIS (VENDOR EKSPEDISI) -->
            <div id="panelBiteship" class="space-y-4 hidden">

                <!-- Notice: Kurir Toko Blocked inside panel -->
                <div id="panelBiteshipKurirTokoBlock" class="hidden p-4 bg-amber-50 border border-amber-300 rounded-xl text-xs text-amber-900 flex items-start gap-3">
                    <span class="material-symbols-outlined text-[22px] text-amber-600 shrink-0">storefront</span>
                    <div class="space-y-1">
                        <div class="font-bold text-amber-950">Layanan Resi Ekspedisi Otomatis Tidak Tersedia untuk Kurir Toko</div>
                        <p class="text-amber-800">Pesanan ini menggunakan pengiriman <strong>Kurir Toko</strong> yang dikirim oleh armada toko sendiri. Layanan resi ekspedisi otomatis hanya berlaku untuk kurir ekspedisi pihak ketiga. Silakan gunakan tab <strong>Input Resi Manual</strong>.</p>
                        <button type="button" onclick="switchFulfillmentTab('manual')" class="mt-2 inline-flex items-center gap-1.5 px-3 py-1.5 bg-primary text-white rounded-lg text-xs font-bold hover:opacity-90">
                            <span class="material-symbols-outlined text-[15px]">arrow_back</span>
                            <span>Buka Input Resi Manual</span>
                        </button>
                    </div>
                </div>
                
                @if(isset($biteshipConfigured) && !$biteshipConfigured)
                    <div class="p-3.5 bg-amber-50 border border-amber-200 rounded-xl flex items-start gap-2.5 text-xs text-amber-900">
                        <span class="material-symbols-outlined text-[20px] text-amber-600 mt-0.5 shrink-0">warning</span>
                        <div class="space-y-1">
                            <div class="font-bold">Layanan Resi Ekspedisi Otomatis Belum Aktif</div>
                            <p class="text-amber-800">Koneksi layanan ekspedisi otomatis belum diaktifkan. Anda dapat menggunakan tab <strong>Input Resi Manual</strong> untuk memasukkan nomor resi atau surat jalan pengiriman secara langsung.</p>
                        </div>
                    </div>
                @else
                    <div class="p-3 bg-emerald-50/70 border border-emerald-200/80 rounded-xl flex items-center justify-between text-xs text-emerald-900">
                        <div class="flex items-center gap-2">
                            <span class="material-symbols-outlined text-[18px] text-emerald-600">verified</span>
                            <span><strong>Layanan Ekspedisi Terhubung:</strong> Sistem akan mengambil nomor resi (AWB) langsung dari vendor ekspedisi yang dipilih dan memperbarui dokumen pengiriman secara otomatis.</span>
                        </div>
                        <span class="px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-800 font-bold text-[10px] uppercase">Ready</span>
                    </div>
                @endif

                <!-- Notice: Resi Ekspedisi Sudah Terbit -->
                <div id="biteshipAlreadyIssuedNotice" class="hidden p-3.5 bg-blue-50/90 border border-blue-200 rounded-xl text-xs text-blue-900 flex items-start gap-2.5">
                    <span class="material-symbols-outlined text-[20px] text-blue-600 mt-0.5 shrink-0">verified</span>
                    <div class="space-y-1">
                        <div class="font-bold text-blue-950">Nomor Resi Ekspedisi Sudah Terbit</div>
                        <p class="text-blue-800">Nomor resi untuk pesanan ini telah aktif dari vendor ekspedisi. Pengambilan resi otomatis tidak dapat diulang guna mencegah duplikasi pengiriman. Silakan gunakan tab <strong>Lacak Pengiriman</strong> untuk memantau perjalanan kurir.</p>
                    </div>
                </div>

                <form id="biteshipShipmentForm" onsubmit="submitBiteshipForm(event)" class="space-y-4">
                    <!-- Delivery Info Summary -->
                    <div class="p-3.5 bg-surface-container-low rounded-xl border border-outline-variant/30 space-y-2 text-xs">
                        <div class="font-bold text-on-surface text-[11px] uppercase tracking-wider text-primary flex items-center gap-1">
                            <span class="material-symbols-outlined text-[14px]">location_on</span>
                            <span>Tujuan Pengiriman Pelanggan</span>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 text-on-surface">
                            <div>
                                <span class="text-on-surface-variant block text-[10px]">Penerima:</span>
                                <span class="font-bold" id="biteshipCustomerName">-</span> (<span id="biteshipCustomerPhone">-</span>)
                            </div>
                            <div>
                                <span class="text-on-surface-variant block text-[10px]">Alamat Lengkap:</span>
                                <span class="text-[11px]" id="biteshipCustomerAddress">-</span>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <div class="space-y-1">
                            <label for="biteshipCourierCompany" class="block text-xs font-semibold text-on-surface flex items-center justify-between">
                                <span>Pilih Vendor Ekspedisi</span>
                                <span class="text-[10px] text-on-surface-variant font-normal">Data Vendor Aktif</span>
                            </label>
                            <select id="biteshipCourierCompany" onchange="onBiteshipCourierSelected(this.value)" class="w-full px-3 py-2 border border-outline-variant rounded-xl text-xs font-bold text-primary focus:ring-2 focus:ring-primary/20 focus:outline-none bg-white">
                                <!-- Opsi ekspedisi akan diisi otomatis dari database -->
                            </select>
                        </div>

                        <div class="space-y-1">
                            <label for="biteshipCourierType" class="block text-xs font-semibold text-on-surface">
                                Tipe Layanan
                            </label>
                            <select id="biteshipCourierType" class="w-full px-3 py-2 border border-outline-variant rounded-xl text-xs focus:ring-2 focus:ring-primary/20 focus:outline-none bg-white">
                                <option value="reg" selected>Reguler / Standard</option>
                                <option value="standard">Standard</option>
                                <option value="ez">EZ (J&T Regular)</option>
                                <option value="siuntung">SiUntung (SiCepat)</option>
                                <option value="yes">YES / Next Day</option>
                                <option value="cargo">Kargo / Trucking</option>
                            </select>
                        </div>

                        <div class="space-y-1">
                            <label for="biteshipPostalCode" class="block text-xs font-semibold text-on-surface">
                                Kode Pos Tujuan <span class="text-danger">*</span>
                            </label>
                            <input type="number" id="biteshipPostalCode" required placeholder="5 digit angka" class="w-full px-3 py-2 border border-outline-variant rounded-xl text-xs focus:ring-2 focus:ring-primary/20 focus:outline-none bg-white">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div class="space-y-1">
                            <label for="biteshipFinalStatus" class="block text-xs font-semibold text-on-surface">
                                Status Pesanan Setelah Mendapatkan Resi
                            </label>
                            <select id="biteshipFinalStatus" class="w-full px-3 py-2 border border-outline-variant rounded-xl text-xs focus:ring-2 focus:ring-primary/20 focus:outline-none bg-white">
                                <option value="4" selected>Shipped (Pesanan Dikirim)</option>
                                <option value="5">Delivered (Pesanan Selesai)</option>
                            </select>
                        </div>

                        <div class="space-y-1">
                            <label for="biteshipDestinationNote" class="block text-xs font-semibold text-on-surface">
                                Catatan Pengiriman (Opsional)
                            </label>
                            <input type="text" id="biteshipDestinationNote" placeholder="Instruksi untuk kurir..." class="w-full px-3 py-2 border border-outline-variant rounded-xl text-xs focus:ring-2 focus:ring-primary/20 focus:outline-none bg-white">
                        </div>
                    </div>

                    <div id="biteshipError" class="hidden p-3 bg-red-50 border border-red-200 rounded-xl text-xs text-danger font-medium flex items-center gap-2">
                        <span class="material-symbols-outlined text-[16px]">error</span>
                        <span id="biteshipErrorText">Terjadi kendala saat menghubungi vendor ekspedisi.</span>
                    </div>

                    <div class="flex justify-end gap-2 pt-3 border-t border-outline-variant/20">
                        <button type="button" onclick="closeResiModal()" class="px-4 py-2 border border-outline-variant rounded-xl text-xs font-medium text-on-surface-variant hover:bg-surface-container transition-colors">
                            Batal
                        </button>
                        <button type="submit" id="hitBiteshipBtn" class="px-5 py-2 bg-gradient-to-r from-amber-600 to-amber-700 hover:from-amber-700 hover:to-amber-800 text-white rounded-xl text-xs font-bold shadow-md hover:shadow-lg transition-all flex items-center gap-1.5 active:scale-95">
                            <span class="material-symbols-outlined text-[16px]" id="hitBiteshipIcon">cloud_sync</span>
                            <span id="hitBiteshipText">Get Resi Otomatis</span>
                        </button>
                    </div>
                </form>
            </div>

            <!-- TAB 3: TRACKING VIEW -->
            <div id="panelTracking" class="space-y-4 hidden">
                <div class="p-4 bg-surface-container-low rounded-xl border border-outline-variant/30 space-y-3">
                    <div class="flex items-center justify-between flex-wrap gap-3">
                        <div>
                            <div class="text-[10px] text-on-surface-variant font-bold uppercase tracking-wider">Nomor Resi (AWB)</div>
                            <div class="text-base font-bold font-mono text-primary select-all mt-0.5" id="trackingNumberDisplay">-</div>
                            <div class="text-[11px] text-on-surface-variant font-medium mt-0.5" id="trackingCourierDisplay">Kurir Pesanan</div>
                        </div>
                        <div class="flex items-center gap-2 flex-wrap">
                            <span id="trackingStatusBadge" class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-gray-100 text-gray-700 border border-gray-200">
                                <span class="material-symbols-outlined text-[15px]" id="trackingStatusIcon">local_shipping</span>
                                <span id="trackingStatusText">Menunggu Log</span>
                            </span>
                            <a id="externalTrackingLink" href="#" target="_blank" class="hidden px-3 py-1.5 bg-blue-50 hover:bg-blue-100 text-blue-700 border border-blue-200 rounded-lg text-xs font-semibold flex items-center gap-1 transition-colors" title="Buka tautan pelacakan resmi ekspedisi">
                                <span class="material-symbols-outlined text-[15px]">open_in_new</span>
                                <span>Lacak di Website Ekspedisi</span>
                            </a>
                            <button type="button" onclick="fetchBiteshipTracking()" class="px-3 py-1.5 bg-primary/10 hover:bg-primary/20 text-primary rounded-lg text-xs font-semibold flex items-center gap-1 transition-colors" title="Perbarui Status Pelacakan">
                                <span class="material-symbols-outlined text-[15px]">refresh</span>
                                <span>Muat Ulang</span>
                            </button>
                        </div>
                    </div>
                    <div class="flex items-center justify-between text-[11px] text-on-surface-variant pt-2.5 border-t border-outline-variant/30 flex-wrap gap-2">
                        <div class="flex items-center gap-1.5 text-emerald-700 font-medium">
                            <span class="material-symbols-outlined text-[15px]">verified</span>
                            <span>Pembaruan status pengiriman tersinkronisasi otomatis dari ekspedisi</span>
                        </div>
                        <div id="trackingLastUpdated" class="text-[10px] text-on-surface-variant/80 font-mono"></div>
                    </div>
                </div>

                <div id="trackingLoading" class="hidden py-8 text-center text-xs text-on-surface-variant">
                    <div class="inline-block animate-spin w-6 h-6 border-2 border-primary border-t-transparent rounded-full mb-2"></div>
                    <p>Memuat status pelacakan dari log webhook kurir...</p>
                </div>

                <div id="trackingEmpty" class="hidden py-6 text-center text-xs text-on-surface-variant bg-surface-container-lowest rounded-xl border border-dashed border-outline-variant/60">
                    <span class="material-symbols-outlined text-[28px] opacity-40 mb-1">inventory_2</span>
                    <p id="trackingEmptyText">Belum ada riwayat log checkpoint untuk resi ini.</p>
                </div>

                <div id="trackingTimeline" class="space-y-3 relative pl-6 border-l-2 border-primary/20 ml-3">
                    <!-- Events will be populated dynamically -->
                </div>
            </div>

        </div>
    </div>
</div>

<script>
window.ordersCache = window.ordersCache || {};
let currentActiveOrderData = null;
const biteshipCouriersCatalog = @json(\App\Services\BiteshipService::getSupportedCouriers());

function formatHumanErrorMessage(raw) {
    if (!raw) return 'Terjadi kendala saat memproses permintaan.';
    let msg = typeof raw === 'object' ? (raw.message || JSON.stringify(raw)) : String(raw);
    if (msg.includes('SQLSTATE') || msg.includes('violates foreign key') || msg.includes('constraint')) {
        return 'Data relasi (User / Gudang / Kurir) belum sinkron pada sistem. Silakan periksa kembali profil pengguna Anda atau hubungi admin.';
    }
    if (msg.includes('Reference id has already been used')) {
        return 'Nomor resi untuk pesanan ini sudah pernah diterbitkan. Pengambilan resi otomatis tidak dapat diulang.';
    }
    if (msg.includes('invalid or missing postal code')) {
        return 'Kode pos tujuan tidak valid atau tidak didukung oleh kurir yang dipilih. Pastikan kode pos 5 digit sesuai wilayah tujuan.';
    }
    if (msg.includes('Biteship')) {
        msg = msg.replace(/Biteship/gi, 'vendor ekspedisi');
    }
    return msg;
}

function switchFulfillmentTab(tab) {
    if (tab === 'biteship' && currentActiveOrderData?.is_kurir_toko) {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'warning',
                title: 'Pengiriman Kurir Toko',
                text: 'Pesanan Kurir Toko dikirimkan oleh armada internal toko dan hanya dapat menggunakan Input Resi Manual.',
                confirmButtonColor: '#1e3a8a',
                confirmButtonText: 'Buka Input Resi Manual'
            });
        }
        tab = 'manual';
    }

    const tabManual = document.getElementById('tabBtnManual');
    const tabBiteship = document.getElementById('tabBtnBiteship');
    const tabTracking = document.getElementById('tabBtnTracking');

    const panelManual = document.getElementById('panelManual');
    const panelBiteship = document.getElementById('panelBiteship');
    const panelTracking = document.getElementById('panelTracking');

    [tabManual, tabBiteship, tabTracking].forEach(el => {
        if (!el) return;
        el.classList.remove('border-primary', 'text-primary', 'bg-white');
        el.classList.add('border-transparent', 'text-on-surface-variant');
    });

    [panelManual, panelBiteship, panelTracking].forEach(el => {
        if (!el) return;
        el.classList.add('hidden');
    });

    if (tab === 'biteship') {
        tabBiteship.classList.add('border-primary', 'text-primary', 'bg-white');
        tabBiteship.classList.remove('border-transparent', 'text-on-surface-variant');
        panelBiteship.classList.remove('hidden');
    } else if (tab === 'tracking') {
        tabTracking.classList.add('border-primary', 'text-primary', 'bg-white');
        tabTracking.classList.remove('border-transparent', 'text-on-surface-variant');
        panelTracking.classList.remove('hidden');
        fetchBiteshipTracking();
    } else {
        tabManual.classList.add('border-primary', 'text-primary', 'bg-white');
        tabManual.classList.remove('border-transparent', 'text-on-surface-variant');
        panelManual.classList.remove('hidden');
    }
}

function onBiteshipCourierSelected(courierKey) {
    const typeSelect = document.getElementById('biteshipCourierType');
    if (!typeSelect) return;
    typeSelect.innerHTML = '';

    const cleanKey = (courierKey || '').toLowerCase();
    let courierConfig = biteshipCouriersCatalog[cleanKey] || null;
    if (!courierConfig) {
        for (const item of Object.values(biteshipCouriersCatalog)) {
            if ((item.code && item.code.toLowerCase() === cleanKey) || (item.courier_company && item.courier_company.toLowerCase() === cleanKey)) {
                courierConfig = item;
                break;
            }
        }
    }

    if (courierConfig && courierConfig.services && Object.keys(courierConfig.services).length > 0) {
        Object.entries(courierConfig.services).forEach(([svcKey, svcName]) => {
            const opt = document.createElement('option');
            opt.value = svcKey;
            opt.textContent = svcName;
            if (svcKey === courierConfig.default_service) opt.selected = true;
            typeSelect.appendChild(opt);
        });
    } else {
        typeSelect.innerHTML = '<option value="reg" selected>Reguler / Standard</option>';
    }
}

function openResiModal(order, defaultTab = 'manual') {
    if (typeof order === 'string') {
        if (window.ordersCache && window.ordersCache[order]) {
            order = window.ordersCache[order];
        } else {
            try { 
                order = JSON.parse(order); 
            } catch (e) { 
                order = { id: order }; 
            }
        }
    }
    if (!order) return;
    if (order.id) {
        window.ordersCache[order.id] = order;
    }
    currentActiveOrderData = order;

    const isKurirToko = !!(order.is_kurir_toko || (order.courier_type === 'toko') || (order.courier_code === 'kurir_toko') || (order.courier_name && order.courier_name.toLowerCase().includes('toko')));
    order.is_kurir_toko = isKurirToko;

    const kurirTokoNotice = document.getElementById('kurirTokoNotice');
    const tabBadgeKurirToko = document.getElementById('tabBadgeKurirToko');
    const tabBtnBiteship = document.getElementById('tabBtnBiteship');
    const panelBiteshipKurirTokoBlock = document.getElementById('panelBiteshipKurirTokoBlock');
    const biteshipShipmentForm = document.getElementById('biteshipShipmentForm');

    if (isKurirToko) {
        if (kurirTokoNotice) kurirTokoNotice.classList.remove('hidden');
        if (tabBadgeKurirToko) tabBadgeKurirToko.classList.remove('hidden');
        if (tabBtnBiteship) tabBtnBiteship.classList.add('hidden');
        if (panelBiteshipKurirTokoBlock) panelBiteshipKurirTokoBlock.classList.remove('hidden');
        if (biteshipShipmentForm) biteshipShipmentForm.classList.add('hidden');

        // Kurir Toko HANYA BISA INPUT RESI MANUAL
        if (defaultTab === 'biteship') {
            defaultTab = 'manual';
        }
    } else {
        if (kurirTokoNotice) kurirTokoNotice.classList.add('hidden');
        if (tabBadgeKurirToko) tabBadgeKurirToko.classList.add('hidden');
        if (tabBtnBiteship) tabBtnBiteship.classList.remove('hidden');
        if (panelBiteshipKurirTokoBlock) panelBiteshipKurirTokoBlock.classList.add('hidden');
        if (biteshipShipmentForm) biteshipShipmentForm.classList.remove('hidden');
    }

    document.getElementById('modalOrderBadge').textContent = '#' + (order.order_number || order.id || '');
    document.getElementById('formOrderId').value = order.id || '';
    document.getElementById('formTrackingNumber').value = order.resi || '';

    const courierName = order.courier_name || 'Kurir Pesanan';
    const courierCode = (order.courier_code || order.courier_name || '').toLowerCase();

    // 1. Populate Manual Courier Select (#formCourierId) dari katalog DB
    const manualCourierSelect = document.getElementById('formCourierId');
    if (manualCourierSelect) {
        manualCourierSelect.innerHTML = '';
        let foundManualMatch = false;
        Object.entries(biteshipCouriersCatalog).forEach(([key, item]) => {
            const opt = document.createElement('option');
            opt.value = item.id || key;
            opt.textContent = item.name + (item.courier_type === 'toko' ? ' (Kurir Toko)' : '');
            if (item.id === order.courier_id || (item.code && item.code.toLowerCase() === courierCode)) {
                opt.selected = true;
                foundManualMatch = true;
            } else if (isKurirToko && !foundManualMatch && item.courier_type === 'toko') {
                opt.selected = true;
                foundManualMatch = true;
            }
            manualCourierSelect.appendChild(opt);
        });
        if (!foundManualMatch && order.courier_name) {
            const opt = document.createElement('option');
            opt.value = order.courier_id || '';
            opt.textContent = order.courier_name;
            opt.selected = true;
            manualCourierSelect.prepend(opt);
        }
    }

    // Prefill Biteship Destination summary
    if (document.getElementById('biteshipCustomerName')) {
        document.getElementById('biteshipCustomerName').textContent = order.customer_name || 'Pelanggan';
    }
    if (document.getElementById('biteshipCustomerPhone')) {
        document.getElementById('biteshipCustomerPhone').textContent = order.customer_phone || '-';
    }
    if (document.getElementById('biteshipCustomerAddress')) {
        document.getElementById('biteshipCustomerAddress').textContent = order.shipping_address || 'Alamat tidak tersedia';
    }
    if (document.getElementById('biteshipPostalCode')) {
        document.getElementById('biteshipPostalCode').value = order.postal_code || '';
    }

    // 2. Tentukan kecocokan ekspedisi awal pesanan
    let matchedCompanyKey = null;
    for (const [key, item] of Object.entries(biteshipCouriersCatalog)) {
        if (item.id === order.courier_id || key === courierCode || (item.code && item.code.toLowerCase() === courierCode) || (order.courier_name && item.name.toLowerCase().includes(order.courier_name.toLowerCase()))) {
            matchedCompanyKey = key;
            break;
        }
    }

    if (!matchedCompanyKey) {
        if (courierCode.includes('j&t') || courierCode.includes('jnt') || courierCode.includes('jt')) matchedCompanyKey = 'jnt';
        else if (courierCode.includes('sicepat')) matchedCompanyKey = 'sicepat';
        else if (courierCode.includes('pos')) matchedCompanyKey = 'pos';
        else if (courierCode.includes('tiki')) matchedCompanyKey = 'tiki';
        else if (courierCode.includes('anteraja')) matchedCompanyKey = 'anteraja';
        else if (courierCode.includes('ninja')) matchedCompanyKey = 'ninja';
        else if (courierCode.includes('lion')) matchedCompanyKey = 'lion';
        else if (courierCode.includes('paxel')) matchedCompanyKey = 'paxel';
        else if (courierCode.includes('jne')) matchedCompanyKey = 'jne';
        else matchedCompanyKey = Object.keys(biteshipCouriersCatalog)[0] || 'jne';
    }

    // 3. Populate Biteship Courier Select (#biteshipCourierCompany) dari data DB
    const biteshipCompanySelect = document.getElementById('biteshipCourierCompany');
    if (biteshipCompanySelect) {
        biteshipCompanySelect.innerHTML = '';
        Object.entries(biteshipCouriersCatalog).forEach(([key, item]) => {
            const opt = document.createElement('option');
            opt.value = item.courier_company || item.code || key;
            opt.textContent = item.name;
            if (key === matchedCompanyKey || opt.value === matchedCompanyKey) {
                opt.selected = true;
            }
            biteshipCompanySelect.appendChild(opt);
        });

        // Trigger render tipe layanan untuk kurir terpilih
        onBiteshipCourierSelected(biteshipCompanySelect.value || matchedCompanyKey);
    }

    // Handle Already Issued Biteship Resi
    const hasBiteshipResi = !!(order.has_biteship_resi || (order.meta && order.meta.biteship_order_id) || (order.resi && (order.fulfillment_type === 'biteship' || (order.meta && order.meta.fulfillment_type === 'biteship'))));
    const biteshipIssuedNotice = document.getElementById('biteshipAlreadyIssuedNotice');
    const hitBiteshipBtn = document.getElementById('hitBiteshipBtn');
    const hitBiteshipText = document.getElementById('hitBiteshipText');

    if (hasBiteshipResi) {
        if (biteshipIssuedNotice) biteshipIssuedNotice.classList.remove('hidden');
        if (hitBiteshipBtn) {
            hitBiteshipBtn.disabled = true;
            hitBiteshipBtn.classList.add('opacity-50', 'cursor-not-allowed');
            hitBiteshipBtn.classList.remove('active:scale-95');
        }
        if (hitBiteshipText) hitBiteshipText.textContent = 'Resi Ekspedisi Sudah Terbit';
        // Auto-switch to tracking view if user opens modal
        if (defaultTab === 'biteship' && order.resi) {
            defaultTab = 'tracking';
        }
    } else {
        if (biteshipIssuedNotice) biteshipIssuedNotice.classList.add('hidden');
        if (hitBiteshipBtn) {
            hitBiteshipBtn.disabled = false;
            hitBiteshipBtn.classList.remove('opacity-50', 'cursor-not-allowed');
            hitBiteshipBtn.classList.add('active:scale-95');
        }
        if (hitBiteshipText) hitBiteshipText.textContent = 'Get Resi Otomatis';
    }

    // Handle Order status >= 4 (Shipped / Delivered): Lock manual resi input as well!
    const isShippedOrDelivered = (parseInt(order.status) === 4 || parseInt(order.status) === 5);
    const manualLockedNotice = document.getElementById('manualResiLockedNotice');
    const saveResiBtn = document.getElementById('saveResiBtn');
    const inputTrackingNumber = document.getElementById('formTrackingNumber');
    const selectCourierId = document.getElementById('formCourierId');
    const selectTargetStatus = document.getElementById('formStatusTarget');

    if (isShippedOrDelivered) {
        if (manualLockedNotice) manualLockedNotice.classList.remove('hidden');
        if (saveResiBtn) {
            saveResiBtn.disabled = true;
            saveResiBtn.classList.add('opacity-50', 'cursor-not-allowed');
            saveResiBtn.innerHTML = '<span class="material-symbols-outlined text-[16px]">lock</span><span>Resi Terkunci (' + (order.status_label || 'Shipped') + ')</span>';
        }
        if (inputTrackingNumber) {
            inputTrackingNumber.disabled = true;
            inputTrackingNumber.classList.add('bg-slate-100', 'cursor-not-allowed');
        }
        if (selectCourierId) {
            selectCourierId.disabled = true;
            selectCourierId.classList.add('bg-slate-100', 'cursor-not-allowed');
        }
        if (selectTargetStatus) {
            selectTargetStatus.disabled = true;
            selectTargetStatus.classList.add('bg-slate-100', 'cursor-not-allowed');
        }
        // If opening default tab manual or biteship, switch to tracking if resi exists
        if ((defaultTab === 'manual' || defaultTab === 'biteship') && (order.resi || order.delivery_status)) {
            defaultTab = 'tracking';
        }
    } else {
        if (manualLockedNotice) manualLockedNotice.classList.add('hidden');
        if (saveResiBtn) {
            saveResiBtn.disabled = false;
            saveResiBtn.classList.remove('opacity-50', 'cursor-not-allowed');
            saveResiBtn.innerHTML = '<span class="material-symbols-outlined text-[16px]">save</span><span>Simpan Resi Manual</span>';
        }
        if (inputTrackingNumber) {
            inputTrackingNumber.disabled = false;
            inputTrackingNumber.classList.remove('bg-slate-100', 'cursor-not-allowed');
        }
        if (selectCourierId) {
            selectCourierId.disabled = false;
            selectCourierId.classList.remove('bg-slate-100', 'cursor-not-allowed');
        }
        if (selectTargetStatus) {
            selectTargetStatus.disabled = false;
            selectTargetStatus.classList.remove('bg-slate-100', 'cursor-not-allowed');
        }
    }

    // Toggle tracking tab button
    const tabTracking = document.getElementById('tabBtnTracking');
    if (order.resi || order.delivery_status) {
        tabTracking.classList.remove('hidden');
        document.getElementById('trackingNumberDisplay').textContent = order.resi || 'Menunggu Resi Kurir';
        if (order.courier_name && document.getElementById('trackingCourierDisplay')) {
            document.getElementById('trackingCourierDisplay').textContent = order.courier_name;
        }
        if (order.delivery_status_label && document.getElementById('trackingStatusText')) {
            document.getElementById('trackingStatusText').textContent = order.delivery_status_label;
            const badge = document.getElementById('trackingStatusBadge');
            if (badge && order.delivery_status_badge_class) {
                badge.className = 'inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold border ' + order.delivery_status_badge_class;
            }
        }
        if (order.delivery_updated_at && document.getElementById('trackingLastUpdated')) {
            document.getElementById('trackingLastUpdated').textContent = 'Pembaruan: ' + order.delivery_updated_at;
        }
    } else {
        tabTracking.classList.add('hidden');
    }

    document.getElementById('formResiError').classList.add('hidden');
    document.getElementById('biteshipError').classList.add('hidden');
    resetCopyButton();

    switchFulfillmentTab(defaultTab);

    const modal = document.getElementById('resiModal');
    modal.classList.remove('hidden');
    modal.classList.add('flex');
}

function closeResiModal() {
    const modal = document.getElementById('resiModal');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
}

function resetCopyButton() {
    const copyIcon = document.getElementById('copyResiIcon');
    const copyText = document.getElementById('copyResiText');
    if (copyIcon) copyIcon.textContent = 'content_copy';
    if (copyText) copyText.textContent = 'Salin';
}

function copyResiCode() {
    const code = document.getElementById('formTrackingNumber').value.trim();
    if (!code) return;

    navigator.clipboard.writeText(code).then(() => {
        const copyIcon = document.getElementById('copyResiIcon');
        const copyText = document.getElementById('copyResiText');
        if (copyIcon) copyIcon.textContent = 'check';
        if (copyText) copyText.textContent = 'Tersalin!';
        setTimeout(resetCopyButton, 2000);
    });
}

// 1. Submit Manual Resi Form
async function submitResiForm(e) {
    e.preventDefault();
    const orderId = document.getElementById('formOrderId').value;
    const trackingNumber = document.getElementById('formTrackingNumber').value.trim();
    const courierId = document.getElementById('formCourierId').value;
    const statusTarget = document.getElementById('formStatusTarget').value;
    const errorDiv = document.getElementById('formResiError');
    const errorText = document.getElementById('formResiErrorText');
    const saveBtn = document.getElementById('saveResiBtn');

    if (currentActiveOrderData && (parseInt(currentActiveOrderData.status) === 4 || parseInt(currentActiveOrderData.status) === 5)) {
        const msg = 'Pesanan sudah berstatus Dikirim atau Diterima. Nomor resi sudah terkunci dan tidak dapat diubah lagi.';
        errorText.textContent = msg;
        errorDiv.classList.remove('hidden');
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'warning',
                title: 'Resi Terkunci',
                text: msg,
                confirmButtonColor: '#1e3a8a'
            });
        }
        return;
    }

    if (!trackingNumber) {
        const msg = 'Nomor resi pengiriman atau surat jalan wajib diisi.';
        errorText.textContent = msg;
        errorDiv.classList.remove('hidden');
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'warning',
                title: 'Nomor Resi Kosong',
                text: msg,
                confirmButtonColor: '#1e3a8a'
            });
        }
        return;
    }
    errorDiv.classList.add('hidden');

    saveBtn.disabled = true;
    saveBtn.innerHTML = '<div class="inline-block animate-spin w-3 h-3 border-2 border-white border-t-transparent rounded-full mr-1.5"></div> Menyimpan...';

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}';

    try {
        const response = await fetch(`/orders/${orderId}/update-resi`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify({
                tracking_number: trackingNumber,
                courier_id: courierId || null,
                status: statusTarget ? parseInt(statusTarget) : 4
            })
        });

        const data = await response.json();
        if (response.ok && data.success) {
            closeResiModal();
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'success',
                    title: 'Resi Berhasil Disimpan!',
                    html: `Nomor resi: <b class="font-mono text-primary text-base">${trackingNumber}</b><br><span class="text-xs text-gray-500">Status pesanan diperbarui menjadi: <b>${data.status_label || 'Dikirim'}</b></span>`,
                    confirmButtonColor: '#1e3a8a',
                    timer: 2500,
                    timerProgressBar: true
                });
            }
            updateOrderUIElements(orderId, data);
        } else {
            const err = formatHumanErrorMessage(data.message || 'Gagal menyimpan nomor resi.');
            errorText.textContent = err;
            errorDiv.classList.remove('hidden');
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'warning',
                    title: 'Gagal Menyimpan Resi',
                    text: err,
                    confirmButtonColor: '#1e3a8a'
                });
            }
        }
    } catch (err) {
        console.error(err);
        const errMsg = 'Terjadi kesalahan sistem saat menyimpan nomor resi.';
        errorText.textContent = errMsg;
        errorDiv.classList.remove('hidden');
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'error',
                title: 'Gangguan Sistem',
                text: errMsg,
                confirmButtonColor: '#1e3a8a'
            });
        }
    } finally {
        saveBtn.disabled = false;
        saveBtn.innerHTML = '<span class="material-symbols-outlined text-[16px]">save</span><span>Simpan Resi Manual</span>';
    }
}

// 2. Submit Vendor Expedition Shipment (Get Resi Otomatis)
async function submitBiteshipForm(e) {
    e.preventDefault();
    const orderId = document.getElementById('formOrderId').value;
    const courierCompany = document.getElementById('biteshipCourierCompany').value;
    const courierType = document.getElementById('biteshipCourierType').value;
    const postalCode = document.getElementById('biteshipPostalCode').value.trim();
    const finalStatus = document.getElementById('biteshipFinalStatus').value;
    const destinationNote = document.getElementById('biteshipDestinationNote').value.trim();

    const errorDiv = document.getElementById('biteshipError');
    const errorText = document.getElementById('biteshipErrorText');
    const hitBtn = document.getElementById('hitBiteshipBtn');

    // Pengamanan Kurir Toko
    if (currentActiveOrderData?.is_kurir_toko) {
        const msg = 'Pesanan dengan armada Kurir Toko hanya dapat menggunakan Input Resi Manual. Pengambilan resi otomatis tidak didukung untuk kurir toko.';
        errorText.textContent = msg;
        errorDiv.classList.remove('hidden');
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'warning',
                title: 'Pengiriman Kurir Toko',
                text: msg,
                confirmButtonColor: '#1e3a8a',
                confirmButtonText: 'Buka Input Resi Manual'
            }).then(() => {
                switchFulfillmentTab('manual');
            });
        }
        return;
    }

    if (!postalCode || postalCode.length !== 5) {
        const msg = 'Kode pos tujuan harus berupa 5 digit angka untuk pemesanan ekspedisi.';
        errorText.textContent = msg;
        errorDiv.classList.remove('hidden');
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'warning',
                title: 'Kode Pos Tidak Sesuai',
                text: msg,
                confirmButtonColor: '#1e3a8a'
            });
        }
        return;
    }
    errorDiv.classList.add('hidden');

    hitBtn.disabled = true;
    hitBtn.innerHTML = '<div class="inline-block animate-spin w-3 h-3 border-2 border-white border-t-transparent rounded-full mr-1.5"></div> Menghubungi Vendor Ekspedisi...';

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}';

    try {
        const response = await fetch(`/orders/${orderId}/biteship-shipment`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify({
                courier_company: courierCompany,
                courier_type: courierType,
                destination_postal_code: parseInt(postalCode),
                final_status: finalStatus ? parseInt(finalStatus) : 4,
                destination_note: destinationNote || null
            })
        });

        const res = await response.json();
        if (response.ok && res.success) {
            if (currentActiveOrderData) {
                currentActiveOrderData.has_biteship_resi = true;
                currentActiveOrderData.resi = res.data?.tracking_number;
            }
            closeResiModal();
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'success',
                    title: 'Resi Berhasil Diterbitkan!',
                    html: `Nomor resi dari vendor ekspedisi:<br><b class="font-mono text-primary text-base">${res.data?.tracking_number || ''}</b><br><span class="text-xs text-gray-500">Ekspedisi: <b>${res.data?.courier_name || courierCompany.toUpperCase()}</b></span>`,
                    confirmButtonColor: '#1e3a8a',
                    timer: 2800,
                    timerProgressBar: true
                });
            }
            updateOrderUIElements(orderId, res.data);
        } else {
            const err = formatHumanErrorMessage(res.message || 'Gagal menerbitkan nomor resi dari pihak vendor ekspedisi.');
            errorText.textContent = err;
            errorDiv.classList.remove('hidden');
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'warning',
                    title: 'Gagal Mendapatkan Resi',
                    text: err,
                    confirmButtonColor: '#1e3a8a'
                });
            }
        }
    } catch (err) {
        console.error(err);
        const errMsg = 'Terjadi gangguan jaringan saat menghubungi sistem vendor ekspedisi.';
        errorText.textContent = errMsg;
        errorDiv.classList.remove('hidden');
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'error',
                title: 'Gangguan Koneksi',
                text: errMsg,
                confirmButtonColor: '#1e3a8a'
            });
        }
    } finally {
        hitBtn.disabled = false;
        hitBtn.innerHTML = '<span class="material-symbols-outlined text-[16px]">cloud_sync</span><span>Get Resi Otomatis</span>';
    }
}

// 3. Fetch Tracking (Webhook Logs)
async function fetchBiteshipTracking() {
    const orderId = document.getElementById('formOrderId')?.value;
    if (!orderId) return;

    const loading = document.getElementById('trackingLoading');
    const empty = document.getElementById('trackingEmpty');
    const timeline = document.getElementById('trackingTimeline');
    const extLink = document.getElementById('externalTrackingLink');

    if (loading) loading.classList.remove('hidden');
    if (empty) empty.classList.add('hidden');
    if (timeline) timeline.innerHTML = '';

    try {
        const response = await fetch(`/orders/${orderId}/biteship-tracking`);
        const result = await response.json();
        if (loading) loading.classList.add('hidden');

        if (response.ok && result.success && result.data) {
            const data = result.data;
            const events = data.events || [];

            if (data.resi && document.getElementById('trackingNumberDisplay')) {
                document.getElementById('trackingNumberDisplay').textContent = data.resi;
            }
            if (data.courier_name && document.getElementById('trackingCourierDisplay')) {
                document.getElementById('trackingCourierDisplay').textContent = data.courier_name;
            }
            if (data.status && document.getElementById('trackingStatusText')) {
                document.getElementById('trackingStatusText').textContent = data.status;
                const badge = document.getElementById('trackingStatusBadge');
                if (badge && data.status_badge_class) {
                    badge.className = 'inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold border ' + data.status_badge_class;
                }
            }
            if (data.status_icon && document.getElementById('trackingStatusIcon')) {
                document.getElementById('trackingStatusIcon').textContent = data.status_icon;
            }
            if (data.last_updated && document.getElementById('trackingLastUpdated')) {
                document.getElementById('trackingLastUpdated').textContent = 'Pembaruan: ' + data.last_updated;
            }

            if (extLink) {
                if (data.link) {
                    extLink.href = data.link;
                    extLink.classList.remove('hidden');
                } else {
                    extLink.classList.add('hidden');
                }
            }

            if (events.length === 0) {
                if (empty) {
                    empty.classList.remove('hidden');
                    const emptyText = document.getElementById('trackingEmptyText');
                    if (emptyText) emptyText.textContent = 'Paket baru didaftarkan. Belum ada riwayat checkpoint dari webhook kurir.';
                }
                return;
            }

            if (timeline) {
                timeline.innerHTML = events.map(function(ev, index) {
                    const isLatest = index === 0;
                    const dotClass = isLatest ? 'bg-primary ring-4 ring-primary/20' : 'bg-outline';
                    const titleClass = isLatest ? 'text-primary font-extrabold' : 'text-on-surface font-bold';
                    let payloadSection = '';
                    if (ev.payload && typeof ev.payload === 'object' && Object.keys(ev.payload).length > 0) {
                        payloadSection = `
                            <details class="mt-2 text-[10px] text-on-surface-variant font-mono group">
                                <summary class="cursor-pointer hover:underline text-primary inline-flex items-center gap-1">
                                    <span class="material-symbols-outlined text-[12px] group-open:rotate-90 transition-transform">chevron_right</span>
                                    <span>Lihat Payload Webhook (${ev.event || 'raw'})</span>
                                </summary>
                                <pre class="p-2.5 bg-surface-container-lowest border border-outline-variant/30 rounded-lg mt-1 overflow-x-auto text-[11px] leading-relaxed max-h-40">${JSON.stringify(ev.payload, null, 2)}</pre>
                            </details>
                        `;
                    }
                    return `
                        <div class="relative pb-4 last:pb-0">
                            <div class="absolute -left-[31px] top-0.5 w-3.5 h-3.5 rounded-full ${dotClass}"></div>
                            <div class="flex items-center justify-between gap-2 flex-wrap">
                                <div class="text-[12px] ${titleClass}">${ev.status || 'Status Checkpoint'}</div>
                                ${ev.event ? `<span class="px-1.5 py-0.5 bg-surface-container text-on-surface-variant rounded text-[9px] font-mono">${ev.event}</span>` : ''}
                            </div>
                            <div class="text-[10px] text-on-surface-variant mt-0.5">${ev.time || '-'} ${ev.location ? ' &bull; ' + ev.location : ''}</div>
                            <div class="text-xs text-on-surface mt-1 leading-snug">${ev.description || ''}</div>
                            ${payloadSection}
                        </div>
                    `;
                }).join('');
            }
        } else {
            if (empty) {
                empty.classList.remove('hidden');
                const emptyText = document.getElementById('trackingEmptyText');
                if (emptyText) emptyText.textContent = result.message || 'Tidak ada data tracking untuk nomor resi ini.';
            }
        }
    } catch (err) {
        console.error('Biteship tracking error:', err);
        if (loading) loading.classList.add('hidden');
        if (empty) {
            empty.classList.remove('hidden');
            const emptyText = document.getElementById('trackingEmptyText');
            if (emptyText) emptyText.textContent = 'Gagal memuat status tracking dari log webhook: ' + (err.message || 'Unknown error');
        }
    }
}

// 4. Update UI Elements on Success
function updateOrderUIElements(orderId, data) {
    if (currentActiveOrderData) {
        currentActiveOrderData.resi = data.tracking_number;
        currentActiveOrderData.courier_name = data.courier_name;
        if (data.status) currentActiveOrderData.status = data.status;
        if (data.status_label) currentActiveOrderData.status_label = data.status_label;
        if (data.status_badge_class) currentActiveOrderData.status_badge_class = data.status_badge_class;
        window.ordersCache[orderId] = currentActiveOrderData;
    }

    // Update in orders index
    const orderRowResiBadge = document.getElementById(`order-resi-badge-${orderId}`);
    if (orderRowResiBadge) {
        orderRowResiBadge.innerHTML = `
            <div class="flex items-center gap-1.5">
                <button type="button" onclick="openResiModal('${orderId}', 'tracking')" class="font-mono text-xs font-bold text-primary bg-primary/5 hover:bg-primary/10 px-2 py-0.5 rounded border border-primary/20 text-left transition-colors" title="Lihat Resi">
                    ${data.tracking_number}
                </button>
                <button type="button" onclick="openResiModal('${orderId}', 'tracking')" class="p-1 text-on-surface-variant hover:text-primary transition-colors" title="Lihat Resi">
                    <span class="material-symbols-outlined text-[16px]">visibility</span>
                </button>
            </div>
            <div class="flex items-center gap-1 text-[10px] text-on-surface-variant mt-1 flex-wrap">
                <span class="font-medium">${data.courier_name || 'Kurir'}</span>
                <span>&bull;</span>
                <span class="inline-flex items-center gap-0.5 px-1.5 py-0.2 rounded text-[9px] font-bold border bg-cyan-100 text-cyan-800 border-cyan-200">
                    ${data.status_label || 'Resi Diterbitkan'}
                </span>
            </div>
        `;
    }

    // Update in show.blade.php
    const showResiBadge = document.getElementById('show-order-resi-code');
    if (showResiBadge) {
        showResiBadge.textContent = data.tracking_number;
    }
    const showDeliveryBadge = document.getElementById('show-order-delivery-status');
    if (showDeliveryBadge) {
        showDeliveryBadge.textContent = 'Resi Diterbitkan';
    }
    const showCourierBadge = document.getElementById('show-order-courier-name');
    if (showCourierBadge) {
        showCourierBadge.textContent = data.courier_name;
    }
    const showHeaderBtn = document.getElementById('show-header-resi-text');
    if (showHeaderBtn) {
        showHeaderBtn.textContent = 'Resi: ' + data.tracking_number;
    }
    const showStatusBadge = document.getElementById('show-order-status-badge');
    if (showStatusBadge && data.status_label) {
        showStatusBadge.textContent = data.status_label;
        if (data.status_badge_class) {
            showStatusBadge.className = 'inline-flex items-center gap-1 px-2.5 py-1 rounded-md text-xs font-bold ' + data.status_badge_class;
        }
    }

    // Reload page after brief delay if in show view to refresh entire order card
    if (window.location.pathname.includes('/orders/') && !window.location.pathname.endsWith('/orders')) {
        setTimeout(() => { window.location.reload(); }, 1800);
    }
}

document.getElementById('resiModal')?.addEventListener('click', function(e) {
    if (e.target === this) {
        closeResiModal();
    }
});
</script>

