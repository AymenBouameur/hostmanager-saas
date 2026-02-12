<?php

namespace MajorMedia\ToolBox\Updates;

use MajorMedia\ToolBox\Models\Role;
use October\Rain\Database\Updates\Seeder;

class SeedRolesTable extends Seeder
{
  public function run()
  {
    Role::create(['name' => 'client']);
    Role::create(['name' => 'coach']);
    Role::create(['name' => 'dietitian']);
    Role::create(['name' => 'osteopath']);
  }
}
