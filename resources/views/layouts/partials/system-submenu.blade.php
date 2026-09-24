<div class="flex border-b border-outline-variant mb-6 overflow-x-auto">
    @can('roles.index')
        <a href="{{ route('roles.index') }}" class="{{ request()->routeIs('roles.*') ? 'bg-primary text-white font-bold px-6 py-2.5 text-sm whitespace-nowrap rounded-t-lg transition-colors focus:outline-none' : 'text-secondary hover:text-primary px-6 py-2.5 text-sm transition-colors whitespace-nowrap focus:outline-none' }}">Roles & Hak Akses</a>
    @endcan
    @can('permissions.index')
        <a href="{{ route('permissions.index') }}" class="{{ request()->routeIs('permissions.*') ? 'bg-primary text-white font-bold px-6 py-2.5 text-sm whitespace-nowrap rounded-t-lg transition-colors focus:outline-none' : 'text-secondary hover:text-primary px-6 py-2.5 text-sm transition-colors whitespace-nowrap focus:outline-none' }}">Permissions</a>
    @endcan
    @can('users.index')
        <a href="{{ route('users.index') }}" class="{{ request()->routeIs('users.*') ? 'bg-primary text-white font-bold px-6 py-2.5 text-sm whitespace-nowrap rounded-t-lg transition-colors focus:outline-none' : 'text-secondary hover:text-primary px-6 py-2.5 text-sm transition-colors whitespace-nowrap focus:outline-none' }}">Admin Users</a>
    @endcan
    @can('profile.show')
        <a href="{{ route('profile.show') }}" class="{{ request()->routeIs('profile.*') ? 'bg-primary text-white font-bold px-6 py-2.5 text-sm whitespace-nowrap rounded-t-lg transition-colors focus:outline-none' : 'text-secondary hover:text-primary px-6 py-2.5 text-sm transition-colors whitespace-nowrap focus:outline-none' }}">Profile</a>
    @endcan
</div>
