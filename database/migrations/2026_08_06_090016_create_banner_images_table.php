<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('banner_images')) {
            Schema::create('banner_images', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->uuid('banner_id');
                $table->string('image_web_url', 500)->nullable();
                $table->string('image_mobile_url', 500)->nullable();
                $table->string('link_url', 500)->nullable();
                $table->integer('sort_order')->default(0);
                $table->boolean('deleted')->default(false);
                $table->timestamps();
                $table->foreign('banner_id')->references('id')->on('banners')->onDelete('cascade');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('banner_images');
    }
};
