<?php namespace Majormedia\Companies\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

return new class extends Migration
{
    public function up()
    {
        Schema::table('backend_users', function ($table) {
            $table->unsignedInteger('company_id')->nullable()->after('id')->index('fk_company_id');

            $table->foreign('company_id', 'fk_backend_users_company_id')
                ->references('id')
                ->on('majormedia_companies_companies')
                ->onDelete('set null')
                ->onUpdate('cascade');
        });
    }

    public function down()
    {
        Schema::table('backend_users', function ($table) {
            $table->dropForeign('fk_backend_users_company_id');
            $table->dropColumn('company_id');
        });
    }
};
