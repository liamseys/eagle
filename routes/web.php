<?php

use App\Filament\Pages\Auth\Welcome;
use App\Http\Controllers\ArticleController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\FormController;
use App\Http\Controllers\IndexController;
use App\Http\Middleware\SetDefaultLocaleForUrls;
use Illuminate\Support\Facades\Route;
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
    Route::get('articles/{article}/publish', [ArticleController::class, 'publish'])
        ->name('articles.publish');
    Route::get('articles/{article}/unpublish', [ArticleController::class, 'unpublish'])
        ->name('articles.unpublish');
    Route::post('forms/submit', [FormController::class, 'submit'])
        ->name('forms.submit');
    Route::resource('forms', FormController::class)->only('show');
    Route::get('forms/{form}/activate', [FormController::class, 'activate'])
        ->name('forms.activate');
    Route::get('forms/{form}/deactivate', [FormController::class, 'deactivate'])
        ->name('forms.deactivate');
    Route::get('forms/{form}/embed', [FormController::class, 'embed'])
        ->name('forms.embed');
});

Route::group(['middleware' => ['web', WelcomesNewUsers::class]], function () {
    Route::get('eagle/welcome/{user}', Welcome::class)->name('welcome');
});
