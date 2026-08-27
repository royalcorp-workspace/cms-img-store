<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'id', 'invoice_number', 'order_id', 'customer_id', 'courier_id', 'shipping_addresses_id',
        'status', 'payment_method', 'payment_status', 'settlement_id', 'subtotal', 'tax', 'discount',
        'total', 'shipping_cost', 'shipping_cost_subsidy', 'transaction_fee', 'voucher_id', 'voucher_nominal',
        'notes', 'meta', 'creator', 'editor', 'deleted', 'due_date'
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'tax' => 'decimal:2',
        'discount' => 'decimal:2',
        'total' => 'decimal:2',
        'shipping_cost' => 'decimal:2',
        'shipping_cost_subsidy' => 'decimal:2',
        'transaction_fee' => 'decimal:2',
        'voucher_nominal' => 'decimal:2',
        'meta' => 'array',
        'deleted' => 'boolean',
        'due_date' => 'datetime',
    ];

    public function items()
    {
        return $this->hasMany(InvoiceItem::class);
    }
}
