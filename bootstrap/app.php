<?php

use App\Http\Middleware\SetDefaultLocaleForUrls;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Support\Facades\RateLimiter;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
        then: function () {
            RateLimiter::for('chatbot', function (Request $request) {
                return Limit::perMinute(20)->by($request->ip());
            });
        },
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->prependToPriorityList(
            before: SubstituteBindings::class,
            prepend: SetDefaultLocaleForUrls::class,
        );
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
