<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class RefProductCategorySeeder extends Seeder
{
    public function run(): void
    {
        DB::table('ref_product_categories')->delete();

        $data = [
            [
                'id' => Str::uuid()->toString(),
                'code' => 'DV',
                'name' => 'Divan',
                'description' => 'Rangka tempat tidur / penopang matras (Divan).',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => Str::uuid()->toString(),
                'code' => 'KM',
                'name' => 'Kasur Matras',
                'description' => 'Kasur matras / spring bed.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => Str::uuid()->toString(),
                'code' => 'HB',
                'name' => 'Headboard / Sandaran',
                'description' => 'Sandaran kepala untuk tempat tidur.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => Str::uuid()->toString(),
                'code' => 'EL',
                'name' => 'Elite',
                'description' => 'Produk matras / aksesoris merk Elite.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => Str::uuid()->toString(),
                'code' => 'EM',
                'name' => 'Elite Mattress',
                'description' => 'Kasur busa / matras merk Elite.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => Str::uuid()->toString(),
                'code' => 'MR',
                'name' => 'Moro',
                'description' => 'Kategori produk Moro.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => Str::uuid()->toString(),
                'code' => 'BJ',
                'name' => 'Busa Bed',
                'description' => 'Kategori busa kasur biasa.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('ref_product_categories')->insert($data);

        $this->command->info('Reference Product Category data seeded successfully!');
    }
}
