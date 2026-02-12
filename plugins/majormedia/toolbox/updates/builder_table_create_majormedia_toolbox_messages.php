<?php namespace MajorMedia\ToolBox\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateMajorMediaToolBoxContactMessages extends Migration
{
    public function up()
    {
        Schema::create('majormedia_toolbox_messages', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->string('full_name')->nullable();
            $table->string('company')->nullable();
            $table->string('service')->nullable();
            $table->string('website')->nullable();
            $table->string('duration')->nullable();
            $table->string('subject')->nullable();
            $table->text('message')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
        });
    }
    
    public function down()
    {
      \DB::statement('SET FOREIGN_KEY_CHECKS = 0');
        Schema::dropIfExists('majormedia_toolbox_messages');
      \DB::statement('SET FOREIGN_KEY_CHECKS = 1');
    }
}