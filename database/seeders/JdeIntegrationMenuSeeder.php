<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class JdeIntegrationMenuSeeder extends Seeder
{
    public function run(): void
    {
        $this->command?->info('=== Seeding Menu & Hak Akses Integrasi JDE ===');

        // 1. Pastikan Permission Terdaftar
        $permissions = [
            'admin.jde.index' => 'Hak akses melihat dashboard integrasi JDE dan tabel staging',
            'admin.jde.pull' => 'Hak akses menarik data JDE secara manual',
            'admin.jde.upload' => 'Hak akses upload file dump JSON JDE',
        ];

        $roleAdmin = DB::table('user_managements.roles')->where('name', 'Admin')->first();

        foreach ($permissions as $permName => $desc) {
            $perm = DB::table('user_managements.permissions')->where('name', $permName)->first();
            if (!$perm) {
                $permId = (string) Str::uuid();
                DB::table('user_managements.permissions')->insert([
                    'id' => $permId,
                    'name' => $permName,
                    'guard_name' => 'web',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            } else {
                $permId = $perm->id;
            }

            // Assign ke role Admin
            if ($roleAdmin) {
                DB::table('user_managements.role_has_permissions')->insertOrIgnore([
                    'permission_id' => $permId,
                    'role_id' => $roleAdmin->id,
                ]);
            }
        }

        // 2. Pastikan Parent Menu 'Master Data JDE' Ada
        $parentMenu = DB::table('menus')
            ->whereNull('parent_id')
            ->where(function ($q) {
                $q->where('title', 'Master Data JDE')
                  ->orWhere('title', 'Tarik JDE')
                  ->orWhere('title', 'Integrasi Oracle JDE ERP')
                  ->orWhere('title', 'Integrasi JDE')
                  ->orWhere('route_name', 'admin.jde.index');
            })
            ->first();

        if (!$parentMenu) {
            $parentId = (string) Str::uuid();
            DB::table('menus')->insert([
                'id' => $parentId,
                'parent_id' => null,
                'title' => 'Master Data JDE',
                'icon' => 'sync_alt',
                'route_name' => 'admin.jde.index',
                'url' => '/admin/jde',
                'permission' => 'admin.jde.index',
                'order' => 13,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $this->command?->info("Parent Menu 'Master Data JDE' berhasil dibuat.");
        } else {
            $parentId = $parentMenu->id;
            DB::table('menus')->where('id', $parentId)->update([
                'title' => 'Master Data JDE',
                'icon' => 'sync_alt',
                'route_name' => 'admin.jde.index',
                'url' => '/admin/jde',
                'permission' => 'admin.jde.index',
                'order' => 13,
                'is_active' => true,
                'updated_at' => now(),
            ]);
            $this->command?->info("Parent Menu 'Master Data JDE' diperbarui.");
        }

        // Bersihkan child lama yang redundan
        DB::table('menus')->where('parent_id', $parentId)->delete();

        // 3. Sub-Menus di dalam Master Data JDE (Hanya Item Master yang Aktif, lainnya di-hide)
        $subMenus = [
            [
                'title' => 'Item Master',
                'icon' => 'bed',
                'route_name' => 'admin.jde.index',
                'url' => '/admin/jde?tab=item_master',
                'order' => 1,
                'is_active' => true,
            ],
            [
                'title' => 'Base Price',
                'icon' => 'payments',
                'route_name' => 'admin.jde.index',
                'url' => '/admin/jde?tab=base_price',
                'order' => 2,
                'is_active' => false, // Di-hide / dinonaktifkan sementara sesuai kebutuhan fase 1
            ],
            [
                'title' => 'Cabang JDE',
                'icon' => 'factory',
                'route_name' => 'admin.jde.index',
                'url' => '/admin/jde?tab=branch',
                'order' => 3,
                'is_active' => false, // Di-hide / dinonaktifkan sementara sesuai kebutuhan fase 1
            ],
            [
                'title' => 'Toko Retail',
                'icon' => 'store',
                'route_name' => 'admin.jde.index',
                'url' => '/admin/jde?tab=customer',
                'order' => 4,
                'is_active' => false, // Di-hide / dinonaktifkan sementara sesuai kebutuhan fase 1
            ],
        ];

        foreach ($subMenus as $sub) {
            DB::table('menus')->insert([
                'id' => (string) Str::uuid(),
                'parent_id' => $parentId,
                'title' => $sub['title'],
                'icon' => $sub['icon'],
                'route_name' => $sub['route_name'],
                'url' => $sub['url'],
                'permission' => 'admin.jde.index',
                'order' => $sub['order'],
                'is_active' => $sub['is_active'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $statusStr = $sub['is_active'] ? 'AKTIF' : 'HIDE (Nonaktif)';
            $this->command?->info("Submenu '{$sub['title']}' [{$statusStr}] berhasil dibuat.");
        }

        $this->command?->info('=== Seeding Menu Integrasi JDE Selesai ===');
    }
}
