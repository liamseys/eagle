<?php

namespace App\Http\Middleware;

use App\Settings\AdvancedSettings;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetHelpCenterIndexingHeaders
{
    /**
     * Ask search engines not to index the public Help Center when
     * indexing has been disabled in the advanced settings.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if (! app(AdvancedSettings::class)->hc_search_engine_indexing) {
            $response->headers->set('X-Robots-Tag', 'noindex');
        }

        return $response;
    }
}
