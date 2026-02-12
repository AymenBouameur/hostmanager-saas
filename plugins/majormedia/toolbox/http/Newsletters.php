<?php

namespace MajorMedia\ToolBox\Http;

use Backend\Classes\Controller;
use System\Classes\PluginManager;
use MajorMedia\ToolBox\Models\Newsletter;

/**
 * Messages Back-end Controller
 */
class Newsletters extends Controller {

    public $implement = [
        'MajorMedia.ToolBox.Behaviors.RestController'
    ];
    public $restConfig = 'config_rest.yaml';

  public function extendModel($model)
  {
    return $model->active();
  }
  
  public function subscribe(){
        if (!PluginManager::instance()->exists('Vdomah.JWTAuth')) {
            return response()->json(['status' => 'error', 'message' => 'JWTAuth plugin not provided'], 400);
        }

        $data = \Request::input();
        $rules = [
            'email' => 'required|email',
        ];

        $validation = \Validator::make($data, $rules);
        if ($validation->fails()) {
            return response()->json(['status' => 'error', 'message' => $validation->messages()->first()], 400);
        }
        try{
            if(!empty(Newsletter::whereEmail($data['email'])->first())){
                return response()->json(['status' => 'error', 'message' => 'You have already subscribed with this email'], 400);
            }
            $newsletter = new Newsletter();
            $newsletter->email = $data['email'];
            $newsletter->save();
            return response()->json(['status' => 'success', 'message' => 'You\'ve been subscribed to our newsletter'], 200);
            
        } catch (Exception $ex) {
            return response()->json(['status' => 'error', 'message' => $ex->getMessage()], 400);
        }
  }

}
