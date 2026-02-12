<?php namespace Majormedia\Stripe\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

return new class extends Migration
{
    public function up()
    {
        Schema::table('backend_users', function ($table) {
            $table->string('stripe_customer_id')->nullable()->after('company_id')->index('index_stripe_customer_id');
        });
    }

    public function down()
    {
        Schema::table('backend_users', function ($table) {
            $table->dropColumn('stripe_customer_id');
        });
    }
};
