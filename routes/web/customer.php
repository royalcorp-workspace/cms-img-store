<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Customer\CustomerController;

Route::get('/customers', [CustomerController::class, 'index'])->name('customers.index');
Route::get('/customers/create', [CustomerController::class, 'create'])->name('customers.create');
Route::post('/customers', [CustomerController::class, 'store'])->name('customers.store');
Route::get('/customers/{id}', [CustomerController::class, 'show'])->name('customers.show');
Route::get('/customers/{id}/edit', [CustomerController::class, 'edit'])->name('customers.edit');
Route::put('/customers/{id}', [CustomerController::class, 'update'])->name('customers.update');
Route::delete('/customers/{id}', [CustomerController::class, 'destroy'])->name('customers.destroy');

// Customer Addresses routes (Embedded in edit customer)
Route::post('/customers/{customer}/addresses', [\App\Http\Controllers\Customer\CustomerAddressController::class, 'store'])->name('customers.addresses.store');
Route::get('/customers/{customer}/addresses/{address}/edit', [\App\Http\Controllers\Customer\CustomerAddressController::class, 'edit'])->name('customers.addresses.edit');
Route::put('/customers/{customer}/addresses/{address}', [\App\Http\Controllers\Customer\CustomerAddressController::class, 'update'])->name('customers.addresses.update');
Route::delete('/customers/{customer}/addresses/{address}', [\App\Http\Controllers\Customer\CustomerAddressController::class, 'destroy'])->name('customers.addresses.destroy');
