<div class="flex border-b border-outline-variant mb-6 overflow-x-auto">
    @can('customers.index')
        <a href="{{ route('customers.index') }}" class="{{ request()->routeIs('customers.*') ? 'bg-primary text-white font-bold px-6 py-2.5 text-sm whitespace-nowrap rounded-t-lg transition-colors focus:outline-none' : 'text-secondary hover:text-primary px-6 py-2.5 text-sm transition-colors whitespace-nowrap focus:outline-none' }}">Semua Customers</a>
    @endcan

    @canany(['customer-groups.index', 'customers.index'])
        <a href="{{ route('customer-groups.index') }}" class="{{ request()->routeIs('customer-groups.*') ? 'bg-primary text-white font-bold px-6 py-2.5 text-sm whitespace-nowrap rounded-t-lg transition-colors focus:outline-none' : 'text-secondary hover:text-primary px-6 py-2.5 text-sm transition-colors whitespace-nowrap focus:outline-none' }}">Customer Groups</a>
    @endcanany
</div>
