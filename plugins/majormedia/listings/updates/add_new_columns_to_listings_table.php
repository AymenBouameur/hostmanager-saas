<?php namespace Majormedia\Listings\Updates;

use Schema;
use October\Rain\Database\Schema\Blueprint;
use October\Rain\Database\Updates\Migration;

/**
 * AddNewColumnsToListingsTable Migration
 *
 * @link https://docs.octobercms.com/3.x/extend/database/structure.html
 */
return new class extends Migration
{
    /**
     * up builds the migration
     */
    public function up()
    {
        Schema::table('majormedia_listings_listings', function(Blueprint $table) {
            $table->string('owner_full_name')->nullable()->index('index_owner_full_name');
        });
    }

    /**
     * down reverses the migration
     */
    public function down()
    {
        Schema::table('majormedia_listings_listings', function(Blueprint $table) {
            $table->dropColumn('owner_full_name');
            $table->dropIndex('index_owner_full_name');
        });
    }
};
