<?php

namespace App\Models\Auth;

use App\Traits\HasSchemaTable;

use Illuminate\Database\Eloquent\Model;

class EmailVerification extends Model
{
    use HasSchemaTable;

    protected $table = 'email_verifications';

    protected $fillable = [
        'user_id',
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
