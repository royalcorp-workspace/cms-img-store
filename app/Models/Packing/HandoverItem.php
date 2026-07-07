<?php

namespace App\Models\Packing;

use App\Models\Order\OrderItem;
use App\Models\Product\Product;
use App\Models\Product\Variant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HandoverItem extends Model
{
    use HasUuids;

    protected $table = 'hand_overs_items';

    protected $fillable = ['handover_id', 'order_item_id', 'product_id', 'product_variant_id', 'quantity_ordered', 'quantity_handed_over', 'warehouse_location_id', 'status', 'notes'];

    protected function casts(): array
    {
        return [
            'quantity_ordered' => 'integer',
            'quantity_handed_over' => 'integer',
        ];
    }

    public function handover(): BelongsTo
    {
        return $this->belongsTo(Handover::class, 'handover_id');
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
        return $this->belongsTo(\App\Models\Warehouse\WarehouseLocation::class, 'warehouse_location_id');
    }
}
