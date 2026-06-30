@extends('layouts.app')

@section('title', 'Role Management')

@section('content')
<div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
    <div>
        <h1 class="font-headline-xl text-headline-xl text-on-surface">Role Management</h1>
        <p class="text-body-md text-on-surface-variant">Define and manage access levels across the organization.</p>
    </div>
    <button class="flex items-center gap-2 bg-primary-container text-white px-6 py-2.5 rounded-lg font-headline-md text-headline-md hover:bg-primary transition-all active:scale-95 shadow-sm">
        <span class="material-symbols-outlined">add_circle</span>
        Create Role
    </button>
</div>

<div class="grid grid-cols-1 md:grid-cols-3 gap-container-gap">
    <div class="bg-white p-card-padding rounded-xl shadow-subtle border border-surface-container flex items-center gap-4">
        <div class="w-12 h-12 rounded-lg bg-primary/10 flex items-center justify-center text-primary">
            <span class="material-symbols-outlined text-[28px]">shield_person</span>
        </div>
        <div>
            <p class="text-on-surface-variant font-label-md text-label-md">Total Roles</p>
            <div class="flex items-baseline gap-2">
                <span class="font-metric-display text-metric-display text-on-surface">12</span>
                <span class="text-success font-label-sm text-label-sm flex items-center">+2 this month</span>
            </div>
        </div>
    </div>
    <div class="bg-white p-card-padding rounded-xl shadow-subtle border border-surface-container flex items-center gap-4">
        <div class="w-12 h-12 rounded-lg bg-tertiary/10 flex items-center justify-center text-tertiary">
            <span class="material-symbols-outlined text-[28px]">group</span>
        </div>
        <div>
            <p class="text-on-surface-variant font-label-md text-label-md">Active Users</p>
            <div class="flex items-baseline gap-2">
                <span class="font-metric-display text-metric-display text-on-surface">1,284</span>
                <span class="text-success font-label-sm text-label-sm flex items-center">+12%</span>
            </div>
        </div>
    </div>
    <div class="bg-white p-card-padding rounded-xl shadow-subtle border border-surface-container flex items-center gap-4">
        <div class="w-12 h-12 rounded-lg bg-warning/10 flex items-center justify-center text-warning">
            <span class="material-symbols-outlined text-[28px]">recent_actors</span>
        </div>
        <div>
            <p class="text-on-surface-variant font-label-md text-label-md">Recently Added</p>
            <span class="font-metric-display text-metric-display text-on-surface">Warehouse Manager</span>
        </div>
    </div>
</div>

