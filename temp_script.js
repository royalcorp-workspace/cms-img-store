function switchTab(tab) {
    document.querySelectorAll('.tab-panel').forEach(p => p.classList.add('hidden'));
    document.querySelectorAll('.tab-btn').forEach(b => {
        b.classList.remove('border-b-2', 'border-primary', 'text-on-surface', 'font-medium');
        b.classList.add('text-on-surface-variant');
    });
    document.getElementById('panel-' + tab).classList.remove('hidden');
    const btn = document.getElementById('tab-' + tab);
    btn.classList.add('border-b-2', 'border-primary', 'text-on-surface', 'font-medium');
    btn.classList.remove('text-on-surface-variant');
}

const productId = '{{ $productId }}';
let localImages = [];
localImages.push({ id: '{{ $img->id }}', url: '{{ $img->url }}' });
    $variantData = $variants->map(function ($v) {
        return [
            'id' => $v->id,
            'sku' => $v->sku ?? '',
            'variant_name' => $v->variant_name ?? '',
            'attributes' => (function() use ($v) {
                $raw = $v->getRawOriginal('attributes');
                if (!$raw) return null;
                $parsed = is_string($raw) ? json_decode($raw, true) : $raw;
                if (is_array($parsed)) {
                    foreach (['width', 'length', 'height', 'weight'] as $ik) {
                        unset($parsed[$ik]);
                    }
                }
                return $parsed;
            })(),
            'base_price' => $v->base_price ?? 0,
            'sell_price' => $v->sell_price ?? 0,
            'stock_qty' => $v->stock_quantity ?? 0,
            'min_order_qty' => $v->min_order_qty ?? 1,
            'status' => $v->status ?? 1,
        ];
    })->values()->all();

    $colorData = $colors->map(function ($c) {
        return [
            'id' => $c->id,
            'color_name' => $c->color_name ?? '',
            'color_code' => $c->color_code ?? '#FF0000',
            'status' => $c->status ?? 1,
        ];
    })->values()->all();

async function handleMediaUpload(input) {
    const files = input.files;
    if (files.length === 0) return;
    
    for (const file of files) {
        const reader = new FileReader();
        reader.onload = function(e) {
            localImages.push({ id: null, file: file, url: e.target.result });
            renderLocalPreviews();
        };
        reader.readAsDataURL(file);
    }
    input.value = '';
}

function renderLocalPreviews() {
    const container = document.getElementById('localPreviewContainer');
    container.innerHTML = '';
    localImages.forEach(function(img, index) {
        const div = document.createElement('div');
        div.className = 'relative group border border-outline-variant rounded-lg overflow-hidden cursor-move';
        div.setAttribute('data-index', index);
        div.innerHTML = '<img src="' + img.url + '" alt="" class="w-full h-32 object-cover"><button type="button" onclick="removeLocalImage(' + index + ')" class="absolute top-1 right-1 bg-danger text-white rounded-full p-1 opacity-0 group-hover:opacity-100 transition-opacity"><span class="material-symbols-outlined text-[16px]">close</span></button>';
        container.appendChild(div);
    });
    
    if (window.sortableMedia) {
        window.sortableMedia.destroy();
    }
    window.sortableMedia = new Sortable(container, {
        animation: 150,
        onEnd: function (evt) {
            const itemEl = evt.item;
            const newIndex = evt.newIndex;
            const oldIndex = evt.oldIndex;
            const element = localImages.splice(oldIndex, 1)[0];
            localImages.splice(newIndex, 0, element);
            renderLocalPreviews();
        },
    });
}

function removeLocalImage(index) {
    localImages.splice(index, 1);
    renderLocalPreviews();
}

async function deleteMedia(id) {
    // Legacy function, replaced by removeLocalImage for all images
}}'}
        });
        if (res.ok) location.reload();
    } catch (e) {
        console.error(e);
        alert('Failed to delete image');
    }
}

