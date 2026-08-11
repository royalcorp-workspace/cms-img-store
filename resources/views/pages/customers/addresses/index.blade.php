@extends('layouts.app')

@section('title', 'Buku Alamat - ' . $customer->name)

@section('content')
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="font-headline-lg text-headline-lg text-on-surface">Buku Alamat Pelanggan</h1>
            <nav class="flex items-center gap-2 text-body-md text-on-surface-variant mt-1">
                <a href="{{ route('dashboard') }}" class="hover:text-primary transition-colors">Dashboard</a>
                <span class="material-symbols-outlined text-[16px]">chevron_right</span>
                <a href="{{ route('customers.index') }}" class="text-primary hover:underline">Customers</a>
                <span class="material-symbols-outlined text-[16px]">chevron_right</span>
                <span>{{ $customer->name }}</span>
            </nav>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('customers.addresses.create', $customer->id) }}" class="flex items-center gap-2 px-4 py-2 bg-primary text-white font-label-md hover:opacity-90 transition-all rounded-lg">
                <span class="material-symbols-outlined text-[18px]">add</span> Tambah Alamat
            </a>
        </div>
    </div>

    @include('layouts.partials.customer-submenu')

    <div class="bg-surface rounded-2xl shadow-sm border border-outline-variant overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-surface-variant/30 border-b border-outline-variant">
                        <th class="px-6 py-4 text-label-md font-bold text-on-surface">Label</th>
                        <th class="px-6 py-4 text-label-md font-bold text-on-surface">Penerima</th>
                        <th class="px-6 py-4 text-label-md font-bold text-on-surface">Alamat Lengkap</th>
                        <th class="px-6 py-4 text-label-md font-bold text-on-surface">Status</th>
                        <th class="px-6 py-4 text-label-md font-bold text-on-surface w-[150px] text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-outline-variant">
                    @forelse($addresses as $address)
                        <tr class="hover:bg-surface-variant/10 transition-colors">
                            <td class="px-6 py-4">
                                <span class="font-bold">{{ $address->label }}</span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="font-medium text-on-surface">{{ $address->recipient_name }}</div>
                                <div class="text-body-sm text-on-surface-variant">{{ $address->phone }}</div>
                            </td>
                            <td class="px-6 py-4">
                                <p class="text-body-sm max-w-sm">{{ $address->address }}</p>
                                <div class="text-xs text-on-surface-variant mt-1">
                                    {{ $address->subDistrict ? $address->subDistrict->sub_district : '-' }}, 
                                    {{ $address->city ? $address->city->name : '-' }}
                                    {{ $address->postal_code ? ' ' . $address->postal_code : '' }}
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                @if($address->is_primary)
                                    <span class="inline-flex items-center px-2 py-1 bg-primary/10 text-primary text-xs font-bold rounded-md">
                                        Utama
                                    </span>
                                @else
                                    <span class="text-on-surface-variant text-xs">Opsional</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right space-x-2">
                                <a href="{{ route('customers.addresses.edit', [$customer->id, $address->id]) }}" class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-primary/10 text-primary hover:bg-primary hover:text-white transition-colors" title="Edit">
                                    <span class="material-symbols-outlined text-[18px]">edit</span>
                                </a>
                                <form action="{{ route('customers.addresses.destroy', [$customer->id, $address->id]) }}" method="POST" class="inline-block" onsubmit="return confirm('Apakah Anda yakin ingin menghapus alamat ini?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-danger/10 text-danger hover:bg-danger hover:text-white transition-colors" title="Delete">
                                        <span class="material-symbols-outlined text-[18px]">delete</span>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-8 text-center text-on-surface-variant">
                                <span class="material-symbols-outlined text-4xl mb-2 opacity-50">location_off</span>
                                <p>Belum ada alamat tersimpan.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
