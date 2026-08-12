<?php

namespace App\Models\Shipping;

use App\Models\Shipping\ShippingAddress;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Courier extends Model
{
    use HasUuids;

    protected $table = 'couriers';

    protected $fillable = [
        'code',
        'name',
        'type',
        'is_active',
        'sort_order',
        'creator',
        'editor',
        'deleted',
    ];

    protected function casts(): array
    {
        return [
            'type' => 'integer',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
            'deleted' => 'boolean',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    protected static function boot(): void
    {
        parent::boot();

        static::addGlobalScope('active', function ($query) {
            $query->where('is_active', true)
                ->where('deleted', false);
        });
    }

    public function shippingAddresses(): HasMany
    {
        return $this->hasMany(ShippingAddress::class, 'courier_id');
    }

    public function restrictedCategories(): BelongsToMany
    {
        return $this->belongsToMany(\App\Models\Product\Category::class, 'courier_category', 'courier_id', 'category_id');
    }

    public function restrictedProducts(): BelongsToMany
    {
        return $this->belongsToMany(\App\Models\Product\Product::class, 'courier_product', 'courier_id', 'product_id');
    }

    public function shippingPrices(): HasMany
    {
        return $this->hasMany(ShippingAddress::class, 'courier_id', 'id')->where('type', 1);
    }

    public function deliveries(): HasMany
    {
        return $this->hasMany(\App\Models\Packing\Delivery::class, 'courier_id', 'id');
    }
}
