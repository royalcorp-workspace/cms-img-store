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
        Schema::table('homepage_sections', function (Blueprint $table) {
            if (!Schema::hasColumn('homepage_sections', 'meta')) {
                $table->json('meta')->nullable()->after('is_visible');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('homepage_sections', function (Blueprint $table) {
            if (Schema::hasColumn('homepage_sections', 'meta')) {
                $table->dropColumn('meta');
            }
        });
    }
};
