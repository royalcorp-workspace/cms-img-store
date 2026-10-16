<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('orders')) {
            return;
        }

        // 1. Partial Index khusus untuk query pengecekan antrean pengiriman JDE.
        // Index ini hanya menyimpan baris yang jde_push_status != 2 (Success) dan deleted = false.
        // Dengan Partial Index PostgreSQL:
        // Meskipun tabel orders memiliki jutaan baris, ukuran index ini hanya beberapa kilobyte (hanya baris yang pending/failed),
        // sehingga query cron SELECT berjalan instan (O(1) / < 1ms) tanpa membebani I/O CPU database sama sekali.
        DB::statement("
            CREATE INDEX IF NOT EXISTS idx_orders_jde_push_queue 
            ON orders (status, payment_status, created_at) 
            WHERE jde_push_status != 2 AND deleted = false;
        ");

        // 2. Index standar untuk kolom status & payment_status jika belum ada
        DB::statement("
            CREATE INDEX IF NOT EXISTS idx_orders_status_payment_status 
            ON orders (status, payment_status);
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("DROP INDEX IF EXISTS idx_orders_jde_push_queue;");
        DB::statement("DROP INDEX IF EXISTS idx_orders_status_payment_status;");
    }
};
