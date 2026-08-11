<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('products_bundling') && !Schema::hasColumn('products_bundling', 'image_url')) {
            Schema::table('products_bundling', function (Blueprint $table) {
                $table->string('image_url', 500)->nullable()->after('price');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('products_bundling') && Schema::hasColumn('products_bundling', 'image_url')) {
            Schema::table('products_bundling', function (Blueprint $table) {
                $table->dropColumn('image_url');
            });
        }
    }
};
