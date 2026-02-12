<?php namespace MajorMedia\ToolBox\Traits;

use System\Classes\PluginManager;
use JWTAuth;
use AuthException;

trait GetCurrentUserFromJWT
{

  public function getCurrentUser()
  {
    if (!PluginManager::instance()->exists('Vdomah.JWTAuth')) {
      //throw new ApplicationException("Vous ne pouvez pas utiliser la fonction 'getCurrentUser' sans installer le plugin: Vdomah.JWTAuth");
      return response()->json(['error' => 1, 'code' => 12000, 'messages' => ['user_id' => "User not found by given user_id !"]], 400);
    }
    // Get the currently authenticated user

    $token = explode(" ", request()->header("authorization"))[1] ?? null;

    if(!$token) 
      return null;

    if (empty($user = JWTAuth::toUser($token))) {
      //throw new ApplicationException("Aucun utilisateur n'a été trouvé !");
      return response()->json(['error' => 1, 'code' => 12001, 'messages' => ['user_id' => "User not found by given user_id !"]], 400);
    }
    // todo: throw when user is desactivated
    return $user;
  }

}
