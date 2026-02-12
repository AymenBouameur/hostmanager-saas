<?php namespace MajorMedia\Billing\Models;

use Model;
use October\Rain\Database\Traits\Validation;

class PricingPlan extends Model
{
    use Validation;

    public $table = 'majormedia_billing_pricing_plans';

    public $rules = [
        'name' => 'required',
        'slug' => 'required|unique:majormedia_billing_pricing_plans',
    ];

    public $fillable = [
        'name',
        'slug',
        'description',
        'max_listings',
        'max_rooms',
        'max_users',
        'monthly_price',
        'annual_price',
        'trial_days',
        'support_level',
        'is_active',
        'is_pinned',
        'sort_order',
    ];

    public $visible = [
        'id',
        'name',
        'slug',
        'description',
        'max_listings',
        'max_rooms',
        'max_users',
        'monthly_price',
        'annual_price',
        'trial_days',
        'support_level',
        'is_active',
        'created_at',
        'updated_at',
    ];

    public $hasMany = [
        'subscriptions' => [Subscription::class, 'key' => 'pricing_plan_id'],
    ];

    const LABEL_SUPPORT_LEVEL_1 = 'Basic';
    const LABEL_SUPPORT_LEVEL_2 = 'Priority';
    const LABEL_SUPPORT_LEVEL_3 = 'Dedicated';
}
