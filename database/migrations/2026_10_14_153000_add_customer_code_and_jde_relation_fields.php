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
        // 1. Tambah customer_code dan address_number di tabel customers
        Schema::table('customers', function (Blueprint $table) {
            if (!Schema::hasColumn('customers', 'customer_code')) {
                $table->string('customer_code', 50)->nullable()->index()->after('customer_type');
            }
            if (!Schema::hasColumn('customers', 'address_number')) {
                $table->bigInteger('address_number')->nullable()->index()->after('customer_code');
            }
        });

        // 2. Tambah address_number di tabel store (untuk pemetaan JDE Address Number per Store / Cabang)
        Schema::table('store', function (Blueprint $table) {
            if (!Schema::hasColumn('store', 'address_number')) {
                $table->bigInteger('address_number')->nullable()->index()->after('code');
            }
        });

        // 3. Tambah relasi store, channel, business_unit, dan address_number di tabel orders
        Schema::table('orders', function (Blueprint $table) {
            if (!Schema::hasColumn('orders', 'store_id')) {
                $table->uuid('store_id')->nullable()->index()->after('customer_id');
            }
            if (!Schema::hasColumn('orders', 'store_channel_id')) {
                $table->uuid('store_channel_id')->nullable()->index()->after('store_id');
            }
            if (!Schema::hasColumn('orders', 'business_unit')) {
                $table->string('business_unit', 50)->nullable()->index()->after('store_channel_id');
            }
            if (!Schema::hasColumn('orders', 'address_number')) {
                $table->bigInteger('address_number')->nullable()->index()->after('business_unit');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn(['customer_code', 'address_number']);
        });

        Schema::table('store', function (Blueprint $table) {
            $table->dropColumn(['address_number']);
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['store_id', 'store_channel_id', 'business_unit', 'address_number']);
        });
    }
};
