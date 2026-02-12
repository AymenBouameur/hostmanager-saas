<?php namespace MajorMedia\ToolBox\Updates;

use Backend\Models\User;
use DB;
use MajorMedia\ToolBox\Traits\CustomSettings;
use Seeder;

class seedCommonSettings extends Seeder
{
  use CustomSettings;

  public function run()
  {
    // SET System_settings
    $this->setNewSystemSettings('rainlab_builder_settings', '{"author_name":"@MajorMedia","author_namespace":"MajorMedia"}');
    $this->setNewSystemSettings('system_log_settings', '{"log_events":"1","log_requests":"1","log_theme":"1"}');

    // Set Backend user interface
    $this->setUserPreferences();

    // Set Manager Role
    $this->setManagerRole();
  }
}