function renderVariants() {
    const container = document.getElementById('variantsList');
    container.innerHTML = '';
    localVariants.forEach(function(v, index) {
        const isSaved = !!v.id;
        const div = document.createElement('div');
        div.className = 'border border-outline-variant rounded-lg p-4';
        div.innerHTML = `
            <div class="flex items-start justify-between mb-3">
                <div>
                    <p class="font-body-md text-body-md text-on-surface font-semibold">${v.variant_name || v.sku || 'Variant'}</p>
                    <p class="text-label-sm text-on-surface-variant">SKU: ${v.sku || '-'}</p>
                </div>
                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-label-sm font-label-sm ${v.status == 1 ? 'bg-success/10 text-success' : 'bg-danger/10 text-danger'}">
                    <span class="w-1.5 h-1.5 rounded-full bg-current"></span> ${v.status == 1 ? 'Active' : 'Inactive'}
                </span>
            </div>
            ${v.attributes && typeof v.attributes === 'object' && Object.keys(v.attributes).length > 0 ? `
                <div class="mb-3">
                    <p class="text-on-surface-variant text-label-sm mb-1">Attributes:</p>
                    <div class="flex flex-wrap gap-1">
                        ${Object.entries(v.attributes).map(([k, val]) => `<span class="bg-surface-variant/50 text-on-surface px-2 py-0.5 rounded text-[11px]">${k}: ${val}</span>`).join('')}
                    </div>
                </div>
            ` : ''}
            <div class="grid grid-cols-2 gap-3 text-body-sm">
                <div>
                    <p class="text-on-surface-variant">Price</p>
                    <p class="font-medium text-on-surface"><del class="text-xs">Rp${Number(v.base_price).toLocaleString('id-ID', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</del> Rp${Number(v.sell_price).toLocaleString('id-ID', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</p>
                </div>
                <div>
                    <p class="text-on-surface-variant">Stock</p>
                    <p class="font-medium text-on-surface">${v.stock_qty || 0}</p>
                </div>
            </div>
            <div class="flex justify-end mt-3 pt-3 border-t border-outline-variant/20">
                <button type="button" onclick="editLocalVariant(${index})" class="text-primary hover:opacity-80 text-label-sm flex items-center gap-1 mr-3">
                    <span class="material-symbols-outlined text-[16px]">edit</span> Edit
                </button>
                <button type="button" onclick="removeLocalVariant(${index})" class="text-danger hover:opacity-80 text-label-sm flex items-center gap-1">
                    <span class="material-symbols-outlined text-[16px]">delete</span> Delete
                </button>
            </div>
        `;
        container.appendChild(div);
    });
    document.getElementById('variantsInput').value = JSON.stringify(localVariants);
}

function renderColors() {
    const container = document.getElementById('colorsList');
    container.innerHTML = '';
    localColors.forEach(function(c, index) {
        const isSaved = !!c.id;
        const div = document.createElement('div');
        div.className = 'border border-outline-variant rounded-lg p-4';
        div.innerHTML = `
            <div class="flex items-start justify-between mb-3">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-full border border-outline-variant" style="background-color: ${c.color_code}"></div>
                    <div>
                        <p class="font-body-md text-body-md text-on-surface font-semibold">${c.color_name || 'Unnamed Color'}</p>
                        <p class="text-label-sm text-on-surface-variant font-mono">${c.color_code}</p>
                    </div>
                </div>
                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-label-sm font-label-sm ${c.status == 1 ? 'bg-success/10 text-success' : 'bg-danger/10 text-danger' }">
                    <span class="w-1.5 h-1.5 rounded-full bg-current"></span> ${c.status == 1 ? 'Active' : 'Inactive'}
                </span>
            </div>
            <div class="flex justify-end mt-3 pt-3 border-t border-outline-variant/20">
                ${isSaved ? '<button type="button" class="text-primary hover:opacity-80 text-label-sm flex items-center gap-1 mr-3"><span class="material-symbols-outlined text-[16px]">edit</span> Edit</button>' : ''}
                <button type="button" onclick="removeColor(${index})" class="text-danger hover:opacity-80 text-label-sm flex items-center gap-1">
                    <span class="material-symbols-outlined text-[16px]">delete</span> Delete
                </button>
            </div>
        `;
        container.appendChild(div);
    });
    document.getElementById('colorsInput').value = JSON.stringify(localColors);
}

