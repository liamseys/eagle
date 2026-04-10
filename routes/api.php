<?php

use App\Http\Controllers\Api\ChatbotController;
use App\Http\Controllers\Api\TicketController;
use Illuminate\Support\Facades\Route;

Route::apiResources([
    'tickets' => TicketController::class,
]);

Route::get('chatbot/config', [ChatbotController::class, 'config']);
Route::post('chatbot/message', [ChatbotController::class, 'message'])
    ->middleware('throttle:chatbot');
