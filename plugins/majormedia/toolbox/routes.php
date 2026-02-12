<?php

  Route::group(['prefix' => 'getApi/v1/endpoint'], function () {
    Route::resource('cities', \MajorMedia\ToolBox\Http\Cities::class);
    Route::resource('states', \MajorMedia\ToolBox\Http\States::class);
    Route::resource('countries', \MajorMedia\ToolBox\Http\Countries::class);
  });

  Route::resource('getApi/v1/endpoint/messages', \MajorMedia\ToolBox\Http\Messages::class);

  Route::group(['prefix' => 'getApi/v2/endpoint'], function () {
    Route::post('subscribe', "\MajorMedia\ToolBox\Http\Newsletters@subscribe");
  });

  Route::post('/ajax/cities/search', function () {
    $results = ['query' => $query = post('query', ''), 'suggestions' => []];

    $q = \MajorMedia\ToolBox\Models\City::active()->where(function ($q) use ($query) {
      foreach (explode(' ', $query) as $t) {
        $q->orWhere('name', 'like', '%' . $t . '%');
        $q->orWhere('postal_code', 'like', '%' . $t . '%');
      }
    });
    $result = $q->with(['state' => function ($q) {
      $q->active()->with(['country' => function ($q) {
        $q->active();
      }]);
      if ($country_id = post('country_id')) {
        $q->whereCountryId($country_id);
      }
    }])->whereHas('state', function ($q) {
      $q->active()->whereHas('country', function ($q) {
        $q->active();
      });
      if ($country_id = post('country_id')) {
        $q->whereCountryId($country_id);
      }
    })->ordered()->paginate(20);
    foreach ($result as $item) {
      $results['suggestions'][] = [
        'value' => $item->name . ($item->postal_code ? ' (' . $item->postal_code . ')' : ''),
        'data' => ['code' => $item->id],
      ];
    }
    return $results;
  });