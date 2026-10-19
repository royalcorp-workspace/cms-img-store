<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\HomeController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\ProfileController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\PermissionController;

Route::get('/dashboard', [HomeController::class, 'dashboard'])->name('dashboard');

Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');

// Role Management Routes
Route::resource('roles', RoleController::class);

// Permission Management Routes
Route::get('/permissions', [PermissionController::class, 'index'])->name('permissions.index');
Route::post('/permissions/sync', [PermissionController::class, 'sync'])->name('permissions.sync');

// User Management Routes
Route::resource('users', UserController::class);

// Real-Time Notification Routes
Route::prefix('notifications')->name('notifications.')->group(function () {
    Route::get('/', [\App\Http\Controllers\Admin\NotificationController::class, 'index'])->name('index');
    Route::post('/{id}/read', [\App\Http\Controllers\Admin\NotificationController::class, 'markAsRead'])->name('read');
    Route::post('/mark-all-read', [\App\Http\Controllers\Admin\NotificationController::class, 'markAllAsRead'])->name('mark-all-read');
    Route::post('/test-stock-alert', [\App\Http\Controllers\Admin\NotificationController::class, 'testStockAlert'])->name('test-stock-alert');
});
