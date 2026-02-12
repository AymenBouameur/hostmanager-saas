<?php namespace MajorMedia\ToolBox\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateMajorMediaToolBoxNewslettersCategories extends Migration
{
  public function up()
  {
    Schema::create('majormedia_toolbox_newsletters_categories', function ($table) {
      $table->engine = 'InnoDB';
      $table->increments('id')->unsigned();
      $table->string('name')->nullable()->index('index_name');
      $table->boolean('is_active')->default(1)->index('index_is_active');
      $table->boolean('is_pinned')->default(0)->index('index_is_pinned');
      $table->integer('sort_order')->default(0)->index('index_sort_order');
      $table->timestamp('created_at')->nullable()->index('index_created_at');
      $table->timestamp('updated_at')->nullable()->index('index_updated_at');
    });
  }

  public function down()
  {
    \DB::statement('SET FOREIGN_KEY_CHECKS = 0');
    Schema::dropIfExists('majormedia_toolbox_newsletters_categories');
    \DB::statement('SET FOREIGN_KEY_CHECKS = 1');
  }
}
