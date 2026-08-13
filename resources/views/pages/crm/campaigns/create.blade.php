@extends('layouts.app')

@section('title', 'Buat Campaign Baru')

@section('content')
<div class="px-4 py-6 sm:px-6 lg:px-8 max-w-full">
    <div class="md:flex md:items-center md:justify-between">
        <div class="min-w-0 flex-1">
            <h2 class="text-2xl font-bold leading-7 text-gray-900 dark:text-white sm:truncate sm:text-3xl sm:tracking-tight">Buat Campaign Baru</h2>
        </div>
    </div>

    <div class="mt-8">
        <form action="{{ route('admin.crm.campaigns.store') }}" method="POST" class="bg-white dark:bg-surface shadow-sm ring-1 ring-gray-900/5 sm:rounded-xl md:col-span-2">
            @csrf
            <div class="px-4 py-6 sm:p-8">
                <div class="grid max-w-2xl grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6">
                    
                    <div class="sm:col-span-4">
                        <label for="name" class="block text-sm font-medium leading-6 text-gray-900 dark:text-white">Nama Campaign Internal</label>
                        <div class="mt-2">
                            <input type="text" name="name" id="name" required placeholder="Cth: Promo Ramadhan 2026" class="block w-full rounded-md border-0 py-1.5 text-gray-900 dark:bg-surface-container dark:text-white shadow-sm ring-1 ring-inset ring-outline focus:ring-2 focus:ring-inset focus:ring-primary sm:text-sm sm:leading-6">
                        </div>
                    </div>

                    <div class="sm:col-span-4">
                        <label for="subject" class="block text-sm font-medium leading-6 text-gray-900 dark:text-white">Subjek Email Penerima</label>
                        <div class="mt-2">
                            <input type="text" name="subject" id="subject" required placeholder="Cth: Dapatkan Diskon 50% Kasur Lipat!" class="block w-full rounded-md border-0 py-1.5 text-gray-900 dark:bg-surface-container dark:text-white shadow-sm ring-1 ring-inset ring-outline focus:ring-2 focus:ring-inset focus:ring-primary sm:text-sm sm:leading-6">
                        </div>
                    </div>

                    <div class="sm:col-span-4">
                        <label for="email_template_id" class="block text-sm font-medium leading-6 text-gray-900 dark:text-white">Pilih Template Email</label>
                        <div class="mt-2">
                            <select id="email_template_id" name="email_template_id" required class="block w-full rounded-md border-0 py-1.5 text-gray-900 dark:bg-surface-container dark:text-white shadow-sm ring-1 ring-inset ring-outline focus:ring-2 focus:ring-inset focus:ring-primary sm:text-sm sm:leading-6">
                                <option value="" disabled selected>Pilih template...</option>
                                @foreach($templates as $tpl)
                                <option value="{{ $tpl->id }}">{{ $tpl->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <p class="mt-1 text-xs text-gray-500">Belum punya template? <a href="{{ route('admin.crm.email-templates.create') }}" class="text-primary hover:underline">Buat di Email Builder</a>.</p>
                    </div>

                    <div class="sm:col-span-4">
                        <label for="scheduled_at" class="block text-sm font-medium leading-6 text-gray-900 dark:text-white">Jadwal Kirim (Opsional)</label>
                        <div class="mt-2">
                            <input type="datetime-local" name="scheduled_at" id="scheduled_at" class="block w-full rounded-md border-0 py-1.5 text-gray-900 dark:bg-surface-container dark:text-white shadow-sm ring-1 ring-inset ring-outline focus:ring-2 focus:ring-inset focus:ring-primary sm:text-sm sm:leading-6">
                        </div>
                        <p class="mt-1 text-xs text-gray-500">Kosongkan jika ingin dikirim secara manual setelah campaign dibuat.</p>
                    </div>

                </div>
            </div>
            <div class="flex items-center justify-end gap-x-6 border-t border-outline/20 px-4 py-4 sm:px-8">
                <a href="{{ route('admin.crm.campaigns.index') }}" class="text-sm font-semibold leading-6 text-gray-900 dark:text-white">Batal</a>
                <button type="submit" class="rounded-md bg-primary px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-primary/80 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary">
                    Simpan Campaign
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
