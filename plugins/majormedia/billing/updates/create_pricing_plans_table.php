<?php namespace Majormedia\Billing\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

return new class extends Migration
{
    public function up()
    {
        Schema::create('majormedia_billing_pricing_plans', function ($table) {
            $table->increments('id');
            $table->string('name')->index('index_name');
            $table->string('slug')->unique('unique_slug');
            $table->text('description')->nullable();
            $table->integer('max_listings')->nullable()->index('index_max_listings');
            $table->integer('max_rooms')->nullable()->index('index_max_rooms');
            $table->integer('max_users')->nullable()->index('index_max_users');
            $table->decimal('monthly_price', 10, 2)->default(0)->index('index_monthly_price');
            $table->decimal('annual_price', 10, 2)->default(0)->index('index_annual_price');
            $table->integer('trial_days')->default(0);
            $table->smallInteger('support_level')->default(1)->index('index_support_level');
            $table->boolean('is_active')->default(1)->index('index_is_active');
            $table->boolean('is_pinned')->default(0)->index('index_is_pinned');
            $table->integer('sort_order')->default(0)->index('index_sort_order');
            $table->timestamp('created_at')->nullable()->index('index_created_at');
            $table->timestamp('updated_at')->nullable()->index('index_updated_at');
        });
    }

    public function down()
    {
        Schema::dropIfExists('majormedia_billing_pricing_plans');
    }
};
