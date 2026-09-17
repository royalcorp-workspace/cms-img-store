<?php

namespace App\Models\Product;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Category extends Model
{
    use HasUuids;

    protected $table = 'product_category';

    protected $keyType = 'string';
    public $incrementing = false;

    protected $appends = ["banner_web_url", "banner_mobile_url", "courier_setting_type_label", "courier_type_label", "shipping_scheme_label"];

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
        'courier_setting_type',
        'courier_type',
        'shipping_scheme',
        'shipping_cost',
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
            'shipping_cost' => 'decimal:2',
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

    public function getCourierSettingTypeLabelAttribute(): string
    {
        return match($this->courier_setting_type) {
            'global' => 'Global (Semua Produk Seragam)',
            default => 'Detail (Kondisional Per Produk)',
        };
    }

    public function getCourierTypeLabelAttribute(): string
    {
        return match($this->courier_type) {
            'toko' => 'Pengiriman by Toko',
            'expedisi' => 'Pengiriman by Expedisi',
            default => 'Keduanya (Toko & Expedisi)',
        };
    }

    public function getShippingSchemeLabelAttribute(): string
    {
        return match($this->shipping_scheme) {
            'fixed' => 'Ongkos Kirim Tetap (Fixed Rate)',
            default => 'Hitung Dari Dimensi & Berat',
        };
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
