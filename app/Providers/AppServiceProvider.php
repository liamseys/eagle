<?php

namespace App\Providers;

use App\Settings\GeneralSettings;
use Filament\Support\Colors\Color;
use Filament\Support\Facades\FilamentColor;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\QueryException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Gate;
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
    public function boot(GeneralSettings $generalSettings): void
    {
        try {
            $color = Color::hex($generalSettings->branding_primary_color);
        } catch (QueryException $e) {
            $color = Color::hex('#000000');
        }

        FilamentColor::register(['primary' => $color]);

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

        // Gate::policy(Article::class, ArticlePolicy::class);
        // Gate::policy(Category::class, CategoryPolicy::class);
        // Gate::policy(Form::class, FormPolicy::class);
    }
}
