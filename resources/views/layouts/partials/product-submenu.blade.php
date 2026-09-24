<div class="flex border-b border-outline-variant mb-6 overflow-x-auto">
    @can('products.index')
        <a href="{{ route('products.index') }}" class="{{ request()->routeIs('products.*') && !request()->routeIs('product-suggestions.*') ? 'bg-primary text-white font-bold px-6 py-2.5 text-sm whitespace-nowrap rounded-t-lg transition-colors focus:outline-none' : 'text-secondary hover:text-primary px-6 py-2.5 text-sm transition-colors whitespace-nowrap focus:outline-none' }}">Product</a>
    @endcan
    @can('categories.index')
        <a href="{{ route('categories.index') }}" class="{{ request()->routeIs('categories.*') ? 'bg-primary text-white font-bold px-6 py-2.5 text-sm whitespace-nowrap rounded-t-lg transition-colors focus:outline-none' : 'text-secondary hover:text-primary px-6 py-2.5 text-sm transition-colors whitespace-nowrap focus:outline-none' }}">Category</a>
    @endcan
    @can('brands.index')
        <a href="{{ route('brands.index') }}" class="{{ request()->routeIs('brands.*') ? 'bg-primary text-white font-bold px-6 py-2.5 text-sm whitespace-nowrap rounded-t-lg transition-colors focus:outline-none' : 'text-secondary hover:text-primary px-6 py-2.5 text-sm transition-colors whitespace-nowrap focus:outline-none' }}">Brand</a>
    @endcan
    @can('product-suggestions.index')
        <a href="{{ route('product-suggestions.index') }}" class="{{ request()->routeIs('product-suggestions.*') ? 'bg-primary text-white font-bold px-6 py-2.5 text-sm whitespace-nowrap rounded-t-lg transition-colors focus:outline-none' : 'text-secondary hover:text-primary px-6 py-2.5 text-sm transition-colors whitespace-nowrap focus:outline-none' }}">Suggestions</a>
    @endcan
    @if(auth()->user()?->hasRole('Super Admin') || auth()->user()?->can('tags.index'))
        <a href="{{ route('tags.index') }}" class="{{ request()->routeIs('tags.*') ? 'bg-primary text-white font-bold px-6 py-2.5 text-sm whitespace-nowrap rounded-t-lg transition-colors focus:outline-none' : 'text-secondary hover:text-primary px-6 py-2.5 text-sm transition-colors whitespace-nowrap focus:outline-none' }}">Product Tags</a>
    @endif
</div>
