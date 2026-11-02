<?php

namespace Database\Seeders;

use App\Models\Admin;
use App\Models\Role;
use App\Models\Store\Store;
use App\Models\Store\SalesStore;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class SalesStoreHierarchySeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 1. Hubungkan Sales Canvasser ke Toko Binaan (store.sales_stores)
     * 2. Bangun User Tree Hierarki Komisi:
     *    Sales Canvasser (Pabrik) -> Owner Toko (Dealer) -> SPG Toko & SPG Internal
     */
    public function run(): void
    {
        DB::transaction(function () {
            $defaultPasswordHash = Hash::make('password123');
            $now = now();

            // 1. Ambil Akun Sales Canvasser (Budi Sales)
            $sales = Admin::where('username', 'sales01')->orWhere('email', 'sales.canvasser@royal.co.id')->first();
            if ($sales) {
                $sales->user_type = 'sales';
                $sales->role_persona = 'SALES_INTERNAL';
                $sales->parent_user_id = null;
                $sales->save();
            }

            // 2. Ambil Toko AYUK PURI dan TOKO JADI BARU
            $storeAyukPuri = Store::where('name', 'AYUK PURI')->orWhere('address_number', 107849)->first();
            $storeJadiBaru = Store::where('name', 'TOKO JADI BARU')->orWhere('address_number', 101178)->first();

            // 3. Masukkan ke store.sales_stores (Budi membina kedua toko ini)
            if ($sales && $storeAyukPuri) {
                SalesStore::updateOrCreate(
                    ['sales_user_id' => $sales->id, 'store_id' => $storeAyukPuri->id],
                    ['assigned_date' => $now->toDateString(), 'is_primary' => true]
                );
            }

            if ($sales && $storeJadiBaru) {
                SalesStore::updateOrCreate(
                    ['sales_user_id' => $sales->id, 'store_id' => $storeJadiBaru->id],
                    ['assigned_date' => $now->toDateString(), 'is_primary' => false]
                );
            }

            // 4. Update Owner Toko Ayuk Puri & Jadi Baru ke role_persona = 'OWNER_TOKO' dan parent_user_id = sales
            $ownerAyukPuri = Admin::where('username', 'toko_107849')->first();
            if ($ownerAyukPuri && $sales) {
                $ownerAyukPuri->role_persona = 'OWNER_TOKO';
                $ownerAyukPuri->parent_user_id = $sales->id;
                $ownerAyukPuri->is_owner = true;
                $ownerAyukPuri->save();
            }

            $ownerJadiBaru = Admin::where('username', 'toko_101178')->first();
            if ($ownerJadiBaru && $sales) {
                $ownerJadiBaru->role_persona = 'OWNER_TOKO';
                $ownerJadiBaru->parent_user_id = $sales->id;
                $ownerJadiBaru->is_owner = true;
                $ownerJadiBaru->save();
            }

            // 5. Pastikan Role 'Store Partner' ada untuk SPG
            $storePartnerRole = Role::where('slug', 'store-partner')->first();

            
            if ($storeAyukPuri && $ownerAyukPuri) {
                $spgToko = Admin::where('username', 'spg_puri01')->first();
                if (!$spgToko) {
                    $spgToko = new Admin();
                    $spgToko->id = (string) Str::uuid();
                    $spgToko->name = 'Siti - SPG Toko Ayuk Puri';
                    $spgToko->username = 'spg_puri01';
                    $spgToko->email = 'spg.puri01@royal.co.id';
                    $spgToko->phone = '081234567801';
                    $spgToko->user_type = 'store';
                    $spgToko->role_persona = 'SPG_TOKO';
                    $spgToko->store_id = $storeAyukPuri->id;
                    $spgToko->parent_user_id = $ownerAyukPuri->id;
                    $spgToko->password_hash = $defaultPasswordHash;
                    $spgToko->email_verified = true;
                    $spgToko->email_verified_at = $now;
                    $spgToko->is_active = true;
                    $spgToko->is_owner = false;
                    $spgToko->creator = 'SalesStoreHierarchySeeder';
                    $spgToko->save();
                } else {
                    $spgToko->store_id = $storeAyukPuri->id;
                    $spgToko->parent_user_id = $ownerAyukPuri->id;
                    $spgToko->role_persona = 'SPG_TOKO';
                    $spgToko->save();
                }

                if ($storePartnerRole && $spgToko) {
                    $spgToko->roles()->syncWithoutDetaching([
                        $storePartnerRole->id => ['model_type' => Admin::class],
                    ]);
                }
            }

           
            if ($storeAyukPuri && $sales) {
                $spgInternal = Admin::where('username', 'spg_royal01')->first();
                if (!$spgInternal) {
                    $spgInternal = new Admin();
                    $spgInternal->id = (string) Str::uuid();
                    $spgInternal->name = 'Dewi - Promotor Internal Royal';
                    $spgInternal->username = 'spg_royal01';
                    $spgInternal->email = 'spg.royal01@royal.co.id';
                    $spgInternal->phone = '081234567802';
                    $spgInternal->user_type = 'store';
                    $spgInternal->role_persona = 'SPG_INTERNAL';
                    $spgInternal->store_id = $storeAyukPuri->id;
                    $spgInternal->parent_user_id = $sales->id;
                    $spgInternal->password_hash = $defaultPasswordHash;
                    $spgInternal->email_verified = true;
                    $spgInternal->email_verified_at = $now;
                    $spgInternal->is_active = true;
                    $spgInternal->is_owner = false;
                    $spgInternal->creator = 'SalesStoreHierarchySeeder';
                    $spgInternal->save();
                } else {
                    $spgInternal->store_id = $storeAyukPuri->id;
                    $spgInternal->parent_user_id = $sales->id;
                    $spgInternal->role_persona = 'SPG_INTERNAL';
                    $spgInternal->save();
                }

                if ($storePartnerRole && $spgInternal) {
                    $spgInternal->roles()->syncWithoutDetaching([
                        $storePartnerRole->id => ['model_type' => Admin::class],
                    ]);
                }
            }
        });
    }
}
