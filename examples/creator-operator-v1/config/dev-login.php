<?php

return [
    'enabled' => (bool) env('DEV_LOGIN_PREFILL', env('APP_ENV') === 'local'),

    'email' => env('DEV_LOGIN_EMAIL', 'operator@creator-operator.local'),

    'password' => env('DEV_LOGIN_PASSWORD', 'password'),

    'accounts' => [
        ['label' => 'Operator', 'email' => 'operator@creator-operator.local'],
        ['label' => 'Creator', 'email' => 'creator@creator-operator.local'],
    ],
];
