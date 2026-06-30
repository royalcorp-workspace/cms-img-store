<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Role extends Model
{
    use SoftDeletes;

    protected $table = 'roles';

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'name',
        'guard_name',
        'slug',
        'description',
        'level',
        'is_system',
        'is_active',
        'parent_id',
    ];

    protected function casts(): array
    {
        return [
            'level'     => 'integer',
            'is_system' => 'boolean',
            'is_active' => 'boolean',
            'deleted_at'=> 'datetime',
        ];
    }

    public function parent()
    {
        return $this->belongsTo(Role::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Role::class, 'parent_id');
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'model_has_roles', 'model_id', 'role_id');
    }

    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'role_has_permissions', 'role_id', 'permission_id');
    }
}
