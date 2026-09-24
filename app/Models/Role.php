<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Role extends Model
{
    use SoftDeletes;

    protected $table = 'roles';

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'id',
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

    protected static function booted(): void
    {
        static::creating(function ($model) {
            if (empty($model->{$model->getKeyName()})) {
                $model->{$model->getKeyName()} = (string) Str::uuid();
            }
            if (empty($model->guard_name)) {
                $model->guard_name = 'web';
            }
            if (empty($model->slug) && !empty($model->name)) {
                $model->slug = Str::slug($model->name);
            }
            if (!isset($model->level)) {
                $model->level = 10;
            }
            if (!isset($model->is_active)) {
                $model->is_active = true;
            }
            if (!isset($model->is_system)) {
                $model->is_system = false;
            }
        });
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
        return $this->belongsToMany(User::class, 'model_has_roles', 'role_id', 'model_id');
    }

    public function admins(): BelongsToMany
    {
        return $this->belongsToMany(Admin::class, 'model_has_roles', 'role_id', 'model_id');
    }

    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'role_has_permissions', 'role_id', 'permission_id');
    }

    /**
     * Sync permissions with array of names, IDs, or permission objects.
     */
    public function syncPermissions(array|\Traversable $permissions): self
    {
        $permissionIds = [];

        foreach ($permissions as $permission) {
            if (empty($permission)) {
                continue;
            }

            if (is_string($permission)) {
                // Check if already a UUID
                if (Str::isUuid($permission)) {
                    $found = Permission::find($permission);
                    if ($found) {
                        $permissionIds[] = $found->id;
                        continue;
                    }
                }

                // Check by name
                $found = Permission::where('name', $permission)->first();
                if ($found) {
                    $permissionIds[] = $found->id;
                } else {
                    // Create if not exists
                    $created = Permission::create([
                        'name' => $permission,
                        'guard_name' => $this->guard_name ?: 'web',
                        'is_active' => true,
                        'description' => "Permission for {$permission}",
                    ]);
                    $permissionIds[] = $created->id;
                }
            } elseif ($permission instanceof Permission) {
                $permissionIds[] = $permission->id;
            } elseif (is_object($permission) && isset($permission->id)) {
                $permissionIds[] = $permission->id;
            }
        }

        $this->permissions()->sync(array_unique($permissionIds));

        return $this;
    }

    public function givePermissionTo(string|Permission $permission): self
    {
        if (is_string($permission)) {
            $permission = Permission::firstOrCreate(
                ['name' => $permission],
                ['guard_name' => $this->guard_name ?: 'web', 'is_active' => true]
            );
        }

        if ($permission && !$this->permissions()->where('permissions.id', $permission->id)->exists()) {
            $this->permissions()->attach($permission->id);
        }

        return $this;
    }

    public function revokePermissionTo(string|Permission $permission): self
    {
        if (is_string($permission)) {
            $permission = Permission::where('name', $permission)->first();
        }

        if ($permission) {
            $this->permissions()->detach($permission->id);
        }

        return $this;
    }

    public function hasPermissionTo(string $permission): bool
    {
        return $this->permissions()->where('permissions.name', $permission)->exists();
    }
}
