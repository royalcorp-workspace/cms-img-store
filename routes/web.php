<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\HomeController;
use App\Http\Controllers\Product\CategoryController;
use App\Http\Controllers\Auth\LoginController;

Route::get('/', function () {
    return redirect()->route('login');
});
Route::get('/category/{slug}', [CategoryController::class, 'show'])->name('categories.show');

// Public route bypass for screenshots
Route::get('/dashboard', [HomeController::class, 'dashboard'])->name('dashboard');

require __DIR__ . '/web/auth.php';

Route::middleware(['auth:admin', 'admin'])->group(function () {
   
    require __DIR__ . '/web/admin.php';
    require __DIR__ . '/web/product.php';
    require __DIR__ . '/web/store.php';
    require __DIR__ . '/web/content.php';
    require __DIR__ . '/web/order.php';
    require __DIR__ . '/web/picking.php';
    require __DIR__ . '/web/packing.php';
    require __DIR__ . '/web/shipping.php';
    require __DIR__ . '/web/customer.php';
    require __DIR__ . '/web/chat.php';
});
