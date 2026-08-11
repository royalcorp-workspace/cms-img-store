<div class="flex border-b border-outline-variant mb-6 overflow-x-auto">
    <a href="{{ route('customers.index') }}" class="{{ request()->routeIs('customers.index') || request()->routeIs('customers.create') ? 'bg-primary text-white font-bold px-6 py-2.5 text-sm whitespace-nowrap rounded-t-lg transition-colors focus:outline-none' : 'text-secondary hover:text-primary px-6 py-2.5 text-sm transition-colors whitespace-nowrap focus:outline-none' }}">Semua Customers</a>
    
    @if(isset($customer) && $customer->id)
        <a href="{{ route('customers.show', $customer->id) }}" class="{{ request()->routeIs('customers.show') || request()->routeIs('customers.edit') ? 'bg-primary text-white font-bold px-6 py-2.5 text-sm whitespace-nowrap rounded-t-lg transition-colors focus:outline-none' : 'text-secondary hover:text-primary px-6 py-2.5 text-sm transition-colors whitespace-nowrap focus:outline-none' }}">Profil</a>
    @endif
</div>
