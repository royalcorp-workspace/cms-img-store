<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Menu;
use Illuminate\Support\Str;

class MenuSeeder extends Seeder
{
    public function run(): void
    {
        $menus = [
            [
                'title' => 'General',
                'icon' => null,
                'route_name' => null,
                'url' => null,
                'permission' => null,
                'parent_id' => null,
                'order' => 1,
                'children' => [
                    ['title' => 'Dashboard', 'icon' => 'dashboard', 'route_name' => 'dashboard', 'permission' => 'dashboard.view', 'order' => 1],
                ]
            ],
            [
                'title' => 'Products',
                'icon' => null,
                'route_name' => null,
                'url' => null,
                'permission' => null,
                'parent_id' => null,
                'order' => 2,
                'children' => [
                    ['title' => 'Products', 'icon' => 'inventory_2', 'route_name' => 'products.index', 'permission' => 'products.view', 'order' => 1],
                    ['title' => 'Category', 'icon' => 'category', 'route_name' => 'categories.index', 'permission' => 'categories.view', 'order' => 2],
                    ['title' => 'Inventory', 'icon' => 'warehouse', 'route_name' => 'inventory.index', 'permission' => 'inventory.view', 'order' => 3],
                ]
            ],
            [
                'title' => 'Promotions',
                'icon' => null,
                'route_name' => null,
                'url' => null,
                'permission' => null,
                'parent_id' => null,
                'order' => 3,
                'children' => [
                    ['title' => 'Orders', 'icon' => 'shopping_cart', 'route_name' => 'orders.show', 'permission' => 'orders.view', 'order' => 1],
                    ['title' => 'Vouchers', 'icon' => 'local_offer', 'route_name' => 'vouchers.index', 'permission' => 'vouchers.view', 'order' => 2],
                    ['title' => 'Price Settings', 'icon' => 'local_offer', 'route_name' => 'price-settings.index', 'permission' => 'price-settings.view', 'order' => 3],
                ]
            ],
            [
                'title' => 'Customers',
                'icon' => null,
                'route_name' => null,
                'url' => null,
                'permission' => null,
                'parent_id' => null,
                'order' => 4,
                'children' => [
                    ['title' => 'Customers', 'icon' => 'person', 'route_name' => 'customers.index', 'permission' => 'customers.view', 'order' => 1],
                ]
            ],
            [
                'title' => 'System',
                'icon' => null,
                'route_name' => null,
                'url' => null,
                'permission' => null,
                'parent_id' => null,
                'order' => 5,
                'children' => [
                    ['title' => 'Roles', 'icon' => 'admin_panel_settings', 'route_name' => 'roles.index', 'permission' => 'roles.view', 'order' => 1],
                    ['title' => 'Permissions', 'icon' => 'shield_person', 'route_name' => 'permissions.index', 'permission' => 'permissions.view', 'order' => 2],
                    ['title' => 'Users', 'icon' => 'group', 'route_name' => 'users.index', 'permission' => 'users.view', 'order' => 3],
                    ['title' => 'Profile', 'icon' => 'person', 'route_name' => 'profile.show', 'permission' => 'profile.view', 'order' => 4],
                ]
            ],
        ];

        foreach ($menus as $menuData) {
            $children = $menuData['children'] ?? [];
            unset($menuData['children']);
            
            $parent = Menu::create(array_merge($menuData, ['id' => Str::uuid()]));
            
            foreach ($children as $child) {
                Menu::create(array_merge($child, [
                    'id' => Str::uuid(),
                    'parent_id' => $parent->id,
                ]));
            }
        }
    }
}