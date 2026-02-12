<?php namespace MajorMedia\ToolBox\Traits;

use MajorMedia\ToolBox\Models\Settings;
use System\Classes\PluginManager;

trait GetSetting
{
  public function getSetting($key)
  {
    if (PluginManager::instance()->exists('RainLab.Translate')) {
      return Settings::instance()->getAttributeTranslated($key, \RainLab\Translate\Classes\Translator::instance()->getLocale());
    } else {
      return Settings::instance()->$key;
    }
  }
}
