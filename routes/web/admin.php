<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\HomeController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\ProfileController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\PermissionController;

Route::get('/dashboard', [HomeController::class, 'dashboard'])->name('dashboard');

Route::get('/users', [UserController::class, 'index'])->name('users.index');

Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');

Route::get('/roles', [RoleController::class, 'index'])->name('roles.index');

Route::get('/permissions', [PermissionController::class, 'index'])->name('permissions.index');
