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
