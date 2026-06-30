<?php

namespace App\Models\Promo;

use App\Models\Product\Product;
use App\Models\Product\Variant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PriceProductSetting extends Model
{
    use HasUuids;

    protected $table = 'price_product_settings';

    protected $fillable = [
        'code',
        'title',
        'description',
        'type',
        'scope',
        'discount_type',
        'discount_value',
        'min_purchase',
        'max_discount',
        'start_date',
        'end_date',
        'image_url',
        'is_active',
        'is_featured',
        'sort_order',
        'creator',
        'editor',
        'deleted',
        'volume_tiers',
    ];

    protected function casts(): array
    {
        return [
            'type' => 'integer',
            'scope' => 'integer',
            'discount_type' => 'integer',
            'discount_value' => 'decimal:2',
            'min_purchase' => 'decimal:2',
            'max_discount' => 'decimal:2',
            'start_date' => 'datetime',
            'end_date' => 'datetime',
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
            'sort_order' => 'integer',
            'deleted' => 'boolean',
            'volume_tiers' => 'array',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    protected static function boot(): void
    {
        parent::boot();

        static::addGlobalScope('active', function ($query) {
            $query->where('price_product_settings.is_active', true)
                ->where('price_product_settings.deleted', false)
                ->where(function ($q) {
                    $q->whereNull('price_product_settings.start_date')->orWhere('price_product_settings.start_date', '<=', now());
                })
                ->where(function ($q) {
                    $q->whereNull('price_product_settings.end_date')->orWhere('price_product_settings.end_date', '>=', now());
                });
        });
    }

    public function scopeActive($query)
    {
        return $query->where('price_product_settings.is_active', true)
            ->where('price_product_settings.deleted', false)
            ->where(function ($q) {
                $q->whereNull('price_product_settings.start_date')->orWhere('price_product_settings.start_date', '<=', now());
            })
            ->where(function ($q) {
                $q->whereNull('price_product_settings.end_date')->orWhere('price_product_settings.end_date', '>=', now());
            });
    }

    public function scopeFeatured($query)
    {
        return $query->active()->where('price_product_settings.is_featured', true)->orderBy('price_product_settings.sort_order');
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'price_product_setting_items', 'price_product_setting_id', 'product_id')
            ->withPivot('discount_type', 'discount_value', 'variant_id');
    }

    public function variants(): BelongsToMany
    {
        return $this->belongsToMany(\App\Models\Product\Variant::class, 'price_product_setting_items', 'price_product_setting_id', 'variant_id')
            ->withPivot('discount_type', 'discount_value', 'product_id');
    }

    public function volumeTiers(): HasMany
    {
        return $this->hasMany(\App\Models\Promo\VolumeTier::class, 'price_product_setting_id', 'id');
    }

    public function isVolumeDiscount(): bool
    {
        return (int) $this->type === 2;
    }

    public function typeLabel(): string
    {
        return $this->isVolumeDiscount() ? 'Diskon Volume' : 'Diskon Langsung';
    }
}
