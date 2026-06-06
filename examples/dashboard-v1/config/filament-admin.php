<?php

return [
    'dev_login' => [
        'enabled' => (bool) env('FILAMENT_DEV_LOGIN_PREFILL', env('APP_ENV') === 'local'),
        'email' => env('FILAMENT_DEV_LOGIN_EMAIL', 'test@example.com'),
        'password' => env('FILAMENT_DEV_LOGIN_PASSWORD', 'password'),
    ],
];
