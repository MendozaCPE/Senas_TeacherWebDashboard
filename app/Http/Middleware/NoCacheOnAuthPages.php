<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class NoCacheOnAuthPages
{
    /**
     * Handle an incoming request.
     * Sets HTTP headers that prevent browsers from caching authenticated pages.
     * This ensures that after logout, pressing the browser Back button causes
     * the browser to re-fetch the page from the server (which will then
     * redirect to login) rather than showing a stale cached version.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        return $response->withHeaders([
            'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0, post-check=0, pre-check=0',
            'Pragma'        => 'no-cache',
            'Expires'       => 'Sat, 01 Jan 2000 00:00:00 GMT',
        ]);
    }
}
