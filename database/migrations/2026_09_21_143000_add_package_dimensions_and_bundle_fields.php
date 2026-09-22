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
        Schema::table('product_variants', function (Blueprint $table) {
            if (!Schema::hasColumn('product_variants', 'package_length')) {
                $table->decimal('package_length', 10, 2)->nullable()->after('height');
            }
            if (!Schema::hasColumn('product_variants', 'package_width')) {
                $table->decimal('package_width', 10, 2)->nullable()->after('package_length');
            }
            if (!Schema::hasColumn('product_variants', 'package_height')) {
                $table->decimal('package_height', 10, 2)->nullable()->after('package_width');
            }
            if (!Schema::hasColumn('product_variants', 'package_weight')) {
                $table->decimal('package_weight', 10, 2)->nullable()->after('package_height');
            }
        });

        Schema::table('products_bundling_items', function (Blueprint $table) {
            if (!Schema::hasColumn('products_bundling_items', 'is_suggest')) {
                $table->boolean('is_suggest')->default(false)->after('quantity');
            }
            if (!Schema::hasColumn('products_bundling_items', 'bundle_price')) {
                $table->decimal('bundle_price', 15, 2)->nullable()->after('is_suggest');
            }
            if (!Schema::hasColumn('products_bundling_items', 'discount_percent')) {
                $table->decimal('discount_percent', 5, 2)->nullable()->after('bundle_price');
            }
            if (!Schema::hasColumn('products_bundling_items', 'discount_nominal')) {
                $table->decimal('discount_nominal', 15, 2)->nullable()->after('discount_percent');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('product_variants', function (Blueprint $table) {
            $table->dropColumn(['package_length', 'package_width', 'package_height', 'package_weight']);
        });

        Schema::table('products_bundling_items', function (Blueprint $table) {
            $table->dropColumn(['is_suggest', 'bundle_price', 'discount_percent', 'discount_nominal']);
        });
    }
};
