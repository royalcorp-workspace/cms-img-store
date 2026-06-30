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
            ['name' => 'Super Admin', 'slug' => 'super-admin', 'description' => 'Full system access', 'level' => 100, 'is_system' => true, 'is_active' => true],
            ['name' => 'Admin', 'slug' => 'admin', 'description' => 'Manage all operations', 'level' => 80, 'is_system' => true, 'is_active' => true],
            ['name' => 'Manager', 'slug' => 'manager', 'description' => 'Manage products & orders', 'level' => 50, 'is_system' => false, 'is_active' => true],
            ['name' => 'Staff', 'slug' => 'staff', 'description' => 'View & basic operations', 'level' => 20, 'is_system' => false, 'is_active' => true],
            ['name' => 'Viewer', 'slug' => 'viewer', 'description' => 'Read-only access', 'level' => 10, 'is_system' => false, 'is_active' => true],
        ];

        $roleIds = [];
        foreach ($roles as $role) {
            $roleIds[$role['slug']] = DB::table('roles')->insertGetId(array_merge($role, [
                'id' => Str::uuid(),
                'guard_name' => 'web',
                'created_at' => $now,
                'updated_at' => $now,
            ]));
        }

        $permMap = DB::table('permissions')->get()
            ->groupBy(fn($p) => $p->resource . '.' . $p->action)
            ->map(fn($group) => $group->pluck('id')->all());

        $insert = function ($roleId, array $permKeys) use ($permMap, $now) {
            foreach ($permKeys as $key) {
                if (isset($permMap[$key])) {
                    foreach ($permMap[$key] as $pid) {
                        DB::table('role_has_permissions')->updateOrInsert(
                            ['role_id' => $roleId, 'permission_id' => $pid],
                            ['created_at' => $now, 'updated_at' => $now]
                        );
                    }
                }
            }
        };

        $insert($roleIds['super-admin'], $permMap->keys()->toArray());
        $insert($roleIds['admin'], $this->adminPermissions());
        $insert($roleIds['manager'], $this->managerPermissions());
        $insert($roleIds['staff'], $this->staffPermissions());
        $insert($roleIds['viewer'], $this->viewerPermissions());
    }

    private function adminPermissions(): array
    {
        return [
            'dashboard.view',
            'products.view', 'products.create', 'products.edit', 'products.delete', 'products.export',
            'products.add_button', 'products.edit_button', 'products.delete_button',
            'categories.view', 'categories.create', 'categories.edit', 'categories.delete',
            'inventory.view', 'inventory.create', 'inventory.adjust',
            'orders.view', 'orders.create', 'orders.edit', 'orders.delete',
            'orders.approve_button', 'orders.cancel_button', 'orders.refund_button', 'orders.print_invoice_button',
            'vouchers.view', 'vouchers.create', 'vouchers.edit', 'vouchers.delete',
            'vouchers.generate_button',
            'customers.view', 'customers.create', 'customers.edit', 'customers.delete',
            'customers.export_button',
            'price-settings.view', 'price-settings.create', 'price-settings.edit', 'price-settings.delete',
            'roles.view', 'roles.create', 'roles.edit', 'roles.delete',
            'roles.assign_button',
            'permissions.view',
            'permissions.manage_button',
            'users.view', 'users.create', 'users.edit', 'users.delete',
            'profile.view', 'profile.edit',
            'profile.save_button',
        ];
    }

    private function managerPermissions(): array
    {
        return [
            'dashboard.view',
            'products.view', 'products.create', 'products.edit', 'products.export',
            'products.add_button', 'products.edit_button',
            'categories.view', 'categories.create', 'categories.edit',
            'inventory.view', 'inventory.create', 'inventory.adjust',
            'orders.view', 'orders.edit',
            'orders.approve_button', 'orders.cancel_button', 'orders.print_invoice_button',
            'vouchers.view', 'vouchers.create',
            'vouchers.generate_button',
            'price-settings.view', 'price-settings.create', 'price-settings.edit',
            'customers.view', 'customers.create', 'customers.edit',
            'customers.export_button',
            'profile.view', 'profile.edit',
            'profile.save_button',
        ];
    }

    private function staffPermissions(): array
    {
        return [
            'dashboard.view',
            'products.view', 'products.create',
            'products.add_button',
            'categories.view',
            'inventory.view', 'inventory.create',
            'orders.view', 'orders.edit',
            'vouchers.view',
            'customers.view',
            'price-settings.view',
            'profile.view', 'profile.edit',
            'profile.save_button',
        ];
    }

    private function viewerPermissions(): array
    {
        return [
            'dashboard.view',
            'products.view',
            'categories.view',
            'inventory.view',
            'orders.view',
            'vouchers.view',
            'customers.view',
            'price-settings.view',
            'profile.view',
        ];
    }
}
