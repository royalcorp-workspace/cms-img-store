<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('blog_posts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('title', 255);
            $table->string('slug', 255)->unique();
            $table->string('excerpt')->nullable();
            $table->text('content')->nullable();
            $table->string('featured_image', 255)->nullable();
            $table->string('author_name', 255)->nullable();
            $table->boolean('is_published')->default(false);
            $table->boolean('is_featured')->default(false);
            $table->dateTime('published_at')->nullable();
            $table->integer('sort_order')->default(0);
            $table->string('meta_title', 255)->nullable();
            $table->string('meta_description')->nullable();
            $table->string('creator')->nullable();
            $table->string('editor')->nullable();
            $table->boolean('deleted')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('blog_posts');
    }
};
