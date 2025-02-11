<?php

namespace App\Providers;

use App\Models\HelpCenter\Article;
use App\Models\HelpCenter\Category;
use App\Models\HelpCenter\Form;
use App\Policies\ArticlePolicy;
use App\Policies\CategoryPolicy;
use App\Policies\FormPolicy;
use App\Settings\GeneralSettings;
use Filament\Support\Colors\Color;
use Filament\Support\Facades\FilamentColor;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\QueryException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
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
        $this->configureCommands();
        $this->configureModels();
        $this->configureMacros();
        $this->configureFilamentColor($generalSettings);
        $this->configureGatePolicies();
    }

    /**
     * Configure the application's commands.
     */
    private function configureCommands(): void
    {
        DB::prohibitDestructiveCommands(
            $this->app->isProduction()
        );
    }

    /**
     * Configure the application's models.
     */
    private function configureModels(): void
    {
        Model::shouldBeStrict();
        Model::preventAccessingMissingAttributes();
        Model::preventLazyLoading();

        if ($this->app->isProduction()) {
            Model::handleLazyLoadingViolationUsing(function ($model, $relation) {
                $class = get_class($model);
                info("Attempted to lazy load [{$relation}] on model [{$class}].");
            });
        }
    }

    /**
     * Configure the application's macros.
     */
    private function configureMacros(): void
    {
        Carbon::macro('inApplicationTimezone', function () {
            return $this->tz(config('app.timezone_display'));
        });

        Carbon::macro('inUserTimezone', function () {
            return $this->tz(auth()->user()?->timezone ?? config('app.timezone_display'));
        });
    }

    /**
     * Configure the Filament color settings.
     */
    private function configureFilamentColor(GeneralSettings $generalSettings): void
    {
        try {
            $color = Color::hex($generalSettings->branding_primary_color);
        } catch (QueryException $e) {
            $color = Color::hex('#000000');
        }

        FilamentColor::register(['primary' => $color]);
    }

    /**
     * Configure the application's gate policies.
     */
    private function configureGatePolicies(): void
    {
        Gate::policy(Article::class, ArticlePolicy::class);
        Gate::policy(Category::class, CategoryPolicy::class);
        Gate::policy(Form::class, FormPolicy::class);
    }
}
