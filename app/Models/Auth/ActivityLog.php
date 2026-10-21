<?php

namespace App\Models\Auth;

use App\Traits\HasSchemaTable;

use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    use HasSchemaTable;

    protected $table = 'activity_logs';

    protected $fillable = [
        'user_id',
        'action',
        'ip_address',
        'user_agent',
        'payload',
    ];

    protected function casts(): array
    {
        return [
            'payload' => 'array',
        ];
    }
}
