<?php

namespace App\Models\Packing;

use App\Models\Order\Order;
use App\Models\User;
use App\Models\Warehouse\Warehouse;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PackingSlip extends Model
{
    use HasUuids;

    protected $fillable = ['picking_list_id', 'order_id', 'packer_id', 'status', 'box_count', 'weight', 'notes'];

    protected function casts(): array
    {
        return [
            'box_count' => 'integer',
            'weight' => 'decimal:2',
        ];
    }

    public function pickingList(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Picking\PickingList::class, 'picking_list_id');
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    public function packer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'packer_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(PackingSlipItem::class, 'packing_slip_id');
    }
}