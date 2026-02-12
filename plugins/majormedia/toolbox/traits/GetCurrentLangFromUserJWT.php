<?php namespace MajorMedia\ToolBox\Traits;

use Illuminate\Pagination\LengthAwarePaginator;

trait GetCurrentLangFromUserJWT
{

  public function setCurrentLangFromUser($user, $model, $force_lang = '')
  {
    $lang = $force_lang ?: $user->lang;
    if (in_array($lang, ['fr', 'ar'])) {
      if ($model instanceof LengthAwarePaginator) {
        foreach ($model as $i => $r) {
          $r->translateContext($lang);
          $model[$i] = $r;
        }
      } else {
        $model->translateContext($lang);
      }
    }
    return $model;
  }

}
