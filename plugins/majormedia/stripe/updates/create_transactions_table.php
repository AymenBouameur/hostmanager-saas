<?php namespace Majormedia\Stripe\Updates;

use Schema;
use October\Rain\Database\Schema\Blueprint;
use October\Rain\Database\Updates\Migration;

return new class extends Migration
{
    public function up()
    {
        Schema::create('majormedia_stripe_transactions', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->string('ref')->index('index_ref');
            $table->string('stripe_payment_intent_id')->nullable()->index('index_stripe_pi');
            $table->boolean('paid')->default(0)->index('index_paid');
            $table->decimal('amount', 10, 2)->index('index_amount');
            $table->unsignedInteger('company_id')->nullable()->index('fk_company_id');
            $table->unsignedInteger('subscription_id')->nullable()->index('fk_subscription_id');

            $table->foreign('company_id', 'fk_transactions_company_id')
                ->references('id')
                ->on('majormedia_companies_companies')
                ->onDelete('set null')
                ->onUpdate('cascade');

            $table->foreign('subscription_id', 'fk_transactions_subscription_id')
                ->references('id')
                ->on('majormedia_billing_subscriptions')
                ->onDelete('set null')
                ->onUpdate('cascade');

            $table->boolean('is_active')->default(1)->index('index_is_active');
            $table->boolean('is_pinned')->default(0)->index('index_is_pinned');
            $table->integer('sort_order')->default(1)->index('index_sort_order');
            $table->timestamp('created_at')->nullable()->index('index_created_at');
            $table->timestamp('updated_at')->nullable()->index('index_updated_at');
        });
    }

    public function down()
    {
        Schema::dropIfExists('majormedia_stripe_transactions');
    }
};
