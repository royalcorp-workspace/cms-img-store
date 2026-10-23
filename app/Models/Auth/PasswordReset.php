<?php

namespace App\Models\Auth;

use App\Traits\HasSchemaTable;

use Illuminate\Database\Eloquent\Model;

class PasswordReset extends Model
{
    use HasSchemaTable;

    protected $table = 'password_resets';

    protected $fillable = [
        'email',
        'token',
        'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
        ];
    }
}
