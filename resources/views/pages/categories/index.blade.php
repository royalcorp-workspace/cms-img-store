@extends('layouts.app')

@section('title', 'Categories')

@section('content')
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="font-headline-lg text-headline-lg text-on-surface">Categories</h1>
            <nav class="flex items-center gap-2 text-label-sm text-on-surface-variant mt-1 font-medium">
                <a href="{{ route('dashboard') }}" class="hover:text-primary transition-colors">eCommerce</a>
                <span class="material-symbols-outlined text-[14px]">chevron_right</span>
                <span class="text-on-surface">Categories</span>
            </nav>
        </div>
        <div class="flex items-center gap-3 relative group">
            <button type="button" class="flex items-center gap-2 px-3 py-1.5 text-on-surface-variant hover:text-on-surface rounded-lg transition-colors">
                <span class="material-symbols-outlined text-[18px]">info</span>
                <span class="text-label-md font-label-md">Tutorial</span>
            </button>
            <div class="hidden group-hover:block absolute right-0 top-full mt-2 w-80 bg-surface-container-highest rounded-lg shadow-lg border border-outline-variant p-4 z-10">
                <h4 class="font-label-md text-label-md text-on-surface mb-2">How to use Category Tree:</h4>
                <ul class="text-body-md text-on-surface-variant space-y-1 list-disc list-inside">
                    <li>Right-click on any node to create child category</li>
                    <li>Right-click to rename or delete existing categories</li>
                    <li>Drag to reorder categories (if enabled)</li>
                    <li>Use the modal form to edit category details</li>
                </ul>
            </div>
            
            <a href="{{ route('categories.create') }}" class="btn-save flex items-center gap-2 px-5 py-2 bg-primary text-white rounded-xl text-xs font-bold hover:opacity-90 transition-all shadow-sm active:scale-95">
                <span class="material-symbols-outlined text-[18px]">add</span>
                <span>Add Category</span>
            </a>
        </div>
    </div>

    @include('layouts.partials.product-submenu')

    @if(session('success'))
    <div class="mb-6 p-4 bg-success/10 border border-success/20 rounded-xl text-success flex items-center gap-3">
        <span class="material-symbols-outlined text-[20px]">check_circle</span>
        <span class="text-sm font-semibold">{{ session('success') }}</span>
    </div>
    @endif

    <div class="bg-white rounded-xl shadow-sm border border-outline-variant overflow-hidden">
        <div class="p-4 border-b border-outline-variant flex flex-col md:flex-row md:items-center justify-end gap-4">
            <form action="{{ route('categories.index') }}" method="GET" class="flex items-center gap-2">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search categories..." class="px-3 py-2 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary/20 focus:outline-none w-full md:w-64">
                <button type="submit" class="px-4 py-2 border border-outline-variant text-on-surface rounded-lg font-label-md hover:bg-surface-container transition-colors">Search</button>
            </form>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-body-sm text-on-surface">
                <thead class="bg-surface-container-lowest text-on-surface-variant font-label-md border-b border-outline-variant">
                    <tr>
                        <th class="px-4 py-3 font-medium">Name</th>
                        <th class="px-4 py-3 font-medium">Slug</th>
                        <th class="px-4 py-3 font-medium">Parent</th>
                        <th class="px-4 py-3 font-medium">Kurir & Ongkir</th>
                        <th class="px-4 py-3 font-medium">Sort</th>
                        <th class="px-4 py-3 font-medium">Status</th>
                        <th class="px-4 py-3 font-medium text-center">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-outline-variant/50">
                    @forelse($categories as $category)
                    <tr class="hover:bg-surface-container-lowest/50 transition-colors">
                        <td class="px-4 py-3 font-medium">{{ $category->name }}</td>
                        <td class="px-4 py-3 text-on-surface-variant">{{ $category->slug }}</td>
                        <td class="px-4 py-3">{{ $category->parent ? $category->parent->name : '-' }}</td>
                        <td class="px-4 py-3">
                            <div class="flex flex-col gap-1">
                                <span class="inline-flex items-center gap-1 text-[11px] font-semibold {{ ($category->courier_setting_type ?? 'detail') === 'global' ? 'text-primary' : 'text-on-surface-variant' }}">
                                    <span class="w-1.5 h-1.5 rounded-full {{ ($category->courier_setting_type ?? 'detail') === 'global' ? 'bg-primary' : 'bg-outline-variant' }}"></span>
                                    {{ ($category->courier_setting_type ?? 'detail') === 'global' ? 'Global: ' . $category->courier_type_label : 'Detail (Per Produk)' }}
                                </span>
                                @if(($category->courier_setting_type ?? 'detail') === 'global')
                                    <span class="text-[10px] text-on-surface-variant">
                                        {{ $category->shipping_scheme === 'fixed' ? 'Ongkir Tetap (Rp ' . number_format($category->shipping_cost, 0, ',', '.') . ')' : 'Hitung dari Dimensi' }}
                                    </span>
                                @endif
                            </div>
                        </td>
                        <td class="px-4 py-3">{{ $category->sort_order }}</td>
                        <td class="px-4 py-3">
                            @if($category->status)
                                <span class="px-2 py-1 bg-success/10 text-success text-[11px] font-bold uppercase rounded-full tracking-wider">Active</span>
                            @else
                                <span class="px-2 py-1 bg-outline-variant/20 text-on-surface-variant text-[11px] font-bold uppercase rounded-full tracking-wider">Inactive</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-center">
                            <div class="flex gap-2 justify-center items-center">
                                <a href="{{ route('categories.edit', $category->id) }}" class="text-on-surface-variant hover:text-primary transition-colors" title="Edit"><span class="material-symbols-outlined text-[18px]">edit</span></a>
                                <button onclick="deleteCategory('{{ $category->id }}')" class="text-on-surface-variant hover:text-danger transition-colors" title="Delete"><span class="material-symbols-outlined text-[18px]">delete</span></button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-4 py-8 text-center text-on-surface-variant">No categories found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($categories->hasPages())
        <div class="p-4 border-t border-outline-variant">
            {{ $categories->links() }}
        </div>
        @endif
    </div>
@endsection

@push('scripts')
<script>
const csrfToken = '{{ csrf_token() }}';

function deleteCategory(id) {
    if (!confirm('Hapus kategori ini? Subkategori juga akan terhapus jika ada.')) return;
    $.ajax({
        url: '{{ url('categories') }}/' + id,
        method: 'DELETE',
        headers: { 'X-CSRF-TOKEN': csrfToken },
        success: function () {
            window.location.reload();
        },
        error: function () {
            alert('Gagal menghapus kategori');
        }
    });
}
</script>
@endpush
