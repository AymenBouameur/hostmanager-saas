<?php namespace Majormedia\Billing\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

return new class extends Migration
{
    public function up()
    {
        Schema::create('majormedia_billing_payments', function ($table) {
            $table->increments('id');
            $table->unsignedInteger('company_id')->index('fk_company_id');
            $table->unsignedInteger('subscription_id')->index('fk_subscription_id');
            $table->unsignedInteger('payment_method_id')->nullable()->index('fk_payment_method_id');
            $table->string('invoice_number')->unique('unique_invoice_number');
            $table->decimal('amount', 10, 2)->index('index_amount');
            $table->decimal('tax', 10, 2)->default(0)->index('index_tax');
            $table->decimal('total', 10, 2)->index('index_total');
            $table->smallInteger('status')->default(1)->index('index_status');
            $table->date('billing_period_start')->index('index_billing_period_start');
            $table->date('billing_period_end')->index('index_billing_period_end');
            $table->timestamp('paid_at')->nullable()->index('index_paid_at');
            $table->timestamp('created_at')->nullable()->index('index_created_at');
            $table->timestamp('updated_at')->nullable()->index('index_updated_at');

            $table->foreign('company_id', 'fk_payments_company_id')
                ->references('id')
                ->on('majormedia_companies_companies')
                ->onDelete('cascade')
                ->onUpdate('cascade');

            $table->foreign('subscription_id', 'fk_payments_subscription_id')
                ->references('id')
                ->on('majormedia_billing_subscriptions')
                ->onDelete('cascade')
                ->onUpdate('cascade');

            $table->foreign('payment_method_id', 'fk_payments_payment_method_id')
                ->references('id')
                ->on('majormedia_billing_payment_methods')
                ->onDelete('set null')
                ->onUpdate('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('majormedia_billing_payments');
    }
};
