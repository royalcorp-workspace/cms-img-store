<aside id="sidebar" class="fixed left-0 top-0 h-full w-sidebar-width bg-sidebar-bg flex flex-col py-6 z-50 transition-all duration-300">
    <div class="px-6 mb-6 flex items-center justify-between">
        <div class="flex items-center gap-2">
            <img alt="IMG Logo" class="h-8 w-auto object-contain sidebar-logo" src="{{ asset('admin/assets/images/logo.png') }}">
            <span class="font-headline-xl text-headline-xl font-bold text-sidebar-active tracking-tight">IMG</span>
        </div>
    </div>
    <nav class="flex-1 min-h-0 overflow-y-auto sidebar-scroll">
        @php
            $admin = \Illuminate\Support\Facades\Auth::guard('admin')->user();
            $unreadChatCount = \App\Models\Message::where('sender_type', 'customer')->where('is_read', false)->count();

            // Mapping active route patterns per module
            $moduleActivePatterns = [
                'dashboard' => ['dashboard'],
                'customers' => ['customers.*'],
                'chat' => ['chat.*'],
                'products' => ['products.*', 'categories.*', 'brands.*', 'product-suggestions.*'],
                'inventory' => ['inventory.*', 'warehouses.*'],
                'sales' => ['orders.*', 'settlements.*', 'reconciliation.*'],
                'orders' => ['orders.*', 'settlements.*', 'reconciliation.*'],
                'promotions' => ['vouchers.*', 'price-settings.*', 'price-product-setting-store.*', 'events.*', 'bundlings.*'],
                'vouchers' => ['vouchers.*', 'price-settings.*', 'price-product-setting-store.*', 'events.*', 'bundlings.*'],
                'pick & pack' => ['picking-list.*', 'packing-slip.*', 'packing-out.*', 'handover.*', 'delivery.*'],
                'picking-list' => ['picking-list.*', 'packing-slip.*', 'packing-out.*', 'handover.*', 'delivery.*'],
                'store management' => ['store-groups.*', 'stores.*', 'store-tiers.*', 'store-channels.*', 'store-channel-stocks.*'],
                'store-management' => ['store-groups.*', 'stores.*', 'store-tiers.*', 'store-channels.*', 'store-channel-stocks.*'],
                'shipping & payment' => ['couriers.*', 'payment-methods.*', 'shipping-addresses.*'],
                'shipping-payment' => ['couriers.*', 'payment-methods.*', 'shipping-addresses.*'],
                'content' => ['content.*'],
                'system' => ['roles.*', 'permissions.*', 'users.*', 'profile.*'],
            ];

            // Helper to check if user has permission for a route string (supports piped routes)
            $canAccessRoute = function ($routeStr) use ($admin) {
                if (!$admin) return false;
                if ($admin->isSuperAdmin()) return true;
                if (empty($routeStr)) return false;
                $routes = explode('|', $routeStr);
                foreach ($routes as $r) {
                    $r = trim($r);
                    if (!empty($r) && ($admin->can($r) || $admin->hasPermission($r))) {
                        return true;
                    }
                }
                return false;
            };

            // Recursive helper to check if user can access a menu item or any of its descendants
            $hasMenuAccess = function ($item) use (&$hasMenuAccess, $canAccessRoute, $admin) {
                if (!$admin) return false;
                if ($admin->isSuperAdmin()) return true;
                if (!empty($item->route_name) && $canAccessRoute($item->route_name)) {
                    return true;
                }
                if (isset($item->childs) && count($item->childs)) {
                    foreach ($item->childs as $child) {
                        if ($hasMenuAccess($child)) {
                            return true;
                        }
                    }
                }
                return false;
            };

            // Helper to check if route is active for a menu module
            $isModuleActive = function ($menu, $patternsMap) {
                $slug = strtolower(trim($menu->title));
                $patterns = $patternsMap[$slug] ?? null;
                if (!$patterns && !empty($menu->route_name)) {
                    $key = strtolower(trim($menu->route_name));
                    $patterns = $patternsMap[$key] ?? null;
                }

                if ($patterns) {
                    foreach ($patterns as $p) {
                        if (request()->routeIs($p)) {
                            return true;
                        }
                    }
                }

                if (!empty($menu->route_name)) {
                    $routes = explode('|', $menu->route_name);
                    foreach ($routes as $r) {
                        $r = trim($r);
                        if (request()->routeIs($r)) return true;
                        $prefix = \Illuminate\Support\Str::beforeLast($r, '.');
                        if (!empty($prefix) && request()->routeIs($prefix . '.*')) return true;
                    }
                }

                return false;
            };
        @endphp

        <div class="flex flex-col gap-1">
            @foreach($menus as $menu)
                @if($hasMenuAccess($menu))
                    @php
                        // Resolve target destination route (first accessible route)
                        $targetRoute = $menu->route_name;
                        if (!$targetRoute || !Route::has($targetRoute)) {
                            foreach ($menu->childs as $c) {
                                $cRoute = $c->route_name ? explode('|', $c->route_name)[0] : null;
                                if ($cRoute && Route::has($cRoute) && $canAccessRoute($cRoute)) {
                                    $targetRoute = $cRoute;
                                    break;
                                }
                            }
                        }
                        if (str_contains($targetRoute ?? '', '|')) {
                            $targetRoute = explode('|', $targetRoute)[0];
                        }
                        if (!$targetRoute || !Route::has($targetRoute)) {
                            $targetRoute = 'dashboard';
                        }

                        $isActive = $isModuleActive($menu, $moduleActivePatterns);
                        $badgeCount = ($menu->route_name === 'chat.index' || strtolower($menu->title) === 'live chat') ? $unreadChatCount : null;
                    @endphp

                    <a class="sidebar-link flex items-center justify-between w-full px-4 py-3 text-sidebar-text hover:bg-sidebar-active/10 hover:text-sidebar-active transition-colors duration-200 {{ $isActive ? 'bg-primary-container text-sidebar-active font-semibold' : '' }}" 
                       href="{{ route($targetRoute) }}"
                       title="{{ $menu->title }}">
                        <div class="flex items-center gap-3">
                            <span class="material-symbols-outlined shrink-0 text-[20px]">{{ $menu->icon ?: 'circle' }}</span>
                            <span class="sidebar-link-text font-label-md text-label-md whitespace-nowrap">{{ $menu->title }}</span>
                        </div>
                        @if($badgeCount !== null)
                            <span id="menu-badge-chat" class="sidebar-badge bg-danger text-white text-[10px] font-bold px-1.5 py-0.5 rounded-full min-w-[18px] text-center {{ $badgeCount > 0 ? '' : 'hidden' }}">{{ $badgeCount }}</span>
                        @endif
                    </a>
                @endif
            @endforeach
        </div>
    </nav>
    <div class="mt-auto px-4 pt-4 border-t border-white/10 sidebar-bottom">
        @php
            $admin = \Illuminate\Support\Facades\Auth::guard('admin')->user();
        @endphp
        <div class="flex items-center gap-3">
            @if($admin && !empty($admin->photo_url))
                <img class="w-10 h-10 rounded-full object-cover border-2 border-primary-container shrink-0 sidebar-bottom-img" src="{{ $admin->photo_url }}" alt="{{ $admin?->name }}"/>
            @else
                <div class="w-10 h-10 rounded-full bg-primary-container flex items-center justify-center border-2 border-primary-container shrink-0 sidebar-bottom-img">
                    <span class="text-sidebar-active font-bold text-sm">{{ strtoupper(substr($admin?->name ?? 'A', 0, 1)) }}</span>
                </div>
            @endif
            <div class="sidebar-bottom-text overflow-hidden">
                <p class="font-label-md text-label-md text-sidebar-active truncate">{{ $admin?->name ?? 'Admin User' }}</p>
                <p class="text-[10px] font-label-sm text-sidebar-text uppercase truncate">
                    {{ $admin?->roles->first()?->name ?? 'Administrator' }}
                </p>
            </div>
        </div>
    </div>
    <button id="sidebarToggle" class="sidebar-toggle-btn absolute -right-3 top-1/2 -translate-y-1/2 w-6 h-6 rounded-full bg-white shadow-md flex items-center justify-center text-sidebar-bg hover:bg-sidebar-active hover:text-white transition-colors duration-200 cursor-pointer p-0 border-0 hidden lg:flex z-[60]">
        <span id="sidebarToggleIcon" class="material-symbols-outlined text-base">chevron_left</span>
    </button>