function addColor() {
    const name = document.getElementById('cName').value.trim();
    const code = document.getElementById('cCode').value.trim();

    if (!code) {
        showWarningModal('Please select a color code.');
        return;
    }

    const payload = {
        color_name: name,
        color_code: code,
        status: document.getElementById('cStatus').value == '1' ? 1 : 0,
    };
    localColors.push(payload);
    renderColors();
    document.getElementById('cName').value = '';
    document.getElementById('cCode').value = '#FF0000';
    document.getElementById('cColorPicker').value = '#FF0000';
    document.getElementById('cStatus').value = '1';
}

function removeColor(index) {
    localColors.splice(index, 1);
    renderColors();
}

async function editColor(id, data) {
    currentEditColorId = id;
    document.getElementById('cName').value = data.color_name || '';
    document.getElementById('cCode').value = data.color_code || '#FF0000';
    document.getElementById('cColorPicker').value = data.color_code || '#FF0000';
    document.getElementById('cStatus').value = data.status == 1 ? '1' : '0';
    openColorModal();
}

function openColorModal() {
    document.getElementById('colorModal').classList.remove('hidden');
    document.getElementById('colorModal').classList.add('flex');
}

function closeColorModal() {
    document.getElementById('colorModal').classList.add('hidden');
    document.getElementById('colorModal').classList.remove('flex');
    currentEditColorId = null;
}

async function saveColorFromModal() {
    const name = document.getElementById('mcName').value.trim();
    const code = document.getElementById('mcCode').value.trim();

    if (!code) {
        showWarningModal('Please select a color code.');
        return;
    }

    const payload = {
        color_name: name,
        color_code: code,
        status: document.getElementById('mcStatus').value == '1' ? 1 : 0,
    };

    if (currentEditColorIndex !== null) {
        payload.id = localColors[currentEditColorIndex].id || null;
        localColors[currentEditColorIndex] = payload;
    } else {
        localColors.push(payload);
    }
    renderColors();
    closeColorModal();
}

async function deleteColor(index) {
    if (!confirm('Delete this color?')) return;
    localColors.splice(index, 1);
    renderColors();
}

const ATTRIBUTE_MASTER = {
    "Kelengkapan": ["Mattress", "Full Set", "Kasur Saja", "Divan + Sandaran", "Set Kasur + Divan"],
    "Size": ["90x200", "100x200", "120x200", "140x200", "160x200", "180x200", "200x200"],
    "Warna / Motif": ["Sage", "Blue", "White", "Grey", "Beige", "Black", "Red", "Brown", "Pink", "Navy", "Green"],
    "Ukuran": ["90x200", "100x200", "120x200", "140x200", "160x200", "180x200", "200x200"],
    "Tinggi Kasur": ["T20", "T22", "T24", "T25", "T26", "T27", "T30", "T32", "T35", "T40", "T45"],
    "Ukuran kasur": ["90x200", "100x200", "120x200", "140x200", "160x200", "180x200", "200x200"]
};

function createAttributeRow(key = '', val = '') {
    const div = document.createElement('div');
    div.className = 'flex gap-2 items-center attribute-row';
    
    let keyOptions = '<option value="">Pilih Level / Kategori</option>';
    let valOptions = '<option value="">Pilih Opsi</option>';
    
    let isCustomKey = key && !ATTRIBUTE_MASTER[key];
    
    for (const k in ATTRIBUTE_MASTER) {
        keyOptions += `<option value="${k}" ${k === key ? 'selected' : ''}>${k}</option>`;
    }
    
    if (isCustomKey) {
        keyOptions += `<option value="${key}" selected>${key}</option>`;
        valOptions += `<option value="${val}" selected>${val}</option>`;
    } else if (key && ATTRIBUTE_MASTER[key]) {
        ATTRIBUTE_MASTER[key].forEach(v => {
            valOptions += `<option value="${v}" ${v === val ? 'selected' : ''}>${v}</option>`;
        });
        if (val && !ATTRIBUTE_MASTER[key].includes(val)) {
            valOptions += `<option value="${val}" selected>${val}</option>`;
        }
    }

    div.innerHTML = `
        <select class="attr-key w-1/3 px-3 py-1.5 text-sm border border-outline-variant rounded-md focus:ring-2 focus:ring-primary/20 focus:outline-none" onchange="updateAttrValOptions(this)">
            ${keyOptions}
        </select>
        <select class="attr-val flex-1 px-3 py-1.5 text-sm border border-outline-variant rounded-md focus:ring-2 focus:ring-primary/20 focus:outline-none">
            ${valOptions}
        </select>
        <button type="button" onclick="this.parentElement.remove()" class="text-danger hover:opacity-80 material-symbols-outlined text-[18px]">close</button>
    `;
    
    return div;
}

