<?php namespace Majormedia\Billing\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

return new class extends Migration
{
    public function up()
    {
        Schema::create('majormedia_billing_subscriptions', function ($table) {
            $table->increments('id');
            $table->unsignedInteger('company_id')->index('fk_company_id');
            $table->unsignedInteger('pricing_plan_id')->index('fk_pricing_plan_id');
            $table->smallInteger('status')->default(1)->index('index_status');
            $table->smallInteger('billing_cycle')->default(1)->index('index_billing_cycle');
            $table->timestamp('starts_at')->nullable()->index('index_starts_at');
            $table->timestamp('ends_at')->nullable()->index('index_ends_at');
            $table->timestamp('trial_ends_at')->nullable()->index('index_trial_ends_at');
            $table->timestamp('next_billing_date')->nullable()->index('index_next_billing_date');
            $table->timestamp('canceled_at')->nullable();
            $table->timestamp('created_at')->nullable()->index('index_created_at');
            $table->timestamp('updated_at')->nullable()->index('index_updated_at');

            $table->foreign('company_id', 'fk_subscriptions_company_id')
                ->references('id')
                ->on('majormedia_companies_companies')
                ->onDelete('cascade')
                ->onUpdate('cascade');

            $table->foreign('pricing_plan_id', 'fk_subscriptions_pricing_plan_id')
                ->references('id')
                ->on('majormedia_billing_pricing_plans')
                ->onDelete('cascade')
                ->onUpdate('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('majormedia_billing_subscriptions');
    }
};
