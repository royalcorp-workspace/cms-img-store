<?php

namespace App\Models\Promo;

use App\Models\Product\Product;
use App\Models\Product\Variant;
use App\Models\Store\Store;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StorePricing extends Model
{
    use HasUuids;

    protected $table = 'price_product_setting_store';

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'id',
        'price_product_setting_id',
        'store_id',
        'store_channel_id',
        'product_id',
        'variant_id',
        'adjustments',
    ];

    protected function casts(): array
    {
        return [
            'adjustments' => 'array',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(Variant::class, 'variant_id');
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class, 'store_id');
    }

    public function getBasePriceAttribute(): float
    {
        if (!$this->adjustments || empty($this->adjustments)) {
            return 0;
        }

        $base = $this->adjustments[0]['adjustment_amount'] ?? 0;
        return (float) $base;
    }

    public function getFinalPriceAttribute(): float
    {
        if (!$this->adjustments || empty($this->adjustments)) {
            return 0;
        }

        $finalPrice = 0;
        foreach ($this->adjustments as $adj) {
            $finalPrice += (float) ($adj['adjustment_amount'] ?? 0);
        }

        return max(0, round($finalPrice, 2));
    }
}