function updateAttrValOptions(keySelect) {
    const key = keySelect.value;
    const valSelect = keySelect.parentElement.querySelector('.attr-val');
    let valOptions = '<option value="">Pilih Opsi</option>';
    if (key && ATTRIBUTE_MASTER[key]) {
        ATTRIBUTE_MASTER[key].forEach(v => {
            valOptions += `<option value="${v}">${v}</option>`;
        });
    }
    valSelect.innerHTML = valOptions;
}

function addVAttributeRow(key = '', val = '') {
    document.getElementById('vAttributesContainer').appendChild(createAttributeRow(key, val));
}

function addMvAttributeRow(key = '', val = '') {
    document.getElementById('mvAttributesContainer').appendChild(createAttributeRow(key, val));
}

function getAttributesFromContainer(containerId) {
    const container = document.getElementById(containerId);
    const rows = container.querySelectorAll('.attribute-row');
    const attrs = {};
    rows.forEach(r => {
        const key = r.querySelector('.attr-key').value.trim();
        const val = r.querySelector('.attr-val').value.trim();
        if (key && val) {
            attrs[key] = val;
        }
    });
    return Object.keys(attrs).length > 0 ? attrs : null;
}

function renderAttributesToContainer(containerId, attrs) {
    const container = document.getElementById(containerId);
    container.innerHTML = '';
    if (attrs && typeof attrs === 'object') {
        for (const [key, val] of Object.entries(attrs)) {
            container.appendChild(createAttributeRow(key, val));
        }
    }
}

function addVariant() {
    const sku = document.getElementById('vSku').value.trim();
    const name = document.getElementById('vName').value.trim();
    const basePrice = document.getElementById('vBasePrice').value;
    const sellPrice = document.getElementById('vSellPrice').value;
    const stock = document.getElementById('vStock').value;

    if (!sku && !name) {
        showWarningModal('Please fill in at least SKU or Variant Name.');
        return;
    }
    if (!sellPrice || parseFloat(sellPrice) < 0) {
        showWarningModal('Please enter a valid Sell Price.');
        document.getElementById('vSellPrice').focus();
        return;
    }
    if (stock === '' || parseInt(stock) < 0) {
        showWarningModal('Please enter a valid Stock Qty.');
        document.getElementById('vStock').focus();
        return;
    }

    const payload = {
        sku: sku,
        variant_name: name,
        attributes: getAttributesFromContainer('vAttributesContainer'),
        base_price: basePrice,
        sell_price: sellPrice,
        stock_qty: stock,
        min_order_qty: document.getElementById('vMinOrder').value || 1,
        status: document.getElementById('vStatus').value == '1' ? 1 : 0,
    };
    localVariants.push(payload);
    renderVariants();
    document.getElementById('vSku').value = '';
    document.getElementById('vName').value = '';
    document.getElementById('vAttributesContainer').innerHTML = '';
    document.getElementById('vBasePrice').value = '';
    document.getElementById('vSellPrice').value = '';
    document.getElementById('vStock').value = '';
    document.getElementById('vMinOrder').value = '';
    document.getElementById('vStatus').value = '1';
}

function removeLocalVariant(index) {
    localVariants.splice(index, 1);
    renderVariants();
}

let currentEditVariantId = null;

