<aside id="sidebar" class="fixed left-0 top-0 h-full w-sidebar-width bg-sidebar-bg flex flex-col py-6 z-50 transition-all duration-300">
    <div class="px-6 mb-6 flex items-center justify-between">
        <span class="font-headline-xl text-headline-xl font-bold text-sidebar-active tracking-tight">IMG</span>
    </div>
    <nav class="flex-1 min-h-0 overflow-y-auto sidebar-scroll">
        @php
        $menuGroups = [
            [
                'title' => 'Dashboard',
                'icon' => 'dashboard',
                'route_name' => 'dashboard',
                'children' => []
            ],
            [
                'title' => 'Customers',
                'icon' => 'person',
                'route_name' => 'customers.index',
                'children' => []
            ],
            [
                'title' => 'Products',
                'icon' => 'inventory_2',
                'children' => [
                    ['icon' => 'inventory_2', 'title' => 'Products', 'route_name' => 'products.index'],
                    ['icon' => 'category', 'title' => 'Category', 'route_name' => 'categories.index'],
                    ['icon' => 'warehouse', 'title' => 'Inventory', 'route_name' => 'inventory.index'],
                ]
            ],
            [
                'title' => 'Sales',
                'icon' => 'shopping_cart',
                'children' => [
                    ['icon' => 'shopping_cart', 'title' => 'Orders', 'route_name' => 'orders.index'],
                    ['icon' => 'account_balance', 'title' => 'Payment Reconciliation', 'route_name' => 'reconciliation.index'],
                ]
            ],
            [
                'title' => 'Promotions',
                'icon' => 'local_offer',
                'children' => [
                    ['icon' => 'local_offer', 'title' => 'Vouchers', 'route_name' => 'vouchers.index'],
                    ['icon' => 'local_offer', 'title' => 'Price Settings', 'route_name' => 'price-settings.index'],
                    ['icon' => 'storefront', 'title' => 'Store Pricing', 'route_name' => 'price-product-setting-store.index'],
                ]
            ],
            [
                'title' => 'Pick & Pack',
                'icon' => 'package',
                'children' => [
                    ['icon' => 'inventory', 'title' => 'Picking List', 'route_name' => 'picking-list.index'],
                    ['icon' => 'package', 'title' => 'Packing Slip', 'route_name' => 'packing-slip.index'],
                    ['icon' => 'output_circle', 'title' => 'Packing Out', 'route_name' => 'packing-out.index'],
                    ['icon' => 'handshake', 'title' => 'Handover', 'route_name' => 'handover.index'],
                    ['icon' => 'local_shipping', 'title' => 'Delivery', 'route_name' => 'delivery.index'],
                ]
            ],
            [
                'title' => 'Store Management',
                'icon' => 'store',
                'children' => [
                    ['icon' => 'account_tree', 'title' => 'Store Groups', 'route_name' => 'store-groups.index'],
                    ['icon' => 'layers', 'title' => 'Store Tiers', 'route_name' => 'store-tiers.index'],
                    ['icon' => 'store', 'title' => 'Stores', 'route_name' => 'stores.index'],
                    ['icon' => 'devices', 'title' => 'Channel Groups', 'route_name' => 'store-channel-groups.index'],
                    ['icon' => 'terminal', 'title' => 'Channels', 'route_name' => 'store-channels.index'],
                ]
            ],
            [
                'title' => 'Shipping & Payment',
                'icon' => 'payments',
                'children' => [
                    ['icon' => 'local_shipping', 'title' => 'Couriers', 'route_name' => 'couriers.index'],
                    ['icon' => 'pin_drop', 'title' => 'Shipping Address Rates', 'route_name' => 'shipping-addresses.index'],
                    ['icon' => 'account_balance_wallet', 'title' => 'Payment Methods', 'route_name' => 'payment-methods.index'],
                ]
            ],
            [
                'title' => 'Content',
                'icon' => 'article',
                'children' => [
                    ['icon' => 'help', 'title' => 'FAQ', 'route_name' => 'content.faq.index'],
                    ['icon' => 'article', 'title' => 'Blog', 'route_name' => 'content.blog.index'],
                    ['icon' => 'info', 'title' => 'About Us', 'route_name' => 'content.about.index'],
                    ['icon' => 'assignment_return', 'title' => 'How To Return', 'route_name' => 'content.how-to-return.index'],
                    ['icon' => 'gavel', 'title' => 'Terms & Conditions', 'route_name' => 'content.terms.index'],
                    ['icon' => 'privacy_tip', 'title' => 'Privacy Policy', 'route_name' => 'content.privacy.index'],
                    ['icon' => 'verified', 'title' => 'Warranty Claims', 'route_name' => 'content.warranty.index'],
                ]
            ],
            [
                'title' => 'System',
                'icon' => 'settings',
                'children' => [
                    ['icon' => 'admin_panel_settings', 'title' => 'Roles', 'route_name' => 'roles.index'],
                    ['icon' => 'shield_person', 'title' => 'Permissions', 'route_name' => 'permissions.index'],
                    ['icon' => 'group', 'title' => 'Users', 'route_name' => 'users.index'],
                    ['icon' => 'person', 'title' => 'Profile', 'route_name' => 'profile.show'],
                ]
            ],
        ];
        @endphp

        <div class="flex flex-col gap-1">
            @foreach($menuGroups as $menu)
                @if(empty($menu['children']))
                    @php
                        $isActive = request()->routeIs($menu['route_name'] . '*');
                    @endphp
                    <a class="sidebar-link flex items-center justify-start px-4 py-3 text-sidebar-text hover:bg-sidebar-active/10 hover:text-sidebar-active transition-colors duration-200 gap-3 {{ $isActive ? 'bg-primary-container text-sidebar-active' : '' }}" href="{{ route($menu['route_name']) }}">
                        <span class="material-symbols-outlined shrink-0">{{ $menu['icon'] }}</span>
                        <span class="sidebar-link-text font-label-md text-label-md whitespace-nowrap">{{ $menu['title'] }}</span>
                    </a>
                @else
                    @php
                        $hasActiveChild = false;
                        foreach($menu['children'] as $child) {
                            if(request()->routeIs($child['route_name'] . '*')) {
                                $hasActiveChild = true;
                                break;
                            }
                        }
                    @endphp
                    <div class="sidebar-group-container {{ $hasActiveChild ? 'is-open' : '' }} relative">
                        <button class="sidebar-group-header w-full flex items-center justify-between px-4 py-3 text-sidebar-text hover:bg-sidebar-active/10 hover:text-sidebar-active transition-colors duration-200 cursor-pointer {{ $hasActiveChild ? 'bg-white/5 text-sidebar-active' : '' }}">
                            <div class="flex items-center gap-3">
                                <span class="material-symbols-outlined shrink-0">{{ $menu['icon'] }}</span>
                                <span class="sidebar-link-text font-label-md text-label-md whitespace-nowrap">{{ $menu['title'] }}</span>
                            </div>
                            <span class="material-symbols-outlined text-[18px] transition-transform duration-200 sidebar-link-text chevron-icon shrink-0">expand_more</span>
                        </button>
                        <div class="sidebar-group-content transition-all duration-300 ease-in-out">
                            <div class="overflow-hidden">
                                <div class="py-1 pl-6 flex flex-col border-l border-white/10 ml-6 mt-1 mb-2 gap-1">
                                    @foreach($menu['children'] as $child)
                                        @php
                                            $isChildActive = request()->routeIs($child['route_name'] . '*');
                                        @endphp
                                        <a class="sidebar-link flex items-center justify-start py-2 px-3 text-sidebar-text hover:bg-sidebar-active/10 hover:text-sidebar-active rounded transition-colors duration-200 gap-2 {{ $isChildActive ? 'bg-primary-container/50 text-sidebar-active' : '' }}" href="{{ route($child['route_name']) }}">
                                            <span class="material-symbols-outlined shrink-0 text-[18px]">{{ $child['icon'] }}</span>
                                            <span class="sidebar-link-text font-label-md text-label-md whitespace-nowrap">{{ $child['title'] }}</span>
                                        </a>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            @endforeach
        </div>
    </nav>
    <div class="mt-auto px-4 pt-4 border-t border-white/10 sidebar-bottom">
        @php
            $admin = \Illuminate\Support\Facades\Auth::guard('admin')->user();
        @endphp
        <div class="flex items-center gap-3">
            @if($admin && $admin->photo_url)
                <img class="w-10 h-10 rounded-full object-cover border-2 border-primary-container shrink-0 sidebar-bottom-img" src="{{ $admin->photo_url }}" alt="{{ $admin?->name }}"/>
            @else
                <div class="w-10 h-10 rounded-full bg-primary-container flex items-center justify-center border-2 border-primary-container shrink-0 sidebar-bottom-img">
                    <span class="text-sidebar-active font-bold text-sm">{{ strtoupper(substr($admin?->name ?? 'A', 0, 1)) }}</span>
                </div>
            @endif
            <div class="sidebar-bottom-text">
                <p class="font-label-md text-label-md text-sidebar-active">{{ $admin?->name ?? 'Admin User' }}</p>
                <p class="text-[10px] font-label-sm text-sidebar-text uppercase">Admin</p>
            </div>
        </div>
    </div>
    <button id="sidebarToggle" class="sidebar-toggle-btn absolute -right-3 top-1/2 -translate-y-1/2 w-6 h-6 rounded-full bg-white shadow-md flex items-center justify-center text-sidebar-bg hover:bg-sidebar-active hover:text-white transition-colors duration-200 cursor-pointer p-0 border-0 hidden lg:flex z-[60]">
        <span id="sidebarToggleIcon" class="material-symbols-outlined text-base">chevron_left</span>
    </button>
