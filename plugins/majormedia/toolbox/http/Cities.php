<?php namespace MajorMedia\ToolBox\Http;
use Backend\Classes\Controller;
/**
 * Cities Back-end Controller
 */
class Cities extends Controller
{
  public $implement = [
    'MajorMedia.ToolBox.Behaviors.RestController'
  ];
  public $restConfig = 'config_rest.yaml';
  public function extendModel($model)
  {
    if ($ciso = \Request::input('country_code')) {
      $model = $model->whereHas('state', function ($q) use ($ciso) {
        $q->whereHas('country', function ($q) use ($ciso) {
          $q->whereCode($ciso);
        });
      });
    }
    return $model->active()->ordered(['is_pinned' => 'desc', 'name' => 'asc', 'sort_order' => 'asc']);
  }
  // public function extendAfterFetch($response)
  // {
  //   try {
  //     foreach($response as $i => $r){
  //        $r->translateContext(\Request::input('lang'));
  //        $response[$i] = $r;
  //     }
  //   } catch (Exception $ex) {
  //     return response()->json(['error' => 1, 'messages' => $ex->getMessage()], 400);
  //   }
  //   return $response;
  // }
}