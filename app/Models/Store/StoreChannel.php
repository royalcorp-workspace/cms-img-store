<?php

namespace App\Models\Store;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

use App\Models\Store\StoreChannelStock;

class StoreChannel extends Model
{
    protected $table = 'store_channel';

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'store_id',
        'store_channel_group_id',
        'code',
        'name',
        'description',
        'status',
        'sort_order',
        'creator',
        'editor',
        'deleted',
    ];

    protected function casts(): array
    {
        return [
            'status' => 'boolean',
            'sort_order' => 'integer',
            'deleted' => 'boolean',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class, 'store_id');
    }

    public function channelGroup(): BelongsTo
    {
        return $this->belongsTo(StoreChannelGroup::class, 'store_channel_group_id');
    }

    public function stocks(): HasMany
    {
        return $this->hasMany(StoreChannelStock::class, 'store_channel_id');
    }
}
