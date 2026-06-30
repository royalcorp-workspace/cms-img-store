<?php

namespace App\Models\Auth;

use Illuminate\Database\Eloquent\Model;

class DeviceSession extends Model
{
    protected $table = 'device_sessions';

    protected $fillable = [
        'user_id',
        'device_name',
        'ip_address',
        'user_agent',
        'last_used_at',
    ];

    protected function casts(): array
    {
        return [
            'last_used_at' => 'datetime',
        ];
    }
}
