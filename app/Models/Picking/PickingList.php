<?php

namespace App\Models\Picking;

use App\Models\Order\Order;
use App\Models\User;
use App\Models\Warehouse\Warehouse;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PickingList extends Model
{
    use HasUuids;

    protected $fillable = ['order_id', 'warehouse_id', 'picker_id', 'status', 'priority', 'notes'];

    protected function casts(): array
    {
        return [
            'priority' => 'integer',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'warehouse_id');
    }

    public function picker(): BelongsTo
    {
        return $this->belongsTo(User::class, 'picker_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(PickingListItem::class, 'picking_list_id');
    }
}