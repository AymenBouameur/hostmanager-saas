<?php

use Majormedia\InCore\Http\Extras;

Route::group([
    'prefix' => 'getApi/v1/endpoint',
], function () {

  Route::get('links', "\Majormedia\InCore\Http\Extras@links");
  Route::post('contact-messages',Extras::class . '@contactMessages');

});