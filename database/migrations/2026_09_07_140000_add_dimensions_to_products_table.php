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
            if (!Schema::hasColumn('products', 'length')) {
                $table->string('length', 50)->nullable()->after('courier_type');
            }
            if (!Schema::hasColumn('products', 'width')) {
                $table->string('width', 50)->nullable()->after('length');
            }
            if (!Schema::hasColumn('products', 'height')) {
                $table->string('height', 50)->nullable()->after('width');
            }
            if (!Schema::hasColumn('products', 'weight')) {
                $table->string('weight', 50)->nullable()->after('height');
            }
        });

        Schema::table('product_variants', function (Blueprint $table) {
            if (!Schema::hasColumn('product_variants', 'length')) {
                $table->string('length', 50)->nullable();
            }
            if (!Schema::hasColumn('product_variants', 'width')) {
                $table->string('width', 50)->nullable();
            }
            if (!Schema::hasColumn('product_variants', 'height')) {
                $table->string('height', 50)->nullable();
            }
            if (!Schema::hasColumn('product_variants', 'weight')) {
                $table->string('weight', 50)->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            foreach (['length', 'width', 'height', 'weight'] as $col) {
                if (Schema::hasColumn('products', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
