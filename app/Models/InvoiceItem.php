<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InvoiceItem extends Model
{
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'id', 'invoice_id', 'product_id', 'product_variant_id', 'product_color_id', 'name',
        'quantity', 'unit_price', 'discount_nominal', 'discount_percent', 'total', 'weight',
        'item_notes', 'meta'
    ];

    protected $casts = [
        'unit_price' => 'decimal:2',
        'discount_nominal' => 'decimal:2',
        'discount_percent' => 'decimal:2',
        'total' => 'decimal:2',
        'weight' => 'decimal:2',
        'meta' => 'array',
    ];
}
