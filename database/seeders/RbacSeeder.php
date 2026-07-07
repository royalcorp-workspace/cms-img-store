<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class RbacSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        $roles = [
            ['name' => 'Admin', 'slug' => 'admin', 'description' => 'Manage all operations', 'level' => 80, 'is_system' => true, 'is_active' => true],
        ];

        $roleIds = [];
        foreach ($roles as $role) {
            $existingId = DB::table('roles')->where('slug', $role['slug'])->where('guard_name', 'web')->value('id');
            
            if ($existingId) {
                $roleIds[$role['slug']] = $existingId;
                DB::table('roles')->where('id', $existingId)->update(array_merge($role, [
                    'updated_at' => $now,
                ]));
            } else {
                $newId = (string) Str::uuid();
                DB::table('roles')->insert(array_merge($role, [
                    'id' => $newId,
                    'guard_name' => 'web',
                    'created_at' => $now,
                    'updated_at' => $now,
                ]));
                $roleIds[$role['slug']] = $newId;
            }
        }

        $adminId = $roleIds['admin'] ?? null;
        if (!$adminId) {
            return;
        }

        $permMap = DB::table('permissions')->get()
            ->groupBy(fn($p) => $p->resource . '.' . $p->action)
            ->map(fn($group) => $group->pluck('id')->all());

        $adminDeny = ['roles.delete', 'permissions.manage_button'];
        foreach ($permMap as $key => $ids) {
            if (!in_array($key, $adminDeny)) {
                foreach ($ids as $pid) {
                    DB::table('role_has_permissions')->updateOrInsert(
                        ['role_id' => $adminId, 'permission_id' => $pid],
                        ['created_at' => $now]
                    );
                }
            }
        }
    }
}
