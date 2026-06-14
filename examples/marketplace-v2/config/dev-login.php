<?php

return [
  'enabled' => (bool) env('DEV_LOGIN_PREFILL', env('APP_ENV') === 'local'),

  'email' => env('DEV_LOGIN_EMAIL', 'admin@marketplace.local'),

  'password' => env('DEV_LOGIN_PASSWORD', 'password'),

  'accounts' => [
    ['label' => 'Admin', 'email' => 'admin@marketplace.local'],
    ['label' => 'Customer', 'email' => 'customer@marketplace.local'],
    ['label' => 'Vendor', 'email' => 'kindly-crafts@marketplace.local'],
  ],
];
