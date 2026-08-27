<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('products') && Schema::hasColumn('products', 'base_price')) {
            Schema::table('products', function (Blueprint $table) {
                $table->dropColumn('base_price');
            });
        }

        if (Schema::hasTable('product_variants')) {
            Schema::table('product_variants', function (Blueprint $table) {
                if (Schema::hasColumn('product_variants', 'price')) {
                    $table->renameColumn('price', 'sell_price');
                }
                if (!Schema::hasColumn('product_variants', 'base_price')) {
                    $table->decimal('base_price', 15, 2)->nullable()->after('sku');
                }
            });
        }

        if (Schema::hasTable('product_images') && !Schema::hasColumn('product_images', 'variant_id')) {
            Schema::table('product_images', function (Blueprint $table) {
                $table->uuid('variant_id')->nullable()->after('product_id')->comment('ID Varian referensi');
                $table->foreign('variant_id')->references('id')->on('product_variants')->onDelete('cascade');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('product_images') && Schema::hasColumn('product_images', 'variant_id')) {
            Schema::table('product_images', function (Blueprint $table) {
                $table->dropForeign(['variant_id']);
                $table->dropColumn('variant_id');
            });
        }

        if (Schema::hasTable('product_variants')) {
            Schema::table('product_variants', function (Blueprint $table) {
                if (Schema::hasColumn('product_variants', 'base_price')) {
                    $table->dropColumn('base_price');
                }
                if (Schema::hasColumn('product_variants', 'sell_price')) {
                    $table->renameColumn('sell_price', 'price');
                }
            });
        }

        if (Schema::hasTable('products') && !Schema::hasColumn('products', 'base_price')) {
            Schema::table('products', function (Blueprint $table) {
                $table->string('base_price', 255)->nullable();
            });
        }
    }
};
