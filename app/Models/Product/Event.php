<?php

namespace App\Models\Product;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Promo\PriceProductSetting;

class Event extends Model
{
    use HasUuids;

    protected $table = 'events';

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'title',
        'slug',
        'start_date',
        'end_date',
        'is_active',
        'deleted',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'deleted' => 'boolean',
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected static function boot(): void
    {
        parent::boot();
        static::addGlobalScope('not-deleted', fn($q) => $q->where('events.deleted', false));
    }

    public function popup(): HasOne
    {
        return $this->hasOne(EventPopup::class, 'event_id');
    }

    public function priceProductSettings(): HasMany
    {
        return $this->hasMany(PriceProductSetting::class, 'event_id');
    }
}
