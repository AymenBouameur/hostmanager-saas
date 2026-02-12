<?php namespace MajorMedia\ToolBox\Updates;

use Schema;
use October\Rain\Database\Schema\Blueprint;
use October\Rain\Database\Updates\Migration;

class CreateMailViewsTable extends Migration
{
    public function up()
    {
        Schema::create('majormedia_toolbox_mail_views', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->timestamps();
        });
    }

    public function down()
    {
      \DB::statement('SET FOREIGN_KEY_CHECKS = 0');
        Schema::dropIfExists('majormedia_toolbox_mail_views');
      \DB::statement('SET FOREIGN_KEY_CHECKS = 1');
    }
}
