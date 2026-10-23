<?php

namespace App\Models\Inventory;

use App\Traits\HasSchemaTable;

use App\Models\Product\Product;
use App\Models\Product\Variant;
use App\Models\Store\Store;
use App\Models\Store\StoreChannel;
use App\Models\Warehouse\Warehouse;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Inventory extends Model
{
    use HasSchemaTable;

    use HasUuids;

    protected $table = 'inventories';

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'product_id',
        'product_variant_id',
        'warehouse_id',
        'store_id',
        'store_channel_id',
        'on_stock',
        'incoming',
        'on_order',
        'outgoing',
        'available',
        'quantity',
        'creator',
        'editor',
        'deleted',
    ];

    protected function casts(): array
    {
        return [
            'on_stock' => 'integer',
            'incoming' => 'integer',
            'on_order' => 'integer',
            'outgoing' => 'integer',
            'available' => 'integer',
            'quantity' => 'integer',
            'deleted' => 'boolean',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    protected static function boot(): void
    {
        parent::boot();
        static::addGlobalScope('not-deleted', fn($q) => $q->where('inventories.deleted', false));

        static::saving(function (Inventory $inventory) {
            // Formula: available = on_stock - on_order
            $onStock = (int) ($inventory->on_stock ?? 0);
            $onOrder = (int) ($inventory->on_order ?? 0);

            $inventory->available = max(0, $onStock - $onOrder);
            $inventory->quantity = $inventory->available;
        });

        static::saved(function (Inventory $inventory) {
            // Check if on_stock is low (<= 5) or out of stock (<= 0)
            if ($inventory->wasRecentlyCreated || $inventory->wasChanged('on_stock')) {
                if ((int) $inventory->on_stock <= 5) {
                    try {
                        \App\Services\InventoryService::checkAndBroadcastStockAlert($inventory);
                    } catch (\Throwable $e) {
                        \Illuminate\Support\Facades\Log::warning('Stock Alert broadcast failed: ' . $e->getMessage());
                    }
                }
            }
        });
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(Variant::class, 'product_variant_id');
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'warehouse_id');
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class, 'store_id');
    }

    public function channel(): BelongsTo
    {
        return $this->belongsTo(StoreChannel::class, 'store_channel_id');
    }
}
