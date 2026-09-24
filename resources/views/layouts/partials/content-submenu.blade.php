<div class="flex border-b border-outline-variant mb-6 overflow-x-auto">
    @can('content.faq.index')
        <a href="{{ route('content.faq.index') }}" class="{{ request()->routeIs('content.faq.*') ? 'bg-primary text-white font-bold px-6 py-2.5 text-sm whitespace-nowrap rounded-t-lg transition-colors focus:outline-none' : 'text-secondary hover:text-primary px-6 py-2.5 text-sm transition-colors whitespace-nowrap focus:outline-none' }}">FAQ</a>
    @endcan
    @can('content.blog.index')
        <a href="{{ route('content.blog.index') }}" class="{{ request()->routeIs('content.blog.*') ? 'bg-primary text-white font-bold px-6 py-2.5 text-sm whitespace-nowrap rounded-t-lg transition-colors focus:outline-none' : 'text-secondary hover:text-primary px-6 py-2.5 text-sm transition-colors whitespace-nowrap focus:outline-none' }}">Blog</a>
    @endcan
    @can('content.about.index')
        <a href="{{ route('content.about.index') }}" class="{{ request()->routeIs('content.about.*') ? 'bg-primary text-white font-bold px-6 py-2.5 text-sm whitespace-nowrap rounded-t-lg transition-colors focus:outline-none' : 'text-secondary hover:text-primary px-6 py-2.5 text-sm transition-colors whitespace-nowrap focus:outline-none' }}">About Us</a>
    @endcan
    @can('content.how-to-return.index')
        <a href="{{ route('content.how-to-return.index') }}" class="{{ request()->routeIs('content.how-to-return.*') ? 'bg-primary text-white font-bold px-6 py-2.5 text-sm whitespace-nowrap rounded-t-lg transition-colors focus:outline-none' : 'text-secondary hover:text-primary px-6 py-2.5 text-sm transition-colors whitespace-nowrap focus:outline-none' }}">How To Return</a>
    @endcan
    @can('content.terms.index')
        <a href="{{ route('content.terms.index') }}" class="{{ request()->routeIs('content.terms.*') ? 'bg-primary text-white font-bold px-6 py-2.5 text-sm whitespace-nowrap rounded-t-lg transition-colors focus:outline-none' : 'text-secondary hover:text-primary px-6 py-2.5 text-sm transition-colors whitespace-nowrap focus:outline-none' }}">Terms</a>
    @endcan
    @can('content.privacy.index')
        <a href="{{ route('content.privacy.index') }}" class="{{ request()->routeIs('content.privacy.*') ? 'bg-primary text-white font-bold px-6 py-2.5 text-sm whitespace-nowrap rounded-t-lg transition-colors focus:outline-none' : 'text-secondary hover:text-primary px-6 py-2.5 text-sm transition-colors whitespace-nowrap focus:outline-none' }}">Privacy Policy</a>
    @endcan
    @can('content.warranty.index')
        <a href="{{ route('content.warranty.index') }}" class="{{ request()->routeIs('content.warranty.*') ? 'bg-primary text-white font-bold px-6 py-2.5 text-sm whitespace-nowrap rounded-t-lg transition-colors focus:outline-none' : 'text-secondary hover:text-primary px-6 py-2.5 text-sm transition-colors whitespace-nowrap focus:outline-none' }}">Warranty</a>
    @endcan
    @can('content.banners.index')
        <a href="{{ route('content.banners.index') }}" class="{{ request()->routeIs('content.banners.*') ? 'bg-primary text-white font-bold px-6 py-2.5 text-sm whitespace-nowrap rounded-t-lg transition-colors focus:outline-none' : 'text-secondary hover:text-primary px-6 py-2.5 text-sm transition-colors whitespace-nowrap focus:outline-none' }}">Banners</a>
    @endcan
    @can('content.homepage.index')
        <a href="{{ route('content.homepage.index') }}" class="{{ request()->routeIs('content.homepage.*') ? 'bg-primary text-white font-bold px-6 py-2.5 text-sm whitespace-nowrap rounded-t-lg transition-colors focus:outline-none' : 'text-secondary hover:text-primary px-6 py-2.5 text-sm transition-colors whitespace-nowrap focus:outline-none' }}">Homepages</a>
    @endcan
</div>
