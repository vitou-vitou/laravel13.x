<?php

return [
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
    ],
];
