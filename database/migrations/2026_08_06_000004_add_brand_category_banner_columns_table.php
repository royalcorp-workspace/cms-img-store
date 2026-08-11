<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('brands') && !Schema::hasColumn('brands', 'banner_web')) {
            Schema::table('brands', function (Blueprint $table) {
                $table->string('banner_web', 500)->nullable()->after('banner');
                $table->string('banner_mobile', 500)->nullable()->after('banner_web');
            });
        }

        if (Schema::hasTable('product_category') && !Schema::hasColumn('product_category', 'banner_web')) {
            Schema::table('product_category', function (Blueprint $table) {
                $table->string('banner_web', 500)->nullable()->after('description');
                $table->string('banner_mobile', 500)->nullable()->after('banner_web');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('brands') && Schema::hasColumn('brands', 'banner_web')) {
            Schema::table('brands', function (Blueprint $table) {
                $table->dropColumn('banner_web');
                $table->dropColumn('banner_mobile');
            });
        }

        if (Schema::hasTable('product_category') && Schema::hasColumn('product_category', 'banner_web')) {
            Schema::table('product_category', function (Blueprint $table) {
                $table->dropColumn('banner_web');
                $table->dropColumn('banner_mobile');
            });
        }
    }
};
