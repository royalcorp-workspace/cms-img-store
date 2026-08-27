<?php

namespace App\Models\Order;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Settlement extends Model
{
    use HasUuids;

    protected $table = 'settlements';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'reference_id',
        'settlement_date',
        'gross_amount',
        'fee_amount',
        'net_amount',
        'status',
        'notes',
    ];

    protected $casts = [
        'settlement_date' => 'datetime',
        'gross_amount' => 'decimal:2',
        'fee_amount' => 'decimal:2',
        'net_amount' => 'decimal:2',
    ];

    // Assuming a settlement can have many orders
    public function orders()
    {
        return $this->hasMany(Order::class, 'settlement_id');
    }
}
