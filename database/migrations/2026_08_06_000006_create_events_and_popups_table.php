<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('events')) {
            Schema::create('events', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->string('title');
                $table->string('slug')->unique();
                $table->text('description')->nullable();
                $table->timestamp('start_date')->nullable();
                $table->timestamp('end_date')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
                $table->uuid('creator')->nullable();
                $table->uuid('editor')->nullable();
                $table->boolean('deleted')->default(false);
            });
        }

        if (Schema::hasTable('price_product_settings') && !Schema::hasColumn('price_product_settings', 'event_id')) {
            Schema::table('price_product_settings', function (Blueprint $table) {
                $table->uuid('event_id')->nullable()->after('id');
            });
        }

        if (!Schema::hasTable('event_popups')) {
            Schema::create('event_popups', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->uuid('event_id');
                $table->string('title')->nullable();
                $table->string('image_url')->nullable();
                $table->string('link_url')->nullable();
                $table->string('button_text', 100)->default('Lihat Promo');
                $table->boolean('is_active')->default(true);
                $table->timestamps();
                $table->uuid('creator')->nullable();
                $table->uuid('editor')->nullable();
                $table->boolean('deleted')->default(false);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('event_popups');
        Schema::dropIfExists('events');
    }
};
