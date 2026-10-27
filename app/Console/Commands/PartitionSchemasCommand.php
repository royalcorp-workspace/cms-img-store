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
    protected $signature = 'db:partition-schemas {--force : Force schema creation and table migration}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Partition database tables into their respective domain schemas (PostgreSQL Multi-Schema)';

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
            'temporary_jde_item_masters',
            'temporary_jde_base_prices',
        ],
        'sales' => [
            'orders',
            'order_items',
            'order_logs',
            'settlements',
            'payments',
            'payment_methods',
            'invoices',
            'invoice_items',
            'void_orders',
            'credit_memos',
        ],
        'promo' => [
            'vouchers',
            'voucher_products',
            'voucher_brands',
            'voucher_usages',
            'voucher_claims',
            'voucher_customers',
            'price_product_settings',
            'price_product_setting_items',
            'events',
            'event_popups',
        ],
        'customer' => [
            'customers',
            'customer_groups',
            'customer_group_relations',
            'addresses',
            'shipping_addresses',
            'shipping_address_rates',
            'wishlists',
        ],
        'shipping' => [
            'couriers',
            'courier_restrictions',
            'courier_rules',
            'deliveries',
            'delivery_logs',
            'packing_outs',
            'packing_slips',
            'packing_slip_items',
            'picking_lists',
            'picking_list_items',
            'hand_overs',
            'hand_over_orders',
            'provinces',
            'cities',
            'sub_districts',
            'villages',
        ],
        'inventory' => [
            'inventories',
            'stock_cards',
            'warehouses',
            'order_buffers',
            'order_buffer_items',
            'order_buffer_notes',
        ],
        'store' => [
            'store',
            'store_channels',
            'store_channel_stocks',
            'store_tiers',
            'store_groups',
            'store_followers',
            'temporary_jde_branches',
            'temporary_jde_customers',
        ],
        'content' => [
            'banners',
            'banner_images',
            'homepage_sections',
            'homepage_section_items',
            'notifications',
            'chats',
            'chat_messages',
        ],
        'user_managements' => [
            'users',
            'user_admin',
            'roles',
            'permissions',
            'role_permissions',
            'user_roles',
            'rbac_user_roles',
            'activity_logs',
            'login_attempts',
            'device_sessions',
            'api_keys',
            'key_usage',
            'password_resets',
            'email_verifications',
            'otps',
        ],
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

            if (!$this->option('force') && !$this->confirm('Apakah Anda ingin mencoba melanjutkan? (Mungkin akan error permission)', false)) {
                return self::FAILURE;
            }
        }

        $allSchemas = array_keys($this->domainSchemas);
        $searchPathList = implode(', ', array_merge(['public'], $allSchemas));

        // 1. Buat Schema
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

        // 3. Pindahkan Tabel dari public ke Domain Schema
        $this->info('3. Memindahkan Tabel ke Domain Schemas...');
        $totalMoved = 0;
        foreach ($this->domainSchemas as $targetSchema => $tables) {
            $schemaReady = (bool) DB::selectOne("SELECT 1 FROM information_schema.schemata WHERE schema_name = ?", [$targetSchema]);
            if (!$schemaReady) {
                $this->warn("   [!] Schema [{$targetSchema}] belum ada di database. Tabel tidak dapat dipindahkan.");
                continue;
            }

            foreach ($tables as $table) {
                $existsInPublic = (bool) DB::selectOne(
                    "SELECT 1 FROM information_schema.tables WHERE table_schema = 'public' AND table_name = ?",
                    [$table]
                );

                if ($existsInPublic) {
                    try {
                        DB::statement("ALTER TABLE \"public\".\"{$table}\" SET SCHEMA \"{$targetSchema}\"");
                        $this->line("   - [public.{$table}] -> <info>[{$targetSchema}.{$table}]</info>");
                        $totalMoved++;
                    } catch (\Throwable $e) {
                        $this->line("   - [public.{$table}]: <error>Gagal ({$e->getMessage()})</error>");
                    }
                }
            }
        }

        $this->newLine();
        $this->info("SELESAI: Total {$totalMoved} tabel berhasil dipindahkan ke Domain Schema masing-masing.");
        return self::SUCCESS;
    }
}
