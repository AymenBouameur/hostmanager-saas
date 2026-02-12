<?php namespace Majormedia\Companies\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

return new class extends Migration
{
    public function up()
    {
        Schema::table('majormedia_listings_listings', function ($table) {
            $table->unsignedInteger('company_id')->nullable()->after('user_id')->index('fk_company_id');
            $table->integer('nb_rooms')->nullable()->after('owner_full_name')->index('index_nb_rooms');

            $table->foreign('company_id', 'fk_listings_company_id')
                ->references('id')
                ->on('majormedia_companies_companies')
                ->onDelete('set null')
                ->onUpdate('cascade');
        });
    }

    public function down()
    {
        Schema::table('majormedia_listings_listings', function ($table) {
            $table->dropForeign('fk_listings_company_id');
            $table->dropColumn(['company_id', 'nb_rooms']);
        });
    }
};
