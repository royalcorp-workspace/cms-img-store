<?php

declare(strict_types=1);

namespace App\Traits;

trait HasSchemaTable
{
    /**
     * Map of model namespaces / classes to PostgreSQL domain schemas.
     *
     * @var array<string, string>
     */
    public static array $domainSchemaMap = [
        // Event and EventPopup belong to promo schema
        'App\Models\Product\Event' => 'promo',
        'App\Models\Product\EventPopup' => 'promo',

        // Product domain
        'App\Models\Product\\' => 'finish_goods',

        // Sales domain
        'App\Models\Order\\' => 'sales',
        'App\Models\Payment' => 'sales',
        'App\Models\PaymentMethod' => 'sales',
        'App\Models\Invoice' => 'sales',
        'App\Models\InvoiceItem' => 'sales',
        'App\Models\VoidOrder' => 'sales',
        'App\Models\CreditMemo' => 'sales',

        // Customer domain
        'App\Models\Customer\\' => 'customer',

        // Shipping & Location domain
        'App\Models\Shipping\\' => 'shipping',
        'App\Models\Packing\\' => 'shipping',
        'App\Models\Picking\\' => 'shipping',
        'App\Models\Location\\' => 'shipping',

        // Inventory domain
        'App\Models\Inventory\\' => 'inventory',
        'App\Models\Warehouse\\' => 'inventory',
        'App\Models\Buffer\\' => 'inventory',

        // Promo domain
        'App\Models\Promo\\' => 'promo',

        // Store domain
        'App\Models\Store\\' => 'store',

        // Content domain
        'App\Models\Content\\' => 'content',

        // User Management domain
        'App\Models\Admin' => 'user_managements',
        'App\Models\User' => 'user_managements',
        'App\Models\Role' => 'user_managements',
        'App\Models\Permission' => 'user_managements',
        'App\Models\Auth\\' => 'user_managements',

        // Reviews
        'App\Models\Review' => 'finish_goods',
    ];

    /**
     * Get the table associated with the model, prefixed with PostgreSQL schema if configured.
     *
     * @return string
     */
    public function getTable()
    {
        $table = parent::getTable();

        // If multi-schema is explicitly disabled in .env (e.g. dev environment with restricted DB permissions)
        if (!env('DB_MULTI_SCHEMA', true)) {
            return str_contains($table, '.') ? explode('.', $table)[1] : $table;
        }

        // Only prefix with schema if database driver is PostgreSQL
        try {
            $driver = $this->getConnection()->getDriverName();
        } catch (\Throwable $e) {
            $driver = config('database.default') === 'pgsql' ? 'pgsql' : null;
        }

        if ($driver === 'pgsql') {
            $schema = $this->getModelSchema();
            if (!empty($schema) && !str_contains($table, '.')) {
                return "{$schema}.{$table}";
            }
        }

        return $table;
    }

    /**
     * Determine the database schema for this model.
     *
     * @return string
     */
    public function getModelSchema(): string
    {
        // 1. If model explicitly defines a $schema property
        if (isset($this->schema) && !empty($this->schema)) {
            return (string) $this->schema;
        }

        // 2. Check namespace/class mapping
        $class = static::class;
        foreach (static::$domainSchemaMap as $prefix => $schema) {
            if (str_starts_with($class, $prefix)) {
                return $schema;
            }
        }

        // 3. Fallback to default DB_SCHEMA or 'public'
        return config('database.connections.pgsql.schema') ?? env('DB_SCHEMA', 'public');
    }

    /**
     * Resolve the configured database schema for PostgreSQL.
     *
     * @return string|null
     */
    public static function getDatabaseSchema(): ?string
    {
        return config('database.connections.pgsql.schema') ?? env('DB_SCHEMA', 'public');
    }

    /**
     * Format a given table name with the database schema if applicable.
     *
     * @param string $tableName
     * @param string|null $schema
     * @return string
     */
    public static function withSchema(string $tableName, ?string $schema = null): string
    {
        if (!env('DB_MULTI_SCHEMA', true)) {
            return str_contains($tableName, '.') ? explode('.', $tableName)[1] : $tableName;
        }

        $schema = $schema ?? static::getDatabaseSchema();

        if (!empty($schema) && !str_contains($tableName, '.')) {
            return "{$schema}.{$tableName}";
        }

        return $tableName;
    }
}
