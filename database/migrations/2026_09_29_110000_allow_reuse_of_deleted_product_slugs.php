<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Rename existing soft-deleted products' slugs to append '-deleted-' and part of UUID if not already appended
        DB::statement("
            UPDATE products 
            SET slug = slug || '-deleted-' || substring(id::text from 1 for 8) 
            WHERE deleted = true AND slug NOT LIKE '%-deleted-%'
        ");

        // 2. Drop the strict unconditional unique constraint on products(slug)
        DB::statement("ALTER TABLE products DROP CONSTRAINT IF EXISTS products_slug_unique");
        DB::statement("DROP INDEX IF EXISTS products_slug_unique");

        // 3. Create a partial unique index that only enforces uniqueness for active (non-deleted) products
        DB::statement("CREATE UNIQUE INDEX products_slug_unique ON products (slug) WHERE deleted = false");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("DROP INDEX IF EXISTS products_slug_unique");
        DB::statement("ALTER TABLE products ADD CONSTRAINT products_slug_unique UNIQUE (slug)");
    }
};
