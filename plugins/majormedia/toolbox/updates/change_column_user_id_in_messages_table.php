<?php
namespace MajorMedia\ToolBox\Updates;

use October\Rain\Database\Schema\Blueprint;
use Schema;
use October\Rain\Database\Updates\Migration;

class ChangeColumnUserIdInMessagesTable extends Migration
{
    public function up()
    {
        Schema::table('majormedia_toolbox_messages', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->nullable()->index('fk_user_id')->change();
        });
    }
}