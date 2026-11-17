<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::transaction(function () {
            // 1. finish_goods.products
            if (Schema::hasTable('finish_goods.products')) {
                Schema::table('finish_goods.products', function (Blueprint $table) {
                    if (!Schema::hasColumn('finish_goods.products', 'is_ppn_active')) {
                        $table->boolean('is_ppn_active')->default(true)->after('show_on_web');
                    }
                    if (!Schema::hasColumn('finish_goods.products', 'ppn_rate')) {
                        $table->decimal('ppn_rate', 5, 2)->default(11.00)->after('is_ppn_active');
                    }
                });

                DB::statement("
                    UPDATE finish_goods.products
                    SET is_ppn_active = true,
                        ppn_rate = 11.00
                    WHERE is_ppn_active IS NULL OR ppn_rate IS NULL
                ");
            }

            // 2. finish_goods.product_variants
            if (Schema::hasTable('finish_goods.product_variants')) {
                Schema::table('finish_goods.product_variants', function (Blueprint $table) {
                    if (!Schema::hasColumn('finish_goods.product_variants', 'is_ppn_active')) {
                        $table->boolean('is_ppn_active')->default(true)->after('sell_price');
                    }
                });

                DB::statement("
                    UPDATE finish_goods.product_variants
                    SET is_ppn_active = true
                    WHERE is_ppn_active IS NULL
                ");
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::transaction(function () {
            if (Schema::hasTable('finish_goods.products')) {
                Schema::table('finish_goods.products', function (Blueprint $table) {
                    if (Schema::hasColumn('finish_goods.products', 'ppn_rate')) {
                        $table->dropColumn('ppn_rate');
                    }
                    if (Schema::hasColumn('finish_goods.products', 'is_ppn_active')) {
                        $table->dropColumn('is_ppn_active');
                    }
                });
            }

            if (Schema::hasTable('finish_goods.product_variants')) {
                Schema::table('finish_goods.product_variants', function (Blueprint $table) {
                    if (Schema::hasColumn('finish_goods.product_variants', 'is_ppn_active')) {
                        $table->dropColumn('is_ppn_active');
                    }
                });
            }
        });
    }
};
