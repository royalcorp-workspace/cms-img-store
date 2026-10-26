@php
    $adminUser = \Illuminate\Support\Facades\Auth::guard('admin')->user() ?? auth()->user();
@endphp

<div class="flex border-b border-outline-variant mb-6 overflow-x-auto">
    @if(!$adminUser || $adminUser->isSuperAdmin() || $adminUser->hasPermission('admin.jde.index') || $adminUser->can('admin.jde.index'))
        {{-- Sub Menu Aktif --}}
        <a href="{{ route('admin.jde.index', ['tab' => 'item_master']) }}" 
           class="{{ request('tab', 'item_master') === 'item_master' ? 'bg-primary text-white font-bold px-6 py-2.5 text-sm whitespace-nowrap rounded-t-lg transition-colors focus:outline-none' : 'text-secondary hover:text-primary px-6 py-2.5 text-sm transition-colors whitespace-nowrap focus:outline-none' }}">
            Item Master
        </a>

        {{-- Sub Menu Sementara di-hide / di-komen sesuai kebutuhan --}}
        {{--
        <a href="{{ route('admin.jde.index', ['tab' => 'base_price']) }}" class="text-secondary/40 px-6 py-2.5 text-sm whitespace-nowrap cursor-not-allowed">
            Base Price
        </a>
        <a href="{{ route('admin.jde.index', ['tab' => 'branch']) }}" class="text-secondary/40 px-6 py-2.5 text-sm whitespace-nowrap cursor-not-allowed">
            Cabang JDE
        </a>
        <a href="{{ route('admin.jde.index', ['tab' => 'customer']) }}" class="text-secondary/40 px-6 py-2.5 text-sm whitespace-nowrap cursor-not-allowed">
            Toko Retail
        </a>
        --}}
    @endif
</div>