async function editVariant(id, data) {
    currentEditVariantId = id;
    document.getElementById('mvSku').value = data.sku || '';
    document.getElementById('mvName').value = data.variant_name || '';
    document.getElementById('mvBasePrice').value = data.base_price || '';
    document.getElementById('mvSellPrice').value = data.sell_price || '';
    document.getElementById('mvStock').value = data.stock_qty || '';
    document.getElementById('mvMinOrder').value = data.min_order_qty || '';
    document.getElementById('mvStatus').value = data.status == 1 ? '1' : '0';
    renderAttributesToContainer('mvAttributesContainer', data.attributes);
    openVariantModal();
}

async function editLocalVariant(index) {
    const v = localVariants[index];
    currentEditLocalIndex = index;
    document.getElementById('vSku').value = v.sku || '';
    document.getElementById('vName').value = v.variant_name || '';
    renderAttributesToContainer('vAttributesContainer', v.attributes);
    document.getElementById('vBasePrice').value = v.base_price || '';
    document.getElementById('vSellPrice').value = v.sell_price || '';
    document.getElementById('vStock').value = v.stock_qty || '';
    document.getElementById('vMinOrder').value = v.min_order_qty || '';
    document.getElementById('vStatus').value = v.status == 1 ? '1' : '0';
    document.getElementById('addVariantBtn').classList.add('hidden');
    document.getElementById('updateVariantBtn').classList.remove('hidden');
    document.getElementById('cancelVariantBtn').classList.remove('hidden');
}

function cancelEditVariant() {
    currentEditLocalIndex = null;
    document.getElementById('addVariantBtn').classList.remove('hidden');
    document.getElementById('updateVariantBtn').classList.add('hidden');
    document.getElementById('cancelVariantBtn').classList.add('hidden');
    document.getElementById('vSku').value = '';
    document.getElementById('vName').value = '';
    document.getElementById('vAttributesContainer').innerHTML = '';
    document.getElementById('vBasePrice').value = '';
    document.getElementById('vSellPrice').value = '';
    document.getElementById('vStock').value = '';
    document.getElementById('vMinOrder').value = '';
    document.getElementById('vStatus').value = '1';
}

let currentEditLocalIndex = null;

function updateLocalVariant() {
    const sku = document.getElementById('vSku').value.trim();
    const name = document.getElementById('vName').value.trim();
    const basePrice = document.getElementById('vBasePrice').value;
    const sellPrice = document.getElementById('vSellPrice').value;
    const stock = document.getElementById('vStock').value;

    if (!sku && !name) {
        showWarningModal('Please fill in at least SKU or Variant Name.');
        return;
    }
    if (!sellPrice || parseFloat(sellPrice) < 0) {
        showWarningModal('Please enter a valid Sell Price.');
        document.getElementById('vSellPrice').focus();
        return;
    }
    if (stock === '' || parseInt(stock) < 0) {
        showWarningModal('Please enter a valid Stock Qty.');
        document.getElementById('vStock').focus();
        return;
    }

    localVariants[currentEditLocalIndex] = {
        ...localVariants[currentEditLocalIndex],
        sku: sku,
        variant_name: name,
        attributes: getAttributesFromContainer('vAttributesContainer'),
        base_price: basePrice,
        sell_price: sellPrice,
        stock_qty: stock,
        min_order_qty: document.getElementById('vMinOrder').value || 1,
        status: document.getElementById('vStatus').value == '1' ? 1 : 0,
    };
    renderVariants();
    cancelEditVariant();
}

function showWarningModal(message) {
    document.getElementById('warningMessage').textContent = message;
    document.getElementById('warningModal').classList.remove('hidden');
    document.getElementById('warningModal').classList.add('flex');
}

function closeWarningModal() {
    document.getElementById('warningModal').classList.add('hidden');
    document.getElementById('warningModal').classList.remove('flex');
}

function openVariantModal() {
    document.getElementById('variantModalTitle').textContent = currentEditVariantId ? 'Edit Variation' : 'Add New Variation';
    document.getElementById('variantModal').classList.remove('hidden');
    document.getElementById('variantModal').classList.add('flex');
}

function closeVariantModal() {
    document.getElementById('variantModal').classList.add('hidden');
    document.getElementById('variantModal').classList.remove('flex');
}

