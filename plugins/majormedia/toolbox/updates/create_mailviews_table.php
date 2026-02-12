<?php namespace MajorMedia\ToolBox\Updates;

use Schema;
use October\Rain\Database\Schema\Blueprint;
use October\Rain\Database\Updates\Migration;

class CreateMailviewsTable extends Migration
{
    public function up()
    {
        Schema::create('majormedia_toolbox_mailviews', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->timestamps();
        });
    }

    public function down()
    {
      \DB::statement('SET FOREIGN_KEY_CHECKS = 0');
        Schema::dropIfExists('majormedia_toolbox_mailviews');
      \DB::statement('SET FOREIGN_KEY_CHECKS = 1');
    }
}
