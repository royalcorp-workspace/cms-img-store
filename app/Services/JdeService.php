<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Order\Order;
use App\Models\Store\Store;
use App\Models\Store\StoreChannel;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class JdeService
{
    /**
     * Cek apakah order memenuhi syarat untuk dikirim ke JDE.
     * Syarat baru: Order sudah bayar (lunas) atau status proses sudah dimulai,
     * tidak memerlukan nomor resi terlebih dahulu (flow dimajukan).
     */
    public function isOrderEligible(Order $order): bool
    {
        // 1. Jika sudah sukses dikirim, jangan kirim ulang
        if ((int) $order->jde_push_status === Order::JDE_STATUS_SUCCESS) {
            return false;
        }

        // 2. Jangan kirim jika draft, dibatalkan, atau diretur
        if (in_array((int) $order->status, [Order::STATUS_DRAFT, Order::STATUS_CANCELLED, Order::STATUS_RETURNED], true)) {
            return false;
        }

        // 3. Status harus sudah bayar atau sudah dimulai (Confirmed / Processing / Shipped / Delivered)
        if (!$order->isPaidOrStarted()) {
            return false;
        }

        // 4. Order harus memiliki item produk
        if ($order->relationLoaded('items')) {
            if ($order->items->isEmpty()) {
                return false;
            }
        } elseif (!$order->items()->exists()) {
            return false;
        }

        return true;
    }

    /**
     * Susun data payload dan pastikan Business Unit & Address Number terisi.
     */
    public function buildPayload(Order $order): array
    {
        $order->loadMissing(['items.variant', 'items.product', 'customer', 'store', 'storeChannel']);

        $businessUnit = null;
        $addressNumber = null;

        // 1. Dari kolom tabel orders jika ada
        if (!empty($order->business_unit)) {
            $businessUnit = trim((string) $order->business_unit);
        }
        if (!empty($order->address_number)) {
            $addressNumber = (int) $order->address_number;
        }

        // 2. Dari meta order jika belum ada
        if (!$businessUnit && !empty($order->meta['business_unit'])) {
            $businessUnit = trim((string) $order->meta['business_unit']);
        }
        if (!$addressNumber && !empty($order->meta['address_number'])) {
            $addressNumber = (int) $order->meta['address_number'];
        }

        // 3. Dari relasi StoreChannel
        $channel = $order->storeChannel;
        if (!$channel && !empty($order->store_channel_id)) {
            $channel = StoreChannel::with('store.group')->find($order->store_channel_id);
        }
        if (!$channel && !empty($order->meta['store_channel_id'])) {
            $channel = StoreChannel::with('store.group')->find($order->meta['store_channel_id']);
        }

        if ($channel) {
            $rawCode = trim((string) $channel->code);
            $cleanCode = preg_replace('/-[A-Za-z0-9]+$/', '', $rawCode);
            $businessUnit = $businessUnit ?: ($cleanCode ?: ($channel->store?->code ?? $channel->store?->group?->code));

            if (!$addressNumber && !empty($channel->address_number)) {
                $addressNumber = (int) $channel->address_number;
            }
            if (!$addressNumber && !empty($channel->store?->address_number)) {
                $addressNumber = (int) $channel->store->address_number;
            }
        }

        // 4. Dari relasi Store
        $store = $order->store;
        if (!$store && !empty($order->store_id)) {
            $store = Store::with('group')->find($order->store_id);
        }
        if (!$store && !empty($order->meta['store_id'])) {
            $store = Store::with('group')->find($order->meta['store_id']);
        }

        if ($store) {
            $businessUnit = $businessUnit ?: ($store->code ?? $store->group?->code);
            if (!$addressNumber && !empty($store->address_number)) {
                $addressNumber = (int) $store->address_number;
            }
        }

        // 5. Default fallback dari environment / konfigurasi
        $businessUnit = (string) ($businessUnit ?: config('services.jde.business_unit', env('JDE_BUSINESS_UNIT', '18011')));
        $addressNumber = (int) ($addressNumber ?: config('services.jde.address_number', env('JDE_ADDRESS_NUMBER', 100228)));

        // Persist ke order jika ada perubahan
        if ($order->business_unit !== $businessUnit || (int) $order->address_number !== $addressNumber) {
            $order->business_unit = $businessUnit;
            $order->address_number = $addressNumber;
            if ($channel && !$order->store_channel_id) {
                $order->store_channel_id = $channel->id;
            }
            if ($store && !$order->store_id) {
                $order->store_id = $store->id;
            }
            $order->saveQuietly();
        }

        $cleanOrderNumber = ltrim((string) $order->order_number, '#');
        $prefix = (string) config('services.jde.keterangan_prefix', env('JDE_KETERANGAN_PREFIX', 'WEB-IMG '));
        if (str_ends_with($prefix, '-') || str_ends_with($prefix, '_')) {
            $keterangan = $prefix . $cleanOrderNumber;
        } else {
            $keterangan = rtrim($prefix) . ' ' . $cleanOrderNumber;
        }

        $orderLines = [];
        foreach ($order->items as $item) {
            $secondItemNumber = method_exists($item, 'getJdeItemNumber') ? $item->getJdeItemNumber() : ($item->sku ?? '');

            $orderLines[] = [
                '2nd_item_number' => (string) $secondItemNumber,
                'qty' => (int) $item->quantity,
            ];
        }

        $username = config('services.jde.username', env('JDE_USERNAME', 'UDO'));
        $password = config('services.jde.password', env('JDE_PASSWORD', 'udoroyal'));
        $environment = config('services.jde.environment', env('JDE_ENVIRONMENT', 'JPY920'));
        $role = config('services.jde.role', env('JDE_ROLE', '*ALL'));

        return [
            'username' => (string) $username,
            'password' => (string) $password,
            'environment' => (string) $environment,
            'role' => (string) $role,
            'business_unit' => $businessUnit,
            'address_number' => $addressNumber,
            'keterangan' => $keterangan,
            'orderLines' => $orderLines,
        ];
    }

    /**
     * Eksekusi pengiriman data order ke JDE Orchestrator.
     * Mengembalikan array hasil pengiriman dengan status success/failure.
     */
    public function pushOrder(Order $order, string $actor = 'queue_worker'): array
    {
        // 1. Guard idempotency: jangan kirim ulang jika sudah sukses
        if ((int) $order->jde_push_status === Order::JDE_STATUS_SUCCESS) {
            return [
                'success' => true,
                'already_sent' => true,
                'message' => 'Pesanan #' . $order->order_number . ' sudah berhasil di-push ke JDE sebelumnya.',
                'jde_push_status' => Order::JDE_STATUS_SUCCESS,
            ];
        }

        // 2. Tandai sedang diproses (Processing)
        $order->jde_push_status = Order::JDE_STATUS_PROCESSING;
        $order->saveQuietly();

        $payload = $this->buildPayload($order);
        $jsonPayload = json_encode($payload, JSON_UNESCAPED_SLASHES);

        // Masking password untuk logging & meta
        $logPayload = $payload;
        if (!empty($logPayload['password'])) {
            $logPayload['password'] = '********';
        }

        $endpoint = config('services.jde.endpoint', env('JDE_ENDPOINT'));
        $timeout = (int) config('services.jde.timeout', 30);
        $meta = $order->meta ?? [];
        $retryCount = (int) ($meta['jde_push_retry_count'] ?? 0);

        if (empty($endpoint)) {
            $order->jde_push_status = Order::JDE_STATUS_FAILED;
            $meta['jde_push_error'] = 'JDE_ENDPOINT not configured in environment';
            $meta['jde_push_failed_at'] = now()->toDateTimeString();
            $order->meta = $meta;
            $order->saveQuietly();

            $this->logJde('warning', 'Push Order Aborted', [
                'order_number' => $order->order_number,
                'order_id' => $order->id,
                'reason' => 'JDE_ENDPOINT not configured in .env',
            ]);

            return [
                'success' => false,
                'message' => 'Konfigurasi JDE_ENDPOINT belum diset pada environment (.env).',
                'jde_push_status' => Order::JDE_STATUS_FAILED,
            ];
        }

        $this->logJde('info', 'Push Order Initiated', [
            'order_number' => $order->order_number,
            'order_id' => $order->id,
            'actor' => $actor,
            'retry_count' => $retryCount,
            'endpoint' => $endpoint,
            'payload' => $logPayload,
        ]);

        try {
            // Cek jika mode mock aktif (berguna untuk testing local tanpa VPN/JDE fisik)
            if (config('services.jde.mock', false)) {
                $isOk = true;
                $responseData = [
                    'mock' => true,
                    'status' => 'SUCCESS',
                    'message' => 'Mock JDE Orchestrator Success',
                    'order_number' => $order->order_number,
                ];
            } else {
                $httpClient = Http::withoutVerifying()
                    ->timeout($timeout)
                    ->withHeaders([
                        'Accept' => '*/*',
                        'Content-Type' => 'application/json',
                        'User-Agent' => 'PostmanRuntime/7.39.0',
                        'Cache-Control' => 'no-cache',
                    ]);

                $response = $httpClient->withBody($jsonPayload, 'application/json')->post($endpoint);
                $responseData = $response->json();
                $isOk = $response->successful();

                if ($isOk && is_array($responseData) && isset($responseData['hasErrors']) && $responseData['hasErrors'] === true) {
                    $isOk = false;
                }
            }

            $meta['jde_push_payload'] = $logPayload;
            $meta['jde_push_response'] = $responseData ?? ($response->body() ?? null);
            $meta['jde_pushed_at'] = now()->toDateTimeString();
            $meta['jde_pushed_by'] = $actor;
            $meta['jde_last_attempt_at'] = now()->toDateTimeString();

            if ($isOk) {
                $order->jde_push_status = Order::JDE_STATUS_SUCCESS;
                $order->jde_push_date = now()->format('Y-m-d');
                unset($meta['jde_push_error']);

                $this->logJde('info', 'Push Order Success', [
                    'order_number' => $order->order_number,
                    'order_id' => $order->id,
                    'actor' => $actor,
                    'response' => $responseData ?? ($response->body() ?? null),
                ]);

                $order->meta = $meta;
                $order->save();

                return [
                    'success' => true,
                    'message' => 'Pesanan #' . $order->order_number . ' berhasil di-push ke JDE.',
                    'jde_push_status' => Order::JDE_STATUS_SUCCESS,
                    'jde_push_status_label' => $order->jdePushStatusLabel(),
                    'jde_push_date' => $order->jde_push_date?->format('d M Y') ?? now()->format('d M Y'),
                    'response' => $responseData,
                ];
            }

            // JDE mengembalikan error status / payload hasErrors
            $order->jde_push_status = Order::JDE_STATUS_FAILED;
            $meta['jde_push_error'] = is_array($responseData)
                ? json_encode($responseData)
                : ((isset($response) ? $response->body() : '') ?: 'HTTP status ' . (isset($response) ? $response->status() : 500));
            $meta['jde_push_failed_at'] = now()->toDateTimeString();
            $meta['jde_push_retry_count'] = $retryCount + 1;

            $this->logJde('warning', 'Push Order Error Response', [
                'order_number' => $order->order_number,
                'order_id' => $order->id,
                'status_code' => isset($response) ? $response->status() : 500,
                'error' => $meta['jde_push_error'],
                'payload' => $logPayload,
            ]);

            $order->meta = $meta;
            $order->save();

            return [
                'success' => false,
                'message' => 'JDE mengembalikan respon error: ' . ($meta['jde_push_error'] ?? 'Terjadi kesalahan pada Orchestrator JDE'),
                'jde_push_status' => Order::JDE_STATUS_FAILED,
                'jde_push_status_label' => $order->jdePushStatusLabel(),
                'response' => $responseData,
            ];

        } catch (\Throwable $e) {
            $meta['jde_push_payload'] = $logPayload;
            $meta['jde_push_error'] = $e->getMessage();
            $meta['jde_push_failed_at'] = now()->toDateTimeString();
            $meta['jde_push_retry_count'] = $retryCount + 1;
            $meta['jde_last_attempt_at'] = now()->toDateTimeString();

            $order->jde_push_status = Order::JDE_STATUS_FAILED;
            $order->meta = $meta;
            $order->save();

            $this->logJde('error', 'Push Order Exception', [
                'order_number' => $order->order_number,
                'order_id' => $order->id,
                'exception' => $e->getMessage(),
                'payload' => $logPayload,
            ]);

            return [
                'success' => false,
                'message' => 'Gagal menghubungi service JDE: ' . $e->getMessage(),
                'jde_push_status' => Order::JDE_STATUS_FAILED,
                'jde_push_status_label' => $order->jdePushStatusLabel(),
            ];
        }
    }

    /**
     * Helper untuk mencatat log JDE harian dalam format banner terstruktur dan rapi.
     */
    public function logJde(string $level, string $action, array $data = []): void
    {
        $timestamp = now()->toDateTimeString();
        $divider = str_repeat('=', 80);
        $subDivider = str_repeat('-', 80);

        $banner = "\n{$divider}\n";
        $banner .= "  JDE INTEGRATION EVENT: [" . strtoupper($action) . "]\n";
        $banner .= "  Timestamp: {$timestamp}\n";
        $banner .= "{$subDivider}\n";

        foreach ($data as $key => $value) {
            if (is_array($value) || is_object($value)) {
                $formattedValue = json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
                $banner .= sprintf("  %-16s:\n%s\n", ucfirst($key), $formattedValue);
            } else {
                $banner .= sprintf("  %-16s: %s\n", ucfirst((string) $key), (string) $value);
            }
        }

        $banner .= "{$divider}\n";

        try {
            Log::channel('jde')->$level($banner);
        } catch (\Throwable $e) {
            Log::$level($banner);
        }
    }
}
