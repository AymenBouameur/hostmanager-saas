<?php

namespace MajorMedia\Userplus\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class AddNewFieldsToUsersTable extends Migration
{
    public function up()
    {
        Schema::table('users', function ($table) {
            $table->integer('role_id')->unsigned()->after('id')->nullable()->index('fk_role_id');
            $table->date('birth_day')->nullable()->index('index_birth_day');
            $table->boolean('mail_notifications')->default(1)->index('index_mail_notifications');
            $table->integer('otp_code')->unsigned()->nullable()->index('index_otp_code');
            $table->string('phone')->nullable()->index('index_phone');
            // completed profile
            $table->boolean('completed_profile')->default(0)->index('index_completed_profile');

            // firebase and auth
            $table->text('token')->nullable();
            $table->text('messagesToken')->nullable();
            $table->text('externalResponseFb')->nullable();
            $table->text('externalResponseGoogle')->nullable();
            $table->text('externalResponseApple')->nullable();

            $table->foreign('role_id', 'fk_users_role_id')->references('id')->on('majormedia_userplus_roles')->onDelete('set null')->onUpdate('CASCADE');

        });
    }

    public function down()
    {
        Schema::table('users', function ($table) {
            $table->dropColumn('otp_code');
            $table->dropColumn('token');
            $table->dropColumn('externalResponseFb');
            $table->dropColumn('externalResponseGoogle');
            $table->dropColumn('externalResponseApple');
        });
    }
}
