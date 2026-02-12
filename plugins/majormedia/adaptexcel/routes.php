<?php

use Majormedia\Adaptexcel\Http\AdaptExcel;
use MajorMedia\Adaptexcel\Http\pdfGenerator;

Route::group(['prefix' => 'getApi/v1/endpoint',], function () {
  // Route::resource('adaptexcel', AdaptExcel::class)->only(['index']);
  Route::get('adaptexcel', AdaptExcel::class . '@method');
  //Route::get('adaptexcel', AdaptExcel::class.'@get');
  Route::post('adaptexcel', AdaptExcel::class . '@matchHeader');
  Route::post('testEvvivoApi', AdaptExcel::class . '@testAccessTokenApi');
  Route::get('available-properties', AdaptExcel::class . '@getAvailableProperties');
});

