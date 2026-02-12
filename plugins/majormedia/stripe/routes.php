<?php namespace Majormedia\Stripe;

use Route;

Route::group(['prefix' => 'getApi/v1/endpoint'], function () {
    Route::post('webhook', Http\Webhook::class);
    Route::post('subscription/pay', Http\Payments::class . '@paySubscription');
    Route::get('sync-stripe-admins', Http\Payments::class . '@syncStripeAdmins');
});
