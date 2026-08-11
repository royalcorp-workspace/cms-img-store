<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('product_variants') && !Schema::hasColumn('product_variants', 'reseller_price')) {
            Schema::table('product_variants', function (Blueprint $table) {
                $table->decimal('reseller_price', 16, 2)->nullable()->after('price');
            });
        }

        if (Schema::hasTable('products') && !Schema::hasColumn('products', 'is_bundle')) {
            Schema::table('products', function (Blueprint $table) {
                $table->boolean('is_bundle')->default(false)->after('status');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('product_variants') && Schema::hasColumn('product_variants', 'reseller_price')) {
            Schema::table('product_variants', function (Blueprint $table) {
                $table->dropColumn('reseller_price');
            });
        }

        if (Schema::hasTable('products') && Schema::hasColumn('products', 'is_bundle')) {
            Schema::table('products', function (Blueprint $table) {
                $table->dropColumn('is_bundle');
            });
        }
    }
};
