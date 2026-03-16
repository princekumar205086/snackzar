<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Canonical Domain Middleware
 * Enforces HTTPS and preferred domain (snackzar.com without www)
 * Implements Phase 1: Canonical Domain enforcement
 */
class CanonicalDomain
{
    public function handle(Request $request, Closure $next): Response
    {
        $domain = config('app.domain', 'snackzar.com');
        $scheme = config('app.scheme', 'https');

        $host = $request->getHost();
        $path = $request->getRequestUri();

        // Check if redirect is needed
        $needsRedirect = false;
        $redirectUrl = null;

        // Force HTTPS
        if (!$request->isSecure() && config('app.env') !== 'local') {
            $needsRedirect = true;
            $redirectUrl = "{$scheme}://{$domain}{$path}";
        }
        // Redirect www to non-www
        elseif ($host === "www.{$domain}") {
            $needsRedirect = true;
            $redirectUrl = "{$scheme}://{$domain}{$path}";
        }
        // Redirect other domains to canonical domain
        elseif ($host !== $domain && config('app.env') === 'production') {
            $needsRedirect = true;
            $redirectUrl = "{$scheme}://{$domain}{$path}";
        }

        if ($needsRedirect && $redirectUrl) {
            return redirect($redirectUrl, 301);
        }

        return $next($request);
    }
}
