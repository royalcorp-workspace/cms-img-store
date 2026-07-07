<?php

namespace App\Models\Packing;

use App\Models\Order\Order;
use App\Models\Shipping\Courier;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Delivery extends Model
{
    use HasUuids;

    protected $fillable = ['packing_out_id', 'order_id', 'courier_id', 'tracking_number', 'driver_name', 'driver_phone', 'status', 'shipped_at', 'delivered_at', 'notes'];

    protected function casts(): array
    {
        return ['shipped_at' => 'timestamp', 'delivered_at' => 'timestamp'];
    }

    public function getStatusAttribute($value)
    {
        $map = [
            1 => 'pending',
            2 => 'in_transit',
            3 => 'delivered',
            4 => 'failed',
            5 => 'returned',
        ];
        return $map[$value] ?? 'pending';
    }

    public function setStatusAttribute($value)
    {
        $map = [
            'pending' => 1,
            'in_transit' => 2,
            'delivered' => 3,
            'failed' => 4,
            'returned' => 5,
        ];
        $this->attributes['status'] = $map[$value] ?? 1;
    }

    public function packingOut(): BelongsTo
    {
        return $this->belongsTo(PackingOut::class, 'packing_out_id');
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    public function courier(): BelongsTo
    {
        return $this->belongsTo(Courier::class, 'courier_id');
    }
}