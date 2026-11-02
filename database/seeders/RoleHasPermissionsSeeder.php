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

        $operationalRoles = [
            'store-partner' => DB::table('roles')->where('slug', 'store-partner')->value('id'),
            'sales-canvasser' => DB::table('roles')->where('slug', 'sales-canvasser')->value('id'),
        ];

        $operationalPermNames = [
            'dashboard',
            'orders', 'orders.index', 'orders.view', 'orders.show', 'orders.create', 'orders.edit',
            'orders.update-status', 'orders.update-resi', 'orders.verify-payment', 'orders.push-to-jde',
            'orders.waybill.history', 'orders.export', 'orders.print_invoice_button', 'orders.approve_button',
            'store-management', 'stores.index', 'stores.view',
            'products', 'products.index', 'products.view', 'products.edit', 'profile'
        ];

        $operationalPermissionIds = DB::table('permissions')
            ->whereIn('name', $operationalPermNames)
            ->where('name', 'not like', '%void%')
            ->where('name', 'not like', '%biteship%')
            ->where('action', 'not like', '%biteship%')
            ->pluck('id');

        foreach ($operationalRoles as $roleSlug => $roleId) {
            if ($roleId) {
                DB::table('role_has_permissions')->where('role_id', $roleId)->delete();

                foreach ($operationalPermissionIds as $pid) {
                    DB::table('role_has_permissions')->updateOrInsert(
                        ['role_id' => $roleId, 'permission_id' => $pid],
                        ['created_at' => $now]
                    );
                }
            }
        }
    }
}
