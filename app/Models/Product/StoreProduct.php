<?php

declare(strict_types=1);

namespace App\Models\Product;

use App\Traits\HasSchemaTable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StoreProduct extends Model
{
    use HasSchemaTable;
    use HasUuids;

    protected $table = 'finish_goods.store_products';

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'temporary_item_id',
        'store_id',
        'branch_id',
        'business_unit',
        'short_item_no',
        'sku',
        'item_name',
        'item_size',
        'brand_name',
        'category_name',
        'segments',
        'base_price',
        'suggested_retail_price',
        'custom_retail_price',
        'is_active_for_pos',
        'is_floor_display',
        'is_dedicated',
        'display_order',
    ];

    protected $casts = [
        'short_item_no' => 'integer',
        'segments' => 'array',
        'base_price' => 'decimal:2',
        'suggested_retail_price' => 'decimal:2',
        'custom_retail_price' => 'decimal:2',
        'is_active_for_pos' => 'boolean',
        'is_floor_display' => 'boolean',
        'is_dedicated' => 'boolean',
        'display_order' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function store(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Store\Store::class, 'store_id');
    }

    public function temporaryItem(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Temporary\TemporaryJdeItemMaster::class, 'temporary_item_id');
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Product\Variant::class, 'sku', 'sku');
    }
}
