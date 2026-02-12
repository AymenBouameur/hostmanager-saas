<?php namespace MajorMedia\ToolBox\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateToolsStates extends Migration
{
  public function up()
  {
    Schema::create('majormedia_toolbox_states', function ($table) {
      $table->engine = 'InnoDB';
      $table->increments('id')->unsigned();
      $table->integer('country_id')->nullable()->unsigned()->index('index_country_id');
      $table->string('name')->nullable()->index('index_name');
      $table->string('slug')->nullable()->unique('unique_slug');
      $table->string('code')->nullable()->index('index_code');
      $table->string('lat')->nullable()->index('index_lat');
      $table->string('lng')->nullable()->index('index_lng');
      $table->boolean('is_active')->default(1)->index('index_is_active');
      $table->boolean('is_pinned')->default(0)->index('index_is_pinned');
      $table->integer('sort_order')->default(0)->index('index_sort_order');
      $table->timestamp('created_at')->nullable()->index('index_created_at');
      $table->timestamp('updated_at')->nullable()->index('index_updated_at');
      $table->unique(['country_id', 'code'], 'unique_country_id_code');
      $table->foreign('country_id', 'fk_states_country_id')->references('id')->on('majormedia_toolbox_countries')->onUpdate('cascade')->onDelete('cascade');
    });
  }

  public function down()
  {
    \DB::statement('SET FOREIGN_KEY_CHECKS = 0');
    Schema::dropIfExists('majormedia_toolbox_states');
    \DB::statement('SET FOREIGN_KEY_CHECKS = 1');
  }
}
