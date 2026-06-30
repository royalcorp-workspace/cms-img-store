<?php

namespace App\Traits;

use Illuminate\Support\Facades\Gate;

trait HasRoles
{
    public function hasRole(string $roleSlug): bool
    {
        return $this->roles()
            ->where('slug', $roleSlug)
            ->where('is_active', true)
            ->exists();
    }

    public function hasAnyRole(array $roleSlugs): bool
    {
        return $this->roles()
            ->whereIn('slug', $roleSlugs)
            ->where('is_active', true)
            ->exists();
    }

    public function hasPermission(string $permission): bool
    {
        if ($this->hasRole('super-admin')) {
            return true;
        }

        $roleIds = $this->roles()->where('is_active', true)->pluck('roles.id');

        $hasRolePerm = \DB::table('role_has_permissions')
            ->join('permissions', 'role_has_permissions.permission_id', '=', 'permissions.id')
            ->whereIn('role_has_permissions.role_id', $roleIds)
            ->where('permissions.name', $permission)
            ->where('permissions.is_active', true)
            ->exists();

        $hasUserPerm = \DB::table('model_has_permissions')
            ->join('permissions', 'model_has_permissions.permission_id', '=', 'permissions.id')
            ->where('model_has_permissions.model_id', $this->id)
            ->where('permissions.name', $permission)
            ->where('permissions.is_active', true)
            ->exists();

        return $hasRolePerm || $hasUserPerm;
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

    public function roles()
    {
        return $this->belongsToMany(Role::class, 'model_has_roles', 'model_id', 'role_id');
    }

    public function permissions()
    {
        return \DB::table('permissions')
            ->join('role_has_permissions', 'permissions.id', '=', 'role_has_permissions.permission_id')
            ->join('model_has_roles', 'role_has_permissions.role_id', '=', 'model_has_roles.role_id')
            ->where('model_has_roles.model_id', $this->id)
            ->where('permissions.is_active', true)
            ->select('permissions.*');
    }

    public function directPermissions()
    {
        return $this->belongsToMany(Permission::class, 'model_has_permissions', 'model_id', 'permission_id');
    }
}
