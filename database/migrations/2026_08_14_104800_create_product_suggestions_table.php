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
        Schema::create('product_suggestions', function (Blueprint $table) {
            $table->uuid('product_id');
            $table->uuid('suggested_product_id');
            $table->integer('sort_order')->default(0);
            
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
            $table->foreign('suggested_product_id')->references('id')->on('products')->onDelete('cascade');
            
            $table->primary(['product_id', 'suggested_product_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_suggestions');
    }
};
