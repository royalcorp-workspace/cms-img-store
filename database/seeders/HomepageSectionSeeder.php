<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Content\HomepageSection;

class HomepageSectionSeeder extends Seeder
{
    public function run()
    {
        $sections = [
            [
                'section_key' => 'kategori',
                'title' => 'Pilih Sesuai Kebutuhan Istirahat (Kategori)',
                'sort_order' => 1,
                'is_visible' => true,
            ],
            [
                'section_key' => 'best_seller',
                'title' => 'Produk Unggulan (Best Seller)',
                'sort_order' => 2,
                'is_visible' => true,
            ],
            [
                'section_key' => 'pilihan_brand',
                'title' => 'Pilihan Brand / Mitra Resmi',
                'sort_order' => 3,
                'is_visible' => true,
            ],
            [
                'section_key' => 'promo_brand',
                'title' => 'Promo Brand Pilihan',
                'sort_order' => 4,
                'is_visible' => true,
            ],
            [
                'section_key' => 'bundling',
                'title' => 'Paket Spesial Untukmu (Bundling)',
                'sort_order' => 5,
                'is_visible' => true,
            ],
            [
                'section_key' => 'rekomendasi',
                'title' => 'Rekomendasi Produk',
                'sort_order' => 6,
                'is_visible' => true,
            ],
        ];

        foreach ($sections as $section) {
            HomepageSection::updateOrCreate(
                ['section_key' => $section['section_key']],
                $section
            );
        }
    }
}
