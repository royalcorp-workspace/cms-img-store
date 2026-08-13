# Rencana & Strategi Pembuatan Custom CRM & Email Marketing

Membangun sistem *Customer Relationship Management* (CRM) dan Email Marketing (sering disebut *convergence*) sendiri adalah langkah yang sangat cerdas. Meskipun pada awalnya membutuhkan *effort* pengembangan, dalam jangka panjang ini akan menghemat biaya *subscription* bulanan (seperti Mailchimp, ActiveCampaign, dsb) dan memberikan Anda kendali penuh atas data pelanggan.

Berikut adalah *insight* dan rancangan arsitektur untuk membangun sistem ini secara "gratis" (self-hosted) di dalam ekosistem aplikasi Anda yang sudah ada.

---

## 1. Tantangan & Solusi (The "Why & How")

| Tantangan | Solusi Custom CRM |
| :--- | :--- |
| **Email Masuk Spam** | Jangan gunakan SMTP bawaan server (Postfix). Gunakan layanan pihak ketiga tier gratis yang bereputasi tinggi seperti **Resend** (Gratis 3.000 email/bulan), **Brevo/Sendinblue** (300 email/hari), atau **Mailtrap**. |
| **Server Timeout saat Blast** | Gunakan sistem **Queue & Worker**. Jangan mengirim 1.000 email secara sinkron. Masukkan ke dalam antrean (Redis) dan biarkan *worker* mengirimkannya satu per satu di *background*. |
| **Desain Template Email** | Integrasikan editor simpel (seperti GrapesJS) atau gunakan MJML untuk menghasilkan HTML email yang responsif di semua perangkat (Gmail, Outlook, Apple Mail). |

---

## 2. Arsitektur Database (Konsep Dasar)

Untuk memulai, kita hanya membutuhkan beberapa tabel inti yang bisa disematkan ke dalam database `img-db` Anda:

### A. CRM & Segmentasi
*   **`customers` / `leads`**: Sudah ada, tinggal ditambahkan kolom `tags` atau relasi ke tabel `customer_segments`.
*   **`segments`**: Tabel untuk mengelompokkan user (contoh: "VIP", "Pernah Beli Kasur", "Cart Abandoner").

### B. Email Marketing
*   **`campaigns`**: Menyimpan data promosi (Judul, Subjek, Jadwal Kirim, Status: *Draft, Scheduled, Processing, Completed*).
*   **`email_templates`**: Menyimpan desain HTML murni atau *Blade/Jinja template*.
*   **`campaign_logs`**: Menyimpan status setiap email yang dikirim (Siapa penerimanya, status terkirim, *bounced*, dll).

---

## 3. Fitur Utama (Minimum Viable Product - MVP)

Untuk versi awal yang gratis dan fungsional, kita fokus pada fitur berikut:

1. **Manajemen Kontak & Tagging** 
   Kemampuan di CMS untuk melihat daftar pelanggan, riwayat belanja mereka, dan memberikan "Tag" (misal: *Loyal Customer*).
2. **Email Template Builder**
   Halaman di CMS untuk membuat kerangka email menggunakan editor teks biasa atau *Rich Text* sederhana.
3. **Broadcast / Blast Email**
   Memilih segment/tag tertentu, merangkai subjek, dan menekan tombol "Kirim Sekarang" atau "Jadwalkan".
4. **Tracking Dasar (Opsional di Awal)**
   Menyisipkan *tracking pixel* (gambar 1x1 transparan) untuk melacak apakah email dibuka (*Open Rate*).

---

## 4. Pemilihan Teknologi di Ekosistem Anda

Karena Anda memiliki **Laravel (CMS & Web)** dan **FastAPI (Mobile Backend)**, Anda punya 2 pilihan di mana sistem ini akan di-*host*:

> [!TIP]
> **Rekomendasi Utama: Bangun di Laravel (CMS `cms-img-store`)**
> Laravel memiliki sistem **Mail**, **Queues**, dan **Task Scheduling (Cron)** yang sangat *powerful* dan mudah di-setup (menggunakan Redis yang sudah Anda miliki). Selain itu, antarmuka admin (UI) sudah ada di CMS, sehingga Anda tinggal menambahkan menu "CRM" dan "Campaigns".

