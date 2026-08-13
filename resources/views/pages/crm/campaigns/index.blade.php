@extends('layouts.app')

@section('title', 'Email Campaigns')

@section('content')
<div class="px-4 py-6 sm:px-6 lg:px-8 max-w-full">
    <div class="sm:flex sm:items-center">
        <div class="sm:flex-auto">
            <h1 class="text-2xl font-bold leading-6 text-gray-900 dark:text-white">Email Campaigns</h1>
            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Atur pengiriman blast email massal dan promo promosi.</p>
        </div>
        <div class="mt-4 sm:ml-16 sm:mt-0 sm:flex-none">
            <a href="{{ route('admin.crm.campaigns.create') }}" class="block rounded-md bg-primary px-3 py-2 text-center text-sm font-semibold text-white shadow-sm hover:bg-primary/80">
                Buat Campaign Baru
            </a>
        </div>
    </div>
    
    @if(session('success'))
    <div class="mt-4 p-4 mb-4 text-sm text-green-800 rounded-lg bg-green-50 dark:bg-gray-800 dark:text-green-400" role="alert">
        {{ session('success') }}
    </div>
    @endif

    <div class="mt-8 flow-root">
        <div class="-mx-4 -my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
            <div class="inline-block min-w-full py-2 align-middle sm:px-6 lg:px-8">
                <div class="overflow-hidden shadow ring-1 ring-black ring-opacity-5 sm:rounded-lg">
                    <table class="min-w-full divide-y divide-outline/20">
                        <thead class="bg-surface-gray dark:bg-surface-container">
                            <tr>
                                <th scope="col" class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 dark:text-white sm:pl-6">Nama Campaign</th>
                                <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900 dark:text-white">Template</th>
                                <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900 dark:text-white">Status</th>
                                <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900 dark:text-white">Jadwal Kirim</th>
                                <th scope="col" class="relative py-3.5 pl-3 pr-4 sm:pr-6">
                                    <span class="sr-only">Aksi</span>
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-outline/20 bg-white dark:bg-surface">
                            @forelse($campaigns as $campaign)
                            <tr>
                                <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-900 dark:text-white sm:pl-6">
                                    {{ $campaign->name }}<br>
                                    <span class="text-xs text-gray-500 font-normal">{{ $campaign->subject }}</span>
                                </td>
                                <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500 dark:text-gray-400">{{ $campaign->template->name ?? '-' }}</td>
                                <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500 dark:text-gray-400">
                                    @if($campaign->status == 'draft')
                                    <span class="inline-flex items-center rounded-md bg-gray-50 px-2 py-1 text-xs font-medium text-gray-600 ring-1 ring-inset ring-gray-500/10">Draft</span>
                                    @elseif($campaign->status == 'processing')
                                    <span class="inline-flex items-center rounded-md bg-blue-50 px-2 py-1 text-xs font-medium text-blue-700 ring-1 ring-inset ring-blue-700/10">Sending...</span>
                                    @else
                                    <span class="inline-flex items-center rounded-md bg-green-50 px-2 py-1 text-xs font-medium text-green-700 ring-1 ring-inset ring-green-600/20">Sent</span>
                                    @endif
                                </td>
                                <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500 dark:text-gray-400">{{ $campaign->scheduled_at ? \Carbon\Carbon::parse($campaign->scheduled_at)->format('d M Y H:i') : 'Kirim Langsung' }}</td>
                                <td class="relative whitespace-nowrap py-4 pl-3 pr-4 text-right text-sm font-medium sm:pr-6 flex gap-3 justify-end items-center">
                                    
                                    @if($campaign->status == 'draft')
                                    <form action="{{ route('admin.crm.campaigns.blast', $campaign->id) }}" method="POST" onsubmit="return confirm('Mulai Blast Email sekarang?')">
                                        @csrf
                                        <button type="submit" class="text-white bg-primary px-3 py-1 rounded text-xs hover:bg-primary-fixed">BLAST</button>
                                    </form>
                                    @endif

                                    <form action="{{ route('admin.crm.campaigns.destroy', $campaign->id) }}" method="POST" onsubmit="return confirm('Hapus campaign ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-900 ml-3">Hapus</button>
                                    </form>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="py-10 text-center text-sm text-gray-500 dark:text-gray-400">
                                    Belum ada campaign blast email.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="mt-4">
                    {{ $campaigns->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
