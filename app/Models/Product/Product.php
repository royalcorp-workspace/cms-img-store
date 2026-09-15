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
        
        static::creating(function ($product) {
            if (empty($product->slug)) {
                $product->slug = \Illuminate\Support\Str::slug($product->name);
            }
            if (empty($product->code)) {
                $datePrefix = 'PRD' . date('dmy');
                $lastProduct = static::withoutGlobalScope('not-deleted')
                    ->where('code', 'like', $datePrefix . '%')
                    ->orderBy('code', 'desc')
                    ->first();
                    
                if ($lastProduct && preg_match('/(\d{5})$/', $lastProduct->code, $matches)) {
                    $lastNumber = (int) $matches[1];
                    $newNumber = $lastNumber + 1;
                } else {
                    $newNumber = 1;
                }
                
                $product->code = $datePrefix . str_pad((string)$newNumber, 5, '0', STR_PAD_LEFT);
            }
        });

        static::updating(function ($product) {
            if (empty($product->slug)) {
                $product->slug = \Illuminate\Support\Str::slug($product->name);
            }
            if (empty($product->code)) {
                $datePrefix = 'PRD' . date('dmy');
                $lastProduct = static::withoutGlobalScope('not-deleted')
                    ->where('code', 'like', $datePrefix . '%')
                    ->orderBy('code', 'desc')
                    ->first();
                    
                if ($lastProduct && preg_match('/(\d{5})$/', $lastProduct->code, $matches)) {
                    $lastNumber = (int) $matches[1];
                    $newNumber = $lastNumber + 1;
                } else {
                    $newNumber = 1;
                }
                
                $product->code = $datePrefix . str_pad((string)$newNumber, 5, '0', STR_PAD_LEFT);
            }
        });
    }

    protected $fillable = [
        'id',
        'code',
        'category_id',
        'brand_id',
        'name',
        'slug',
        'thumbnail',
        'alt_text',
        'short_description',
        'description',
        'warranty_duration',
        'courier_type',
        'shipping_scheme',
        'shipping_cost',
        'length',
        'width',
        'height',
        'weight',
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
            'segments' => 'array',
            'best_seller' => 'boolean',
            'is_new' => 'boolean',
            'sort_order' => 'integer',
            'status' => 'boolean',
            'deleted' => 'boolean',
            'shipping_cost' => 'decimal:2',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    protected $appends = [
        'thumbnail_url',
        'discounts',
        'final_price',
        'courier_type_label',
        'shipping_scheme_label',
        'effective_courier_type',
        'effective_shipping_scheme',
        'effective_shipping_cost',
    ];

    public function getThumbnailUrlAttribute(): ?string
    {
        if ($this->thumbnail) {
            return media_url($this->thumbnail);
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
            $price = 0; // base_price removed, product discounts should be applied to variants or handled differently.
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
        if ($this->relationLoaded('variants') && $this->variants->isNotEmpty()) {
            return (float) ($this->variants->min('sell_price') ?? $this->variants->min('base_price') ?? 0);
        }
        $firstVariant = $this->variants()->first();
        if ($firstVariant) {
            return (float) ($firstVariant->sell_price ?? $firstVariant->base_price ?? 0);
        }
        return 0;
    }

    public function getPriceAttribute(): float
    {
        return $this->final_price;
    }

    public function getEffectiveCourierTypeAttribute(): string
    {
        if ($this->relationLoaded('category') && $this->category && ($this->category->courier_setting_type ?? 'detail') === 'global') {
            return $this->category->courier_type ?: ($this->courier_type ?: 'keduanya');
        }
        return $this->courier_type ?: 'keduanya';
    }

    public function getEffectiveShippingSchemeAttribute(): string
    {
        return $this->shipping_scheme ?: 'dimension';
    }

    public function getEffectiveShippingCostAttribute(): float
    {
        return (float) ($this->shipping_cost ?? 0);
    }

    public function getCourierTypeLabelAttribute(): string
    {
        return match($this->effective_courier_type) {
            'toko' => 'Pengiriman by Toko',
            'expedisi' => 'Pengiriman by Expedisi',
            default => 'Keduanya (Toko & Expedisi)',
        };
    }

    public function getShippingSchemeLabelAttribute(): string
    {
        return match($this->effective_shipping_scheme) {
            'fixed' => 'Ongkir Tetap (Fixed Rate)',
            default => 'Hitung Dari Dimensi & Berat',
        };
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

    public function suggestedProducts(): BelongsToMany
    {
        return $this->belongsToMany(
            Product::class,
            'product_suggestions',
            'product_id',
            'suggested_product_id'
        )->withPivot('sort_order')->orderByPivot('sort_order');
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
