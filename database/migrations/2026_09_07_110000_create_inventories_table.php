<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('inventories')) {
            Schema::create('inventories', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->foreignUuid('product_id')->nullable()->constrained('products')->cascadeOnDelete();
                $table->foreignUuid('product_variant_id')->nullable()->constrained('product_variants')->cascadeOnDelete();
                $table->foreignUuid('warehouse_id')->nullable()->constrained('warehouses')->cascadeOnDelete();
                $table->foreignUuid('store_id')->nullable()->constrained('store')->cascadeOnDelete();
                $table->foreignUuid('store_channel_id')->nullable()->constrained('store_channel')->cascadeOnDelete();
                $table->integer('incoming')->default(0);
                $table->integer('on_order')->default(0);
                $table->integer('outgoing')->default(0);
                $table->integer('available')->default(0);
                $table->integer('quantity')->default(0);
                $table->string('creator', 100)->nullable();
                $table->string('editor', 100)->nullable();
                $table->boolean('deleted')->default(false);
                $table->timestampTz('created_at', 0)->nullable();
                $table->timestampTz('updated_at', 0)->nullable();

                $table->index(['product_variant_id', 'warehouse_id', 'store_channel_id'], 'inv_variant_wh_channel_idx');
                $table->index('product_id', 'inv_product_idx');
                $table->index('warehouse_id', 'inv_warehouse_idx');
                $table->index('store_channel_id', 'inv_channel_idx');
            });
        }

        // 1. Ensure default StoreGroup
        $storeGroupId = DB::table('store_group')->where('name', 'Online Retail Group')->value('id');
        if (!$storeGroupId) {
            $existingGroup = DB::table('store_group')->first();
            $storeGroupId = $existingGroup ? $existingGroup->id : Str::uuid()->toString();
            if (!$existingGroup) {
                DB::table('store_group')->insert([
                    'id' => $storeGroupId,
                    'code' => 'GRP-ONLINE',
                    'name' => 'Online Retail Group',
                    'description' => 'Grup Toko Online',
                    'status' => true,
                    'sort_order' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        // 2. Ensure Store: "Online Retail"
        $store = DB::table('store')->where('name', 'Online Retail')->orWhere('code', 'ONLINE_RETAIL')->first();
        if (!$store) {
            $storeId = Str::uuid()->toString();
            DB::table('store')->insert([
                'id' => $storeId,
                'store_group_id' => $storeGroupId,
                'code' => 'ONLINE_RETAIL',
                'name' => 'Online Retail',
                'credit_limit' => 0,
                'outstanding_balance' => 0,
                'payment_term' => 0,
                'status' => true,
                'sort_order' => 1,
                'deleted' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } else {
            $storeId = $store->id;
        }

        // 3. Ensure default StoreChannelGroup
        $channelGroupId = DB::table('store_channel_group')->where('name', 'Default')->value('id');
        if (!$channelGroupId) {
            $existingChannelGroup = DB::table('store_channel_group')->first();
            $channelGroupId = $existingChannelGroup ? $existingChannelGroup->id : Str::uuid()->toString();
            if (!$existingChannelGroup) {
                DB::table('store_channel_group')->insert([
                    'id' => $channelGroupId,
                    'name' => 'Default',
                    'description' => 'Default Channel Group',
                    'status' => true,
                    'sort_order' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        // 4. Ensure StoreChannel: "Web IMG"
        $channel = DB::table('store_channel')->where('name', 'Web IMG')->orWhere('code', 'WEB_IMG')->first();
        if (!$channel) {
            $channelId = Str::uuid()->toString();
            DB::table('store_channel')->insert([
                'id' => $channelId,
                'store_id' => $storeId,
                'store_channel_group_id' => $channelGroupId,
                'code' => 'WEB_IMG',
                'name' => 'Web IMG',
                'description' => 'Kanal Penjualan Website Resmi IMG',
                'status' => true,
                'sort_order' => 1,
                'deleted' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } else {
            $channelId = $channel->id;
        }

        // 5. Ensure Default Warehouse
        $warehouse = DB::table('warehouses')->where('code', 'GD-JKT01')->first() ?? DB::table('warehouses')->first();
        if (!$warehouse) {
            $warehouseId = Str::uuid()->toString();
            DB::table('warehouses')->insert([
                'id' => $warehouseId,
                'code' => 'GD-JKT01',
                'name' => 'Gudang Utama - Jakarta',
                'city' => 'Jakarta Barat',
                'address' => 'Jl. Daan Mogot KM 11',
                'status' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } else {
            $warehouseId = $warehouse->id;
        }

        // 6. Populate inventories from existing product variants if inventories is empty
        if (DB::table('inventories')->count() === 0) {
            $variants = DB::table('product_variants')
                ->where('deleted', false)
                ->where('stock_quantity', '>', 0)
                ->get(['id', 'product_id', 'stock_quantity']);

            $now = now();
            $batch = [];
            foreach ($variants as $v) {
                $batch[] = [
                    'id' => Str::uuid()->toString(),
                    'product_id' => $v->product_id,
                    'product_variant_id' => $v->id,
                    'warehouse_id' => $warehouseId,
                    'store_id' => $storeId,
                    'store_channel_id' => $channelId,
                    'incoming' => 0,
                    'on_order' => 0,
                    'outgoing' => 0,
                    'available' => (int) $v->stock_quantity,
                    'quantity' => (int) $v->stock_quantity,
                    'deleted' => false,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];

                if (count($batch) >= 500) {
                    DB::table('inventories')->insert($batch);
                    $batch = [];
                }
            }
            if (!empty($batch)) {
                DB::table('inventories')->insert($batch);
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('inventories');
    }
};
