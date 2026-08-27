<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Order\OrderController;
use App\Http\Controllers\Order\ReconciliationController;
use App\Http\Controllers\Order\SettlementController;

Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
Route::get('/orders/{id}', [OrderController::class, 'show'])->name('orders.show');
Route::post('/orders/{id}/verify-payment', [OrderController::class, 'verifyPayment'])->name('orders.verify-payment');

Route::get('/settlements', [SettlementController::class, 'index'])->name('settlements.index');
Route::get('/settlements/{id}', [SettlementController::class, 'show'])->name('settlements.show');

Route::get('/reconciliation', [ReconciliationController::class, 'index'])->name('reconciliation.index');

use App\Http\Controllers\Order\VoidOrderController;

Route::get('/void-orders', [VoidOrderController::class, 'index'])->name('orders.void.index');
Route::get('/void-orders/{id}', [VoidOrderController::class, 'show'])->name('orders.void.show');
Route::post('/void-orders/restore', [VoidOrderController::class, 'restore'])->name('orders.void.restore');
