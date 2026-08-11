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
        Schema::table('orders', function (Blueprint $table) {
            if (!Schema::hasColumn('orders', 'order_number')) {
                $table->string('order_number', 100)->nullable()->unique()->after('id');
            }
            if (!Schema::hasColumn('orders', 'courier_id')) {
                $table->uuid('courier_id')->nullable()->after('customer_id');
                $table->foreign('courier_id')->references('id')->on('couriers')->onDelete('set null');
            }
            if (!Schema::hasColumn('orders', 'voucher_nominal')) {
                $table->decimal('voucher_nominal', 15, 2)->default(0.00)->after('voucher_id');
            }
            if (!Schema::hasColumn('orders', 'shipping_cost')) {
                $table->decimal('shipping_cost', 15, 2)->default(0.00)->after('voucher_nominal');
            }
            if (!Schema::hasColumn('orders', 'shipping_cost_subsidy')) {
                $table->decimal('shipping_cost_subsidy', 15, 2)->default(0.00)->after('shipping_cost');
            }
            if (!Schema::hasColumn('orders', 'shipping_addresses_id')) {
                $table->uuid('shipping_addresses_id')->nullable()->after('shipping_cost_subsidy');
                $table->foreign('shipping_addresses_id')->references('id')->on('shipping_addresses')->onDelete('set null');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (Schema::hasColumn('orders', 'shipping_addresses_id')) {
                $table->dropForeign(['shipping_addresses_id']);
                $table->dropColumn('shipping_addresses_id');
            }
            if (Schema::hasColumn('orders', 'shipping_cost_subsidy')) {
                $table->dropColumn('shipping_cost_subsidy');
            }
            if (Schema::hasColumn('orders', 'shipping_cost')) {
                $table->dropColumn('shipping_cost');
            }
            if (Schema::hasColumn('orders', 'voucher_nominal')) {
                $table->dropColumn('voucher_nominal');
            }
            if (Schema::hasColumn('orders', 'courier_id')) {
                $table->dropForeign(['courier_id']);
                $table->dropColumn('courier_id');
            }
            if (Schema::hasColumn('orders', 'order_number')) {
                $table->dropColumn('order_number');
            }
        });
    }
};
