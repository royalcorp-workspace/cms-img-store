@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="mb-8">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="font-headline-lg text-headline-lg text-on-surface">Dashboard</h1>
            <p class="text-body-md text-on-surface-variant mt-1">Welcome back, Admin User</p>
        </div>
        <div class="flex items-center gap-3">
            <form method="GET" class="flex items-center gap-2">
                <input type="month" name="month" value="{{ $month }}" class="px-3 py-2 border border-outline-variant rounded-lg text-body-md focus:ring-2 focus:ring-primary/20 focus:outline-none bg-white" onchange="this.form.submit()">
            </form>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-container-gap mb-8">
    <div class="bg-white p-card-padding rounded-xl shadow-sm border border-outline-variant/30">
        <p class="text-on-surface-variant font-label-md text-label-md uppercase tracking-wider">Total Orders</p>
        <h3 class="font-metric-display text-metric-display text-on-surface mt-1">{{ number_format($stats['total'] ?? 0) }}</h3>
    </div>
    <div class="bg-white p-card-padding rounded-xl shadow-sm border border-outline-variant/30">
        <p class="text-on-surface-variant font-label-md text-label-md uppercase tracking-wider">Draft</p>
        <h3 class="font-metric-display text-metric-display text-gray-500 mt-1">{{ number_format($stats['draft'] ?? 0) }}</h3>
    </div>
    <div class="bg-white p-card-padding rounded-xl shadow-sm border border-outline-variant/30">
        <p class="text-on-surface-variant font-label-md text-label-md uppercase tracking-wider">Pending Approval</p>
        <h3 class="font-metric-display text-metric-display text-warning mt-1">{{ number_format($stats['pending'] ?? 0) }}</h3>
    </div>
    <div class="bg-white p-card-padding rounded-xl shadow-sm border border-outline-variant/30">
        <p class="text-on-surface-variant font-label-md text-label-md uppercase tracking-wider">Confirmed</p>
        <h3 class="font-metric-display text-metric-display text-blue-500 mt-1">{{ number_format($stats['confirmed'] ?? 0) }}</h3>
    </div>
    <div class="bg-white p-card-padding rounded-xl shadow-sm border border-outline-variant/30">
        <p class="text-on-surface-variant font-label-md text-label-md uppercase tracking-wider">Processing</p>
        <h3 class="font-metric-display text-metric-display text-indigo-500 mt-1">{{ number_format($stats['processing'] ?? 0) }}</h3>
    </div>
    <div class="bg-white p-card-padding rounded-xl shadow-sm border border-outline-variant/30">
        <p class="text-on-surface-variant font-label-md text-label-md uppercase tracking-wider">Shipped</p>
        <h3 class="font-metric-display text-metric-display text-purple-500 mt-1">{{ number_format($stats['shipped'] ?? 0) }}</h3>
    </div>
    <div class="bg-white p-card-padding rounded-xl shadow-sm border border-outline-variant/30">
        <p class="text-on-surface-variant font-label-md text-label-md uppercase tracking-wider">Delivered</p>
        <h3 class="font-metric-display text-metric-display text-success mt-1">{{ number_format($stats['delivered'] ?? 0) }}</h3>
    </div>
    <div class="bg-white p-card-padding rounded-xl shadow-sm border border-outline-variant/30">
        <p class="text-on-surface-variant font-label-md text-label-md uppercase tracking-wider">Cancelled</p>
        <h3 class="font-metric-display text-metric-display text-danger mt-1">{{ number_format($stats['cancelled'] ?? 0) }}</h3>
    </div>
    <div class="bg-white p-card-padding rounded-xl shadow-sm border border-outline-variant/30">
        <p class="text-on-surface-variant font-label-md text-label-md uppercase tracking-wider">Returned</p>
        <h3 class="font-metric-display text-metric-display text-orange-500 mt-1">{{ number_format($stats['returned'] ?? 0) }}</h3>
    </div>
</div>


@endsection