</aside>

<style>
/* Accordion Grid animation styling */
.sidebar-group-content {
    display: grid;
    grid-template-rows: 0fr;
    transition: grid-template-rows 0.25s cubic-bezier(0.4, 0, 0.2, 1);
}
.sidebar-group-container.is-open .sidebar-group-content {
    grid-template-rows: 1fr;
}
.sidebar-group-container.is-open .chevron-icon {
    transform: rotate(180deg);
}

@media (min-width: 1024px) {
    aside#sidebar.sidebar-collapsed {
        width: 70px !important;
    }
    aside#sidebar.sidebar-collapsed ~ main {
        margin-left: 70px !important;
    }
    aside#sidebar.sidebar-collapsed .sidebar-group-title,
    aside#sidebar.sidebar-collapsed .sidebar-link-text,
    aside#sidebar.sidebar-collapsed .sidebar-bottom-text,
    aside#sidebar.sidebar-collapsed .chevron-icon {
        display: none !important;
    }
    aside#sidebar.sidebar-collapsed nav {
        padding: 0;
        overflow: visible !important;
    }
    aside#sidebar.sidebar-collapsed .sidebar-link,
    aside#sidebar.sidebar-collapsed .sidebar-group-header {
        justify-content: center;
        padding-left: 0;
        padding-right: 0;
        margin-left: 0;
        margin-right: 0;
        width: 100%;
        gap: 0 !important;
    }
    aside#sidebar.sidebar-collapsed .sidebar-group-header .flex {
        gap: 0 !important;
    }
    aside#sidebar.sidebar-collapsed .sidebar-bottom {
        display: flex;
        justify-content: center;
        padding: 0;
    }
    aside#sidebar.sidebar-collapsed .sidebar-bottom-img {
        margin: 0 auto;
    }
    aside#sidebar.sidebar-collapsed .sidebar-toggle-btn #sidebarToggleIcon {
        transform: rotate(180deg);
    }
    aside#sidebar.sidebar-collapsed .sidebar-toggle-btn {
        right: -8px;
    }

    /* Floating Popup menu for collapsed sidebar */
    aside#sidebar.sidebar-collapsed .sidebar-group-container {
        position: relative;
    }
    aside#sidebar.sidebar-collapsed .sidebar-group-content {
        display: block !important;
        position: absolute;
        left: 100%;
        top: 0;
        margin-left: 4px;
        width: 220px;
        background-color: var(--color-sidebar-bg);
        border-radius: 0.5rem;
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.3), 0 4px 6px -4px rgba(0, 0, 0, 0.3);
        border: 1px solid rgba(255, 255, 255, 0.1);
        max-height: none !important;
        opacity: 0;
        visibility: hidden;
        pointer-events: none;
        overflow: visible;
        transition: opacity 0.2s ease, visibility 0.2s ease, transform 0.2s ease;
        transform: translateX(10px);
        z-index: 100;
    }
    aside#sidebar.sidebar-collapsed .sidebar-group-container:hover .sidebar-group-content {
        opacity: 1;
        visibility: visible;
        pointer-events: auto;
        transform: translateX(0);
    }
    aside#sidebar.sidebar-collapsed .sidebar-group-content .overflow-hidden {
        overflow: visible !important;
    }
    aside#sidebar.sidebar-collapsed .sidebar-group-content .py-1 {
        padding-left: 0.75rem !important;
        padding-right: 0.75rem !important;
        margin-left: 0 !important;
        border-left: none !important;
        margin-top: 0 !important;
        margin-bottom: 0 !important;
    }
    aside#sidebar.sidebar-collapsed .sidebar-group-content .sidebar-link-text {
        display: inline !important;
    }
    aside#sidebar.sidebar-collapsed .sidebar-group-content .sidebar-link {
        justify-content: flex-start !important;
        width: auto !important;
        padding-left: 0.75rem !important;
        padding-right: 0.75rem !important;
        gap: 0.5rem !important;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const sidebar = document.getElementById('sidebar');
    const toggleBtn = document.getElementById('sidebarToggle');
    const toggleIcon = document.getElementById('sidebarToggleIcon');
    const storageKey = 'admin-sidebar-collapsed';

    function updateClasses() {
        if (window.innerWidth < 1024) {
            sidebar.classList.remove('sidebar-collapsed');
            localStorage.removeItem(storageKey);
            return;
        }

        if (localStorage.getItem(storageKey) === 'true') {
            sidebar.classList.add('sidebar-collapsed');
            if (toggleIcon) toggleIcon.textContent = 'chevron_right';
        } else {
            sidebar.classList.remove('sidebar-collapsed');
            if (toggleIcon) toggleIcon.textContent = 'chevron_left';
        }
    }

    if (toggleBtn) {
        toggleBtn.addEventListener('click', function() {
            if (window.innerWidth < 1024) return;
            const isCollapsed = localStorage.getItem(storageKey) === 'true';
            localStorage.setItem(storageKey, isCollapsed ? 'false' : 'true');
            updateClasses();
        });
    }

    window.addEventListener('resize', updateClasses);
    updateClasses();

    // Accordion Toggle for sidebar groups
    const groupHeaders = document.querySelectorAll('.sidebar-group-header');
    groupHeaders.forEach(header => {
        header.addEventListener('click', function(e) {
            // Only toggle accordion if sidebar is NOT collapsed
            if (sidebar.classList.contains('sidebar-collapsed')) {
                return;
            }
            
            const container = this.closest('.sidebar-group-container');
            const isOpen = container.classList.contains('is-open');
            
            // Close all other groups
            document.querySelectorAll('.sidebar-group-container').forEach(c => {
                if (c !== container) {
                    c.classList.remove('is-open');
                }
            });

            if (isOpen) {
                container.classList.remove('is-open');
            } else {
                container.classList.add('is-open');
            }
        });
    });
});
</script>
