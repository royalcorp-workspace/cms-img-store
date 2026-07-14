@extends('layouts.app')

@section('title', 'Create Packing Out')

@section('content')
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="font-headline-lg text-headline-lg text-on-surface">Create Packing Out</h1>
            <nav class="flex items-center gap-2 text-body-md text-on-surface-variant mt-1">
                <a href="{{ route('dashboard') }}" class="text-primary hover:underline">Dashboard</a>
                <span class="material-symbols-outlined text-[16px]">chevron_right</span>
                <span>Pick & Pack</span>
                <span class="material-symbols-outlined text-[16px]">chevron_right</span>
                <a href="{{ route('packing-out.index') }}" class="text-primary hover:underline">Packing Out</a>
                <span class="material-symbols-outlined text-[16px]">chevron_right</span>
                <span>Create</span>
            </nav>
        </div>
        <a href="{{ route('packing-slip.show', $packingSlip->id) }}" class="flex items-center gap-2 px-4 py-2 bg-surface-container text-on-surface rounded-lg font-label-md text-label-md hover:opacity-90 transition-all">
            <span class="material-symbols-outlined text-[18px]">arrow_back</span> Back to Packing Slip
        </a>
    </div>

    @if ($errors->any())
        <div class="bg-danger-100 border border-danger-200 text-danger-700 px-4 py-3 rounded-lg mb-6">
            <ul class="list-disc list-inside">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('packing-out.store') }}" method="POST">
        @csrf
        <input type="hidden" name="packing_slip_id" value="{{ $packingSlip->id }}">

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
            <!-- Packing Out Details -->
            <div class="lg:col-span-2 space-y-6">
                <div class="bg-white rounded-xl shadow-sm border border-outline-variant/30 p-6">
                    <h2 class="font-headline-md text-headline-md text-on-surface mb-4 font-semibold">Packing Out Setup</h2>
                    
                    <div class="space-y-4">
                        <div>
                            <label for="warehouse_id" class="block font-label-md text-label-md text-on-surface-variant mb-2">Select Warehouse</label>
                            <select name="warehouse_id" id="warehouse_id" 
                                class="w-full px-4 py-2.5 border border-outline/30 rounded-lg focus:outline-none focus:border-primary transition-colors text-body-md bg-white" required>
                                <option value="" disabled selected>-- Choose Warehouse --</option>
                                @foreach(\App\Models\Warehouse\Warehouse::where('status', true)->get() as $wh)
                                    <option value="{{ $wh->id }}">{{ $wh->name }} ({{ $wh->code }})</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="notes" class="block font-label-md text-label-md text-on-surface-variant mb-2">Notes (Optional)</label>
                            <textarea name="notes" id="notes" rows="4" placeholder="Add optional packaging or shipping instructions..."
                                class="w-full px-4 py-2.5 border border-outline/30 rounded-lg focus:outline-none focus:border-primary transition-colors text-body-md"></textarea>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Packing Slip Metadata & Submit Card -->
            <div class="space-y-6">
                <div class="bg-white rounded-xl shadow-sm border border-outline-variant/30 p-6">
                    <h2 class="font-headline-md text-headline-md text-on-surface mb-4 font-semibold">Summary Details</h2>
                    <div class="space-y-4">
                        <div>
                            <p class="text-label-sm text-on-surface-variant mb-1">Packing Slip ID</p>
                            <p class="font-body-md text-body-md text-on-surface font-semibold">#{{ substr($packingSlip->id, 0, 8) }}</p>
                        </div>
                        <div>
                            <p class="text-label-sm text-on-surface-variant mb-1">Order Ref</p>
                            <p class="font-body-md text-body-md text-on-surface">#{{ substr($packingSlip->order->id, 0, 8) }}</p>
                        </div>
                        <div>
                            <p class="text-label-sm text-on-surface-variant mb-1">Customer</p>
                            <p class="font-body-md text-body-md text-on-surface font-semibold">{{ $packingSlip->order->customer->name ?? 'N/A' }}</p>
                        </div>
                        <div>
                            <p class="text-label-sm text-on-surface-variant mb-1">Boxes to Ship</p>
                            <p class="font-body-md text-body-md text-on-surface font-semibold bg-surface-container px-2 py-0.5 rounded inline-block">{{ $packingSlip->box_count }}</p>
                        </div>
                        <div class="pt-4 border-t border-outline-variant/20">
                            <button type="submit" class="w-full flex items-center justify-center gap-2 px-4 py-2.5 bg-primary text-white rounded-lg font-label-md text-label-md hover:opacity-90 transition-all shadow-sm">
                                <span class="material-symbols-outlined text-[18px]">done</span> Create Packing Out
                            </button>
                            <a href="{{ route('packing-slip.show', $packingSlip->id) }}" class="w-full mt-2 flex items-center justify-center gap-2 px-4 py-2.5 bg-surface-container text-on-surface rounded-lg font-label-md text-label-md hover:bg-surface-container-high transition-all">
                                Cancel
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
@endsection
