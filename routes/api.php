<?php

use App\Http\Controllers\ChatController;
use App\Http\Controllers\FaqController;
use Illuminate\Support\Facades\Route;

Route::post('/chat', [ChatController::class, 'sendMessage']);
Route::get('/chat/{sessionId}/history', [ChatController::class, 'getHistory']);

Route::prefix('campsites/{campsite}')->group(function () {
    Route::get('/faqs', [FaqController::class, 'index']);
    Route::post('/faqs', [FaqController::class, 'store']);
    Route::put('/faqs/{faq}', [FaqController::class, 'update']);
    Route::delete('/faqs/{faq}', [FaqController::class, 'destroy']);
});