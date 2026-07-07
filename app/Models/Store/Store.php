<?php

namespace App\Models\Store;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Store extends Model
{
    protected $table = 'store';

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'store_group_id',
        'tier_id',
        'code',
        'name',
        'owner_user_id',
        'credit_limit',
        'outstanding_balance',
        'address',
        'phone',
        'email',
        'documents',
        'payment_term',
        'status',
        'sort_order',
        'creator',
        'editor',
        'deleted',
    ];

    protected function casts(): array
    {
        return [
            'credit_limit' => 'decimal:2',
            'outstanding_balance' => 'decimal:2',
            'documents' => 'array',
            'payment_term' => 'integer',
            'status' => 'boolean',
            'sort_order' => 'integer',
            'deleted' => 'boolean',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(StoreGroup::class, 'store_group_id');
    }

    public function tier(): BelongsTo
    {
        return $this->belongsTo(StoreTier::class, 'tier_id');
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Admin::class, 'owner_user_id');
    }

    public function channels(): HasMany
    {
        return $this->hasMany(StoreChannel::class, 'store_id');
    }
}
