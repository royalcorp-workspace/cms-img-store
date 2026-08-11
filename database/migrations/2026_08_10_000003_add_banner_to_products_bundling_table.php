<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('products_bundling') && !Schema::hasColumn('products_bundling', 'banner_image')) {
            Schema::table('products_bundling', function (Blueprint $table) {
                $table->string('banner_image')->nullable()->after('image_url');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('products_bundling') && Schema::hasColumn('products_bundling', 'banner_image')) {
            Schema::table('products_bundling', function (Blueprint $table) {
                $table->dropColumn('banner_image');
            });
        }
    }
};
