<?php

namespace Database\Seeders;

use App\Models\Admin;
use App\Models\Role;
use App\Models\Store\Store;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class StoreUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Seed store accounts, associate existing stores with owner_user_id,
     * and prepare Sales Canvasser operational accounts.
     */
    public function run(): void
    {
        DB::transaction(function () {
            $now = now();
            $defaultPasswordHash = Hash::make('password123');

            // 1. Pastikan Role 'Store Partner' dan 'Sales Canvasser' tersedia
            $roles = [
                [
                    'name'        => 'Store Partner',
                    'slug'        => 'store-partner',
                    'description' => 'Owner dan Kasir Toko Mebel Mitra (Dealer)',
                    'level'       => 50,
                    'is_system'   => true,
                    'is_active'   => true,
                ],
                [
                    'name'        => 'Sales Canvasser',
                    'slug'        => 'sales-canvasser',
                    'description' => 'Sales Lapangan / Canvasser Pembina Toko Cabang',
                    'level'       => 60,
                    'is_system'   => true,
                    'is_active'   => true,
                ],
            ];

            $roleMap = [];
            foreach ($roles as $roleData) {
                $role = Role::where('slug', $roleData['slug'])->first();
                if (!$role) {
                    $role = new Role();
                    $role->id = (string) Str::uuid();
                    $role->guard_name = 'web';
                    $role->name = $roleData['name'];
                    $role->slug = $roleData['slug'];
                    $role->description = $roleData['description'];
                    $role->level = $roleData['level'];
                    $role->is_system = $roleData['is_system'];
                    $role->is_active = $roleData['is_active'];
                    $role->save();
                }
                $roleMap[$roleData['slug']] = $role->id;
            }

            // 2. Berikan izin akses modul Pesanan (Orders) ke role 'Store Partner' di RBAC
            // CATATAN: Void Orders & Biteship Tracking HANYA berlaku untuk Online Retail (tidak diberikan ke Toko/Dealer)
            $storePartnerRoleId = $roleMap['store-partner'] ?? null;
            if ($storePartnerRoleId) {
                // Bersihkan permissions lama untuk store partner agar fresh
                DB::table('role_has_permissions')->where('role_id', $storePartnerRoleId)->delete();

                $orderPermissionIds = DB::table('permissions')
                    ->where(function ($q) {
                        $q->where('resource', 'orders')
                          ->orWhere('name', 'like', 'orders%')
                          ->orWhere('name', 'profile');
                    })
                    ->where('name', 'not like', '%void%')
                    ->where('name', 'not like', '%biteship%')
                    ->where('action', 'not like', '%biteship%')
                    ->pluck('id');

                foreach ($orderPermissionIds as $permId) {
                    DB::table('role_has_permissions')->updateOrInsert(
                        ['role_id' => $storePartnerRoleId, 'permission_id' => $permId],
                        ['created_at' => $now]
                    );
                }
            }

            // 3. Buat / Hubungkan Akun Admin untuk seluruh Toko Mebel yang memiliki Address Number JDE
            $stores = Store::where('deleted', false)
                ->where('status', true)
                ->whereNotNull('address_number')
                ->get();

            $storePartnerRoleId = $roleMap['store-partner'] ?? null;

            foreach ($stores as $store) {
                $an8 = (int) $store->address_number;
                if ($an8 <= 0) {
                    continue;
                }

                // Tentukan email unik berdasarkan AN8 atau email toko yang valid
                $email = "toko.jde{$an8}@royal.co.id";
                if (!empty($store->email) && $store->email !== '-' && filter_var($store->email, FILTER_VALIDATE_EMAIL)) {
                    $email = strtolower(trim($store->email));
                }

                $username = "toko_{$an8}";

                $admin = Admin::where('email', $email)->first();
                if (!$admin && !empty($store->owner_user_id)) {
                    $admin = Admin::find($store->owner_user_id);
                }

                if (!$admin) {
                    $admin = new Admin();
                    $admin->id = (string) Str::uuid();
                    $admin->store_id = $store->id;
                    $admin->name = "Store - {$store->name}";
                    $admin->username = $username;
                    $admin->email = $email;
                    $admin->user_type = 'store';
                    $admin->phone = (!empty($store->phone) && $store->phone !== '-') ? $store->phone : null;
                    $admin->password_hash = $defaultPasswordHash;
                    $admin->email_verified = true;
                    $admin->email_verified_at = $now;
                    $admin->is_active = true;
                    $admin->is_owner = true;
                    $admin->deleted = false;
                    $admin->creator = 'StoreUserSeeder';
                    $admin->save();
                } else {
                    $admin->store_id = $store->id;
                    $admin->name = "Store - {$store->name}";
                    $admin->username = $admin->username ?: $username;
                    $admin->user_type = 'store';
                    $admin->password_hash = $defaultPasswordHash;
                    $admin->is_active = true;
                    $admin->is_owner = true;
                    $admin->deleted = false;
                    $admin->save();
                }

                // Pasangkan role Store Partner
                if ($storePartnerRoleId) {
                    $admin->roles()->syncWithoutDetaching([
                        $storePartnerRoleId => ['model_type' => Admin::class],
                    ]);
                }

                // Update owner_user_id toko
                $store->owner_user_id = $admin->id;
                if (empty($store->email) || $store->email === '-') {
                    $store->email = $email;
                }
                $store->save();
            }

            // 4. Siapkan Akun Sales Canvasser (untuk mode asistensi toko binaan)
            $salesEmail = 'sales.canvasser@royal.co.id';
            $salesAdmin = Admin::where('email', $salesEmail)->first();
            if (!$salesAdmin) {
                $salesAdmin = new Admin();
                $salesAdmin->id = (string) Str::uuid();
                $salesAdmin->name = 'Budi Sales Canvasser';
                $salesAdmin->username = 'sales01';
                $salesAdmin->email = $salesEmail;
                $salesAdmin->user_type = 'sales';
                $salesAdmin->sales_code = 'SC001';
                $salesAdmin->phone = '081198765432';
                $salesAdmin->password_hash = $defaultPasswordHash;
                $salesAdmin->email_verified = true;
                $salesAdmin->email_verified_at = $now;
                $salesAdmin->is_active = true;
                $salesAdmin->is_owner = false;
                $salesAdmin->deleted = false;
                $salesAdmin->creator = 'StoreUserSeeder';
                $salesAdmin->save();
            } else {
                $salesAdmin->user_type = 'sales';
                $salesAdmin->sales_code = 'SC001';
                $salesAdmin->username = 'sales01';
                $salesAdmin->save();
            }

            $salesRoleId = $roleMap['sales-canvasser'] ?? null;
            if ($salesRoleId && $salesAdmin) {
                $salesAdmin->roles()->syncWithoutDetaching([
                    $salesRoleId => ['model_type' => Admin::class],
                ]);
            }
        });
    }
}
