<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\MenuController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\CustomerController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\VoucherController;
use App\Http\Controllers\Api\InventoryController;
use App\Http\Controllers\Api\RoleController;
use App\Http\Controllers\Api\PermissionController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\AuthController;

Route::prefix('v1')->group(function () {
    Route::post('login', [AuthController::class, 'login']);
    Route::post('mock-login', [AuthController::class, 'mockLogin']);
    Route::get('mock-login', [AuthController::class, 'getMockLogin']);

    Route::middleware('jwt')->group(function () {
        Route::get('menus', [MenuController::class, 'index']);
        Route::get('menus/{id}', [MenuController::class, 'show']);

        Route::get('products', [ProductController::class, 'index']);
        Route::post('products', [ProductController::class, 'store']);
        Route::get('products/{product}', [ProductController::class, 'show']);
        Route::put('products/{product}', [ProductController::class, 'update']);
        Route::delete('products/{product}', [ProductController::class, 'destroy']);

        Route::post('products/{product}/images', [ProductController::class, 'storeImage']);
        Route::delete('products/images/{image}', [ProductController::class, 'destroyImage']);
        Route::post('products/{product}/variants', [ProductController::class, 'storeVariant']);
        Route::put('products/variants/{variant}', [ProductController::class, 'updateVariant']);
        Route::delete('products/variants/{variant}', [ProductController::class, 'destroyVariant']);
        Route::post('products/{product}/colors', [ProductController::class, 'storeColor']);
        Route::put('products/colors/{color}', [ProductController::class, 'updateColor']);
        Route::delete('products/colors/{color}', [ProductController::class, 'destroyColor']);

        Route::get('categories', [CategoryController::class, 'index']);
        Route::get('categories/flat', [CategoryController::class, 'flat']);
        Route::post('categories', [CategoryController::class, 'store']);
        Route::get('categories/{category}', [CategoryController::class, 'show']);
        Route::put('categories/{category}', [CategoryController::class, 'update']);
        Route::delete('categories/{category}', [CategoryController::class, 'destroy']);

        Route::get('customers', [CustomerController::class, 'index']);
        Route::post('customers', [CustomerController::class, 'store']);
        Route::get('customers/{customer}', [CustomerController::class, 'show']);
        Route::put('customers/{customer}', [CustomerController::class, 'update']);
        Route::delete('customers/{customer}', [CustomerController::class, 'destroy']);

        Route::get('orders', [OrderController::class, 'index']);
        Route::get('orders/{order}', [OrderController::class, 'show']);

        Route::get('vouchers', [VoucherController::class, 'index']);
        Route::post('vouchers', [VoucherController::class, 'store']);
        Route::get('vouchers/{voucher}', [VoucherController::class, 'show']);
        Route::put('vouchers/{voucher}', [VoucherController::class, 'update']);
        Route::delete('vouchers/{voucher}', [VoucherController::class, 'destroy']);

        Route::get('inventory', [InventoryController::class, 'index']);
        Route::post('inventory', [InventoryController::class, 'store']);

        Route::get('roles', [RoleController::class, 'index']);
        Route::post('roles', [RoleController::class, 'store']);
        Route::get('roles/{role}', [RoleController::class, 'show']);
        Route::put('roles/{role}', [RoleController::class, 'update']);
        Route::delete('roles/{role}', [RoleController::class, 'destroy']);

        Route::get('permissions', [PermissionController::class, 'index']);
        Route::post('permissions', [PermissionController::class, 'store']);
        Route::get('permissions/{permission}', [PermissionController::class, 'show']);
        Route::put('permissions/{permission}', [PermissionController::class, 'update']);
        Route::delete('permissions/{permission}', [PermissionController::class, 'destroy']);

        Route::get('users', [UserController::class, 'index']);
        Route::post('users', [UserController::class, 'store']);
        Route::get('users/{user}', [UserController::class, 'show']);
        Route::put('users/{user}', [UserController::class, 'update']);
        Route::delete('users/{user}', [UserController::class, 'destroy']);
    });
});