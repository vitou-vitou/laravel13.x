<?php

return [
    /*
    | mock — Track A: operator_plan column + radio form (CI / local default)
    | stripe — Track B: Cashier subscription gates Pro creator limit
    */
    'mode' => env('OPERATOR_BILLING_MODE', 'mock'),

    'stripe_prices' => [
        'pro' => env('STRIPE_PRICE_PRO'),
    ],

    'default_plan' => 'starter',

    'plans' => [
        'starter' => [
            'label' => 'Starter',
            'creator_limit' => 3,
            'price_monthly' => 0,
            'description' => 'Up to 3 creators — pilot / solo operator.',
        ],
        'pro' => [
            'label' => 'Pro',
            'creator_limit' => 25,
            'price_monthly' => 49,
            'description' => 'Up to 25 creators — small agency roster.',
        ],
        'demo' => [
            'label' => 'Demo',
            'creator_limit' => 500,
            'price_monthly' => 0,
            'description' => 'Khmer travel demo roster — up to 500 creators (local seed only).',
        ],
    ],
];
