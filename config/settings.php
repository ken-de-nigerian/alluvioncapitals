<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Registration Settings
    |--------------------------------------------------------------------------
    |
    | This option controls whether a new user registration is enabled.
    | When disabled, the registration routes will be unavailable.
    |
    */
    'register' => [
        'enabled' => env('REGISTRATION_ENABLED', true),
    ],
    'login' => [
        'social_enabled' => env('SOCIAL_LOGIN_ENABLED', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Email Settings
    |--------------------------------------------------------------------------
    |
    | Configuration for email-related features in the admin panel.
    |
    */
    'email_notification' => true,    // Enable/disable all email notifications
    'email_verification' => true,    // Require email verification for all accounts
    'email_provider' => 'phpmailer',      // Default mailer service to use for all emails

    /*
    |--------------------------------------------------------------------------
    | Donation Settings
    |--------------------------------------------------------------------------
    |
    | Configuration for donation processing and limits.
    | All monetary values are in the base currency unit.
    |
    */
    'donation' => [
        'min_amount' => 10.00,         // Minimum allowed donation amount
        'max_amount' => 1000000000.00,  // Maximum allowed donation amount
        'fixed_fee' => 50.00           // Fixed fee charged per donation
    ],

    /*
    |--------------------------------------------------------------------------
    | Currency Settings
    |--------------------------------------------------------------------------
    |
    | Default currency configuration for financial transactions.
    |
    */
    'currency' => [
        'code' => 'USD',               // ISO currency code
        'symbol' => '$',               // Currency symbol
        'precision' => 2               // Decimal places to display
    ]
];
