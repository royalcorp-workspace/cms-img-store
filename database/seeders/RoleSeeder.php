<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        $roles = [
            ['name' => 'Admin', 'slug' => 'admin', 'description' => 'Manage all operations', 'level' => 80, 'is_system' => true, 'is_active' => true],
        ];

        foreach ($roles as $role) {
            DB::table('roles')->updateOrInsert(
                ['slug' => $role['slug'], 'guard_name' => 'web'],
                array_merge($role, [
                    'id'        => Str::uuid(),
                    'guard_name' => 'web',
                    'created_at'=> $now,
                    'updated_at'=> $now,
                ])
            );
        }
    }
}
