<?php

namespace App\Services;

use App\Models\Inventory\Inventory;
use App\Models\Store\Store;
use App\Models\Store\StoreChannel;
use App\Models\Warehouse\Warehouse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class InventoryService
{
    /**
     * Get or create the default Online Retail store and Web IMG store channel.
     */
    public static function getWebImgChannel(): StoreChannel
    {
        $channel = StoreChannel::where('code', 'WEB_IMG')
            ->orWhere('name', 'Web IMG')
            ->first();

        if ($channel) {
            return $channel;
        }

        return DB::transaction(function () {
            // 1. Ensure StoreGroup exists
            $storeGroupId = DB::table('store_group')->where('name', 'Online Retail Group')->value('id')
                ?? DB::table('store_group')->value('id');

            if (!$storeGroupId) {
                $storeGroupId = Str::uuid()->toString();
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

            // 2. Ensure Store: Online Retail
            $store = Store::where('code', 'ONLINE_RETAIL')
                ->orWhere('name', 'Online Retail')
                ->first();

            if (!$store) {
                $store = Store::create([
                    'id' => Str::uuid()->toString(),
                    'store_group_id' => $storeGroupId,
                    'code' => 'ONLINE_RETAIL',
                    'name' => 'Online Retail',
                    'credit_limit' => 0,
                    'outstanding_balance' => 0,
                    'payment_term' => 0,
                    'status' => true,
                    'sort_order' => 1,
                    'deleted' => false,
                ]);
            }

            // 3. Ensure StoreChannelGroup
            $channelGroupId = DB::table('store_channel_group')->where('name', 'Default')->value('id')
                ?? DB::table('store_channel_group')->value('id');

            if (!$channelGroupId) {
                $channelGroupId = Str::uuid()->toString();
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

            // 4. Create StoreChannel: Web IMG
            return StoreChannel::create([
                'id' => Str::uuid()->toString(),
                'store_id' => $store->id,
                'store_channel_group_id' => $channelGroupId,
                'code' => 'WEB_IMG',
                'name' => 'Web IMG',
                'description' => 'Kanal Penjualan Website Resmi IMG',
                'status' => true,
                'sort_order' => 1,
                'deleted' => false,
            ]);
        });
    }

    /**
     * Get default warehouse (GD-JKT01 or first active warehouse).
     */
    public static function getDefaultWarehouse(): ?Warehouse
    {
        return Warehouse::where('code', 'GD-JKT01')->first()
            ?? Warehouse::where('status', true)->first()
            ?? Warehouse::first();
    }

    /**
     * Ensure an inventory record exists for a variant.
     */
    public static function ensureVariantInventory(
        string $productId,
        string $variantId,
        ?string $warehouseId = null,
        ?string $channelId = null,
        int $initialAvailable = 0
    ): Inventory {
        $warehouse = $warehouseId ? Warehouse::find($warehouseId) : self::getDefaultWarehouse();
        $channel = $channelId ? StoreChannel::find($channelId) : self::getWebImgChannel();

        $inventory = Inventory::where('product_variant_id', $variantId)
            ->where('warehouse_id', $warehouse?->id)
            ->where('store_channel_id', $channel->id)
            ->first();

        if ($inventory) {
            return $inventory;
        }

        return Inventory::create([
            'id' => Str::uuid()->toString(),
            'product_id' => $productId,
            'product_variant_id' => $variantId,
            'warehouse_id' => $warehouse?->id,
            'store_id' => $channel->store_id,
            'store_channel_id' => $channel->id,
            'on_stock' => $initialAvailable,
            'incoming' => 0,
            'on_order' => 0,
            'outgoing' => 0,
            'available' => $initialAvailable,
            'quantity' => $initialAvailable,
            'deleted' => false,
        ]);
    }

    /**
     * Handle incoming web order: reserve from available to on_order.
     */
    public static function recordWebOrder(string $variantId, int $quantity): bool
    {
        $channel = self::getWebImgChannel();
        $inventory = Inventory::where('product_variant_id', $variantId)
            ->where('store_channel_id', $channel->id)
            ->first();

        if (!$inventory) {
            $variant = \App\Models\Product\Variant::find($variantId);
            if (!$variant) return false;
            $inventory = self::ensureVariantInventory($variant->product_id, $variantId, null, $channel->id, 0);
        }

        $inventory->on_order = $inventory->on_order + $quantity;
        $inventory->save();

        return true;
    }

    /**
     * Handle order cancellation / void: return from on_order to available.
     */
    public static function recordWebOrderCancelled(string $variantId, int $quantity): bool
    {
        $channel = self::getWebImgChannel();
        $inventory = Inventory::where('product_variant_id', $variantId)
            ->where('store_channel_id', $channel->id)
            ->first();

        if (!$inventory) return false;

        $inventory->on_order = max(0, $inventory->on_order - $quantity);
        $inventory->save();

        return true;
    }

    /**
     * Handle order delivery / shipment: move from on_order to outgoing.
     */
    public static function recordWebOrderShipped(string $variantId, int $quantity): bool
    {
        $channel = self::getWebImgChannel();
        $inventory = Inventory::where('product_variant_id', $variantId)
            ->where('store_channel_id', $channel->id)
            ->first();

        if (!$inventory) return false;

        $inventory->on_order = max(0, $inventory->on_order - $quantity);
        $inventory->outgoing = $inventory->outgoing + $quantity;
        $inventory->save();

        return true;
    }

    /**
     * Handle completed delivery: reduce outgoing and deduct on_stock.
     */
    public static function recordWebOrderDelivered(string $variantId, int $quantity): bool
    {
        $channel = self::getWebImgChannel();
        $inventory = Inventory::where('product_variant_id', $variantId)
            ->where('store_channel_id', $channel->id)
            ->first();

        if (!$inventory) return false;

        $inventory->outgoing = max(0, $inventory->outgoing - $quantity);
        $inventory->on_stock = max(0, $inventory->on_stock - $quantity);
        $inventory->save();

        return true;
    }
}
