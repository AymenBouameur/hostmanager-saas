<?php

use Majormedia\Statistics\Http\Statistics;
use MajorMedia\ToolBox\Middleware\JwtAuthMiddleware;

Route::group(['prefix' => 'getApi/v1/endpoint/dashboard', 'middleware' => JwtAuthMiddleware::class], function () {
  Route::get('overview', Statistics::class . '@overview');
  Route::get('acquisition-channels', Statistics::class . '@acquisitionChannels');
  Route::get('last-documents', Statistics::class . '@getLastDocumentsPerProperty');
  Route::prefix('charts-data')->group(function () {
    Route::get('bookings-count', Statistics::class . '@getBookingsCount');
    Route::get('bookings-revenue', Statistics::class . '@getBookingsRevenue');
    Route::get('booking-revenue-monthly', Statistics::class . '@getBookingRevenuByMonths');
  });
});