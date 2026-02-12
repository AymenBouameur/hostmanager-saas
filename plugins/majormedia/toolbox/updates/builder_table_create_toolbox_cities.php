<?php
namespace MajorMedia\ToolBox\Updates;

use October\Rain\Database\Updates\Migration;
use Schema;

class BuilderTableCreateToolsCities extends Migration
{
  public function up()
  {
    Schema::create('majormedia_toolbox_cities', function ($table) {
      $table->engine = 'InnoDB';
      $table->increments('id')->unsigned();
      $table->integer('state_id')->nullable()->unsigned()->index('index_state_id');
      $table->string('name')->nullable()->index('index_name');
      $table->string('slug')->nullable()->unique('unique_slug');
      $table->string('lat')->nullable()->index('index_lat');
      $table->string('lng')->nullable()->index('index_lng');
      // $table->string('postal_code')->nullable()->index('index_postal_code');
      $table->boolean('is_active')->default(1)->index('index_is_active');
      $table->boolean('is_pinned')->default(0)->index('index_is_pinned');
      $table->integer('sort_order')->default(0)->index('index_sort_order');
      $table->timestamp('created_at')->nullable()->index('index_created_at');
      $table->timestamp('updated_at')->nullable()->index('index_updated_at');
      $table->foreign('state_id', 'fk_cities_state_id')->references('id')->on('majormedia_toolbox_states')->onUpdate('cascade')->onDelete('cascade');
    });
  }

  public function down()
  {
    \DB::statement('SET FOREIGN_KEY_CHECKS = 0');
    Schema::dropIfExists('majormedia_toolbox_cities');
    \DB::statement('SET FOREIGN_KEY_CHECKS = 1');
  }
}
