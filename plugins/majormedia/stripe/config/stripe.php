<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Stripe API Secret Key
    |--------------------------------------------------------------------------
    |
    | This is your Stripe secret key, used to authenticate requests
    | from your backend to Stripe's API.
    |
    */

    'secret' => env('STRIPE_API_SECRET'),

    /*
    |--------------------------------------------------------------------------
    | Stripe Webhook Secret
    |--------------------------------------------------------------------------
    |
    | This secret is used to verify that incoming webhooks are genuinely
    | from Stripe. Set this in your .env file.
    |
    */

    'webhook_secret' => env('STRIPE_WEBHOOK_SECRET'),

    /*
    |--------------------------------------------------------------------------
    | Success and Cancel URLs
    |--------------------------------------------------------------------------
    |
    | These URLs are used after checkout or payment intent confirmation.
    |
    */

    'success_url' => env('STRIPE_SUCCESS_URL', 'https://google.com'),
    'cancel_url' => env('STRIPE_CANCEL_URL', 'https://google.com/cancel'),

    /*
    |--------------------------------------------------------------------------
    | Return URL After Payment
    |--------------------------------------------------------------------------
    |
    | This is used for redirection after a successful payment intent flow.
    |
    */

    'return_url' => env('STRIPE_RETURN_SUCCESS_URL', ''),

    /*
    |--------------------------------------------------------------------------
    | Currency
    |--------------------------------------------------------------------------
    |
    | This is the default currency used for your payments.
    |
    */

    'currency' => env('CURRENCY', 'EUR'),

    /*
    |--------------------------------------------------------------------------
    | Stripe API Version (Optional)
    |--------------------------------------------------------------------------
    |
    | Optionally specify the Stripe API version. Leave null to use default.
    |
    */

    'api_version' => env('STRIPE_API_VERSION', null),

    /*
    |--------------------------------------------------------------------------
    | Stripe Logging (Optional)
    |--------------------------------------------------------------------------
    |
    | Log Stripe requests/responses for debugging (only in dev mode).
    |
    */

    'log_requests' => env('STRIPE_LOG', false),
];
