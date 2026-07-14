<?php

namespace App\Models\Store;

use App\Models\Product\Product;
use App\Models\Product\Variant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StoreChannelStock extends Model
{
    use HasUuids;

    protected $fillable = [
        'product_id',
        'product_variant_id',
        'store_id',
        'store_channel_id',
        'incoming',
        'booked',
        'on_order',
        'outgoing',
        'quantity',
        'creator',
        'editor',
        'deleted',
    ];

    protected function casts(): array
    {
        return [
            'incoming' => 'integer',
            'booked' => 'integer',
            'on_order' => 'integer',
            'outgoing' => 'integer',
            'quantity' => 'integer',
            'deleted' => 'boolean',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(Variant::class, 'product_variant_id');
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class, 'store_id');
    }

    public function channel(): BelongsTo
    {
        return $this->belongsTo(StoreChannel::class, 'store_channel_id');
    }
}
