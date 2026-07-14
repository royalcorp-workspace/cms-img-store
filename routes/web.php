<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\VoucherController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\PriceProductSettingController;
use App\Http\Controllers\PriceProductSettingStoreController;
use App\Http\Controllers\StoreGroupController;
use App\Http\Controllers\StoreController;
use App\Http\Controllers\StoreTierController;
use App\Http\Controllers\StoreChannelGroupController;
use App\Http\Controllers\StoreChannelController;
use App\Http\Controllers\Content\FaqController;
use App\Http\Controllers\Content\BlogPostController;
use App\Http\Controllers\Content\AboutUsController;
use App\Http\Controllers\Content\HowToReturnController;
use App\Http\Controllers\Content\TermsAndConditionController;
use App\Http\Controllers\Content\PrivacyPolicyController;
use App\Http\Controllers\Content\WarrantyClaimController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Picking\PickingListController;
use App\Http\Controllers\Packing\PackingSlipController;
use App\Http\Controllers\Packing\PackingOutController;
use App\Http\Controllers\Packing\DeliveryController;
use App\Http\Controllers\Packing\HandoverController;
use App\Http\Controllers\CourierController;
use App\Http\Controllers\PaymentMethodController;
use App\Http\Controllers\ShippingAddressController;

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/category/{slug}', [CategoryController::class, 'show'])->name('categories.show');

Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

Route::get('/register', function () {
    return view('auth.register');
})->name('register');

