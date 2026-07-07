<aside id="sidebar" class="fixed left-0 top-0 h-full w-sidebar-width bg-sidebar-bg flex flex-col py-6 z-50 transition-all duration-300">
    <div class="px-6 mb-6 flex items-center justify-between">
        <span class="font-headline-xl text-headline-xl font-bold text-sidebar-active tracking-tight">IMG</span>
    </div>
    <nav class="flex-1 min-h-0 overflow-y-auto sidebar-scroll">
        @php
        $menuGroups = [
            ['title' => 'General', 'order' => 1, 'children' => [
                ['icon' => 'dashboard', 'title' => 'Dashboard', 'route_name' => 'dashboard'],
            ]],
            ['title' => 'Customers', 'order' => 2, 'children' => [
                ['icon' => 'person', 'title' => 'Customers', 'route_name' => 'customers.index'],
            ]],
            ['title' => 'Products', 'order' => 3, 'children' => [
                ['icon' => 'inventory_2', 'title' => 'Products', 'route_name' => 'products.index'],
                ['icon' => 'category', 'title' => 'Category', 'route_name' => 'categories.index'],
                ['icon' => 'warehouse', 'title' => 'Inventory', 'route_name' => 'inventory.index'],
            ]],
            ['title' => 'Sales', 'order' => 4, 'children' => [
                ['icon' => 'shopping_cart', 'title' => 'Orders', 'route_name' => 'orders.index'],
            ]],
            ['title' => 'Promotions', 'order' => 5, 'children' => [
                ['icon' => 'local_offer', 'title' => 'Vouchers', 'route_name' => 'vouchers.index'],
                ['icon' => 'local_offer', 'title' => 'Price Settings', 'route_name' => 'price-settings.index'],
                ['icon' => 'storefront', 'title' => 'Store Pricing', 'route_name' => 'price-product-setting-store.index'],
            ]],
            ['title' => 'Pick & Pack', 'order' => 6, 'children' => [
                ['icon' => 'inventory', 'title' => 'Picking List', 'route_name' => 'picking-list.index'],
                ['icon' => 'package', 'title' => 'Packing Slip', 'route_name' => 'packing-slip.index'],
                ['icon' => 'output_circle', 'title' => 'Packing Out', 'route_name' => 'packing-out.index'],
                ['icon' => 'handshake', 'title' => 'Handover', 'route_name' => 'handover.index'],
                ['icon' => 'local_shipping', 'title' => 'Delivery', 'route_name' => 'delivery.index'],
            ]],
            ['title' => 'Store Management', 'order' => 7, 'children' => [
                ['icon' => 'account_tree', 'title' => 'Store Groups', 'route_name' => 'store-groups.index'],
                ['icon' => 'layers', 'title' => 'Store Tiers', 'route_name' => 'store-tiers.index'],
                ['icon' => 'store', 'title' => 'Stores', 'route_name' => 'stores.index'],
                ['icon' => 'devices', 'title' => 'Channel Groups', 'route_name' => 'store-channel-groups.index'],
                ['icon' => 'terminal', 'title' => 'Channels', 'route_name' => 'store-channels.index'],
            ]],
            ['title' => 'Shipping & Payment', 'order' => 8, 'children' => [
                ['icon' => 'local_shipping', 'title' => 'Couriers', 'route_name' => 'couriers.index'],
                ['icon' => 'account_balance_wallet', 'title' => 'Payment Methods', 'route_name' => 'payment-methods.index'],
            ]],
            ['title' => 'Content', 'order' => 9, 'children' => [
                ['icon' => 'help', 'title' => 'FAQ', 'route_name' => 'content.faq.index'],
                ['icon' => 'article', 'title' => 'Blog', 'route_name' => 'content.blog.index'],
                ['icon' => 'info', 'title' => 'About Us', 'route_name' => 'content.about.index'],
                ['icon' => 'assignment_return', 'title' => 'How To Return', 'route_name' => 'content.how-to-return.index'],
                ['icon' => 'gavel', 'title' => 'Terms & Conditions', 'route_name' => 'content.terms.index'],
                ['icon' => 'privacy_tip', 'title' => 'Privacy Policy', 'route_name' => 'content.privacy.index'],
                ['icon' => 'verified', 'title' => 'Warranty Claims', 'route_name' => 'content.warranty.index'],
            ]],
            ['title' => 'System', 'order' => 10, 'children' => [
                ['icon' => 'admin_panel_settings', 'title' => 'Roles', 'route_name' => 'roles.index'],
                ['icon' => 'shield_person', 'title' => 'Permissions', 'route_name' => 'permissions.index'],
                ['icon' => 'group', 'title' => 'Users', 'route_name' => 'users.index'],
                ['icon' => 'person', 'title' => 'Profile', 'route_name' => 'profile.show'],
            ]],
        ];
        @endphp
        @foreach($menuGroups as $menu)
            <p class="sidebar-group-title px-4 py-2 text-label-sm uppercase text-sidebar-text opacity-50 tracking-widest font-bold m-0 {{ $menu['order'] > 1 ? 'mt-4' : '' }}">{{ $menu['title'] }}</p>
            @foreach($menu['children'] as $child)
                <a class="sidebar-link flex items-center justify-start px-4 py-3 text-sidebar-text hover:bg-sidebar-active/10 hover:text-sidebar-active transition-colors duration-200 {{ request()->routeIs($child['route_name'] . '*') ? 'bg-primary-container text-sidebar-active' : '' }}" href="{{ route($child['route_name']) }}">
                    <span class="material-symbols-outlined shrink-0">{{ $child['icon'] }}</span>
                    <span class="sidebar-link-text font-label-md text-label-md whitespace-nowrap">{{ $child['title'] }}</span>
                </a>
            @endforeach
        @endforeach
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
@media (min-width: 1024px) {
    aside#sidebar.sidebar-collapsed {
        width: 70px !important;
    }
    aside#sidebar.sidebar-collapsed ~ main {
        margin-left: 70px !important;
    }
    aside#sidebar.sidebar-collapsed .sidebar-group-title,
    aside#sidebar.sidebar-collapsed .sidebar-link-text,
    aside#sidebar.sidebar-collapsed .sidebar-bottom-text {
        display: none;
    }
    aside#sidebar.sidebar-collapsed nav {
        padding: 0;
    }
    aside#sidebar.sidebar-collapsed .sidebar-link {
        justify-content: center;
        padding-left: 0;
        padding-right: 0;
        margin-left: 0;
        margin-right: 0;
        width: 100%;
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
});
</script>
