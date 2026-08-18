@extends('layouts.app')

@section('title', 'Categories')

@section('content')
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="font-headline-lg text-headline-lg text-on-surface">Categories</h1>
            <nav class="flex items-center gap-2 text-body-md text-on-surface-variant mt-1">
                <a href="{{ route('dashboard') }}" class="text-primary hover:underline">Dashboard</a>
                <span class="material-symbols-outlined text-[16px]">chevron_right</span>
                <span>Categories</span>
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
            
            <button onclick="createCategory()" class="flex items-center gap-2 px-5 py-2 bg-primary text-white font-label-md hover:opacity-90 transition-all">
                <span class="material-symbols-outlined text-[18px]">add</span>
                Add Category
            </button>
        </div>
    </div>

    @include('layouts.partials.product-submenu')

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
                        <td class="px-4 py-3">{{ $category->sort_order }}</td>
                        <td class="px-4 py-3">
                            @if($category->status)
                                <span class="px-2 py-1 bg-success/10 text-success text-[11px] font-bold uppercase rounded-full tracking-wider">Active</span>
                            @else
                                <span class="px-2 py-1 bg-outline-variant/20 text-on-surface-variant text-[11px] font-bold uppercase rounded-full tracking-wider">Inactive</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-center">
                            <div class="flex gap-2 justify-center">
                                <button onclick="editCategory('{{ $category->id }}')" class="text-on-surface-variant hover:text-primary transition-colors" title="Edit"><span class="material-symbols-outlined text-[18px]">edit</span></button>
                                <button onclick="deleteCategory('{{ $category->id }}')" class="text-on-surface-variant hover:text-danger transition-colors" title="Delete"><span class="material-symbols-outlined text-[18px]">delete</span></button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-4 py-8 text-center text-on-surface-variant">No categories found.</td>
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

    <div id="categoryModal" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center">
        <div class="bg-white rounded-xl shadow-lg border border-outline-variant w-full max-w-md mx-4">
            <div class="p-6 border-b border-outline-variant">
                <h3 id="modalTitle" class="font-headline-md text-headline-md text-on-surface">Create Category</h3>
            </div>
            <form id="categoryForm" class="p-6 space-y-4">
                <input type="hidden" id="categoryId" name="id">
                <div class="space-y-1.5">
                    <label class="block text-label-sm font-medium text-on-surface-variant">Name <span class="text-danger">*</span></label>
                    <input type="text" id="categoryName" name="name" class="w-full px-3 py-2 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary/20 focus:outline-none" placeholder="Category name" required>
                </div>
                <div class="space-y-1.5">
                    <label class="block text-label-sm font-medium text-on-surface-variant">Slug</label>
                    <input type="text" id="categorySlug" name="slug" class="w-full px-3 py-2 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary/20 focus:outline-none" placeholder="auto-generated">
                </div>
                <div class="space-y-1.5">
                    <label class="block text-label-sm font-medium text-on-surface-variant">Description</label>
                    <textarea id="categoryDesc" name="description" rows="3" class="w-full px-3 py-2 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary/20 focus:outline-none" placeholder="Optional description"></textarea>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div class="space-y-1.5">
                        <label class="block text-label-sm font-medium text-on-surface-variant">Parent</label>
                        <select id="categoryParent" name="parent_id" class="w-full px-3 py-2 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary/20 focus:outline-none bg-white select2-enable">
                            <option value="">None (Top Level)</option>
                        </select>
                    </div>
                    <div class="space-y-1.5">
                        <label class="block text-label-sm font-medium text-on-surface-variant">Sort Order</label>
                        <input type="number" id="categorySort" name="sort_order" value="0" min="0" class="w-full px-3 py-2 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary/20 focus:outline-none">
                    </div>
                </div>
                <div class="space-y-1.5">
                    <label class="block text-label-sm font-medium text-on-surface-variant">Status</label>
                    <select id="categoryStatus" name="status" class="w-full px-3 py-2 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary/20 focus:outline-none bg-white select2-enable">
                        <option value="1">Active</option>
                        <option value="0">Inactive</option>
                    </select>
                </div>
                <div class="grid grid-cols-2 gap-4 border-t border-outline-variant pt-4 mt-2">
                    <div class="space-y-1.5">
                        <label class="block text-label-sm font-medium text-on-surface-variant">Banner Desktop (Web)</label>
                        <input type="file" id="categoryBannerWeb" name="banner_web" accept="image/*" class="w-full text-xs text-gray-500 file:mr-2 file:py-1 file:px-2 file:rounded file:border-0 file:text-xs file:font-semibold file:bg-primary file:text-white hover:file:opacity-90">
                    </div>
                    <div class="space-y-1.5">
                        <label class="block text-label-sm font-medium text-on-surface-variant">Banner Mobile</label>
                        <input type="file" id="categoryBannerMobile" name="banner_mobile" accept="image/*" class="w-full text-xs text-gray-500 file:mr-2 file:py-1 file:px-2 file:rounded file:border-0 file:text-xs file:font-semibold file:bg-primary file:text-white hover:file:opacity-90">
                    </div>
                </div>
            </form>
            <div class="p-6 border-t border-outline-variant flex justify-end gap-3">
                <button type="button" onclick="closeModal()" class="px-4 py-2 border border-outline-variant text-on-surface-variant rounded-lg font-label-md hover:bg-surface-container transition-colors">Cancel</button>
                <button type="submit" form="categoryForm" class="px-4 py-2 bg-primary text-white font-label-md hover:opacity-90 transition-all">Save</button>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
