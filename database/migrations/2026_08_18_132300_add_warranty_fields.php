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
            if (!Schema::hasColumn('product_category', 'has_warranty')) {
                $table->boolean('has_warranty')->default(false)->after('is_active');
            }
        });

        Schema::table('products', function (Blueprint $table) {
            if (!Schema::hasColumn('products', 'warranty_duration')) {
                $table->string('warranty_duration')->nullable()->after('description');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('product_category', function (Blueprint $table) {
            if (Schema::hasColumn('product_category', 'has_warranty')) {
                $table->dropColumn('has_warranty');
            }
        });

        Schema::table('products', function (Blueprint $table) {
            if (Schema::hasColumn('products', 'warranty_duration')) {
                $table->dropColumn('warranty_duration');
            }
        });
    }
};
