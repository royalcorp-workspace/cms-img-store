<?php

namespace App\Models\Packing;

use App\Models\Order\Order;
use App\Models\Shipping\Courier;
use App\Models\Warehouse\Warehouse;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Handover extends Model
{
    use HasUuids;

    protected $table = 'hand_overs';

    protected $fillable = ['packing_out_id', 'order_id', 'warehouse_id', 'courier_id', 'driver_name', 'driver_phone', 'tracking_number', 'status', 'notes', 'handover_at'];

    protected function casts(): array
    {
        return [
            'handover_at' => 'datetime',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function packingOut(): BelongsTo
    {
        return $this->belongsTo(PackingOut::class, 'packing_out_id');
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'warehouse_id');
    }

    public function courier(): BelongsTo
    {
        return $this->belongsTo(Courier::class, 'courier_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(HandoverItem::class, 'handover_id');
    }
}
