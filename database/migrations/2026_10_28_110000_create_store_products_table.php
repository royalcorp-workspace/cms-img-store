<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $hasFinishGoods = DB::selectOne("SELECT 1 FROM information_schema.schemata WHERE schema_name = 'finish_goods'") !== null;
        $tableStoreProducts = $hasFinishGoods ? 'finish_goods.store_products' : 'store_products';

        if (!Schema::hasTable($tableStoreProducts)) {
            Schema::create($tableStoreProducts, function (Blueprint $table) {
                $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
                $table->uuid('temporary_item_id')->nullable();

                // Relasi Toko & Cabang
                $table->uuid('store_id');
                $table->bigInteger('branch_id')->nullable();
                $table->string('business_unit', 50)->nullable();

                // Identitas Artikel JDE
                $table->bigInteger('short_item_no')->default(0);
                $table->string('sku', 100);
                $table->string('item_name', 255);
                $table->string('item_size', 100)->nullable();
                $table->string('brand_name', 100)->nullable();
                $table->string('category_name', 100)->nullable();
                $table->jsonb('segments')->nullable();

                // Harga Toko Retail
                $table->decimal('base_price', 15, 2)->default(0.00); // Modal Grosir dari JDE
                $table->decimal('suggested_retail_price', 15, 2)->nullable();
                $table->decimal('custom_retail_price', 15, 2)->nullable(); // Harga Banderol Toko ke Showroom

                // Status Etalase Toko
                $table->boolean('is_active_for_pos')->default(true);
                $table->boolean('is_floor_display')->default(false); // Dipajang di lantai etalase toko
                $table->boolean('is_dedicated')->default(false);     // Produk pesanan khusus toko
                $table->integer('display_order')->default(0);

                $table->timestampsTz();

                $table->unique(['store_id', 'sku'], 'store_products_store_sku_unique');
                $table->index(['store_id', 'is_active_for_pos'], 'idx_store_prod_lookup');
                $table->index(['store_id', 'is_floor_display'], 'idx_store_prod_floor');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $hasFinishGoods = DB::selectOne("SELECT 1 FROM information_schema.schemata WHERE schema_name = 'finish_goods'") !== null;
        $tableStoreProducts = $hasFinishGoods ? 'finish_goods.store_products' : 'store_products';

        Schema::dropIfExists($tableStoreProducts);
    }
};
