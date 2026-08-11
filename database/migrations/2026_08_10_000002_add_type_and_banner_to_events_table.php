<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('events')) {
            Schema::table('events', function (Blueprint $table) {
                if (!Schema::hasColumn('events', 'event_type')) {
                    $table->string('event_type')->default('mega_campaign')->after('slug');
                }
                if (!Schema::hasColumn('events', 'banner_image')) {
                    $table->string('banner_image')->nullable()->after('event_type');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('events')) {
            Schema::table('events', function (Blueprint $table) {
                $table->dropColumn(['event_type', 'banner_image']);
            });
        }
    }
};
