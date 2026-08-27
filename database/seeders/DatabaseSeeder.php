<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            PermissionSeeder::class,
            RoleHasPermissionsSeeder::class,
            MenuSeeder::class,
            HomepageSectionSeeder::class,
            // DashboardSeeder::class,
            UserAdminSeeder::class,
            LocationSeeder::class,
            PaymentMethodSeeder::class,
            CourierSeeder::class,
            MattressProductSeeder::class,
            ContentSeeder::class,
        ]);
    }
}
