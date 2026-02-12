<?php namespace MajorMedia\ToolBox\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateMajorMediaToolBoxCities extends Migration
{
    public function up()
    {
        Schema::table('majormedia_toolbox_cities', function($table)
        {
            $table->string('postal_code')->nullable()->index('index_postal_code');
        });
    }
    
    public function down()
    {
        Schema::table('majormedia_toolbox_cities', function($table)
        {
            $table->dropColumn('postal_code');
        });
    }
}
