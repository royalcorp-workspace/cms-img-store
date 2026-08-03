<?php

namespace App\Models\Buffer;

use App\Models\Product\Product;
use App\Models\Product\Variant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BufferItem extends Model
{
    use HasUuids;

    protected $table = 'buffer_items';

    protected $fillable = [
        'buffer_id',
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

    public function buffer(): BelongsTo
    {
        return $this->belongsTo(Buffer::class, 'buffer_id', 'id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id', 'id');
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(Variant::class, 'product_variant_id', 'id');
    }
}
