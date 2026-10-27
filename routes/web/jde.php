<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Jde\JdeIntegrationController;

Route::prefix('admin/jde')->name('admin.jde.')->group(function () {
    Route::get('/', [JdeIntegrationController::class, 'index'])->name('index');
    Route::post('/pull', [JdeIntegrationController::class, 'pull'])->name('pull');
    Route::post('/upload', [JdeIntegrationController::class, 'upload'])->name('upload');
});
