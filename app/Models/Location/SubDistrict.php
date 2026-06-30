<?php

namespace App\Models\Location;

use App\Models\Customer\Address;
use App\Models\Location\City;
use App\Models\Location\Province;
use App\Models\Shipping\ShippingAddress;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SubDistrict extends Model
{
    use HasUuids;

    protected $table = 'sub_districts';

    protected $fillable = [
        'province_id',
        'city_id',
        'district',
        'sub_district',
        'postal_code',
        'is_active',
        'sort_order',
        'creator',
        'editor',
        'deleted',
    ];

    protected function casts(): array
    {
        return [
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

    public function province(): BelongsTo
    {
        return $this->belongsTo(Province::class, 'province_id', 'id');
    }

    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class, 'city_id', 'id');
    }

    public function addresses(): HasMany
    {
        return $this->hasMany(Address::class, 'sub_district_id', 'id');
    }

    public function shippingAddresses(): HasMany
    {
        return $this->hasMany(ShippingAddress::class, 'sub_district_id', 'id');
    }
}
