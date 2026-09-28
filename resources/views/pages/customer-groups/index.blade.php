@extends('layouts.app')

@section('title', 'Customer Groups')

@section('content')
<div class="flex flex-col md:flex-row md:items-end justify-between gap-4 mb-6">
    <div>
        <h3 class="font-headline-xl text-headline-xl text-on-surface">Customer Groups</h3>
        <nav class="flex items-center gap-2 text-body-md text-on-surface-variant mt-1">
            <a href="{{ route('dashboard') }}" class="hover:text-primary transition-colors">Dashboard</a>
            <span class="material-symbols-outlined text-[16px]">chevron_right</span>
            <a href="{{ route('customers.index') }}" class="hover:text-primary transition-colors">Customers</a>
            <span class="material-symbols-outlined text-[16px]">chevron_right</span>
            <span>Customer Groups</span>
        </nav>
        <p class="text-body-md text-secondary mt-1">Kelola group pelanggan (Karyawan, Reseller, Member Khusus) untuk promosi dan voucher khusus.</p>
    </div>
    <a href="{{ route('customer-groups.create') }}" class="bg-primary hover:bg-primary-container text-white px-6 py-3 rounded-xl font-headline-md text-headline-md flex items-center gap-2 transition-all shadow-md active:scale-95">
        <span class="material-symbols-outlined">add_circle</span> Tambah Group
    </a>
</div>

@include('layouts.partials.customer-submenu')

<div class="bg-white rounded-xl shadow-sm border border-outline-variant/30 overflow-hidden">
    <div class="px-6 py-4 border-b border-outline-variant/50 flex flex-col sm:flex-row justify-between items-center gap-4">
        <h4 class="font-headline-md text-headline-md text-on-surface">Daftar Customer Group</h4>
        <form method="GET" class="flex items-center gap-2 w-full sm:w-auto">
            <input name="search" value="{{ request('search') }}" class="px-4 py-2 border border-outline-variant rounded-lg text-body-md focus:ring-1 focus:ring-primary focus:border-primary" placeholder="Cari nama group..."/>
            <button type="submit" class="px-4 py-2 bg-primary text-white rounded-lg font-label-md hover:opacity-90 transition-all shadow-sm">Filter</button>
            <a href="{{ route('customer-groups.index') }}" class="px-4 py-2 border border-outline-variant text-on-surface rounded-lg font-label-md hover:bg-surface-container transition-colors">Clear</a>
        </form>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-left">
            <thead class="bg-surface-gray">
                <tr>
                    <th class="px-6 py-4 text-label-sm font-label-sm text-secondary uppercase tracking-widest">Nama Group</th>
                    <th class="px-6 py-4 text-label-sm font-label-sm text-secondary uppercase tracking-widest">Keterangan</th>
                    <th class="px-6 py-4 text-label-sm font-label-sm text-secondary uppercase tracking-widest text-center">Diskon Group</th>
                    <th class="px-6 py-4 text-label-sm font-label-sm text-secondary uppercase tracking-widest text-center">Jumlah Member</th>
                    <th class="px-6 py-4 text-label-sm font-label-sm text-secondary uppercase tracking-widest text-center">Status</th>
                    <th class="px-6 py-4 text-label-sm font-label-sm text-secondary uppercase tracking-widest text-center">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-outline-variant/20">
                @forelse($groups as $group)
                <tr class="hover:bg-surface-container-low transition-colors group {{ !$group->is_active ? 'opacity-60' : '' }}">
                    <td class="px-6 py-5">
                        <span class="font-headline-md text-headline-md text-primary font-semibold">{{ $group->name }}</span>
                    </td>
                    <td class="px-6 py-5 text-body-md text-on-surface">
                        {{ $group->description ?? '-' }}
                    </td>
                    <td class="px-6 py-5 text-center">
                        @if((float)$group->discount_percent > 0)
                            <span class="px-2.5 py-1 bg-primary/10 text-primary font-bold rounded-lg text-label-md">
                                {{ floatval($group->discount_percent) }}%
                            </span>
                        @else
                            <span class="text-secondary text-body-sm">-</span>
                        @endif
                    </td>
                    <td class="px-6 py-5 text-center">
                        <span class="px-2.5 py-1 bg-surface-container text-on-surface font-medium rounded-lg text-label-md">
                            {{ $group->members_count }} member
                        </span>
                    </td>
                    <td class="px-6 py-5 text-center">
                        <div class="flex justify-center">
                            @if($group->is_active)
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-success/10 text-success rounded-full text-label-md font-label-md">
                                <span class="w-1.5 h-1.5 rounded-full bg-success"></span> Active
                            </span>
                            @else
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-danger/10 text-danger rounded-full text-label-md font-label-md">
                                <span class="w-1.5 h-1.5 rounded-full bg-danger"></span> Inactive
                            </span>
                            @endif
                        </div>
                    </td>
                    <td class="px-6 py-5 text-center">
                        <div class="flex gap-2 justify-center">
                            <a href="{{ route('customer-groups.edit', $group->id) }}" class="p-2 text-secondary hover:text-primary transition-colors" title="Edit">
                                <span class="material-symbols-outlined">edit</span>
                            </a>
                            <form action="{{ route('customer-groups.destroy', $group->id) }}" method="POST" class="inline" onsubmit="return confirm('Hapus group {{ $group->name }}?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="p-2 text-secondary hover:text-danger transition-colors" title="Delete">
                                    <span class="material-symbols-outlined">delete</span>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-6 py-8 text-center text-on-surface-variant">Belum ada customer group</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="px-6 py-4 bg-surface-container-low border-t border-outline-variant/30 flex flex-col md:flex-row justify-between items-center gap-4">
        <span class="text-label-md text-secondary">Menampilkan {{ $groups->firstItem() ?? 0 }}-{{ $groups->lastItem() ?? 0 }} dari {{ $groups->total() }} data</span>
        <div class="flex items-center gap-1">
            {{ $groups->links() }}
        </div>
    </div>
</div>
@endsection
