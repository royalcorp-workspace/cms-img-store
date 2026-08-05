<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ContentSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Seed about_us table
        $aboutUsId = '019f5935-e100-7256-81e8-23c114d2eaa4';
        $aboutUsExists = DB::table('about_us')->where('company_name', 'Royal Foam Indonesia')->first();
        if (!$aboutUsExists) {
            DB::table('about_us')->insert([
                'id' => $aboutUsId,
                'company_name' => 'Royal Foam Indonesia',
                'tagline' => 'Tidur Nyenyak, Bangun Segar untuk Hari Lebih Produktif',
                'description' => 'Royal Foam Indonesia adalah produsen kasur busa dan perlengkapan tidur terkemuka di Indonesia yang telah melayani keluarga Indonesia selama lebih dari 30 tahun. Kami berdedikasi menyediakan produk kasur berkualitas premium dengan teknologi anti-bakteri dan garansi jangka panjang untuk mendukung kesehatan tidur Anda.',
                'vision' => 'Menjadi merek kasur busa nomor satu pilihan keluarga Indonesia yang dikenal karena inovasi kesehatan tidur, kualitas terbaik, dan pelayanan prima.',
                'mission' => "1. Menghadirkan inovasi kasur busa berteknologi tinggi demi kesehatan tulang belakang.\n2. Mengutamakan kepuasan pelanggan dengan pelayanan purna jual terbaik dan garansi resmi.\n3. Menerapkan proses produksi ramah lingkungan dan standar keamanan tinggi.",
                'values' => 'Integritas, Inovasi, Kenyamanan, Kepuasan Pelanggan, Kualitas Tanpa Kompromi',
                'established_year' => 1995,
                'address' => 'Jl.Raya Barat, Cimareme, Kec. Ngamprah, Kabupaten Bandung Barat, Jawa Barat 40552',
                'phone' => '021-89891234',
                'email' => 'info@royalfoam.co.id',
                'logo' => 'images/content/about_us_logo.png',
                'cover_image' => 'images/content/about_us_cover.jpg',
                'social_media' => json_encode([
                    'instagram' => 'https://instagram.com/royalfoam',
                    'facebook' => 'https://facebook.com/royalfoam',
                    'twitter' => 'https://twitter.com/royalfoam'
                ]),
                'is_active' => true,
                'sort_order' => 1,
                'deleted' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // 2. Seed blog_posts table
        $blogs = [
            [
                'id' => '019f5935-e200-7256-81e8-23c114d2eaa4',
                'title' => '5 Cara Memilih Kasur yang Tepat untuk Kesehatan Tulang Belakang',
                'slug' => '5-cara-memilih-kasur-untuk-kesehatan-tulang-belakang',
                'excerpt' => 'Memilih kasur yang salah bisa menyebabkan nyeri punggung yang mengganggu aktivitas sehari-hari. Simak panduan lengkap memilih kasur orthopedic di sini.',
                'content' => '<p>Tidur malam yang berkualitas sangat dipengaruhi oleh kasur yang Anda gunakan. Memilih kasur yang salah bukan hanya membuat tidur tidak nyenyak, melainkan juga berisiko tinggi menyebabkan nyeri punggung atau cedera tulang belakang. Berikut adalah 5 cara memilih kasur yang tepat:</p><ul><li>Pilihlah tingkat kekerasan kasur (firmness) yang sesuai dengan posisi tidur Anda.</li><li>Gunakan kasur dengan sistem penopang orthopedic khusus.</li><li>Pertimbangkan material kasur, seperti latex alami atau memory foam berkualitas.</li><li>Pastikan ukuran kasur memberikan kebebasan bergerak yang cukup.</li><li>Pilihlah produk yang memiliki garansi resmi minimal 10 tahun.</li></ul>',
                'featured_image' => 'images/blog/pilihan-kasur-orthopedic.jpg',
                'author_name' => 'Dr. Budi Santoso',
                'is_published' => true,
                'is_featured' => true,
                'published_at' => now(),
                'sort_order' => 1,
                'meta_title' => 'Panduan Memilih Kasur Orthopedic Terbaik | Royal Foam',
                'meta_description' => 'Nyeri punggung setelah bangun tidur? Simak 5 cara memilih kasur orthopedic yang tepat demi menjaga kesehatan tulang belakang Anda.',
                'deleted' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => '019f5935-e201-7256-81e8-23c114d2eaa4',
                'title' => 'Mengapa Teknologi Anti-Bakteri Sangat Penting untuk Kasur Busa Anda?',
                'slug' => 'mengapa-teknologi-anti-bakteri-penting-untuk-kasur-busa',
                'excerpt' => 'Kasur merupakan sarang utama berkembangbiaknya bakteri dan tungau debu jika tidak dirawat. Pelajari perlindungan teknologi Sanitized.',
                'content' => '<p>Apakah Anda sering terbangun dengan kondisi bersin-bersin, hidung tersumbat, atau gatal-gatal di kulit? Jika iya, bisa jadi kasur Anda telah menjadi sarang debu, bakteri, dan tungau. Kasur busa biasa tanpa perlindungan khusus sangat rentan menyerap kelembapan dan keringat, yang menjadi media tumbuh kembang mikroorganisme berbahaya. Teknologi Sanitized hadir sebagai solusi perlindungan maksimal, menjaga kasur tetap higienis, bebas bau apek, dan aman untuk kulit sensitif maupun penderita asma.</p>',
                'featured_image' => 'images/blog/kasur-anti-bakteri.jpg',
                'author_name' => 'Rina Wijaya',
                'is_published' => true,
                'is_featured' => false,
                'published_at' => now(),
                'sort_order' => 2,
                'meta_title' => 'Pentingnya Kasur Busa Anti-Bakteri Sanitized | Royal Foam',
                'meta_description' => 'Ketahui manfaat teknologi anti-bakteri Sanitized pada kasur busa Royal Foam untuk kesehatan kulit dan pernapasan keluarga Anda.',
                'deleted' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ];

        foreach ($blogs as $blog) {
            $exists = DB::table('blog_posts')->where('slug', $blog['slug'])->first();
            if (!$exists) {
                DB::table('blog_posts')->insert($blog);
            }
        }

        // 3. Seed faqs table
        $faqs = [
            [
                'id' => '019f5935-e300-7256-81e8-23c114d2eaa4',
                'question' => 'Berpa lama garansi kasur Royal Foam dan bagaimana cara klaimnya?',
                'answer' => 'Garansi kasur busa Royal Foam bervariasi mulai dari 10 tahun, 15 tahun, hingga 20 tahun tergantung pada tipe produk yang dibeli. Klaim garansi dapat diajukan dengan mudah secara online melalui menu "Klaim Garansi" di website kami dengan melampirkan foto kartu garansi fisik, nota pembelian, serta bukti foto/video kondisi kasur yang diklaim.',
                'sort_order' => 1,
                'is_published' => true,
                'view_count' => 120,
                'deleted' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => '019f5935-e301-7256-81e8-23c114d2eaa4',
                'question' => 'Apakah Royal Foam menyediakan layanan pengiriman gratis?',
                'answer' => 'Ya, kami memberikan fasilitas Gratis Ongkir untuk wilayah JABODETABEK dengan menggunakan armada pengiriman resmi kami. Untuk pengiriman ke luar JABODETABEK, biaya pengiriman akan disesuaikan dengan tarif ekspedisi kargo yang dipilih saat checkout.',
                'sort_order' => 2,
                'is_published' => true,
                'view_count' => 85,
                'deleted' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => '019f5935-e302-7256-81e8-23c114d2eaa4',
                'question' => 'Bagaimana cara membersihkan kasur busa Royal Foam jika terkena noda atau cairan?',
                'answer' => 'Jangan menjemur kasur langsung di bawah terik matahari karena dapat merusak struktur busa. Jika terkena cairan, segera tekan-tekan bagian tersebut dengan kain kering atau tisu untuk menyerap cairan. Cukup bersihkan noda menggunakan kain lembap dengan sabun lembut, lalu keringkan menggunakan kipas angin atau hairdryer berdaya rendah.',
                'sort_order' => 3,
                'is_published' => true,
                'view_count' => 95,
                'deleted' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ];

        foreach ($faqs as $faq) {
            $exists = DB::table('faqs')->where('question', $faq['question'])->first();
            if (!$exists) {
                DB::table('faqs')->insert($faq);
            }
        }

        // 4. Seed how_to_returns table
        $howToReturnId = '019f5935-e400-7256-81e8-23c114d2eaa4';
        $howToReturnExists = DB::table('how_to_returns')->where('slug', 'panduan-pengembalian-produk')->first();
        if (!$howToReturnExists) {
            DB::table('how_to_returns')->insert([
                'id' => $howToReturnId,
                'title' => 'Panduan Pengembalian Produk (Retur)',
                'slug' => 'panduan-pengembalian-produk',
                'content' => 'Kami berkomitmen untuk selalu memberikan produk kasur dan perlengkapan tidur dengan kualitas terbaik. Namun, jika Anda menerima produk dalam keadaan rusak pabrik, cacat saat pengiriman, atau spesifikasi tidak sesuai pesanan, Anda dapat melakukan pengembalian (retur) produk dengan mengikuti langkah-langkah di bawah ini.',
                'steps' => json_encode([
                    'Langkah 1: Dokumentasikan kondisi produk melalui foto dan video unboxing.',
                    'Langkah 2: Hubungi Customer Service kami via WhatsApp/Email maksimal 7 hari kalender setelah produk diterima.',
                    'Langkah 3: Tim kami akan menganalisis laporan Anda dan memberikan konfirmasi persetujuan retur dalam 1-2 hari kerja.',
                    'Langkah 4: Setelah disetujui, kami akan mengirimkan kurir untuk mengambil produk lama dan mengantarkan produk pengganti yang baru tanpa biaya tambahan.'
                ]),
                'featured_image' => 'images/content/return_policy.jpg',
                'is_published' => true,
                'sort_order' => 1,
                'meta_title' => 'Panduan & Kebijakan Pengembalian Kasur | Royal Foam',
                'meta_description' => 'Pelajari kebijakan penukaran dan pengembalian produk kasur Royal Foam yang rusak saat pengiriman atau cacat produksi.',
                'deleted' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // 5. Seed privacy_policies table
        $privacyPolicyId = '019f5935-e500-7256-81e8-23c114d2eaa4';
        $privacyPolicyExists = DB::table('privacy_policies')->where('slug', 'kebijakan-privasi')->first();
        if (!$privacyPolicyExists) {
            DB::table('privacy_policies')->insert([
                'id' => $privacyPolicyId,
                'title' => 'Kebijakan Privasi Royal Foam',
                'slug' => 'kebijakan-privasi',
                'content' => 'Kebijakan Privasi ini menjelaskan bagaimana Royal Foam Indonesia mengumpulkan, menyimpan, menggunakan, dan melindungi data pribadi Anda saat menggunakan layanan dan berbelanja melalui situs resmi kami. Kami berkomitmen untuk menjaga keamanan data pribadi Anda dengan standar enkripsi terbaik guna mencegah akses tanpa izin.',
                'version' => 'v1.0',
                'effective_date' => '2026-01-01',
                'is_published' => true,
                'sort_order' => 1,
                'meta_title' => 'Kebijakan Privasi Pengguna | Royal Foam',
                'meta_description' => 'Informasi lengkap tentang komitmen kami dalam melindungi privasi dan keamanan data pribadi pelanggan Royal Foam.',
                'deleted' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // 6. Seed terms_and_conditions table
        $termsId = '019f5935-e600-7256-81e8-23c114d2eaa4';
        $termsExists = DB::table('terms_and_conditions')->where('slug', 'syarat-dan-ketentuan')->first();
        if (!$termsExists) {
            DB::table('terms_and_conditions')->insert([
                'id' => $termsId,
                'title' => 'Syarat dan Ketentuan Layanan',
                'slug' => 'syarat-dan-ketentuan',
                'content' => 'Dengan mengakses, mendaftar, dan membeli produk di website resmi Royal Foam, Anda dianggap menyetujui seluruh aturan, syarat, dan ketentuan yang berlaku. Harap membaca halaman ini dengan saksama sebelum melakukan transaksi pembelian kasur busa atau produk kami lainnya.',
                'version' => 'v1.0',
                'effective_date' => '2026-01-01',
                'is_published' => true,
                'sort_order' => 1,
                'meta_title' => 'Syarat & Ketentuan Penggunaan Layanan | Royal Foam',
                'meta_description' => 'Bacalah Syarat & Ketentuan Layanan pembelian kasur online di Royal Foam sebelum bertransaksi.',
                'deleted' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // 7. Seed warranty_claims table
        $warrantyId = '019f5935-e700-7256-81e8-23c114d2eaa4';
        $warrantyExists = DB::table('warranty_claims')->where('slug', 'panduan-klaim-garansi')->first();
        if (!$warrantyExists) {
            DB::table('warranty_claims')->insert([
                'id' => $warrantyId,
                'title' => 'Panduan Klaim Garansi Kasur',
                'slug' => 'panduan-klaim-garansi',
                'content' => 'Garansi kasur busa Royal Foam memberikan jaminan perlindungan terhadap penurunan kualitas kasur (kempes) secara signifikan di luar batas wajar akibat pemakaian normal. Kami memberikan garansi hingga 20 tahun untuk tipe kasur tertentu sebagai bentuk komitmen kualitas kami.',
                'steps' => json_encode([
                    'Siapkan kartu garansi resmi dan nota pembelian.',
                    'Foto kasur secara keseluruhan dan foto khusus pada area yang mengalami kempes dengan meletakkan penggaris sebagai skala pembanding.',
                    'Kirimkan formulir permohonan klaim melalui situs web atau hubungi customer service kami.',
                    'Petugas kami akan melakukan verifikasi data dan menjadwalkan pemeriksaan fisik atau penggantian busa jika disetujui.'
                ]),
                'required_documents' => json_encode([
                    'Kartu Garansi Fisik Asli',
                    'Nota atau Bukti Pembelian Toko',
                    'Foto Kasur Utuh di atas Ranjang',
                    'Foto Detail Kasur yang Kempes (diukur dengan beban minimal)'
                ]),
                'processing_time_days' => 14,
                'featured_image' => 'images/content/warranty_claim.jpg',
                'is_published' => true,
                'sort_order' => 1,
                'meta_title' => 'Cara & Syarat Klaim Garansi Kasur | Royal Foam',
                'meta_description' => 'Panduan lengkap mengenai cara mengajukan klaim garansi resmi kasur busa Royal Foam yang kempes atau rusak.',
                'deleted' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
        // 8. Seed vouchers table
        $vouchers = [
            [
                'id' => '019f5935-e800-7256-81e8-23c114d2eaa4',
                'code' => 'ROYALBEST',
                'title' => 'Diskon Spesial Royal',
                'description' => 'Potongan langsung Rp 50.000 untuk minimal pembelian Rp 500.000.',
                'type' => 2, // Nominal (Rp)
                'scope' => 1, // Semua produk
                'allow_stacking' => false,
                'value' => 50000.00,
                'min_purchase' => 500000.00,
                'max_discount' => null,
                'usage_limit' => 100,
                'usage_limit_per_user' => 1,
                'used_count' => 0,
                'start_date' => now(),
                'end_date' => now()->addMonths(12),
                'valid_for_new_customer' => false,
                'is_active' => true,
                'creator' => 'system',
                'editor' => 'system',
                'deleted' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => '019f5935-e801-7256-81e8-23c114d2eaa4',
                'code' => 'TIDURNYENYAK',
                'title' => 'Diskon Persentase Tidur Nyenyak',
                'description' => 'Potongan 10% dengan maksimal diskon Rp 150.000 untuk minimal pembelian Rp 1.000.000.',
                'type' => 1, // Persentase (%)
                'scope' => 1,
                'allow_stacking' => false,
                'value' => 10.00,
                'min_purchase' => 1000000.00,
                'max_discount' => 150000.00,
                'usage_limit' => 200,
                'usage_limit_per_user' => 1,
                'used_count' => 0,
                'start_date' => now(),
                'end_date' => now()->addMonths(12),
                'valid_for_new_customer' => false,
                'is_active' => true,
                'creator' => 'system',
                'editor' => 'system',
                'deleted' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => '019f5935-e802-7256-81e8-23c114d2eaa4',
                'code' => 'ONGKIRGRATIS',
                'title' => 'Gratis Ongkir Hemat',
                'description' => 'Potongan ongkos kirim hingga Rp 20.000 dengan minimal belanja Rp 200.000.',
                'type' => 3, // Diskon Ongkir (Rp)
                'scope' => 1,
                'allow_stacking' => true,
                'value' => 20000.00,
                'min_purchase' => 200000.00,
                'max_discount' => null,
                'usage_limit' => 500,
                'usage_limit_per_user' => 2,
                'used_count' => 0,
                'start_date' => now(),
                'end_date' => now()->addMonths(12),
                'valid_for_new_customer' => false,
                'is_active' => true,
                'creator' => 'system',
                'editor' => 'system',
                'deleted' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ];

        foreach ($vouchers as $voucher) {
            $exists = DB::table('vouchers')->where('code', $voucher['code'])->first();
            if (!$exists) {
                DB::table('vouchers')->insert($voucher);
            }
        }
    }
}
