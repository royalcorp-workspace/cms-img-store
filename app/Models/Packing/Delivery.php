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

    protected $fillable = [
        'packing_out_id',
        'order_id',
        'courier_id',
        'tracking_number',
        'driver_name',
        'driver_phone',
        'status',
        'shipped_at',
        'delivered_at',
        'notes',
        'estimated_delivery_at',
        'estimated_delivery_min',
        'estimated_delivery_max',
        'estimated_delivery_duration',
        'eta_source',
        'eta_notes',
    ];

    protected function casts(): array
    {
        return [
            'shipped_at' => 'datetime',
            'delivered_at' => 'datetime',
            'estimated_delivery_at' => 'datetime',
            'estimated_delivery_min' => 'datetime',
            'estimated_delivery_max' => 'datetime',
        ];
    }

    public function getEtaLabelAttribute(): ?string
    {
        if ($this->estimated_delivery_min && $this->estimated_delivery_max) {
            $minStr = $this->estimated_delivery_min->format('d M');
            $maxStr = $this->estimated_delivery_max->format('d M Y');
            $durationStr = $this->estimated_delivery_duration ? " ({$this->estimated_delivery_duration})" : '';
            return "{$minStr} - {$maxStr}{$durationStr}";
        }

        if ($this->estimated_delivery_at) {
            $dateStr = $this->estimated_delivery_at->format('d M Y H:i');
            $durationStr = $this->estimated_delivery_duration ? " ({$this->estimated_delivery_duration})" : '';
            return "{$dateStr}{$durationStr}";
        }

        if ($this->estimated_delivery_duration) {
            return $this->estimated_delivery_duration;
        }

        return null;
    }

    public function getEtaSourceLabelAttribute(): string
    {
        return match($this->eta_source) {
            'biteship' => 'Vendor Ekspedisi (Biteship)',
            'store' => 'Ditentukan Toko (Kurir Toko)',
            'manual' => 'Ditentukan Manual',
            default => 'Belum Ditentukan',
        };
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

    public function deliveryLogs(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(DeliveryLog::class, 'delivery_id');
    }
}