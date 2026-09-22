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
        Schema::table('deliveries', function (Blueprint $table) {
            if (!Schema::hasColumn('deliveries', 'estimated_delivery_at')) {
                $table->timestamp('estimated_delivery_at')->nullable()->after('delivered_at');
            }
            if (!Schema::hasColumn('deliveries', 'estimated_delivery_min')) {
                $table->timestamp('estimated_delivery_min')->nullable()->after('estimated_delivery_at');
            }
            if (!Schema::hasColumn('deliveries', 'estimated_delivery_max')) {
                $table->timestamp('estimated_delivery_max')->nullable()->after('estimated_delivery_min');
            }
            if (!Schema::hasColumn('deliveries', 'estimated_delivery_duration')) {
                $table->string('estimated_delivery_duration', 100)->nullable()->after('estimated_delivery_max');
            }
            if (!Schema::hasColumn('deliveries', 'eta_source')) {
                $table->string('eta_source', 50)->nullable()->after('estimated_delivery_duration')->comment('biteship, store, manual');
            }
            if (!Schema::hasColumn('deliveries', 'eta_notes')) {
                $table->text('eta_notes')->nullable()->after('eta_source');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('deliveries', function (Blueprint $table) {
            if (Schema::hasColumn('deliveries', 'eta_notes')) {
                $table->dropColumn('eta_notes');
            }
            if (Schema::hasColumn('deliveries', 'eta_source')) {
                $table->dropColumn('eta_source');
            }
            if (Schema::hasColumn('deliveries', 'estimated_delivery_duration')) {
                $table->dropColumn('estimated_delivery_duration');
            }
            if (Schema::hasColumn('deliveries', 'estimated_delivery_max')) {
                $table->dropColumn('estimated_delivery_max');
            }
            if (Schema::hasColumn('deliveries', 'estimated_delivery_min')) {
                $table->dropColumn('estimated_delivery_min');
            }
            if (Schema::hasColumn('deliveries', 'estimated_delivery_at')) {
                $table->dropColumn('estimated_delivery_at');
            }
        });
    }
};
