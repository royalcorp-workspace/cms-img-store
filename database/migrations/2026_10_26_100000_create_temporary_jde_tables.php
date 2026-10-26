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
        DB::transaction(function () {
            // 1. Tabel finish_goods.temporary_jde_item_masters
            if (!Schema::hasTable('finish_goods.temporary_jde_item_masters')) {
                Schema::create('finish_goods.temporary_jde_item_masters', function (Blueprint $table) {
                    $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));

                    // Field Asli Oracle JDE (POS_V59POSA1 / F4101 / F4102)
                    $table->bigInteger('short_item_no');
                    $table->string('second_item_number', 100);
                    $table->string('description', 255);
                    $table->string('description_line_2', 255)->nullable();
                    $table->string('sales_code1', 50)->nullable();
                    $table->string('sales_code1_desc', 100)->nullable();
                    $table->string('process2', 50)->nullable();
                    $table->string('uom', 20)->default('PC');
                    $table->string('stocking_type', 20)->default('P');
                    $table->string('line_type', 20)->default('S');
                    $table->string('user_id', 50)->nullable();
                    $table->string('program_id', 50)->nullable();
                    $table->date('date_update')->nullable();
                    $table->string('template', 100)->nullable();

                    // Segmentasi Artikel (Struktur JSON terpadu seperti di finish_goods.products)
                    $table->jsonb('segments')->nullable();
                    $table->string('post_retail', 50)->nullable();
                    $table->string('img_reference', 255)->nullable();

                    // Field Kontrol Staging CMS
                    $table->string('tax_type', 20)->default('PPN');
                    $table->string('sync_status', 30)->default('PENDING'); // PENDING, PROCESSED, SKIPPED, REJECTED
                    $table->uuid('processed_product_id')->nullable();
                    $table->uuid('processed_by')->nullable();
                    $table->timestampTz('processed_at')->nullable();
                    $table->jsonb('raw_payload')->nullable();
                    $table->text('error_message')->nullable();

                    $table->timestampsTz();

                    // Indexes
                    $table->index('short_item_no', 'idx_temp_jde_item_short_no');
                    $table->index('second_item_number', 'idx_temp_jde_item_2nd_no');
                    $table->index('sync_status', 'idx_temp_jde_item_status');
                });

                // Index GIN untuk kolom segments JSONB
                DB::statement('CREATE INDEX IF NOT EXISTS idx_temp_jde_item_segments ON finish_goods.temporary_jde_item_masters USING GIN (segments);');
            }

            // 2. Tabel finish_goods.temporary_jde_base_prices
            if (!Schema::hasTable('finish_goods.temporary_jde_base_prices')) {
                Schema::create('finish_goods.temporary_jde_base_prices', function (Blueprint $table) {
                    $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));

                    // Field Asli Oracle JDE (POS_F4106)
                    $table->bigInteger('short_item_no');
                    $table->string('second_item_number', 100);
                    $table->string('business_unit', 50);
                    $table->string('business_unit_desc', 255)->nullable();
                    $table->string('currency_code', 10)->default('IDR');
                    $table->string('uom', 20)->default('PC');
                    $table->decimal('base_price', 15, 2)->default(0);
                    $table->date('effective_date');
                    $table->date('expired_date')->nullable();
                    $table->string('user_id', 50)->nullable();
                    $table->string('program_id', 50)->default('P4106');
                    $table->date('date_update')->nullable();
                    $table->integer('time_of_day')->nullable();

                    // Field Kontrol Staging CMS
                    $table->boolean('tax_included')->default(false);
                    $table->string('sync_status', 30)->default('PENDING');
                    $table->uuid('mapped_price_id')->nullable();
                    $table->uuid('mapped_warehouse_id')->nullable();
                    $table->uuid('processed_by')->nullable();
                    $table->timestampTz('processed_at')->nullable();
                    $table->jsonb('raw_payload')->nullable();
                    $table->text('error_message')->nullable();

                    $table->timestampsTz();

                    // Indexes
                    $table->index(['second_item_number', 'business_unit'], 'idx_temp_jde_price_lookup');
                    $table->index('sync_status', 'idx_temp_jde_price_status');
                });
            }

            // 3. Tabel store.temporary_jde_branches
            if (!Schema::hasTable('store.temporary_jde_branches')) {
                Schema::create('store.temporary_jde_branches', function (Blueprint $table) {
                    $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));

                    // Field Asli Oracle JDE (POS_V59POSB1 / F0006)
                    $table->string('business_unit', 50)->unique('idx_temp_jde_branch_bu');
                    $table->string('company', 50)->default('18000');
                    $table->string('cabang_code', 50);
                    $table->string('cabang_desc', 255);
                    $table->bigInteger('short_item_no')->nullable();
                    $table->string('process2', 50)->nullable();
                    $table->string('stocking_type', 20)->default('P');
                    $table->string('user_id', 50)->nullable();
                    $table->string('program_id', 50)->default('R5541012A');
                    $table->date('date_update')->nullable();
                    $table->integer('time_of_day')->nullable();

                    // Field Kontrol Staging CMS
                    $table->string('sync_status', 30)->default('PENDING');
                    $table->uuid('mapped_branch_id')->nullable();
                    $table->uuid('mapped_warehouse_id')->nullable();
                    $table->jsonb('raw_payload')->nullable();

                    $table->timestampsTz();
                });
            }

            // 4. Tabel store.temporary_jde_customers
            if (!Schema::hasTable('store.temporary_jde_customers')) {
                Schema::create('store.temporary_jde_customers', function (Blueprint $table) {
                    $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));

                    // Field Asli Oracle JDE (POS_V55AR19 / F0101 / F03012)
                    $table->bigInteger('address_number')->unique('idx_temp_jde_cust_an');
                    $table->string('business_unit', 50);
                    $table->string('company', 50)->default('00000');
                    $table->decimal('credit_limit', 15, 2)->default(0);
                    $table->string('payment_term', 20)->default('045');
                    $table->string('hold_code', 10)->default(' ');
                    $table->string('user_id', 50)->nullable();
                    $table->string('program_id', 50)->default('P03013');
                    $table->date('date_update')->nullable();
                    $table->integer('time_update')->nullable();
                    $table->string('alpha_name', 255);
                    $table->string('channel_code', 50)->default('15');
                    $table->string('channel_desc', 100)->default('RETAIL');
                    $table->string('cabang_code', 50)->nullable();
                    $table->string('cabang_desc', 255)->nullable();

                    // Field Kontrol Staging CMS
                    $table->string('sync_status', 30)->default('PENDING');
                    $table->uuid('mapped_store_id')->nullable();
                    $table->jsonb('raw_payload')->nullable();
                    $table->text('notes')->nullable();

                    $table->timestampsTz();

                    // Indexes
                    $table->index('sync_status', 'idx_temp_jde_cust_status');
                    $table->index('business_unit', 'idx_temp_jde_cust_bu');
                });
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::transaction(function () {
            Schema::dropIfExists('finish_goods.temporary_jde_item_masters');
            Schema::dropIfExists('finish_goods.temporary_jde_base_prices');
            Schema::dropIfExists('store.temporary_jde_branches');
            Schema::dropIfExists('store.temporary_jde_customers');
        });
    }
};
