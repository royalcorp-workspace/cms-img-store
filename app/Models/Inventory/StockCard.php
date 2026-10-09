<?php

namespace App\Models\Inventory;

use App\Models\Product\Product;
use App\Models\Product\Variant;
use App\Models\Store\StoreChannel;
use App\Models\Warehouse\Warehouse;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockCard extends Model
{
    use HasUuids;

    protected $table = 'stock_cards';

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'inventory_id',
        'product_id',
        'product_variant_id',
        'warehouse_id',
        'store_channel_id',
        'transaction_type',
        'reference_type',
        'reference_number',
        'qty_in',
        'qty_out',
        'stock_before',
        'stock_after',
        'notes',
        'creator',
    ];

    protected function casts(): array
    {
        return [
            'qty_in' => 'integer',
            'qty_out' => 'integer',
            'stock_before' => 'integer',
            'stock_after' => 'integer',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function inventory(): BelongsTo
    {
        return $this->belongsTo(Inventory::class, 'inventory_id');
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

    public function channel(): BelongsTo
    {
        return $this->belongsTo(StoreChannel::class, 'store_channel_id');
    }
}
