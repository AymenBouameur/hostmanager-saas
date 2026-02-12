<?php namespace MajorMedia\ToolBox\Updates;

use October\Rain\Database\Updates\Migration;
use Schema;

class BuilderTableCreateMajorMediaToolBoxMailview extends Migration
{
    public function up()
    {
        Schema::create('majormedia_toolbox_mailview', function ($table) {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->string('to')->nullable();
            $table->string('cc')->nullable();
            $table->string('bcc')->nullable();
            $table->string('from')->nullable();
            $table->string('subject')->nullable();
            $table->longText('body')->nullable();
            $table->string('template')->nullable();
            $table->string('mailuniqid', 255)->nullable();
            $table->boolean('sent')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
        });
    }
    
    public function down()
    {
      \DB::statement('SET FOREIGN_KEY_CHECKS = 0');
        Schema::dropIfExists('majormedia_toolbox_mailview');
      \DB::statement('SET FOREIGN_KEY_CHECKS = 1');
    }
}
