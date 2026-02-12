<?php namespace MajorMedia\ToolBox\Components;

use Cms\Classes\ComponentBase;
use MajorMedia\ToolBox\Traits\GetSetting;

class HelpWidget extends ComponentBase
{
  use GetSetting;

  public $info = [];

  public function componentDetails()
  {
    return [
      'name' => 'HelpWidget Component',
      'description' => 'No description provided yet...'
    ];
  }

  public function defineProperties()
  {
    return [
      'info' => [
        'label' => "Informations à afficher",
        'desciption' => "Séparées par des '|', par exemple: address|contact_email|contact_phone|contact_fix",
        'default' => '',
      ]
    ];
  }

  public function init()
  {
    foreach (explode('|', $this->property('info')) as $key) {
      if (count(explode(':', $key)) == 1 && ($info = $this->getSetting($key))) {
        $this->info[$key] = [
          'type' => 'text',
          'value' => $info,
        ];
      }
      elseif (count($info_arr = explode(':', $key)) == 2 && ($info = $this->getSetting($key = $info_arr[0]))) {
        $this->info[$key] = [
          'type' => $info_arr[1],
          'value' => $info,
        ];
      }
    }
  }
}
