<?php

namespace App\Models\Product;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use HasUuids;

    protected $table = 'products';

    protected $keyType = 'string';
    public $incrementing = false;

    protected static function boot(): void
    {
        parent::boot();
        static::addGlobalScope('not-deleted', fn($q) => $q->where('products.deleted', false));
    }

    protected $fillable = [
        'id',
        'category_id',
        'brand_id',
        'name',
        'slug',
        'thumbnail',
        'alt_text',
        'short_description',
        'description',
        'base_price',
        'segments',
        'best_seller',
        'is_new',
        'sort_order',
        'status',
        'creator',
        'editor',
        'deleted',
    ];

    protected function casts(): array
    {
        return [
            'base_price' => 'decimal:2',
            'segments' => 'array',
            'best_seller' => 'boolean',
            'is_new' => 'boolean',
            'sort_order' => 'integer',
            'status' => 'boolean',
            'deleted' => 'boolean',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    protected $appends = ['thumbnail_url', 'discounts', 'final_price'];

    public function getThumbnailUrlAttribute(): ?string
    {
        if ($this->thumbnail) {
            if (filter_var($this->thumbnail, FILTER_VALIDATE_URL)) {
                return $this->thumbnail;
            }
            return asset('storage/' . ltrim($this->thumbnail, '/'));
        }

        if ($this->relationLoaded('images') && $this->images->isNotEmpty()) {
            return $this->images->first()->url;
        }

        return null;
    }

    public function getDiscountsAttribute(): array
    {
        if (!$this->relationLoaded('priceProductSettings')) {
            $this->load('priceProductSettings');
        }

        return $this->priceProductSettings->map(function ($setting) {
            $price = (float) ($this->base_price ?? 0);
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
            return (float) ($this->base_price ?? 0);
        }
        $prices = array_column($discounts, 'final_price');
        return min($prices);
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class, 'brand_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function images(): HasMany
    {
        return $this->hasMany(Image::class, 'product_id');
    }

    public function variants(): HasMany
    {
        return $this->hasMany(Variant::class, 'product_id');
    }

    public function colors(): HasMany
    {
        return $this->hasMany(Color::class, 'product_id');
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class, 'product_tag_relations', 'product_id', 'tag_id');
    }

    public function priceProductSettings(): BelongsToMany
    {
        return $this->belongsToMany(\App\Models\Promo\PriceProductSetting::class, 'price_product_setting_items', 'product_id', 'price_product_setting_id')
            ->withPivot('discount_type', 'discount_value');
    }

    public function storePricings(): HasMany
    {
        return $this->hasMany(\App\Models\Promo\StorePricing::class, 'product_id', 'id');
    }
}
