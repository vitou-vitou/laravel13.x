<?php

$appUrl = env('APP_URL', 'http://dashboard-v1.test');
$defaultHost = parse_url($appUrl, PHP_URL_HOST) ?: 'dashboard-v1.test';

return [
    'enabled' => (bool) env('TUNNEL_ADMIN_ENABLED', env('APP_ENV') === 'local'),

    'default_herd_host' => $defaultHost,

    'ngrok_api_url' => env('NGROK_API_URL', 'http://127.0.0.1:4040'),

    'traffic_policy_path' => base_path('ngrok-traffic-policy.yml'),

    'oauth_callbacks' => [
        'GOOGLE_REDIRECT_URI' => '/auth/google/callback',
        'MICROSOFT_REDIRECT_URI' => '/auth/microsoft/callback',
        'GITHUB_REDIRECT_URI' => '/auth/github/callback',
    ],
];
