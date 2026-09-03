# Strategi dan Teknis Integrasi Object Storage (RustFS / S3)

Dokumen ini menjelaskan strategi serta implementasi teknis untuk mengintegrasikan layanan Object Storage (seperti RustFS atau S3) pada aplikasi web dengan menggunakan pola **Pre-Signed URL (Direct Upload)**.

---

## 1. Strategi Integrasi

Pola **Direct Upload (Pre-Signed URL)** dipilih sebagai strategi utama agar payload file (binary) yang diunggah oleh user (client) tidak perlu melewati server aplikasi (Laravel) backend.

### Keuntungan Utama
1. **Performa Server Aplikasi:** Meringankan beban server backend karena server tidak menangani pemrosesan payload file berukuran besar.
2. **Efisiensi Resource & Bandwidth:** Hemat memory (RAM) dan bandwidth jaringan dari sisi server karena transfer file berlangsung langsung dari browser client ke Object Storage (RustFS).
3. **Keamanan Path & Tipe File:** Server tetap memegang kendali atas pembuatan nama file dan lokasi path file sehingga mencegah pengguna menimpa path atau mengunggah tipe file sembarangan.

### Alur Kerja (Workflow) Integrasi
1. **Request URL:** Frontend meminta izin ke backend Laravel untuk melakukan upload dengan mengirimkan metadata file (misal: `mime_type`, `extension`).
2. **Generate URL:** Backend memvalidasi, membuat random *file path* (UUID), dan meng-generate **Pre-Signed URL** via SDK, lalu mengembalikannya ke frontend.
3. **Direct Upload:** Frontend mengunggah (HTTP `PUT`) binary file secara langsung ke Object Storage menggunakan URL yang telah bertanda tangan tersebut.
4. **Simpan ke Database:** Frontend menerima respons berhasil, kemudian melanjutkan request untuk menyimpan path string file tersebut ke tabel database (misal: tabel produk).

---

## 2. Panduan Teknis Implementasi

### A. Konfigurasi Environment (`.env`) & Filesystem

Tambahkan variabel berikut pada `.env`:



Pastikan pada konfigurasi `config/filesystems.php`, disk s3 Anda disetup dengan benar:
```php
's3' => [
    'driver' => 's3',
    'key' => env('AWS_ACCESS_KEY_ID'),
    'secret' => env('AWS_SECRET_ACCESS_KEY'),
    'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    'bucket' => env('AWS_BUCKET'),
    'url' => env('AWS_URL'),
    'endpoint' => env('AWS_ENDPOINT'),
    'use_path_style_endpoint' => env('AWS_USE_PATH_STYLE_ENDPOINT', true),
],
```

### B. Endpoint Pre-Signed URL (Backend Laravel)

Backend bertugas menerbitkan Signed URL yang kedaluwarsa secara cepat (misal: 5 menit).

```php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MediaController extends Controller
{
    public function getUploadUrl(Request $request)
    {
        // Whitelist validasi tipe file 
        $request->validate([
            'mime_type' => 'required|in:image/jpeg,image/png,image/webp',
            'extension' => 'required|in:jpg,jpeg,png,webp',
        ]);

        $filePath = 'products/' . date('Y/m/') . Str::uuid() . '.' . $request->extension;
        $client = Storage::disk('s3')->getClient();
        $command = $client->getCommand('PutObject', [
            'Bucket'      => env('AWS_BUCKET'),
            'Key'         => $filePath,
            'ContentType' => $request->mime_type,
        ]);

        // URL bertanda tangan, valid untuk 5 menit
        $signedRequest = $client->createPresignedRequest($command, '+5 minutes');

        return response()->json([
            'upload_url' => (string) $signedRequest->getUri(),
            'file_path'  => $filePath,
            'public_url' => env('AWS_URL') . '/' . $filePath,
        ]);
    }
}
```

### C. Implementasi Direct Upload (Frontend)

Frontend harus melakukan *fetch* pada endpoint di atas, lalu *PUT* file binary ke Object Storage.

```javascript
async function uploadProductImage(file) {
    const extension = file.name.split('.').pop().toLowerCase();
    const mimeType = file.type;

    // 1. Minta Signed URL
    const authRes = await fetch('/api/media/upload-url', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({ mime_type: mimeType, extension })
    });

    if (!authRes.ok) throw new Error('Gagal mendapatkan pre-signed URL');
    const { upload_url, file_path, public_url } = await authRes.json();

    // 2. Direct upload file binary ke RustFS via HTTP PUT
    const uploadRes = await fetch(upload_url, {
        method: 'PUT',
        headers: {
            'Content-Type': mimeType // Wajib sama dengan yang divalidasi di backend
        },
        body: file
    });

    if (!uploadRes.ok) throw new Error('Gagal mengunggah file ke Object Storage');

    // 3. File berhasil diupload, kembalikan parameter path
    return { file_path, public_url };
}
```

### D. Operasi Penghapusan File (Backend)

Operasi `DELETE` hanya boleh dilakukan melalui API internal backend, tidak boleh menggunakan signed URL untuk dihapus dari frontend.

```php
use Illuminate\Support\Facades\Storage;
use App\Models\Product;

public function destroy($id){
    $product = Product::findOrFail($id);

    // Menghapus physical file dari RustFS melalui API S3 backend
    if ($product->image_path && Storage::disk('s3')->exists($product->image_path)) {
        Storage::disk('s3')->delete($product->image_path);
    }
    
    $product->delete();

    return response()->json([
        'status'  => 'success',
        'message' => 'Produk dan gambar berhasil dihapus'
    ]);
}
```

---

## 3. Checklist Keamanan (Security)

Sebelum aplikasi masuk ke tahap *production*, pastikan hal-hal berikut telah diterapkan:

- [ ] **Autentikasi Endpoint:** Endpoint `getUploadUrl` harus diamankan (misalnya dengan middleware auth `sanctum` atau `session`), tidak terbuka bebas.
- [ ] **Batasan Ukuran File:** Validasi ukuran file di frontend maupun dengan memanfaatkan fitur *policy condition* pada Pre-signed URL (bila didukung oleh SDK yang dipakai).
- [ ] **Manajemen Rahasia (Secret):** Pastikan `AWS_ACCESS_KEY_ID` & `AWS_SECRET_ACCESS_KEY` tidak tersimpan di repositori (gunakan `.env` dan `.gitignore`).
- [ ] **CORS Object Storage:** Pastikan bucket di RustFS membatasi akses CORS. Konfigurasikan agar hanya origin URL aplikasi (frontend) yang diizinkan melakukan HTTP `PUT`.
- [ ] **Masa Berlaku (TTL):** Set masa berlaku *signed URL* menjadi cukup singkat (misal: 5 menit), untuk mengurangi risiko eksploitasi jika URL tersebut tercegat/terbongkar.
- [ ] **Otorisasi Penghapusan:** Endpoint untuk menghapus data dan file memvalidasi hak akses (otorisasi) *user role* yang berwenang.
