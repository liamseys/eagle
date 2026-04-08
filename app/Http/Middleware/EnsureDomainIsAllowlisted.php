<?php

namespace App\Http\Middleware;

use App\Settings\GeneralSettings;
use Closure;
use Filament\Facades\Filament;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;

class EnsureDomainIsAllowlisted
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return $next($request);
        }

        $generalSettings = app(GeneralSettings::class);

        if (empty($generalSettings->allowlisted_domains)) {
            return $next($request);
        }

        $domain = substr(strrchr($user->email, '@'), 1);

        $isAllowlisted = collect($generalSettings->allowlisted_domains)
            ->contains('domain', $domain);

        if (! $isAllowlisted) {
            Filament::auth()->logout();

            throw ValidationException::withMessages([
                'data.email' => __('This domain is not allowlisted to access Eagle. Please contact your administrator.'),
            ]);
        }

        return $next($request);
    }
}
