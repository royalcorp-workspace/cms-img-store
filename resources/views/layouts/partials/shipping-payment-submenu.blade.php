<div class="flex border-b border-outline-variant mb-6 overflow-x-auto">
    @can('couriers.index')
        <a href="{{ route('couriers.index') }}" class="{{ request()->routeIs('couriers.*') ? 'bg-primary text-white font-bold px-6 py-2.5 text-sm whitespace-nowrap rounded-t-lg transition-colors focus:outline-none' : 'text-secondary hover:text-primary px-6 py-2.5 text-sm transition-colors whitespace-nowrap focus:outline-none' }}">Couriers</a>
    @endcan
    @can('shipping-addresses.index')
        <a href="{{ route('shipping-addresses.index') }}" class="{{ request()->routeIs('shipping-addresses.*') ? 'bg-primary text-white font-bold px-6 py-2.5 text-sm whitespace-nowrap rounded-t-lg transition-colors focus:outline-none' : 'text-secondary hover:text-primary px-6 py-2.5 text-sm transition-colors whitespace-nowrap focus:outline-none' }}">Shipping Address Rates</a>
    @endcan
    @can('payment-methods.index')
        <a href="{{ route('payment-methods.index') }}" class="{{ request()->routeIs('payment-methods.*') ? 'bg-primary text-white font-bold px-6 py-2.5 text-sm whitespace-nowrap rounded-t-lg transition-colors focus:outline-none' : 'text-secondary hover:text-primary px-6 py-2.5 text-sm transition-colors whitespace-nowrap focus:outline-none' }}">Payment Methods</a>
    @endcan
</div>
