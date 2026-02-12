<?php namespace Majormedia\Billing\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

return new class extends Migration
{
    public function up()
    {
        Schema::create('majormedia_billing_payment_methods', function ($table) {
            $table->increments('id');
            $table->unsignedInteger('company_id')->index('fk_company_id');
            $table->smallInteger('type')->default(1)->index('index_type');
            $table->string('label')->nullable()->index('index_label');
            $table->string('last_four', 4)->nullable();
            $table->string('holder_name')->nullable();
            $table->text('details')->nullable();
            $table->boolean('is_default')->default(0)->index('index_is_default');
            $table->boolean('is_active')->default(1)->index('index_is_active');
            $table->timestamp('created_at')->nullable()->index('index_created_at');
            $table->timestamp('updated_at')->nullable()->index('index_updated_at');

            $table->foreign('company_id', 'fk_payment_methods_company_id')
                ->references('id')
                ->on('majormedia_companies_companies')
                ->onDelete('cascade')
                ->onUpdate('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('majormedia_billing_payment_methods');
    }
};
