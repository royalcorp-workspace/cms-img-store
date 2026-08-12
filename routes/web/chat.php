<?php

use App\Http\Controllers\ChatController;
use Illuminate\Support\Facades\Route;

Route::prefix('chat')->name('chat.')->group(function () {
    Route::get('/', [ChatController::class, 'index'])->name('index');
    Route::get('/conversations', [ChatController::class, 'fetchConversations'])->name('conversations');
    Route::get('/{id}/messages', [ChatController::class, 'fetchMessages'])->name('messages');
    Route::post('/{id}/messages', [ChatController::class, 'sendMessage'])->name('send');
});
