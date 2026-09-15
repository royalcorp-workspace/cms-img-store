<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('vouchers')) {
            Schema::table('vouchers', function (Blueprint $table) {
                if (!Schema::hasColumn('vouchers', 'show_on_web')) {
                    $table->boolean('show_on_web')->default(true)->after('is_active');
                }
            });
        }

        if (!Schema::hasTable('voucher_customers')) {
            Schema::create('voucher_customers', function (Blueprint $table) {
                $table->uuid('id')->default(DB::raw('gen_random_uuid()'))->primary();
                $table->uuid('voucher_id');
                $table->uuid('customer_id');
                $table->string('creator', 100)->nullable();
                $table->string('editor', 100)->nullable();
                $table->boolean('deleted')->default(false);
                $table->timestamps();

                $table->foreign('voucher_id')->references('id')->on('vouchers')->onDelete('cascade');
                $table->foreign('customer_id')->references('id')->on('customers')->onDelete('cascade');
                $table->unique(['voucher_id', 'customer_id']);
            });
        }

        Schema::dropIfExists('voucher_products');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
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

                $table->foreign('voucher_id')->references('id')->on('vouchers')->onDelete('cascade');
                $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
                $table->unique(['voucher_id', 'product_id']);
            });
        }

        Schema::dropIfExists('voucher_customers');

        if (Schema::hasTable('vouchers') && Schema::hasColumn('vouchers', 'show_on_web')) {
            Schema::table('vouchers', function (Blueprint $table) {
                $table->dropColumn('show_on_web');
            });
        }
    }
};
