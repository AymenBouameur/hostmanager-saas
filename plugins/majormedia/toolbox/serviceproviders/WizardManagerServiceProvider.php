<?php namespace MajorMedia\ToolBox\ServiceProviders;

use MajorMedia\ToolBox\Classes\Wizard\WizardManager;
use October\Rain\Support\ServiceProvider;

class WizardManagerServiceProvider extends ServiceProvider
{
  public function register()
  {
    $this->app->bind('WizardManager', function() {
      return new WizardManager();
    });
  }
}