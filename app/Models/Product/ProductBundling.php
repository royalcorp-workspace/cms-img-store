<?php

namespace App\Models\Product;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductBundling extends Model
{
    use HasUuids;

    protected $table = 'products_bundling';

    protected $keyType = 'string';
    public $incrementing = false;

    protected $appends = ['image_full_url', 'banner_image_full_url'];

    public function getImageFullUrlAttribute(): ?string
    {
        return media_url($this->image_url);
    }

    public function getBannerImageFullUrlAttribute(): ?string
    {
        return media_url($this->banner_image);
    }

    protected $fillable = [
        'name',
        'slug',
        'description',
        'price',
        'discount_type',
        'image_url',
        'banner_image',
        'is_active',
        'event_id',
        'creator',
        'editor',
        'deleted',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'is_active' => 'boolean',
        'deleted' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected static function boot(): void
    {
        parent::boot();
        static::addGlobalScope('not-deleted', fn($q) => $q->where('products_bundling.deleted', false));
    }

    public function items(): HasMany
    {
        return $this->hasMany(ProductBundlingItem::class, 'product_bundling_id');
    }
}
