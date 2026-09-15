<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Order\Order;
use App\Models\Shipping\Courier;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class BiteshipService
{
    private ?string $apiKey;
    private string $baseUrl;
    private array $originConfig;

    public function __construct()
    {
        $this->apiKey = config('services.biteship.api_key') ?: env('BITESHIP_API_KEY');
        $this->baseUrl = rtrim((string) (config('services.biteship.base_url') ?: env('BITESHIP_BASE_URL', 'https://api.biteship.com')), '/');
        $this->originConfig = [
            'area_id' => config('services.biteship.origin_area_id') ?: env('BITESHIP_ORIGIN_AREA_ID'),
            'postal_code' => config('services.biteship.origin_postal_code') ?: env('BITESHIP_ORIGIN_POSTAL_CODE', '40552'),
            'address' => config('services.biteship.origin_address') ?: env('BITESHIP_ORIGIN_ADDRESS', 'Jl. Raya Barat No. 802, Cimareme, Kec. Ngamprah, Kabupaten Bandung Barat, Jawa Barat 40552'),
            'contact_name' => config('services.biteship.origin_contact_name') ?: env('BITESHIP_ORIGIN_CONTACT_NAME', 'Admin Gudang IMG'),
            'contact_phone' => config('services.biteship.origin_contact_phone') ?: env('BITESHIP_ORIGIN_CONTACT_PHONE', '081112345678'),
        ];
    }

    /**
     * Check if Biteship integration is active and configured with an API key.
     */
    public function isConfigured(): bool
    {
        return !empty($this->apiKey);
    }

    public function getApiKey(): ?string
    {
        return $this->apiKey;
    }

    public function getBaseUrl(): string
    {
        return $this->baseUrl;
    }

    public function getOriginConfig(): array
    {
        return $this->originConfig;
    }

    /**
     * Map internal courier code to Biteship courier company code.
     */
    public function mapCourierCompany(string $code): string
    {
        $clean = strtolower(trim($code));
        return match ($clean) {
            'jt', 'j&t', 'j&t express' => 'jnt',
            'sicepat express' => 'sicepat',
            'jne express' => 'jne',
            'pos indonesia' => 'pos',
            default => $clean,
        };
    }

    /**
     * Standard Biteship catalog containing courier services and default services.
     */
    public static array $biteshipServiceCatalog = [
        'jne' => [
            'name' => 'JNE Express',
            'services' => [
                'reg' => 'Reguler (REG)',
                'yes' => 'Yakin Esok Sampai (YES)',
                'oke' => 'Ongkos Kirim Ekonomis (OKE)',
                'jtr' => 'JNE Trucking (JTR)',
            ],
            'default_service' => 'reg',
        ],
        'sicepat' => [
            'name' => 'SiCepat Express',
            'services' => [
                'reg' => 'Reguler',
                'siuntung' => 'SiUntung',
                'best' => 'Besok Sampai Tujuan (BEST)',
                'gokil' => 'Cargo Kilat (GOKIL)',
            ],
            'default_service' => 'reg',
        ],
        'jnt' => [
            'name' => 'J&T Express',
            'services' => [
                'ez' => 'EZ (Reguler)',
            ],
            'default_service' => 'ez',
        ],
        'pos' => [
            'name' => 'Pos Indonesia',
            'services' => [
                'reg' => 'Pos Reguler',
                'sameday' => 'Pos Sameday',
                'nextday' => 'Pos Next Day',
                'cargo' => 'Pos Kargo',
            ],
            'default_service' => 'reg',
        ],
        'tiki' => [
            'name' => 'TIKI',
            'services' => [
                'reg' => 'Regular Service (REG)',
                'ons' => 'Over Night Service (ONS)',
                'eko' => 'Economy Service (EKO)',
                'sds' => 'Same Day Service (SDS)',
            ],
            'default_service' => 'reg',
        ],
        'anteraja' => [
            'name' => 'Anteraja',
            'services' => [
                'reg' => 'Reguler',
                'same_day' => 'Same Day',
            ],
            'default_service' => 'reg',
        ],
        'ninja' => [
            'name' => 'Ninja Xpress',
            'services' => [
                'standard' => 'Standard',
            ],
            'default_service' => 'standard',
        ],
        'lion' => [
            'name' => 'Lion Parcel',
            'services' => [
                'reg_pack' => 'REGPACK',
                'big_pack' => 'BIGPACK',
            ],
            'default_service' => 'reg_pack',
        ],
        'sentralcargo' => [
            'name' => 'Sentral Cargo',
            'services' => [
                'land_non_electronic' => 'Darat Non-Elektronik',
                'land_electronic' => 'Darat Elektronik',
                'air_non_electronic' => 'Udara Non-Elektronik',
            ],
            'default_service' => 'land_non_electronic',
        ],
        'idexpress' => [
            'name' => 'IDexpress',
            'services' => [
                'reg' => 'Reguler Standard',
                'reg_half_kilo' => 'Half Kilo',
                'idtruck' => 'IDtruck Kargo',
            ],
            'default_service' => 'reg',
        ],
        'paxel' => [
            'name' => 'Paxel',
            'services' => [
                'small' => 'Paxel Small',
                'medium' => 'Paxel Medium',
                'large' => 'Paxel Large',
                'paxel_big' => 'Paxel Big (Kargo)',
            ],
            'default_service' => 'medium',
        ],
        'gojek' => [
            'name' => 'Gojek',
            'services' => [
                'instant' => 'GoSend Instant',
                'same_day' => 'GoSend Same Day',
            ],
            'default_service' => 'instant',
        ],
        'grab' => [
            'name' => 'Grab',
            'services' => [
                'instant' => 'GrabExpress Instant',
                'same_day' => 'GrabExpress Same Day',
            ],
            'default_service' => 'instant',
        ],
    ];

    /**
     * Get list of supported couriers synchronized with the database 'couriers' table.
     * When couriers are modified, created, or deactivated in the couriers table,
     * this list immediately reflects the database changes while attaching supported Biteship services.
     */
    public static function getSupportedCouriers(): array
    {
        $catalog = self::$biteshipServiceCatalog;

        try {
            $dbCouriers = Courier::query()
                ->where('is_active', true)
                ->where('deleted', false)
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get();

            if ($dbCouriers->isNotEmpty()) {
                $result = [];
                $serviceInstance = new self();

                foreach ($dbCouriers as $courier) {
                    $rawCode = strtolower(trim((string) $courier->code));
                    $mappedCompany = $serviceInstance->mapCourierCompany($rawCode);

                    // Ambil konfigurasi service dari katalog Biteship
                    $knownConfig = $catalog[$mappedCompany]
                                ?? $catalog[$rawCode]
                                ?? null;

                    $services = $knownConfig['services'] ?? [
                        'reg' => 'Reguler / Standard',
                    ];
                    $defaultService = $knownConfig['default_service'] ?? array_key_first($services) ?? 'reg';

                    $result[$rawCode] = [
                        'id' => (string) $courier->id,
                        'code' => $courier->code,
                        'name' => $courier->name, // Mengambil langsung dari tabel couriers di DB
                        'courier_company' => $mappedCompany,
                        'courier_type' => $courier->courier_type ?? 'expedisi',
                        'services' => $services,
                        'default_service' => $defaultService,
                        'is_active' => (bool) $courier->is_active,
                        'sort_order' => (int) $courier->sort_order,
                    ];
                }

                return $result;
            }
        } catch (\Throwable $e) {
            // Fallback ke catalog bawaan jika terjadi kendala pada database
        }

        return $catalog;
    }

    /**
     * Get available service types for a courier company.
     */
    public function getCourierServices(string $courierCompany): array
    {
        $code = strtolower(trim($courierCompany));
        $mapped = $this->mapCourierCompany($code);
        $couriers = self::getSupportedCouriers();

        // 1. Cek langsung dengan code atau mapped company
        if (isset($couriers[$code]['services'])) {
            return $couriers[$code]['services'];
        }
        if (isset($couriers[$mapped]['services'])) {
            return $couriers[$mapped]['services'];
        }

        // 2. Cek apakah ada courier dengan courier_company matching
        foreach ($couriers as $item) {
            if ((($item['courier_company'] ?? '') === $mapped || ($item['code'] ?? '') === $code) && !empty($item['services'])) {
                return $item['services'];
            }
        }

        // 3. Fallback ke katalog standar Biteship
        if (isset(self::$biteshipServiceCatalog[$mapped]['services'])) {
            return self::$biteshipServiceCatalog[$mapped]['services'];
        }

        return ['reg' => 'Standard / Regular'];
    }

    /**
     * Get standard default service type for a courier company.
     */
    public function getDefaultServiceForCourier(string $courierCompany): string
    {
        $code = strtolower(trim($courierCompany));
        $mapped = $this->mapCourierCompany($code);
        $couriers = self::getSupportedCouriers();

        if (isset($couriers[$code]['default_service'])) {
            return $couriers[$code]['default_service'];
        }
        if (isset($couriers[$mapped]['default_service'])) {
            return $couriers[$mapped]['default_service'];
        }

        foreach ($couriers as $item) {
            if ((($item['courier_company'] ?? '') === $mapped || ($item['code'] ?? '') === $code) && !empty($item['default_service'])) {
                return $item['default_service'];
            }
        }

        if (isset(self::$biteshipServiceCatalog[$mapped]['default_service'])) {
            return self::$biteshipServiceCatalog[$mapped]['default_service'];
        }

        return 'reg';
    }

    /**
     * Build standard Biteship shipment creation payload from an Order model.
     */
    public function buildOrderPayload(
        Order $order,
        ?string $courierCompany = null,
        ?string $courierType = null,
        ?string $originNote = null,
        ?string $destinationNote = null
    ): array {
        // 1. Resolve Courier
        if (empty($courierCompany)) {
            $rawCourier = $order->courier?->code ?? $order->courier_name ?? 'jne';
            $courierCompany = $this->mapCourierCompany($rawCourier);
        } else {
            $courierCompany = $this->mapCourierCompany($courierCompany);
        }

        if (empty($courierType)) {
            $courierType = $this->getDefaultServiceForCourier($courierCompany);
        }

        // 2. Resolve Destination Data
        $shippingMeta = $order->meta['shipping_address'] ?? [];
        $destContactName = $shippingMeta['recipient_name'] ?? $shippingMeta['name'] ?? $order->customer?->name ?? 'Pelanggan';
        $destContactPhone = $shippingMeta['phone'] ?? $order->customer?->phone ?? '08123456789';

        $destAddrParts = array_filter([
            $shippingMeta['address'] ?? null,
            $shippingMeta['sub_district'] ?? null,
            $shippingMeta['city'] ?? null,
            $shippingMeta['province'] ?? null,
        ]);

        if (empty($destAddrParts) && $order->customer && method_exists($order->customer, 'addresses') && $order->customer->addresses->isNotEmpty()) {
            $firstAddr = $order->customer->addresses->first();
            $destAddrParts = [$firstAddr->address];
        }

        $destAddress = implode(', ', $destAddrParts);
        if (empty($destAddress)) {
            $destAddress = 'Alamat Pengiriman Pelanggan';
        }

        $postalCode = (int) ($shippingMeta['postal_code'] ?? 0);

        // 3. Format Items
        $items = [];
        foreach ($order->items as $item) {
            $qty = max(1, (int) $item->quantity);
            $price = max(1000, (int) round((float) ($item->unit_price ?? $item->total ?? 10000)));
            $name = (string) ($item->name ?? 'Produk');

            // Determine weight in grams (Biteship expects grams, min 100g)
            $weightVal = (float) ($item->variant?->weight ?? $item->product?->weight ?? 0);
            if ($weightVal > 0 && $weightVal < 25) {
                $weightGrams = (int) round($weightVal * 1000);
            } elseif ($weightVal >= 25) {
                $weightGrams = (int) round($weightVal);
            } else {
                $weightGrams = 1000; // Default 1 kg
            }

            $items[] = [
                'name' => mb_substr($name, 0, 80),
                'description' => mb_substr($name, 0, 80),
                'value' => $price,
                'quantity' => $qty,
                'weight' => max(100, $weightGrams),
                'length' => 10,
                'width' => 10,
                'height' => 10,
            ];
        }

        if (empty($items)) {
            $items[] = [
                'name' => 'Paket Pesanan #' . ($order->order_number ?? substr($order->id, 0, 8)),
                'description' => 'Paket Pengiriman Barang',
                'value' => max(1000, (int) round((float) $order->total)),
                'quantity' => 1,
                'weight' => 1000,
                'length' => 10,
                'width' => 10,
                'height' => 10,
            ];
        }

        $payload = [
            'origin_contact_name' => $this->originConfig['contact_name'],
            'origin_contact_phone' => $this->originConfig['contact_phone'],
            'origin_address' => $this->originConfig['address'],
            'destination_contact_name' => $destContactName,
            'destination_contact_phone' => $destContactPhone,
            'destination_address' => $destAddress,
            'courier_company' => $courierCompany,
            'courier_type' => strtolower($courierType),
            'delivery_type' => 'now',
            'order_note' => 'Pesanan #' . ($order->order_number ?? substr($order->id, 0, 8)),
            'reference_id' => (string) ($order->order_number ?? $order->id),
            'metadata' => [
                'order_id' => (string) $order->id,
                'order_number' => (string) ($order->order_number ?? $order->id),
            ],
            'items' => $items,
        ];

        if (!empty($this->originConfig['postal_code'])) {
            $payload['origin_postal_code'] = (int) $this->originConfig['postal_code'];
        }

        if (!empty($this->originConfig['area_id'])) {
            $payload['origin_area_id'] = $this->originConfig['area_id'];
        }

        if ($postalCode > 0) {
            $payload['destination_postal_code'] = $postalCode;
        }

        if (!empty($destinationNote)) {
            $payload['destination_note'] = mb_substr($destinationNote, 0, 200);
        } elseif (!empty($order->notes)) {
            $payload['destination_note'] = mb_substr($order->notes, 0, 200);
        }

        if (!empty($originNote)) {
            $payload['origin_note'] = mb_substr($originNote, 0, 200);
        }

        return $payload;
    }

    /**
     * Format and write Biteship logs in a clean, human-readable multi-line layout
     * to both laravel.log and dedicated daily biteship channel.
     *
     * @param string $level (info, warning, error)
     * @param string $category (ACTIVITY, ISSUE, TRACKING)
     * @param string $title
     * @param array $details
     */
    public function logStructured(string $level, string $category, string $title, array $details = []): void
    {
        $user = auth()->user()?->username ?? auth()->user()?->name ?? 'system';
        $ip = null;
        try {
            $ip = request()?->ip();
        } catch (\Throwable) {}
        $ip = $ip ?? 'cli';

        // 1. Build beautiful human-readable banner
        $lines = [];
        $lines[] = "";
        $lines[] = "================================================================================";
        $lines[] = sprintf("[%s %s] %s", 'BITESHIP', strtoupper($category), $title);
        $lines[] = "--------------------------------------------------------------------------------";
        $lines[] = sprintf("%-18s: %s", 'Waktu', now()->format('Y-m-d H:i:s T'));
        $lines[] = sprintf("%-18s: %s (%s)", 'User / IP', $user, $ip);

        foreach ($details as $key => $val) {
            if (is_array($val)) {
                $json = json_encode($val, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
                $indented = implode("\n", array_map(fn($line) => '  ' . $line, explode("\n", (string) $json)));
                $lines[] = sprintf("%-18s:\n%s", ucwords(str_replace('_', ' ', (string) $key)), $indented);
            } else {
                if (is_bool($val)) {
                    $val = $val ? 'Ya (true)' : 'Tidak (false)';
                } elseif ($val === null || $val === '') {
                    $val = '-';
                }
                $lines[] = sprintf("%-18s: %s", ucwords(str_replace('_', ' ', (string) $key)), (string) $val);
            }
        }
        $lines[] = "================================================================================";

        $formattedBanner = implode("\n", $lines);

        $context = array_merge([
            'category' => $category,
            'user' => $user,
            'ip' => $ip,
        ], $details);

        // 1. Write to default application logger (laravel.log)
        try {
            Log::$level($formattedBanner);
        } catch (\Throwable) {}

        // 2. Write to dedicated daily biteship channel (storage/logs/biteship-YYYY-MM-DD.log)
        try {
            Log::channel('biteship')->$level($formattedBanner);
        } catch (\Throwable) {}
    }

    /**
     * Log user or admin activity regarding Biteship actions.
     */
    public function logActivity(string $action, string $message, array $context = []): void
    {
        $this->logStructured('info', 'ACTIVITY', $message, $context);
    }

    /**
     * Log issue/error for Biteship tracing and troubleshooting.
     */
    public function logIssue(string $action, string $message, array $context = []): void
    {
        $this->logStructured('error', 'ISSUE', $message, $context);
    }

    /**
     * Create an order / shipment on Biteship.
     *
     * @param array $payload
     * @return array
     */
    public function createOrder(array $payload): array
    {
        $orderNumber = $payload['metadata']['order_number'] ?? ($payload['reference_id'] ?? 'unknown');
        $orderId = $payload['metadata']['order_id'] ?? '-';
        $courier = strtoupper($payload['courier_company'] ?? '') . ' (' . strtoupper($payload['courier_type'] ?? '') . ')';
        $originPostal = $payload['origin_postal_code'] ?? ($this->originConfig['postal_code'] ?? '-');
        $destPostal = $payload['destination_postal_code'] ?? '-';

        if (!$this->isConfigured()) {
            $this->logStructured('warning', 'ISSUE', "API Key Biteship Belum Dikonfigurasi di .env", [
                'Nomor Order' => $orderNumber,
                'Order ID' => $orderId,
                'Keterangan' => 'BITESHIP_API_KEY kosong. Silakan isi di file .env',
                'Payload Request' => $payload,
            ]);

            return [
                'success' => false,
                'message' => 'Biteship API Key belum dikonfigurasi di file .env (BITESHIP_API_KEY).',
            ];
        }

        $startTime = microtime(true);

        try {
            $response = Http::withHeaders([
                'Authorization' => $this->apiKey,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ])->timeout(20)->post("{$this->baseUrl}/v1/orders", $payload);

            $durationMs = round((microtime(true) - $startTime) * 1000, 2);
            $body = $response->json() ?? [];

            if ($response->successful() && ($body['success'] ?? false)) {
                $waybillId = data_get($body, 'courier.waybill_id')
                    ?? data_get($body, 'courier.tracking_id')
                    ?? data_get($body, 'data.courier.waybill_id')
                    ?? data_get($body, 'data.courier.tracking_id')
                    ?? data_get($body, 'id');

                $biteshipOrderId = $body['id'] ?? data_get($body, 'data.id');
                $trackingUrl = data_get($body, 'courier.link') ?? data_get($body, 'data.courier.link');
                $courierCompany = data_get($body, 'courier.company') ?? ($payload['courier_company'] ?? '');

                $this->logStructured('info', 'ACTIVITY', "Pesanan Berhasil Dibuat di Biteship (#{$orderNumber})", [
                    'Nomor Order' => $orderNumber,
                    'Order ID' => $orderId,
                    'Ekspedisi' => strtoupper($courierCompany) . ' (' . strtoupper($payload['courier_type'] ?? '') . ')',
                    'Nomor Resi (AWB)' => $waybillId ?: 'Belum terbit',
                    'Biteship Order ID' => $biteshipOrderId,
                    'Rute Pengiriman' => "{$originPostal} -> {$destPostal}",
                    'Penerima' => ($payload['destination_contact_name'] ?? '-') . ' (' . ($payload['destination_contact_phone'] ?? '-') . ')',
                    'Alamat Tujuan' => $payload['destination_address'] ?? '-',
                    'Tracking Link' => $trackingUrl ?: '-',
                    'Total Item' => count($payload['items'] ?? []) . ' item',
                    'Durasi Respon' => "{$durationMs} ms (HTTP {$response->status()})",
                ]);

                return [
                    'success' => true,
                    'message' => 'Pengiriman berhasil dibuat di Biteship!',
                    'order_id' => $biteshipOrderId,
                    'waybill_id' => $waybillId,
                    'courier' => [
                        'company' => data_get($body, 'courier.company'),
                        'name' => data_get($body, 'courier.name'),
                        'tracking_id' => data_get($body, 'courier.tracking_id'),
                        'waybill_id' => $waybillId,
                        'tracking_url' => $trackingUrl,
                    ],
                    'raw' => $body,
                ];
            }

            $errorMessage = $body['error']
                ?? $body['message']
                ?? ('Biteship API error: HTTP ' . $response->status());
            $errorCode = $body['code'] ?? $response->status();

            $this->logStructured('error', 'ISSUE', "Pembuatan Pesanan Ditolak oleh Biteship (#{$orderNumber})", [
                'Nomor Order' => $orderNumber,
                'Order ID' => $orderId,
                'Pesan Error' => $errorMessage,
                'Kode Error' => $errorCode . ' (HTTP ' . $response->status() . ')',
                'Ekspedisi' => $courier,
                'Rute Pengiriman' => "{$originPostal} -> {$destPostal}",
                'Durasi Respon' => "{$durationMs} ms",
                'Payload Request' => $payload,
                'Respon Biteship' => $body,
            ]);

            return [
                'success' => false,
                'message' => $errorMessage,
                'raw' => $body,
            ];
        } catch (\Throwable $e) {
            $durationMs = round((microtime(true) - $startTime) * 1000, 2);

            $this->logStructured('error', 'ISSUE', "Koneksi ke API Biteship Gagal (#{$orderNumber})", [
                'Nomor Order' => $orderNumber,
                'Order ID' => $orderId,
                'Pesan Error' => $e->getMessage(),
                'Lokasi Error' => $e->getFile() . ':' . $e->getLine(),
                'Durasi' => "{$durationMs} ms",
                'Payload Request' => $payload,
            ]);

            return [
                'success' => false,
                'message' => 'Koneksi ke Biteship gagal: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Retrieve live tracking data for a waybill.
     */
    public function getTracking(string $waybillId, string $courierCode): array
    {
        if (!$this->isConfigured() || empty(trim($waybillId))) {
            return [];
        }

        $courierCode = $this->mapCourierCompany($courierCode);
        $startTime = microtime(true);

        try {
            $response = Http::withHeaders([
                'Authorization' => $this->apiKey,
                'Accept' => 'application/json',
            ])->timeout(12)->get("{$this->baseUrl}/v1/trackings/{$waybillId}/couriers/{$courierCode}");

            $durationMs = round((microtime(true) - $startTime) * 1000, 2);

            if ($response->successful()) {
                $body = $response->json() ?? [];
                $normalized = $this->normalizeTrackingResponse($body);

                $latestEvent = !empty($normalized['events']) ? end($normalized['events']) : null;
                $latestDesc = $latestEvent ? (($latestEvent['time'] ?? '') . ' - ' . ($latestEvent['description'] ?? '')) : '-';

                $this->logStructured('info', 'ACTIVITY', "Pelacakan Live Resi Berhasil ({$waybillId})", [
                    'Nomor Resi (AWB)' => $waybillId,
                    'Ekspedisi' => strtoupper($courierCode),
                    'Status Terkini' => ($normalized['status_label'] ?? $normalized['status']) . " ({$normalized['status']})",
                    'Jumlah Checkpoint' => count($normalized['events'] ?? []) . ' titik pembaruan',
                    'Update Terakhir' => $latestDesc,
                    'Durasi Respon' => "{$durationMs} ms",
                ]);

                return $normalized;
            }

            $this->logStructured('warning', 'ISSUE', "Data Pelacakan Resi Belum Ditemukan ({$waybillId})", [
                'Nomor Resi (AWB)' => $waybillId,
                'Ekspedisi' => strtoupper($courierCode),
                'HTTP Status' => $response->status(),
                'Durasi Respon' => "{$durationMs} ms",
                'Respon Biteship' => $response->json() ?? [],
            ]);
        } catch (\Throwable $e) {
            $this->logStructured('error', 'ISSUE', "Gagal Menghubungi API Tracking Biteship ({$waybillId})", [
                'Nomor Resi (AWB)' => $waybillId,
                'Ekspedisi' => strtoupper($courierCode),
                'Pesan Error' => $e->getMessage(),
            ]);
        }

        return [];
    }

    /**
     * Normalize tracking response from Biteship.
     */
    private function normalizeTrackingResponse(array $raw): array
    {
        $history = $raw['history'] ?? [];
        $events = [];

        foreach ($history as $h) {
            $events[] = [
                'time' => $h['updated_at'] ?? '',
                'status' => $h['status'] ?? 'ON_PROCESS',
                'location' => $h['city'] ?? $h['location'] ?? '',
                'description' => $h['note'] ?? $h['service_type'] ?? '',
            ];
        }

        return [
            'provider' => strtolower($raw['courier']['company'] ?? 'biteship'),
            'provider_name' => $raw['courier']['name'] ?? 'Ekspedisi',
            'waybill_id' => $raw['waybill_id'] ?? '',
            'status' => strtoupper($raw['status'] ?? 'ON_PROCESS'),
            'status_label' => ucwords(str_replace('_', ' ', $raw['status'] ?? 'Dalam Proses')),
            'current_location' => $raw['destination']['address'] ?? '',
            'events' => $events,
            'link' => $raw['link'] ?? null,
        ];
    }
}
