@extends('layouts.app')

@section('title', 'Bulk Import Products')

@section('content')
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="font-headline-lg text-headline-lg text-on-surface">Bulk Import Products</h1>
            <nav class="flex items-center gap-2 text-body-md text-on-surface-variant mt-1">
                <a href="{{ route('dashboard') }}" class="text-primary hover:underline">Dashboard</a>
                <span class="material-symbols-outlined text-[16px]">chevron_right</span>
                <a href="{{ route('products.index') }}" class="text-primary hover:underline">Products</a>
                <span class="material-symbols-outlined text-[16px]">chevron_right</span>
                <span>Bulk Import</span>
            </nav>
        </div>
        <a href="{{ route('products.index') }}" class="flex items-center gap-2 px-4 py-2 bg-surface-container-high text-on-surface hover:bg-surface-container-highest rounded-lg font-label-md text-label-md transition-all border border-outline-variant/30">
            <span class="material-symbols-outlined text-[18px]">arrow_back</span>
            Back to Products
        </a>
    </div>

    <!-- Alert Messages -->
    @if(session('error'))
        <div class="mb-6 p-4 bg-danger/10 border border-danger/20 text-danger rounded-xl flex items-start gap-3">
            <span class="material-symbols-outlined text-[20px] shrink-0 mt-0.5">error</span>
            <div class="text-body-md font-medium">{{ session('error') }}</div>
        </div>
    @endif

    <!-- Import Results Summary -->
    @if(session('import_result'))
        @php
            $result = session('import_result');
        @endphp
        <div class="bg-white rounded-xl shadow-sm border border-outline-variant/30 overflow-hidden mb-6 p-6">
            <h2 class="font-headline-md text-headline-md text-on-surface mb-4 flex items-center gap-2">
                <span class="material-symbols-outlined text-primary text-[24px]">analytics</span>
                Import Results Summary
            </h2>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
                <div class="bg-success/10 border border-success/20 rounded-xl p-4 flex items-center gap-4">
                    <div class="w-12 h-12 rounded-full bg-success/20 flex items-center justify-center text-success">
                        <span class="material-symbols-outlined text-[28px]">check_circle</span>
                    </div>
                    <div>
                        <div class="text-label-sm text-on-surface-variant uppercase tracking-wider">Success</div>
                        <div class="text-headline-lg font-bold text-success">{{ $result['success'] }} products</div>
                    </div>
                </div>
                
                <div class="bg-warning/10 border border-warning/20 rounded-xl p-4 flex items-center gap-4">
                    <div class="w-12 h-12 rounded-full bg-warning/20 flex items-center justify-center text-warning">
                        <span class="material-symbols-outlined text-[28px]">info</span>
                    </div>
                    <div>
                        <div class="text-label-sm text-on-surface-variant uppercase tracking-wider">Skipped</div>
                        <div class="text-headline-lg font-bold text-warning">{{ $result['skipped'] }} rows</div>
                    </div>
                </div>

                <div class="bg-danger/10 border border-danger/20 rounded-xl p-4 flex items-center gap-4">
                    <div class="w-12 h-12 rounded-full bg-danger/20 flex items-center justify-center text-danger">
                        <span class="material-symbols-outlined text-[28px]">cancel</span>
                    </div>
                    <div>
                        <div class="text-label-sm text-on-surface-variant uppercase tracking-wider">Failed</div>
                        <div class="text-headline-lg font-bold text-danger">{{ $result['failed'] }} products</div>
                    </div>
                </div>
            </div>

            @if(!empty($result['errors']))
                <div class="border-t border-outline-variant/20 pt-4">
                    <h3 class="font-headline-sm text-headline-sm text-on-surface mb-3 flex items-center gap-2">
                        <span class="material-symbols-outlined text-danger text-[20px]">list_alt</span>
                        Detailed Error Logs
                    </h3>
                    <div class="overflow-x-auto border border-outline-variant/30 rounded-xl">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-surface-gray border-b border-outline-variant/30">
                                    <th class="px-4 py-3 font-label-md text-label-md text-on-surface-variant uppercase">Row</th>
                                    <th class="px-4 py-3 font-label-md text-label-md text-on-surface-variant uppercase">Product Name</th>
                                    <th class="px-4 py-3 font-label-md text-label-md text-on-surface-variant uppercase">Error Details</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-outline-variant/20">
                                @foreach($result['errors'] as $error)
                                    <tr class="hover:bg-surface-container/20">
                                        <td class="px-4 py-3 text-body-md font-bold text-on-surface">Row {{ $error['row'] }}</td>
                                        <td class="px-4 py-3 text-body-md text-secondary">{{ $error['name'] }}</td>
                                        <td class="px-4 py-3 text-body-md text-danger">{{ $error['message'] }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Import Form Card -->
        <div class="lg:col-span-2 bg-white rounded-xl shadow-sm border border-outline-variant/30 p-6 flex flex-col justify-between">
            <div>
                <h2 class="font-headline-md text-headline-md text-on-surface mb-2">Upload Import File</h2>
                <p class="text-body-md text-on-surface-variant mb-6">Select an Excel (.xlsx, .xls) or CSV file with the required formats to bulk import your products.</p>
                
                <form action="{{ route('products.import.store') }}" method="POST" enctype="multipart/form-data" id="importForm">
                    @csrf
                    
                    <div class="mb-6">
                        <label class="block text-label-md font-bold text-on-surface mb-2">Spreadsheet File</label>
                        
                        <div id="dropzone" class="border-2 border-dashed border-outline-variant/60 hover:border-primary/80 transition-all rounded-xl p-8 flex flex-col items-center justify-center text-center cursor-pointer bg-surface-gray/50 hover:bg-primary-container/10 group relative">
                            <input type="file" name="file" id="fileInput" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer" accept=".xlsx,.xls,.csv,.txt" required>
                            
                            <span class="material-symbols-outlined text-[48px] text-on-surface-variant group-hover:text-primary transition-colors mb-3">upload_file</span>
                            <div class="text-headline-sm font-semibold text-on-surface mb-1">Click to upload or drag & drop</div>
                            <div class="text-body-sm text-on-surface-variant">Accepts .xlsx, .xls, .csv files (max. 10MB)</div>
                            
                            <!-- Selected file name preview -->
                            <div id="filePreview" class="hidden mt-4 p-3 bg-primary-container/20 border border-primary/20 rounded-lg flex items-center gap-2 max-w-full">
                                <span class="material-symbols-outlined text-primary">description</span>
                                <span id="fileName" class="text-body-md font-medium text-primary truncate max-w-[280px]">file_name.xlsx</span>
                                <button type="button" id="clearFile" class="text-on-surface-variant hover:text-danger p-0.5 rounded-full hover:bg-surface-gray/50">
                                    <span class="material-symbols-outlined text-[16px] block">close</span>
                                </button>
                            </div>
                        </div>
                        @error('file')
                            <p class="text-danger text-body-sm mt-1 font-medium">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-outline-variant/20">
                        <button type="submit" id="submitBtn" class="flex items-center gap-2 px-6 py-2.5 bg-primary text-white rounded-lg font-label-md text-label-md hover:opacity-95 transition-all shadow-sm">
                            <span class="material-symbols-outlined text-[18px]">cloud_upload</span>
                            Start Importing Products
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Instructions & Template Card -->
        <div class="bg-white rounded-xl shadow-sm border border-outline-variant/30 p-6 flex flex-col justify-between">
            <div>
                <h2 class="font-headline-md text-headline-md text-on-surface mb-2">Instructions</h2>
                <p class="text-body-md text-on-surface-variant mb-4">Please download the template to see the correct structure before uploading.</p>
                
                <div class="space-y-4 mb-6">
                    <div class="flex gap-3">
                        <div class="w-6 h-6 rounded-full bg-primary/10 text-primary flex items-center justify-center shrink-0 text-label-sm font-bold">1</div>
                        <p class="text-body-md text-on-surface-variant"><strong class="text-on-surface">Multiple Sheets:</strong> The Excel file contains 3 sheets: <code class="bg-surface-gray px-1.5 py-0.5 rounded text-primary">products</code>, <code class="bg-surface-gray px-1.5 py-0.5 rounded text-primary">product_colors</code>, and <code class="bg-surface-gray px-1.5 py-0.5 rounded text-primary">product_variants</code>.</p>
                    </div>
                    
                    <div class="flex gap-3">
                        <div class="w-6 h-6 rounded-full bg-primary/10 text-primary flex items-center justify-center shrink-0 text-label-sm font-bold">2</div>
                        <p class="text-body-md text-on-surface-variant"><strong class="text-on-surface">Categories & Brands:</strong> Category and Brand names in the sheet will be automatically created if they do not exist in the database.</p>
                    </div>

                    <div class="flex gap-3">
                        <div class="w-6 h-6 rounded-full bg-primary/10 text-primary flex items-center justify-center shrink-0 text-label-sm font-bold">3</div>
                        <p class="text-body-md text-on-surface-variant"><strong class="text-on-surface">Linking Relations:</strong> In <code class="bg-surface-gray px-1.5 py-0.5 rounded text-primary">product_colors</code> and <code class="bg-surface-gray px-1.5 py-0.5 rounded text-primary">product_variants</code> sheets, the <code class="bg-surface-gray px-1.5 py-0.5 rounded text-primary">product_id</code> column can use either the <strong class="text-on-surface">UUID</strong> or the exact <strong class="text-on-surface">product_name</strong> from the first sheet.</p>
                    </div>

                    <div class="flex gap-3">
                        <div class="w-6 h-6 rounded-full bg-primary/10 text-primary flex items-center justify-center shrink-0 text-label-sm font-bold">4</div>
                        <p class="text-body-md text-on-surface-variant"><strong class="text-on-surface">Product Updates:</strong> If the product <code class="bg-surface-gray px-1.5 py-0.5 rounded text-primary">id</code> (UUID) or <code class="bg-surface-gray px-1.5 py-0.5 rounded text-primary">slug</code> already exists, the database will update it; otherwise, a new product will be created.</p>
                    </div>
                </div>
            </div>
            
            <div class="pt-4 border-t border-outline-variant/20 mt-4">
                <a href="{{ route('products.import.template') }}" target="_blank" download="product_import_template.xlsx" class="flex items-center justify-center gap-2 px-4 py-3 bg-secondary-container text-on-secondary-container hover:bg-secondary-container/80 transition-colors rounded-xl font-label-md text-label-md">
                    <span class="material-symbols-outlined">download</span>
                    Download Excel Template
                </a>
            </div>
        </div>
    </div>

    <!-- Loading Overlay/Modal -->
    <div id="loadingOverlay" class="fixed inset-0 bg-black/60 backdrop-blur-sm z-[100] flex items-center justify-center opacity-0 pointer-events-none transition-all duration-300">
        <div class="bg-white rounded-2xl shadow-xl p-8 max-w-sm w-full mx-4 flex flex-col items-center text-center">
            <!-- Spinner -->
            <div class="relative w-16 h-16 mb-4">
                <div class="absolute inset-0 rounded-full border-4 border-primary/20"></div>
                <div class="absolute inset-0 rounded-full border-4 border-primary border-t-transparent animate-spin"></div>
            </div>
            <h3 class="font-headline-md text-headline-md text-on-surface mb-2">Importing Products...</h3>
            <p class="text-body-md text-on-surface-variant">Please do not close this tab or refresh the page while we process the spreadsheet data.</p>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const fileInput = document.getElementById('fileInput');
        const filePreview = document.getElementById('filePreview');
        const fileName = document.getElementById('fileName');
        const clearFile = document.getElementById('clearFile');
        const dropzone = document.getElementById('dropzone');
        const importForm = document.getElementById('importForm');
        const loadingOverlay = document.getElementById('loadingOverlay');

        fileInput.addEventListener('change', function(e) {
            if (fileInput.files.length > 0) {
                const file = fileInput.files[0];
                fileName.textContent = file.name;
                filePreview.classList.remove('hidden');
                dropzone.classList.add('border-primary/80', 'bg-primary-container/5');
            } else {
                resetPreview();
            }
        });

        clearFile.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            resetPreview();
        });

        function resetPreview() {
            fileInput.value = '';
            filePreview.classList.add('hidden');
            dropzone.classList.remove('border-primary/80', 'bg-primary-container/5');
        }

        // Dropzone drag & drop styling handlers
        ['dragenter', 'dragover'].forEach(eventName => {
            dropzone.addEventListener(eventName, () => {
                dropzone.classList.add('border-primary', 'bg-primary-container/10');
            }, false);
        });

        ['dragleave', 'drop'].forEach(eventName => {
            dropzone.addEventListener(eventName, () => {
                if (fileInput.files.length === 0) {
                    dropzone.classList.remove('border-primary', 'bg-primary-container/10');
                }
            }, false);
        });

        // Submit form handler
        importForm.addEventListener('submit', function() {
            // Show loading overlay
            loadingOverlay.classList.remove('opacity-0', 'pointer-events-none');
            // Disable submit button
            const submitBtn = document.getElementById('submitBtn');
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.classList.add('opacity-50', 'cursor-not-allowed');
            }
        });
    });
    </script>
@endsection
