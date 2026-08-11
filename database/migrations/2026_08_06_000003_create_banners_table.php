<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('banners')) {
            Schema::create('banners', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->string('title')->nullable();
                $table->string('link_url')->nullable();
                $table->boolean('is_active')->default(true);
                $table->integer('sort_order')->default(0);
                $table->smallInteger('type')->default(1)->comment('1 = slider, 2 = running banner');
                $table->smallInteger('device_flag')->default(1)->comment('1 = all, 2 = web only, 3 = mobile only');
                $table->smallInteger('placement_size')->default(1)->comment('1 = Main Banner 1920x500, 2 = Square 300x300, 3 = Custom');
                $table->smallInteger('content_type')->default(1)->comment('1 = File Upload, 2 = Embed Code / External URL');
                $table->string('image_web_url', 500)->nullable();
                $table->string('image_mobile_url', 500)->nullable();
                $table->text('embed_web_content')->nullable();
                $table->text('embed_mobile_content')->nullable();
                $table->timestamps();
                $table->uuid('creator')->nullable();
                $table->uuid('editor')->nullable();
                $table->boolean('deleted')->default(false);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('banners');
    }
};
