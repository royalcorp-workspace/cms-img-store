<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('price_product_setting_items')) {
            Schema::table('price_product_setting_items', function (Blueprint $table) {
                // Make product_id nullable
                $table->uuid('product_id')->nullable()->change();
                
                // Add bundling_id foreign key column
                if (!Schema::hasColumn('price_product_setting_items', 'bundling_id')) {
                    $table->foreignUuid('bundling_id')
                        ->nullable()
                        ->after('variant_id')
                        ->constrained('products_bundling')
                        ->onDelete('cascade');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('price_product_setting_items')) {
            Schema::table('price_product_setting_items', function (Blueprint $table) {
                $table->uuid('product_id')->nullable(false)->change();
                if (Schema::hasColumn('price_product_setting_items', 'bundling_id')) {
                    $table->dropForeign(['bundling_id']);
                    $table->dropColumn('bundling_id');
                }
            });
        }
    }
};
