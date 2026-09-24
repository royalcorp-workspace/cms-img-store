<div class="flex border-b border-outline-variant mb-6 overflow-x-auto">
    @can('store-groups.index')
        <a href="{{ route('store-groups.index') }}" class="{{ request()->routeIs('store-groups.*') ? 'bg-primary text-white font-bold px-6 py-2.5 text-sm whitespace-nowrap rounded-t-lg transition-colors focus:outline-none' : 'text-secondary hover:text-primary px-6 py-2.5 text-sm transition-colors whitespace-nowrap focus:outline-none' }}">Store Groups</a>
    @endcan
    @can('store-tiers.index')
        <a href="{{ route('store-tiers.index') }}" class="{{ request()->routeIs('store-tiers.*') ? 'bg-primary text-white font-bold px-6 py-2.5 text-sm whitespace-nowrap rounded-t-lg transition-colors focus:outline-none' : 'text-secondary hover:text-primary px-6 py-2.5 text-sm transition-colors whitespace-nowrap focus:outline-none' }}">Store Tiers</a>
    @endcan
    @can('stores.index')
        <a href="{{ route('stores.index') }}" class="{{ request()->routeIs('stores.*') ? 'bg-primary text-white font-bold px-6 py-2.5 text-sm whitespace-nowrap rounded-t-lg transition-colors focus:outline-none' : 'text-secondary hover:text-primary px-6 py-2.5 text-sm transition-colors whitespace-nowrap focus:outline-none' }}">Stores</a>
    @endcan
    @can('store-channel-groups.index')
        <a href="{{ route('store-channel-groups.index') }}" class="{{ request()->routeIs('store-channel-groups.*') ? 'bg-primary text-white font-bold px-6 py-2.5 text-sm whitespace-nowrap rounded-t-lg transition-colors focus:outline-none' : 'text-secondary hover:text-primary px-6 py-2.5 text-sm transition-colors whitespace-nowrap focus:outline-none' }}">Channel Groups</a>
    @endcan
    @can('store-channels.index')
        <a href="{{ route('store-channels.index') }}" class="{{ request()->routeIs('store-channels.*') ? 'bg-primary text-white font-bold px-6 py-2.5 text-sm whitespace-nowrap rounded-t-lg transition-colors focus:outline-none' : 'text-secondary hover:text-primary px-6 py-2.5 text-sm transition-colors whitespace-nowrap focus:outline-none' }}">Channels</a>
    @endcan
</div>
