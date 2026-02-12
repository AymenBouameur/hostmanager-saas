<?php

use Majormedia\Eviivo\Http\Webhook;

Route::group(['prefix' => 'getApi/v1/endpoint'], function () {
  Route::post('/webhook/eviivo', Webhook::class.'@handle');

});