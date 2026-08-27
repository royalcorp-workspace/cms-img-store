<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('buffers', function (Blueprint $table) {
            $table->uuid('courier_id')->nullable();
            $table->uuid('voucher_id')->nullable();
            $table->decimal('voucher_nominal', 15, 2)->default(0);
            $table->decimal('shipping_cost', 15, 2)->default(0);
            $table->decimal('shipping_cost_subsidy', 15, 2)->default(0);
            $table->uuid('shipping_addresses_id')->nullable();
            $table->decimal('transaction_fee', 15, 2)->default(0);
        });
    }

    public function down(): void
    {
        Schema::table('buffers', function (Blueprint $table) {
            $table->dropColumn([
                'courier_id',
                'voucher_id',
                'voucher_nominal',
                'shipping_cost',
                'shipping_cost_subsidy',
                'shipping_addresses_id',
                'transaction_fee',
            ]);
        });
    }
};