</aside>

<style>
/* Base transition for smooth collapse */
aside#sidebar {
    transition: width 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

aside#sidebar .sidebar-link-text,
aside#sidebar .sidebar-bottom-text {
    transition: opacity 0.2s ease, visibility 0.2s ease;
}

aside#sidebar ~ main {
    transition: margin-left 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

/* Collapsed State Styles (Desktop only) */
@media (min-width: 1024px) {
    aside#sidebar.sidebar-collapsed {
        width: 72px !important;
    }
    aside#sidebar.sidebar-collapsed ~ main {
        margin-left: 72px !important;
    }
    aside#sidebar.sidebar-collapsed .sidebar-group-title,
    aside#sidebar.sidebar-collapsed .sidebar-link-text,
    aside#sidebar.sidebar-collapsed .sidebar-bottom-text,
    aside#sidebar.sidebar-collapsed .sidebar-badge {
        display: none !important;
    }
    aside#sidebar.sidebar-collapsed nav {
        padding-left: 0.5rem;
        padding-right: 0.5rem;
    }
    aside#sidebar.sidebar-collapsed .sidebar-link {
        justify-content: center !important;
        padding-left: 0 !important;
        padding-right: 0 !important;
        border-radius: 0.5rem;
    }
    aside#sidebar.sidebar-collapsed .sidebar-bottom {
        padding-left: 0.5rem !important;
        padding-right: 0.5rem !important;
        display: flex;
        justify-content: center;
    }
    aside#sidebar.sidebar-collapsed .sidebar-bottom-img {
        margin: 0 auto;
    }
    aside#sidebar.sidebar-collapsed .sidebar-toggle-btn #sidebarToggleIcon {
        transform: rotate(180deg);
    }
    aside#sidebar.sidebar-collapsed .sidebar-toggle-btn {
        right: -12px;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const sidebar = document.getElementById('sidebar');
    const toggleBtn = document.getElementById('sidebarToggle');
    const toggleIcon = document.getElementById('sidebarToggleIcon');
    const storageKey = 'admin-sidebar-collapsed';

    // Check stored state on load
    if (localStorage.getItem(storageKey) === 'true') {
        if (window.innerWidth >= 1024) {
            sidebar.classList.add('sidebar-collapsed');
        } else {
            sidebar.classList.remove('sidebar-collapsed');
        }
    }

    if (toggleBtn) {
        toggleBtn.addEventListener('click', function(e) {
            e.preventDefault();
            const isCollapsed = sidebar.classList.toggle('sidebar-collapsed');
            localStorage.setItem(storageKey, isCollapsed);
        });
    }

    // Reset collapse state on smaller screens automatically
    window.addEventListener('resize', function() {
        if (window.innerWidth < 1024) {
            sidebar.classList.remove('sidebar-collapsed');
        } else if (localStorage.getItem(storageKey) === 'true') {
            sidebar.classList.add('sidebar-collapsed');
        }
    });
});
</script>