<div class="bg-white rounded-xl shadow-subtle border border-surface-container overflow-hidden">
    <div class="p-6 border-b border-surface-container flex justify-between items-center bg-surface-container-low/30">
        <h3 class="font-headline-md text-headline-md text-on-surface">System Roles</h3>
        <div class="flex gap-2">
            <button class="px-4 py-1.5 border border-outline-variant rounded-md font-label-md text-label-md text-on-surface-variant hover:bg-surface-gray transition-colors">
                Export PDF
            </button>
            <button class="px-4 py-1.5 border border-outline-variant rounded-md font-label-md text-label-md text-on-surface-variant hover:bg-surface-gray transition-colors">
                Filter
            </button>
        </div>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-surface-gray/50 border-b border-surface-container">
                    <th class="px-6 py-4 font-label-sm text-label-sm text-on-surface-variant uppercase tracking-wider">Role Name</th>
                    <th class="px-6 py-4 font-label-sm text-label-sm text-on-surface-variant uppercase tracking-wider">Description</th>
                    <th class="px-6 py-4 font-label-sm text-label-sm text-on-surface-variant uppercase tracking-wider text-center">Users</th>
                    <th class="px-6 py-4 font-label-sm text-label-sm text-on-surface-variant uppercase tracking-wider">Date Created</th>
                    <th class="px-6 py-4 font-label-sm text-label-sm text-on-surface-variant uppercase tracking-wider text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-surface-container">
                <tr class="hover:bg-surface-container-low/20 transition-colors group">
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-3">
                            <div class="w-2 h-2 rounded-full bg-primary"></div>
                            <span class="font-headline-md text-headline-md text-on-surface">Super Admin</span>
                        </div>
                    </td>
                    <td class="px-6 py-4 max-w-xs">
                        <p class="font-body-md text-body-md text-on-surface-variant truncate">Full system access with no restrictions.</p>
                    </td>
                    <td class="px-6 py-4 text-center">
                        <span class="inline-flex items-center justify-center px-2.5 py-0.5 rounded-full bg-secondary-container text-on-secondary-container font-label-md text-label-md">3</span>
                    </td>
                    <td class="px-6 py-4 font-body-md text-body-md text-on-surface-variant">Oct 12, 2023</td>
                    <td class="px-6 py-4 text-right">
                        <div class="flex justify-end gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                            <button class="p-1.5 text-tertiary hover:bg-tertiary/10 rounded transition-colors" title="Manage Permissions"><span class="material-symbols-outlined">rule</span></button>
                            <button class="p-1.5 text-on-surface-variant hover:bg-surface-gray rounded transition-colors" title="Edit Role"><span class="material-symbols-outlined">edit</span></button>
                            <button class="p-1.5 text-danger hover:bg-danger/10 rounded transition-colors" title="Delete Role"><span class="material-symbols-outlined">delete</span></button>
                        </div>
                    </td>
                </tr>
                <tr class="hover:bg-surface-container-low/20 transition-colors group">
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-3">
                            <div class="w-2 h-2 rounded-full bg-success"></div>
                            <span class="font-headline-md text-headline-md text-on-surface">Editor</span>
                        </div>
                    </td>
                    <td class="px-6 py-4 max-w-xs">
                        <p class="font-body-md text-body-md text-on-surface-variant truncate">Manage products, categories, and inventory details.</p>
                    </td>
                    <td class="px-6 py-4 text-center">
                        <span class="inline-flex items-center justify-center px-2.5 py-0.5 rounded-full bg-secondary-container text-on-secondary-container font-label-md text-label-md">24</span>
                    </td>
                    <td class="px-6 py-4 font-body-md text-body-md text-on-surface-variant">Nov 05, 2023</td>
                    <td class="px-6 py-4 text-right">
                        <div class="flex justify-end gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                            <button class="p-1.5 text-tertiary hover:bg-tertiary/10 rounded transition-colors" title="Manage Permissions"><span class="material-symbols-outlined">rule</span></button>
                            <button class="p-1.5 text-on-surface-variant hover:bg-surface-gray rounded transition-colors" title="Edit Role"><span class="material-symbols-outlined">edit</span></button>
                            <button class="p-1.5 text-danger hover:bg-danger/10 rounded transition-colors" title="Delete Role"><span class="material-symbols-outlined">delete</span></button>
                        </div>
                    </td>
                </tr>
                <tr class="hover:bg-surface-container-low/20 transition-colors group">
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-3">
                            <div class="w-2 h-2 rounded-full bg-warning"></div>
                            <span class="font-headline-md text-headline-md text-on-surface">Warehouse Manager</span>
                        </div>
                    </td>
                    <td class="px-6 py-4 max-w-xs">
                        <p class="font-body-md text-body-md text-on-surface-variant truncate">Focus on logistics, stock levels, and order fulfillment.</p>
                    </td>
                    <td class="px-6 py-4 text-center">
                        <span class="inline-flex items-center justify-center px-2.5 py-0.5 rounded-full bg-secondary-container text-on-secondary-container font-label-md text-label-md">8</span>
                    </td>
                    <td class="px-6 py-4 font-body-md text-body-md text-on-surface-variant">Jan 18, 2024</td>
                    <td class="px-6 py-4 text-right">
                        <div class="flex justify-end gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                            <button class="p-1.5 text-tertiary hover:bg-tertiary/10 rounded transition-colors" title="Manage Permissions"><span class="material-symbols-outlined">rule</span></button>
                            <button class="p-1.5 text-on-surface-variant hover:bg-surface-gray rounded transition-colors" title="Edit Role"><span class="material-symbols-outlined">edit</span></button>
                            <button class="p-1.5 text-danger hover:bg-danger/10 rounded transition-colors" title="Delete Role"><span class="material-symbols-outlined">delete</span></button>
                        </div>
                    </td>
                </tr>
                <tr class="hover:bg-surface-container-low/20 transition-colors group">
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-3">
                            <div class="w-2 h-2 rounded-full bg-tertiary"></div>
                            <span class="font-headline-md text-headline-md text-on-surface">Support</span>
                        </div>
                    </td>
                    <td class="px-6 py-4 max-w-xs">
                        <p class="font-body-md text-body-md text-on-surface-variant truncate">Handle customer inquiries and order status tracking.</p>
                    </td>
                    <td class="px-6 py-4 text-center">
                        <span class="inline-flex items-center justify-center px-2.5 py-0.5 rounded-full bg-secondary-container text-on-secondary-container font-label-md text-label-md">15</span>
                    </td>
                    <td class="px-6 py-4 font-body-md text-body-md text-on-surface-variant">Feb 02, 2024</td>
                    <td class="px-6 py-4 text-right">
                        <div class="flex justify-end gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                            <button class="p-1.5 text-tertiary hover:bg-tertiary/10 rounded transition-colors" title="Manage Permissions"><span class="material-symbols-outlined">rule</span></button>
                            <button class="p-1.5 text-on-surface-variant hover:bg-surface-gray rounded transition-colors" title="Edit Role"><span class="material-symbols-outlined">edit</span></button>
                            <button class="p-1.5 text-danger hover:bg-danger/10 rounded transition-colors" title="Delete Role"><span class="material-symbols-outlined">delete</span></button>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
    <div class="p-6 border-t border-surface-container flex items-center justify-between bg-surface-container-low/10">
        <p class="font-body-md text-body-md text-on-surface-variant">Showing 1 to 4 of 12 roles</p>
        <div class="flex gap-1">
            <button class="w-8 h-8 flex items-center justify-center rounded border border-outline-variant hover:bg-surface-gray disabled:opacity-30" disabled="">
                <span class="material-symbols-outlined text-[18px]">chevron_left</span>
            </button>
            <button class="w-8 h-8 flex items-center justify-center rounded bg-primary-container text-on-primary-container font-label-md font-bold text-label-sm">1</button>
            <button class="w-8 h-8 flex items-center justify-center rounded border border-outline-variant hover:bg-surface-gray font-label-sm">2</button>
            <button class="w-8 h-8 flex items-center justify-center rounded border border-outline-variant hover:bg-surface-gray font-label-sm">3</button>
            <button class="w-8 h-8 flex items-center justify-center rounded border border-outline-variant hover:bg-surface-gray">
                <span class="material-symbols-outlined text-[18px]">chevron_right</span>
            </button>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 gap-container-gap">
    <div class="relative overflow-hidden bg-primary-container text-white p-8 rounded-xl flex items-center justify-between">
        <div class="relative z-10 max-w-sm">
            <h4 class="font-headline-lg text-headline-lg mb-2">Need a custom workflow?</h4>
            <p class="font-body-md opacity-90 mb-4">You can combine existing permissions to create a hybrid role specifically for seasonal contractors.</p>
            <button class="bg-white text-primary px-6 py-2 rounded-lg font-label-md hover:bg-surface-bright transition-colors">
                Explore Permissions
            </button>
        </div>
        <div class="absolute -right-10 -bottom-10 opacity-20 transform rotate-12 pointer-events-none">
            <span class="material-symbols-outlined text-[200px]" style="font-variation-settings: 'FILL' 1;">admin_panel_settings</span>
        </div>
    </div>
    <div class="bg-white p-8 rounded-xl border border-outline-variant shadow-subtle flex flex-col justify-center">
        <h4 class="font-headline-lg text-headline-lg text-on-surface mb-4">Role Integrity Logs</h4>
        <div class="space-y-4">
            <div class="flex items-start gap-4">
                <div class="mt-1 w-2 h-2 rounded-full bg-success"></div>
                <div>
                    <p class="font-label-md text-label-md text-on-surface">Super Admin modified Editor permissions</p>
                    <p class="font-label-sm text-label-sm text-on-surface-variant">2 hours ago</p>
                </div>
            </div>
            <div class="flex items-start gap-4">
                <div class="mt-1 w-2 h-2 rounded-full bg-primary"></div>
                <div>
                    <p class="font-label-md text-label-md text-on-surface">New role "Warehouse Manager" successfully created</p>
                    <p class="font-label-sm text-label-sm text-on-surface-variant">1 day ago</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection