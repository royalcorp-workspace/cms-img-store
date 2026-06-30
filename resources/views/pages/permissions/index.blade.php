@extends('layouts.app')

@section('title', 'Permissions Management')

@section('content')
<div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
    <div>
        <h1 class="font-headline-lg text-headline-lg text-on-surface">Role Permissions</h1>
        <p class="text-body-md text-on-surface-variant mt-1">Configure access levels and capabilities for each role.</p>
    </div>
    <div class="flex items-center gap-3">
        <button class="flex items-center gap-2 px-4 py-2 border border-outline-variant text-on-surface-variant rounded-lg font-label-md hover:bg-surface-container transition-colors">
            Discard
        </button>
        <button class="flex items-center gap-2 px-5 py-2 bg-primary text-white rounded-lg font-label-md hover:opacity-90 transition-all shadow-sm">
            <span class="material-symbols-outlined text-[18px]">save</span>
            Save Changes
        </button>
    </div>
</div>

<div class="bg-primary-container/10 border border-primary-container/30 rounded-xl px-5 py-4 mb-8 flex items-start gap-4">
    <span class="material-symbols-outlined text-primary text-[24px] mt-0.5">info</span>
    <div>
        <p class="font-body-md font-medium text-on-surface mb-1">Updating permissions will affect 14 active users.</p>
        <p class="text-body-md text-secondary">Changes are applied immediately upon saving. Users may need to refresh their session to see updated access rights.</p>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-container-gap mb-8">
    <div class="bg-white rounded-xl shadow-sm border border-outline-variant/30 overflow-hidden">
        <div class="p-gutter border-b border-outline-variant flex justify-between items-center bg-surface-container-low/30">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-primary/10 flex items-center justify-center">
                    <span class="material-symbols-outlined text-primary text-[22px]">inventory_2</span>
                </div>
                <div>
                    <h3 class="font-headline-md text-headline-md text-on-surface">Products Management</h3>
                    <p class="text-label-sm text-on-surface-variant">Catalog & Listings</p>
                </div>
            </div>
            <label class="flex items-center gap-2 cursor-pointer">
                <input type="checkbox" class="rounded border-outline-variant text-primary focus:ring-primary/20" checked>
                <span class="text-label-sm text-on-surface-variant">Select All</span>
            </label>
        </div>
        <div class="p-gutter space-y-1">
            <label class="flex items-center justify-between py-2 cursor-pointer group">
                <div class="flex items-center gap-3">
                    <input type="checkbox" class="rounded border-outline-variant text-primary focus:ring-primary/20" checked>
                    <span class="font-body-md text-body-md text-on-surface font-medium group-hover:text-primary transition-colors">View Products</span>
                </div>
                <span class="px-2.5 py-0.5 bg-success/10 text-success rounded-full text-label-sm font-label-sm">Standard</span>
            </label>
            <label class="flex items-center justify-between py-2 cursor-pointer group">
                <div class="flex items-center gap-3">
                    <input type="checkbox" class="rounded border-outline-variant text-primary focus:ring-primary/20" checked>
                    <span class="font-body-md text-body-md text-on-surface font-medium group-hover:text-primary transition-colors">Create Products</span>
                </div>
            </label>
            <label class="flex items-center justify-between py-2 cursor-pointer group">
                <div class="flex items-center gap-3">
                    <input type="checkbox" class="rounded border-outline-variant text-primary focus:ring-primary/20" checked>
                    <span class="font-body-md text-body-md text-on-surface font-medium group-hover:text-primary transition-colors">Edit Products</span>
                </div>
            </label>
            <label class="flex items-center justify-between py-2 cursor-pointer group">
                <div class="flex items-center gap-3">
                    <input type="checkbox" class="rounded border-outline-variant text-primary focus:ring-primary/20">
                    <span class="font-body-md text-body-md text-on-surface-variant font-medium group-hover:text-primary transition-colors">Delete Products</span>
                </div>
                <span class="px-2.5 py-0.5 bg-danger/10 text-danger rounded-full text-label-sm font-label-sm">Restricted</span>
            </label>
            <label class="flex items-center justify-between py-2 cursor-pointer group">
                <div class="flex items-center gap-3">
                    <input type="checkbox" class="rounded border-outline-variant text-primary focus:ring-primary/20" checked>
                    <span class="font-body-md text-body-md text-on-surface font-medium group-hover:text-primary transition-colors">Export Catalog</span>
                </div>
            </label>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-outline-variant/30 overflow-hidden">
        <div class="p-gutter border-b border-outline-variant flex justify-between items-center bg-surface-container-low/30">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-tertiary/10 flex items-center justify-center">
                    <span class="material-symbols-outlined text-tertiary text-[22px]">shopping_cart</span>
                </div>
                <div>
                    <h3 class="font-headline-md text-headline-md text-on-surface">Orders & Transactions</h3>
                    <p class="text-label-sm text-on-surface-variant">Fulfillment & Revenue</p>
                </div>
            </div>
            <label class="flex items-center gap-2 cursor-pointer">
                <input type="checkbox" class="rounded border-outline-variant text-primary focus:ring-primary/20" checked>
                <span class="text-label-sm text-on-surface-variant">Select All</span>
            </label>
        </div>
        <div class="p-gutter space-y-1">
            <label class="flex items-center justify-between py-2 cursor-pointer group">
                <div class="flex items-center gap-3">
                    <input type="checkbox" class="rounded border-outline-variant text-primary focus:ring-primary/20" checked>
                    <span class="font-body-md text-body-md text-on-surface font-medium group-hover:text-primary transition-colors">View Orders</span>
                </div>
            </label>
            <label class="flex items-center justify-between py-2 cursor-pointer group">
                <div class="flex items-center gap-3">
                    <input type="checkbox" class="rounded border-outline-variant text-primary focus:ring-primary/20" checked>
                    <span class="font-body-md text-body-md text-on-surface font-medium group-hover:text-primary transition-colors">Manage Status</span>
                </div>
            </label>
            <label class="flex items-center justify-between py-2 cursor-pointer group">
                <div class="flex items-center gap-3">
                    <input type="checkbox" class="rounded border-outline-variant text-primary focus:ring-primary/20" checked>
                    <span class="font-body-md text-body-md text-on-surface font-medium group-hover:text-primary transition-colors">Issue Refunds</span>
                </div>
                <span class="px-2.5 py-0.5 bg-warning/10 text-warning rounded-full text-label-sm font-label-sm">Privileged</span>
            </label>
            <label class="flex items-center justify-between py-2 cursor-pointer group">
                <div class="flex items-center gap-3">
                    <input type="checkbox" class="rounded border-outline-variant text-primary focus:ring-primary/20">
                    <span class="font-body-md text-body-md text-on-surface-variant font-medium group-hover:text-primary transition-colors">Delete Orders</span>
                </div>
            </label>
            <label class="flex items-center justify-between py-2 cursor-pointer group">
                <div class="flex items-center gap-3">
                    <input type="checkbox" class="rounded border-outline-variant text-primary focus:ring-primary/20">
                    <span class="font-body-md text-body-md text-on-surface-variant font-medium group-hover:text-primary transition-colors">Export Reports</span>
                </div>
            </label>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-outline-variant/30 overflow-hidden">
        <div class="p-gutter border-b border-outline-variant flex justify-between items-center bg-surface-container-low/30">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-secondary/10 flex items-center justify-center">
                    <span class="material-symbols-outlined text-secondary text-[22px]">person</span>
                </div>
                <div>
                    <h3 class="font-headline-md text-headline-md text-on-surface">Customer Management</h3>
                    <p class="text-label-sm text-on-surface-variant">CRM & Insights</p>
                </div>
            </div>
            <label class="flex items-center gap-2 cursor-pointer">
                <input type="checkbox" class="rounded border-outline-variant text-primary focus:ring-primary/20" checked>
                <span class="text-label-sm text-on-surface-variant">Select All</span>
            </label>
        </div>
        <div class="p-gutter space-y-1">
            <label class="flex items-center justify-between py-2 cursor-pointer group">
                <div class="flex items-center gap-3">
                    <input type="checkbox" class="rounded border-outline-variant text-primary focus:ring-primary/20" checked>
                    <span class="font-body-md text-body-md text-on-surface font-medium group-hover:text-primary transition-colors">View Customer Data</span>
                </div>
            </label>
            <label class="flex items-center justify-between py-2 cursor-pointer group">
                <div class="flex items-center gap-3">
                    <input type="checkbox" class="rounded border-outline-variant text-primary focus:ring-primary/20">
                    <span class="font-body-md text-body-md text-on-surface-variant font-medium group-hover:text-primary transition-colors">Modify Profiles</span>
                </div>
            </label>
            <label class="flex items-center justify-between py-2 cursor-pointer group">
                <div class="flex items-center gap-3">
                    <input type="checkbox" class="rounded border-outline-variant text-primary focus:ring-primary/20">
                    <span class="font-body-md text-body-md text-on-surface-variant font-medium group-hover:text-primary transition-colors">Contact Access</span>
                </div>
            </label>
            <label class="flex items-center justify-between py-2 cursor-pointer group">
                <div class="flex items-center gap-3">
                    <input type="checkbox" class="rounded border-outline-variant text-primary focus:ring-primary/20">
                    <span class="font-body-md text-body-md text-on-surface-variant font-medium group-hover:text-primary transition-colors">Export Data</span>
                </div>
                <span class="px-2.5 py-0.5 bg-danger/10 text-danger rounded-full text-label-sm font-label-sm">Sensitive</span>
            </label>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-outline-variant/30 overflow-hidden">
        <div class="p-gutter border-b border-outline-variant flex justify-between items-center bg-surface-container-low/30">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-primary/10 flex items-center justify-center">
                    <span class="material-symbols-outlined text-primary text-[22px]">warehouse</span>
                </div>
                <div>
                    <h3 class="font-headline-md text-headline-md text-on-surface">Inventory & Stock</h3>
                    <p class="text-label-sm text-on-surface-variant">Supply Chain</p>
                </div>
            </div>
            <label class="flex items-center gap-2 cursor-pointer">
                <input type="checkbox" class="rounded border-outline-variant text-primary focus:ring-primary/20" checked>
                <span class="text-label-sm text-on-surface-variant">Select All</span>
            </label>
        </div>
        <div class="p-gutter space-y-1">
            <label class="flex items-center justify-between py-2 cursor-pointer group">
                <div class="flex items-center gap-3">
                    <input type="checkbox" class="rounded border-outline-variant text-primary focus:ring-primary/20" checked>
                    <span class="font-body-md text-body-md text-on-surface font-medium group-hover:text-primary transition-colors">Monitor Stock</span>
                </div>
            </label>
            <label class="flex items-center justify-between py-2 cursor-pointer group">
                <div class="flex items-center gap-3">
                    <input type="checkbox" class="rounded border-outline-variant text-primary focus:ring-primary/20" checked>
                    <span class="font-body-md text-body-md text-on-surface font-medium group-hover:text-primary transition-colors">Adjust Levels</span>
                </div>
            </label>
            <label class="flex items-center justify-between py-2 cursor-pointer group">
                <div class="flex items-center gap-3">
                    <input type="checkbox" class="rounded border-outline-variant text-primary focus:ring-primary/20" checked>
                    <span class="font-body-md text-body-md text-on-surface font-medium group-hover:text-primary transition-colors">Transfer Units</span>
                </div>
            </label>
            <label class="flex items-center justify-between py-2 cursor-pointer group">
                <div class="flex items-center gap-3">
                    <input type="checkbox" class="rounded border-outline-variant text-primary focus:ring-primary/20">
                    <span class="font-body-md text-body-md text-on-surface-variant font-medium group-hover:text-primary transition-colors">Audit Logs</span>
                </div>
            </label>
        </div>
    </div>
</div>
@endsection
