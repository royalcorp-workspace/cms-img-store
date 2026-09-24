<div class="flex border-b border-outline-variant mb-6 overflow-x-auto">
    @can('content.banners.index')
        <a href="{{ route('content.banners.index') }}" class="{{ request()->routeIs('content.banners.*') ? 'bg-primary text-white font-bold px-6 py-2.5 text-sm whitespace-nowrap rounded-t-lg transition-colors focus:outline-none' : 'text-secondary hover:text-primary px-6 py-2.5 text-sm transition-colors whitespace-nowrap focus:outline-none' }}">Banners</a>
    @endcan
</div>
