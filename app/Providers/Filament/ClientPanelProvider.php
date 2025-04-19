<?php

namespace App\Providers\Filament;

use App\Filament\AvatarProviders\GravatarProvider;
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
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class ClientPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        $generalSettings = app(GeneralSettings::class);

        try {
            $font = $generalSettings->branding_primary_font;
            $brandLogoBlack = ! empty($generalSettings->branding_logo_black)
                ? Storage::url($generalSettings->branding_logo_black)
                : asset('img/logo/logo-black.svg');
            $brandLogoWhite = ! empty($generalSettings->branding_logo_white)
                ? Storage::url($generalSettings->branding_logo_white)
                : asset('img/logo/logo-white.svg');
        } catch (QueryException $e) {
            $font = 'Lexend';
            $brandLogoBlack = asset('img/logo/logo-black.svg');
            $brandLogoWhite = asset('img/logo/logo-white.svg');
        }

        return $panel
            ->id('client')
            ->path('client')
            ->login()
            ->passwordReset()
            ->font($font, provider: GoogleFontProvider::class)
            ->viteTheme('resources/css/filament/app/theme.css')
            ->darkMode(false)
            ->brandLogo(fn () => Auth::guard('client')->guest()
                ? $brandLogoBlack
                : $brandLogoWhite)
            ->brandLogoHeight('2rem')
            ->favicon(asset('favicon.png'))
            ->defaultAvatarProvider(GravatarProvider::class)
            ->discoverResources(in: app_path('Filament/Client/Resources'), for: 'App\\Filament\\Client\\Resources')
            ->discoverPages(in: app_path('Filament/Client/Pages'), for: 'App\\Filament\\Client\\Pages')
            ->pages([
                Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Client/Widgets'), for: 'App\\Filament\\Client\\Widgets')
            ->widgets([
                Widgets\AccountWidget::class,
                Widgets\FilamentInfoWidget::class,
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
            ->authGuard('client')
            ->authPasswordBroker('clients');
    }
}
