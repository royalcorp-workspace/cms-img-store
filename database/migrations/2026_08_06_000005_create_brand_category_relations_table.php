<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('brand_category_relations')) {
            Schema::create('brand_category_relations', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->uuid('brand_id');
                $table->uuid('category_id');
                $table->integer('sort_order')->default(0);
                $table->timestamps();

                $table->foreign('brand_id')->references('id')->on('brands')->onDelete('cascade');
                $table->foreign('category_id')->references('id')->on('product_category')->onDelete('cascade');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('brand_category_relations');
    }
};
