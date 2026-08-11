<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Drop the composite primary key constraint
        DB::statement('ALTER TABLE price_product_setting_items DROP CONSTRAINT IF EXISTS price_product_setting_items_pkey');
        
        // Add a UUID primary key column
        if (!Schema::hasColumn('price_product_setting_items', 'id')) {
            Schema::table('price_product_setting_items', function (Blueprint $table) {
                $table->uuid('id')->default(DB::raw('gen_random_uuid()'))->nullable();
            });
            
            // Populate existing rows with UUIDs
            $rows = DB::table('price_product_setting_items')->get();
            foreach ($rows as $row) {
                DB::table('price_product_setting_items')
                    ->where('price_product_setting_id', $row->price_product_setting_id)
                    ->where('product_id', $row->product_id)
                    ->where(function($q) use ($row) {
                        if ($row->variant_id === null) {
                            $q->whereNull('variant_id');
                        } else {
                            $q->where('variant_id', $row->variant_id);
                        }
                    })
                    ->update(['id' => (string) \Illuminate\Support\Str::uuid()]);
            }
            
            // Set NOT NULL and set as primary key
            DB::statement('ALTER TABLE price_product_setting_items ALTER COLUMN id SET NOT NULL');
            DB::statement('ALTER TABLE price_product_setting_items ADD PRIMARY KEY (id)');
        }
    }

    public function down(): void
    {
        // Revert is not strictly required but we can keep it empty
    }
};
