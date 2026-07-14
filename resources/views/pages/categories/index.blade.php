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
        <div class="relative group">
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
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-outline-variant/30">
        <div class="p-4 border-b border-outline-variant flex items-center gap-2">
            <button onclick="createCategory()" class="px-4 py-2 bg-primary text-white rounded-lg font-label-md hover:opacity-90 transition-all shadow-sm">Add Category</button>
            <button onclick="refreshTree()" class="px-4 py-2 border border-outline-variant text-on-surface rounded-lg font-label-md hover:bg-surface-container transition-colors">Refresh</button>
        </div>
        <div class="p-4">
            <div id="categoryTree">Loading categories...</div>
        </div>
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
                        <select id="categoryParent" name="parent_id" class="w-full px-3 py-2 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary/20 focus:outline-none bg-white">
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
                    <select id="categoryStatus" name="status" class="w-full px-3 py-2 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary/20 focus:outline-none bg-white">
                        <option value="1">Active</option>
                        <option value="0">Inactive</option>
                    </select>
                </div>
            </form>
            <div class="p-6 border-t border-outline-variant flex justify-end gap-3">
                <button type="button" onclick="closeModal()" class="px-4 py-2 border border-outline-variant text-on-surface-variant rounded-lg font-label-md hover:bg-surface-container transition-colors">Cancel</button>
                <button type="submit" form="categoryForm" class="px-4 py-2 bg-primary text-white rounded-lg font-label-md hover:opacity-90 transition-all shadow-sm">Save</button>
            </div>
        </div>
    </div>
@endsection

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/jstree@3.3.16/dist/themes/default/style.min.css" rel="stylesheet" />
<style>
.jstree-default .jstree-anchor:hover {
    background: var(--color-surface-container-high) !important;
    color: var(--color-on-surface) !important;
}
.jstree-default .jstree-clicked,
.jstree-default .jstree-clicked:hover {
    background: var(--color-secondary-container) !important;
    color: var(--color-on-secondary-container) !important;
    box-shadow: none !important;
}
.jstree-default .jstree-search,
.jstree-default .jstree-search .jstree-icon {
    color: var(--color-on-surface) !important;
}
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/jstree@3.3.16/dist/jstree.min.js"></script>
<script>
const csrfToken = '{{ csrf_token() }}';

$(document).ready(function () {
    $('#categoryTree').html('Loading...');
    $.getJSON('{{ route('categories.flat') }}', function (res) {
        const nodes = res.data || [];
        $('#categoryTree').empty();
        const map = {};
        const roots = [];
        nodes.forEach(function(n) {
            map[n.id] = { id: n.id, text: n.text, state: { opened: true, selected: false } };
        });
        nodes.forEach(function(n) {
            if (!n.parent || n.parent === '#' || !map[n.parent]) {
                roots.push(map[n.id]);
            } else {
                map[n.parent].children = map[n.parent].children || [];
                map[n.parent].children.push(map[n.id]);
            }
        });
        Object.keys(map).forEach(function(id) {
            var nd = map[id];
            if (nd.children && nd.children.length > 0) {
                nd.icon = 'jstree-folder';
                nd.state.opened = true;
            } else {
                nd.icon = 'jstree-file';
            }
        });
        tree = $('#categoryTree');
        tree.jstree({
            core: {
                data: roots,
                check_callback: true,
                multiple: false,
                themes: { stripes: true }
            },
            plugins: ['types', 'state', 'contextmenu'],
            types: {
                'default': { icon: 'jstree-folder' },
                'file': { icon: 'jstree-file' }
            },
            state: { key: 'category-tree-state' },
            contextmenu: {
                items: function (node) {
                    return {
                        'create': {
                            'label': 'Create Child',
                            'action': function () { createCategory(node.text, node.id); },
                            'separator_before': true
                        },
                        'rename': {
                            'label': 'Rename',
                            'action': function () { editCategory(node.id); }
                        },
                        'delete': {
                            'label': 'Delete',
                            'action': function () { deleteCategory(node.id); }
                        }
                    };
                }
            }
        });
    }).fail(function() {
        $('#categoryTree').html('<p class="text-danger">Failed to load categories. Please make sure the product_category table exists and has data.</p>');
    });
});

function refreshTree() {
    if (tree && tree.length) tree.jstree(true).refresh();
}

function createCategory(parentName, parentId) {
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
            if (tree && tree.length) tree.jstree(true).refresh();
        },
        error: function () {
            alert('Failed to delete category');
        }
    });
}

$('#categoryForm').on('submit', function (e) {
    e.preventDefault();
    const id = $('#categoryId').val();
    const payload = {
        name: $('#categoryName').val(),
        slug: $('#categorySlug').val(),
        description: $('#categoryDesc').val(),
        parent_id: $('#categoryParent').val() || null,
        sort_order: parseInt($('#categorySort').val(), 10),
        status: $('#categoryStatus').val() == '1' ? 1 : 0,
    };
    const url = id ? '{{ url('categories') }}/' + id : '{{ url('categories') }}';
    const method = id ? 'PUT' : 'POST';

    $.ajax({
        url: url,
        method: method,
        headers: { 'X-CSRF-TOKEN': csrfToken, 'Content-Type': 'application/json' },
        data: JSON.stringify(payload),
        success: function () {
            closeModal();
            if (tree && tree.length) tree.jstree(true).refresh();
        },
        error: function () {
            alert('Failed to save category');
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
