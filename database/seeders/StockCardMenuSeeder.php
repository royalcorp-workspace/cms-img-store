<?php

namespace Database\Seeders;

use App\Models\Inventory\Inventory;
use App\Models\Inventory\StockCard;
use App\Models\Permission;
use App\Models\Role;
use App\Services\InventoryService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class StockCardMenuSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Ensure permission exists
        $permissionName = 'inventory.stock-card.index';
        $perm = Permission::firstOrCreate(
            ['name' => $permissionName],
            [
                'id' => (string) Str::uuid(),
                'guard_name' => 'web',
                'resource' => 'inventory',
                'action' => 'stock-card.index',
                'group' => 'Inventory',
                'description' => 'Hak akses untuk Kartu Stok (inventory.stock-card.index)',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        // Assign permission to all existing roles
        $roles = Role::all();
        foreach ($roles as $role) {
            $roleHasPermission = DB::table('role_has_permissions')
                ->where('role_id', $role->id)
                ->where('permission_id', $perm->id)
                ->exists();

            if (!$roleHasPermission) {
                DB::table('role_has_permissions')->insert([
                    'permission_id' => $perm->id,
                    'role_id' => $role->id,
                ]);
            }
        }

        // 2. Find parent Inventory menu
        $parentInventoryMenu = DB::table('menus')
            ->whereNull('parent_id')
            ->where(function ($q) {
                $q->where('title', 'Inventory')
                  ->orWhere('route_name', 'inventory')
                  ->orWhere('route_name', 'like', 'inventory%');
            })
            ->first();

        $parentInventoryId = $parentInventoryMenu?->id;

        // If parent menu doesn't exist, create it
        if (!$parentInventoryId) {
            $parentInventoryId = (string) Str::uuid();
            DB::table('menus')->insert([
                'id' => $parentInventoryId,
                'parent_id' => null,
                'title' => 'Inventory',
                'icon' => 'warehouse',
                'route_name' => 'inventory.index',
                'url' => null,
                'permission' => 'inventory.index',
                'order' => 5,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // 3. Add or update submenu 'Kartu Stok' under parent Inventory
        $existingStockCardMenu = DB::table('menus')
            ->where('parent_id', $parentInventoryId)
            ->where(function ($q) {
                $q->where('title', 'Kartu Stok')
                  ->orWhere('title', 'Stock Card')
                  ->orWhere('route_name', 'inventory.stock-card.index');
            })
            ->first();

        $maxOrder = DB::table('menus')->where('parent_id', $parentInventoryId)->max('order') ?? 2;

        if (!$existingStockCardMenu) {
            DB::table('menus')->insert([
                'id' => (string) Str::uuid(),
                'parent_id' => $parentInventoryId,
                'title' => 'Kartu Stok',
                'icon' => 'swap_horiz',
                'route_name' => 'inventory.stock-card.index',
                'url' => null,
                'permission' => 'inventory.stock-card.index',
                'order' => $maxOrder + 1,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } else {
            DB::table('menus')->where('id', $existingStockCardMenu->id)->update([
                'title' => 'Kartu Stok',
                'icon' => 'swap_horiz',
                'route_name' => 'inventory.stock-card.index',
                'permission' => 'inventory.stock-card.index',
                'is_active' => true,
                'updated_at' => now(),
            ]);
        }

        // 4. Seed initial baseline records into stock_cards for existing inventories if empty
        if (StockCard::count() === 0) {
            $inventories = Inventory::with(['variant.product', 'warehouse', 'channel'])
                ->where('deleted', false)
                ->take(30)
                ->get();

            foreach ($inventories as $inv) {
                $onStock = (int) $inv->on_stock;
                $incoming = (int) $inv->incoming;
                $outgoing = (int) $inv->outgoing;

                StockCard::create([
                    'id' => (string) Str::uuid(),
                    'inventory_id' => $inv->id,
                    'product_id' => $inv->product_id,
                    'product_variant_id' => $inv->product_variant_id,
                    'warehouse_id' => $inv->warehouse_id,
                    'store_channel_id' => $inv->store_channel_id,
                    'transaction_type' => 'incoming',
                    'reference_type' => 'initial_seed',
                    'reference_number' => 'INIT-' . strtoupper(substr(Str::random(6), 0, 6)),
                    'qty_in' => $incoming > 0 ? $incoming : $onStock,
                    'qty_out' => $outgoing,
                    'stock_before' => max(0, $onStock - ($incoming > 0 ? $incoming : 0) + $outgoing),
                    'stock_after' => $onStock,
                    'notes' => 'Saldo awal stok sistem (Baseline)',
                    'creator' => 'Seeder',
                    'created_at' => now()->subHours(rand(1, 48)),
                    'updated_at' => now(),
                ]);
            }
        }
    }
}
