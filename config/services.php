<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
        'scheme' => 'https',
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    /*
    |--------------------------------------------------------------------------
    | AI Provider Selection
    |--------------------------------------------------------------------------
    | Set AI_PROVIDER in your .env to switch between AI backends:
    |   AI_PROVIDER=deepseek  (default) — uses DEEPSEEK_API_KEY
    |   AI_PROVIDER=gemini              — uses GEMINI_API_KEY
    |
    | Get DeepSeek keys : https://openrouter.ai/  (or https://platform.deepseek.com)
    | Get Gemini keys   : https://aistudio.google.com/app/apikey
    */

    'deepseek' => [
        'api_key'  => env('DEEPSEEK_API_KEY'),
        'model'    => env('DEEPSEEK_MODEL', 'deepseek/deepseek-chat'),
        'base_url' => env('DEEPSEEK_BASE_URL', 'https://openrouter.ai/api/v1'),
    ],

    'gemini' => [
        'api_key' => env('GEMINI_API_KEY'),
        'model'   => env('GEMINI_MODEL', 'gemini-2.0-flash'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Google OAuth (via Laravel Socialite)
    |--------------------------------------------------------------------------
    | GOOGLE_CLIENT_ID     — OAuth 2.0 Client ID from Google Cloud Console
    | GOOGLE_CLIENT_SECRET — OAuth 2.0 Client Secret
    | GOOGLE_REDIRECT_URI  — Must match an authorized redirect URI in GCC
    */
    'google' => [
        'client_id'     => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect'      => env('GOOGLE_REDIRECT_URI', '/auth/google/callback'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Application Auth Settings
    |--------------------------------------------------------------------------
    | DEVELOPER_MODE        — true = allow any email; false = restrict to domain
    | ALLOWED_EMAIL_DOMAIN  — domain suffix enforced in production (e.g. deped.gov.ph)
    */
    'auth' => [
        'developer_mode'       => env('DEVELOPER_MODE', true),
        'allowed_email_domain' => env('ALLOWED_EMAIL_DOMAIN', 'deped.gov.ph'),
    ],

];
