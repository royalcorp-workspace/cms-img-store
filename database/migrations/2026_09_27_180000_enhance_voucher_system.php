<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vouchers', function (Blueprint $table) {
            if (!Schema::hasColumn('vouchers', 'visibility')) {
                $table->string('visibility', 20)->default('public')->after('show_on_web');
                // public = auto-available, claimable = must claim first, hidden = code-only
            }
            if (!Schema::hasColumn('vouchers', 'store_id')) {
                // stores.id is integer, so use unsignedInteger
                $table->unsignedInteger('store_id')->nullable()->after('visibility');
                $table->foreign('store_id')->references('id')->on('stores')->onDelete('set null');
            }
            if (!Schema::hasColumn('vouchers', 'require_follow')) {
                $table->boolean('require_follow')->default(false)->after('store_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('vouchers', function (Blueprint $table) {
            if (Schema::hasColumn('vouchers', 'require_follow')) {
                $table->dropColumn('require_follow');
            }
            if (Schema::hasColumn('vouchers', 'store_id')) {
                $table->dropForeign(['store_id']);
                $table->dropColumn('store_id');
            }
            if (Schema::hasColumn('vouchers', 'visibility')) {
                $table->dropColumn('visibility');
            }
        });
    }
};
