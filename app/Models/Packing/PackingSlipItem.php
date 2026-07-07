<?php

namespace App\Models\Packing;

use App\Models\Order\OrderItem;
use App\Models\Product\Product;
use App\Models\Product\Variant;
use App\Models\Warehouse\WarehouseLocation;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PackingSlipItem extends Model
{
    use HasUuids;

    protected $fillable = ['packing_slip_id', 'order_item_id', 'product_id', 'product_variant_id', 'warehouse_location_id', 'quantity_ordered', 'quantity_packed', 'box_number'];

    protected function casts(): array
    {
        return [
            'quantity_ordered' => 'integer',
            'quantity_packed' => 'integer',
            'box_number' => 'integer',
        ];
    }

    public function packingSlip(): BelongsTo
    {
        return $this->belongsTo(PackingSlip::class, 'packing_slip_id');
    }

    public function orderItem(): BelongsTo
    {
        return $this->belongsTo(OrderItem::class, 'order_item_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(Variant::class, 'product_variant_id');
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(WarehouseLocation::class, 'warehouse_location_id');
    }
}