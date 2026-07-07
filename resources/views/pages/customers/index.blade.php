@extends('layouts.app')

@section('title', 'Customers')

@section('content')
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="font-headline-lg text-headline-lg text-on-surface">Customers</h1>
            <nav class="flex items-center gap-2 text-body-md text-on-surface-variant mt-1">
                <a href="{{ route('dashboard') }}" class="text-primary hover:underline">Dashboard</a>
                <span class="material-symbols-outlined text-[16px]">chevron_right</span>
                <span>Customers</span>
            </nav>
        </div>
        <a href="{{ route('customers.create') }}" class="flex items-center gap-2 px-4 py-2 bg-primary text-white rounded-lg font-label-md hover:opacity-90 transition-all shadow-sm">
            <span class="material-symbols-outlined text-[18px]">add</span> Add Customer
        </a>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-container-gap mb-8">
        <div class="bg-white p-card-padding rounded-xl shadow-sm border border-outline-variant/30">
            <div class="flex justify-between items-start mb-4">
                <div class="p-2.5 bg-primary/10 text-primary rounded-lg">
                    <span class="material-symbols-outlined">group</span>
                </div>
            </div>
            <p class="text-on-surface-variant font-label-md uppercase tracking-wider">Total Customers</p>
            <h3 class="font-metric-display text-metric-display">{{ number_format($customers->total()) }}</h3>
        </div>
        <div class="bg-white p-card-padding rounded-xl shadow-sm border border-outline-variant/30">
            <div class="flex justify-between items-start mb-4">
                <div class="p-2.5 bg-tertiary/10 text-tertiary rounded-lg">
                    <span class="material-symbols-outlined">how_to_reg</span>
                </div>
            </div>
            <p class="text-on-surface-variant font-label-md uppercase tracking-wider">Active Customers</p>
            <h3 class="font-metric-display text-metric-display">{{ number_format($customers->where('deleted', false)->count()) }}</h3>
        </div>
        <div class="bg-white p-card-padding rounded-xl shadow-sm border border-outline-variant/30">
            <div class="flex justify-between items-start mb-4">
                <div class="p-2.5 bg-warning/10 text-warning rounded-lg">
                    <span class="material-symbols-outlined">person_add_alt</span>
                </div>
            </div>
            <p class="text-on-surface-variant font-label-md uppercase tracking-wider">New This Month</p>
            <h3 class="font-metric-display text-metric-display">{{ number_format($customers->where('created_at', '>=', now()->startOfMonth())->count()) }}</h3>
        </div>
        <div class="bg-white p-card-padding rounded-xl shadow-sm border border-outline-variant/30">
            <div class="flex justify-between items-start mb-4">
                <div class="p-2.5 bg-danger/10 text-danger rounded-lg">
                    <span class="material-symbols-outlined">person_off</span>
                </div>
            </div>
            <p class="text-on-surface-variant font-label-md uppercase tracking-wider">Inactive</p>
            <h3 class="font-metric-display text-metric-display">{{ number_format($customers->where('deleted', true)->count()) }}</h3>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-outline-variant/30 overflow-hidden">
        <div class="p-gutter border-b border-outline-variant flex flex-col md:flex-row justify-between items-center gap-4 bg-surface-container-lowest">
            <form method="GET" class="flex items-center gap-3 w-full md:w-auto">
                <div class="relative w-full md:w-80">
                    <input name="search" value="{{ request('search') }}" class="w-full border border-outline-variant rounded-lg px-10 py-2 text-body-md focus:ring-1 focus:ring-primary focus:border-primary transition-all" placeholder="Search customers..."/>
                    <span class="material-symbols-outlined absolute left-3 top-2.5 text-on-surface-variant text-[20px]">search</span>
                </div>
                <select name="status" class="text-label-md font-label-md bg-white border border-outline-variant rounded-lg px-4 py-2 pr-10 focus:ring-1 focus:ring-primary">
                    <option value="">Status: All</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                </select>
                <button type="submit" class="px-4 py-2 bg-primary text-white rounded-lg font-label-md hover:opacity-90 transition-all shadow-sm">Filter</button>
                <a href="{{ route('customers.index') }}" class="px-4 py-2 border border-outline-variant text-on-surface rounded-lg font-label-md hover:bg-surface-container transition-colors">Clear</a>
            </form>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead class="bg-surface-gray">
                    <tr>
                        <th class="px-gutter py-4 font-headline-md text-[13px]">
                            <div class="flex items-center gap-2">
                                Customer <span class="material-symbols-outlined text-[14px]">unfold_more</span>
                            </div>
                        </th>
                        <th class="px-gutter py-4 font-headline-md text-[13px]">Phone</th>
                        <th class="px-gutter py-4 font-headline-md text-[13px]">Orders</th>
                        <th class="px-gutter py-4 font-headline-md text-[13px]">Status</th>
                        <th class="px-gutter py-4 font-headline-md text-[13px] text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-outline-variant">
                    @forelse($customers as $customer)
                    <tr class="hover:bg-surface-container-low transition-colors group">
                        <td class="px-gutter py-4">
                            <div class="flex items-center gap-3">
                                <div class="h-10 w-10 rounded-full bg-surface-gray flex items-center justify-center text-on-surface-variant font-bold text-label-md">
                                    {{ substr($customer->name, 0, 1) }}
                                </div>
                                <div>
                                    <p class="font-headline-md text-on-surface">{{ $customer->name }}</p>
                                    <p class="text-label-sm text-on-surface-variant lowercase">{{ $customer->email }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-gutter py-4 text-body-md">{{ $customer->phone ?? '-' }}</td>
                        <td class="px-gutter py-4 text-body-md font-bold">{{ $customer->orders->count() }}</td>
                        <td class="px-gutter py-4">
                            @if($customer->deleted)
                            <div class="flex items-center gap-1.5 text-danger">
                                <span class="h-2 w-2 rounded-full bg-danger"></span>
                                <span class="text-label-md font-bold uppercase tracking-wider">Inactive</span>
                            </div>
                            @else
                            <div class="flex items-center gap-1.5 text-success">
                                <span class="h-2 w-2 rounded-full bg-success"></span>
                                <span class="text-label-md font-bold uppercase tracking-wider">Active</span>
                            </div>
                            @endif
                        </td>
                        <td class="px-gutter py-4">
                            <div class="flex justify-end gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                <a href="{{ route('customers.show', $customer->id) }}" class="p-1.5 text-secondary hover:text-primary transition-colors"><span class="material-symbols-outlined">visibility</span></a>
                                <a href="{{ route('customers.edit', $customer->id) }}" class="p-1.5 text-secondary hover:text-primary transition-colors"><span class="material-symbols-outlined">edit</span></a>
                                <form action="{{ route('customers.destroy', $customer->id) }}" method="POST" class="inline" onsubmit="return confirm('Delete this customer?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="p-1.5 text-secondary hover:text-danger transition-colors"><span class="material-symbols-outlined">delete</span></button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-gutter py-8 text-center text-on-surface-variant">No customers found</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-gutter border-t border-outline-variant flex flex-col md:flex-row justify-between items-center gap-4 bg-surface-container-lowest">
            <p class="text-label-md text-on-surface-variant">Showing {{ $customers->firstItem() ?? 0 }}-{{ $customers->lastItem() ?? 0 }} of {{ $customers->total() }} customers</p>
            <div class="flex items-center gap-1">
                {{ $customers->links() }}
            </div>
        </div>
    </div>
@endsection
