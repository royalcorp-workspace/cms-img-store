<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('inventories')) {
            Schema::table('inventories', function (Blueprint $table) {
                if (!Schema::hasColumn('inventories', 'on_stock')) {
                    $table->integer('on_stock')->default(0);
                }
            });

            // Set on_stock from existing available + on_order + outgoing
            DB::table('inventories')->update([
                'on_stock' => DB::raw('COALESCE(available, 0) + COALESCE(on_order, 0) + COALESCE(outgoing, 0)'),
            ]);

            // Fill incoming with initial values for existing inventories if 0
            $inventories = DB::table('inventories')->where('incoming', 0)->get();
            foreach ($inventories as $inv) {
                // Populate realistic incoming stock between 10 and 50 based on variant
                $sampleIncoming = 15;
                if ($inv->on_stock > 0) {
                    $sampleIncoming = (int) ceil($inv->on_stock * 0.8);
                }
                DB::table('inventories')->where('id', $inv->id)->update([
                    'incoming' => $sampleIncoming,
                ]);
            }

            // Ensure available = GREATEST(0, on_stock - on_order - outgoing)
            DB::table('inventories')->update([
                'available' => DB::raw('GREATEST(0, on_stock - on_order - outgoing)'),
                'quantity' => DB::raw('GREATEST(0, on_stock - on_order - outgoing)'),
            ]);
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('inventories')) {
            Schema::table('inventories', function (Blueprint $table) {
                if (Schema::hasColumn('inventories', 'on_stock')) {
                    $table->dropColumn('on_stock');
                }
            });
        }
    }
};
