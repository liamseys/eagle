<?php

namespace App\Providers\Filament;

use App\Filament\AvatarProviders\GravatarProvider;
use App\Filament\Pages\Auth\EditProfile;
use App\Filament\Widgets\StatsOverview;
use App\Filament\Widgets\TicketPriorityChart;
use App\Filament\Widgets\TicketTypeChart;
use App\Http\Middleware\EnsureUserIsActive;
use App\Settings\GeneralSettings;
use Filament\FontProviders\GoogleFontProvider;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\View\PanelsRenderHook;
use Illuminate\Contracts\View\View;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AppPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        $generalSettings = app(GeneralSettings::class);

        try {
            $path = $generalSettings->app_path;
            $font = $generalSettings->branding_primary_font;
        } catch (QueryException $e) {
            $path = 'eagle';
            $font = 'Lexend';
        }

        return $panel
            ->default()
            ->id('app')
            ->path($path)
            ->login()
            ->passwordReset()
            ->emailVerification()
            ->profile(EditProfile::class)
            ->font($font, provider: GoogleFontProvider::class)
            ->viteTheme('resources/css/filament/app/theme.css')
            ->darkMode(false)
            ->favicon(asset('favicon.png'))
            ->brandLogo(fn () => Auth::guest()
                ? asset('img/logo/logo-black.svg')
                : asset('img/logo/logo-white.svg'))
            ->brandLogoHeight('2rem')
            ->defaultAvatarProvider(GravatarProvider::class)
            ->discoverResources(
                in: app_path('Filament/Resources'),
                for: 'App\\Filament\\Resources',
            )
            ->discoverPages(
                in: app_path('Filament/Pages'),
                for: 'App\\Filament\\Pages',
            )
            ->discoverClusters(
                in: app_path('Filament/Clusters'),
                for: 'App\\Filament\\Clusters',
            )
            ->pages([
                Pages\Dashboard::class,
            ])
            ->discoverWidgets(
                in: app_path('Filament/Widgets'),
                for: 'App\\Filament\\Widgets',
            )
            ->widgets([
                StatsOverview::class,
                TicketPriorityChart::class,
                TicketTypeChart::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
                EnsureUserIsActive::class,
            ])
            ->databaseNotifications()
            ->renderHook(
                PanelsRenderHook::SIDEBAR_FOOTER,
                fn (): View => view('filament.disclaimer'),
            );
    }
}
