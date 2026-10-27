@php
    $adminUser = \Illuminate\Support\Facades\Auth::guard('admin')->user() ?? auth()->user();
@endphp

<div class="flex items-center gap-2 border-b border-outline-variant mb-6 overflow-x-auto pb-0.5">
    @if(!$adminUser || $adminUser->isSuperAdmin() || $adminUser->hasPermission('admin.jde.index') || $adminUser->can('admin.jde.index'))
        {{-- Sub Menu Aktif --}}
        <a href="{{ route('admin.jde.index', ['tab' => 'item_master']) }}" 
           class="{{ request('tab', 'item_master') === 'item_master' ? 'border-b-2 border-primary text-primary font-bold px-5 py-2.5 text-sm whitespace-nowrap transition-all' : 'text-on-surface-variant hover:text-primary px-5 py-2.5 text-sm transition-all whitespace-nowrap font-medium' }}">
            <span class="flex items-center gap-2">
                <span class="material-symbols-outlined text-[18px]">bed</span>
                <span>Item Master</span>
            </span>
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
