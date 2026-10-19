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
    public static function recordWebOrderDelivered(string $variantId, int $quantity, ?string $orderNumber = null): bool
    {
        $channel = self::getWebImgChannel();
        $inventory = Inventory::where('product_variant_id', $variantId)
            ->where('store_channel_id', $channel->id)
            ->first();

        if (!$inventory) return false;

        $stockBefore = (int)$inventory->on_stock;
        $inventory->outgoing = max(0, $inventory->outgoing - $quantity);
        $inventory->on_stock = max(0, $inventory->on_stock - $quantity);
        $inventory->save();

        self::recordStockCard([
            'inventory_id' => $inventory->id,
            'product_id' => $inventory->product_id,
            'product_variant_id' => $variantId,
            'warehouse_id' => $inventory->warehouse_id,
            'store_channel_id' => $inventory->store_channel_id,
            'transaction_type' => 'outgoing',
            'reference_type' => 'web_order',
            'reference_number' => $orderNumber,
            'qty_in' => 0,
            'qty_out' => $quantity,
            'stock_before' => $stockBefore,
            'stock_after' => $inventory->on_stock,
            'notes' => 'Pesanan terkirim / selesai pengantaran',
            'creator' => 'System',
        ]);

        return true;
    }

    /**
     * Record a stock card movement entry.
     */
    public static function recordStockCard(array $data): ?\App\Models\Inventory\StockCard
    {
        try {
            return \App\Models\Inventory\StockCard::create([
                'id' => (string) Str::uuid(),
                'inventory_id' => $data['inventory_id'] ?? null,
                'product_id' => $data['product_id'] ?? null,
                'product_variant_id' => $data['product_variant_id'],
                'warehouse_id' => $data['warehouse_id'] ?? null,
                'store_channel_id' => $data['store_channel_id'] ?? null,
                'transaction_type' => $data['transaction_type'] ?? 'adjustment',
                'reference_type' => $data['reference_type'] ?? 'manual',
                'reference_number' => $data['reference_number'] ?? null,
                'qty_in' => (int)($data['qty_in'] ?? 0),
                'qty_out' => (int)($data['qty_out'] ?? 0),
                'stock_before' => (int)($data['stock_before'] ?? 0),
                'stock_after' => (int)($data['stock_after'] ?? 0),
                'notes' => $data['notes'] ?? null,
                'creator' => $data['creator'] ?? (\Illuminate\Support\Facades\Auth::user()?->name ?? 'Admin'),
            ]);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Failed to create stock card: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Check inventory stock levels and trigger Pusher alert if on_stock is low or 0.
     */
    public static function checkAndBroadcastStockAlert(Inventory $inventory): ?\App\Models\Notification
    {
        try {
            $onStock = (int) ($inventory->on_stock ?? 0);
            $available = (int) ($inventory->available ?? 0);

            // Condition: Trigger alert when on_stock is 0 or critical (<= 5)
            if ($onStock > 5) {
                return null;
            }

            // Load product and variant details if not loaded
            if (!$inventory->relationLoaded('product') || !$inventory->product) {
                $inventory->load('product');
            }
            if (!$inventory->relationLoaded('variant') || !$inventory->variant) {
                $inventory->load('variant');
            }
            if (!$inventory->relationLoaded('warehouse') && $inventory->warehouse_id) {
                $inventory->load('warehouse');
            }
            if (!$inventory->relationLoaded('store') && $inventory->store_id) {
                $inventory->load('store');
            }
            if (!$inventory->relationLoaded('channel') && $inventory->store_channel_id) {
                $inventory->load('channel');
            }

            $productName = $inventory->product?->name ?? 'Produk';
            $variantName = $inventory->variant?->name ?? $inventory->variant?->sku ?? 'Varian Standar';
            $sku = $inventory->variant?->sku ?? '-';

            $locationParts = array_filter([
                $inventory->warehouse?->name,
                $inventory->store?->name,
                $inventory->channel?->name,
            ]);
            $locationName = !empty($locationParts) ? implode(' / ', $locationParts) : 'Gudang Utama';

            $isZero = $onStock <= 0;
            $severity = $isZero ? 'critical' : 'warning';
            $type = $isZero ? 'out_of_stock' : 'low_stock';

            if ($isZero) {
                $title = "🚨 STOK HABIS (0): {$productName}";
                $message = "Stok on_stock untuk {$productName} ({$variantName}) di {$locationName} saat ini habis (0 unit)! Segera lakukan restock.";
            } else {
                $title = "⚠️ STOK MENIPIS ({$onStock}): {$productName}";
                $message = "Stok on_stock untuk {$productName} ({$variantName}) di {$locationName} tersisa {$onStock} unit. Waspada agar tidak mencapai 0!";
            }

            // Link to inventory index filtering this variant or product
            $linkUrl = route('inventory.index', ['search' => $inventory->variant?->name ?: $productName]);

            // Avoid duplicate unread notification for the same product & variant with exact same title within the last 10 minutes
            $recentNotif = \App\Models\Notification::where('type', 'stock_alert')
                ->where('is_read', false)
                ->where('title', $title)
                ->where('created_at', '>=', now()->subMinutes(10))
                ->first();

            if ($recentNotif) {
                $notification = $recentNotif;
                $notification->touch();
            } else {
                $notification = \App\Models\Notification::create([
                    'id' => (string) Str::uuid(),
                    'user_id' => null,
                    'title' => $title,
                    'message' => $message,
                    'type' => 'stock_alert',
                    'link_url' => $linkUrl,
                    'is_read' => false,
                    'is_broadcast' => false, // false ensures it is private to Admin CMS and never leaks to public customer web
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            // Construct payload for Pusher broadcast
            $payload = [
                'id' => (string) $notification->id,
                'inventory_id' => (string) $inventory->id,
                'product_id' => (string) $inventory->product_id,
                'product_variant_id' => (string) $inventory->product_variant_id,
                'product_name' => $productName,
                'variant_name' => $variantName,
                'sku' => $sku,
                'location_name' => $locationName,
                'on_stock' => $onStock,
                'available' => $available,
                'type' => $type,
                'severity' => $severity,
                'title' => $title,
                'message' => $message,
                'link_url' => $linkUrl,
                'created_at' => now()->toIso8601String(),
                'time_ago' => 'Baru saja',
            ];

            // Real-time push notification via Pusher
            broadcast(new \App\Events\StockAlertEvent($payload));

            \Illuminate\Support\Facades\Log::info("Stock alert broadcasted via Pusher", [
                'inventory_id' => $inventory->id,
                'product_name' => $productName,
                'on_stock' => $onStock,
                'severity' => $severity,
            ]);

            return $notification;
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Failed to broadcast stock alert: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
            return null;
        }
    }
}
