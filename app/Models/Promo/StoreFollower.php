<?php

namespace App\Models\Promo;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class StoreFollower extends Model
{
    use HasUuids;

    protected $table = 'store_followers';

    protected $fillable = [
        'store_id',
        'customer_id',
    ];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function store()
    {
        return $this->belongsTo(\App\Models\Store\Store::class, 'store_id', 'id');
    }

    public function customer()
    {
        return $this->belongsTo(\App\Models\Customer\Customer::class, 'customer_id', 'id');
    }
}
