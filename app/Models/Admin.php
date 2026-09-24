<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Traits\HasRoles;
use Illuminate\Support\Str;

class Admin extends Authenticatable
{
    use Notifiable, HasRoles;

    protected $table = 'user_admin';

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'id',
        'user_id',
        'name',
        'email',
        'phone',
        'password_hash',
        'email_verified',
        'email_verified_at',
        'is_active',
        'creator',
        'editor',
        'deleted',
    ];

    protected $hidden = [
        'password_hash',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'email_verified'    => 'boolean',
            'is_active'         => 'boolean',
            'deleted'           => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function ($model) {
            if (empty($model->{$model->getKeyName()})) {
                $model->{$model->getKeyName()} = (string) Str::uuid();
            }
            if (!isset($model->is_active)) {
                $model->is_active = true;
            }
            if (!isset($model->deleted)) {
                $model->deleted = false;
            }
        });
    }

    public function getAuthPassword()
    {
        return $this->password_hash;
    }

    public function setPasswordAttribute($value)
    {
        if (!empty($value)) {
            $this->attributes['password_hash'] = bcrypt($value);
        }
    }
}
