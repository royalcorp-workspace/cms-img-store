<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Picking\PickingListController;

Route::prefix('picking-list')->name('picking-list.')->group(function () {
    Route::get('/', [PickingListController::class, 'index'])->name('index');
    Route::get('/create/{order_id?}', [PickingListController::class, 'create'])->name('create');
    Route::get('/{id}', [PickingListController::class, 'show'])->name('show');
    Route::post('/', [PickingListController::class, 'store'])->name('store');
    Route::post('/{id}/item', [PickingListController::class, 'updateItem'])->name('update-item');
});
