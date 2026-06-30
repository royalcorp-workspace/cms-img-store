<aside class="fixed left-0 top-0 h-full w-sidebar-width bg-sidebar-bg flex flex-col py-6 z-50 transition-all duration-300">
    <div class="px-6 mb-6 flex items-center justify-between">
        <span class="font-headline-xl text-headline-xl font-bold text-sidebar-active tracking-tight">IMG</span>
    </div>
    <nav class="flex-1 overflow-y-auto sidebar-scroll space-y-1">
        @php
        $menuGroups = [
            ['title' => 'General', 'order' => 1, 'children' => [
                ['icon' => 'dashboard', 'title' => 'Dashboard', 'route_name' => 'dashboard'],
            ]],
            ['title' => 'Products', 'order' => 2, 'children' => [
                ['icon' => 'inventory_2', 'title' => 'Products', 'route_name' => 'products.index'],
                ['icon' => 'category', 'title' => 'Category', 'route_name' => 'categories.index'],
                ['icon' => 'warehouse', 'title' => 'Inventory', 'route_name' => 'inventory.index'],
            ]],
            ['title' => 'Promotions', 'order' => 3, 'children' => [
                ['icon' => 'shopping_cart', 'title' => 'Orders', 'route_name' => 'orders.index'],
                ['icon' => 'local_offer', 'title' => 'Vouchers', 'route_name' => 'vouchers.index'],
                ['icon' => 'local_offer', 'title' => 'Price Settings', 'route_name' => 'price-settings.index'],
            ]],
            ['title' => 'Customers', 'order' => 4, 'children' => [
                ['icon' => 'person', 'title' => 'Customers', 'route_name' => 'customers.index'],
            ]],
            ['title' => 'System', 'order' => 5, 'children' => [
                ['icon' => 'admin_panel_settings', 'title' => 'Roles', 'route_name' => 'roles.index'],
                ['icon' => 'shield_person', 'title' => 'Permissions', 'route_name' => 'permissions.index'],
                ['icon' => 'group', 'title' => 'Users', 'route_name' => 'users.index'],
                ['icon' => 'person', 'title' => 'Profile', 'route_name' => 'profile.show'],
            ]],
        ];
        @endphp
        @foreach($menuGroups as $menu)
            <p class="px-4 py-2 text-label-sm uppercase text-sidebar-text opacity-50 tracking-widest font-bold {{ $menu['order'] > 1 ? 'mt-4' : '' }}">{{ $menu['title'] }}</p>
            @foreach($menu['children'] as $child)
                <a class="flex items-center gap-3 px-4 py-3 text-sidebar-text hover:bg-sidebar-active/10 hover:text-sidebar-active transition-colors duration-200 rounded-lg mx-2 {{ request()->routeIs($child['route_name'] . '*') ? 'bg-primary-container text-sidebar-active translate-x-1 transition-transform duration-200' : '' }}" href="{{ route($child['route_name']) }}">
                    <span class="material-symbols-outlined">{{ $child['icon'] }}</span>
                    <span class="font-label-md text-label-md">{{ $child['title'] }}</span>
                </a>
            @endforeach
        @endforeach
    </nav>
    <div class="mt-auto px-4 pt-4 border-t border-white/10">
        <div class="flex items-center gap-3">
            <img class="w-10 h-10 rounded-full object-cover border-2 border-primary-container" src="https://lh3.googleusercontent.com/aida-public/AB6AXuDN6J2G_WoIEjZsk0ndhZxVyrPKeJ-sasOXOqAjPaJgHn5qR8SUf8ufE_GpmoeaYS6uoh-Fi7qYP9KUWzJ9-uGEBp07smSo-xxEmIUQt5sA18fmBtm7JM8mQSBOxdqnca84qNA4jxKN_pBhMSGPcqRIeA2nqaSzFHsBDO21-Evoa_NPnkcw2oMvqm-N114qVFIzA-oZquhLg-z0n4frLXM08RLfq9ivAmB7iLEHf_mud9u0eRdKJ9Ry25HGVutc4rcblLaNnaW-ckeN"/>
            <div>
                <p class="font-label-md text-label-md text-sidebar-active">Admin User</p>
                <p class="text-[10px] font-label-sm text-sidebar-text uppercase">Store Manager</p>
            </div>
        </div>
    </div>
</aside>