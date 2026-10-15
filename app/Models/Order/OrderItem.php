<?php

namespace App\Models\Order;

use App\Models\Order\Order;
use App\Models\Product\Product;
use App\Models\Product\Variant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    use HasUuids;

    protected $table = 'order_items';

    protected $fillable = [
        'order_id',
        'product_id',
        'product_variant_id',
        'name',
        'quantity',
        'unit_price',
        'total',
        'discount_nominal',
        'discount_percent',
        'item_notes',
        'meta',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'unit_price' => 'decimal:2',
            'total' => 'decimal:2',
            'discount_nominal' => 'decimal:2',
            'discount_percent' => 'decimal:2',
            'meta' => 'array',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'order_id', 'id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id', 'id');
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(Variant::class, 'product_variant_id', 'id');
    }

    public function pickingListItem(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(\App\Models\Picking\PickingListItem::class, 'order_item_id');
    }

    public function packingSlipItem(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(\App\Models\Packing\PackingSlipItem::class, 'order_item_id');
    }

    /**
     * Dapatkan 2nd_item_number untuk JDE (hanya ambil base SKU murni sebelum dash/suffix)
     * Contoh: KBR100010011348S200120-FS-T20 -> KBR100010011348S200120
     */
    public function getJdeItemNumber(): string
    {
        $raw = $this->variant?->sku
            ?? ($this->meta['sku'] ?? null)
            ?? $this->product?->code
            ?? $this->name;

        $clean = trim((string) $raw);
        if (str_contains($clean, '-')) {
            $clean = explode('-', $clean)[0];
        }

        return $clean;
    }
}
