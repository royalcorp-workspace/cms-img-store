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
        Schema::table('products', function (Blueprint $table) {
            if (Schema::hasColumn('products', 'shipping_scheme')) {
                $table->dropColumn('shipping_scheme');
            }
            if (Schema::hasColumn('products', 'shipping_cost')) {
                $table->dropColumn('shipping_cost');
            }
        });

        Schema::table('product_variants', function (Blueprint $table) {
            if (Schema::hasColumn('product_variants', 'shipping_cost')) {
                $table->dropColumn('shipping_cost');
            }
        });

        Schema::table('product_category', function (Blueprint $table) {
            if (Schema::hasColumn('product_category', 'shipping_scheme')) {
                $table->dropColumn('shipping_scheme');
            }
            if (Schema::hasColumn('product_category', 'shipping_cost')) {
                $table->dropColumn('shipping_cost');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('shipping_scheme', 50)->default('dimension')->after('courier_type');
            $table->decimal('shipping_cost', 15, 2)->default(0)->after('shipping_scheme');
        });

        Schema::table('product_variants', function (Blueprint $table) {
            $table->decimal('shipping_cost', 15, 2)->default(0)->after('base_price');
        });

        Schema::table('product_category', function (Blueprint $table) {
            $table->string('shipping_scheme', 50)->default('dimension')->after('courier_type');
            $table->decimal('shipping_cost', 15, 2)->default(0)->after('shipping_scheme');
        });
    }
};
