<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Add fields to orders table
        Schema::table('orders', function (Blueprint $table) {
            if (!Schema::hasColumn('orders', 'order_date')) {
                $table->date('order_date')->nullable()->after('order_number');
            }
            if (!Schema::hasColumn('orders', 'jde_push_status')) {
                $table->smallInteger('jde_push_status')->default(0)->nullable()->after('status');
            }
            if (!Schema::hasColumn('orders', 'jde_push_date')) {
                $table->date('jde_push_date')->nullable()->after('jde_push_status');
            }
        });

        // Backfill existing orders with order_date & jde_push_status
        DB::statement("UPDATE orders SET order_date = created_at::date WHERE order_date IS NULL AND created_at IS NOT NULL");
        DB::statement("UPDATE orders SET order_date = CURRENT_DATE WHERE order_date IS NULL");
        DB::statement("UPDATE orders SET jde_push_status = 0 WHERE jde_push_status IS NULL");

        // 2. Create order_logs table
        if (!Schema::hasTable('order_logs')) {
            Schema::create('order_logs', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->uuid('order_id');
                $table->string('action', 100)->index();
                $table->string('status_from', 50)->nullable();
                $table->string('status_to', 50)->nullable();
                $table->text('notes')->nullable();
                $table->string('creator', 255)->nullable();
                $table->string('editor', 255)->nullable();
                $table->timestamps();

                $table->foreign('order_id')->references('id')->on('orders')->onDelete('cascade');
                $table->index('created_at');
            });

            // Backfill initial order_logs for existing orders
            $existingOrders = DB::table('orders')->select('id', 'status', 'creator', 'editor', 'created_at', 'updated_at')->get();
            $now = now();
            foreach ($existingOrders as $order) {
                DB::table('order_logs')->insert([
                    'id' => (string) Str::uuid(),
                    'order_id' => $order->id,
                    'action' => 'created',
                    'status_from' => null,
                    'status_to' => (string) $order->status,
                    'notes' => 'Initial order record',
                    'creator' => $order->creator ?: 'system',
                    'editor' => $order->editor ?: $order->creator ?: 'system',
                    'created_at' => $order->created_at ?: $now,
                    'updated_at' => $order->updated_at ?: $now,
                ]);
            }
        }

        // 3. Clean up any existing phone numbers starting with '+'
        DB::statement("UPDATE customers SET phone = LTRIM(phone, '+') WHERE phone LIKE '+%'");
        DB::statement("UPDATE addresses SET phone = LTRIM(phone, '+') WHERE phone LIKE '+%'");
        DB::statement("UPDATE users SET phone = LTRIM(phone, '+') WHERE phone LIKE '+%'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_logs');

        Schema::table('orders', function (Blueprint $table) {
            if (Schema::hasColumn('orders', 'jde_push_date')) {
                $table->dropColumn('jde_push_date');
            }
            if (Schema::hasColumn('orders', 'jde_push_status')) {
                $table->dropColumn('jde_push_status');
            }
            if (Schema::hasColumn('orders', 'order_date')) {
                $table->dropColumn('order_date');
            }
        });
    }
};
