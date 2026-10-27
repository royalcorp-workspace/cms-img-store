<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class PartitionSchemasCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:partition-schemas {--force : Force schema creation and table migration} {--clean-only : Only remove duplicate tables from public schema}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Partition database tables into their respective domain schemas (PostgreSQL Multi-Schema) and clean public duplicates';

    /**
     * Map of domain schemas and their associated tables.
     *
     * @var array<string, list<string>>
     */
    protected array $domainSchemas = [
        'finish_goods' => [
            'brand_category_relations',
            'brands',
            'product_category',
            'product_colors',
            'product_images',
            'product_reviews',
            'product_suggestions',
            'product_tag_relations',
            'product_tags',
            'product_variants',
            'products',
            'products_bundling',
            'products_bundling_items',
            'ref_product_categories',
            'reviews',
            'temporary_jde_base_prices',
            'temporary_jde_item_masters',
        ],
        'sales' => [
            'credit_memos',
            'invoice_items',
            'invoices',
            'order_items',
            'order_logs',
            'order_voids',
            'orders',
            'payment_methods',
            'payments',
            'settlements',
            'void_orders',
        ],
        'promo' => [
            'event_popups',
            'events',
            'price_product_setting_items',
            'price_product_setting_store',
            'price_product_setting_tier',
            'price_product_setting_volume_tiers',
            'price_product_settings',
            'store_followers',
            'voucher_brands',
            'voucher_categories',
            'voucher_claims',
            'voucher_customer_groups',
            'voucher_customers',
            'voucher_products',
            'voucher_usages',
            'vouchers',
        ],
        'customer' => [
            'add_to_cart_items',
            'add_to_carts',
            'addresses',
            'customer_group_members',
            'customer_group_relations',
            'customer_groups',
            'customers',
            'wishlists',
        ],
        'shipping' => [
            'cities',
            'courier_category',
            'courier_product',
            'courier_restrictions',
            'courier_rules',
            'couriers',
            'deliveries',
            'delivery_items',
            'delivery_logs',
            'hand_overs',
            'hand_over_orders',
            'hand_overs_items',
            'packing_outs',
            'packing_slip_items',
            'packing_slips',
            'picking_list_items',
            'picking_lists',
            'provinces',
            'shipping_addresses',
            'shipping_address_rates',
            'sub_districts',
            'villages',
        ],
        'inventory' => [
            'branch_weekly_limits',
            'buffer_items',
            'buffers',
            'inventories',
            'order_buffers',
            'order_buffer_items',
            'order_buffer_notes',
            'product_stocks',
            'stock_cards',
            'warehouse_locations',
            'warehouses',
        ],
        'store' => [
            'branches',
            'store',
            'store_channel',
            'store_channel_group',
            'store_channel_groups',
            'store_channel_stocks',
            'store_channels',
            'store_group',
            'store_groups',
            'store_tier',
            'store_tiers',
            'temporary_jde_branches',
            'temporary_jde_customers',
            'tiers',
            'trash_stores',
        ],
        'content' => [
            'about_us',
            'banner_images',
            'banners',
            'blog_posts',
            'chats',
            'chat_messages',
            'faqs',
            'homepage_sections',
            'homepage_section_items',
            'how_to_returns',
            'notifications',
            'privacy_policies',
            'terms_and_conditions',
            'warranty_claims',
        ],
        'user_managements' => [
            'activity_logs',
            'api_keys',
            'device_sessions',
            'email_verifications',
            'key_permissions',
            'key_usage',
            'login_attempts',
            'model_has_permissions',
            'model_has_roles',
            'otps',
            'password_resets',
            'permissions',
            'rbac_permissions',
            'rbac_role_permissions',
            'rbac_roles',
            'rbac_user_roles',
            'refresh_tokens',
            'role_has_permissions',
            'roles',
            'user_admin',
            'user_roles',
            'users',
        ],
    ];

    /**
     * System / framework tables that MUST stay in the public schema.
     *
     * @var list<string>
     */
    protected array $reservedPublicTables = [
        'migrations',
        'sessions',
        'cache',
        'cache_locks',
        'jobs',
        'failed_jobs',
        'job_batches',
        'personal_access_tokens',
    ];

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        if (DB::getDriverName() !== 'pgsql') {
            $this->error('Perintah ini hanya berlaku untuk koneksi basis data PostgreSQL.');
            return self::FAILURE;
        }

        $this->info('===========================================================');
        $this->info('  POSTGRESQL MULTI-SCHEMA PARTITIONING & MIGRATION        ');
        $this->info('===========================================================');

        $currentUser = DB::selectOne("SELECT current_user as u")?->u ?? 'unknown';
        $currentDb = DB::selectOne("SELECT current_database() as d")?->d ?? 'unknown';
        $canCreate = (bool) (DB::selectOne("SELECT has_database_privilege(current_user, current_database(), 'CREATE') as can_create")?->can_create ?? false);

        $this->line("Koneksi DB  : <comment>{$currentDb}</comment>");
        $this->line("User DB     : <comment>{$currentUser}</comment>");
        $this->line("Hak CREATE  : " . ($canCreate ? '<info>YA (Bisa membuat schema)</info>' : '<error>TIDAK (Permission Denied)</error>'));
        $this->newLine();

        if (!$canCreate) {
            $this->error("PERINGATAN: User [{$currentUser}] tidak memiliki hak 'CREATE' pada database [{$currentDb}].");
            $this->line("Untuk mengatasi ini, jalankan perintah berikut dari user superuser / postgres:");
            $this->comment("  ALTER DATABASE {$currentDb} OWNER TO {$currentUser};");
            $this->newLine();

            if (!$this->option('force') && !$this->confirm('Apakah Anda ingin mencoba melanjutkan?', false)) {
                return self::FAILURE;
            }
        }

        $allSchemas = array_keys($this->domainSchemas);
        $searchPathList = implode(', ', array_merge(['public'], $allSchemas));

        // 1. Buat Schema jika belum ada
        $this->info('1. Memeriksa dan Membuat Domain Schemas...');
        foreach ($allSchemas as $schema) {
            $exists = (bool) DB::selectOne("SELECT 1 FROM information_schema.schemata WHERE schema_name = ?", [$schema]);
            if ($exists) {
                $this->line("   - Schema [{$schema}]: <info>SUDAH ADA</info>");
            } else {
                try {
                    DB::statement("CREATE SCHEMA IF NOT EXISTS \"{$schema}\"");
                    $this->line("   - Schema [{$schema}]: <info>BERHASIL DIBUAT</info>");
                } catch (\Throwable $e) {
                    $this->line("   - Schema [{$schema}]: <error>GAGAL ({$e->getMessage()})</error>");
                }
            }
        }
        $this->newLine();

        // 2. Set search_path
        $this->info('2. Mengatur search_path...');
        try {
            DB::statement("SET search_path TO {$searchPathList}");
            $this->line("   - Session search_path: <info>BERHASIL</info>");
        } catch (\Throwable $e) {
            $this->line("   - Session search_path: <error>{$e->getMessage()}</error>");
        }

        try {
            DB::statement("ALTER DATABASE \"{$currentDb}\" SET search_path TO {$searchPathList}");
            $this->line("   - Database default search_path: <info>BERHASIL</info>");
        } catch (\Throwable $e) {
            $this->line("   - Database default search_path: <comment>Dilewati (memerlukan hak superuser)</comment>");
        }
        $this->newLine();

        // 3. Pindahkan Tabel dari public ke Domain Schema atau bersihkan duplikat
        $this->info('3. Memproses Pemindahan Tabel & Pembersihan Duplikat di public...');
        $totalMoved = 0;
        $totalCleaned = 0;

        foreach ($this->domainSchemas as $targetSchema => $tables) {
            $schemaReady = (bool) DB::selectOne("SELECT 1 FROM information_schema.schemata WHERE schema_name = ?", [$targetSchema]);
            if (!$schemaReady) {
                $this->warn("   [!] Schema [{$targetSchema}] belum ada di database. Dilewati.");
                continue;
            }

            foreach ($tables as $table) {
                $existsInPublic = (bool) DB::selectOne(
                    "SELECT 1 FROM information_schema.tables WHERE table_schema = 'public' AND table_name = ?",
                    [$table]
                );

                if ($existsInPublic) {
                    $existsInTarget = (bool) DB::selectOne(
                        "SELECT 1 FROM information_schema.tables WHERE table_schema = ? AND table_name = ?",
                        [$targetSchema, $table]
                    );

                    if (!$existsInTarget) {
                        // Tabel hanya ada di public -> Pindahkan ke domain schema
                        try {
                            DB::statement("ALTER TABLE \"public\".\"{$table}\" SET SCHEMA \"{$targetSchema}\"");
                            $this->line("   - [public.{$table}] -> <info>[{$targetSchema}.{$table}] (MOVED)</info>");
                            $totalMoved++;
                        } catch (\Throwable $e) {
                            $this->line("   - [public.{$table}]: <error>Gagal move ({$e->getMessage()})</error>");
                        }
                    } else {
                        // Tabel SUDAH ADA di domain schema -> Hapus duplikat lama di public
                        try {
                            DB::statement("DROP TABLE \"public\".\"{$table}\" CASCADE");
                            $this->line("   - [public.{$table}] -> <comment>Dihapus dari public (sudah ada di {$targetSchema}.{$table})</comment>");
                            $totalCleaned++;
                        } catch (\Throwable $e) {
                            $this->line("   - [public.{$table}]: <error>Gagal hapus duplikat ({$e->getMessage()})</error>");
                        }
                    }
                }
            }
        }

        $this->newLine();
        $this->info("===========================================================");
        $this->info("SELESAI:");
        $this->info("  - {$totalMoved} tabel berhasil dipindahkan ke Domain Schema.");
        $this->info("  - {$totalCleaned} tabel duplikat berhasil dibersihkan dari schema public.");
        $this->info("===========================================================");

        return self::SUCCESS;
    }
}