async function saveVariantFromModal() {
    const sku = document.getElementById('mvSku').value.trim();
    const name = document.getElementById('mvName').value.trim();
    const basePrice = document.getElementById('mvBasePrice').value;
    const sellPrice = document.getElementById('mvSellPrice').value;
    const stock = document.getElementById('mvStock').value;
    const minOrder = document.getElementById('mvMinOrder').value;
    const sort = document.getElementById('mvSort').value;
    const status = document.getElementById('mvStatus').value;

    const attrs = [];
    document.querySelectorAll('.mv-attr-row').forEach(row => {
        const k = row.querySelector('.mv-attr-name').value;
        const v = row.querySelector('.mv-attr-val').value;
        if (k && v) {
            attrs.push({name: k, value: v});
        }
    });

    const payload = {
        sku: sku,
        variant_name: name,
        attributes: attrs,
        base_price: parseFloat(basePrice) || 0,
        sell_price: parseFloat(sellPrice) || 0,
        stock_qty: parseInt(stock) || 0,
        min_order_qty: parseInt(minOrder) || 1,
        sort_order: parseInt(sort) || 0,
        status: parseInt(status) || 0
    };

    if (currentEditVariantIndex !== null) {
        payload.id = localVariants[currentEditVariantIndex].id || null;
        localVariants[currentEditVariantIndex] = payload;
    } else {
        localVariants.push(payload);
    }
    renderVariants();
    closeVariantModal();
}

async function deleteVariant(index) {
    if (!confirm('Delete this variant?')) return;
    localVariants.splice(index, 1);
    renderVariants();
}

document.getElementById('productForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    document.getElementById('variantsInput').value = JSON.stringify(localVariants);
    document.getElementById('colorsInput').value = JSON.stringify(localColors);
    
    // Get Quill content
    var quillHtml = quill.root.innerHTML;
    // If empty (only contains <p><br></p>), set it to empty string
    if (quillHtml === '<p><br></p>') {
        quillHtml = '';
    }
    document.getElementById('description-input').value = quillHtml;

    let formData = new FormData(this);
    
    // Process localImages for existing vs new
    localImages.forEach((img, i) => {
        if(img.file) {
            formData.append('new_images[]', img.file);
            formData.append('new_image_orders[]', i);
        } else if (img.id) {
            formData.append('existing_images[]', img.id);
            formData.append('existing_image_orders[]', i);
        }
    });

    try {
        const submitBtn = this.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerText;
        submitBtn.innerText = 'Saving...';
        submitBtn.disabled = true;

        let res = await fetch(this.action, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        });
        
        if (res.ok) {
            window.location.href = '{{ route("products.index") }}';
        } else {
            const errData = await res.json();
            console.error(errData);
            alert(errData.message || 'Failed to save product');
            submitBtn.innerText = originalText;
            submitBtn.disabled = false;
        }
    } catch(err) {
        console.error(err);
        alert('Error saving product');
        const submitBtn = this.querySelector('button[type="submit"]');
        submitBtn.innerText = 'Update Product';
        submitBtn.disabled = false;
    }
});

renderLocalPreviews();
renderLocalPreviews();
renderVariants();
renderColors();

$(document).ready(function() {
    // Initialize Quill
    window.quill = new Quill('#quill-editor', {
        theme: 'snow',
        modules: {
            toolbar: [
                [{ 'header': [1, 2, 3, false] }],
                ['bold', 'italic', 'underline', 'strike'],
                [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                ['link', 'clean']
            ]
        }
    });

    $('#categorySelect').select2({
        placeholder: 'Select Category',
        allowClear: true,
        width: '100%'
    });
    $('#brandSelect').select2({
        placeholder: 'Select Brand',
        allowClear: true,
        width: '100%'
    });

    $('input[name="name"]').on('input', function() {
        let slug = $(this).val().toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/(^-|-$)+/g, '');
        $('#productSlug').val(slug);
    });

        alert('Warning: Slug (URL) yang dihasilkan sudah digunakan oleh data lain. Silakan ubah nama atau slug secara manual.');
});
