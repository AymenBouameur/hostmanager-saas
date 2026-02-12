<?php namespace Majormedia\InCore\Models;

use October\Rain\Database\Model;

class Settings extends Model
{
  public $implement = ['System.Behaviors.SettingsModel'];

  // A unique code
  public $settingsCode = 'majormedia_incore_settings';

  // Reference to field configuration
  public $settingsFields = 'fields.yaml';

}