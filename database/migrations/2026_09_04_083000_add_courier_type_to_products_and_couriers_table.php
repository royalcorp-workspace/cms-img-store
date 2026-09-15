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
            if (!Schema::hasColumn('products', 'courier_type')) {
                $table->string('courier_type', 50)->default('keduanya')->after('warranty_duration');
            }
        });

        Schema::table('couriers', function (Blueprint $table) {
            if (!Schema::hasColumn('couriers', 'courier_type')) {
                $table->string('courier_type', 50)->default('expedisi')->after('name');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('couriers', function (Blueprint $table) {
            if (Schema::hasColumn('couriers', 'courier_type')) {
                $table->dropColumn('courier_type');
            }
        });

        Schema::table('products', function (Blueprint $table) {
            if (Schema::hasColumn('products', 'courier_type')) {
                $table->dropColumn('courier_type');
            }
        });
    }
};
