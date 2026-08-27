<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CreditMemo extends Model
{
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'id', 'credit_memo_number', 'order_id', 'gateway', 'transaction_id', 'amount', 'status', 'payload', 'paid_at'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'payload' => 'array',
        'paid_at' => 'datetime',
    ];
}
