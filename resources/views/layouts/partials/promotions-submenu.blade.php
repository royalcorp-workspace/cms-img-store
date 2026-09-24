<div class="flex border-b border-outline-variant mb-6 overflow-x-auto">
    @can('vouchers.index')
        <a href="{{ route('vouchers.index') }}" class="{{ request()->routeIs('vouchers.*') ? 'bg-primary text-white font-bold px-6 py-2.5 text-sm whitespace-nowrap rounded-t-lg transition-colors focus:outline-none' : 'text-secondary hover:text-primary px-6 py-2.5 text-sm transition-colors whitespace-nowrap focus:outline-none' }}">Vouchers</a>
    @endcan
    @can('price-settings.index')
        <a href="{{ route('price-settings.index') }}" class="{{ request()->routeIs('price-settings.*') ? 'bg-primary text-white font-bold px-6 py-2.5 text-sm whitespace-nowrap rounded-t-lg transition-colors focus:outline-none' : 'text-secondary hover:text-primary px-6 py-2.5 text-sm transition-colors whitespace-nowrap focus:outline-none' }}">Price Settings</a>
    @endcan
    @can('price-product-setting-store.index')
        <a href="{{ route('price-product-setting-store.index') }}" class="{{ request()->routeIs('price-product-setting-store.*') ? 'bg-primary text-white font-bold px-6 py-2.5 text-sm whitespace-nowrap rounded-t-lg transition-colors focus:outline-none' : 'text-secondary hover:text-primary px-6 py-2.5 text-sm transition-colors whitespace-nowrap focus:outline-none' }}">Store Pricing</a>
    @endcan
    @can('bundlings.index')
        <a href="{{ route('bundlings.index') }}" class="{{ request()->routeIs('bundlings.*') ? 'bg-primary text-white font-bold px-6 py-2.5 text-sm whitespace-nowrap rounded-t-lg transition-colors focus:outline-none' : 'text-secondary hover:text-primary px-6 py-2.5 text-sm transition-colors whitespace-nowrap focus:outline-none' }}">Bundling</a>
    @endcan
    @can('events.index')
        <a href="{{ route('events.index') }}" class="{{ request()->routeIs('events.*') ? 'bg-primary text-white font-bold px-6 py-2.5 text-sm whitespace-nowrap rounded-t-lg transition-colors focus:outline-none' : 'text-secondary hover:text-primary px-6 py-2.5 text-sm transition-colors whitespace-nowrap focus:outline-none' }}">Events</a>
    @endcan
</div>
