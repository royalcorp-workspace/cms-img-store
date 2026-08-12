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
        Schema::create('courier_category', function (Blueprint $table) {
            $table->uuid('courier_id');
            $table->uuid('category_id');
            $table->primary(['courier_id', 'category_id']);
            $table->foreign('courier_id')->references('id')->on('couriers')->onDelete('cascade');
            $table->foreign('category_id')->references('id')->on('product_category')->onDelete('cascade');
        });

        Schema::create('courier_product', function (Blueprint $table) {
            $table->uuid('courier_id');
            $table->uuid('product_id');
            $table->primary(['courier_id', 'product_id']);
            $table->foreign('courier_id')->references('id')->on('couriers')->onDelete('cascade');
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('courier_product');
        Schema::dropIfExists('courier_category');
    }
};
