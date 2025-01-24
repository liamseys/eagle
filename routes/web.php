<?php

use App\Http\Controllers\IndexController;
use App\Http\Middleware\SetDefaultLocaleForUrls;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('index');
})->middleware(SetDefaultLocaleForUrls::class);

Route::group([
    'prefix' => 'hc/{locale}',
    'where' => ['locale' => '[a-zA-Z]{2}'],
    'middleware' => SetDefaultLocaleForUrls::class,
], function () {
    Route::get('/', IndexController::class)->name('index');
});
