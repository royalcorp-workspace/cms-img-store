<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class RoleHasPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        $superAdmin = DB::table('roles')->where('slug', 'super-admin')->value('id');
        $admin      = DB::table('roles')->where('slug', 'admin')->value('id');
        $manager    = DB::table('roles')->where('slug', 'manager')->value('id');
        $staff      = DB::table('roles')->where('slug', 'staff')->value('id');
        $viewer     = DB::table('roles')->where('slug', 'viewer')->value('id');

        $permMap = DB::table('permissions')->get()
            ->groupBy(fn($p) => $p->resource.'.'.$p->action)
            ->map(fn($group) => $group->pluck('id')->all());

        $now = now();

        // ── SUPER ADMIN: semua ──────────────────────────
        foreach ($permMap as $ids) {
            foreach ($ids as $pid) {
                $this->insert($superAdmin, $pid, $now);
            }
        }

        // ── ADMIN: semua kecuali delete_role & manage_permissions ──
        $adminDeny = ['roles.delete', 'permissions.manage_button'];
        foreach ($permMap as $key => $ids) {
            if (!in_array($key, $adminDeny)) {
                foreach ($ids as $pid) {
                    $this->insert($admin, $pid, $now);
                }
            }
        }

        // ── MANAGER ──────────────────────────────────────
        $managerPerms = [
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
            'profile.view', 'profile.edit', 'profile.save_button',
        ];
        foreach ($managerPerms as $key) {
            if (isset($permMap[$key])) {
                foreach ($permMap[$key] as $pid) {
                    $this->insert($manager, $pid, $now);
                }
            }
        }

        // ── STAFF ────────────────────────────────────────
        $staffPerms = [
            'dashboard.view',
            'products.view', 'products.create',
            'products.add_button',
            'categories.view',
            'inventory.view', 'inventory.create',
            'orders.view', 'orders.edit',
            'vouchers.view',
            'customers.view',
            'profile.view', 'profile.edit', 'profile.save_button',
        ];
        foreach ($staffPerms as $key) {
            if (isset($permMap[$key])) {
                foreach ($permMap[$key] as $pid) {
                    $this->insert($staff, $pid, $now);
                }
            }
        }

        // ── VIEWER ───────────────────────────────────────
        $viewerPerms = [
            'dashboard.view',
            'products.view',
            'categories.view',
            'inventory.view',
            'orders.view',
            'vouchers.view',
            'customers.view',
            'profile.view',
        ];
        foreach ($viewerPerms as $key) {
            if (isset($permMap[$key])) {
                foreach ($permMap[$key] as $pid) {
                    $this->insert($viewer, $pid, $now);
                }
            }
        }
    }

    private function insert(string $roleId, string $permId, string $now): void
    {
        DB::table('role_has_permissions')->insert([
            'permission_id' => $permId,
            'role_id'       => $roleId,
            'created_at'    => $now,
        ]);
    }
}
