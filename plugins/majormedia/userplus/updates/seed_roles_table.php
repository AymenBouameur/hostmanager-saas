<?php

namespace Majormedia\Userplus\Updates;

use Majormedia\UserPlus\Models\Role;
use October\Rain\Database\Updates\Seeder;

class SeedRolesTable extends Seeder
{
  public function run()
  {
    Role::create(['name' => 'Client']);
    Role::create(['name' => 'Liverur']);
  }
}
