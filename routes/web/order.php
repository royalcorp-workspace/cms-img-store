<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Order\OrderController;
use App\Http\Controllers\Order\ReconciliationController;

Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
Route::get('/orders/{id}', [OrderController::class, 'show'])->name('orders.show');
Route::post('/orders/{id}/verify-payment', [OrderController::class, 'verifyPayment'])->name('orders.verify-payment');

Route::get('/reconciliation', [ReconciliationController::class, 'index'])->name('reconciliation.index');