Route::middleware(['auth:admin', 'admin'])->group(function () {
    Route::get('/dashboard', [HomeController::class, 'dashboard'])->name('dashboard');

    Route::get('/products', [ProductController::class, 'index'])->name('products.index');
    Route::get('/products/import', [ProductController::class, 'importForm'])->name('products.import.form');
    Route::post('/products/import', [ProductController::class, 'importStore'])->name('products.import.store');
    Route::get('/products/import/template', [ProductController::class, 'importTemplate'])->name('products.import.template');
    Route::get('/products/create', [ProductController::class, 'create'])->name('products.create');
    Route::post('/products', [ProductController::class, 'store'])->name('products.store');
    Route::get('/products/{id}', [ProductController::class, 'show'])->name('products.show');
    Route::get('/products/{id}/edit', [ProductController::class, 'edit'])->name('products.edit');
    Route::put('/products/{id}', [ProductController::class, 'update'])->name('products.update');
    Route::delete('/products/{id}', [ProductController::class, 'destroy'])->name('products.destroy');

    Route::get('/users', [UserController::class, 'index'])->name('users.index');

    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');

    Route::get('/categories', [CategoryController::class, 'index'])->name('categories.index');
    Route::get('/categories/flat', [CategoryController::class, 'flat'])->name('categories.flat');
    Route::post('/categories', [CategoryController::class, 'store'])->name('categories.store');
    Route::get('/categories/{id}/edit', [CategoryController::class, 'edit'])->name('categories.edit');
    Route::put('/categories/{id}', [CategoryController::class, 'update'])->name('categories.update');
    Route::delete('/categories/{id}', [CategoryController::class, 'destroy'])->name('categories.destroy');

    Route::get('/customers', [CustomerController::class, 'index'])->name('customers.index');
    Route::get('/customers/create', [CustomerController::class, 'create'])->name('customers.create');
    Route::post('/customers', [CustomerController::class, 'store'])->name('customers.store');
    Route::get('/customers/{id}', [CustomerController::class, 'show'])->name('customers.show');
    Route::get('/customers/{id}/edit', [CustomerController::class, 'edit'])->name('customers.edit');
    Route::put('/customers/{id}', [CustomerController::class, 'update'])->name('customers.update');
    Route::delete('/customers/{id}', [CustomerController::class, 'destroy'])->name('customers.destroy');

    Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{id}', [OrderController::class, 'show'])->name('orders.show');
    Route::post('/orders/{id}/verify-payment', [OrderController::class, 'verifyPayment'])->name('orders.verify-payment');
    Route::get('/reconciliation', [\App\Http\Controllers\ReconciliationController::class, 'index'])->name('reconciliation.index');

    Route::prefix('picking-list')->name('picking-list.')->group(function () {
        Route::get('/', [PickingListController::class, 'index'])->name('index');
        Route::get('/create/{order_id?}', [PickingListController::class, 'create'])->name('create');
        Route::get('/{id}', [PickingListController::class, 'show'])->name('show');
        Route::post('/', [PickingListController::class, 'store'])->name('store');
        Route::post('/{id}/item', [PickingListController::class, 'updateItem'])->name('update-item');
    });

    Route::prefix('packing-slip')->name('packing-slip.')->group(function () {
        Route::get('/', [PackingSlipController::class, 'index'])->name('index');
        Route::get('/{id}', [PackingSlipController::class, 'show'])->name('show');
        Route::get('/order/{order_id}', [PackingSlipController::class, 'create'])->name('create');
        Route::post('/', [PackingSlipController::class, 'store'])->name('store');
    });

    Route::prefix('packing-out')->name('packing-out.')->group(function () {
        Route::get('/', [PackingOutController::class, 'index'])->name('index');
        Route::get('/{id}', [PackingOutController::class, 'show'])->name('show');
        Route::get('/packing-slip/{packing_slip_id}', [PackingOutController::class, 'create'])->name('create');
        Route::post('/', [PackingOutController::class, 'store'])->name('store');
        Route::post('/{id}/confirm', [PackingOutController::class, 'confirmOut'])->name('confirm');
    });

    Route::prefix('delivery')->name('delivery.')->group(function () {
        Route::get('/', [DeliveryController::class, 'index'])->name('index');
        Route::get('/{id}', [DeliveryController::class, 'show'])->name('show');
        Route::get('/packing-out/{packing_out_id}', [DeliveryController::class, 'create'])->name('create');
        Route::post('/', [DeliveryController::class, 'store'])->name('store');
        Route::put('/{id}/status', [DeliveryController::class, 'updateStatus'])->name('update-status');
    });

    Route::prefix('handover')->name('handover.')->group(function () {
        Route::get('/', [HandoverController::class, 'index'])->name('index');
        Route::get('/{id}', [HandoverController::class, 'show'])->name('show');
        Route::get('/packing-out/{packing_out_id}', [HandoverController::class, 'create'])->name('create');
        Route::post('/', [HandoverController::class, 'store'])->name('store');
    });

    Route::get('/vouchers', [VoucherController::class, 'index'])->name('vouchers.index');
    Route::get('/vouchers/create', [VoucherController::class, 'create'])->name('vouchers.create');
    Route::post('/vouchers', [VoucherController::class, 'store'])->name('vouchers.store');
    Route::get('/vouchers/{id}/edit', [VoucherController::class, 'edit'])->name('vouchers.edit');
    Route::put('/vouchers/{id}', [VoucherController::class, 'update'])->name('vouchers.update');
    Route::delete('/vouchers/{id}', [VoucherController::class, 'destroy'])->name('vouchers.destroy');

    Route::get('/inventory', [InventoryController::class, 'index'])->name('inventory.index');
    Route::get('/inventory/create', [InventoryController::class, 'create'])->name('inventory.create');

    Route::get('/roles', [RoleController::class, 'index'])->name('roles.index');

    Route::get('/permissions', [PermissionController::class, 'index'])->name('permissions.index');

    Route::post('/price-settings/bulk', [PriceProductSettingController::class, 'bulkStore'])->name('price-settings.bulk.store');
    Route::get('/price-settings', [PriceProductSettingController::class, 'index'])->name('price-settings.index');
    Route::get('/price-settings/bulk', [PriceProductSettingController::class, 'bulk'])->name('price-settings.bulk');
    Route::get('/price-settings/create', [PriceProductSettingController::class, 'create'])->name('price-settings.create');
    Route::get('/price-settings/{id}/edit', [PriceProductSettingController::class, 'edit'])->name('price-settings.edit');
    Route::put('/price-settings/{id}', [PriceProductSettingController::class, 'update'])->name('price-settings.update');
    Route::post('/price-settings', [PriceProductSettingController::class, 'store'])->name('price-settings.store');

    Route::get('/price-product-setting-store', [PriceProductSettingStoreController::class, 'index'])->name('price-product-setting-store.index');
    Route::get('/price-product-setting-store/create', [PriceProductSettingStoreController::class, 'create'])->name('price-product-setting-store.create');
    Route::post('/price-product-setting-store', [PriceProductSettingStoreController::class, 'store'])->name('price-product-setting-store.store');
    Route::get('/price-product-setting-store/{id}/edit', [PriceProductSettingStoreController::class, 'edit'])->name('price-product-setting-store.edit');
    Route::put('/price-product-setting-store/{id}', [PriceProductSettingStoreController::class, 'update'])->name('price-product-setting-store.update');
    Route::delete('/price-product-setting-store/{id}', [PriceProductSettingStoreController::class, 'destroy'])->name('price-product-setting-store.destroy');

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

    Route::get('/couriers', [CourierController::class, 'index'])->name('couriers.index');
    Route::get('/couriers/create', [CourierController::class, 'create'])->name('couriers.create');
    Route::post('/couriers', [CourierController::class, 'store'])->name('couriers.store');
    Route::get('/couriers/{id}/edit', [CourierController::class, 'edit'])->name('couriers.edit');
    Route::put('/couriers/{id}', [CourierController::class, 'update'])->name('couriers.update');
    Route::delete('/couriers/{id}', [CourierController::class, 'destroy'])->name('couriers.destroy');

    Route::get('/shipping-addresses', [ShippingAddressController::class, 'index'])->name('shipping-addresses.index');
    Route::get('/shipping-addresses/create', [ShippingAddressController::class, 'create'])->name('shipping-addresses.create');
    Route::post('/shipping-addresses', [ShippingAddressController::class, 'store'])->name('shipping-addresses.store');
    Route::post('/shipping-addresses/save-inline', [ShippingAddressController::class, 'saveInline'])->name('shipping-addresses.save-inline');
    Route::get('/shipping-addresses/{id}/edit', [ShippingAddressController::class, 'edit'])->name('shipping-addresses.edit');
    Route::put('/shipping-addresses/{id}', [ShippingAddressController::class, 'update'])->name('shipping-addresses.update');
    Route::delete('/shipping-addresses/{id}', [ShippingAddressController::class, 'destroy'])->name('shipping-addresses.destroy');

    Route::get('/payment-methods', [PaymentMethodController::class, 'index'])->name('payment-methods.index');
    Route::get('/payment-methods/create', [PaymentMethodController::class, 'create'])->name('payment-methods.create');
    Route::post('/payment-methods', [PaymentMethodController::class, 'store'])->name('payment-methods.store');
    Route::get('/payment-methods/{id}/edit', [PaymentMethodController::class, 'edit'])->name('payment-methods.edit');
    Route::put('/payment-methods/{id}', [PaymentMethodController::class, 'update'])->name('payment-methods.update');
    Route::delete('/payment-methods/{id}', [PaymentMethodController::class, 'destroy'])->name('payment-methods.destroy');

    Route::prefix('content')->name('content.')->group(function () {
        Route::get('/faq', [FaqController::class, 'index'])->name('faq.index');
        Route::get('/faq/create', [FaqController::class, 'create'])->name('faq.create');
        Route::post('/faq', [FaqController::class, 'store'])->name('faq.store');
        Route::get('/faq/{id}/edit', [FaqController::class, 'edit'])->name('faq.edit');
        Route::put('/faq/{id}', [FaqController::class, 'update'])->name('faq.update');
        Route::delete('/faq/{id}', [FaqController::class, 'destroy'])->name('faq.destroy');

        Route::get('/blog', [BlogPostController::class, 'index'])->name('blog.index');
        Route::get('/blog/create', [BlogPostController::class, 'create'])->name('blog.create');
        Route::post('/blog', [BlogPostController::class, 'store'])->name('blog.store');
        Route::get('/blog/{id}/edit', [BlogPostController::class, 'edit'])->name('blog.edit');
        Route::put('/blog/{id}', [BlogPostController::class, 'update'])->name('blog.update');
        Route::delete('/blog/{id}', [BlogPostController::class, 'destroy'])->name('blog.destroy');

        Route::get('/about-us', [AboutUsController::class, 'index'])->name('about.index');
        Route::get('/about-us/create', [AboutUsController::class, 'create'])->name('about.create');
        Route::post('/about-us', [AboutUsController::class, 'store'])->name('about.store');
        Route::get('/about-us/{id}/edit', [AboutUsController::class, 'edit'])->name('about.edit');
        Route::put('/about-us/{id}', [AboutUsController::class, 'update'])->name('about.update');
        Route::delete('/about-us/{id}', [AboutUsController::class, 'destroy'])->name('about.destroy');

        Route::get('/how-to-return', [HowToReturnController::class, 'index'])->name('how-to-return.index');
        Route::get('/how-to-return/create', [HowToReturnController::class, 'create'])->name('how-to-return.create');
        Route::post('/how-to-return', [HowToReturnController::class, 'store'])->name('how-to-return.store');
        Route::get('/how-to-return/{id}/edit', [HowToReturnController::class, 'edit'])->name('how-to-return.edit');
        Route::put('/how-to-return/{id}', [HowToReturnController::class, 'update'])->name('how-to-return.update');
        Route::delete('/how-to-return/{id}', [HowToReturnController::class, 'destroy'])->name('how-to-return.destroy');

        Route::get('/terms', [TermsAndConditionController::class, 'index'])->name('terms.index');
        Route::get('/terms/create', [TermsAndConditionController::class, 'create'])->name('terms.create');
        Route::post('/terms', [TermsAndConditionController::class, 'store'])->name('terms.store');
        Route::get('/terms/{id}/edit', [TermsAndConditionController::class, 'edit'])->name('terms.edit');
        Route::put('/terms/{id}', [TermsAndConditionController::class, 'update'])->name('terms.update');
        Route::delete('/terms/{id}', [TermsAndConditionController::class, 'destroy'])->name('terms.destroy');

        Route::get('/privacy', [PrivacyPolicyController::class, 'index'])->name('privacy.index');
        Route::get('/privacy/create', [PrivacyPolicyController::class, 'create'])->name('privacy.create');
        Route::post('/privacy', [PrivacyPolicyController::class, 'store'])->name('privacy.store');
        Route::get('/privacy/{id}/edit', [PrivacyPolicyController::class, 'edit'])->name('privacy.edit');
        Route::put('/privacy/{id}', [PrivacyPolicyController::class, 'update'])->name('privacy.update');
        Route::delete('/privacy/{id}', [PrivacyPolicyController::class, 'destroy'])->name('privacy.destroy');

        Route::get('/warranty', [WarrantyClaimController::class, 'index'])->name('warranty.index');
        Route::get('/warranty/create', [WarrantyClaimController::class, 'create'])->name('warranty.create');
        Route::post('/warranty', [WarrantyClaimController::class, 'store'])->name('warranty.store');
        Route::get('/warranty/{id}/edit', [WarrantyClaimController::class, 'edit'])->name('warranty.edit');
        Route::put('/warranty/{id}', [WarrantyClaimController::class, 'update'])->name('warranty.update');
        Route::delete('/warranty/{id}', [WarrantyClaimController::class, 'destroy'])->name('warranty.destroy');
    });
});

