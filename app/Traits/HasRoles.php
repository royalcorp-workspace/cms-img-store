<?php

namespace App\Traits;

use App\Models\Role;
use App\Models\Permission;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

trait HasRoles
{
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'model_has_roles', 'model_id', 'role_id');
    }

    public function hasRole(string $roleSlug): bool
    {
        $normalized = strtolower(trim($roleSlug));
        return $this->roles()
            ->where(function ($q) use ($normalized, $roleSlug) {
                $q->where('slug', $roleSlug)
                  ->orWhere('name', $roleSlug)
                  ->orWhereRaw('LOWER(name) = ?', [$normalized])
                  ->orWhereRaw('LOWER(slug) = ?', [$normalized]);
            })
            ->where('is_active', true)
            ->exists();
    }

    public function hasAnyRole(array $roleSlugs): bool
    {
        foreach ($roleSlugs as $slug) {
            if ($this->hasRole($slug)) {
                return true;
            }
        }
        return false;
    }

    public function isSuperAdmin(): bool
    {
        return $this->hasRole('admin') 
            || $this->hasRole('super-admin') 
            || $this->hasRole('Super Admin')
            || $this->hasRole('Administrator');
    }

    public function hasPermission(string $permission): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        // Handle piped permissions (e.g. "products.create|products.store")
        if (str_contains($permission, '|')) {
            $perms = explode('|', $permission);
            foreach ($perms as $p) {
                if ($this->hasPermission(trim($p))) {
                    return true;
                }
            }
            return false;
        }

        $roleIds = $this->roles()->where('is_active', true)->pluck('roles.id');

        $hasRolePerm = DB::table('role_has_permissions')
            ->join('permissions', 'role_has_permissions.permission_id', '=', 'permissions.id')
            ->whereIn('role_has_permissions.role_id', $roleIds)
            ->where('permissions.name', $permission)
            ->where('permissions.is_active', true)
            ->exists();

        if ($hasRolePerm) {
            return true;
        }

        $hasUserPerm = DB::table('model_has_permissions')
            ->join('permissions', 'model_has_permissions.permission_id', '=', 'permissions.id')
            ->where('model_has_permissions.model_id', $this->id)
            ->where('permissions.name', $permission)
            ->where('permissions.is_active', true)
            ->exists();

        return $hasUserPerm;
    }

    public function hasAnyPermission(array $permissions): bool
    {
        foreach ($permissions as $perm) {
            if ($this->hasPermission($perm)) {
                return true;
            }
        }
        return false;
    }

    public function hasAllPermissions(array $permissions): bool
    {
        foreach ($permissions as $perm) {
            if (! $this->hasPermission($perm)) {
                return false;
            }
        }
        return true;
    }

    public function assignRole(string|Role $role): self
    {
        if (is_string($role)) {
            $roleModel = Role::where('name', $role)
                ->orWhere('slug', $role)
                ->orWhere('id', $role)
                ->first();
        } else {
            $roleModel = $role;
        }

        if ($roleModel && !$this->roles()->where('roles.id', $roleModel->id)->exists()) {
            $this->roles()->attach($roleModel->id, [
                'model_type' => get_class($this),
                'created_at' => now(),
            ]);
        }

        return $this;
    }

    public function removeRole(string|Role $role): self
    {
        if (is_string($role)) {
            $roleModel = Role::where('name', $role)
                ->orWhere('slug', $role)
                ->orWhere('id', $role)
                ->first();
        } else {
            $roleModel = $role;
        }

        if ($roleModel) {
            $this->roles()->detach($roleModel->id);
        }

        return $this;
    }

    public function syncRoles(array $roles): self
    {
        $roleIds = [];
        foreach ($roles as $r) {
            if (is_string($r)) {
                $roleModel = Role::where('name', $r)
                    ->orWhere('slug', $r)
                    ->orWhere('id', $r)
                    ->first();
                if ($roleModel) {
                    $roleIds[$roleModel->id] = [
                        'model_type' => get_class($this),
                        'created_at' => now(),
                    ];
                }
            } elseif ($r instanceof Role) {
                $roleIds[$r->id] = [
                    'model_type' => get_class($this),
                    'created_at' => now(),
                ];
            }
        }

        $this->roles()->sync($roleIds);

        return $this;
    }

    public function permissions()
    {
        return DB::table('permissions')
            ->join('role_has_permissions', 'permissions.id', '=', 'role_has_permissions.permission_id')
            ->join('model_has_roles', 'role_has_permissions.role_id', '=', 'model_has_roles.role_id')
            ->where('model_has_roles.model_id', $this->id)
            ->where('permissions.is_active', true)
            ->select('permissions.*');
    }

    public function directPermissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'model_has_permissions', 'model_id', 'permission_id');
    }

    public function getAllPermissions()
    {
        if ($this->isSuperAdmin()) {
            return Permission::where('is_active', true)->get();
        }

        $rolePermissions = Permission::whereIn('id', function ($query) {
            $query->select('permission_id')
                ->from('role_has_permissions')
                ->whereIn('role_id', $this->roles()->where('roles.is_active', true)->pluck('roles.id'));
        })->where('is_active', true)->get();

        $direct = $this->directPermissions()->where('is_active', true)->get();

        return $rolePermissions->merge($direct)->unique('id');
    }
}
