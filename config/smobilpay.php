<?php

return [
    "scraping" => [
        "username" => env("SMOBILPAY_SCRAPING_USERNAME"),
        "password" => env("SMOBILPAY_SCRAPING_PASSWORD"),
        "baseUrl" => env("SMOBILPAY_SCRAPING_BASE_URL")
    ],
    'api' => [
        'url' => env('SMOBILPAY_URL'),
        'token' => env('SMOBILPAY_TOKEN'),
        'secret' => env('SMOBILPAY_SECRET'),
        'version' => env('SMOBILPAY_VERSION', '3.0.0'),
        'payment_email' => env('SMOBILPAY_PAYMENT_EMAIL', '3.0.0')
    ],
];