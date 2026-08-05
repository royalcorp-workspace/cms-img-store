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
        $tables = [
            'voucher_products',
            'voucher_categories',
            'product_tag_relations',
            'price_product_setting_store',
            'price_product_setting_tier'
        ];

        foreach ($tables as $tableName) {
            if (Schema::hasTable($tableName)) {
                Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                    if (!Schema::hasColumn($tableName, 'creator')) {
                        $table->string('creator', 100)->nullable();
                    }
                    if (!Schema::hasColumn($tableName, 'editor')) {
                        $table->string('editor', 100)->nullable();
                    }
                    if (!Schema::hasColumn($tableName, 'deleted')) {
                        $table->boolean('deleted')->default(false);
                    }
                    if (!Schema::hasColumn($tableName, 'updated_at')) {
                        $table->timestampTz('updated_at', 0)->nullable();
                    }
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tables = [
            'voucher_products',
            'voucher_categories',
            'product_tag_relations',
            'price_product_setting_store',
            'price_product_setting_tier'
        ];

        foreach ($tables as $tableName) {
            if (Schema::hasTable($tableName)) {
                Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                    $colsToDrop = [];
                    if (Schema::hasColumn($tableName, 'creator')) {
                        $colsToDrop[] = 'creator';
                    }
                    if (Schema::hasColumn($tableName, 'editor')) {
                        $colsToDrop[] = 'editor';
                    }
                    if (Schema::hasColumn($tableName, 'deleted')) {
                        $colsToDrop[] = 'deleted';
                    }
                    if (Schema::hasColumn($tableName, 'updated_at')) {
                        // Check if it was added in this migration (e.g. not original schema)
                        if (in_array($tableName, ['voucher_products', 'voucher_categories', 'product_tag_relations'])) {
                            $colsToDrop[] = 'updated_at';
                        }
                    }
                    
                    if (!empty($colsToDrop)) {
                        $table->dropColumn($colsToDrop);
                    }
                });
            }
        }
    }
};
