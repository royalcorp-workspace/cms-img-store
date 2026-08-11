<div class="flex border-b border-outline-variant mb-6 overflow-x-auto">
    <a href="{{ route('inventory.index') }}" class="{{ request()->routeIs('inventory.*') ? 'bg-primary text-white font-bold px-6 py-2.5 text-sm whitespace-nowrap rounded-t-lg transition-colors focus:outline-none' : 'text-secondary hover:text-primary px-6 py-2.5 text-sm transition-colors whitespace-nowrap focus:outline-none' }}">Inventory</a>
</div>
