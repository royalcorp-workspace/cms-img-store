<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Store\StoreController;
use App\Http\Controllers\Store\StoreTierController;
use App\Http\Controllers\Store\StoreChannelGroupController;
use App\Http\Controllers\Store\StoreChannelController;
use App\Http\Controllers\Store\StoreChannelStockController;
use App\Http\Controllers\Store\PriceProductSettingStoreController;
use App\Http\Controllers\Store\StoreGroupController;

Route::get('/store-groups', [StoreGroupController::class, 'index'])->name('store-groups.index');
Route::get('/store-groups/create', [StoreGroupController::class, 'create'])->name('store-groups.create');
Route::post('/store-groups', [StoreGroupController::class, 'store'])->name('store-groups.store');
Route::get('/store-groups/{id}/edit', [StoreGroupController::class, 'edit'])->name('store-groups.edit');
Route::put('/store-groups/{id}', [StoreGroupController::class, 'update'])->name('store-groups.update');
Route::delete('/store-groups/{id}', [StoreGroupController::class, 'destroy'])->name('store-groups.destroy');

Route::get('/store-tiers', [StoreTierController::class, 'index'])->name('store-tiers.index');
Route::get('/store-tiers/create', [StoreTierController::class, 'create'])->name('store-tiers.create');
Route::post('/store-tiers', [StoreTierController::class, 'store'])->name('store-tiers.store');
Route::get('/store-tiers/{id}/edit', [StoreTierController::class, 'edit'])->name('store-tiers.edit');
Route::put('/store-tiers/{id}', [StoreTierController::class, 'update'])->name('store-tiers.update');
Route::delete('/store-tiers/{id}', [StoreTierController::class, 'destroy'])->name('store-tiers.destroy');

Route::get('/stores', [StoreController::class, 'index'])->name('stores.index');
Route::get('/stores/create', [StoreController::class, 'create'])->name('stores.create');
Route::post('/stores', [StoreController::class, 'store'])->name('stores.store');
Route::get('/stores/{id}/edit', [StoreController::class, 'edit'])->name('stores.edit');
Route::put('/stores/{id}', [StoreController::class, 'update'])->name('stores.update');
Route::delete('/stores/{id}', [StoreController::class, 'destroy'])->name('stores.destroy');

Route::get('/store-channel-groups', [StoreChannelGroupController::class, 'index'])->name('store-channel-groups.index');
Route::get('/store-channel-groups/create', [StoreChannelGroupController::class, 'create'])->name('store-channel-groups.create');
Route::post('/store-channel-groups', [StoreChannelGroupController::class, 'store'])->name('store-channel-groups.store');
Route::get('/store-channel-groups/{id}/edit', [StoreChannelGroupController::class, 'edit'])->name('store-channel-groups.edit');
Route::put('/store-channel-groups/{id}', [StoreChannelGroupController::class, 'update'])->name('store-channel-groups.update');
Route::delete('/store-channel-groups/{id}', [StoreChannelGroupController::class, 'destroy'])->name('store-channel-groups.destroy');

Route::get('/store-channels', [StoreChannelController::class, 'index'])->name('store-channels.index');
Route::get('/store-channels/create', [StoreChannelController::class, 'create'])->name('store-channels.create');
Route::post('/store-channels', [StoreChannelController::class, 'store'])->name('store-channels.store');
Route::get('/store-channels/{id}/edit', [StoreChannelController::class, 'edit'])->name('store-channels.edit');
Route::put('/store-channels/{id}', [StoreChannelController::class, 'update'])->name('store-channels.update');
Route::delete('/store-channels/{id}', [StoreChannelController::class, 'destroy'])->name('store-channels.destroy');

Route::get('/store-channel-stocks', [StoreChannelStockController::class, 'index'])->name('store-channel-stocks.index');
Route::get('/store-channel-stocks/create', [StoreChannelStockController::class, 'create'])->name('store-channel-stocks.create');
Route::post('/store-channel-stocks', [StoreChannelStockController::class, 'store'])->name('store-channel-stocks.store');
Route::get('/store-channel-stocks/{id}/edit', [StoreChannelStockController::class, 'edit'])->name('store-channel-stocks.edit');
Route::put('/store-channel-stocks/{id}', [StoreChannelStockController::class, 'update'])->name('store-channel-stocks.update');
Route::delete('/store-channel-stocks/{id}', [StoreChannelStockController::class, 'destroy'])->name('store-channel-stocks.destroy');

Route::get('/price-product-setting-store', [PriceProductSettingStoreController::class, 'index'])->name('price-product-setting-store.index');
Route::get('/price-product-setting-store/create', [PriceProductSettingStoreController::class, 'create'])->name('price-product-setting-store.create');
Route::post('/price-product-setting-store', [PriceProductSettingStoreController::class, 'store'])->name('price-product-setting-store.store');
Route::get('/price-product-setting-store/{id}/edit', [PriceProductSettingStoreController::class, 'edit'])->name('price-product-setting-store.edit');
Route::put('/price-product-setting-store/{id}', [PriceProductSettingStoreController::class, 'update'])->name('price-product-setting-store.update');
Route::delete('/price-product-setting-store/{id}', [PriceProductSettingStoreController::class, 'destroy'])->name('price-product-setting-store.destroy');
