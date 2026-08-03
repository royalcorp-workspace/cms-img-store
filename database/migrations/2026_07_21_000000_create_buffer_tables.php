<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('buffers', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('session_id', 100)->nullable();
            $table->string('customer_name', 255)->nullable();
            $table->string('customer_email', 255)->nullable();
            $table->string('customer_phone', 50)->nullable();
            $table->decimal('subtotal', 15, 2)->default(0);
            $table->decimal('tax', 15, 2)->default(0);
            $table->decimal('discount', 15, 2)->default(0);
            $table->decimal('total', 15, 2)->default(0);
            $table->json('meta')->nullable();
            $table->string('creator', 36)->nullable();
            $table->string('editor', 36)->nullable();
            $table->timestamps();
        });

        Schema::create('buffer_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('buffer_id');
            $table->uuid('product_id');
            $table->uuid('product_variant_id')->nullable();
            $table->string('name', 255);
            $table->integer('quantity')->default(1);
            $table->decimal('unit_price', 15, 2)->default(0);
            $table->decimal('total', 15, 2)->default(0);
            $table->decimal('discount_nominal', 15, 2)->default(0);
            $table->decimal('discount_percent', 5, 2)->default(0);
            $table->text('item_notes')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('buffer_items');
        Schema::dropIfExists('buffers');
    }
};
