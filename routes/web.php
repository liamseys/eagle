<?php

use App\Filament\Pages\Auth\Welcome;
use App\Http\Controllers\ArticleController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\FormController;
use App\Http\Controllers\IndexController;
use App\Http\Middleware\SetDefaultLocaleForUrls;
use Illuminate\Support\Facades\Route;
use Spatie\Honeypot\ProtectAgainstSpam;
use Spatie\WelcomeNotification\WelcomesNewUsers;

Route::get('/', function () {
    return redirect()->route('index');
})->middleware(SetDefaultLocaleForUrls::class);

Route::group([
    'prefix' => 'hc/{locale}',
    'where' => ['locale' => '[a-zA-Z]{2}'],
    'middleware' => SetDefaultLocaleForUrls::class,
], function () {
    Route::get('/', IndexController::class)->name('index');
    Route::resource('categories', CategoryController::class)->only('show');
    Route::resource('articles', ArticleController::class)->only('show');
    Route::post('forms/submit', [FormController::class, 'submit'])
        ->middleware(ProtectAgainstSpam::class)
        ->name('forms.submit');
    Route::resource('forms', FormController::class)->only('show');
});

Route::group(['middleware' => ['web', WelcomesNewUsers::class]], function () {
    Route::get('eagle/welcome/{user}', Welcome::class)->name('welcome');
});
