<?php namespace MajorMedia\ToolBox\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateMajorMediaToolBoxNewslettersCategoryNewsletter extends Migration
{
    public function up()
    {
        Schema::create('majormedia_toolbox_newsletters_category_newsletter', function($table)
        {
          $table->integer('newsletter_id')->unsigned()->index('newsletter_id');
          $table->integer('category_id')->unsigned()->index('fk_category_id');
          $table->timestamp('created_at')->nullable()->index('index_created_at');
          $table->timestamp('updated_at')->nullable()->index('index_updated_at');
          $table->primary(['newsletter_id','category_id'],'pk_newsletter_category');
             
          $table->foreign('newsletter_id','fk_category_newsletter_id')->references('id')->on('majormedia_toolbox_newsletters')->onUpdate('cascade')->onDelete('cascade');
          $table->foreign('category_id','fk_newsletter_category_id')->references('id')->on('majormedia_toolbox_newsletters_categories')->onUpdate('cascade')->onDelete('cascade');
        
        });
    }
    
    public function down()
    {
      \DB::statement('SET FOREIGN_KEY_CHECKS = 0');
        Schema::dropIfExists('majormedia_toolbox_newsletters_category_newsletter');
      \DB::statement('SET FOREIGN_KEY_CHECKS = 1');
    }
}
