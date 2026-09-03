<?php

namespace App\Models\Product;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    protected $table = 'product_category';

    protected $keyType = 'string';
    public $incrementing = false;

    protected $appends = ["banner_web_url", "banner_mobile_url"];

    protected $fillable = [
        'parent_id',
        'name',
        'tagline',
        'slug',
        'description',
        'banner_web',
        'banner_mobile',
        'sort_order',
        'is_active',
        'has_warranty',
        'creator',
        'editor',
        'deleted',
    ];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
            'is_active' => 'boolean',
            'deleted' => 'boolean',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function getStatusAttribute()
    {
        return $this->is_active;
    }

    public function setStatusAttribute($value)
    {
        $this->attributes['is_active'] = $value;
    }

    
    public function getBannerWebUrlAttribute(): ?string
    {
        return media_url($this->banner_web);
    }

    public function getBannerMobileUrlAttribute(): ?string
    {
        return media_url($this->banner_mobile);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class, 'category_id');
    }
}
