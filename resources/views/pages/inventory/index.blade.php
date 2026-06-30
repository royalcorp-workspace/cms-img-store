@extends('layouts.app')

@section('title', 'Warehouse Inventory')

@section('content')
<div class="flex justify-between items-end">
    <div>
        <h2 class="font-headline-lg text-headline-lg text-on-surface">Warehouse Inventory</h2>
        <p class="font-body-md text-body-md text-on-surface-variant">Real-time stock monitoring across global distribution centers.</p>
    </div>
    <div class="flex gap-3">
        <button class="flex items-center gap-2 px-4 py-2 bg-surface-container-highest border border-outline-variant rounded-lg text-secondary font-label-md hover:bg-surface-container-high transition-all">
            <span class="material-symbols-outlined text-[20px]">download</span>
            Export Report
        </button>
        <a href="{{ route('inventory.create') }}" class="flex items-center gap-2 px-4 py-2 bg-primary text-white rounded-lg font-label-md shadow-sm hover:opacity-90 transition-all">
            <span class="material-symbols-outlined text-[20px]">add</span>
            Add New Stock
        </a>
    </div>
</div>

<div class="grid grid-cols-1 md:grid-cols-3 gap-container-gap">
    <div class="bg-surface-container-lowest p-card-padding rounded-xl shadow-sm border border-outline-variant/30 group hover:border-primary/30 transition-all">
        <div class="flex justify-between items-start mb-4">
            <div class="p-3 bg-primary-container/10 text-primary rounded-lg">
                <span class="material-symbols-outlined text-[28px]">inventory</span>
            </div>
            <div class="flex items-center gap-1 text-success bg-success/10 px-2 py-0.5 rounded-full">
                <span class="material-symbols-outlined text-[14px]">trending_up</span>
                <span class="text-[12px] font-bold">12.5%</span>
            </div>
        </div>
        <p class="text-on-surface-variant font-label-md uppercase tracking-wide">Total Stock Units</p>
        <h3 class="font-metric-display text-[32px] text-on-surface mt-1">1,284,590</h3>
        <p class="text-[12px] text-on-surface-variant mt-2">Across 8 global locations</p>
    </div>
    <div class="bg-surface-container-lowest p-card-padding rounded-xl shadow-sm border border-outline-variant/30 group hover:border-warning/30 transition-all">
        <div class="flex justify-between items-start mb-4">
            <div class="p-3 bg-warning/10 text-warning rounded-lg">
                <span class="material-symbols-outlined text-[28px]">priority_high</span>
            </div>
            <div class="flex items-center gap-1 text-danger bg-danger/10 px-2 py-0.5 rounded-full">
                <span class="material-symbols-outlined text-[14px]">trending_up</span>
                <span class="text-[12px] font-bold">4.2%</span>
            </div>
        </div>
        <p class="text-on-surface-variant font-label-md uppercase tracking-wide">Low Stock Items</p>
        <h3 class="font-metric-display text-[32px] text-on-surface mt-1">142</h3>
        <p class="text-[12px] text-on-surface-variant mt-2">Requires immediate attention</p>
    </div>
    <div class="bg-surface-container-lowest p-card-padding rounded-xl shadow-sm border border-outline-variant/30 group hover:border-danger/30 transition-all">
        <div class="flex justify-between items-start mb-4">
            <div class="p-3 bg-danger/10 text-danger rounded-lg">
                <span class="material-symbols-outlined text-[28px]">block</span>
            </div>
            <div class="flex items-center gap-1 text-success bg-success/10 px-2 py-0.5 rounded-full">
                <span class="material-symbols-outlined text-[14px]">trending_down</span>
                <span class="text-[12px] font-bold">2.1%</span>
            </div>
        </div>
        <p class="text-on-surface-variant font-label-md uppercase tracking-wide">Out of Stock</p>
        <h3 class="font-metric-display text-[32px] text-on-surface mt-1">28</h3>
        <p class="text-[12px] text-on-surface-variant mt-2">Critical replenishment needed</p>
    </div>
</div>

