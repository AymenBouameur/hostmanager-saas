<?php namespace Majormedia\Userplus\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class CreateRolesTable extends Migration
{
    public function up()
    {
        Schema::create('majormedia_userplus_roles', function ($table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->string('name')->index('index_name');
            $table->timestamps();
        });
    }

    public function down()
    {
        \DB::statement('SET FOREIGN_KEY_CHECKS = 0');
        Schema::dropIfExists('majormedia_userplus_roles');
        \DB::statement('SET FOREIGN_KEY_CHECKS = 1');
    }
}
