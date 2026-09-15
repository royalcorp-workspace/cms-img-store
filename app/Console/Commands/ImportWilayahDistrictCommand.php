<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Location\City;
use App\Models\Location\Province;
use App\Models\Location\SubDistrict;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class ImportWilayahDistrictCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'wilayah:import
                            {--replace : Ganti/hapus data sub_districts lama sebelum mengimpor yang baru}
                            {--province= : Filter kode provinsi tertentu (contoh: 31 untuk DKI Jakarta, atau 31,32)}
                            {--with-villages : Ikut sertakan level kelurahan/desa (memerlukan waktu lebih lama)}
                            {--chunk=500 : Ukuran batch chunk insert ke database}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import dan replace data provinsi, kabupaten/kota, dan kecamatan (district) dari API https://wilayah.id/ lengkap dengan kode pos';

    /**
     * Command aliases for convenient usage.
     *
     * @var array<string>
     */
    protected $aliases = ['district:import', 'location:import-wilayah'];

    /**
     * Base URL for Wilayah.id API.
     */
    private const WILAYAH_BASE_URL = 'https://wilayah.id/api';

    /**
     * Upstream repository for Indonesian postal codes (matched with Kemendagri codes).
     */
    private const POSTAL_CODE_URL = 'https://raw.githubusercontent.com/cahyadsn/wilayah_kodepos/main/json/wilayah_kodepos.min.json';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $startTime = microtime(true);
        $this->info('================================================================');
        $this->info('  IMPORT & SINKRONISASI DATA WILAYAH INDONESIA (WILAYAH.ID)');
        $this->info('================================================================');

        $isReplace = (bool) $this->option('replace');
        $provinceFilter = $this->option('province') ? explode(',', (string) $this->option('province')) : null;
        $withVillages = (bool) $this->option('with-villages');
        $chunkSize = max(50, (int) $this->option('chunk'));

        // 1. Download / Load Postal Code Dictionary
        $this->info('1. Memuat pemetaan kode pos resmi Indonesia...');
        $postalMap = $this->loadPostalCodeMap();
        $this->line('   [OK] Data kode pos siap digunakan (' . number_format(count($postalMap)) . ' entri).');

        // 2. Fetch Provinces from Wilayah.id
        $this->info('2. Mengambil data provinsi dari ' . self::WILAYAH_BASE_URL . '/provinces.json ...');
        $provincesData = $this->fetchProvinces($provinceFilter);

        if (empty($provincesData)) {
            $this->error('Gagal mengambil data provinsi dari https://wilayah.id/. Periksa koneksi internet.');
            return self::FAILURE;
        }

        $this->line('   [OK] Ditemukan ' . count($provincesData) . ' provinsi.');

        // If replacing globally without province filter
        if ($isReplace && empty($provinceFilter)) {
            $this->warn('Menghapus data sub_districts lama untuk digantikan data baru...');
            DB::table('sub_districts')->delete();
        }

        // 3. Process Provinces & Cities
        $this->info('3. Menyinkronkan provinsi, kabupaten/kota, dan kecamatan (districts)...');
        $progressBar = $this->output->createProgressBar(count($provincesData));
        $progressBar->start();

        $stats = [
            'provinces' => 0,
            'cities' => 0,
            'districts' => 0,
        ];

        $subDistrictsBatch = [];
        $totalInserted = 0;

        foreach ($provincesData as $provItem) {
            $provCode = trim((string) ($provItem['code'] ?? ''));
            $provName = trim((string) ($provItem['name'] ?? ''));

            if (empty($provCode) || empty($provName)) {
                $progressBar->advance();
                continue;
            }

            // Sync Province Model
            $provinceModel = $this->syncProvince($provCode, $provName);
            $stats['provinces']++;

            // If replace mode and specific province filter is used, clear old data for this province
            if ($isReplace && $provinceFilter) {
                DB::table('sub_districts')->where('province_id', $provinceModel->id)->delete();
            }

            // Fetch Regencies for this Province
            $regencies = $this->fetchRegencies($provCode);
            if (empty($regencies)) {
                $progressBar->advance();
                continue;
            }

            // Batch fetch districts for all regencies in this province using HTTP pool
            $districtsByRegency = $this->fetchDistrictsForRegencies($regencies);

            foreach ($regencies as $regItem) {
                $regCode = trim((string) ($regItem['code'] ?? ''));
                $regName = trim((string) ($regItem['name'] ?? ''));

                if (empty($regCode) || empty($regName)) {
                    continue;
                }

                // Sync City Model
                $cityModel = $this->syncCity($provinceModel, $regName);
                $stats['cities']++;

                $districts = $districtsByRegency[$regCode] ?? [];

                foreach ($districts as $distItem) {
                    $distCode = trim((string) ($distItem['code'] ?? ''));
                    $distName = trim((string) ($distItem['name'] ?? ''));

                    if (empty($distCode) || empty($distName)) {
                        continue;
                    }

                    // Resolve Postal Code for this district
                    $districtPostal = $this->resolveDistrictPostalCode($distCode, $postalMap);

                    if ($withVillages) {
                        // Level Kelurahan / Desa
                        $villages = $this->fetchVillages($distCode);
                        if (!empty($villages)) {
                            foreach ($villages as $vilItem) {
                                $vilCode = trim((string) ($vilItem['code'] ?? ''));
                                $vilName = trim((string) ($vilItem['name'] ?? ''));
                                $vilPostal = $postalMap[$vilCode] ?? $districtPostal;

                                $subDistrictsBatch[] = [
                                    'id' => (string) Str::uuid(),
                                    'province_id' => $provinceModel->id,
                                    'province' => $provinceModel->name,
                                    'city_id' => $cityModel->id,
                                    'district' => $distName,
                                    'sub_district' => $vilName,
                                    'postal_code' => $vilPostal,
                                    'is_active' => true,
                                    'sort_order' => 1,
                                    'creator' => 'system',
                                    'editor' => 'system',
                                    'deleted' => false,
                                    'created_at' => now(),
                                    'updated_at' => now(),
                                ];

                                $stats['districts']++;
                            }
                        } else {
                            // Fallback to district if no villages returned
                            $subDistrictsBatch[] = $this->buildSubDistrictRow($provinceModel, $cityModel, $distName, $distName, $districtPostal);
                            $stats['districts']++;
                        }
                    } else {
                        // Level Kecamatan (District)
                        $subDistrictsBatch[] = $this->buildSubDistrictRow($provinceModel, $cityModel, $distName, $distName, $districtPostal);
                        $stats['districts']++;
                    }

                    // Flush batch insert when chunk size reached
                    if (count($subDistrictsBatch) >= $chunkSize) {
                        DB::table('sub_districts')->insert($subDistrictsBatch);
                        $totalInserted += count($subDistrictsBatch);
                        $subDistrictsBatch = [];
                    }
                }
            }

            $progressBar->advance();
        }

        // Flush remaining records
        if (!empty($subDistrictsBatch)) {
            DB::table('sub_districts')->insert($subDistrictsBatch);
            $totalInserted += count($subDistrictsBatch);
            $subDistrictsBatch = [];
        }

        $progressBar->finish();
        $this->newLine(2);

        $duration = round(microtime(true) - $startTime, 2);

        $this->info('================================================================');
        $this->info('  HASIL SINKRONISASI WILAYAH');
        $this->info('================================================================');

        $this->table(
            ['Metrik', 'Jumlah'],
            [
                ['Provinsi Tersinkron', $stats['provinces']],
                ['Kabupaten / Kota Tersinkron', $stats['cities']],
                ['Districts / Kecamatan Dimasukkan', number_format($totalInserted)],
                ['Mode Penggantian (--replace)', $isReplace ? 'Ya (Data lama diganti)' : 'Tidak'],
                ['Level Kelurahan (--with-villages)', $withVillages ? 'Ya' : 'Tidak (Level Kecamatan)'],
                ['Total Waktu Eksekusi', "{$duration} detik"],
            ]
        );

        $this->info('Sukses! Data wilayah dan kecamatan telah siap digunakan di database dev/production.');
        return self::SUCCESS;
    }

    /**
     * Build standard row for sub_districts table.
     */
    private function buildSubDistrictRow(Province $province, City $city, string $district, string $subDistrict, string $postalCode): array
    {
        return [
            'id' => (string) Str::uuid(),
            'province_id' => $province->id,
            'province' => $province->name,
            'city_id' => $city->id,
            'district' => $district,
            'sub_district' => $subDistrict,
            'postal_code' => $postalCode,
            'is_active' => true,
            'sort_order' => 1,
            'creator' => 'system',
            'editor' => 'system',
            'deleted' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    /**
     * Fetch list of provinces from wilayah.id.
     */
    private function fetchProvinces(?array $filterCodes = null): array
    {
        try {
            $response = Http::timeout(15)->retry(2, 500)->get(self::WILAYAH_BASE_URL . '/provinces.json');
            if (!$response->successful()) {
                return [];
            }

            $all = $response->json()['data'] ?? [];
            if (empty($filterCodes)) {
                return $all;
            }

            $filterCodes = array_map('trim', $filterCodes);
            return array_values(array_filter($all, function ($item) use ($filterCodes) {
                return in_array(trim((string) ($item['code'] ?? '')), $filterCodes, true);
            }));
        } catch (\Throwable $e) {
            $this->warn('Error fetching provinces: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Fetch regencies for a given province code.
     */
    private function fetchRegencies(string $provCode): array
    {
        try {
            $response = Http::timeout(15)->retry(2, 500)->get(self::WILAYAH_BASE_URL . "/regencies/{$provCode}.json");
            if ($response->successful()) {
                return $response->json()['data'] ?? [];
            }
        } catch (\Throwable) {
            $this->warn("Error fetching regencies for province {$provCode}: " . $e->getMessage());
        }

        return [];
    }

    /**
     * Batch fetch districts for an array of regencies using HTTP pool.
     */
    private function fetchDistrictsForRegencies(array $regencies): array
    {
        if (empty($regencies)) {
            return [];
        }

        $result = [];
        $chunks = array_chunk($regencies, 15);

        foreach ($chunks as $chunk) {
            $responses = Http::pool(function ($pool) use ($chunk) {
                foreach ($chunk as $reg) {
                    $regCode = trim((string) ($reg['code'] ?? ''));
                    if ($regCode) {
                        $pool->as($regCode)->timeout(15)->get(self::WILAYAH_BASE_URL . "/districts/{$regCode}.json");
                    }
                }
            });

            foreach ($responses as $regCode => $response) {
                if ($response instanceof \Illuminate\Http\Client\Response && $response->successful()) {
                    $result[$regCode] = $response->json()['data'] ?? [];
                } else {
                    $result[$regCode] = [];
                }
            }
        }

        return $result;
    }

    /**
     * Fetch villages for a district code.
     */
    private function fetchVillages(string $distCode): array
    {
        try {
            $response = Http::timeout(12)->retry(2, 400)->get(self::WILAYAH_BASE_URL . "/villages/{$distCode}.json");
            if ($response->successful()) {
                return $response->json()['data'] ?? [];
            }
        } catch (\Throwable) {}

        return [];
    }

    /**
     * Download or load cached postal code dictionary.
     */
    private function loadPostalCodeMap(): array
    {
        $cachePath = storage_path('app/wilayah_kodepos.min.json');

        if (File::exists($cachePath) && (time() - File::lastModified($cachePath) < 86400 * 30)) {
            try {
                $decoded = json_decode(File::get($cachePath), true);
                if (is_array($decoded) && !empty($decoded)) {
                    return $decoded;
                }
            } catch (\Throwable) {}
        }

        try {
            $response = Http::timeout(25)->get(self::POSTAL_CODE_URL);
            if ($response->successful()) {
                $content = $response->body();
                @File::ensureDirectoryExists(storage_path('app'));
                @File::put($cachePath, $content);
                return json_decode($content, true) ?? [];
            }
        } catch (\Throwable $e) {
            $this->warn('Gagal mengunduh kode pos online: ' . $e->getMessage() . '. Menggunakan fallback internal.');
        }

        return [];
    }

    /**
     * Resolve representative postal code for a district code (e.g. 31.74.06 -> 12430).
     */
    private function resolveDistrictPostalCode(string $distCode, array $postalMap): string
    {
        // 1. Direct match or prefix search
        $prefix = $distCode . '.';
        foreach ($postalMap as $code => $postal) {
            if (str_starts_with((string) $code, $prefix)) {
                return (string) $postal;
            }
        }

        // 2. Default province prefix fallback
        $provCode = substr($distCode, 0, 2);
        return match ($provCode) {
            '31' => '10110', // DKI Jakarta
            '32' => '40111', // Jawa Barat
            '33' => '50111', // Jawa Tengah
            '34' => '55111', // D.I. Yogyakarta
            '35' => '60111', // Jawa Timur
            '36' => '15111', // Banten
            '51' => '80111', // Bali
            default => '10000',
        };
    }

    /**
     * Synchronize a province record into provinces table.
     */
    private function syncProvince(string $code, string $name): Province
    {
        // 1. Check if province already exists with this code
        $existing = Province::withoutGlobalScopes()->where('code', $code)->first();
        if ($existing) {
            $existing->update([
                'name' => $name,
                'is_active' => true,
                'deleted' => false,
            ]);
            return $existing;
        }

        // 2. Check by name (e.g. existing 'DKI' with name 'DKI Jakarta')
        $byName = Province::withoutGlobalScopes()->where('name', $name)->first();
        if ($byName) {
            // Update code to Kemendagri numeric code
            $byName->update([
                'code' => $code,
                'is_active' => true,
                'deleted' => false,
            ]);
            return $byName;
        }

        // 3. Create new
        return Province::create([
            'id' => (string) Str::uuid(),
            'code' => $code,
            'name' => $name,
            'is_active' => true,
            'sort_order' => (int) $code,
            'creator' => 'system',
            'editor' => 'system',
            'deleted' => false,
        ]);
    }

    /**
     * Synchronize a city record into cities table.
     */
    private function syncCity(Province $province, string $name): City
    {
        // Clean City Name (remove prefix 'Kota Administrasi ' or normalize for matching)
        $cleanName = trim($name);

        $existing = City::withoutGlobalScopes()
            ->where('province_id', $province->id)
            ->where(function ($query) use ($cleanName) {
                $query->where('name', $cleanName)
                    ->orWhere('name', str_ireplace(['Kota Administrasi ', 'Kabupaten Administrasi '], '', $cleanName))
                    ->orWhere('name', 'like', '%' . str_ireplace(['Kota ', 'Kabupaten ', 'Administrasi '], '', $cleanName) . '%');
            })
            ->first();

        if ($existing) {
            $existing->update([
                'name' => $cleanName,
                'province' => $province->name,
                'is_active' => true,
                'deleted' => false,
            ]);
            return $existing;
        }

        return City::create([
            'id' => (string) Str::uuid(),
            'province_id' => $province->id,
            'province' => $province->name,
            'name' => $cleanName,
            'is_active' => true,
            'sort_order' => 1,
            'creator' => 'system',
            'editor' => 'system',
            'deleted' => false,
        ]);
    }
}
