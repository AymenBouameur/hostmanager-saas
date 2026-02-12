<?php namespace Majormedia\Companies\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

return new class extends Migration
{
    public function up()
    {
        Schema::create('majormedia_companies_companies', function ($table) {
            $table->increments('id');
            $table->string('name')->index('index_name');
            $table->string('email')->nullable()->unique('unique_email');
            $table->string('phone')->nullable()->index('index_phone');
            $table->string('address', 512)->nullable();
            $table->unsignedInteger('city_id')->nullable()->index('fk_city_id');
            $table->string('tax_id')->nullable()->index('index_tax_id');
            $table->string('website')->nullable();
            $table->boolean('is_active')->default(1)->index('index_is_active');
            $table->boolean('is_pinned')->default(0)->index('index_is_pinned');
            $table->integer('sort_order')->default(0)->index('index_sort_order');
            $table->timestamp('created_at')->nullable()->index('index_created_at');
            $table->timestamp('updated_at')->nullable()->index('index_updated_at');

            $table->foreign('city_id', 'fk_companies_city_id')
                ->references('id')
                ->on('majormedia_toolbox_cities')
                ->onDelete('set null')
                ->onUpdate('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('majormedia_companies_companies');
    }
};
