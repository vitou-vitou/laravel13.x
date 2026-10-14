<?php

return [
    'password' => env('DASHBOARD_PASSWORD'),
    'initial_password' => env('INITIAL_PASSWORD', '123456'),
    'require_login' => env('REQUIRE_LOGIN', true),
    'auth_mode' => env('AUTH_MODE', 'password'),
    'reset_hint' => 'Forgot password? Reset via CLI -> Settings -> Reset Password to Default.',
];
