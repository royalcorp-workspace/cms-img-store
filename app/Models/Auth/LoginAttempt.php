<?php

namespace App\Models\Auth;

use Illuminate\Database\Eloquent\Model;

class LoginAttempt extends Model
{
    protected $table = 'login_attempts';

    protected $fillable = [
        'email',
        'ip_address',
        'user_agent',
        'success',
    ];

    protected function casts(): array
    {
        return [
            'success' => 'boolean',
        ];
    }
}
