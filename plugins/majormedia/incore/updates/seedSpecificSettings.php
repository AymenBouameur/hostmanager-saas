<?php namespace Majormedia\InCore;

use Majormedia\ToolBox\Traits\CustomSettings;
use Seeder;

class seedSpecificSettings extends Seeder
{
  use CustomSettings;

  public function run()
  {
    $this->initProject(__DIR__ . '/..');
  }
}