<?php

return [

    'auth_date_max_age' => env('TELEGRAM_AUTH_DATE_MAX_AGE', 300),

    'auth_code_ttl' => env('TELEGRAM_AUTH_CODE_TTL', 600),

    'auth_session_ttl' => env('TELEGRAM_AUTH_SESSION_TTL', 900),

    'access_token_ttl' => env('TELEGRAM_ACCESS_TOKEN_TTL', 3600),

    'refresh_token_ttl' => env('TELEGRAM_REFRESH_TOKEN_TTL', 2592000),

    'oidc' => [
        'authorization_url' => env('TELEGRAM_OIDC_AUTH_URL', 'https://oauth.telegram.org/auth'),
        'token_url' => env('TELEGRAM_OIDC_TOKEN_URL', 'https://oauth.telegram.org/token'),
        'userinfo_url' => env('TELEGRAM_OIDC_USERINFO_URL', 'https://oauth.telegram.org/userinfo'),
        'jwks_url' => env('TELEGRAM_OIDC_JWKS_URL', 'https://oauth.telegram.org/.well-known/jwks.json'),
    ],

    'rate_limits' => [
        'auth_start' => env('TELEGRAM_RATE_LIMIT_AUTH_START', 30),
        'auth_callback' => env('TELEGRAM_RATE_LIMIT_AUTH_CALLBACK', 60),
        'token' => env('TELEGRAM_RATE_LIMIT_TOKEN', 30),
    ],

];
