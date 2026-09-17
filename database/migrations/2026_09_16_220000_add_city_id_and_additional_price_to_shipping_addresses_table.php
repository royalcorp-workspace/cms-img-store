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
        Schema::table('shipping_addresses', function (Blueprint $table) {
            if (!Schema::hasColumn('shipping_addresses', 'city_id')) {
                $table->uuid('city_id')->nullable()->after('courier_id');
                $table->index('city_id');
            }
            if (!Schema::hasColumn('shipping_addresses', 'additional_price_per_kg')) {
                $table->decimal('additional_price_per_kg', 15, 2)->default(0)->after('price');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('shipping_addresses', function (Blueprint $table) {
            if (Schema::hasColumn('shipping_addresses', 'additional_price_per_kg')) {
                $table->dropColumn('additional_price_per_kg');
            }
            if (Schema::hasColumn('shipping_addresses', 'city_id')) {
                $table->dropIndex(['city_id']);
                $table->dropColumn('city_id');
            }
        });
    }
};
