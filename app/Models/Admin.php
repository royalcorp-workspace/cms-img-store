<?php

namespace App\Models;

use App\Traits\HasSchemaTable;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Traits\HasRoles;
use Illuminate\Support\Str;

class Admin extends Authenticatable
{
    use HasSchemaTable;

    use Notifiable, HasRoles;

    protected $table = 'store_users';

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'id',
        'user_id',
        'store_id',
        'parent_user_id',
        'name',
        'username',
        'email',
        'phone',
        'user_type',
        'role_persona',
        'sales_code',
        'password_hash',
        'email_verified',
        'email_verified_at',
        'is_active',
        'is_owner',
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
            'is_owner'          => 'boolean',
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

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function stores()
    {
        return $this->hasMany(\App\Models\Store\Store::class, 'owner_user_id');
    }

    public function store()
    {
        return $this->belongsTo(\App\Models\Store\Store::class, 'store_id');
    }

    public function ownedStore()
    {
        return $this->hasOne(\App\Models\Store\Store::class, 'owner_user_id');
    }

    public function getEffectiveStoreAttribute()
    {
        return $this->store ?? $this->ownedStore;
    }

    /**
     * Relasi User Tree (Atasan / Supervisor / Sales Pembina)
     */
    public function parent()
    {
        return $this->belongsTo(Admin::class, 'parent_user_id');
    }

    /**
     * Relasi User Tree (Bawahan / Mitra / Promotor Toko)
     */
    public function children()
    {
        return $this->hasMany(Admin::class, 'parent_user_id');
    }

    /**
     * Relasi penugasan toko untuk akun sales canvasser
     */
    public function salesStores()
    {
        return $this->hasMany(\App\Models\Store\SalesStore::class, 'sales_user_id');
    }

    /**
     * Toko-toko binaan sales canvasser
     */
    public function assignedStores()
    {
        return $this->belongsToMany(\App\Models\Store\Store::class, 'store.sales_stores', 'sales_user_id', 'store_id');
    }

    public function isSales(): bool
    {
        return $this->user_type === 'sales' || $this->role_persona === 'SALES_INTERNAL';
    }

    public function isStore(): bool
    {
        return $this->user_type === 'store' || in_array($this->role_persona, ['OWNER_TOKO', 'SPG_TOKO', 'SPG_INTERNAL'], true);
    }

    public function getAuthPassword()
    {
        return $this->password_hash;
    }

    public function setEmailAttribute($value): void
    {
        $this->attributes['email'] = !empty($value) ? strtolower(trim($value)) : $value;
    }

    public function setPasswordAttribute($value)
    {
        if (!empty($value)) {
            $this->attributes['password_hash'] = bcrypt($value);
        }
    }
}
