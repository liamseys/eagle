<?php

use App\Http\Controllers\Api\TicketController;
use Illuminate\Support\Facades\Route;

Route::apiResources([
    'tickets' => TicketController::class,
]);
