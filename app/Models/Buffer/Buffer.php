<?php

namespace App\Models\Buffer;

use App\Models\Customer\Customer;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Buffer extends Model
{
    use HasUuids;

    protected $table = 'buffers';

    protected $fillable = [
        'customer_id',
        'session_id',
        'customer_name',
        'customer_email',
        'customer_phone',
        'subtotal',
        'tax',
        'discount',
        'total',
        'meta',
        'creator',
        'editor',
    ];

    protected function casts(): array
    {
        return [
            'subtotal' => 'decimal:2',
            'tax' => 'decimal:2',
            'discount' => 'decimal:2',
            'total' => 'decimal:2',
            'meta' => 'array',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id', 'id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(BufferItem::class, 'buffer_id', 'id');
    }
}
