<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Mengisi data ongkir ekspedisi (1 Indonesia) dan kurir toko berbasis kota/kabupaten serta kelurahan/kecamatan
     * dengan titik keberangkatan dari Gudang Cimareme, Kab. Bandung Barat.
     */
    public function up(): void
    {
        $now = now();

        // 1. Bersihkan data seeder sebelumnya jika ada
        DB::table('shipping_addresses')
            ->where('creator', 'migration_seeder')
            ->delete();

        // 2. Dapatkan referensi kurir
        $couriers = DB::table('couriers')
            ->where('deleted', false)
            ->get()
            ->keyBy('code');

        if ($couriers->isEmpty()) {
            return;
        }

        // Ambil semua kota dari database (484 kota di 38 provinsi seluruh Indonesia)
        $cities = DB::table('cities')->where('deleted', false)->get();

        // Base tarif referensi per kg dari Gudang Cimareme (Kab. Bandung Barat) ke setiap provinsi di Indonesia
        $provinceBaseRates = [
            // Jawa & Bali
            'Jawa Barat' => 11000,
            'DKI Jakarta' => 11000,
            'Banten' => 12000,
            'Jawa Tengah' => 17000,
            'Daerah Istimewa Yogyakarta' => 17000,
            'Jawa Timur' => 20000,
            'Bali' => 25000,

            // Sumatera
            'Lampung' => 22000,
            'Sumatera Selatan' => 26000,
            'Bengkulu' => 28000,
            'Jambi' => 28000,
            'Kepulauan Bangka Belitung' => 30000,
            'Sumatera Barat' => 30000,
            'Riau' => 32000,
            'Kepulauan Riau' => 34000,
            'Sumatera Utara' => 34000,
            'Aceh' => 38000,

            // Nusa Tenggara
            'Nusa Tenggara Barat' => 30000,
            'Nusa Tenggara Timur' => 45000,

            // Kalimantan
            'Kalimantan Barat' => 36000,
            'Kalimantan Selatan' => 36000,
            'Kalimantan Tengah' => 38000,
            'Kalimantan Timur' => 38000,
            'Kalimantan Utara' => 45000,

            // Sulawesi
            'Sulawesi Selatan' => 38000,
            'Sulawesi Barat' => 42000,
            'Sulawesi Tengah' => 44000,
            'Sulawesi Tenggara' => 44000,
            'Gorontalo' => 45000,
            'Sulawesi Utara' => 48000,

            // Maluku & Papua
            'Maluku' => 60000,
            'Maluku Utara' => 65000,
            'Papua Barat Daya' => 85000,
            'Papua Barat' => 88000,
            'Papua Tengah' => 95000,
            'Papua' => 95000,
            'Papua Pegunungan' => 110000,
            'Papua Selatan' => 105000,
        ];

        // Offset / variasi harga tiap ekspedisi relatif terhadap base rate
        $expeditionOffsets = [
            'jne' => 0,
            'jnt' => 1000,
            'sicepat' => 500,
            'tiki' => 0,
            'pos' => -500,
        ];

        $ratesToInsert = [];

        // 3. Masukkan tarif kurir ekspedisi untuk SETIAP KOTA di 1 INDONESIA (484 Kota/Kabupaten)
        foreach ($cities as $city) {
            $provName = $city->province ?? '';
            $baseProvRate = $provinceBaseRates[$provName] ?? 25000;

            // Penyesuaian khusus kota lokal sekitar Cimareme / Bandung
            $cityNameLower = strtolower($city->name);
            if (str_contains($cityNameLower, 'bandung barat')) {
                $cityBaseRate = 9000;
            } elseif (str_contains($cityNameLower, 'cimahi')) {
                $cityBaseRate = 10000;
            } elseif (str_contains($cityNameLower, 'kota bandung')) {
                $cityBaseRate = 11000;
            } else {
                $cityBaseRate = $baseProvRate;
            }

            foreach ($expeditionOffsets as $cCode => $offset) {
                if (!isset($couriers[$cCode])) continue;
                $courier = $couriers[$cCode];

                $price = max(8000, $cityBaseRate + $offset);

                $ratesToInsert[] = [
                    'id' => (string) Str::uuid(),
                    'courier_id' => $courier->id,
                    'city_id' => $city->id,
                    'sub_district_id' => null,
                    'type' => 1,
                    'price' => $price,
                    'additional_price_per_kg' => 0,
                    'is_active' => true,
                    'sort_order' => 0,
                    'creator' => 'migration_seeder',
                    'editor' => 'migration_seeder',
                    'deleted' => false,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        // 4. Masukkan tarif spesifik tingkat KELURAHAN / KECAMATAN untuk wilayah lokal (KBB, Cimahi, Kota Bandung)
        $kbbCity = $cities->first(fn($c) => str_contains(strtolower($c->name), 'bandung barat'));
        $cimahiCity = $cities->first(fn($c) => str_contains(strtolower($c->name), 'cimahi'));
        $bandungCity = $cities->first(fn($c) => str_contains(strtolower($c->name), 'kota bandung'));

        // Kelurahan di Kab. Bandung Barat
        $kbbSubDistricts = $kbbCity 
            ? DB::table('sub_districts')->where('city_id', $kbbCity->id)->where('deleted', false)->get()
            : collect();

        foreach ($kbbSubDistricts as $sd) {
            $isVeryClose = in_array(strtolower($sd->district ?? ''), ['ngamprah', 'padalarang', 'batujajar', 'cisarua', 'parongpong']);
            $localBase = $isVeryClose ? 9000 : 11000;

            foreach ($expeditionOffsets as $cCode => $offset) {
                if (!isset($couriers[$cCode])) continue;
                $courier = $couriers[$cCode];

                $ratesToInsert[] = [
                    'id' => (string) Str::uuid(),
                    'courier_id' => $courier->id,
                    'city_id' => null,
                    'sub_district_id' => $sd->id,
                    'type' => 1,
                    'price' => max(8000, $localBase + $offset),
                    'additional_price_per_kg' => 0,
                    'is_active' => true,
                    'sort_order' => 0,
                    'creator' => 'migration_seeder',
                    'editor' => 'migration_seeder',
                    'deleted' => false,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        // Kelurahan di Kota Cimahi
        $cimahiSubDistricts = $cimahiCity 
            ? DB::table('sub_districts')->where('city_id', $cimahiCity->id)->where('deleted', false)->get()
            : collect();

        foreach ($cimahiSubDistricts as $sd) {
            foreach ($expeditionOffsets as $cCode => $offset) {
                if (!isset($couriers[$cCode])) continue;
                $courier = $couriers[$cCode];

                $ratesToInsert[] = [
                    'id' => (string) Str::uuid(),
                    'courier_id' => $courier->id,
                    'city_id' => null,
                    'sub_district_id' => $sd->id,
                    'type' => 1,
                    'price' => max(8000, 10000 + $offset),
                    'additional_price_per_kg' => 0,
                    'is_active' => true,
                    'sort_order' => 0,
                    'creator' => 'migration_seeder',
                    'editor' => 'migration_seeder',
                    'deleted' => false,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        // Kelurahan di Kota Bandung
        $bandungSubDistricts = $bandungCity 
            ? DB::table('sub_districts')->where('city_id', $bandungCity->id)->where('deleted', false)->get()
            : collect();

        foreach ($bandungSubDistricts as $sd) {
            foreach ($expeditionOffsets as $cCode => $offset) {
                if (!isset($couriers[$cCode])) continue;
                $courier = $couriers[$cCode];

                $ratesToInsert[] = [
                    'id' => (string) Str::uuid(),
                    'courier_id' => $courier->id,
                    'city_id' => null,
                    'sub_district_id' => $sd->id,
                    'type' => 1,
                    'price' => max(8000, 11000 + $offset),
                    'additional_price_per_kg' => 0,
                    'is_active' => true,
                    'sort_order' => 0,
                    'creator' => 'migration_seeder',
                    'editor' => 'migration_seeder',
                    'deleted' => false,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        // 5. Tambahkan Kurir Toko untuk Bandung Raya (Kab. Bandung Barat, Kota Cimahi, Kota Bandung)
        if (isset($couriers['kurir_toko'])) {
            $kurirToko = $couriers['kurir_toko'];

            $tokoConfigs = [
                ['city' => $kbbCity, 'price' => 20000, 'extra_kg' => 3000],
                ['city' => $cimahiCity, 'price' => 25000, 'extra_kg' => 4000],
                ['city' => $bandungCity, 'price' => 30000, 'extra_kg' => 4000],
            ];

            foreach ($tokoConfigs as $tConf) {
                if (!$tConf['city']) continue;

                $ratesToInsert[] = [
                    'id' => (string) Str::uuid(),
                    'courier_id' => $kurirToko->id,
                    'city_id' => $tConf['city']->id,
                    'sub_district_id' => null,
                    'type' => 1,
                    'price' => $tConf['price'],
                    'additional_price_per_kg' => $tConf['extra_kg'],
                    'is_active' => true,
                    'sort_order' => 0,
                    'creator' => 'migration_seeder',
                    'editor' => 'migration_seeder',
                    'deleted' => false,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        // 6. Insert dalam batch agar efisien dan cepat
        foreach (array_chunk($ratesToInsert, 200) as $chunk) {
            DB::table('shipping_addresses')->insert($chunk);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('shipping_addresses')
            ->where('creator', 'migration_seeder')
            ->delete();
    }
};
