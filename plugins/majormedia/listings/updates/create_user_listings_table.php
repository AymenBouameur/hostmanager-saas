<?php namespace Majormedia\Listings\Updates;

use Schema;
use October\Rain\Database\Schema\Blueprint;
use October\Rain\Database\Updates\Migration;

/**
 * CreateUserListingsTable Migration
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
        Schema::create('majormedia_listings_user_listings', function(Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable()->index('fk_user_id');
            $table->unsignedBigInteger('listing_id')->nullable()->index('fk_listing_id');

            $table->foreign('user_id', 'fk_user_listings_user_id')->references('id')->on('users')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('listing_id', 'fk_user_listings_listing_id')->references('id')->on('majormedia_listings_listings')->onDelete('cascade')->onUpdate('cascade');
            
            $table->boolean('is_active')->default(1)->index('index_is_active');
            $table->boolean('is_pinned')->default(0)->index('index_is_pinned');
            $table->integer('sort_order')->default(1)->index('index_sort_order');
            $table->timestamp('created_at')->nullable()->index('index_created_at');
            $table->timestamp('updated_at')->nullable()->index('index_updated_at');
        });
    }

    /**
     * down reverses the migration
     */
    public function down()
    {
        Schema::dropIfExists('majormedia_listings_user_listings');
    }
};
