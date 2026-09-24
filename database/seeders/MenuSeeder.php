<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Menu;
use App\Models\Permission;
use App\Models\Role;
use App\Models\Admin;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class MenuSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Full 3-Level Menu Definition (Module Group -> Submenu -> Action Permissions)
        $menus = [
            [
                'name' => 'Dashboard',
                'route' => 'dashboard',
                'additional' => 'dashboard',
                'order' => 1,
                'childs' => [
                    [
                        'name' => 'Overview Dashboard',
                        'route' => 'dashboard',
                        'additional' => 'dashboard',
                        'order' => 1,
                        'childs' => [
                            ['name' => 'View Dashboard', 'route' => 'dashboard', 'order' => 1],
                        ]
                    ]
                ]
            ],
            [
                'name' => 'Customers',
                'route' => 'customers.index',
                'additional' => 'person',
                'order' => 2,
                'childs' => [
                    [
                        'name' => 'Customer List',
                        'route' => 'customers.index',
                        'additional' => 'person',
                        'order' => 1,
                        'childs' => [
                            ['name' => 'Customer Index', 'route' => 'customers.index', 'order' => 1],
                            ['name' => 'Customer Detail', 'route' => 'customers.show', 'order' => 2],
                        ]
                    ]
                ]
            ],
            [
                'name' => 'Live Chat',
                'route' => 'chat.index',
                'additional' => 'forum',
                'order' => 3,
                'childs' => [
                    [
                        'name' => 'Live Chat Management',
                        'route' => 'chat.index',
                        'additional' => 'forum',
                        'order' => 1,
                        'childs' => [
                            ['name' => 'Chat Index', 'route' => 'chat.index', 'order' => 1],
                            ['name' => 'Send Message', 'route' => 'chat.send', 'order' => 2],
                            ['name' => 'Chat Messages', 'route' => 'chat.messages|chat.conversations', 'order' => 3],
                        ]
                    ]
                ]
            ],
            [
                'name' => 'Products',
                'route' => 'products',
                'additional' => 'inventory_2',
                'order' => 4,
                'childs' => [
                    [
                        'name' => 'Products',
                        'route' => 'products.index',
                        'additional' => 'inventory_2',
                        'order' => 1,
                        'childs' => [
                            ['name' => 'Product Index', 'route' => 'products.index', 'order' => 1],
                            ['name' => 'Product Create', 'route' => 'products.create|products.store', 'order' => 2],
                            ['name' => 'Product Edit', 'route' => 'products.edit|products.update', 'order' => 3],
                            ['name' => 'Product Detail', 'route' => 'products.show', 'order' => 4],
                            ['name' => 'Product Delete', 'route' => 'products.destroy', 'order' => 5],
                            ['name' => 'Product Export', 'route' => 'products.export', 'order' => 6],
                            ['name' => 'Product Import', 'route' => 'products.import.form|products.import.store|products.import.template', 'order' => 7],
                        ]
                    ],
                    [
                        'name' => 'Categories',
                        'route' => 'categories.index',
                        'additional' => 'category',
                        'order' => 2,
                        'childs' => [
                            ['name' => 'Category Index', 'route' => 'categories.index|categories.flat', 'order' => 1],
                            ['name' => 'Category Create', 'route' => 'categories.create|categories.store', 'order' => 2],
                            ['name' => 'Category Edit', 'route' => 'categories.edit|categories.update', 'order' => 3],
                            ['name' => 'Category Delete', 'route' => 'categories.destroy', 'order' => 4],
                        ]
                    ],
                    [
                        'name' => 'Brands',
                        'route' => 'brands.index',
                        'additional' => 'branding_watermark',
                        'order' => 3,
                        'childs' => [
                            ['name' => 'Brand Index', 'route' => 'brands.index', 'order' => 1],
                            ['name' => 'Brand Create', 'route' => 'brands.create|brands.store', 'order' => 2],
                            ['name' => 'Brand Edit', 'route' => 'brands.edit|brands.update', 'order' => 3],
                            ['name' => 'Brand Delete', 'route' => 'brands.destroy', 'order' => 4],
                        ]
                    ],
                    [
                        'name' => 'Product Suggestions',
                        'route' => 'product-suggestions.index',
                        'additional' => 'recommend',
                        'order' => 4,
                        'childs' => [
                            ['name' => 'Suggestion Index', 'route' => 'product-suggestions.index', 'order' => 1],
                            ['name' => 'Suggestion Edit', 'route' => 'product-suggestions.edit|product-suggestions.update', 'order' => 2],
                        ]
                    ]
                ]
            ],
            [
                'name' => 'Inventory',
                'route' => 'inventory',
                'additional' => 'warehouse',
                'order' => 5,
                'childs' => [
                    [
                        'name' => 'Stock & Inventory',
                        'route' => 'inventory.index',
                        'additional' => 'inventory',
                        'order' => 1,
                        'childs' => [
                            ['name' => 'Inventory Index', 'route' => 'inventory.index', 'order' => 1],
                            ['name' => 'Add Stock', 'route' => 'inventory.create|inventory.store', 'order' => 2],
                            ['name' => 'Quick Edit Stock', 'route' => 'inventory.quick-edit', 'order' => 3],
                            ['name' => 'Inventory Export', 'route' => 'inventory.export', 'order' => 4],
                            ['name' => 'Inventory Import', 'route' => 'inventory.import', 'order' => 5],
                        ]
                    ],
                    [
                        'name' => 'Warehouses',
                        'route' => 'warehouses.index',
                        'additional' => 'home_work',
                        'order' => 2,
                        'childs' => [
                            ['name' => 'Warehouse Index', 'route' => 'warehouses.index', 'order' => 1],
                            ['name' => 'Warehouse Create', 'route' => 'warehouses.create|warehouses.store', 'order' => 2],
                            ['name' => 'Warehouse Edit', 'route' => 'warehouses.edit|warehouses.update', 'order' => 3],
                            ['name' => 'Warehouse Delete', 'route' => 'warehouses.destroy', 'order' => 4],
                        ]
                    ]
                ]
            ],
            [
                'name' => 'Sales',
                'route' => 'orders',
                'additional' => 'shopping_cart',
                'order' => 6,
                'childs' => [
                    [
                        'name' => 'Orders',
                        'route' => 'orders.index',
                        'additional' => 'receipt_long',
                        'order' => 1,
                        'childs' => [
                            ['name' => 'Order Index', 'route' => 'orders.index', 'order' => 1],
                            ['name' => 'Order Detail', 'route' => 'orders.show', 'order' => 2],
                            ['name' => 'Order Tracking Resi', 'route' => 'orders.biteship.track|orders.waybill.history', 'order' => 3],
                            ['name' => 'Order Export', 'route' => 'orders.export', 'order' => 4],
                            ['name' => 'Void Orders', 'route' => 'orders.void.index|orders.void.show|orders.void.restore', 'order' => 5],
                        ]
                    ],
                    [
                        'name' => 'Settlements',
                        'route' => 'settlements.index',
                        'additional' => 'account_balance_wallet',
                        'order' => 2,
                        'childs' => [
                            ['name' => 'Settlement Index', 'route' => 'settlements.index', 'order' => 1],
                            ['name' => 'Settlement Detail', 'route' => 'settlements.show', 'order' => 2],
                        ]
                    ],
                    [
                        'name' => 'Reconciliation',
                        'route' => 'reconciliation.index',
                        'additional' => 'fact_check',
                        'order' => 3,
                        'childs' => [
                            ['name' => 'Reconciliation Index', 'route' => 'reconciliation.index', 'order' => 1],
                        ]
                    ]
                ]
            ],
            [
                'name' => 'Promotions',
                'route' => 'promotions',
                'additional' => 'local_offer',
                'order' => 7,
                'childs' => [
                    [
                        'name' => 'Vouchers',
                        'route' => 'vouchers.index',
                        'additional' => 'confirmation_number',
                        'order' => 1,
                        'childs' => [
                            ['name' => 'Voucher Index', 'route' => 'vouchers.index', 'order' => 1],
                            ['name' => 'Voucher Create', 'route' => 'vouchers.create|vouchers.store', 'order' => 2],
                            ['name' => 'Voucher Edit', 'route' => 'vouchers.edit|vouchers.update', 'order' => 3],
                            ['name' => 'Voucher Delete', 'route' => 'vouchers.destroy', 'order' => 4],
                        ]
                    ],
                    [
                        'name' => 'Price Settings',
                        'route' => 'price-settings.index',
                        'additional' => 'sell',
                        'order' => 2,
                        'childs' => [
                            ['name' => 'Price Settings Index', 'route' => 'price-settings.index', 'order' => 1],
                            ['name' => 'Price Settings Create', 'route' => 'price-settings.create|price-settings.store', 'order' => 2],
                            ['name' => 'Price Settings Edit', 'route' => 'price-settings.edit|price-settings.update', 'order' => 3],
                            ['name' => 'Price Settings Bulk', 'route' => 'price-settings.bulk|price-settings.bulk.store', 'order' => 4],
                        ]
                    ],
                    [
                        'name' => 'Store Price Settings',
                        'route' => 'price-product-setting-store.index',
                        'additional' => 'storefront',
                        'order' => 3,
                        'childs' => [
                            ['name' => 'Store Price Index', 'route' => 'price-product-setting-store.index', 'order' => 1],
                            ['name' => 'Store Price Create', 'route' => 'price-product-setting-store.create|price-product-setting-store.store', 'order' => 2],
                            ['name' => 'Store Price Edit', 'route' => 'price-product-setting-store.edit|price-product-setting-store.update', 'order' => 3],
                            ['name' => 'Store Price Delete', 'route' => 'price-product-setting-store.destroy', 'order' => 4],
                        ]
                    ],
                    [
                        'name' => 'Events & Popups',
                        'route' => 'events.index',
                        'additional' => 'celebration',
                        'order' => 4,
                        'childs' => [
                            ['name' => 'Event Index', 'route' => 'events.index', 'order' => 1],
                            ['name' => 'Event Create', 'route' => 'events.create|events.store', 'order' => 2],
                            ['name' => 'Event Edit', 'route' => 'events.edit|events.update', 'order' => 3],
                            ['name' => 'Event Delete', 'route' => 'events.destroy', 'order' => 4],
                        ]
                    ],
                    [
                        'name' => 'Product Bundling',
                        'route' => 'bundlings.index',
                        'additional' => 'shopping_bag',
                        'order' => 5,
                        'childs' => [
                            ['name' => 'Bundling Index', 'route' => 'bundlings.index', 'order' => 1],
                            ['name' => 'Bundling Create', 'route' => 'bundlings.create|bundlings.store', 'order' => 2],
                            ['name' => 'Bundling Edit', 'route' => 'bundlings.edit|bundlings.update', 'order' => 3],
                            ['name' => 'Bundling Delete', 'route' => 'bundlings.destroy', 'order' => 4],
                        ]
                    ]
                ]
            ],
            [
                'name' => 'Pick & Pack',
                'route' => 'picking-list',
                'additional' => 'package',
                'order' => 8,
                'childs' => [
                    [
                        'name' => 'Picking List',
                        'route' => 'picking-list.index',
                        'additional' => 'checklist',
                        'order' => 1,
                        'childs' => [
                            ['name' => 'Picking List Index', 'route' => 'picking-list.index', 'order' => 1],
                            ['name' => 'Picking List Detail', 'route' => 'picking-list.show|picking-list.update-item', 'order' => 2],
                        ]
                    ],
                    [
                        'name' => 'Packing Slip',
                        'route' => 'packing-slip.index',
                        'additional' => 'inventory',
                        'order' => 2,
                        'childs' => [
                            ['name' => 'Packing Slip Index', 'route' => 'packing-slip.index', 'order' => 1],
                            ['name' => 'Packing Slip Detail', 'route' => 'packing-slip.show', 'order' => 2],
                        ]
                    ],
                    [
                        'name' => 'Packing Out',
                        'route' => 'packing-out.index',
                        'additional' => 'outbox',
                        'order' => 3,
                        'childs' => [
                            ['name' => 'Packing Out Index', 'route' => 'packing-out.index', 'order' => 1],
                        ]
                    ],
                    [
                        'name' => 'Handover',
                        'route' => 'handover.index',
                        'additional' => 'handshake',
                        'order' => 4,
                        'childs' => [
                            ['name' => 'Handover Index', 'route' => 'handover.index', 'order' => 1],
                        ]
                    ],
                    [
                        'name' => 'Delivery',
                        'route' => 'delivery.index',
                        'additional' => 'local_shipping',
                        'order' => 5,
                        'childs' => [
                            ['name' => 'Delivery Index', 'route' => 'delivery.index', 'order' => 1],
                        ]
                    ]
                ]
            ],
            [
                'name' => 'Store Management',
                'route' => 'store-management',
                'additional' => 'store',
                'order' => 9,
                'childs' => [
                    [
                        'name' => 'Store Groups',
                        'route' => 'store-groups.index',
                        'additional' => 'storefront',
                        'order' => 1,
                        'childs' => [
                            ['name' => 'Store Groups Index', 'route' => 'store-groups.index', 'order' => 1],
                            ['name' => 'Store Groups Create', 'route' => 'store-groups.create|store-groups.store', 'order' => 2],
                            ['name' => 'Store Groups Edit', 'route' => 'store-groups.edit|store-groups.update', 'order' => 3],
                            ['name' => 'Store Groups Delete', 'route' => 'store-groups.destroy', 'order' => 4],
                        ]
                    ],
                    [
                        'name' => 'Stores',
                        'route' => 'stores.index',
                        'additional' => 'store',
                        'order' => 2,
                        'childs' => [
                            ['name' => 'Stores Index', 'route' => 'stores.index', 'order' => 1],
                            ['name' => 'Stores Create', 'route' => 'stores.create|stores.store', 'order' => 2],
                            ['name' => 'Stores Edit', 'route' => 'stores.edit|stores.update', 'order' => 3],
                            ['name' => 'Stores Delete', 'route' => 'stores.destroy', 'order' => 4],
                        ]
                    ],
                    [
                        'name' => 'Store Tiers',
                        'route' => 'store-tiers.index',
                        'additional' => 'layers',
                        'order' => 3,
                        'childs' => [
                            ['name' => 'Store Tiers Index', 'route' => 'store-tiers.index', 'order' => 1],
                            ['name' => 'Store Tiers Create', 'route' => 'store-tiers.create|store-tiers.store', 'order' => 2],
                            ['name' => 'Store Tiers Edit', 'route' => 'store-tiers.edit|store-tiers.update', 'order' => 3],
                            ['name' => 'Store Tiers Delete', 'route' => 'store-tiers.destroy', 'order' => 4],
                        ]
                    ],
                    [
                        'name' => 'Store Channels',
                        'route' => 'store-channels.index',
                        'additional' => 'alt_route',
                        'order' => 4,
                        'childs' => [
                            ['name' => 'Store Channels Index', 'route' => 'store-channels.index', 'order' => 1],
                            ['name' => 'Store Channels Create', 'route' => 'store-channels.create|store-channels.store', 'order' => 2],
                            ['name' => 'Store Channels Edit', 'route' => 'store-channels.edit|store-channels.update', 'order' => 3],
                            ['name' => 'Store Channels Delete', 'route' => 'store-channels.destroy', 'order' => 4],
                        ]
                    ],
                    [
                        'name' => 'Channel Stocks',
                        'route' => 'store-channel-stocks.index',
                        'additional' => 'inventory_2',
                        'order' => 5,
                        'childs' => [
                            ['name' => 'Channel Stocks Index', 'route' => 'store-channel-stocks.index', 'order' => 1],
                            ['name' => 'Channel Stocks Create', 'route' => 'store-channel-stocks.create|store-channel-stocks.store', 'order' => 2],
                            ['name' => 'Channel Stocks Edit', 'route' => 'store-channel-stocks.edit|store-channel-stocks.update', 'order' => 3],
                            ['name' => 'Channel Stocks Delete', 'route' => 'store-channel-stocks.destroy', 'order' => 4],
                        ]
                    ]
                ]
            ],
            [
                'name' => 'Shipping & Payment',
                'route' => 'shipping-payment',
                'additional' => 'payments',
                'order' => 10,
                'childs' => [
                    [
                        'name' => 'Couriers',
                        'route' => 'couriers.index',
                        'additional' => 'local_shipping',
                        'order' => 1,
                        'childs' => [
                            ['name' => 'Couriers Index', 'route' => 'couriers.index', 'order' => 1],
                            ['name' => 'Couriers Create', 'route' => 'couriers.create|couriers.store', 'order' => 2],
                            ['name' => 'Couriers Edit', 'route' => 'couriers.edit|couriers.update', 'order' => 3],
                            ['name' => 'Couriers Delete', 'route' => 'couriers.destroy', 'order' => 4],
                        ]
                    ],
                    [
                        'name' => 'Payment Methods',
                        'route' => 'payment-methods.index',
                        'additional' => 'credit_card',
                        'order' => 2,
                        'childs' => [
                            ['name' => 'Payment Methods Index', 'route' => 'payment-methods.index', 'order' => 1],
                            ['name' => 'Payment Methods Create', 'route' => 'payment-methods.create|payment-methods.store', 'order' => 2],
                            ['name' => 'Payment Methods Edit', 'route' => 'payment-methods.edit|payment-methods.update', 'order' => 3],
                            ['name' => 'Payment Methods Delete', 'route' => 'payment-methods.destroy', 'order' => 4],
                        ]
                    ],
                    [
                        'name' => 'Shipping Addresses',
                        'route' => 'shipping-addresses.index',
                        'additional' => 'pin_drop',
                        'order' => 3,
                        'childs' => [
                            ['name' => 'Shipping Addresses Index', 'route' => 'shipping-addresses.index', 'order' => 1],
                            ['name' => 'Shipping Addresses Create', 'route' => 'shipping-addresses.create|shipping-addresses.store', 'order' => 2],
                            ['name' => 'Shipping Addresses Edit', 'route' => 'shipping-addresses.edit|shipping-addresses.update', 'order' => 3],
                            ['name' => 'Shipping Addresses Delete', 'route' => 'shipping-addresses.destroy', 'order' => 4],
                        ]
                    ]
                ]
            ],
            [
                'name' => 'Content',
                'route' => 'content',
                'additional' => 'article',
                'order' => 11,
                'childs' => [
                    [
                        'name' => 'FAQ',
                        'route' => 'content.faq.index',
                        'additional' => 'help',
                        'order' => 1,
                        'childs' => [
                            ['name' => 'FAQ Index', 'route' => 'content.faq.index', 'order' => 1],
                            ['name' => 'FAQ Create', 'route' => 'content.faq.create|content.faq.store', 'order' => 2],
                            ['name' => 'FAQ Edit', 'route' => 'content.faq.edit|content.faq.update', 'order' => 3],
                            ['name' => 'FAQ Delete', 'route' => 'content.faq.destroy', 'order' => 4],
                        ]
                    ],
                    [
                        'name' => 'Banners',
                        'route' => 'content.banners.index',
                        'additional' => 'image',
                        'order' => 2,
                        'childs' => [
                            ['name' => 'Banners Index', 'route' => 'content.banners.index', 'order' => 1],
                            ['name' => 'Banners Create', 'route' => 'content.banners.create|content.banners.store', 'order' => 2],
                            ['name' => 'Banners Edit', 'route' => 'content.banners.edit|content.banners.update', 'order' => 3],
                            ['name' => 'Banners Delete', 'route' => 'content.banners.destroy', 'order' => 4],
                        ]
                    ],
                    [
                        'name' => 'Blog Posts',
                        'route' => 'content.blog.index',
                        'additional' => 'rss_feed',
                        'order' => 3,
                        'childs' => [
                            ['name' => 'Blog Index', 'route' => 'content.blog.index', 'order' => 1],
                            ['name' => 'Blog Create', 'route' => 'content.blog.create|content.blog.store', 'order' => 2],
                            ['name' => 'Blog Edit', 'route' => 'content.blog.edit|content.blog.update', 'order' => 3],
                            ['name' => 'Blog Delete', 'route' => 'content.blog.destroy', 'order' => 4],
                        ]
                    ],
                    [
                        'name' => 'Static Pages',
                        'route' => 'content.about.index',
                        'additional' => 'menu_book',
                        'order' => 4,
                        'childs' => [
                            ['name' => 'About Us', 'route' => 'content.about.index', 'order' => 1],
                            ['name' => 'Privacy Policy', 'route' => 'content.privacy.index', 'order' => 2],
                            ['name' => 'Terms & Conditions', 'route' => 'content.terms.index', 'order' => 3],
                            ['name' => 'How to Return', 'route' => 'content.return.index', 'order' => 4],
                        ]
                    ]
                ]
            ],
            [
                'name' => 'System',
                'route' => 'system',
                'additional' => 'settings',
                'order' => 12,
                'childs' => [
                    [
                        'name' => 'Roles & Hak Akses',
                        'route' => 'roles.index',
                        'additional' => 'admin_panel_settings',
                        'order' => 1,
                        'childs' => [
                            ['name' => 'Roles Index', 'route' => 'roles.index', 'order' => 1],
                            ['name' => 'Roles Create', 'route' => 'roles.create|roles.store', 'order' => 2],
                            ['name' => 'Roles Edit', 'route' => 'roles.edit|roles.update', 'order' => 3],
                            ['name' => 'Roles Show', 'route' => 'roles.show', 'order' => 4],
                            ['name' => 'Roles Delete', 'route' => 'roles.destroy', 'order' => 5],
                        ]
                    ],
                    [
                        'name' => 'Users',
                        'route' => 'users.index',
                        'additional' => 'group',
                        'order' => 2,
                        'childs' => [
                            ['name' => 'Users Index', 'route' => 'users.index', 'order' => 1],
                            ['name' => 'Users Create', 'route' => 'users.create|users.store', 'order' => 2],
                            ['name' => 'Users Edit', 'route' => 'users.edit|users.update', 'order' => 3],
                            ['name' => 'Users Delete', 'route' => 'users.destroy', 'order' => 4],
                        ]
                    ],
                    [
                        'name' => 'Permissions',
                        'route' => 'permissions.index',
                        'additional' => 'shield_person',
                        'order' => 3,
                        'childs' => [
                            ['name' => 'Permissions Index', 'route' => 'permissions.index', 'order' => 1],
                            ['name' => 'Permissions Sync', 'route' => 'permissions.sync', 'order' => 2],
                        ]
                    ]
                ]
            ]
        ];

        // 2. Clear old menus table cleanly
        DB::table('menus')->delete();

        // 3. Insert hierarchical menu tree
        foreach ($menus as $m) {
            $parentId = (string) Str::uuid();
            DB::table('menus')->insert([
                'id' => $parentId,
                'parent_id' => null,
                'title' => $m['name'],
                'icon' => $m['additional'] ?? null,
                'route_name' => $m['route'] ?? null,
                'url' => null,
                'permission' => $m['route'] ?? null,
                'order' => $m['order'] ?? 1,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            if (!empty($m['childs'])) {
                $this->insertChilds($m['childs'], $parentId);
            }
        }

        // 4. Ensure all routes in menu tree exist in `permissions` table
        $allPermissions = [];
        $menuRecords = Menu::all();
        foreach ($menuRecords as $menu) {
            if (!empty($menu->route_name)) {
                $routes = explode('|', $menu->route_name);
                foreach ($routes as $routeName) {
                    $routeName = trim($routeName);
                    if ($routeName && !str_starts_with($routeName, 'http')) {
                        $parts = explode('.', $routeName);
                        $resource = $parts[0] ?? 'general';
                        $action = $parts[1] ?? 'access';
                        $perm = Permission::firstOrCreate(
                            ['name' => $routeName],
                            [
                                'id' => (string) Str::uuid(),
                                'guard_name' => 'web',
                                'resource' => $resource,
                                'action' => $action,
                                'group' => Str::headline($resource),
                                'description' => "Hak akses untuk {$menu->title} ({$routeName})",
                                'is_active' => true,
                            ]
                        );
                        $allPermissions[] = $perm->id;
                    }
                }
            }
        }

        // 5. Ensure Super Admin / Admin Role exists with all permissions
        $role = Role::firstOrCreate(
            ['slug' => 'admin'],
            [
                'id' => (string) Str::uuid(),
                'name' => 'Super Admin',
                'guard_name' => 'web',
                'description' => 'Administrator dengan hak akses penuh ke seluruh modul sistem.',
                'level' => 100,
                'is_system' => true,
                'is_active' => true,
            ]
        );

        // Sync all permissions to the admin role
        $allPermIds = Permission::pluck('id')->all();
        $role->permissions()->sync($allPermIds);

        // 6. Assign role to Admin user
        $admin = Admin::first();
        if ($admin) {
            $admin->assignRole($role);
        }
    }

    private function insertChilds(array $childs, string $parentId): void
    {
        foreach ($childs as $c) {
            $childId = (string) Str::uuid();
            DB::table('menus')->insert([
                'id' => $childId,
                'parent_id' => $parentId,
                'title' => $c['name'],
                'icon' => $c['additional'] ?? null,
                'route_name' => $c['route'] ?? null,
                'url' => null,
                'permission' => $c['route'] ?? null,
                'order' => $c['order'] ?? 1,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            if (!empty($c['childs'])) {
                $this->insertChilds($c['childs'], $childId);
            }
        }
    }
}