<div class="flex border-b border-outline-variant mb-6 overflow-x-auto">
    @can('picking-list.index')
        <a href="{{ route('picking-list.index') }}" class="{{ request()->routeIs('picking-list.*') ? 'bg-primary text-white font-bold px-6 py-2.5 text-sm whitespace-nowrap rounded-t-lg transition-colors focus:outline-none' : 'text-secondary hover:text-primary px-6 py-2.5 text-sm transition-colors whitespace-nowrap focus:outline-none' }}">Picking List</a>
    @endcan
    @can('packing-slip.index')
        <a href="{{ route('packing-slip.index') }}" class="{{ request()->routeIs('packing-slip.*') ? 'bg-primary text-white font-bold px-6 py-2.5 text-sm whitespace-nowrap rounded-t-lg transition-colors focus:outline-none' : 'text-secondary hover:text-primary px-6 py-2.5 text-sm transition-colors whitespace-nowrap focus:outline-none' }}">Packing Slip</a>
    @endcan
    @can('packing-out.index')
        <a href="{{ route('packing-out.index') }}" class="{{ request()->routeIs('packing-out.*') ? 'bg-primary text-white font-bold px-6 py-2.5 text-sm whitespace-nowrap rounded-t-lg transition-colors focus:outline-none' : 'text-secondary hover:text-primary px-6 py-2.5 text-sm transition-colors whitespace-nowrap focus:outline-none' }}">Packing Out</a>
    @endcan
    @can('handover.index')
        <a href="{{ route('handover.index') }}" class="{{ request()->routeIs('handover.*') ? 'bg-primary text-white font-bold px-6 py-2.5 text-sm whitespace-nowrap rounded-t-lg transition-colors focus:outline-none' : 'text-secondary hover:text-primary px-6 py-2.5 text-sm transition-colors whitespace-nowrap focus:outline-none' }}">Handover</a>
    @endcan
    @can('delivery.index')
        <a href="{{ route('delivery.index') }}" class="{{ request()->routeIs('delivery.*') ? 'bg-primary text-white font-bold px-6 py-2.5 text-sm whitespace-nowrap rounded-t-lg transition-colors focus:outline-none' : 'text-secondary hover:text-primary px-6 py-2.5 text-sm transition-colors whitespace-nowrap focus:outline-none' }}">Delivery</a>
    @endcan
</div>
