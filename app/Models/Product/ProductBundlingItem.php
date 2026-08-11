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
        'creator',
        'editor',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(Variant::class, 'variant_id');
    }
}
