<?php

namespace App\Http\Middleware;

use Illuminate\Http\Middleware\TrustProxies as Middleware;
use Illuminate\Http\Request;

class TrustProxies extends Middleware
{
    /**
     * The trusted proxies for this application.
     *
     * '*' trusts all proxies — safe for shared/managed hosting where the
     * real client IP is forwarded via X-Forwarded-For. This also ensures
     * that X-Forwarded-Proto: https is respected so Laravel knows the
     * request is HTTPS and issues secure session cookies correctly.
     *
     * If you control your infrastructure, replace '*' with the specific
     * proxy IP range (e.g. '10.0.0.0/8').
     *
     * @var array<int, string>|string|null
     */
    protected $proxies = '*';

    /**
     * The headers that should be used to detect proxies.
     *
     * @var int
     */
    protected $headers =
        Request::HEADER_X_FORWARDED_FOR |
        Request::HEADER_X_FORWARDED_HOST |
        Request::HEADER_X_FORWARDED_PORT |
        Request::HEADER_X_FORWARDED_PROTO |
        Request::HEADER_X_FORWARDED_AWS_ELB;
}
