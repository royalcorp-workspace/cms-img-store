<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('store_followers')) {
            Schema::create('store_followers', function (Blueprint $table) {
                $table->uuid('id')->default(DB::raw('gen_random_uuid()'))->primary();
                $table->unsignedInteger('store_id'); // stores.id is integer
                $table->uuid('customer_id');
                $table->timestamps();

                $table->foreign('store_id')->references('id')->on('stores')->onDelete('cascade');
                $table->foreign('customer_id')->references('id')->on('customers')->onDelete('cascade');
                $table->unique(['store_id', 'customer_id']);
            });
        }

        if (!Schema::hasTable('voucher_claims')) {
            Schema::create('voucher_claims', function (Blueprint $table) {
                $table->uuid('id')->default(DB::raw('gen_random_uuid()'))->primary();
                $table->uuid('voucher_id');
                $table->uuid('customer_id');
                $table->timestampTz('claimed_at')->default(DB::raw('NOW()'));
                $table->timestamps();

                $table->foreign('voucher_id')->references('id')->on('vouchers')->onDelete('cascade');
                $table->foreign('customer_id')->references('id')->on('customers')->onDelete('cascade');
                $table->unique(['voucher_id', 'customer_id']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('voucher_claims');
        Schema::dropIfExists('store_followers');
    }
};
