<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('voucher_products')) {
            Schema::create('voucher_products', function (Blueprint $table) {
                $table->uuid('id')->default(DB::raw('gen_random_uuid()'))->primary();
                $table->uuid('voucher_id');
                $table->uuid('product_id');
                $table->string('creator', 100)->nullable();
                $table->string('editor', 100)->nullable();
                $table->boolean('deleted')->default(false);
                $table->timestamps();

                // $table->foreign('voucher_id')->references('id')->on('vouchers')->onDelete('cascade');
                // $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
                $table->unique(['voucher_id', 'product_id']);
            });
        }

        if (!Schema::hasTable('voucher_brands')) {
            Schema::create('voucher_brands', function (Blueprint $table) {
                $table->uuid('id')->default(DB::raw('gen_random_uuid()'))->primary();
                $table->uuid('voucher_id');
                $table->uuid('brand_id');
                $table->string('creator', 100)->nullable();
                $table->string('editor', 100)->nullable();
                $table->boolean('deleted')->default(false);
                $table->timestamps();

                $table->foreign('voucher_id')->references('id')->on('vouchers')->onDelete('cascade');
                $table->foreign('brand_id')->references('id')->on('brands')->onDelete('cascade');
                $table->unique(['voucher_id', 'brand_id']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('voucher_brands');
        Schema::dropIfExists('voucher_products');
    }
};
