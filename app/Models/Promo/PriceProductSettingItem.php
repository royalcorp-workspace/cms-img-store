<?php

namespace App\Models\Promo;

use App\Models\Product\Product;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PriceProductSettingItem extends Model
{
    protected $table = 'price_product_setting_items';
    protected $primaryKey = 'price_product_setting_id';
    public $incrementing = false;

    protected $fillable = [
        'price_product_setting_id',
        'product_id',
        'variant_id',
        'discount_type',
        'discount_value',
        'creator',
        'editor',
        'deleted',
    ];

    protected function casts(): array
    {
        return [
            'discount_type' => 'integer',
            'discount_value' => 'decimal:2',
            'deleted' => 'boolean',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function priceProductSetting(): BelongsTo
    {
        return $this->belongsTo(PriceProductSetting::class, 'price_product_setting_id', 'id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id', 'id');
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Product\Variant::class, 'variant_id', 'id');
    }
}
