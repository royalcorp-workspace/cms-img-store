<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Temporary\TemporaryJdeItemMaster;
use App\Models\Temporary\TemporaryJdeBasePrice;
use App\Models\Temporary\TemporaryJdeBranch;
use App\Models\Temporary\TemporaryJdeCustomer;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class JdePullService
{
    /**
     * Build standard JDE authentication credentials payload (from API_JDE.txt).
     */
    protected function getAuthPayload(): array
    {
        return [
            'username' => (string) config('services.jde.username', 'UDO'),
            'password' => (string) config('services.jde.password', 'udoroyal'),
            'environment' => (string) config('services.jde.environment', 'JPY920'),
            'role' => (string) config('services.jde.role', '*ALL'),
        ];
    }

    /**
     * Resolve full URL from JDE_HOST and relative endpoint.
     */
    protected function getUrl(string $endpointKey): ?string
    {
        $host = config('services.jde.host');
        $endpoint = config("services.jde.{$endpointKey}");

        if (empty($host) || empty($endpoint)) {
            return null;
        }

        return rtrim($host, '/') . '/' . ltrim($endpoint, '/');
    }

    /**
     * Extract rowset dynamically from JDE JSON response.
     */
    protected function extractRowset(array $json): array
    {
        if (!empty($json['rowset']) && is_array($json['rowset'])) {
            return $json['rowset'];
        }

        foreach ($json as $key => $val) {
            if (is_array($val) && !empty($val['rowset']) && is_array($val['rowset'])) {
                return $val['rowset'];
            }
        }

        if (!empty($json['data']) && is_array($json['data'])) {
            return $json['data'];
        }

        return [];
    }

    /**
     * Sanitize date string to YYYY-MM-DD or null.
     */
    protected function sanitizeDate(?string $date): ?string
    {
        if (empty($date) || trim($date) === '' || trim($date) === '0000-00-00') {
            return null;
        }

        $trimmed = trim($date);
        if (preg_match('/^(\d{4}-\d{2}-\d{2})/', $trimmed, $matches)) {
            return $matches[1];
        }

        return null;
    }

    /**
     * 1. Tarik Langsung Data Item Master dari Oracle JDE (POS_ITEM_MASTER)
     */
    public function pullItemMasters(?string $filePath = null, int $limit = 0): array
    {
        $rawRows = [];
        $sourceType = 'DIRECT_ORACLE_JDE_API';

        // Jika dipanggil via Upload JSON file manual oleh admin
        if (!empty($filePath)) {
            if (!file_exists($filePath)) {
                return [
                    'success' => false,
                    'message' => "File upload tidak ditemukan: {$filePath}",
                    'total' => 0,
                    'created' => 0,
                    'updated' => 0,
                ];
            }
            $content = file_get_contents($filePath);
            $json = json_decode($content, true) ?: [];
            $rawRows = $this->extractRowset($json);
            $sourceType = 'UPLOAD_JSON_FILE: ' . basename($filePath);
        } else {
            // Tarik LANGSUNG dari Oracle JDE API
            $url = $this->getUrl('endpoint_item_master');
            $timeout = (int) config('services.jde.timeout', 120);

            if (empty($url)) {
                return [
                    'success' => false,
                    'message' => 'Konfigurasi JDE_HOST atau endpoint_item_master belum disetel.',
                    'total' => 0,
                    'created' => 0,
                    'updated' => 0,
                ];
            }

            try {
                Log::channel('jde')->info('Memulai request langsung ke Oracle JDE Item Master', [
                    'url' => $url,
                    'timeout' => $timeout,
                ]);

                $response = Http::withoutVerifying()
                    ->timeout($timeout)
                    ->post($url, $this->getAuthPayload());

                if (!$response->successful()) {
                    $errorMsg = "JDE Item Master API HTTP {$response->status()}: " . substr($response->body(), 0, 500);
                    Log::channel('jde')->error($errorMsg);
                    return [
                        'success' => false,
                        'message' => $errorMsg,
                        'total' => 0,
                        'created' => 0,
                        'updated' => 0,
                    ];
                }

                $json = $response->json() ?: [];
                $rawRows = $this->extractRowset($json);
            } catch (\Throwable $e) {
                $errorMsg = 'Koneksi ke Oracle JDE Item Master API gagal: ' . $e->getMessage();
                Log::channel('jde')->error($errorMsg);
                return [
                    'success' => false,
                    'message' => $errorMsg,
                    'total' => 0,
                    'created' => 0,
                    'updated' => 0,
                ];
            }
        }

        if (empty($rawRows)) {
            return [
                'success' => false,
                'message' => 'Data Item Master dari Oracle JDE kosong atau format rowset tidak sesuai.',
                'total' => 0,
                'created' => 0,
                'updated' => 0,
            ];
        }

        if ($limit > 0) {
            $rawRows = array_slice($rawRows, 0, $limit);
        }

        $created = 0;
        $updated = 0;

        DB::transaction(function () use ($rawRows, &$created, &$updated) {
            foreach ($rawRows as $row) {
                $sku = trim((string)($row['2nd_item_number'] ?? ($row['second_item_number'] ?? '')));
                if (empty($sku)) {
                    continue;
                }

                $segments = [
                    'uom' => trim((string)($row['uom'] ?? 'PC')),
                    'segment1' => trim((string)($row['segment1'] ?? '')),
                    'segment2' => trim((string)($row['segment2'] ?? '')),
                    'segment3' => trim((string)($row['segment3'] ?? '')),
                    'segment4' => trim((string)($row['segment4'] ?? '')),
                    'segment5' => trim((string)($row['segment5'] ?? '')),
                    'segment6' => trim((string)($row['segment6'] ?? '')),
                    'segment7' => trim((string)($row['segment7'] ?? '')),
                    'segment8' => trim((string)($row['segment8'] ?? '')),
                    'segment9' => trim((string)($row['segment9'] ?? '')),
                    'segment10' => trim((string)($row['segment10'] ?? '')),
                ];

                $record = TemporaryJdeItemMaster::where('second_item_number', $sku)->first();
                $attributes = [
                    'short_item_no' => (int)($row['short_item_no'] ?? 0),
                    'description' => trim((string)($row['description'] ?? '')),
                    'description_line_2' => trim((string)($row['description_line_2'] ?? '')),
                    'sales_code1' => trim((string)($row['sales_code1'] ?? '')),
                    'sales_code1_desc' => trim((string)($row['sales_code1_desc'] ?? '')),
                    'process2' => trim((string)($row['process2'] ?? '')),
                    'uom' => trim((string)($row['uom'] ?? 'PC')),
                    'stocking_type' => trim((string)($row['stocking_type'] ?? 'P')),
                    'line_type' => trim((string)($row['line_type'] ?? 'S')),
                    'user_id' => trim((string)($row['user_id'] ?? '')),
                    'program_id' => trim((string)($row['program_id'] ?? '')),
                    'date_update' => $this->sanitizeDate($row['date_update'] ?? null),
                    'template' => trim((string)($row['template'] ?? '')),
                    'segments' => $segments,
                    'post_retail' => trim((string)($row['post_retail'] ?? '')),
                    'img_reference' => trim((string)($row['img'] ?? ($row['img_reference'] ?? ''))),
                    'sync_status' => 'PENDING',
                    'raw_payload' => $row,
                ];

                if ($record) {
                    $record->update($attributes);
                    $updated++;
                } else {
                    $attributes['second_item_number'] = $sku;
                    TemporaryJdeItemMaster::create($attributes);
                    $created++;
                }
            }
        });

        Log::channel('jde')->info('Penarikan Item Master JDE Selesai', [
            'source' => $sourceType,
            'total_rows' => count($rawRows),
            'created' => $created,
            'updated' => $updated,
        ]);

        return [
            'success' => true,
            'source' => $sourceType,
            'total' => count($rawRows),
            'created' => $created,
            'updated' => $updated,
            'message' => "Berhasil menarik " . count($rawRows) . " Item Master ({$created} baru, {$updated} diperbarui) ke tabel temporary.",
        ];
    }

    /**
     * 2. Tarik Langsung Data Base Price dari Oracle JDE (POS_BASE_PRICE)
     */
    public function pullBasePrices(?string $filePath = null, int $limit = 0): array
    {
        $rawRows = [];
        $sourceType = 'DIRECT_ORACLE_JDE_API';

        if (!empty($filePath)) {
            if (!file_exists($filePath)) {
                return [
                    'success' => false,
                    'message' => "File upload tidak ditemukan: {$filePath}",
                    'total' => 0,
                    'created' => 0,
                    'updated' => 0,
                ];
            }
            $content = file_get_contents($filePath);
            $json = json_decode($content, true) ?: [];
            $rawRows = $this->extractRowset($json);
            $sourceType = 'UPLOAD_JSON_FILE: ' . basename($filePath);
        } else {
            // Tarik LANGSUNG dari Oracle JDE API
            $url = $this->getUrl('endpoint_base_price');
            $timeout = (int) config('services.jde.timeout', 120);

            if (empty($url)) {
                return [
                    'success' => false,
                    'message' => 'Konfigurasi JDE_HOST atau endpoint_base_price belum disetel.',
                    'total' => 0,
                    'created' => 0,
                    'updated' => 0,
                ];
            }

            try {
                Log::channel('jde')->info('Memulai request langsung ke Oracle JDE Base Price', [
                    'url' => $url,
                    'timeout' => $timeout,
                ]);

                $response = Http::withoutVerifying()
                    ->timeout($timeout)
                    ->post($url, $this->getAuthPayload());

                if (!$response->successful()) {
                    $errorMsg = "JDE Base Price API HTTP {$response->status()}: " . substr($response->body(), 0, 500);
                    Log::channel('jde')->error($errorMsg);
                    return [
                        'success' => false,
                        'message' => $errorMsg,
                        'total' => 0,
                        'created' => 0,
                        'updated' => 0,
                    ];
                }

                $json = $response->json() ?: [];
                $rawRows = $this->extractRowset($json);
            } catch (\Throwable $e) {
                $errorMsg = 'Koneksi ke Oracle JDE Base Price API gagal: ' . $e->getMessage();
                Log::channel('jde')->error($errorMsg);
                return [
                    'success' => false,
                    'message' => $errorMsg,
                    'total' => 0,
                    'created' => 0,
                    'updated' => 0,
                ];
            }
        }

        if (empty($rawRows)) {
            return [
                'success' => false,
                'message' => 'Data Base Price dari Oracle JDE kosong atau format rowset tidak sesuai.',
                'total' => 0,
                'created' => 0,
                'updated' => 0,
            ];
        }

        if ($limit > 0) {
            $rawRows = array_slice($rawRows, 0, $limit);
        }

        $created = 0;
        $updated = 0;

        DB::transaction(function () use ($rawRows, &$created, &$updated) {
            foreach ($rawRows as $row) {
                $sku = trim((string)($row['2nd_item_number'] ?? ''));
                $bu = trim((string)($row['business_unit'] ?? ''));

                if (empty($sku) || empty($bu)) {
                    continue;
                }

                $record = TemporaryJdeBasePrice::where('second_item_number', $sku)
                    ->where('business_unit', $bu)
                    ->first();

                $attributes = [
                    'short_item_no' => (int)($row['short_item_no'] ?? 0),
                    'business_unit_desc' => trim((string)($row['business_unit_desc'] ?? '')),
                    'currency_code' => trim((string)($row['currency'] ?? 'IDR')),
                    'uom' => trim((string)($row['uom'] ?? 'PC')),
                    'base_price' => (float)($row['base_price'] ?? 0),
                    'effective_date' => $this->sanitizeDate($row['effective_date'] ?? null) ?: now()->toDateString(),
                    'expired_date' => $this->sanitizeDate($row['expired_date'] ?? null),
                    'user_id' => trim((string)($row['user_id'] ?? '')),
                    'program_id' => trim((string)($row['program_id'] ?? 'P4106')),
                    'date_update' => $this->sanitizeDate($row['date_update'] ?? null),
                    'time_of_day' => !empty($row['time_of_day']) ? (int)$row['time_of_day'] : null,
                    'sync_status' => 'PENDING',
                    'raw_payload' => $row,
                ];

                if ($record) {
                    $record->update($attributes);
                    $updated++;
                } else {
                    $attributes['second_item_number'] = $sku;
                    $attributes['business_unit'] = $bu;
                    TemporaryJdeBasePrice::create($attributes);
                    $created++;
                }
            }
        });

        Log::channel('jde')->info('Penarikan Base Price JDE Selesai', [
            'source' => $sourceType,
            'total_rows' => count($rawRows),
            'created' => $created,
            'updated' => $updated,
        ]);

        return [
            'success' => true,
            'source' => $sourceType,
            'total' => count($rawRows),
            'created' => $created,
            'updated' => $updated,
            'message' => "Berhasil menarik " . count($rawRows) . " Base Price ({$created} baru, {$updated} diperbarui) ke tabel temporary.",
        ];
    }

    /**
     * 3. Tarik Langsung Data Branch Plant dari Oracle JDE (POS_ITEM_BRANCH)
     */
    public function pullBranches(?string $filePath = null, int $limit = 0): array
    {
        $rawRows = [];
        $sourceType = 'DIRECT_ORACLE_JDE_API';

        if (!empty($filePath)) {
            if (!file_exists($filePath)) {
                return [
                    'success' => false,
                    'message' => "File upload tidak ditemukan: {$filePath}",
                    'total' => 0,
                    'created' => 0,
                    'updated' => 0,
                ];
            }
            $content = file_get_contents($filePath);
            $json = json_decode($content, true) ?: [];
            $rawRows = $this->extractRowset($json);
            $sourceType = 'UPLOAD_JSON_FILE: ' . basename($filePath);
        } else {
            // Tarik LANGSUNG dari Oracle JDE API
            $url = $this->getUrl('endpoint_branch');
            $timeout = (int) config('services.jde.timeout', 120);

            if (empty($url)) {
                return [
                    'success' => false,
                    'message' => 'Konfigurasi JDE_HOST atau endpoint_branch belum disetel.',
                    'total' => 0,
                    'created' => 0,
                    'updated' => 0,
                ];
            }

            try {
                Log::channel('jde')->info('Memulai request langsung ke Oracle JDE Branch', [
                    'url' => $url,
                    'timeout' => $timeout,
                ]);

                $response = Http::withoutVerifying()
                    ->timeout($timeout)
                    ->post($url, $this->getAuthPayload());

                if (!$response->successful()) {
                    $errorMsg = "JDE Branch API HTTP {$response->status()}: " . substr($response->body(), 0, 500);
                    Log::channel('jde')->error($errorMsg);
                    return [
                        'success' => false,
                        'message' => $errorMsg,
                        'total' => 0,
                        'created' => 0,
                        'updated' => 0,
                    ];
                }

                $json = $response->json() ?: [];
                $rawRows = $this->extractRowset($json);
            } catch (\Throwable $e) {
                $errorMsg = 'Koneksi ke Oracle JDE Branch API gagal: ' . $e->getMessage();
                Log::channel('jde')->error($errorMsg);
                return [
                    'success' => false,
                    'message' => $errorMsg,
                    'total' => 0,
                    'created' => 0,
                    'updated' => 0,
                ];
            }
        }

        if (empty($rawRows)) {
            return [
                'success' => false,
                'message' => 'Data Branch dari Oracle JDE kosong atau format rowset tidak sesuai.',
                'total' => 0,
                'created' => 0,
                'updated' => 0,
            ];
        }

        if ($limit > 0) {
            $rawRows = array_slice($rawRows, 0, $limit);
        }

        $created = 0;
        $updated = 0;

        DB::transaction(function () use ($rawRows, &$created, &$updated) {
            foreach ($rawRows as $row) {
                $bu = trim((string)($row['business_unit'] ?? ''));
                if (empty($bu)) {
                    continue;
                }

                $record = TemporaryJdeBranch::where('business_unit', $bu)->first();

                $attributes = [
                    'company' => trim((string)($row['company'] ?? '18000')),
                    'cabang_code' => trim((string)($row['cabang_code'] ?? '')),
                    'cabang_desc' => trim((string)($row['cabang_desc'] ?? '')),
                    'short_item_no' => !empty($row['short_item_no']) ? (int)$row['short_item_no'] : null,
                    'process2' => trim((string)($row['process2'] ?? '')),
                    'stocking_type' => trim((string)($row['stocking_type'] ?? 'P')),
                    'user_id' => trim((string)($row['user_id'] ?? '')),
                    'program_id' => trim((string)($row['program_id'] ?? 'R5541012A')),
                    'date_update' => $this->sanitizeDate($row['date_update'] ?? null),
                    'time_of_day' => !empty($row['time_of_day']) ? (int)$row['time_of_day'] : null,
                    'sync_status' => 'PENDING',
                    'raw_payload' => $row,
                ];

                if ($record) {
                    $record->update($attributes);
                    $updated++;
                } else {
                    $attributes['business_unit'] = $bu;
                    TemporaryJdeBranch::create($attributes);
                    $created++;
                }
            }
        });

        Log::channel('jde')->info('Penarikan Branch JDE Selesai', [
            'source' => $sourceType,
            'total_rows' => count($rawRows),
            'created' => $created,
            'updated' => $updated,
        ]);

        return [
            'success' => true,
            'source' => $sourceType,
            'total' => count($rawRows),
            'created' => $created,
            'updated' => $updated,
            'message' => "Berhasil menarik " . count($rawRows) . " Cabang ({$created} baru, {$updated} diperbarui) ke tabel temporary.",
        ];
    }

    /**
     * 4. Tarik Langsung Data Customer / Toko Retail dari JDE
     */
    public function pullCustomers(?string $filePath = null, int $limit = 0): array
    {
        $rawRows = [];
        $sourceType = 'DIRECT_ORACLE_JDE_API';

        if (!empty($filePath)) {
            if (!file_exists($filePath)) {
                return [
                    'success' => false,
                    'message' => "File upload tidak ditemukan: {$filePath}",
                    'total' => 0,
                    'created' => 0,
                    'updated' => 0,
                ];
            }
            $content = file_get_contents($filePath);
            $json = json_decode($content, true) ?: [];
            $rawRows = $this->extractRowset($json);
            $sourceType = 'UPLOAD_JSON_FILE: ' . basename($filePath);
        } else {
            // Tarik LANGSUNG dari Oracle JDE API
            $url = $this->getUrl('endpoint_customer');
            $timeout = (int) config('services.jde.timeout', 120);

            if (empty($url)) {
                return [
                    'success' => false,
                    'message' => 'Konfigurasi JDE_HOST atau endpoint_customer belum disetel.',
                    'total' => 0,
                    'created' => 0,
                    'updated' => 0,
                ];
            }

            try {
                Log::channel('jde')->info('Memulai request langsung ke Oracle JDE Customer Master', [
                    'url' => $url,
                    'timeout' => $timeout,
                ]);

                $response = Http::withoutVerifying()
                    ->timeout($timeout)
                    ->post($url, $this->getAuthPayload());

                if (!$response->successful()) {
                    $errorMsg = "JDE Customer API HTTP {$response->status()}: " . substr($response->body(), 0, 500);
                    Log::channel('jde')->error($errorMsg);
                    return [
                        'success' => false,
                        'message' => $errorMsg,
                        'total' => 0,
                        'created' => 0,
                        'updated' => 0,
                    ];
                }

                $json = $response->json() ?: [];
                $rawRows = $this->extractRowset($json);
            } catch (\Throwable $e) {
                $errorMsg = 'Koneksi ke Oracle JDE Customer API gagal: ' . $e->getMessage();
                Log::channel('jde')->error($errorMsg);
                return [
                    'success' => false,
                    'message' => $errorMsg,
                    'total' => 0,
                    'created' => 0,
                    'updated' => 0,
                ];
            }
        }

        if (empty($rawRows)) {
            return [
                'success' => false,
                'message' => 'Data Customer dari Oracle JDE kosong atau format rowset tidak sesuai.',
                'total' => 0,
                'created' => 0,
                'updated' => 0,
            ];
        }

        if ($limit > 0) {
            $rawRows = array_slice($rawRows, 0, $limit);
        }

        $created = 0;
        $updated = 0;

        DB::transaction(function () use ($rawRows, &$created, &$updated) {
            foreach ($rawRows as $row) {
                $an = (int)($row['address_number'] ?? 0);
                if ($an <= 0) {
                    continue;
                }

                $bu = trim((string)($row['business_unit'] ?? ''));
                $record = TemporaryJdeCustomer::where('address_number', $an)->first();

                $attributes = [
                    'business_unit' => $bu,
                    'company' => trim((string)($row['company'] ?? '00000')),
                    'credit_limit' => (float)($row['credit_limit'] ?? 0),
                    'payment_term' => trim((string)($row['payment_term'] ?? '045')),
                    'hold_code' => trim((string)($row['hold_code'] ?? ' ')),
                    'user_id' => trim((string)($row['user_id'] ?? '')),
                    'program_id' => trim((string)($row['program_id'] ?? 'P03013')),
                    'date_update' => $this->sanitizeDate($row['date_update'] ?? null),
                    'time_update' => !empty($row['time_update']) ? (int)$row['time_update'] : null,
                    'alpha_name' => trim((string)($row['alpha_name'] ?? '-')),
                    'channel_code' => trim((string)($row['channel_code'] ?? '15')),
                    'channel_desc' => trim((string)($row['channel_desc'] ?? 'RETAIL')),
                    'cabang_code' => trim((string)($row['cabang_code'] ?? '')),
                    'cabang_desc' => trim((string)($row['cabang_desc'] ?? '')),
                    'sync_status' => 'PENDING',
                    'raw_payload' => $row,
                ];

                if ($record) {
                    $record->update($attributes);
                    $updated++;
                } else {
                    $attributes['address_number'] = $an;
                    TemporaryJdeCustomer::create($attributes);
                    $created++;
                }
            }
        });

        Log::channel('jde')->info('Penarikan Customer JDE Selesai', [
            'source' => $sourceType,
            'total_rows' => count($rawRows),
            'created' => $created,
            'updated' => $updated,
        ]);

        return [
            'success' => true,
            'source' => $sourceType,
            'total' => count($rawRows),
            'created' => $created,
            'updated' => $updated,
            'message' => "Berhasil menarik " . count($rawRows) . " Toko Retail ({$created} baru, {$updated} diperbarui) ke tabel temporary.",
        ];
    }

    /**
     * 5. Tarik Seluruh Data Master Sekaligus Langsung dari JDE
     */
    public function pullAll(int $limit = 0): array
    {
        $resBranch = $this->pullBranches(null, $limit);
        $resCust = $this->pullCustomers(null, $limit);
        $resItem = $this->pullItemMasters(null, $limit);
        $resPrice = $this->pullBasePrices(null, $limit);

        return [
            'success' => true,
            'branch' => $resBranch,
            'customer' => $resCust,
            'item_master' => $resItem,
            'base_price' => $resPrice,
            'message' => 'Penarikan seluruh data JDE ke tabel temporary selesai diproses.',
        ];
    }
}
