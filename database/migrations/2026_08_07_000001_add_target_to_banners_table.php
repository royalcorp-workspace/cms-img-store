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
        Schema::table('banners', function (Blueprint $table) {
            if (!Schema::hasColumn('banners', 'target_type')) {
                $table->string('target_type')->nullable()->after('type'); // 'brand' or 'category'
            }
            if (!Schema::hasColumn('banners', 'target_id')) {
                $table->string('target_id', 36)->nullable()->after('target_type'); // brand_id or category_id
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('banners', function (Blueprint $table) {
            if (Schema::hasColumn('banners', 'target_type')) {
                $table->dropColumn('target_type');
            }
            if (Schema::hasColumn('banners', 'target_id')) {
                $table->dropColumn('target_id');
            }
        });
    }
};
