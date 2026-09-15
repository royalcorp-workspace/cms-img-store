<?php

namespace App\Models\Inventory;

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
            // Formula: available is what remains after deducting on_order and outgoing from on_stock
            $onStock = (int) ($inventory->on_stock ?? 0);
            $onOrder = (int) ($inventory->on_order ?? 0);
            $outgoing = (int) ($inventory->outgoing ?? 0);

            $inventory->available = max(0, $onStock - $onOrder - $outgoing);
            $inventory->quantity = $inventory->available;
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
