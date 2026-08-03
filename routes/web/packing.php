<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Packing\PackingSlipController;
use App\Http\Controllers\Packing\PackingOutController;
use App\Http\Controllers\Packing\DeliveryController;
use App\Http\Controllers\Packing\HandoverController;

Route::prefix('packing-slip')->name('packing-slip.')->group(function () {
    Route::get('/', [PackingSlipController::class, 'index'])->name('index');
    Route::get('/{id}', [PackingSlipController::class, 'show'])->name('show');
    Route::get('/order/{order_id}', [PackingSlipController::class, 'create'])->name('create');
    Route::post('/', [PackingSlipController::class, 'store'])->name('store');
});

Route::prefix('packing-out')->name('packing-out.')->group(function () {
    Route::get('/', [PackingOutController::class, 'index'])->name('index');
    Route::get('/{id}', [PackingOutController::class, 'show'])->name('show');
    Route::get('/packing-slip/{packing_slip_id}', [PackingOutController::class, 'create'])->name('create');
    Route::post('/', [PackingOutController::class, 'store'])->name('store');
    Route::post('/{id}/confirm', [PackingOutController::class, 'confirmOut'])->name('confirm');
});

Route::prefix('delivery')->name('delivery.')->group(function () {
    Route::get('/', [DeliveryController::class, 'index'])->name('index');
    Route::get('/{id}', [DeliveryController::class, 'show'])->name('show');
    Route::get('/packing-out/{packing_out_id}', [DeliveryController::class, 'create'])->name('create');
    Route::post('/', [DeliveryController::class, 'store'])->name('store');
    Route::put('/{id}/status', [DeliveryController::class, 'updateStatus'])->name('update-status');
});

Route::prefix('handover')->name('handover.')->group(function () {
    Route::get('/', [HandoverController::class, 'index'])->name('index');
    Route::get('/{id}', [HandoverController::class, 'show'])->name('show');
    Route::get('/packing-out/{packing_out_id}', [HandoverController::class, 'create'])->name('create');
    Route::post('/', [HandoverController::class, 'store'])->name('store');
});
