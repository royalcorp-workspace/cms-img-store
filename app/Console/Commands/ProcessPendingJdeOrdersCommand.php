<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Jobs\PushOrderToJdeJob;
use App\Models\Order\Order;
use App\Services\JdeService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ProcessPendingJdeOrdersCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'jde:process-pending 
                            {--limit=50 : Batas maksimal order yang diproses per batch} 
                            {--sync : Eksekusi langsung secara sinkron tanpa antrean queue} 
                            {--force : Abaikan jeda interval retry antar percobaan}';

    /**
     * The console command description.
     */
    protected $description = 'Pengecekan berkala terhadap order yang masih Pending atau Gagal ke JDE untuk diproses ulang oleh Queue Worker';

    /**
     * Execute the console command.
     */
    public function handle(JdeService $jdeService): int
    {
        $limit = (int) $this->option('limit') ?: 50;
        $isSync = (bool) $this->option('sync');
        $isForce = (bool) $this->option('force');

        $this->info("Memulai pengecekan berkala order untuk push ke JDE (Limit: {$limit}, Mode: " . ($isSync ? 'SYNC' : 'QUEUE') . ")...");

        // Ambil order yang status JDE-nya belum Success (Pending = 0 atau Failed = 3),
        // sudah berstatus Diproses (Status >= 3) dan pembayarannya lunas (Paid).
        // Menggunakan PostgreSQL Partial Index (idx_orders_jde_push_queue) & rolling window 30 hari
        // agar eksekusi query tetap stabil, instan (< 1ms), dan tidak membebani database.
        $orders = Order::query()
            ->where('deleted', false)
            ->where('jde_push_status', '!=', Order::JDE_STATUS_SUCCESS)
            ->where('status', '>=', Order::STATUS_PROCESSING)
            ->where(function ($q) {
                $q->where('payment_status', Order::PAYMENT_PAID)
                  ->orWhere('payment_status', 2);
            })
            ->whereNotIn('status', [Order::STATUS_CANCELLED, Order::STATUS_RETURNED])
            ->where('created_at', '>=', now()->subDays(30))
            ->orderBy('created_at', 'asc')
            ->take($limit)
            ->get();

        if ($orders->isEmpty()) {
            $this->info('Tidak ada order yang pending atau membutuhkan retry pengiriman ke JDE.');
            return self::SUCCESS;
        }

        $this->info("Ditemukan {$orders->count()} order yang berpotensi untuk dikirim ke JDE.");

        $maxRetries = (int) config('services.jde.max_retries', 5);
        $retryDelayMinutes = (int) config('services.jde.retry_delay_minutes', 5);

        $dispatched = 0;
        $skipped = 0;
        $successCount = 0;
        $failCount = 0;

        foreach ($orders as $order) {
            // Validasi apakah order memiliki item
            if (!$jdeService->isOrderEligible($order)) {
                $skipped++;
                continue;
            }

            $meta = $order->meta ?? [];
            $retryCount = (int) ($meta['jde_push_retry_count'] ?? 0);
            $lastAttempt = !empty($meta['jde_last_attempt_at']) 
                ? Carbon::parse($meta['jde_last_attempt_at']) 
                : (!empty($meta['jde_push_failed_at']) ? Carbon::parse($meta['jde_push_failed_at']) : null);

            // Jika status Gagal (Failed) dan tidak di-force:
            if ((int) $order->jde_push_status === Order::JDE_STATUS_FAILED && !$isForce) {
                // Batasi jumlah maksimal retry otomatis
                if ($retryCount >= $maxRetries) {
                    $this->line(" - Order #{$order->order_number}: Dilewati (mencapai batas {$retryCount}/{$maxRetries} percobaan gagal). Butuh retry manual.");
                    $skipped++;
                    continue;
                }

                // Cek interval jeda waktu antar retry (backoff)
                if ($lastAttempt && $lastAttempt->diffInMinutes(now()) < $retryDelayMinutes) {
                    $remaining = (int) ceil(max(1, $retryDelayMinutes - $lastAttempt->diffInMinutes(now())));
                    $this->line(" - Order #{$order->order_number}: Menunggu jeda retry (tersisa {$remaining} menit).");
                    $skipped++;
                    continue;
                }
            }

            if ($isSync || config('queue.default') === 'sync') {
                $this->line(" - Order #{$order->order_number}: Memproses push secara sinkron...");
                $res = $jdeService->pushOrder($order, 'cron_scheduler_sync');
                if ($res['success']) {
                    $successCount++;
                    $this->info("   [SUCCESS] {$res['message']}");
                } else {
                    $failCount++;
                    $this->error("   [FAILED] {$res['message']}");
                }
            } else {
                // Dispatch ke Queue Worker
                PushOrderToJdeJob::dispatch($order->id, 'cron_scheduler');
                $dispatched++;
                $this->line(" - Order #{$order->order_number}: Berhasil dimasukkan ke antrean Job Queue (Percobaan ke-" . ($retryCount + 1) . ").");
            }
        }

        if ($isSync || config('queue.default') === 'sync') {
            $this->info("Selesai proses sinkron: Sukses: {$successCount}, Gagal: {$failCount}, Dilewati: {$skipped}.");
        } else {
            $this->info("Selesai dispatch: {$dispatched} order dimasukkan ke antrean queue, {$skipped} dilewati.");
        }

        Log::channel('jde')->info('ProcessPendingJdeOrdersCommand executed', [
            'total_candidates' => $orders->count(),
            'dispatched' => $dispatched,
            'skipped' => $skipped,
            'sync' => $isSync,
            'success_count' => $successCount,
            'fail_count' => $failCount,
        ]);

        return self::SUCCESS;
    }
}
