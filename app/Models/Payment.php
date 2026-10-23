<?php

namespace App\Models;

use App\Traits\HasSchemaTable;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasSchemaTable;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'id', 'payment_number', 'order_id', 'gateway', 'transaction_id', 'amount', 'status', 'payload', 'paid_at'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'payload' => 'array',
        'paid_at' => 'datetime',
    ];
}
