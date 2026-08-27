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
