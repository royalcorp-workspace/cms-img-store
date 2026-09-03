document.addEventListener('submit', async function(e) {
    const form = e.target;
    if (form.hasAttribute('data-direct-upload-handled') || form.id === 'productForm') return;
    
    // Temukan semua input type file yang berisi file
    const fileInputs = Array.from(form.querySelectorAll('input[type="file"]')).filter(input => input.files.length > 0);
    
    if (fileInputs.length === 0) return;

    e.preventDefault();
    
    const loader = document.getElementById('page-loader');
    if (loader) {
        loader.classList.remove('hidden');
        const p = loader.querySelector('p');
        if (p) p.textContent = 'Uploading media to Object Storage...';
    }

    try {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content 
            || form.querySelector('input[name="_token"]')?.value 
            || '';

        for (let input of fileInputs) {
            for (let file of input.files) {
                const extension = file.name.split('.').pop().toLowerCase();
                let mimeType = file.type;
                if (!mimeType) {
                    if (extension === 'png') mimeType = 'image/png';
                    else if (extension === 'webp') mimeType = 'image/webp';
                    else if (extension === 'gif') mimeType = 'image/gif';
                    else if (extension === 'svg') mimeType = 'image/svg+xml';
                    else mimeType = 'image/jpeg';
                }

                const authRes = await fetch('/api/v1/media/upload-url', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify({ mime_type: mimeType, extension })
                });

                if (!authRes.ok) {
                    const errData = await authRes.json().catch(() => ({}));
                    throw new Error(errData.message || 'Gagal mendapatkan pre-signed URL (Status ' + authRes.status + ')');
                }

                const { upload_url, file_path } = await authRes.json();

                const uploadRes = await fetch(upload_url, {
                    method: 'PUT',
                    headers: { 'Content-Type': mimeType },
                    body: file
                });

                if (!uploadRes.ok) {
                    throw new Error('Gagal mengunggah file ke Object Storage (Status ' + uploadRes.status + ')');
                }

                const hiddenInput = document.createElement('input');
                hiddenInput.type = 'hidden';
                hiddenInput.name = input.name;
                hiddenInput.value = file_path;
                form.appendChild(hiddenInput);
            }
            input.disabled = true;
        }

        form.setAttribute('data-direct-upload-handled', 'true');
        form.submit();
    } catch (err) {
        alert('Upload Error: ' + err.message);
        if (loader) loader.classList.add('hidden');
        fileInputs.forEach(input => input.disabled = false);
    }
});