**Alur Kerja (*Workflow*) Broadcast Email di Laravel:**
1. Admin membuat *Campaign* di CMS.
2. Admin menekan "Kirim". Controller akan mengambil 1.000 email pelanggan.
3. Controller melakukan *looping*, tapi tidak langsung mengirim, melainkan melakukan `CampaignEmailJob::dispatch($customer, $campaign)`.
4. Proses di layar Admin selesai dalam 1 detik.
5. Di belakang layar (Terminal server), perintah `php artisan queue:work` akan memproses antrean tersebut, mengirimkan 1 email ke SMTP provider setiap dolarnya sampai habis.

---

## 5. Langkah Selanjutnya

Jika Anda setuju dengan pendekatan ini, kita bisa memulainya dengan tahapan *coding* berikut:

1. **Fase 1: Setup SMTP & Queue**
   *   Mendaftar layanan SMTP gratis (saya sarankan **Resend** atau **Brevo**).
   *   Mengonfigurasi `QUEUE_CONNECTION=redis` di `cms-img-store`.
2. **Fase 2: Database & UI CMS**
   *   Membuat migrasi tabel `campaigns`, `email_templates`, dan `campaign_logs`.
   *   Membuat halaman CRUD di CMS.
3. **Fase 3: Logic Blast Email**
   *   Membuat Laravel Job untuk pengiriman asinkron.
   *   Membuat fitur *scheduler* untuk email yang dijadwalkan.

---

## 6. Pipeline Penjualan (CRM Kanban Board)

Selain fitur Email Marketing, sebuah CRM yang utuh membutuhkan manajemen prospek (Leads Management) secara visual. Pendekatan terbaik untuk ini adalah menggunakan **Kanban Board** (seperti Trello) di mana setiap kartu merepresentasikan 1 pelanggan atau 1 transaksi (Deals).

### A. Tahapan Pipeline (Stages) Rekomendasi
Untuk bisnis ritel kasur dan *sleep accessories* (IMG), siklus penjualan biasanya tidak terlalu panjang namun membutuhkan *follow-up*. Berikut rekomendasi *stages* / tahapan kolom di Kanban Anda:

1. 📥 **New Leads (Prospek Baru)**
   *   Semua *inquiries* baru yang masuk via Website, WhatsApp, atau Chat API (FastAPI) otomatis masuk ke kolom ini.
   *   Admin wajib memberikan respon awal.
2. ⏳ **Pending / Follow-up**
   *   Leads yang sudah dihubungi namun belum merespon, atau sedang berdiskusi dengan pasangannya terkait pemilihan model kasur.
   *   *Action*: Butuh di-follow-up kembali dalam 1-3 hari.
3. ⚙️ **On Process (Sedang Diproses)**
   *   Pelanggan sudah setuju untuk membeli, sedang dalam tahap pembayaran, pengajuan cicilan, atau menunggu konfirmasi stok dari gudang.
4. 🚚 **Delivery (Pengiriman)**
   *   *Tambahan khusus bisnis ritel*: Kasur adalah barang besar yang membutuhkan pengiriman khusus. Kolom ini untuk memantau pesanan yang sedang diatur jadwal pengirimannya.
5. 🤝 **Closing / Won (Berhasil)**
   *   Transaksi selesai, barang sudah diterima. Pelanggan otomatis akan mendapatkan "Tag" *Loyal Customer* agar masuk ke dalam segmen otomatis untuk *Email Marketing* (misal: penawaran aksesoris bantal sebulan kemudian).
6. ❌ **Lost (Gagal)**
   *   Leads yang batal membeli (karena harga, beli di tempat lain, dsb). Harus ada *alasan batal* (Lost Reason) agar manajemen bisa melakukan evaluasi.

### B. Konsep Database untuk Kanban
*   **`pipelines`** (opsional, jika Anda ingin punya banyak jenis pipeline).
*   **`stages`** (menyimpan master kolom: New, Pending, Process, Won, Lost, beserta urutan/posisinya).
*   **`deals`** / **`leads`** (menyimpan data transaksi/orang yang sedang diproses). Berisi relasi ke `customer_id`, `stage_id`, nilai transaksi (Rp), dan *notes* (catatan admin).

