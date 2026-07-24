<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * SanitizeInput — strips dangerous characters from every incoming input value
 * before the request reaches any controller. Works as a global protection
 * layer on top of Laravel's built-in Eloquent parameterized queries.
 *
 * What it does:
 *  • Trims whitespace
 *  • Strips HTML/XML tags (prevents XSS and tag-injection)
 *  • Removes SQL comment tokens (-- /* *\/ #)
 *  • Keeps legitimate alphanumeric/punctuation intact
 *
 * Note: Eloquent/PDO already parameterizes all DB queries, so this is
 * a defence-in-depth layer, not the primary SQLi shield.
 */
class SanitizeInput
{
    /**
     * Keys that should NEVER be sanitized (binary data, JSON payloads, etc.)
     */
    private const SKIP_KEYS = [
        '_token', 'password', 'password_confirmation',
        'contents', 'quiz', 'answers', 'slides',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $input = $request->except(self::SKIP_KEYS);
        $clean = $this->sanitizeArray($input);
        $request->merge($clean);

        return $next($request);
    }

    private function sanitizeArray(array $data): array
    {
        $out = [];
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $out[$key] = $this->sanitizeArray($value);
            } elseif (is_string($value)) {
                $out[$key] = $this->sanitizeString($value);
            } else {
                $out[$key] = $value;
            }
        }
        return $out;
    }

    private function sanitizeString(string $value): string
    {
        // 1. Strip HTML/XML tags
        $value = strip_tags($value);

        // 2. Remove SQL comment sequences
        $value = preg_replace('/(-{2,}|\/\*|\*\/|#)/u', '', $value);

        // 3. Trim surrounding whitespace
        return trim($value);
    }
}