<div class="bg-surface-container-lowest rounded-xl shadow-sm border border-outline-variant/30 overflow-hidden">
    <div class="p-gutter flex justify-between items-center border-b border-outline-variant/30">
        <h3 class="font-headline-md text-headline-md text-on-surface">Inventory Details</h3>
        <div class="flex items-center gap-4">
            <select class="bg-surface-container-low border-outline-variant text-label-md rounded-lg py-1.5 px-4 focus:ring-primary/20">
                <option>All Warehouses</option>
                <option>North America (NA-01)</option>
                <option>Europe (EU-04)</option>
                <option>Asia Pacific (AP-09)</option>
            </select>
            <button class="p-2 text-on-surface-variant hover:bg-surface-container-high rounded-lg">
                <span class="material-symbols-outlined">filter_list</span>
            </button>
        </div>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-surface-gray/50">
                    <th class="px-gutter py-4 font-label-md text-label-md text-on-surface-variant uppercase tracking-wider">Product Name</th>
                    <th class="px-gutter py-4 font-label-md text-label-md text-on-surface-variant uppercase tracking-wider">SKU</th>
                    <th class="px-gutter py-4 font-label-md text-label-md text-on-surface-variant uppercase tracking-wider">Warehouse Location</th>
                    <th class="px-gutter py-4 font-label-md text-label-md text-on-surface-variant uppercase tracking-wider">Current Stock</th>
                    <th class="px-gutter py-4 font-label-md text-label-md text-on-surface-variant uppercase tracking-wider">Reorder Level</th>
                    <th class="px-gutter py-4 font-label-md text-label-md text-on-surface-variant uppercase tracking-wider">Status</th>
                    <th class="px-gutter py-4 font-label-md text-label-md text-on-surface-variant uppercase tracking-wider text-right">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-outline-variant/20">
                <tr class="hover:bg-surface-container/30 transition-colors">
                    <td class="px-gutter py-4">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-lg bg-surface-variant/20 flex items-center justify-center border border-outline-variant/20">
                                <span class="material-symbols-outlined text-primary">smartphone</span>
                            </div>
                            <span class="font-body-md text-body-md text-on-surface font-semibold">CMS Pro X1</span>
                        </div>
                    </td>
                    <td class="px-gutter py-4 font-body-md text-body-md text-on-surface-variant">LPX-2024-001</td>
                    <td class="px-gutter py-4">
                        <span class="font-body-md text-body-md text-on-surface">NA-01 (California)</span>
                    </td>
                    <td class="px-gutter py-4">
                        <span class="font-body-md text-body-md text-on-surface font-bold">1,240</span>
                    </td>
                    <td class="px-gutter py-4 font-body-md text-body-md text-on-surface-variant">250</td>
                    <td class="px-gutter py-4">
                        <div class="flex items-center gap-2">
                            <div class="w-2 h-2 rounded-full bg-success"></div>
                            <span class="text-label-md font-medium text-success">In Stock</span>
                        </div>
                    </td>
                    <td class="px-gutter py-4 text-right">
                        <button class="text-on-surface-variant hover:text-primary transition-colors">
                            <span class="material-symbols-outlined">more_vert</span>
                        </button>
                    </td>
                </tr>
                <tr class="hover:bg-surface-container/30 transition-colors">
                    <td class="px-gutter py-4">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-lg bg-surface-variant/20 flex items-center justify-center border border-outline-variant/20">
                                <span class="material-symbols-outlined text-primary">laptop</span>
                            </div>
                            <span class="font-body-md text-body-md text-on-surface font-semibold">Zenith Book Air</span>
                        </div>
                    </td>
                    <td class="px-gutter py-4 font-body-md text-body-md text-on-surface-variant">ZBA-2024-55</td>
                    <td class="px-gutter py-4">
                        <span class="font-body-md text-body-md text-on-surface">EU-04 (Berlin)</span>
                    </td>
                    <td class="px-gutter py-4">
                        <span class="font-body-md text-body-md text-danger font-bold">42</span>
                    </td>
                    <td class="px-gutter py-4 font-body-md text-body-md text-on-surface-variant">100</td>
                    <td class="px-gutter py-4">
                        <div class="flex items-center gap-2">
                            <div class="w-2 h-2 rounded-full bg-warning animate-pulse"></div>
                            <span class="text-label-md font-medium text-warning">Low Stock</span>
                        </div>
                    </td>
                    <td class="px-gutter py-4 text-right">
                        <button class="text-on-surface-variant hover:text-primary transition-colors">
                            <span class="material-symbols-outlined">more_vert</span>
                        </button>
                    </td>
                </tr>
                <tr class="hover:bg-surface-container/30 transition-colors">
                    <td class="px-gutter py-4">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-lg bg-surface-variant/20 flex items-center justify-center border border-outline-variant/20">
                                <span class="material-symbols-outlined text-primary">headphones</span>
                            </div>
                            <span class="font-body-md text-body-md text-on-surface font-semibold">Sonic Blast Elite</span>
                        </div>
                    </td>
                    <td class="px-gutter py-4 font-body-md text-body-md text-on-surface-variant">SBE-992-H</td>
                    <td class="px-gutter py-4">
                        <span class="font-body-md text-body-md text-on-surface">AP-09 (Singapore)</span>
                    </td>
                    <td class="px-gutter py-4">
                        <span class="font-body-md text-body-md text-danger font-bold">0</span>
                    </td>
                    <td class="px-gutter py-4 font-body-md text-body-md text-on-surface-variant">50</td>
                    <td class="px-gutter py-4">
                        <div class="flex items-center gap-2">
                            <div class="w-2 h-2 rounded-full bg-danger"></div>
                            <span class="text-label-md font-medium text-danger">Out of Stock</span>
                        </div>
                    </td>
                    <td class="px-gutter py-4 text-right">
                        <button class="text-on-surface-variant hover:text-primary transition-colors">
                            <span class="material-symbols-outlined">more_vert</span>
                        </button>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
    <div class="px-gutter py-4 bg-surface-container-low flex justify-between items-center">
        <p class="text-label-md font-label-md text-on-surface-variant">Showing 4 of 28 items</p>
        <div class="flex gap-2">
            <button class="px-3 py-1 bg-white border border-outline-variant rounded text-label-md hover:bg-surface-gray transition-all disabled:opacity-50" disabled="">Previous</button>
            <button class="px-3 py-1 bg-primary text-white rounded text-label-md shadow-sm">1</button>
            <button class="px-3 py-1 bg-white border border-outline-variant rounded text-label-md hover:bg-surface-gray transition-all">2</button>
            <button class="px-3 py-1 bg-white border border-outline-variant rounded text-label-md hover:bg-surface-gray transition-all">Next</button>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-container-gap">
    <div class="lg:col-span-2 bg-surface-container-lowest rounded-xl shadow-sm border border-outline-variant/30 overflow-hidden relative">
        <div class="absolute top-4 left-4 z-10 bg-white/90 backdrop-blur-md p-3 rounded-lg border border-outline-variant/30 shadow-sm">
            <h4 class="font-headline-md text-headline-md text-on-surface">Regional Distribution</h4>
            <p class="text-[10px] text-on-surface-variant uppercase">Global Stock Density</p>
        </div>
        <div class="w-full h-80 flex items-center justify-center" style="background:linear-gradient(135deg, #f8f9fa, #e9ecef);">
            <div class="flex flex-col items-center">
                <span class="material-symbols-outlined text-primary text-[48px] animate-bounce">location_on</span>
                <div class="bg-white px-3 py-1 rounded-full shadow-lg border border-outline-variant font-label-md text-label-md">NA-01 Primary Hub</div>
            </div>
        </div>
    </div>
    <div class="bg-surface-container-lowest rounded-xl shadow-sm border border-outline-variant/30 p-card-padding">
        <h3 class="font-headline-md text-headline-md text-on-surface mb-4">Stock Movements</h3>
        <div class="space-y-4">
            <div class="flex gap-3 relative before:absolute before:left-[11px] before:top-6 before:bottom-0 before:w-px before:bg-outline-variant/30">
                <div class="w-6 h-6 rounded-full bg-success/20 flex items-center justify-center shrink-0 z-10">
                    <span class="material-symbols-outlined text-[14px] text-success">add</span>
                </div>
                <div>
                    <p class="text-body-md text-on-surface font-semibold">Incoming: CMS Pro X1</p>
                    <p class="text-label-md text-on-surface-variant">+500 units to NA-01 Warehouse</p>
                    <p class="text-[10px] text-on-surface-variant uppercase mt-1">2 hours ago</p>
                </div>
            </div>
            <div class="flex gap-3 relative before:absolute before:left-[11px] before:top-6 before:bottom-0 before:w-px before:bg-outline-variant/30">
                <div class="w-6 h-6 rounded-full bg-danger/20 flex items-center justify-center shrink-0 z-10">
                    <span class="material-symbols-outlined text-[14px] text-danger">remove</span>
                </div>
                <div>
                    <p class="text-body-md text-on-surface font-semibold">Outgoing: Zenith Book Air</p>
                    <p class="text-label-md text-on-surface-variant">-120 units from EU-04 Distribution</p>
                    <p class="text-[10px] text-on-surface-variant uppercase mt-1">5 hours ago</p>
                </div>
            </div>
            <div class="flex gap-3">
                <div class="w-6 h-6 rounded-full bg-tertiary/20 flex items-center justify-center shrink-0 z-10">
                    <span class="material-symbols-outlined text-[14px] text-tertiary">sync</span>
                </div>
                <div>
                    <p class="text-body-md text-on-surface font-semibold">Transfer: Sonic Blast Elite</p>
                    <p class="text-label-md text-on-surface-variant">Moving 200 units AP-09 to AU-01</p>
                    <p class="text-[10px] text-on-surface-variant uppercase mt-1">Yesterday, 14:30</p>
                </div>
            </div>
        </div>
        <button class="w-full mt-6 py-2 bg-surface-container text-primary font-label-md rounded-lg hover:bg-primary/5 transition-all">
            View Audit Log
        </button>
    </div>
</div>
@endsection