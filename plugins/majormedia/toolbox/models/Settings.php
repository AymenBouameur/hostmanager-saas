<?php namespace MajorMedia\ToolBox\Models;

use October\Rain\Database\Model;
use System\Models\File;

class Settings extends Model
{
  //use \October\Rain\Database\Traits\Encryptable;
  
  public $implement = ['@RainLab.Translate.Behaviors.TranslatableModel','System.Behaviors.SettingsModel'];
  public $translatable = ['contact_name','address','horaire_travail','form_contact_name'];

  // A unique code
  public $settingsCode = 'majormedia_toolboxsettings';

  // Reference to field configuration
  public $settingsFields = 'fields.yaml';

  //protected $encryptable = ['apis_credentials'];

  //protected $jsonable = ['apis_credentials'];

  public $attachOne = [
    'logo' => [File::class, 'delete' => true],
    'favicon' => [File::class, 'delete' => true]
  ];
}