### C. Alur Kerja (Workflow) Kanban + Email
Gabungan (Konvergensi) antara Kanban dan Email Marketing adalah letak **kekuatan utama** sistem ini:
*   Jika sebuah tiket dipindahkan dari "Delivery" ke "Closing", sistem otomatis menjadwalkan **Email Terima Kasih** beserta voucher diskon *next purchase* 3 hari setelahnya.
*   Jika tiket dipindahkan ke "Lost" dengan alasan "Terlalu Mahal", sistem bisa menjadwalkan **Email Promo Diskon Clearance** bulan depannya.

---
*Silakan baca pembaruan Kanban ini. Jika alur pipeline-nya sudah sesuai dengan realita operasional tim sales/CS IMG di lapangan, kita bisa kunci konsep ini!*

---

## 7. Konvergensi & Pengukuran Efektivitas (Tracking Email)

Bagian terpenting dari sebuah sistem *Email Marketing* bukanlah sekadar "mengirim email", melainkan **Konvergensi**—yaitu kemampuan mengukur sejauh mana email tersebut efektif menghasilkan penjualan, dan bagaimana kanal email terhubung dengan aktivitas *website/CMS*. 

Untuk mengetahui apakah sebuah email efektif atau tidak, kita akan mengimplementasikan 4 pilar pelacakan (*tracking*):

### A. *Open Rate* (Melacak Siapa yang Membuka Email)
**Cara Kerja:** Sistem secara otomatis menyisipkan gambar transparan 1x1 pixel ( *Tracking Pixel* ) di bagian bawah setiap email yang dikirim.
*   **Proses:** Ketika pelanggan membuka email (dan memuat gambar), aplikasi email mereka akan mengunduh pixel tersebut dari server kita. Server mencatat waktu dan mengubah status `campaign_logs` menjadi **"Opened"**.
*   **Fungsi:** Anda jadi tahu persis persentase prospek yang benar-benar membaca penawaran Anda.

### B. *Click-Through Rate / CTR* (Melacak Klik Tombol/Link)
**Cara Kerja:** Setiap *link* atau tombol (misal: "Beli Kasur Sekarang") di dalam *template* email tidak langsung mengarah ke halaman produk.
*   **Proses:** *Link* akan diubah menjadi *link tracking* khusus (contoh: `img.com/track/xyz123?redirect=url_asli`). Saat diklik, server mencatat status **"Clicked"**, lalu dalam hitungan milidetik me-redirect pelanggan ke URL tujuan.
*   **Fungsi:** Mengetahui produk/promo mana yang paling banyak menarik minat pelanggan.

### C. *Conversion Tracking* (Konvergensi Penjualan)
**Cara Kerja:** Ini adalah level tertinggi konvergensi. Menghubungkan *klik email* dengan *transaksi (sales)*.
*   **Proses:** Ketika pelanggan mengklik *link tracking* dari email dan mendarat di website *Front-End*, sistem akan menyimpan sebuah *Cookie* (contoh: `campaign_source = id_campaign`). Jika dalam 3-7 hari pelanggan tersebut melakukan pembayaran / Checkout, transaksi tersebut akan ditandai berasal dari email *campaign* tersebut.
*   **Fungsi:** Di *Dashboard CMS*, Anda bisa melihat laporan: *"Promo Ramadhan menghasilkan Rp 150.000.000 (ROI)"*. Anda jadi tahu persis keuntungan finansial dari setiap *blast* email.

### D. *Bounce & Complaint Handling* (Webhook SMTP)
**Cara Kerja:** Menerima laporan langsung dari penyedia SMTP (Resend/Brevo) jika email gagal masuk.
*   **Proses:** Penyedia SMTP mengirimkan "ping" (*Webhook*) ke server kita jika email masuk ke folder SPAM, atau jika alamat email pelanggan ternyata palsu/tidak aktif. Status diubah menjadi **"Bounced"** atau **"Failed"**.
*   **Fungsi:** Menjaga reputasi domain Anda. Email yang *Bounced* akan otomatis ditandai ( *Blacklisted* ) agar sistem tidak pernah mengirim email lagi ke alamat tersebut. Ini sangat krusial agar domain IMG tidak diblokir oleh Google/Gmail.

---
*Demikian penjelasan mengenai Konvergensi. Setelah Anda membacanya, kita bisa mulai membuat **Dashboard Laporan (Report)** untuk melihat metrik ini, atau mulai memprogram logika **Tracking Pixel** dan **Webhook**-nya. Mana yang ingin dieksekusi terlebih dahulu?*
