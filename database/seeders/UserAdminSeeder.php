<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserAdminSeeder extends Seeder
{
    public function run(): void
    {
        $adminId = DB::table('user_admin')->where('email', 'admin@img.com')->value('id');

        if (!$adminId) {
            $adminId = (string) Str::uuid();
            DB::table('user_admin')->insert([
                'id'                => $adminId,
                'name'              => 'Admin User',
                'email'             => 'admin@img.com',
                'password_hash'     => Hash::make('password'),
                'email_verified'    => true,
                'email_verified_at' => now(),
                'created_at'        => now(),
                'updated_at'        => now(),
            ]);
        } else {
            DB::table('user_admin')->where('id', $adminId)->update([
                'password_hash'     => Hash::make('password'),
                'email_verified'    => true,
                'email_verified_at' => now(),
                'updated_at'        => now(),
            ]);
        }
    }
}
