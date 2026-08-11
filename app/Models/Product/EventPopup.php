<?php

namespace App\Models\Product;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EventPopup extends Model
{
    use HasUuids;

    protected $table = 'event_popups';

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'event_id',
        'title',
        'image_url',
        'link_url',
        'button_text',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class, 'event_id');
    }
}
