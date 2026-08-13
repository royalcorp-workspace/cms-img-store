<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CRM\LeadController;

Route::prefix('admin/crm')->name('admin.crm.')->group(function () {
    Route::get('leads', [LeadController::class, 'index'])->name('leads.index');
    Route::patch('leads/{lead}/stage', [LeadController::class, 'updateStage'])->name('leads.updateStage');
    
    Route::resource('email-templates', \App\Http\Controllers\CRM\EmailTemplateController::class)->except(['show']);
    
    Route::post('campaigns/{campaign}/blast', [\App\Http\Controllers\CRM\CampaignController::class, 'blast'])->name('campaigns.blast');
    Route::resource('campaigns', \App\Http\Controllers\CRM\CampaignController::class);
});
