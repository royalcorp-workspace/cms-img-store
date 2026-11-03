<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Jde\JdeIntegrationController;

Route::prefix('admin/jde')->name('admin.jde.')->group(function () {
    Route::get('/', [JdeIntegrationController::class, 'index'])->name('index');
    Route::post('/pull', [JdeIntegrationController::class, 'pull'])->name('pull');
    Route::post('/upload', [JdeIntegrationController::class, 'upload'])->name('upload');
    Route::post('/item-master/{id}/copy', [JdeIntegrationController::class, 'copyItemMaster'])->name('item-master.copy');
    Route::post('/item-master/copy-batch', [JdeIntegrationController::class, 'copyBatchItemMasters'])->name('item-master.copy-batch');
});
