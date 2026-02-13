<?php namespace MajorMedia\ToolBox\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class AddNewFieldisNoticedOnNewslettersTable extends Migration
{
    public function up()
    {
         Schema::table('majormedia_toolbox_newsletters', function($table)
         {
             $table->boolean('is_noticed')->default(0)->after('email')->index('index_is_noticed');
         });
    }

    public function down()
    {
         Schema::table('majormedia_toolbox_newsletters', function($table)
         {
             $table->dropColumn('is_noticed');
         });
    }
}