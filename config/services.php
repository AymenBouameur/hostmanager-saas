<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Stripe, Mailgun, Mandrill, and others. This file provides a sane
    | default location for this type of information, allowing packages
    | to have a conventional place to find your various credentials.
    |
    */

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
        'scheme' => 'https',
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],
    'eviivo' => [
        'provider_url' => env('APP_ENV') === 'production' ? env('PROD_PROVIDER_URL') : env('TEST_PROVIDER_URL'),
        'provider_type' => env('APP_ENV') === 'production' ? env('PROD_PROVIDER_TYPE') : env('TEST_PROVIDER_TYPE'),
        'token_url' => env('APP_ENV') === 'production' ? env('PROD_TOKEN_URL') : env('TEST_TOKEN_URL'),
        'client_id' => env('APP_ENV') === 'production' ? env('PROD_CLIENT_ID') : env('TEST_CLIENT_ID'),
        'client_secret' => env('APP_ENV') === 'production' ? env('PROD_CLIENT_SECRET') : env('TEST_CLIENT_SECRET'),
        'grant_type' => env('APP_ENV') === 'production' ? env('PROD_GRANT_TYPE') : env('TEST_GRANT_TYPE'),
    ],

];
