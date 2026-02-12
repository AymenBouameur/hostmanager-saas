<?php namespace MajorMedia\ToolBox\Updates;

use October\Rain\Database\Updates\Migration;
use Schema;

class AddUserIdToMajormediaToolboxMessagesTable extends Migration
{
    public function up()
    {
            Schema::table('majormedia_toolbox_messages', function ($table) {
                $table->unsignedBigInteger('user_id')->index('fk_user_id');
                
                $table->foreign('user_id','fk_messages_user_id')->references('id')->on('users')->onDelete('cascade')->onUpdate('cascade');
            });
    }
    public function down()
    {
        Schema::table('majormedia_toolbox_messages', function ($table) {
            \DB::statement('SET FOREIGN_KEY_CHECKS = 0');
            $table->dropForeign('fk_messages_user_id');
            $table->dropColumn('user_id');
            \DB::statement('SET FOREIGN_KEY_CHECKS = 1');
        });
    }
}
