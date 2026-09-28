<?php

namespace Database\Seeders;

use App\Models\Customer\CustomerGroup;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CustomerGroupMenuSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Cari Menu Parent 'Customers'
        $parentCustomerMenu = DB::table('menus')
            ->whereNull('parent_id')
            ->where(function ($q) {
                $q->where('title', 'Customers')
                  ->orWhere('title', 'ilike', '%customer%')
                  ->orWhere('route_name', 'customers');
            })
            ->first();

        if (!$parentCustomerMenu) {
            $parentId = (string) Str::uuid();
            DB::table('menus')->insert([
                'id' => $parentId,
                'parent_id' => null,
                'title' => 'Customers',
                'icon' => 'group',
                'route_name' => 'customers.index',
                'url' => null,
                'permission' => 'customers.index',
                'order' => 2,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $parentCustomerId = $parentId;
        } else {
            $parentCustomerId = $parentCustomerMenu->id;
        }

        // 2. Buat / Pastikan Submenu 'Customer Groups' ada di bawah parent Customers
        $customerGroupMenu = DB::table('menus')
            ->where('parent_id', $parentCustomerId)
            ->where(function ($q) {
                $q->where('title', 'Customer Groups')
                  ->orWhere('route_name', 'customer-groups.index');
            })
            ->first();

        if (!$customerGroupMenu) {
            $cgMenuId = (string) Str::uuid();
            $maxOrder = DB::table('menus')->where('parent_id', $parentCustomerId)->max('order') ?? 1;
            DB::table('menus')->insert([
                'id' => $cgMenuId,
                'parent_id' => $parentCustomerId,
                'title' => 'Customer Groups',
                'icon' => 'group',
                'route_name' => 'customer-groups.index',
                'url' => null,
                'permission' => 'customer-groups.index',
                'order' => $maxOrder + 1,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } else {
            $cgMenuId = $customerGroupMenu->id;
            DB::table('menus')->where('id', $cgMenuId)->update([
                'parent_id' => $parentCustomerId,
                'title' => 'Customer Groups',
                'icon' => 'group',
                'route_name' => 'customer-groups.index',
                'permission' => 'customer-groups.index',
                'is_active' => true,
                'updated_at' => now(),
            ]);
        }

        // 3. Child Actions / Permissions under Customer Groups
        $childActions = [
            ['name' => 'Customer Groups Index', 'route' => 'customer-groups.index', 'order' => 1],
            ['name' => 'Customer Groups Create', 'route' => 'customer-groups.create|customer-groups.store', 'order' => 2],
            ['name' => 'Customer Groups Edit', 'route' => 'customer-groups.edit|customer-groups.update', 'order' => 3],
            ['name' => 'Customer Groups Delete', 'route' => 'customer-groups.destroy', 'order' => 4],
        ];

        foreach ($childActions as $c) {
            $existingChild = DB::table('menus')
                ->where('parent_id', $cgMenuId)
                ->where('title', $c['name'])
                ->first();

            if (!$existingChild) {
                DB::table('menus')->insert([
                    'id' => (string) Str::uuid(),
                    'parent_id' => $cgMenuId,
                    'title' => $c['name'],
                    'icon' => null,
                    'route_name' => $c['route'],
                    'url' => null,
                    'permission' => $c['route'],
                    'order' => $c['order'],
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        // 4. Permissions di tabel `permissions`
        $permissionRoutes = [
            'customer-groups.index' => 'Hak akses melihat daftar Customer Groups',
            'customer-groups.create' => 'Hak akses form tambah Customer Group',
            'customer-groups.store' => 'Hak akses menyimpan Customer Group baru',
            'customer-groups.edit' => 'Hak akses form edit Customer Group',
            'customer-groups.update' => 'Hak akses memperbarui Customer Group',
            'customer-groups.destroy' => 'Hak akses menghapus Customer Group',
        ];

        $permissionIds = [];
        foreach ($permissionRoutes as $permName => $permDesc) {
            $parts = explode('.', $permName);
            $action = end($parts);
            $perm = Permission::firstOrCreate(
                ['name' => $permName],
                [
                    'id' => (string) Str::uuid(),
                    'guard_name' => 'web',
                    'resource' => 'customer-groups',
                    'action' => $action,
                    'group' => 'Customers',
                    'description' => $permDesc,
                    'is_active' => true,
                ]
            );
            $permissionIds[] = $perm->id;
        }

        // 5. Berikan permissions ke role admin & super-admin
        $roles = Role::whereIn('slug', ['admin', 'super-admin'])
            ->orWhere('name', 'ilike', '%admin%')
            ->get();

        foreach ($roles as $role) {
            $role->permissions()->syncWithoutDetaching($permissionIds);
        }

        // 6. Buat sample groups awal jika belum ada
        CustomerGroup::firstOrCreate(
            ['slug' => 'karyawan'],
            [
                'name' => 'Karyawan',
                'description' => 'Group customer khusus karyawan internal toko (Diskon 20%)',
                'discount_percent' => 20.00,
                'is_active' => true,
            ]
        );

        CustomerGroup::firstOrCreate(
            ['slug' => 'reseller'],
            [
                'name' => 'Reseller',
                'description' => 'Group customer khusus reseller / mitra resmi (Diskon 25%)',
                'discount_percent' => 25.00,
                'is_active' => true,
            ]
        );
    }
}
