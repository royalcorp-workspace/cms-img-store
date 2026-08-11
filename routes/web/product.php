<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Product\ProductController;
use App\Http\Controllers\Product\CategoryController;
use App\Http\Controllers\Product\VoucherController;
use App\Http\Controllers\Product\InventoryController;
use App\Http\Controllers\Product\PriceProductSettingController;

Route::get('/products', [ProductController::class, 'index'])->name('products.index');
Route::get('/products/import', [ProductController::class, 'importForm'])->name('products.import.form');
Route::post('/products/import', [ProductController::class, 'importStore'])->name('products.import.store');
Route::get('/products/import/template', [ProductController::class, 'importTemplate'])->name('products.import.template');
Route::get('/products/create', [ProductController::class, 'create'])->name('products.create');
Route::post('/products', [ProductController::class, 'store'])->name('products.store');
Route::get('/products/{id}', [ProductController::class, 'show'])->name('products.show');
Route::get('/products/{id}/edit', [ProductController::class, 'edit'])->name('products.edit');
Route::put('/products/{id}', [ProductController::class, 'update'])->name('products.update');
Route::delete('/products/{id}', [ProductController::class, 'destroy'])->name('products.destroy');

Route::get('/categories', [CategoryController::class, 'index'])->name('categories.index');
Route::get('/categories/flat', [CategoryController::class, 'flat'])->name('categories.flat');
Route::post('/categories', [CategoryController::class, 'store'])->name('categories.store');
Route::get('/categories/{id}/edit', [CategoryController::class, 'edit'])->name('categories.edit');
Route::put('/categories/{id}', [CategoryController::class, 'update'])->name('categories.update');
Route::delete('/categories/{id}', [CategoryController::class, 'destroy'])->name('categories.destroy');

Route::get('/vouchers', [VoucherController::class, 'index'])->name('vouchers.index');
Route::get('/vouchers/create', [VoucherController::class, 'create'])->name('vouchers.create');
Route::post('/vouchers', [VoucherController::class, 'store'])->name('vouchers.store');
Route::get('/vouchers/{id}/edit', [VoucherController::class, 'edit'])->name('vouchers.edit');
Route::put('/vouchers/{id}', [VoucherController::class, 'update'])->name('vouchers.update');
Route::delete('/vouchers/{id}', [VoucherController::class, 'destroy'])->name('vouchers.destroy');

Route::get('/inventory', [InventoryController::class, 'index'])->name('inventory.index');
Route::get('/inventory/create', [InventoryController::class, 'create'])->name('inventory.create');

Route::post('/price-settings/bulk', [PriceProductSettingController::class, 'bulkStore'])->name('price-settings.bulk.store');
Route::get('/price-settings', [PriceProductSettingController::class, 'index'])->name('price-settings.index');
Route::get('/price-settings/bulk', [PriceProductSettingController::class, 'bulk'])->name('price-settings.bulk');
Route::get('/price-settings/create', [PriceProductSettingController::class, 'create'])->name('price-settings.create');
Route::get('/price-settings/{id}/edit', [PriceProductSettingController::class, 'edit'])->name('price-settings.edit');
Route::put('/price-settings/{id}', [PriceProductSettingController::class, 'update'])->name('price-settings.update');
Route::post('/price-settings', [PriceProductSettingController::class, 'store'])->name('price-settings.store');

use App\Http\Controllers\Product\BrandController;
Route::get('/brands', [BrandController::class, 'index'])->name('brands.index');
Route::get('/brands/create', [BrandController::class, 'create'])->name('brands.create');
Route::post('/brands', [BrandController::class, 'store'])->name('brands.store');
Route::get('/brands/{id}/edit', [BrandController::class, 'edit'])->name('brands.edit');
Route::put('/brands/{id}', [BrandController::class, 'update'])->name('brands.update');
Route::delete('/brands/{id}', [BrandController::class, 'destroy'])->name('brands.destroy');

use App\Http\Controllers\Product\ProductBundlingController;
Route::get('/bundlings', [ProductBundlingController::class, 'index'])->name('bundlings.index');
Route::get('/bundlings/create', [ProductBundlingController::class, 'create'])->name('bundlings.create');
Route::post('/bundlings', [ProductBundlingController::class, 'store'])->name('bundlings.store');
Route::get('/bundlings/{id}/edit', [ProductBundlingController::class, 'edit'])->name('bundlings.edit');
Route::put('/bundlings/{id}', [ProductBundlingController::class, 'update'])->name('bundlings.update');
Route::delete('/bundlings/{id}', [ProductBundlingController::class, 'destroy'])->name('bundlings.destroy');

use App\Http\Controllers\Product\EventController;
Route::get('/events', [EventController::class, 'index'])->name('events.index');
Route::get('/events/create', [EventController::class, 'create'])->name('events.create');
Route::post('/events', [EventController::class, 'store'])->name('events.store');
Route::get('/events/{id}/edit', [EventController::class, 'edit'])->name('events.edit');
Route::put('/events/{id}', [EventController::class, 'update'])->name('events.update');
Route::delete('/events/{id}', [EventController::class, 'destroy'])->name('events.destroy');
