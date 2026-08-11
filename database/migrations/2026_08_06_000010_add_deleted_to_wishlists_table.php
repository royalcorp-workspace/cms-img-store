<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('wishlists') && !Schema::hasColumn('wishlists', 'deleted')) {
            Schema::table('wishlists', function (Blueprint $table) {
                $table->boolean('deleted')->default(false)->after('updated_at');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('wishlists') && Schema::hasColumn('wishlists', 'deleted')) {
            Schema::table('wishlists', function (Blueprint $table) {
                $table->dropColumn('deleted');
            });
        }
    }
};
