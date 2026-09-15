<?php

declare(strict_types=1);

namespace App\Models\Packing;

use App\Models\Order\Order;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeliveryLog extends Model
{
    use HasUuids;

    protected $table = 'delivery_logs';

    protected $fillable = [
        'order_id',
        'delivery_id',
        'waybill_id',
        'biteship_order_id',
        'courier_code',
        'event',
        'status',
        'price',
        'cash_on_delivery',
        'location',
        'note',
        'payload',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'cash_on_delivery' => 'decimal:2',
            'payload' => 'array',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    public function delivery(): BelongsTo
    {
        return $this->belongsTo(Delivery::class, 'delivery_id');
    }
}
