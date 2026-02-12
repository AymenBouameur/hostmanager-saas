<?php

use MajorMedia\Bookings\Http\Bookings;

Route::group(['prefix' => 'getApi/v1/endpoint',], function () {
  Route::resource('bookings', Bookings::class);
});