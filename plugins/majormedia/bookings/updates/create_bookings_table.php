<?php
namespace MajorMedia\Bookings\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateChimatchBookinsgBookings extends Migration
{
    public function up()
    {
        Schema::create('majormedia_bookings_bookings', function ($table) {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->unsignedBigInteger('listing_id')->index('fk_listing_id');
            $table->string('reference')->nullable()->unique('unique_reference');
            $table->string('order_reference')->nullable()->index('index_order_reference');
            $table->string('customer')->nullable()->index('index_customer');
            $table->timestamp('check_in')->nullable()->index('index_check_in');
            $table->timestamp('check_out')->nullable()->index('index_check_out');
            $table->decimal('amount', 10, 2)->nullable()->index('index_amount');
            $table->decimal('net_amount', 10, 2)->nullable()->index('index_net_amount');
            $table->decimal('gross_amount', 10, 2)->nullable()->index('index_gross_amount');
            $table->decimal('cleaning_fee', 10, 2)->nullable()->index('index_cleaning_fee');
            $table->decimal('tax', 10, 2)->nullable()->index('index_tax');
            $table->decimal('vacaloc_commission', 10, 2)->nullable()->index('index_vacaloc_commission');
            $table->decimal('ota_commission', 10, 2)->nullable()->index('index_ota_commission');
            $table->decimal('total_amount_order', 10, 2)->nullable()->index('index_total_amount_order');
            $table->decimal('owner_profit', 10, 2)->nullable()->index('index_owner_profit');
            $table->smallInteger('canal')->default(1)->index('index_canal');
            $table->boolean('is_canceled')->default(0)->index('is_canceled_index');
            $table->boolean('is_active')->default(1)->index('index_is_active');
            $table->timestamp('created_at')->nullable()->index('index_created_at');
            $table->timestamp('updated_at')->nullable()->index('index_updated_at');

            $table->foreign('listing_id', 'fk_bookings_listing_id')->references('id')->on('majormedia_listings_listings')->onDelete('cascade')->onUpdate('cascade');
        });
    }

    public function down()
    {
        \DB::statement('SET FOREIGN_KEY_CHECKS = 0');
        Schema::dropIfExists('majormedia_bookings_bookings');
        \DB::statement('SET FOREIGN_KEY_CHECKS = 1');
    }
}
