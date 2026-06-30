<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            ['name' => 'Super Admin', 'slug' => 'super-admin', 'description' => 'Full system access', 'level' => 100, 'is_system' => true, 'is_active' => true],
            ['name' => 'Admin', 'slug' => 'admin', 'description' => 'Manage all operations', 'level' => 80, 'is_system' => true, 'is_active' => true],
            ['name' => 'Manager', 'slug' => 'manager', 'description' => 'Manage products & orders', 'level' => 50, 'is_system' => false, 'is_active' => true],
            ['name' => 'Staff', 'slug' => 'staff', 'description' => 'View & basic operations', 'level' => 20, 'is_system' => false, 'is_active' => true],
            ['name' => 'Viewer', 'slug' => 'viewer', 'description' => 'Read-only access', 'level' => 10, 'is_system' => false, 'is_active' => true],
        ];

        $now = now();
        foreach ($roles as $role) {
            DB::table('roles')->insert(array_merge($role, [
                'id'        => Str::uuid(),
                'guard_name' => 'web',
                'created_at'=> $now,
                'updated_at'=> $now,
            ]));
        }
    }
}
