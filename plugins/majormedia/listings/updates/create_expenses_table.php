<?php namespace Majormedia\Listings\Updates;

use Schema;
use October\Rain\Database\Schema\Blueprint;
use October\Rain\Database\Updates\Migration;

/**
 * CreateExpensesTable Migration
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
        Schema::create('majormedia_listings_expenses', function(Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('listing_id')->index('fk_listing_id');

            $table->string('label')->nullable()->index('index_customer');
            $table->decimal('amount')->nullable()->index('index_amount');
            $table->smallInteger('expenses_type')->default(1)->index('index_expenses_type');
            $table->timestamp('processed_at')->nullable()->index('index_processed_at');
            
            $table->timestamp('created_at')->nullable()->index('index_created_at');
            $table->timestamp('updated_at')->nullable()->index('index_updated_at');

            $table->foreign('listing_id', 'fk_expenses_listing_id')->references('id')->on('majormedia_listings_listings')->onDelete('cascade')->onUpdate('cascade');

        });
    }

    /**
     * down reverses the migration
     */
    public function down()
    {
        Schema::dropIfExists('majormedia_listings_expenses');
    }
};