const csrfToken = '{{ csrf_token() }}';

function createCategory(parentName = null, parentId = null) {
    $('#categoryId').val('');
    $('#categoryName').val(parentName ? '' : 'New Category');
    $('#categorySlug').val('');
    $('#categoryDesc').val('');
    $('#categorySort').val(0);
    $('#categoryStatus').val(1);
    $('#categoryParent').val(parentId || '');
    $('#modalTitle').text('Create Category');
    openModal();
}

function editCategory(id) {
    $.getJSON('{{ url('categories') }}/' + id + '/edit', function (res) {
        const cat = res.data;
        $('#categoryId').val(cat.id);
        $('#categoryName').val(cat.name);
        $('#categorySlug').val(cat.slug);
        $('#categoryDesc').val(cat.description);
        $('#categorySort').val(cat.sort_order);
        $('#categoryStatus').val(cat.status ? 1 : 0);
        $('#categoryParent').val(cat.parent_id || '');
        $('#modalTitle').text('Edit Category');
        openModal();
    });
}

function deleteCategory(id) {
    if (!confirm('Delete this category? Subcategories will also be removed.')) return;
    $.ajax({
        url: '{{ url('categories') }}/' + id,
        method: 'DELETE',
        headers: { 'X-CSRF-TOKEN': csrfToken },
        success: function () {
            window.location.reload();
        },
        error: function () {
            alert('Failed to delete category');
        }
    });
}

$('#categoryForm').on('submit', function (e) {
    e.preventDefault();
    const id = $('#categoryId').val();
    
    // Use FormData to support file uploads
    const formData = new FormData(this);
    if (!formData.get('parent_id')) {
        formData.delete('parent_id');
    }
    
    // For PUT request with file upload, Laravel requires POST with _method=PUT
    const url = id ? '{{ url('categories') }}/' + id : '{{ url('categories') }}';
    if (id) {
        formData.append('_method', 'PUT');
    }
    
    $.ajax({
        url: url,
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': csrfToken },
        data: formData,
        processData: false,
        contentType: false,
        success: function () {
            closeModal();
            window.location.reload();
        },
        error: function (xhr) {
            alert(xhr.responseJSON?.message || 'Error saving category');
        }
    });
});

function openModal() {
    populateParentSelect();
    $('#categoryModal').removeClass('hidden');
}

function closeModal() {
    $('#categoryModal').addClass('hidden');
}

function populateParentSelect() {
    const currentId = $('#categoryId').val();
    $.getJSON('{{ route('categories.flat') }}', function (res) {
        const allNodes = res.data || [];
        const buildOptions = function (parentId, indent) {
            let html = '';
            allNodes.filter(n => (n.parent || '#') === parentId).forEach(function (n) {
                html += '<option value="' + n.id + '">' + indent + n.text + '</option>';
                html += buildOptions(n.id, indent + '\u00a0\u00a0\u00a0');
            });
            return html;
        };
        const options = '<option value="">None (Top Level)</option>' + buildOptions('#', '');
        $('#categoryParent').html(options);
    }).fail(function() {
        $('#categoryParent').html('<option value="">None (Top Level)</option>');
    });
}

$('#categoryModal').on('click', function (e) {
    if (e.target === this) closeModal();
});
</script>
@endpush
