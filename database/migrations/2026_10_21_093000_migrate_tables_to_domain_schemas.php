<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Disable transaction wrapping so that DDL and permission queries do not abort the transaction block.
     */
    public $withinTransaction = false;

    /**
     * Map of domain schemas and their associated tables.
     *
     * @var array<string, list<string>>
     */
    protected array $domainSchemas = [
        'finish_goods' => [
            'products',
            'product_variants',
            'brands',
            'product_category',
            'product_colors',
            'product_images',
            'product_tags',
            'product_tag_relations',
            'ref_product_categories',
            'products_bundling',
            'products_bundling_items',
            'reviews',
            'product_suggestions',
            'product_reviews',
            'brand_category_relations',
        ],
        'sales' => [
            'orders',
            'order_items',
            'order_logs',
            'settlements',
            'invoices',
            'invoice_items',
            'void_orders',
            'order_voids',
            'credit_memos',
            'payments',
            'payment_methods',
        ],
        'customer' => [
            'customers',
            'customer_groups',
            'customer_group_members',
            'addresses',
            'wishlists',
            'add_to_carts',
            'add_to_cart_items',
        ],
        'shipping' => [
            'provinces',
            'cities',
            'sub_districts',
            'couriers',
            'courier_category',
            'courier_product',
            'shipping_addresses',
            'deliveries',
            'delivery_logs',
            'delivery_items',
            'hand_overs',
            'hand_overs_items',
            'packing_slips',
            'packing_slip_items',
            'packing_outs',
            'picking_lists',
            'picking_list_items',
        ],
        'inventory' => [
            'inventories',
            'stock_cards',
            'warehouses',
            'warehouse_locations',
            'product_stocks',
            'buffers',
            'buffer_items',
            'branch_weekly_limits',
        ],
        'promo' => [
            'events',
            'event_popups',
            'vouchers',
            'voucher_usages',
            'voucher_claims',
            'voucher_brands',
            'voucher_categories',
            'voucher_products',
            'voucher_customers',
            'voucher_customer_groups',
            'price_product_settings',
            'price_product_setting_items',
            'price_product_setting_store',
            'price_product_setting_tier',
            'price_product_setting_volume_tiers',
            'store_followers',
        ],
        'store' => [
            'store',
            'stores',
            'store_channel',
            'store_channels',
            'store_group',
            'store_groups',
            'store_tier',
            'store_tiers',
            'store_channel_group',
            'store_channel_groups',
            'store_channel_stocks',
            'branches',
            'trash_stores',
            'tiers',
        ],
        'content' => [
            'banners',
            'banner_images',
            'about_us',
            'blog_posts',
            'faqs',
            'homepage_sections',
            'how_to_returns',
            'privacy_policies',
            'terms_and_conditions',
            'warranty_claims',
            'notifications',
        ],
        'user_managements' => [
            'user_admin',
            'users',
            'roles',
            'permissions',
            'model_has_roles',
            'model_has_permissions',
            'role_has_permissions',
            'rbac_roles',
            'rbac_permissions',
            'rbac_role_permissions',
            'rbac_user_roles',
            'key_permissions',
            'key_usage',
            'api_keys',
            'activity_logs',
            'login_attempts',
            'refresh_tokens',
            'device_sessions',
            'password_resets',
            'email_verifications',
            'otps',
        ],
    ];

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (DB::getDriverName() !== 'pgsql') {
            return;
        }

        $allSchemas = array_keys($this->domainSchemas);
        $searchPathList = implode(', ', array_merge(['public'], $allSchemas));

        // 1. Create all domain schemas if they do not exist
        foreach ($allSchemas as $schema) {
            $schemaExists = DB::selectOne(
                "SELECT 1 FROM information_schema.schemata WHERE schema_name = ?",
                [$schema]
            );

            if (!$schemaExists) {
                try {
                    DB::statement("CREATE SCHEMA IF NOT EXISTS \"{$schema}\"");
                } catch (\Throwable $e) {
                    \Illuminate\Support\Facades\Log::warning("Skipped CREATE SCHEMA {$schema} due to DB permissions: " . $e->getMessage());
                }
            }
        }

        // 2. Set search_path for current connection and alter database default
        DB::statement("SET search_path TO {$searchPathList}");

        try {
            $dbName = config('database.connections.pgsql.database');
            if ($dbName) {
                DB::statement("ALTER DATABASE \"{$dbName}\" SET search_path TO {$searchPathList}");
            }
        } catch (\Throwable $e) {
            // Silently continue if DB user does not have superuser/ALTER DATABASE permissions
        }

        // 3. Move each table from 'public' to its designated domain schema
        foreach ($this->domainSchemas as $targetSchema => $tables) {
            $targetSchemaExists = DB::selectOne(
                "SELECT 1 FROM information_schema.schemata WHERE schema_name = ?",
                [$targetSchema]
            );

            if (!$targetSchemaExists) {
                \Illuminate\Support\Facades\Log::warning("Skipping schema move for {$targetSchema} because schema does not exist.");
                continue;
            }

            foreach ($tables as $table) {
                $exists = DB::selectOne(
                    "SELECT 1 FROM information_schema.tables WHERE table_schema = 'public' AND table_name = ?",
                    [$table]
                );

                if ($exists) {
                    try {
                        DB::statement("ALTER TABLE \"public\".\"{$table}\" SET SCHEMA \"{$targetSchema}\"");
                    } catch (\Throwable $e) {
                        \Illuminate\Support\Facades\Log::warning("Cannot move table {$table} to {$targetSchema}: " . $e->getMessage());
                    }
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() !== 'pgsql') {
            return;
        }

        // Move all tables back from domain schemas to 'public'
        foreach ($this->domainSchemas as $sourceSchema => $tables) {
            $sourceSchemaExists = DB::selectOne(
                "SELECT 1 FROM information_schema.schemata WHERE schema_name = ?",
                [$sourceSchema]
            );

            if (!$sourceSchemaExists) {
                continue;
            }

            foreach ($tables as $table) {
                $exists = DB::selectOne(
                    "SELECT 1 FROM information_schema.tables WHERE table_schema = ? AND table_name = ?",
                    [$sourceSchema, $table]
                );

                if ($exists) {
                    try {
                        DB::statement("ALTER TABLE \"{$sourceSchema}\".\"{$table}\" SET SCHEMA \"public\"");
                    } catch (\Throwable $e) {
                        \Illuminate\Support\Facades\Log::warning("Cannot move table {$table} back to public: " . $e->getMessage());
                    }
                }
            }
        }

        // Reset search_path
        DB::statement("SET search_path TO public");

        try {
            $dbName = config('database.connections.pgsql.database');
            if ($dbName) {
                DB::statement("ALTER DATABASE \"{$dbName}\" SET search_path TO public");
            }
        } catch (\Throwable $e) {
            // Silently continue
        }
    }
};
