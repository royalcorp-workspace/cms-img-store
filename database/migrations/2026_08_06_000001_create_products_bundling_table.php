<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('products_bundling')) {
            Schema::create('products_bundling', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->string('name');
                $table->string('slug')->unique();
                $table->text('description')->nullable();
                $table->decimal('price', 16, 2)->default(0);
                $table->boolean('is_active')->default(true);
                $table->uuid('event_id')->nullable();
                $table->timestamps();
                $table->uuid('creator')->nullable();
                $table->uuid('editor')->nullable();
                $table->boolean('deleted')->default(false);
            });
        }

        if (!Schema::hasTable('products_bundling_items')) {
            Schema::create('products_bundling_items', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->uuid('product_bundling_id');
                $table->uuid('product_id');
                $table->integer('quantity')->default(1);
                $table->uuid('variant_id')->nullable();
                $table->timestamps();
                $table->uuid('creator')->nullable();
                $table->uuid('editor')->nullable();
            });
        }

        if (Schema::hasTable('price_product_settings') && !Schema::hasColumn('price_product_settings', 'bundling_id')) {
            Schema::table('price_product_settings', function (Blueprint $table) {
                $table->uuid('bundling_id')->nullable()->after('event_id');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('products_bundling_items');
        Schema::dropIfExists('products_bundling');
    }
};
