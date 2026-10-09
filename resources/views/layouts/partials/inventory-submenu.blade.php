<div class="flex border-b border-outline-variant mb-6 overflow-x-auto">
    @can('inventory.index')
        <a href="{{ route('inventory.index') }}" class="{{ request()->routeIs('inventory.index', 'inventory.create', 'inventory.edit', 'inventory.import.*') ? 'bg-primary text-white font-bold px-6 py-2.5 text-sm whitespace-nowrap rounded-t-lg transition-colors focus:outline-none' : 'text-secondary hover:text-primary px-6 py-2.5 text-sm transition-colors whitespace-nowrap focus:outline-none' }}">Inventory</a>
    @endcan
    @can('inventory.stock-card.index')
        <a href="{{ route('inventory.stock-card.index') }}" class="{{ request()->routeIs('inventory.stock-card.*') ? 'bg-primary text-white font-bold px-6 py-2.5 text-sm whitespace-nowrap rounded-t-lg transition-colors focus:outline-none' : 'text-secondary hover:text-primary px-6 py-2.5 text-sm transition-colors whitespace-nowrap focus:outline-none' }}">Kartu Stok</a>
    @endcan
    @can('warehouses.index')
        <a href="{{ route('warehouses.index') }}" class="{{ request()->routeIs('warehouses.*') ? 'bg-primary text-white font-bold px-6 py-2.5 text-sm whitespace-nowrap rounded-t-lg transition-colors focus:outline-none' : 'text-secondary hover:text-primary px-6 py-2.5 text-sm transition-colors whitespace-nowrap focus:outline-none' }}">Warehouse</a>
    @endcan
</div>
