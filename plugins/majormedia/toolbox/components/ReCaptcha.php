<?php namespace MajorMedia\ToolBox\Components;

use Cms\Classes\ComponentBase;
use MajorMedia\ToolBox\Models\Settings;
use October\Rain\Exception\ApplicationException;

class ReCaptcha extends ComponentBase
{

  public bool $enable_recaptcha;
  public string $recaptcha_site_key;

  public function componentDetails()
  {
    return [
      'name' => 'reCaptcha Component',
      'description' => 'Displays the reCATPCHA widget.'
    ];
  }

  public function defineProperties()
  {
    return [];
  }

  public function onRun()
  {
    if ($this->enable_recaptcha = Settings::get('recaptcha_enabled', false)) {
      if (empty(Settings::get('recaptcha_site_key'))) {
        throw new ApplicationException("The reCaptcha is not configured: 'recaptcha_site_key' is missing !");
      }
      $this->addJs('https://www.google.com/recaptcha/api.js?render=' . ($this->recaptcha_site_key = Settings::get('recaptcha_site_key')));
    }
  }

}
