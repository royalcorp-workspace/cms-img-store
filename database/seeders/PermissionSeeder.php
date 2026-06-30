<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        $guard = 'web';

        $permissions = [
            // Dashboard
            ['name' => 'dashboard.view', 'guard_name' => $guard, 'resource' => 'dashboard', 'action' => 'view', 'group' => 'Dashboard', 'description' => 'View dashboard'],

            // Products
            ['name' => 'products.view', 'guard_name' => $guard, 'resource' => 'products', 'action' => 'view', 'group' => 'Products', 'description' => 'View products'],
            ['name' => 'products.create', 'guard_name' => $guard, 'resource' => 'products', 'action' => 'create', 'group' => 'Products', 'description' => 'Create product'],
            ['name' => 'products.edit', 'guard_name' => $guard, 'resource' => 'products', 'action' => 'edit', 'group' => 'Products', 'description' => 'Edit product'],
            ['name' => 'products.delete', 'guard_name' => $guard, 'resource' => 'products', 'action' => 'delete', 'group' => 'Products', 'description' => 'Delete product'],
            ['name' => 'products.export', 'guard_name' => $guard, 'resource' => 'products', 'action' => 'export', 'group' => 'Products', 'description' => 'Export products'],

            // Button level
            ['name' => 'products.add_button', 'guard_name' => $guard, 'resource' => 'products', 'action' => 'add_button', 'group' => 'Products', 'description' => 'Show Add Product button'],
            ['name' => 'products.edit_button', 'guard_name' => $guard, 'resource' => 'products', 'action' => 'edit_button', 'group' => 'Products', 'description' => 'Show Edit Product button'],
            ['name' => 'products.delete_button', 'guard_name' => $guard, 'resource' => 'products', 'action' => 'delete_button', 'group' => 'Products', 'description' => 'Show Delete Product button'],

            // Categories
            ['name' => 'categories.view', 'guard_name' => $guard, 'resource' => 'categories', 'action' => 'view', 'group' => 'Categories', 'description' => 'View categories'],
            ['name' => 'categories.create', 'guard_name' => $guard, 'resource' => 'categories', 'action' => 'create', 'group' => 'Categories', 'description' => 'Create category'],
            ['name' => 'categories.edit', 'guard_name' => $guard, 'resource' => 'categories', 'action' => 'edit', 'group' => 'Categories', 'description' => 'Edit category'],
            ['name' => 'categories.delete', 'guard_name' => $guard, 'resource' => 'categories', 'action' => 'delete', 'group' => 'Categories', 'description' => 'Delete category'],

            // Inventory
            ['name' => 'inventory.view', 'guard_name' => $guard, 'resource' => 'inventory', 'action' => 'view', 'group' => 'Inventory', 'description' => 'View inventory'],
            ['name' => 'inventory.create', 'guard_name' => $guard, 'resource' => 'inventory', 'action' => 'create', 'group' => 'Inventory', 'description' => 'Add stock'],
            ['name' => 'inventory.adjust', 'guard_name' => $guard, 'resource' => 'inventory', 'action' => 'adjust', 'group' => 'Inventory', 'description' => 'Adjust stock'],

            // Orders
            ['name' => 'orders.view', 'guard_name' => $guard, 'resource' => 'orders', 'action' => 'view', 'group' => 'Orders', 'description' => 'View orders'],
            ['name' => 'orders.create', 'guard_name' => $guard, 'resource' => 'orders', 'action' => 'create', 'group' => 'Orders', 'description' => 'Create order'],
            ['name' => 'orders.edit', 'guard_name' => $guard, 'resource' => 'orders', 'action' => 'edit', 'group' => 'Orders', 'description' => 'Edit order'],
            ['name' => 'orders.delete', 'guard_name' => $guard, 'resource' => 'orders', 'action' => 'delete', 'group' => 'Orders', 'description' => 'Delete order'],

            // Button level orders
            ['name' => 'orders.approve_button', 'guard_name' => $guard, 'resource' => 'orders', 'action' => 'approve_button', 'group' => 'Orders', 'description' => 'Show Approve button'],
            ['name' => 'orders.cancel_button', 'guard_name' => $guard, 'resource' => 'orders', 'action' => 'cancel_button', 'group' => 'Orders', 'description' => 'Show Cancel button'],
            ['name' => 'orders.refund_button', 'guard_name' => $guard, 'resource' => 'orders', 'action' => 'refund_button', 'group' => 'Orders', 'description' => 'Show Refund button'],
            ['name' => 'orders.print_invoice_button', 'guard_name' => $guard, 'resource' => 'orders', 'action' => 'print_invoice_button', 'group' => 'Orders', 'description' => 'Show Print Invoice button'],

            // Vouchers
            ['name' => 'vouchers.view', 'guard_name' => $guard, 'resource' => 'vouchers', 'action' => 'view', 'group' => 'Vouchers', 'description' => 'View vouchers'],
            ['name' => 'vouchers.create', 'guard_name' => $guard, 'resource' => 'vouchers', 'action' => 'create', 'group' => 'Vouchers', 'description' => 'Create voucher'],
            ['name' => 'vouchers.edit', 'guard_name' => $guard, 'resource' => 'vouchers', 'action' => 'edit', 'group' => 'Vouchers', 'description' => 'Edit voucher'],
            ['name' => 'vouchers.delete', 'guard_name' => $guard, 'resource' => 'vouchers', 'action' => 'delete', 'group' => 'Vouchers', 'description' => 'Delete voucher'],
            ['name' => 'vouchers.generate_button', 'guard_name' => $guard, 'resource' => 'vouchers', 'action' => 'generate_button', 'group' => 'Vouchers', 'description' => 'Show Generate Voucher button'],

            // Customers
            ['name' => 'customers.view', 'guard_name' => $guard, 'resource' => 'customers', 'action' => 'view', 'group' => 'Customers', 'description' => 'View customers'],
            ['name' => 'customers.create', 'guard_name' => $guard, 'resource' => 'customers', 'action' => 'create', 'group' => 'Customers', 'description' => 'Create customer'],
            ['name' => 'customers.edit', 'guard_name' => $guard, 'resource' => 'customers', 'action' => 'edit', 'group' => 'Customers', 'description' => 'Edit customer'],
            ['name' => 'customers.delete', 'guard_name' => $guard, 'resource' => 'customers', 'action' => 'delete', 'group' => 'Customers', 'description' => 'Delete customer'],
            ['name' => 'customers.export_button', 'guard_name' => $guard, 'resource' => 'customers', 'action' => 'export_button', 'group' => 'Customers', 'description' => 'Show Export button'],

            // Roles (System)
            ['name' => 'roles.view', 'guard_name' => $guard, 'resource' => 'roles', 'action' => 'view', 'group' => 'System', 'description' => 'View roles'],
            ['name' => 'roles.create', 'guard_name' => $guard, 'resource' => 'roles', 'action' => 'create', 'group' => 'System', 'description' => 'Create role'],
            ['name' => 'roles.edit', 'guard_name' => $guard, 'resource' => 'roles', 'action' => 'edit', 'group' => 'System', 'description' => 'Edit role'],
            ['name' => 'roles.delete', 'guard_name' => $guard, 'resource' => 'roles', 'action' => 'delete', 'group' => 'System', 'description' => 'Delete role'],
            ['name' => 'roles.assign_button', 'guard_name' => $guard, 'resource' => 'roles', 'action' => 'assign_button', 'group' => 'System', 'description' => 'Show Assign Permission button'],

            // Permissions (System)
            ['name' => 'permissions.view', 'guard_name' => $guard, 'resource' => 'permissions', 'action' => 'view', 'group' => 'System', 'description' => 'View permissions'],
            ['name' => 'permissions.manage_button', 'guard_name' => $guard, 'resource' => 'permissions', 'action' => 'manage_button', 'group' => 'System', 'description' => 'Show Manage Permissions button'],

            // Users (System)
            ['name' => 'users.view', 'guard_name' => $guard, 'resource' => 'users', 'action' => 'view', 'group' => 'System', 'description' => 'View users'],
            ['name' => 'users.create', 'guard_name' => $guard, 'resource' => 'users', 'action' => 'create', 'group' => 'System', 'description' => 'Create user'],
            ['name' => 'users.edit', 'guard_name' => $guard, 'resource' => 'users', 'action' => 'edit', 'group' => 'System', 'description' => 'Edit user'],
            ['name' => 'users.delete', 'guard_name' => $guard, 'resource' => 'users', 'action' => 'delete', 'group' => 'System', 'description' => 'Delete user'],

            // Price Settings (Promo)
            ['name' => 'price-settings.view', 'guard_name' => $guard, 'resource' => 'price-settings', 'action' => 'view', 'group' => 'Promo', 'description' => 'View price settings'],
            ['name' => 'price-settings.create', 'guard_name' => $guard, 'resource' => 'price-settings', 'action' => 'create', 'group' => 'Promo', 'description' => 'Create price setting'],
            ['name' => 'price-settings.edit', 'guard_name' => $guard, 'resource' => 'price-settings', 'action' => 'edit', 'group' => 'Promo', 'description' => 'Edit price setting'],
            ['name' => 'price-settings.delete', 'guard_name' => $guard, 'resource' => 'price-settings', 'action' => 'delete', 'group' => 'Promo', 'description' => 'Delete price setting'],

            // Profile
            ['name' => 'profile.view', 'guard_name' => $guard, 'resource' => 'profile', 'action' => 'view', 'group' => 'Profile', 'description' => 'View profile'],
            ['name' => 'profile.edit', 'guard_name' => $guard, 'resource' => 'profile', 'action' => 'edit', 'group' => 'Profile', 'description' => 'Edit profile'],
            ['name' => 'profile.save_button', 'guard_name' => $guard, 'resource' => 'profile', 'action' => 'save_button', 'group' => 'Profile', 'description' => 'Show Save Profile button'],
        ];

        $now = now();
        foreach ($permissions as $perm) {
            DB::table('permissions')->insert(array_merge($perm, [
                'id'         => Str::uuid(),
                'is_active'  => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]));
        }
    }
}
