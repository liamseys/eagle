<?php

namespace App\Providers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Carbon::macro('inApplicationTimezone', function () {
            return $this->tz(config('app.timezone_display'));
        });

        Carbon::macro('inUserTimezone', function () {
            return $this->tz(auth()->user()?->timezone ?? config('app.timezone_display'));
        });

        Model::preventLazyLoading();

        // But in production, log the violation instead of throwing an exception.
        if ($this->app->isProduction()) {
            Model::handleLazyLoadingViolationUsing(function ($model, $relation) {
                $class = get_class($model);

                info("Attempted to lazy load [{$relation}] on model [{$class}].");
            });
        }

        Model::preventAccessingMissingAttributes();
    }
}
