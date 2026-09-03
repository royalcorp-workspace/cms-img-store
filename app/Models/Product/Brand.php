<?php

namespace App\Models\Product;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Brand extends Model
{
    use HasUuids;

    protected $table = 'brands';

    protected $keyType = 'string';
    public $incrementing = false;

    protected $appends = ['logo_url', 'banner_web_url', 'banner_mobile_url'];

    public function getLogoUrlAttribute(): ?string
    {
        return media_url($this->logo);
    }

    public function getBannerWebUrlAttribute(): ?string
    {
        return media_url($this->banner_web);
    }

    public function getBannerMobileUrlAttribute(): ?string
    {
        return media_url($this->banner_mobile);
    }

    protected static function boot(): void
    {
        parent::boot();
        static::addGlobalScope('not-deleted', fn($q) => $q->where('brands.deleted', false));
    }

    protected $fillable = [
        'name',
        'slug',
        'description',
        'logo',
        'banner_type',
        'banner_web',
        'banner_mobile',
        'embed_web',
        'embed_mobile',
        'banner_link',
        'sort_order',
        'status',
        'is_featured',
        'deleted',
    ];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
            'status' => 'boolean',
            'is_featured' => 'boolean',
            'deleted' => 'boolean',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class, 'brand_id');
    }
}
