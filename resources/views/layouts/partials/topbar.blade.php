<header class="h-16 px-gutter flex justify-between items-center sticky top-0 z-40 bg-surface border-b border-outline-variant transition-all duration-300">
    <div class="flex items-center gap-4">
        <div class="flex items-center gap-2">
            <button class="p-2 text-on-surface-variant hover:bg-surface-container rounded-full transition-colors relative">
                <span class="material-symbols-outlined">notifications</span>
                <span class="absolute top-2 right-2 w-2 h-2 bg-primary rounded-full border-2 border-surface"></span>
            </button>
            <button id="darkModeToggle" class="p-2 text-on-surface-variant hover:bg-surface-container rounded-full transition-colors" title="Toggle Dark Mode">
                <span class="material-symbols-outlined">dark_mode</span>
            </button>
        </div>
    </div>
    <div class="flex items-center gap-4">
        <div class="h-8 w-[1px] bg-outline-variant mx-2"></div>
        @php
            $admin = \Illuminate\Support\Facades\Auth::guard('admin')->user();
        @endphp
        <div class="relative" id="profileDropdownContainer">
            <button class="flex items-center gap-3 pl-2 group" id="profileDropdownBtn">
                <div class="text-right hidden sm:block">
                    <p class="font-label-md text-[14px] leading-none text-on-surface group-hover:text-primary transition-colors">{{ $admin->name ?? 'Admin User' }}</p>
                    <p class="text-label-sm text-on-surface-variant">{{ $admin->email ?? 'admin@example.com' }}</p>
                </div>
                <div class="w-10 h-10 rounded-full bg-surface-container flex items-center justify-center border-2 border-outline-variant group-hover:border-primary transition-colors">
                    <span class="text-on-surface font-bold text-sm">{{ strtoupper(substr($admin->name ?? 'A', 0, 1)) }}</span>
                </div>
                <span class="material-symbols-outlined text-[18px] text-on-surface-variant group-hover:text-primary transition-colors">expand_more</span>
            </button>
            <div id="profileDropdownMenu" class="hidden absolute right-0 top-full mt-1 border-0 shadow-lg rounded-lg overflow-hidden z-50" style="min-width: 200px; background-color: var(--color-surface-container-lowest);">
                <div class="px-3 py-2 border-b border-outline-variant/30">
                    <p class="text-body-md font-semibold" style="color: var(--color-on-surface);">{{ $admin->name ?? 'Admin User' }}</p>
                    <p class="text-label-sm" style="color: var(--color-on-surface-variant);">{{ $admin->email ?? 'admin@example.com' }}</p>
                </div>
                <div class="py-1">
                    <a href="{{ route('profile.show') }}" class="flex items-center px-3 py-2 text-body-md transition-colors" style="color: var(--color-on-surface);" onmouseover="this.style.backgroundColor='var(--color-surface-container-high)'" onmouseout="this.style.backgroundColor='transparent'">
                        <span class="material-symbols-outlined text-[18px] mr-2 align-middle">person</span> Edit Profile
                    </a>
                    <a href="#" class="flex items-center px-3 py-2 text-body-md transition-colors" style="color: var(--color-on-surface);" onmouseover="this.style.backgroundColor='var(--color-surface-container-high)'" onmouseout="this.style.backgroundColor='transparent'">
                        <span class="material-symbols-outlined text-[18px] mr-2 align-middle">settings</span> Settings
                    </a>
                </div>
                <div class="border-t border-outline-variant/30"></div>
                <div class="py-1">
                    <form method="POST" action="{{ route('logout') }}" class="m-0">
                        @csrf
                        <button type="submit" class="w-full text-left flex items-center px-3 py-2 text-body-md transition-colors" style="color: #ef4444;" onmouseover="this.style.backgroundColor='#fef2f2'" onmouseout="this.style.backgroundColor='transparent'">
                            <span class="material-symbols-outlined text-[18px] mr-2 align-middle">logout</span> Logout
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</header>