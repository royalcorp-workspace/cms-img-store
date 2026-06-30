<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\VoucherController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\PriceProductSettingController;

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::get('/login', function () {
    return view('auth.login');
})->name('login');

Route::get('/register', function () {
    return view('auth.register');
})->name('register');

Route::get('/dashboard', [HomeController::class, 'dashboard'])->name('dashboard');

Route::get('/products', [ProductController::class, 'index'])->name('products.index');
Route::get('/products/create', [ProductController::class, 'create'])->name('products.create');
Route::post('/products', [ProductController::class, 'store'])->name('products.store');
Route::get('/products/{id}', [ProductController::class, 'show'])->name('products.show');
Route::get('/products/{id}/edit', [ProductController::class, 'edit'])->name('products.edit');
Route::put('/products/{id}', [ProductController::class, 'update'])->name('products.update');
Route::delete('/products/{id}', [ProductController::class, 'destroy'])->name('products.destroy');

Route::get('/users', [UserController::class, 'index'])->name('users.index');

Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');

Route::get('/categories', [CategoryController::class, 'index'])->name('categories.index');
Route::post('/categories', [CategoryController::class, 'store'])->name('categories.store');
Route::get('/categories/{id}/edit', [CategoryController::class, 'edit'])->name('categories.edit');
Route::put('/categories/{id}', [CategoryController::class, 'update'])->name('categories.update');
Route::delete('/categories/{id}', [CategoryController::class, 'destroy'])->name('categories.destroy');

Route::get('/customers', [CustomerController::class, 'index'])->name('customers.index');
Route::get('/customers/create', [CustomerController::class, 'create'])->name('customers.create');
Route::post('/customers', [CustomerController::class, 'store'])->name('customers.store');
Route::get('/customers/{id}', [CustomerController::class, 'show'])->name('customers.show');
Route::get('/customers/{id}/edit', [CustomerController::class, 'edit'])->name('customers.edit');
Route::put('/customers/{id}', [CustomerController::class, 'update'])->name('customers.update');
Route::delete('/customers/{id}', [CustomerController::class, 'destroy'])->name('customers.destroy');

Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{id}', [OrderController::class, 'show'])->name('orders.show');

Route::get('/vouchers', [VoucherController::class, 'index'])->name('vouchers.index');
Route::get('/vouchers/create', [VoucherController::class, 'create'])->name('vouchers.create');
Route::post('/vouchers', [VoucherController::class, 'store'])->name('vouchers.store');
Route::get('/vouchers/{id}/edit', [VoucherController::class, 'edit'])->name('vouchers.edit');
Route::put('/vouchers/{id}', [VoucherController::class, 'update'])->name('vouchers.update');
Route::delete('/vouchers/{id}', [VoucherController::class, 'destroy'])->name('vouchers.destroy');

Route::get('/inventory', [InventoryController::class, 'index'])->name('inventory.index');
Route::get('/inventory/create', [InventoryController::class, 'create'])->name('inventory.create');

Route::get('/roles', [RoleController::class, 'index'])->name('roles.index');

Route::get('/permissions', [PermissionController::class, 'index'])->name('permissions.index');

Route::post('/price-settings/bulk', [PriceProductSettingController::class, 'bulkStore'])->name('price-settings.bulk.store');
Route::get('/price-settings', [PriceProductSettingController::class, 'index'])->name('price-settings.index');
Route::get('/price-settings/bulk', [PriceProductSettingController::class, 'bulk'])->name('price-settings.bulk');
Route::get('/price-settings/create', [PriceProductSettingController::class, 'create'])->name('price-settings.create');
Route::get('/price-settings/{id}/edit', [PriceProductSettingController::class, 'edit'])->name('price-settings.edit');
Route::put('/price-settings/{id}', [PriceProductSettingController::class, 'update'])->name('price-settings.update');
Route::post('/price-settings', [PriceProductSettingController::class, 'store'])->name('price-settings.store');
