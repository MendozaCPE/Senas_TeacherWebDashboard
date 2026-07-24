<?php

namespace App\Support;

class UrlObfuscator
{
    /**
     * Encodes an integer ID into an obfuscated, URL-safe string.
     */
    public static function encode(mixed $id): string
    {
        if (empty($id)) {
            return '';
        }

        if (!is_numeric($id) && str_contains((string)$id, ':')) {
            return (string)$id;
        }

        $numericId = (int)$id;
        $key = config('app.key', 'senas-secret-key-2026');
        $hash = substr(hash_hmac('sha256', (string)$numericId, $key), 0, 6);
        $raw = $numericId . ':' . $hash;

        return rtrim(strtr(base64_encode($raw), '+/', '-_'), '=');
    }

    /**
     * Decodes an obfuscated string back to an integer ID.
     * Returns null if tampering is detected.
     */
    public static function decode(mixed $value): ?int
    {
        if (empty($value)) {
            return null;
        }

        if (is_numeric($value)) {
            return (int)$value;
        }

        $padded = strtr((string)$value, '-_', '+/');
        $modulo = strlen($padded) % 4;
        if ($modulo) {
            $padded .= str_repeat('=', 4 - $modulo);
        }

        $decoded = base64_decode($padded, true);
        if (!$decoded || !str_contains($decoded, ':')) {
            return null;
        }

        [$id, $hash] = explode(':', $decoded, 2);
        if (!is_numeric($id)) {
            return null;
        }

        $key = config('app.key', 'senas-secret-key-2026');
        $expectedHash = substr(hash_hmac('sha256', (string)$id, $key), 0, 6);

        if (hash_equals($expectedHash, $hash)) {
            return (int)$id;
        }

        return null;
    }
}
