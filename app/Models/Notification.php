<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasUuids;

    protected $table = 'notifications';

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'id',
        'user_id',
        'title',
        'message',
        'type',
        'link_url',
        'is_read',
        'is_broadcast',
        'read_at',
        'starts_at',
        'ends_at',
    ];

    protected function casts(): array
    {
        return [
            'is_read' => 'boolean',
            'is_broadcast' => 'boolean',
            'read_at' => 'datetime',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }
}
