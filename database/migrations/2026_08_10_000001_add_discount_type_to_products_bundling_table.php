<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('products_bundling') && !Schema::hasColumn('products_bundling', 'discount_type')) {
            Schema::table('products_bundling', function (Blueprint $table) {
                $table->string('discount_type')->default('percentage')->after('description');
                // We'll reuse the existing `price` column as the discount_value to avoid dropping columns in production
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('products_bundling') && Schema::hasColumn('products_bundling', 'discount_type')) {
            Schema::table('products_bundling', function (Blueprint $table) {
                $table->dropColumn('discount_type');
            });
        }
    }
};
