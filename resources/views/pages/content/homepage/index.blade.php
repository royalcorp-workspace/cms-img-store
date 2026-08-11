@extends('layouts.app')

@section('title', 'Homepage Settings')

@section('content')
<div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
    <div>
        <h1 class="font-headline-lg text-headline-lg text-on-surface">Homepage Settings</h1>
        <nav class="flex items-center gap-2 text-body-md text-on-surface-variant mt-1">
            <a href="{{ route('dashboard') }}" class="text-primary hover:underline">Dashboard</a>
            <span class="material-symbols-outlined text-[16px]">chevron_right</span>
            <span>Content</span>
            <span class="material-symbols-outlined text-[16px]">chevron_right</span>
            <span>Homepages</span>
        </nav>
    </div>
</div>

@include('layouts.partials.content-submenu')

<div class="bg-surface-container-lowest border border-outline-variant rounded-xl overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left text-body-md">
            <thead class="bg-surface-container-low text-on-surface-variant text-label-md font-medium border-b border-outline-variant">
                <tr>
                    <th class="px-6 py-4">Title</th>
                    <th class="px-6 py-4">Section Key</th>
                    <th class="px-6 py-4">Sort Order</th>
                    <th class="px-6 py-4 text-center">Visibility</th>
                    <th class="px-6 py-4 text-center">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-outline-variant">
                @forelse($sections as $section)
                <tr class="hover:bg-surface-container-lowest transition-colors">
                    <td class="px-6 py-4 font-bold">{{ $section->title }}</td>
                    <td class="px-6 py-4">{{ $section->section_key }}</td>
                    <td class="px-6 py-4">
                        <input type="number" 
                               value="{{ $section->sort_order }}" 
                               class="w-20 px-2 py-1 border border-outline-variant rounded-md text-center focus:ring-2 focus:ring-primary/20 focus:outline-none"
                               onchange="updateOrder('{{ $section->id }}', this.value, '{{ $section->title }}')"
                        >
                    </td>
                    <td class="px-6 py-4 text-center">
                        @if($section->is_visible)
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-green-100 text-green-700 text-xs font-bold">
                                <span class="material-symbols-outlined text-[14px]">visibility</span> Visible
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-red-100 text-red-700 text-xs font-bold">
                                <span class="material-symbols-outlined text-[14px]">visibility_off</span> Hidden
                            </span>
                        @endif
                    </td>
                    <td class="px-6 py-4 text-center">
                        <div class="flex gap-2 justify-center">
                            <a href="{{ route('content.homepage.edit', $section->id) }}" class="w-8 h-8 flex items-center justify-center rounded-lg text-secondary hover:bg-surface-container hover:text-primary transition-colors" title="Edit">
                                <span class="material-symbols-outlined text-[20px]">edit</span>
                            </a>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-6 py-8 text-center text-on-surface-variant">
                        No homepage sections found. Click "Add Section" to create one.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
function updateOrder(id, newOrder, title) {
    Swal.fire({
        title: 'Konfirmasi Ubah Urutan',
        text: `Anda yakin ingin memindahkan "${title}" ke urutan ${newOrder}?`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#005bea',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Ya, Ubah!',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            fetch(`/content/homepage/${id}/update-order`, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ sort_order: newOrder })
            })
            .then(response => response.json())
            .then(data => {
                if(data.success) {
                    Swal.fire({
                        title: 'Berhasil!', 
                        text: 'Urutan telah diperbarui.', 
                        icon: 'success',
                        timer: 1500,
                        showConfirmButton: false
                    }).then(() => window.location.reload());
                } else {
                    Swal.fire('Gagal!', 'Terjadi kesalahan sistem.', 'error');
                }
            })
            .catch(error => {
                Swal.fire('Error!', 'Tidak dapat menghubungi server.', 'error');
            });
        } else {
            // Revert value by reloading
            window.location.reload();
        }
    });
}
</script>
@endpush
@endsection
