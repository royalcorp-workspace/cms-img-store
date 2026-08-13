@extends('layouts.app')

@section('title', isset($emailTemplate) ? 'Edit Template' : 'Buat Template')

@section('content')
<div class="px-4 py-6 sm:px-6 lg:px-8 max-w-5xl mx-auto">
    <div class="md:flex md:items-center md:justify-between mb-8">
        <div class="min-w-0 flex-1">
            <h2 class="text-2xl font-bold leading-7 text-gray-900 dark:text-white sm:truncate sm:text-3xl sm:tracking-tight">
                {{ isset($emailTemplate) ? 'Edit Template Email' : 'Buat Template Email Baru' }}
            </h2>
            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Gunakan text editor di bawah untuk menulis email dengan mudah seperti menulis dokumen biasa.</p>
        </div>
    </div>

    <form action="{{ isset($emailTemplate) ? route('admin.crm.email-templates.update', $emailTemplate->id) : route('admin.crm.email-templates.store') }}" method="POST" class="bg-white dark:bg-surface shadow-sm ring-1 ring-gray-900/5 sm:rounded-xl">
        @csrf
        @if(isset($emailTemplate))
            @method('PUT')
        @endif
        
        <div class="px-4 py-6 sm:p-8">
            <div class="grid max-w-full grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6">
                
                <div class="sm:col-span-3">
                    <label for="name" class="block text-sm font-medium leading-6 text-gray-900 dark:text-white">Nama Template</label>
                    <div class="mt-2">
                        <input type="text" name="name" id="name" required value="{{ old('name', $emailTemplate->name ?? '') }}" placeholder="Cth: Template Follow-up Transaksi" class="block w-full rounded-md border-0 py-1.5 text-gray-900 dark:bg-surface-container dark:text-white shadow-sm ring-1 ring-inset ring-outline focus:ring-2 focus:ring-inset focus:ring-primary sm:text-sm sm:leading-6">
                    </div>
                </div>

                <div class="sm:col-span-3">
                    <label for="subject" class="block text-sm font-medium leading-6 text-gray-900 dark:text-white">Subjek Email Default (Opsional)</label>
                    <div class="mt-2">
                        <input type="text" name="subject" id="subject" value="{{ old('subject', $emailTemplate->subject ?? '') }}" placeholder="Cth: Menunggu Pembayaran Anda" class="block w-full rounded-md border-0 py-1.5 text-gray-900 dark:bg-surface-container dark:text-white shadow-sm ring-1 ring-inset ring-outline focus:ring-2 focus:ring-inset focus:ring-primary sm:text-sm sm:leading-6">
                    </div>
                </div>

                <div class="sm:col-span-6">
                    <label class="block text-sm font-medium leading-6 text-gray-900 dark:text-white mb-2">Konten Email</label>
                    
                    <!-- Editor Toolbar & Container -->
                    <div class="mt-2 rounded-md border border-outline/30 bg-white dark:bg-surface-container overflow-hidden">
                        <div id="editor-container" class="min-h-[400px] text-gray-900 dark:text-white">
                            {!! old('html_content', $emailTemplate->html_content ?? '') !!}
                        </div>
                    </div>
                    
                    <!-- Hidden input to store real HTML content for form submission -->
                    <input type="hidden" name="html_content" id="html_content">
                    <p class="mt-2 text-xs text-gray-500">Anda dapat menggunakan fitur BOLD, miring, menyisipkan link, atau gambar layaknya Microsoft Word.</p>
                </div>

            </div>
        </div>
        
        <div class="flex items-center justify-end gap-x-6 border-t border-outline/20 px-4 py-4 sm:px-8">
            <a href="{{ route('admin.crm.email-templates.index') }}" class="text-sm font-semibold leading-6 text-gray-900 dark:text-white">Batal</a>
            <button type="submit" onclick="submitForm(event)" class="rounded-md bg-primary px-5 py-2 text-sm font-semibold text-white shadow-sm hover:bg-primary/80 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary">
                Simpan Template
            </button>
        </div>
    </form>
</div>

<!-- Load Quill Editor -->
<script src="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const quill = new Quill('#editor-container', {
            theme: 'snow',
            placeholder: 'Mulai mengetik isi email di sini...',
            modules: {
                toolbar: [
                    [{ 'header': [1, 2, 3, false] }],
                    ['bold', 'italic', 'underline', 'strike'],        // toggled buttons
                    [{ 'color': [] }, { 'background': [] }],          // dropdown with defaults from theme
                    ['blockquote', 'code-block'],
                    [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                    [{ 'align': [] }],
                    ['link', 'image', 'video'],
                    ['clean']                                         // remove formatting button
                ]
            }
        });

        // Add CSS fixes for Quill dark mode if needed
        const toolbar = document.querySelector('.ql-toolbar');
        if(toolbar) toolbar.classList.add('dark:bg-surface-gray', 'dark:border-outline/30');
        
        // Handle form submission
        window.submitForm = function(e) {
            // Get HTML content from Quill
            const html = quill.root.innerHTML;
            
            // Validate if empty
            if(quill.getText().trim().length === 0 && !html.includes('<img')) {
                e.preventDefault();
                alert('Konten email tidak boleh kosong!');
                return;
            }
            
            // Put into hidden input
            document.getElementById('html_content').value = html;
        };
    });
</script>
@endsection
