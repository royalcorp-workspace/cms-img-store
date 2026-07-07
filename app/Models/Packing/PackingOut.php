<?php

namespace App\Models\Packing;

use App\Models\Order\Order;
use App\Models\User;
use App\Models\Warehouse\Warehouse;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class PackingOut extends Model
{
    use HasUuids;

    protected $fillable = ['packing_slip_id', 'warehouse_id', 'packer_id', 'status', 'notes'];

    protected function casts(): array
    {
        return ['created_at' => 'datetime', 'updated_at' => 'datetime'];
    }

    public function getStatusAttribute($value)
    {
        $map = [
            1 => 'draft',
            2 => 'ready',
            3 => 'out',
            4 => 'cancelled',
        ];
        return $map[$value] ?? 'draft';
    }

    public function setStatusAttribute($value)
    {
        $map = [
            'draft' => 1,
            'ready' => 2,
            'out' => 3,
            'cancelled' => 4,
        ];
        $this->attributes['status'] = $map[$value] ?? 1;
    }

    public function packingSlip(): BelongsTo
    {
        return $this->belongsTo(PackingSlip::class, 'packing_slip_id');
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'warehouse_id');
    }

    public function packer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'packer_id');
    }

    public function delivery(): HasOne
    {
        return $this->hasOne(Delivery::class, 'packing_out_id');
    }

    public function handover(): HasOne
    {
        return $this->hasOne(Handover::class, 'packing_out_id');
    }
}