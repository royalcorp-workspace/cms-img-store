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
            // 1. finish_goods.product_variants
            if (Schema::hasTable('finish_goods.product_variants')) {
                Schema::table('finish_goods.product_variants', function (Blueprint $table) {
                    if (!Schema::hasColumn('finish_goods.product_variants', 'dpp_price')) {
                        $table->decimal('dpp_price', 15, 2)->default(0)->after('sell_price');
                    }
                    if (!Schema::hasColumn('finish_goods.product_variants', 'ppn_rate')) {
                        $table->decimal('ppn_rate', 5, 2)->default(11.00)->after('dpp_price');
                    }
                    if (!Schema::hasColumn('finish_goods.product_variants', 'ppn_amount')) {
                        $table->decimal('ppn_amount', 15, 2)->default(0)->after('ppn_rate');
                    }
                });

                // Backfill existing product variants: sell_price is tax-inclusive (sudah + PPN 11%)
                DB::statement("
                    UPDATE finish_goods.product_variants
                    SET 
                        ppn_rate = 11.00,
                        dpp_price = CASE 
                            WHEN COALESCE(sell_price, 0) > 0 THEN ROUND(sell_price / 1.11, 2)
                            ELSE 0 
                        END,
                        ppn_amount = CASE 
                            WHEN COALESCE(sell_price, 0) > 0 THEN ROUND(sell_price - (sell_price / 1.11), 2)
                            ELSE 0 
                        END
                    WHERE sell_price > 0 AND (dpp_price = 0 OR dpp_price IS NULL)
                ");
            }

            // 2. sales.order_items
            if (Schema::hasTable('sales.order_items')) {
                Schema::table('sales.order_items', function (Blueprint $table) {
                    if (!Schema::hasColumn('sales.order_items', 'dpp_price')) {
                        $table->decimal('dpp_price', 15, 2)->default(0)->after('unit_price');
                    }
                    if (!Schema::hasColumn('sales.order_items', 'ppn_rate')) {
                        $table->decimal('ppn_rate', 5, 2)->default(11.00)->after('dpp_price');
                    }
                    if (!Schema::hasColumn('sales.order_items', 'ppn_amount')) {
                        $table->decimal('ppn_amount', 15, 2)->default(0)->after('ppn_rate');
                    }
                });

                // Backfill existing order items: unit_price is tax-inclusive
                DB::statement("
                    UPDATE sales.order_items
                    SET 
                        ppn_rate = 11.00,
                        dpp_price = CASE 
                            WHEN COALESCE(unit_price, 0) > 0 THEN ROUND(unit_price / 1.11, 2)
                            ELSE 0 
                        END,
                        ppn_amount = CASE 
                            WHEN COALESCE(unit_price, 0) > 0 THEN ROUND(unit_price - (unit_price / 1.11), 2)
                            ELSE 0 
                        END
                    WHERE unit_price > 0 AND (dpp_price = 0 OR dpp_price IS NULL)
                ");
            }

            // 3. sales.orders
            if (Schema::hasTable('sales.orders')) {
                Schema::table('sales.orders', function (Blueprint $table) {
                    if (!Schema::hasColumn('sales.orders', 'dpp_amount')) {
                        $table->decimal('dpp_amount', 15, 2)->default(0)->after('subtotal');
                    }
                    if (!Schema::hasColumn('sales.orders', 'ppn_rate')) {
                        $table->decimal('ppn_rate', 5, 2)->default(11.00)->after('dpp_amount');
                    }
                    if (!Schema::hasColumn('sales.orders', 'ppn_amount')) {
                        $table->decimal('ppn_amount', 15, 2)->default(0)->after('ppn_rate');
                    }
                });

                // Backfill existing orders: subtotal is tax-inclusive
                DB::statement("
                    UPDATE sales.orders
                    SET 
                        ppn_rate = 11.00,
                        dpp_amount = CASE 
                            WHEN COALESCE(subtotal, 0) > 0 THEN ROUND(subtotal / 1.11, 2)
                            ELSE 0 
                        END,
                        ppn_amount = CASE 
                            WHEN COALESCE(subtotal, 0) > 0 THEN ROUND(subtotal - (subtotal / 1.11), 2)
                            ELSE 0 
                        END,
                        tax = CASE 
                            WHEN COALESCE(subtotal, 0) > 0 THEN ROUND(subtotal - (subtotal / 1.11), 2)
                            ELSE tax 
                        END
                    WHERE subtotal > 0 AND (dpp_amount = 0 OR dpp_amount IS NULL)
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
            if (Schema::hasTable('sales.orders')) {
                Schema::table('sales.orders', function (Blueprint $table) {
                    $table->dropColumn(['dpp_amount', 'ppn_rate', 'ppn_amount']);
                });
            }

            if (Schema::hasTable('sales.order_items')) {
                Schema::table('sales.order_items', function (Blueprint $table) {
                    $table->dropColumn(['dpp_price', 'ppn_rate', 'ppn_amount']);
                });
            }

            if (Schema::hasTable('finish_goods.product_variants')) {
                Schema::table('finish_goods.product_variants', function (Blueprint $table) {
                    $table->dropColumn(['dpp_price', 'ppn_rate', 'ppn_amount']);
                });
            }
        });
    }
};
