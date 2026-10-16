<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\Order\Order;
use App\Services\JdeService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class PushOrderToJdeJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Jumlah percobaan maksimal sebelum job dinyatakan gagal total.
     */
    public int $tries = 3;

    /**
     * Waktu tunggu (detik) antar percobaan ulang (1 menit, 5 menit, 15 menit).
     */
    public array $backoff = [60, 300, 900];

    /**
     * Timeout eksekusi per job (detik).
     */
    public int $timeout = 90;

    /**
     * ID Order yang akan dikirim ke JDE.
     */
    public string $orderId;

    /**
     * Siapa yang memicu job (misal: 'auto_trigger', 'cron_scheduler', 'manual_retry').
     */
    public string $triggeredBy;

    public function __construct(string $orderId, string $triggeredBy = 'auto_trigger')
    {
        $this->orderId = $orderId;
        $this->triggeredBy = $triggeredBy;
    }

    /**
     * Eksekusi job pengiriman ke JDE oleh Queue Worker.
     */
    public function handle(JdeService $jdeService): void
    {
        $order = Order::find($this->orderId);

        if (!$order || $order->deleted) {
            Log::channel('jde')->warning("PushOrderToJdeJob skipped: Order {$this->orderId} not found or deleted.");
            return;
        }

        // Idempotency guard: jika sudah sukses, jangan kirim ulang
        if ((int) $order->jde_push_status === Order::JDE_STATUS_SUCCESS) {
            Log::channel('jde')->info("PushOrderToJdeJob skipped: Order {$order->order_number} already pushed successfully.");
            return;
        }

        // Validasi kelayakan pengiriman (status sudah bayar / dimulai)
        if (!$jdeService->isOrderEligible($order)) {
            Log::channel('jde')->info("PushOrderToJdeJob skipped: Order {$order->order_number} does not meet eligibility conditions.");
            return;
        }

        $result = $jdeService->pushOrder($order, "queue_worker ({$this->triggeredBy})");

        if (!$result['success']) {
            $errorMessage = $result['message'] ?? 'Unknown JDE push error';

            if ($this->job && method_exists($this->job, 'getConnectionName') && $this->job->getConnectionName() !== 'sync') {
                if ($this->attempts() < $this->tries) {
                    // Lepaskan kembali ke antrean untuk dicoba ulang sesuai backoff
                    $delay = $this->backoff[$this->attempts() - 1] ?? 60;
                    $this->release($delay);
                    return;
                }
            }

            Log::channel('jde')->error("PushOrderToJdeJob failed for Order {$order->order_number}: {$errorMessage}");
        }
    }

    /**
     * Menangani kegagalan fatal pada job (misal: unhandled exception atau timeout).
     */
    public function failed(\Throwable $exception): void
    {
        Log::channel('jde')->error("PushOrderToJdeJob FATAL FAILURE for Order {$this->orderId}: " . $exception->getMessage());

        try {
            $order = Order::find($this->orderId);
            if ($order && (int) $order->jde_push_status !== Order::JDE_STATUS_SUCCESS) {
                $meta = $order->meta ?? [];
                $meta['jde_push_error'] = 'Queue Job Failed: ' . $exception->getMessage();
                $meta['jde_push_failed_at'] = now()->toDateTimeString();
                $order->jde_push_status = Order::JDE_STATUS_FAILED;
                $order->meta = $meta;
                $order->saveQuietly();
            }
        } catch (\Throwable $e) {
            Log::channel('jde')->error("Failed updating order status on job failure: " . $e->getMessage());
        }
    }
}
