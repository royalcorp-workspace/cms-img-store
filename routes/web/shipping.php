<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Shipping\ShippingAddressController;
use App\Http\Controllers\Shipping\CourierController;
use App\Http\Controllers\Shipping\PaymentMethodController;

Route::get('/couriers', [CourierController::class, 'index'])->name('couriers.index');
Route::get('/couriers/create', [CourierController::class, 'create'])->name('couriers.create');
Route::post('/couriers', [CourierController::class, 'store'])->name('couriers.store');
Route::get('/couriers/{id}/edit', [CourierController::class, 'edit'])->name('couriers.edit');
Route::put('/couriers/{id}', [CourierController::class, 'update'])->name('couriers.update');
Route::delete('/couriers/{id}', [CourierController::class, 'destroy'])->name('couriers.destroy');

Route::get('/shipping-addresses', [ShippingAddressController::class, 'index'])->name('shipping-addresses.index');
Route::get('/shipping-addresses/create', [ShippingAddressController::class, 'create'])->name('shipping-addresses.create');
Route::post('/shipping-addresses', [ShippingAddressController::class, 'store'])->name('shipping-addresses.store');
Route::post('/shipping-addresses/save-inline', [ShippingAddressController::class, 'saveInline'])->name('shipping-addresses.save-inline');
Route::get('/shipping-addresses/{id}/edit', [ShippingAddressController::class, 'edit'])->name('shipping-addresses.edit');
Route::put('/shipping-addresses/{id}', [ShippingAddressController::class, 'update'])->name('shipping-addresses.update');
Route::delete('/shipping-addresses/{id}', [ShippingAddressController::class, 'destroy'])->name('shipping-addresses.destroy');

Route::get('/payment-methods', [PaymentMethodController::class, 'index'])->name('payment-methods.index');
Route::get('/payment-methods/create', [PaymentMethodController::class, 'create'])->name('payment-methods.create');
Route::post('/payment-methods', [PaymentMethodController::class, 'store'])->name('payment-methods.store');
Route::get('/payment-methods/{id}/edit', [PaymentMethodController::class, 'edit'])->name('payment-methods.edit');
Route::put('/payment-methods/{id}', [PaymentMethodController::class, 'update'])->name('payment-methods.update');
Route::delete('/payment-methods/{id}', [PaymentMethodController::class, 'destroy'])->name('payment-methods.destroy');
