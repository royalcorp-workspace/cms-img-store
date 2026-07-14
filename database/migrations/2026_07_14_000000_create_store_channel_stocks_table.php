<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('store_channel_stocks', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('product_id')->nullable()->constrained('products')->onDelete('cascade');
            $table->foreignUuid('product_variant_id')->nullable()->constrained('product_variants')->onDelete('cascade');
            $table->foreignUuid('store_id')->nullable()->constrained('store')->onDelete('cascade');
            $table->foreignUuid('store_channel_id')->nullable()->constrained('store_channel')->onDelete('cascade');
            $table->integer('incoming')->default(0);
            $table->integer('booked')->default(0);
            $table->integer('on_order')->default(0);
            $table->integer('outgoing')->default(0);
            $table->integer('quantity')->default(0);
            $table->string('creator', 100)->nullable();
            $table->string('editor', 100)->nullable();
            $table->boolean('deleted')->default(false);
            $table->timestampTz('created_at', 0)->nullable();
            $table->timestampTz('updated_at', 0)->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('store_channel_stocks');
    }
};
