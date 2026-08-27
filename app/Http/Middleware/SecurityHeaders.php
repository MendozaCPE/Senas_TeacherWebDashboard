<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

/**
 * SecurityHeaders Middleware
 *
 * Adds essential HTTP security headers to every response.
 * These headers protect against:
 *  - Clickjacking (X-Frame-Options)
 *  - MIME-type sniffing (X-Content-Type-Options)
 *  - Referrer leakage (Referrer-Policy)
 *  - XSS attacks (X-XSS-Protection)
 *  - Unnecessary browser feature access (Permissions-Policy)
 */
class SecurityHeaders
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('X-XSS-Protection', '1; mode=block');
        $response->headers->set('Permissions-Policy', 'geolocation=(), microphone=(), camera=()');

        return $response;
    }
}
