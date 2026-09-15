<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('product_category', function (Blueprint $table) {
            if (!Schema::hasColumn('product_category', 'courier_setting_type')) {
                $table->string('courier_setting_type', 50)->default('detail')->after('has_warranty');
            }
            if (!Schema::hasColumn('product_category', 'courier_type')) {
                $table->string('courier_type', 50)->default('keduanya')->after('courier_setting_type');
            }
            if (!Schema::hasColumn('product_category', 'shipping_scheme')) {
                $table->string('shipping_scheme', 50)->default('dimension')->after('courier_type');
            }
            if (!Schema::hasColumn('product_category', 'shipping_cost')) {
                $table->decimal('shipping_cost', 15, 2)->default(0)->after('shipping_scheme');
            }
        });

        Schema::table('products', function (Blueprint $table) {
            if (!Schema::hasColumn('products', 'shipping_scheme')) {
                $table->string('shipping_scheme', 50)->default('dimension')->after('courier_type');
            }
            if (!Schema::hasColumn('products', 'shipping_cost')) {
                $table->decimal('shipping_cost', 15, 2)->default(0)->after('shipping_scheme');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            if (Schema::hasColumn('products', 'shipping_cost')) {
                $table->dropColumn('shipping_cost');
            }
            if (Schema::hasColumn('products', 'shipping_scheme')) {
                $table->dropColumn('shipping_scheme');
            }
        });

        Schema::table('product_category', function (Blueprint $table) {
            if (Schema::hasColumn('product_category', 'shipping_cost')) {
                $table->dropColumn('shipping_cost');
            }
            if (Schema::hasColumn('product_category', 'shipping_scheme')) {
                $table->dropColumn('shipping_scheme');
            }
            if (Schema::hasColumn('product_category', 'courier_type')) {
                $table->dropColumn('courier_type');
            }
            if (Schema::hasColumn('product_category', 'courier_setting_type')) {
                $table->dropColumn('courier_setting_type');
            }
        });
    }
};
