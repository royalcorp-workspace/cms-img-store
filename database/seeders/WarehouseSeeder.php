<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class WarehouseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Clean up existing warehouse data
        DB::table('picking_list_items')->delete();
        DB::table('picking_lists')->delete();
        DB::table('warehouse_locations')->delete();
        DB::table('warehouses')->delete();

        // 2. Create Gudang Utama Jakarta
        $wh1Id = Str::uuid()->toString();
        DB::table('warehouses')->insert([
            'id' => $wh1Id,
            'code' => 'GD-JKT01',
            'name' => 'Gudang Utama - Jakarta',
            'address' => 'Jl. Daan Mogot No. 12, Jakarta Barat',
            'city' => 'Jakarta Barat',
            'status' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Create Locations for WH 1
        $zoneAId = Str::uuid()->toString();
        DB::table('warehouse_locations')->insert([
            'id' => $zoneAId,
            'warehouse_id' => $wh1Id,
            'code' => 'ZONE-A',
            'name' => 'Zone A (Fast Moving)',
            'type' => 1, // zone
            'parent_id' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $areaA1Id = Str::uuid()->toString();
        DB::table('warehouse_locations')->insert([
            'id' => $areaA1Id,
            'warehouse_id' => $wh1Id,
            'code' => 'AREA-A1',
            'name' => 'Area A1 (Spring Bed)',
            'type' => 2, // area
            'parent_id' => $zoneAId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $rackR1Id = Str::uuid()->toString();
        DB::table('warehouse_locations')->insert([
            'id' => $rackR1Id,
            'warehouse_id' => $wh1Id,
            'code' => 'RACK-R1',
            'name' => 'Rack R1',
            'type' => 3, // rack
            'parent_id' => $areaA1Id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $shelfS1Id = Str::uuid()->toString();
        DB::table('warehouse_locations')->insert([
            'id' => $shelfS1Id,
            'warehouse_id' => $wh1Id,
            'code' => 'SHELF-S1',
            'name' => 'Shelf S1',
            'type' => 4, // shelf
            'parent_id' => $rackR1Id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $binB1Id = Str::uuid()->toString();
        DB::table('warehouse_locations')->insert([
            'id' => $binB1Id,
            'warehouse_id' => $wh1Id,
            'code' => 'BIN-B1',
            'name' => 'Bin B1',
            'type' => 5, // bin
            'parent_id' => $shelfS1Id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 3. Create Gudang Cabang Surabaya
        $wh2Id = Str::uuid()->toString();
        DB::table('warehouses')->insert([
            'id' => $wh2Id,
            'code' => 'GD-SBY02',
            'name' => 'Gudang Cabang - Surabaya',
            'address' => 'Jl. Kenjeran No. 45, Surabaya',
            'city' => 'Surabaya',
            'status' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $zoneBId = Str::uuid()->toString();
        DB::table('warehouse_locations')->insert([
            'id' => $zoneBId,
            'warehouse_id' => $wh2Id,
            'code' => 'ZONE-B',
            'name' => 'Zone B (Slow Moving)',
            'type' => 1, // zone
            'parent_id' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $areaB1Id = Str::uuid()->toString();
        DB::table('warehouse_locations')->insert([
            'id' => $areaB1Id,
            'warehouse_id' => $wh2Id,
            'code' => 'AREA-B1',
            'name' => 'Area B1 (Busa)',
            'type' => 2, // area
            'parent_id' => $zoneBId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 4. Create a Confirmed Order for testing Picking List
        // Fetch a customer dynamically
        $customer = DB::table('customers')->first();
        if ($customer) {
            $customerId = $customer->id;
        } else {
            $customerId = Str::uuid()->toString();
            DB::table('customers')->insert([
                'id' => $customerId,
                'name' => 'John Doe',
                'email' => 'john@example.com',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Fetch a product dynamically
        $product = DB::table('products')->first();
        if ($product) {
            $productId = $product->id;
            $productName = $product->name;
            
            // Fetch variant dynamically
            $variant = DB::table('product_variants')->where('product_id', $productId)->first();
            $variantId = $variant ? $variant->id : null;
        } else {
            // Fallback if no products seeded yet
            $productId = Str::uuid()->toString();
            $productName = 'Kasur Busa Royal Foam';
            $variantId = null;
            
            DB::table('products')->insert([
                'id' => $productId,
                'category_id' => DB::table('product_category')->value('id') ?? Str::uuid()->toString(),
                'brand_id' => DB::table('brands')->value('id') ?? Str::uuid()->toString(),
                'name' => $productName,
                'slug' => 'kasur-busa-royal-foam',
                'status' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Create the test order
        $orderId = Str::uuid()->toString();
        DB::table('orders')->insert([
            'id' => $orderId,
            'order_number' => 'ORD-2026-0001',
            'customer_id' => $customerId,
            'status' => 2, // Confirmed
            'payment_method' => 'Transfer Bank',
            'payment_status' => 1, // Paid
            'subtotal' => 2500000.00,
            'tax' => 0.00,
            'discount' => 0.00,
            'total' => 2500000.00,
            'notes' => 'Pemesanan uji coba flow picking list.',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Create the order item
        DB::table('order_items')->insert([
            'id' => Str::uuid()->toString(),
            'order_id' => $orderId,
            'product_id' => $productId,
            'product_variant_id' => $variantId,
            'name' => $productName,
            'quantity' => 2,
            'unit_price' => 1250000.00,
            'total' => 2500000.00,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->command->info('Warehouse and picking list test order dummy data seeded successfully!');
    }
}
