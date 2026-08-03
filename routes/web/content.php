<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Content\FaqController;
use App\Http\Controllers\Content\BlogPostController;
use App\Http\Controllers\Content\AboutUsController;
use App\Http\Controllers\Content\HowToReturnController;
use App\Http\Controllers\Content\TermsAndConditionController;
use App\Http\Controllers\Content\PrivacyPolicyController;
use App\Http\Controllers\Content\WarrantyClaimController;

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
