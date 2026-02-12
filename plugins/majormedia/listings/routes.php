<?php

use Majormedia\Listings\Http\Invoices;
use Majormedia\Listings\Http\Listings;
use Majormedia\Listings\Http\Statements;

Route::group(['prefix' => 'getApi/v1/endpoint'], function () {
  Route::resource('listings', Listings::class);
  Route::resource('statements', Statements::class);
  Route::resource('invoices', Invoices::class);
  Route::get('reports/generate-pdf/{listingId}', Statements::class . '@generatePdf')->name('reports.generate-pdf');
  Route::get('reports/generate-invoice/{listingId}', Invoices::class . '@generateInvoice');
  Route::get('test-pdf', Statements::class . '@testPdf');
});