async function uploadProductImage(file, folder = 'products') {
    const extension = file.name.split('.').pop().toLowerCase();
    let mimeType = file.type;
    if (!mimeType) {
        if (extension === 'png') mimeType = 'image/png';
        else if (extension === 'webp') mimeType = 'image/webp';
        else if (extension === 'gif') mimeType = 'image/gif';
        else if (extension === 'svg') mimeType = 'image/svg+xml';
        else mimeType = 'image/jpeg';
    }

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';

    // 1. Minta Signed URL & coba Direct Upload
    try {
        const authRes = await fetch('/api/v1/media/upload-url', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify({ mime_type: mimeType, extension, folder })
        });

        if (authRes.ok) {
            const { upload_url, file_path, public_url } = await authRes.json();

            // Direct upload file binary ke RustFS/S3 via HTTP PUT
            const uploadRes = await fetch(upload_url, {
                method: 'PUT',
                headers: {
                    'Content-Type': mimeType
                },
                body: file
            });

            if (uploadRes.ok) {
                return { file_path, public_url };
            }
        }
    } catch (directErr) {
        console.warn('Direct S3 upload could not connect from browser, falling back to server upload...', directErr);
    }

    // 2. Fallback server-side upload jika direct upload diblokir CORS / mixed content
    const fallbackData = new FormData();
    fallbackData.append('file', file);
    fallbackData.append('folder', folder);

    const fallbackRes = await fetch('/api/v1/media/upload', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json'
        },
        body: fallbackData
    });

    if (!fallbackRes.ok) {
        const errData = await fallbackRes.json().catch(() => ({}));
        throw new Error(errData.message || 'Gagal mengunggah file ke Object Storage (Status ' + fallbackRes.status + ')');
    }

    return await fallbackRes.json();
}

// Export fungsi jika menggunakan module bundler
if (typeof module !== 'undefined' && module.exports) {
    module.exports = { uploadProductImage };
}
