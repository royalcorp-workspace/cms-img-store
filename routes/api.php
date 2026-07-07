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
use App\Http\Controllers\Api\PriceProductSettingStoreController;
use App\Http\Controllers\Api\StoreGroupController;
use App\Http\Controllers\Api\StoreController;
use App\Http\Controllers\Api\StoreTierController;
use App\Http\Controllers\Api\StoreChannelGroupController;
use App\Http\Controllers\Api\StoreChannelController;
use App\Http\Controllers\Api\Content\FaqController;
use App\Http\Controllers\Api\Content\BlogPostController;
use App\Http\Controllers\Api\Content\AboutUsController;
use App\Http\Controllers\Api\Content\HowToReturnController;
use App\Http\Controllers\Api\Content\TermsAndConditionController;
use App\Http\Controllers\Api\Content\PrivacyPolicyController;
use App\Http\Controllers\Api\Content\WarrantyClaimController;

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

        Route::get('price-product-setting-store', [PriceProductSettingStoreController::class, 'index']);
        Route::post('price-product-setting-store', [PriceProductSettingStoreController::class, 'store']);
        Route::get('price-product-setting-store/{id}', [PriceProductSettingStoreController::class, 'show']);
        Route::put('price-product-setting-store/{id}', [PriceProductSettingStoreController::class, 'update']);
        Route::delete('price-product-setting-store/{id}', [PriceProductSettingStoreController::class, 'destroy']);

        Route::get('store-groups', [StoreGroupController::class, 'index']);
        Route::post('store-groups', [StoreGroupController::class, 'store']);
        Route::get('store-groups/{id}', [StoreGroupController::class, 'show']);
        Route::put('store-groups/{id}', [StoreGroupController::class, 'update']);
        Route::delete('store-groups/{id}', [StoreGroupController::class, 'destroy']);

        Route::get('store-tiers', [StoreTierController::class, 'index']);
        Route::post('store-tiers', [StoreTierController::class, 'store']);
        Route::get('store-tiers/{id}', [StoreTierController::class, 'show']);
        Route::put('store-tiers/{id}', [StoreTierController::class, 'update']);
        Route::delete('store-tiers/{id}', [StoreTierController::class, 'destroy']);

        Route::get('stores', [StoreController::class, 'index']);
        Route::post('stores', [StoreController::class, 'store']);
        Route::get('stores/{id}', [StoreController::class, 'show']);
        Route::put('stores/{id}', [StoreController::class, 'update']);
        Route::delete('stores/{id}', [StoreController::class, 'destroy']);

        Route::get('store-channel-groups', [StoreChannelGroupController::class, 'index']);
        Route::post('store-channel-groups', [StoreChannelGroupController::class, 'store']);
        Route::get('store-channel-groups/{id}', [StoreChannelGroupController::class, 'show']);
        Route::put('store-channel-groups/{id}', [StoreChannelGroupController::class, 'update']);
        Route::delete('store-channel-groups/{id}', [StoreChannelGroupController::class, 'destroy']);

        Route::get('store-channels', [StoreChannelController::class, 'index']);
        Route::post('store-channels', [StoreChannelController::class, 'store']);
        Route::get('store-channels/{id}', [StoreChannelController::class, 'show']);
        Route::put('store-channels/{id}', [StoreChannelController::class, 'update']);
        Route::delete('store-channels/{id}', [StoreChannelController::class, 'destroy']);

        Route::get('faqs', [FaqController::class, 'index']);
        Route::get('faqs/{id}', [FaqController::class, 'show']);

        Route::get('blog-posts', [BlogPostController::class, 'index']);
        Route::get('blog-posts/{id}', [BlogPostController::class, 'show']);

        Route::get('about-us', [AboutUsController::class, 'index']);

        Route::get('how-to-returns', [HowToReturnController::class, 'index']);
        Route::get('how-to-returns/{id}', [HowToReturnController::class, 'show']);

        Route::get('terms', [TermsAndConditionController::class, 'index']);
        Route::get('terms/{id}', [TermsAndConditionController::class, 'show']);

        Route::get('privacy', [PrivacyPolicyController::class, 'index']);
        Route::get('privacy/{id}', [PrivacyPolicyController::class, 'show']);

        Route::get('warranty', [WarrantyClaimController::class, 'index']);
        Route::get('warranty/{id}', [WarrantyClaimController::class, 'show']);
    });
});