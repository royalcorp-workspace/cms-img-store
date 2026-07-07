<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RoleHasPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        $admin = DB::table('roles')->where('slug', 'admin')->value('id');

        if (!$admin) {
            return;
        }

        $permMap = DB::table('permissions')->get()
            ->groupBy(fn($p) => $p->resource . '.' . $p->action)
            ->map(fn($group) => $group->pluck('id')->all());

        $now = now();

        $adminDeny = ['roles.delete', 'permissions.manage_button'];
        foreach ($permMap as $key => $ids) {
            if (!in_array($key, $adminDeny)) {
                foreach ($ids as $pid) {
                    DB::table('role_has_permissions')->updateOrInsert(
                        ['role_id' => $admin, 'permission_id' => $pid],
                        ['created_at' => $now]
                    );
                }
            }
        }
    }
}
