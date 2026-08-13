@extends('layouts.app')

@section('title', 'CRM Pipeline')

@section('content')
<div class="px-4 py-6 sm:px-6 lg:px-8 max-w-full">
    <div class="sm:flex sm:items-center">
        <div class="sm:flex-auto">
            <h1 class="text-2xl font-bold leading-6 text-gray-900 dark:text-white">CRM Pipeline</h1>
            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Kelola proses prospek dan transaksi pelanggan secara visual.</p>
        </div>
        <div class="mt-4 sm:ml-16 sm:mt-0 sm:flex-none">
            <button type="button" class="block rounded-md bg-primary px-3 py-2 text-center text-sm font-semibold text-white shadow-sm hover:bg-primary/80">
                Tambah Lead Baru
            </button>
        </div>
    </div>

    <!-- Kanban Board Container -->
    <div class="mt-8 flex gap-6 overflow-x-auto pb-4 h-[calc(100vh-250px)]" id="kanban-board">
        @foreach($stages as $stage)
        <div class="flex-shrink-0 w-80 bg-surface-gray dark:bg-surface-container rounded-lg flex flex-col kanban-column" data-stage-id="{{ $stage->id }}">
            <div class="p-4 flex items-center justify-between border-b border-outline/20">
                <div class="flex items-center gap-2">
                    <div class="w-3 h-3 rounded-full" style="background-color: {{ $stage->color }}"></div>
                    <h3 class="font-semibold text-gray-900 dark:text-white">{{ $stage->name }}</h3>
                    <span class="bg-gray-200 dark:bg-surface-dim text-xs font-medium px-2 py-0.5 rounded-full text-gray-600 dark:text-gray-300">
                        {{ $stage->leads->count() }}
                    </span>
                </div>
            </div>
            
            <div class="p-3 flex-1 overflow-y-auto sortable-list min-h-[150px]" data-stage-id="{{ $stage->id }}">
                @foreach($stage->leads as $lead)
                <div class="bg-white dark:bg-surface p-4 rounded shadow-sm border border-outline/30 mb-3 cursor-move hover:border-primary/50 transition-colors" data-lead-id="{{ $lead->id }}">
                    <div class="flex justify-between items-start mb-2">
                        <span class="text-xs font-medium px-2 py-1 bg-surface-gray dark:bg-surface-container rounded text-gray-600 dark:text-gray-300">
                            #{{ substr($lead->id, 0, 8) }}
                        </span>
                        <span class="text-xs font-semibold text-primary">
                            {{ $lead->amount ? 'Rp ' . number_format($lead->amount, 0, ',', '.') : '-' }}
                        </span>
                    </div>
                    <h4 class="font-semibold text-gray-900 dark:text-white text-sm mb-1">
                        {{ $lead->customer ? $lead->customer->name : 'Unknown Customer' }}
                    </h4>
                    <p class="text-xs text-gray-500 dark:text-gray-400 line-clamp-2">
                        {{ $lead->notes ?? 'Tidak ada catatan.' }}
                    </p>
                    <div class="mt-3 text-xs text-gray-400 dark:text-gray-500 flex items-center gap-1">
                        <span class="material-symbols-outlined !text-[14px]">schedule</span>
                        {{ $lead->created_at->diffForHumans() }}
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endforeach
    </div>
</div>

<!-- Load SortableJS -->
<script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const columns = document.querySelectorAll('.sortable-list');
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'); // Ensure CSRF exists, or we use Alpine
        
        columns.forEach(col => {
            new Sortable(col, {
                group: 'shared', // set both lists to same group
                animation: 150,
                ghostClass: 'opacity-50',
                onEnd: function (evt) {
                    const itemEl = evt.item;  // dragged HTMLElement
                    const leadId = itemEl.getAttribute('data-lead-id');
                    const toStageId = evt.to.getAttribute('data-stage-id');
                    const fromStageId = evt.from.getAttribute('data-stage-id');
                    
                    if (toStageId !== fromStageId) {
                        // Send AJAX request to update stage
                        fetch(`/admin/crm/leads/${leadId}/stage`, {
                            method: 'PATCH',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({
                                stage_id: toStageId
                            })
                        }).then(res => res.json())
                        .then(data => {
                            if(data.success) {
                                // optional: show toast
                                console.log('Stage updated!');
                            }
                        });
                    }
                },
            });
        });
    });
</script>
@endsection
