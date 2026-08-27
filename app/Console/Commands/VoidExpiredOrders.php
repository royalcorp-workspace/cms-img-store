<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Order\Order;
use App\Models\VoidOrder;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class VoidExpiredOrders extends Command
{
    protected $signature = 'orders:void-expired';
    protected $description = 'Pindahkan pesanan yang telat bayar (melewati 24 jam) ke tabel void_orders';

    public function handle()
    {
        // 24 hours ago
        $expiredTime = Carbon::now()->subHours(24);

        $expiredOrders = Order::where('status', Order::STATUS_PENDING_APPROVAL)
            ->where('payment_status', 1) // Belum bayar
            ->where('created_at', '<', $expiredTime)
            ->with('items')
            ->get();

        if ($expiredOrders->isEmpty()) {
            $this->info('Tidak ada order yang expired.');
            return;
        }

        DB::beginTransaction();
        try {
            foreach ($expiredOrders as $order) {
                // Pindahkan ke void_orders
                VoidOrder::create([
                    'id' => $order->id, // keep the same UUID
                    'order_number' => $order->order_number,
                    'customer_id' => $order->customer_id,
                    'order_data' => $order->toArray(),
                    'order_items_data' => $order->items->toArray(),
                    'void_reason' => 'Batas waktu pembayaran telah habis (Auto Void)',
                    'voided_at' => now(),
                ]);

                // Hapus data order dan items dari tabel aslinya
                foreach ($order->items as $item) {
                    $item->delete();
                }
                $order->delete();
                
                $this->info("Order {$order->order_number} berhasil divoid.");
            }
            DB::commit();
            $this->info('Selesai memproses void orders.');
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error('Gagal memproses void orders: ' . $e->getMessage());
        }
    }
}
