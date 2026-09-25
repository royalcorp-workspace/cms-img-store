<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Menu;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class DisplayWebMenuSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Cari Menu Parent 'Products'
        $parentProductMenu = DB::table('menus')
            ->whereNull('parent_id')
            ->where(function ($q) {
                $q->where('title', 'Products')
                  ->orWhere('route_name', 'products');
            })
            ->first();

        if (!$parentProductMenu) {
            $parentId = (string) Str::uuid();
            DB::table('menus')->insert([
                'id' => $parentId,
                'parent_id' => null,
                'title' => 'Products',
                'icon' => 'inventory_2',
                'route_name' => 'products',
                'url' => null,
                'permission' => 'products',
                'order' => 4,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $parentProductId = $parentId;
        } else {
            $parentProductId = $parentProductMenu->id;
        }

        // 2. Cek apakah Submenu 'Display Web' sudah ada
        $displayWebMenu = DB::table('menus')
            ->where('parent_id', $parentProductId)
            ->where(function ($q) {
                $q->where('title', 'Display Web')
                  ->orWhere('route_name', 'products.display-web.index');
            })
            ->first();

        if (!$displayWebMenu) {
            $displayWebMenuId = (string) Str::uuid();
            DB::table('menus')->insert([
                'id' => $displayWebMenuId,
                'parent_id' => $parentProductId,
                'title' => 'Display Web',
                'icon' => 'dashboard_customize',
                'route_name' => 'products.display-web.index',
                'url' => null,
                'permission' => 'products.display-web.index',
                'order' => 5,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $this->command->info("Submenu 'Display Web' berhasil dibuat.");
        } else {
            $displayWebMenuId = $displayWebMenu->id;
            DB::table('menus')->where('id', $displayWebMenuId)->update([
                'title' => 'Display Web',
                'icon' => 'dashboard_customize',
                'route_name' => 'products.display-web.index',
                'permission' => 'products.display-web.index',
                'order' => 5,
                'is_active' => true,
                'updated_at' => now(),
            ]);
            $this->command->info("Submenu 'Display Web' sudah ada, atribut diperbarui.");
        }

        // 3. Child Actions / Permissions under Display Web
        $childActions = [
            ['name' => 'Display Web Index', 'route' => 'products.display-web.index', 'order' => 1],
            ['name' => 'Display Web Reorder', 'route' => 'products.display-web.reorder|products.display-web.reorder-suggestions', 'order' => 2],
            ['name' => 'Display Web Suggestions', 'route' => 'products.display-web.add-suggestion|products.display-web.remove-suggestion', 'order' => 3],
            ['name' => 'Display Web Visibility', 'route' => 'products.display-web.toggle-visibility', 'order' => 4],
        ];

        foreach ($childActions as $c) {
            $existingChild = DB::table('menus')
                ->where('parent_id', $displayWebMenuId)
                ->where('title', $c['name'])
                ->first();

            if (!$existingChild) {
                DB::table('menus')->insert([
                    'id' => (string) Str::uuid(),
                    'parent_id' => $displayWebMenuId,
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

        // 4. Buat / Pastikan Permissions ada di tabel `permissions`
        $permissionRoutes = [
            'products.display-web.index' => 'Hak akses melihat halaman Display Web',
            'products.display-web.reorder' => 'Hak akses mengubah urutan produk di Display Web',
            'products.display-web.reorder-suggestions' => 'Hak akses mengubah urutan produk saran di Display Web',
            'products.display-web.add-suggestion' => 'Hak akses menambahkan produk saran di Display Web',
            'products.display-web.remove-suggestion' => 'Hak akses menghapus produk saran di Display Web',
            'products.display-web.toggle-visibility' => 'Hak akses mengubah visibilitas tampil di web',
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
                    'resource' => 'products',
                    'action' => $action,
                    'group' => 'Products',
                    'description' => $permDesc,
                    'is_active' => true,
                ]
            );
            $permissionIds[] = $perm->id;
        }

        // 5. Berikan Hak Akses ke Seluruh Role Admin / Super Admin
        $roles = Role::whereIn('slug', ['admin', 'super-admin'])
            ->orWhere('name', 'ilike', '%admin%')
            ->get();

        foreach ($roles as $role) {
            $role->permissions()->syncWithoutDetaching($permissionIds);
            $this->command->info("Permissions Display Web berhasil disinkronkan ke role: {$role->name}");
        }

        $this->command->info("Seeder Display Web Menu berhasil selesai dijalankan!");
    }
}
