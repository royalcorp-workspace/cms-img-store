<?php

namespace App\Models\Product;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductBundlingItem extends Model
{
    use HasUuids;

    protected $table = 'products_bundling_items';

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'product_bundling_id',
        'product_id',
        'quantity',
        'variant_id',
        'is_suggest',
        'bundle_price',
        'discount_percent',
        'discount_nominal',
        'creator',
        'editor',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'is_suggest' => 'boolean',
        'bundle_price' => 'decimal:2',
        'discount_percent' => 'decimal:2',
        'discount_nominal' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function bundleProduct(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_bundling_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(Variant::class, 'variant_id');
    }
}
