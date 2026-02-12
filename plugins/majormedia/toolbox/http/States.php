<?php namespace MajorMedia\ToolBox\Http;

use Backend\Classes\Controller;
use MajorMedia\ToolBox\Traits\GetCurrentLangFromUserJWT;
use MajorMedia\ToolBox\Traits\GetCurrentUserFromJWT;

/**
 * Cities Back-end Controller
 */
class States extends Controller
{
  use GetCurrentUserFromJWT;
  use GetCurrentLangFromUserJWT;

  public $implement = [
    'MajorMedia.ToolBox.Behaviors.RestController'
  ];

  public $restConfig = 'config_rest.yaml';

  public function extendModel($model)
  {
    return $model->active()->ordered(['is_pinned' => 'desc', 'name' => 'asc', 'sort_order' => 'asc']);
  }

  public function extendAfterFetch($response)
  {
    try {
      $user = $this->getCurrentUser();
      $response = $this->setCurrentLangFromUser($user, $response, \Request::input('lang', ''));
    } catch (Exception $ex) {
      return response()->json(['error' => 1, 'messages' => $ex->getMessage()], 400);
    }
    return $response;
  }

}
