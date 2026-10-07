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
        // 1. Buat tabel referensi labeling
        if (!Schema::hasTable('reff')) {
            Schema::create('reff', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->string('name', 100)->index(); // Contoh: StatusOrder, PaymentStatus, dll
                $table->smallInteger('value')->index(); // Nilai angka smallint
                $table->string('show', 150);          // Label tampilan
                $table->string('description', 255)->nullable();
                $table->integer('sort_order')->default(0);
                $table->boolean('is_active')->default(true);
                $table->timestamps();

                $table->unique(['name', 'value']);
            });
        }

        // 2. Ubah kolom status di product_variants dari varchar ke smallint jika belum
        if (Schema::hasColumn('product_variants', 'status')) {
            // Bersihkan data string non-angka menjadi default 1 atau 0
            DB::statement("UPDATE product_variants SET status = '0' WHERE status IS NULL OR status = ''");
            DB::statement("ALTER TABLE product_variants ALTER COLUMN status TYPE smallint USING (status::smallint)");
            DB::statement("ALTER TABLE product_variants ALTER COLUMN status SET DEFAULT 1");
        }

        // 3. Ubah kolom status, payment_status, & jde_push_status di orders menjadi smallint
        if (Schema::hasColumn('orders', 'status')) {
            DB::statement("ALTER TABLE orders ALTER COLUMN status TYPE smallint USING (status::smallint)");
        }
        if (Schema::hasColumn('orders', 'payment_status')) {
            DB::statement("ALTER TABLE orders ALTER COLUMN payment_status TYPE smallint USING (payment_status::smallint)");
        }
        if (Schema::hasColumn('orders', 'jde_push_status')) {
            DB::statement("UPDATE orders SET jde_push_status = '0' WHERE jde_push_status IS NULL OR jde_push_status = 'pending' OR jde_push_status = ''");
            DB::statement("UPDATE orders SET jde_push_status = '1' WHERE jde_push_status = 'processing'");
            DB::statement("UPDATE orders SET jde_push_status = '2' WHERE jde_push_status IN ('success', 'synced', 'pushed')");
            DB::statement("UPDATE orders SET jde_push_status = '3' WHERE jde_push_status IN ('failed', 'error')");
            DB::statement("ALTER TABLE orders ALTER COLUMN jde_push_status DROP DEFAULT");
            DB::statement("ALTER TABLE orders ALTER COLUMN jde_push_status TYPE smallint USING (jde_push_status::smallint)");
            DB::statement("ALTER TABLE orders ALTER COLUMN jde_push_status SET DEFAULT 0");
        }

        // 4. Ubah status di payment_methods menjadi smallint
        if (Schema::hasColumn('payment_methods', 'status')) {
            DB::statement("ALTER TABLE payment_methods ALTER COLUMN status TYPE smallint USING (status::smallint)");
        }

        // 5. Seed data label referensi awal
        $now = now();
        $refs = [
            // StatusOrder
            ['name' => 'StatusOrder', 'value' => 0, 'show' => 'Draft', 'sort_order' => 1],
            ['name' => 'StatusOrder', 'value' => 1, 'show' => 'Ordered', 'sort_order' => 2],
            ['name' => 'StatusOrder', 'value' => 2, 'show' => 'Confirmed', 'sort_order' => 3],
            ['name' => 'StatusOrder', 'value' => 3, 'show' => 'Processing', 'sort_order' => 4],
            ['name' => 'StatusOrder', 'value' => 4, 'show' => 'Shipped', 'sort_order' => 5],
            ['name' => 'StatusOrder', 'value' => 5, 'show' => 'Delivered', 'sort_order' => 6],
            ['name' => 'StatusOrder', 'value' => 6, 'show' => 'Cancelled', 'sort_order' => 7],
            ['name' => 'StatusOrder', 'value' => 7, 'show' => 'Returned', 'sort_order' => 8],

            // PaymentStatus
            ['name' => 'PaymentStatus', 'value' => 0, 'show' => 'Unpaid', 'sort_order' => 1],
            ['name' => 'PaymentStatus', 'value' => 1, 'show' => 'Paid', 'sort_order' => 2],
            ['name' => 'PaymentStatus', 'value' => 2, 'show' => 'Failed', 'sort_order' => 3],
            ['name' => 'PaymentStatus', 'value' => 3, 'show' => 'Refunded', 'sort_order' => 4],
            ['name' => 'PaymentStatus', 'value' => 4, 'show' => 'Partial', 'sort_order' => 5],

            // CourierType
            ['name' => 'CourierType', 'value' => 1, 'show' => 'Kurir Toko', 'sort_order' => 1],
            ['name' => 'CourierType', 'value' => 2, 'show' => 'Kurir Ekspedisi', 'sort_order' => 2],
            ['name' => 'CourierType', 'value' => 3, 'show' => 'Keduanya', 'sort_order' => 3],

            // ProductStatus
            ['name' => 'ProductStatus', 'value' => 0, 'show' => 'Nonaktif', 'sort_order' => 1],
            ['name' => 'ProductStatus', 'value' => 1, 'show' => 'Aktif', 'sort_order' => 2],

            // GeneralStatus
            ['name' => 'GeneralStatus', 'value' => 0, 'show' => 'Nonaktif', 'sort_order' => 1],
            ['name' => 'GeneralStatus', 'value' => 1, 'show' => 'Aktif', 'sort_order' => 2],

            // CustomerType
            ['name' => 'CustomerType', 'value' => 1, 'show' => 'Pelanggan Reguler', 'sort_order' => 1],
            ['name' => 'CustomerType', 'value' => 2, 'show' => 'Reseller', 'sort_order' => 2],

            // DeliveryStatus
            ['name' => 'DeliveryStatus', 'value' => 1, 'show' => 'Pending', 'sort_order' => 1],
            ['name' => 'DeliveryStatus', 'value' => 2, 'show' => 'Dalam Pengiriman', 'sort_order' => 2],
            ['name' => 'DeliveryStatus', 'value' => 3, 'show' => 'Terkirim', 'sort_order' => 3],
            ['name' => 'DeliveryStatus', 'value' => 4, 'show' => 'Gagal Kirim', 'sort_order' => 4],
            ['name' => 'DeliveryStatus', 'value' => 5, 'show' => 'Retur', 'sort_order' => 5],

            // JdePushStatus
            ['name' => 'JdePushStatus', 'value' => 0, 'show' => 'Pending', 'sort_order' => 1],
            ['name' => 'JdePushStatus', 'value' => 1, 'show' => 'Processing', 'sort_order' => 2],
            ['name' => 'JdePushStatus', 'value' => 2, 'show' => 'Success', 'sort_order' => 3],
            ['name' => 'JdePushStatus', 'value' => 3, 'show' => 'Failed', 'sort_order' => 4],
        ];

        foreach ($refs as $ref) {
            DB::table('reff')->updateOrInsert(
                ['name' => $ref['name'], 'value' => $ref['value']],
                [
                    'id' => (string) Str::uuid(),
                    'show' => $ref['show'],
                    'sort_order' => $ref['sort_order'],
                    'is_active' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reff');
    }
};
