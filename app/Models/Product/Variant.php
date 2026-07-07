<?php

namespace App\Models\Product;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Variant extends Model
{
    use HasUuids;

    protected $table = 'product_variants';

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'id',
        'product_id',
        'sku',
        'variant_name',
        'width',
        'length',
        'height',
        'weight',
        'price',
        'stock_qty',
        'min_order_qty',
        'sort_order',
        'status',
        'creator',
        'editor',
        'deleted',
    ];

    protected function casts(): array
    {
        return [
            'width' => 'decimal:2',
            'length' => 'decimal:2',
            'height' => 'decimal:2',
            'weight' => 'decimal:2',
            'price' => 'decimal:2',
            'stock_qty' => 'integer',
            'min_order_qty' => 'integer',
            'sort_order' => 'integer',
            'status' => 'boolean',
            'deleted' => 'boolean',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function priceProductSettings(): BelongsToMany
    {
        return $this->belongsToMany(\App\Models\Promo\PriceProductSetting::class, 'price_product_setting_items', 'variant_id', 'price_product_setting_id')
            ->withPivot('discount_type', 'discount_value', 'product_id');
    }

    public function storePricings(): HasMany
    {
        return $this->hasMany(\App\Models\Promo\StorePricing::class, 'variant_id', 'id');
    }

    protected $appends = ['discounts', 'final_price'];

    public function getDiscountsAttribute(): array
    {
        if (!$this->relationLoaded('priceProductSettings')) {
            $this->load('priceProductSettings');
        }

        return $this->priceProductSettings->map(function ($setting) {
            $price = (float) ($this->price ?? 0);
            $discountType = $setting->pivot->discount_type ?? $setting->discount_type;
            $discountValue = (float) ($setting->pivot->discount_value ?? $setting->discount_value);
            $finalPrice = match ((int) $discountType) {
                1 => max(0, $price - $discountValue),
                2 => max(0, $price - ($price * $discountValue / 100)),
                default => $price,
            };

            return [
                'id' => $setting->id,
                'title' => $setting->title,
                'code' => $setting->code,
                'type' => $setting->type,
                'discount_type' => $discountType,
                'discount_value' => $discountValue,
                'final_price' => round($finalPrice, 2),
                'start_date' => $setting->start_date?->format('Y-m-d H:i:s'),
                'end_date' => $setting->end_date?->format('Y-m-d H:i:s'),
                'min_purchase' => $setting->min_purchase,
                'max_discount' => $setting->max_discount,
                'is_featured' => $setting->is_featured,
                'sort_order' => $setting->sort_order,
            ];
        })->values()->all();
    }

    public function getFinalPriceAttribute(): float
    {
        $discounts = $this->discounts;
        if (empty($discounts)) {
            return (float) ($this->price ?? 0);
        }
        $prices = array_column($discounts, 'final_price');
        return min($prices);
    }
}
