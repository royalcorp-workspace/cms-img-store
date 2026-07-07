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

            // Store Pricing (Promo)
            ['name' => 'price-product-setting-store.view', 'guard_name' => $guard, 'resource' => 'price-product-setting-store', 'action' => 'view', 'group' => 'Promo', 'description' => 'View store pricing'],
            ['name' => 'price-product-setting-store.create', 'guard_name' => $guard, 'resource' => 'price-product-setting-store', 'action' => 'create', 'group' => 'Promo', 'description' => 'Create store pricing'],
            ['name' => 'price-product-setting-store.edit', 'guard_name' => $guard, 'resource' => 'price-product-setting-store', 'action' => 'edit', 'group' => 'Promo', 'description' => 'Edit store pricing'],
            ['name' => 'price-product-setting-store.delete', 'guard_name' => $guard, 'resource' => 'price-product-setting-store', 'action' => 'delete', 'group' => 'Promo', 'description' => 'Delete store pricing'],

            // Store Groups (Store Management)
            ['name' => 'store-groups.view', 'guard_name' => $guard, 'resource' => 'store-groups', 'action' => 'view', 'group' => 'Store Management', 'description' => 'View store groups'],
            ['name' => 'store-groups.create', 'guard_name' => $guard, 'resource' => 'store-groups', 'action' => 'create', 'group' => 'Store Management', 'description' => 'Create store group'],
            ['name' => 'store-groups.edit', 'guard_name' => $guard, 'resource' => 'store-groups', 'action' => 'edit', 'group' => 'Store Management', 'description' => 'Edit store group'],
            ['name' => 'store-groups.delete', 'guard_name' => $guard, 'resource' => 'store-groups', 'action' => 'delete', 'group' => 'Store Management', 'description' => 'Delete store group'],

            // Store Tiers (Store Management)
            ['name' => 'store-tiers.view', 'guard_name' => $guard, 'resource' => 'store-tiers', 'action' => 'view', 'group' => 'Store Management', 'description' => 'View store tiers'],
            ['name' => 'store-tiers.create', 'guard_name' => $guard, 'resource' => 'store-tiers', 'action' => 'create', 'group' => 'Store Management', 'description' => 'Create store tier'],
            ['name' => 'store-tiers.edit', 'guard_name' => $guard, 'resource' => 'store-tiers', 'action' => 'edit', 'group' => 'Store Management', 'description' => 'Edit store tier'],
            ['name' => 'store-tiers.delete', 'guard_name' => $guard, 'resource' => 'store-tiers', 'action' => 'delete', 'group' => 'Store Management', 'description' => 'Delete store tier'],

            // Stores (Store Management)
            ['name' => 'stores.view', 'guard_name' => $guard, 'resource' => 'stores', 'action' => 'view', 'group' => 'Store Management', 'description' => 'View stores'],
            ['name' => 'stores.create', 'guard_name' => $guard, 'resource' => 'stores', 'action' => 'create', 'group' => 'Store Management', 'description' => 'Create store'],
            ['name' => 'stores.edit', 'guard_name' => $guard, 'resource' => 'stores', 'action' => 'edit', 'group' => 'Store Management', 'description' => 'Edit store'],
            ['name' => 'stores.delete', 'guard_name' => $guard, 'resource' => 'stores', 'action' => 'delete', 'group' => 'Store Management', 'description' => 'Delete store'],

            // Store Channel Groups (Store Management)
            ['name' => 'store-channel-groups.view', 'guard_name' => $guard, 'resource' => 'store-channel-groups', 'action' => 'view', 'group' => 'Store Management', 'description' => 'View store channel groups'],
            ['name' => 'store-channel-groups.create', 'guard_name' => $guard, 'resource' => 'store-channel-groups', 'action' => 'create', 'group' => 'Store Management', 'description' => 'Create store channel group'],
            ['name' => 'store-channel-groups.edit', 'guard_name' => $guard, 'resource' => 'store-channel-groups', 'action' => 'edit', 'group' => 'Store Management', 'description' => 'Edit store channel group'],
            ['name' => 'store-channel-groups.delete', 'guard_name' => $guard, 'resource' => 'store-channel-groups', 'action' => 'delete', 'group' => 'Store Management', 'description' => 'Delete store channel group'],

            // Store Channels (Store Management)
            ['name' => 'store-channels.view', 'guard_name' => $guard, 'resource' => 'store-channels', 'action' => 'view', 'group' => 'Store Management', 'description' => 'View store channels'],
            ['name' => 'store-channels.create', 'guard_name' => $guard, 'resource' => 'store-channels', 'action' => 'create', 'group' => 'Store Management', 'description' => 'Create store channel'],
            ['name' => 'store-channels.edit', 'guard_name' => $guard, 'resource' => 'store-channels', 'action' => 'edit', 'group' => 'Store Management', 'description' => 'Edit store channel'],
            ['name' => 'store-channels.delete', 'guard_name' => $guard, 'resource' => 'store-channels', 'action' => 'delete', 'group' => 'Store Management', 'description' => 'Delete store channel'],

            // Content Management
            ['name' => 'content.faq.view', 'guard_name' => $guard, 'resource' => 'content.faq', 'action' => 'view', 'group' => 'Content', 'description' => 'View FAQ'],
            ['name' => 'content.faq.create', 'guard_name' => $guard, 'resource' => 'content.faq', 'action' => 'create', 'group' => 'Content', 'description' => 'Create FAQ'],
            ['name' => 'content.faq.edit', 'guard_name' => $guard, 'resource' => 'content.faq', 'action' => 'edit', 'group' => 'Content', 'description' => 'Edit FAQ'],
            ['name' => 'content.faq.delete', 'guard_name' => $guard, 'resource' => 'content.faq', 'action' => 'delete', 'group' => 'Content', 'description' => 'Delete FAQ'],

            ['name' => 'content.blog.view', 'guard_name' => $guard, 'resource' => 'content.blog', 'action' => 'view', 'group' => 'Content', 'description' => 'View blog'],
            ['name' => 'content.blog.create', 'guard_name' => $guard, 'resource' => 'content.blog', 'action' => 'create', 'group' => 'Content', 'description' => 'Create blog post'],
            ['name' => 'content.blog.edit', 'guard_name' => $guard, 'resource' => 'content.blog', 'action' => 'edit', 'group' => 'Content', 'description' => 'Edit blog post'],
            ['name' => 'content.blog.delete', 'guard_name' => $guard, 'resource' => 'content.blog', 'action' => 'delete', 'group' => 'Content', 'description' => 'Delete blog post'],

            ['name' => 'content.about.view', 'guard_name' => $guard, 'resource' => 'content.about', 'action' => 'view', 'group' => 'Content', 'description' => 'View About Us'],
            ['name' => 'content.about.create', 'guard_name' => $guard, 'resource' => 'content.about', 'action' => 'create', 'group' => 'Content', 'description' => 'Create About Us'],
            ['name' => 'content.about.edit', 'guard_name' => $guard, 'resource' => 'content.about', 'action' => 'edit', 'group' => 'Content', 'description' => 'Edit About Us'],
            ['name' => 'content.about.delete', 'guard_name' => $guard, 'resource' => 'content.about', 'action' => 'delete', 'group' => 'Content', 'description' => 'Delete About Us'],

            ['name' => 'content.how-to-return.view', 'guard_name' => $guard, 'resource' => 'content.how-to-return', 'action' => 'view', 'group' => 'Content', 'description' => 'View How To Return'],
            ['name' => 'content.how-to-return.create', 'guard_name' => $guard, 'resource' => 'content.how-to-return', 'action' => 'create', 'group' => 'Content', 'description' => 'Create How To Return'],
            ['name' => 'content.how-to-return.edit', 'guard_name' => $guard, 'resource' => 'content.how-to-return', 'action' => 'edit', 'group' => 'Content', 'description' => 'Edit How To Return'],
            ['name' => 'content.how-to-return.delete', 'guard_name' => $guard, 'resource' => 'content.how-to-return', 'action' => 'delete', 'group' => 'Content', 'description' => 'Delete How To Return'],

            ['name' => 'content.terms.view', 'guard_name' => $guard, 'resource' => 'content.terms', 'action' => 'view', 'group' => 'Content', 'description' => 'View Terms & Conditions'],
            ['name' => 'content.terms.create', 'guard_name' => $guard, 'resource' => 'content.terms', 'action' => 'create', 'group' => 'Content', 'description' => 'Create Terms & Conditions'],
            ['name' => 'content.terms.edit', 'guard_name' => $guard, 'resource' => 'content.terms', 'action' => 'edit', 'group' => 'Content', 'description' => 'Edit Terms & Conditions'],
            ['name' => 'content.terms.delete', 'guard_name' => $guard, 'resource' => 'content.terms', 'action' => 'delete', 'group' => 'Content', 'description' => 'Delete Terms & Conditions'],

            ['name' => 'content.privacy.view', 'guard_name' => $guard, 'resource' => 'content.privacy', 'action' => 'view', 'group' => 'Content', 'description' => 'View Privacy Policy'],
            ['name' => 'content.privacy.create', 'guard_name' => $guard, 'resource' => 'content.privacy', 'action' => 'create', 'group' => 'Content', 'description' => 'Create Privacy Policy'],
            ['name' => 'content.privacy.edit', 'guard_name' => $guard, 'resource' => 'content.privacy', 'action' => 'edit', 'group' => 'Content', 'description' => 'Edit Privacy Policy'],
            ['name' => 'content.privacy.delete', 'guard_name' => $guard, 'resource' => 'content.privacy', 'action' => 'delete', 'group' => 'Content', 'description' => 'Delete Privacy Policy'],

            ['name' => 'content.warranty.view', 'guard_name' => $guard, 'resource' => 'content.warranty', 'action' => 'view', 'group' => 'Content', 'description' => 'View Warranty Claims'],
            ['name' => 'content.warranty.create', 'guard_name' => $guard, 'resource' => 'content.warranty', 'action' => 'create', 'group' => 'Content', 'description' => 'Create Warranty Claim'],
            ['name' => 'content.warranty.edit', 'guard_name' => $guard, 'resource' => 'content.warranty', 'action' => 'edit', 'group' => 'Content', 'description' => 'Edit Warranty Claim'],
            ['name' => 'content.warranty.delete', 'guard_name' => $guard, 'resource' => 'content.warranty', 'action' => 'delete', 'group' => 'Content', 'description' => 'Delete Warranty Claim'],

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
