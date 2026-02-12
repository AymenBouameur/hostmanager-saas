<?php namespace MajorMedia\Billing\Models;

use Model;
use October\Rain\Database\Traits\Validation;

class Subscription extends Model
{
    use Validation;

    public $table = 'majormedia_billing_subscriptions';

    public $rules = [
        'company_id'      => 'required',
        'pricing_plan_id' => 'required',
    ];

    public $fillable = [
        'company_id',
        'pricing_plan_id',
        'status',
        'billing_cycle',
        'starts_at',
        'ends_at',
        'trial_ends_at',
        'next_billing_date',
        'canceled_at',
    ];

    public $visible = [
        'id',
        'company_id',
        'pricing_plan_id',
        'status',
        'billing_cycle',
        'starts_at',
        'ends_at',
        'trial_ends_at',
        'next_billing_date',
        'canceled_at',
        'created_at',
        'updated_at',
        'company',
        'pricing_plan',
    ];

    public $belongsTo = [
        'company'      => [\MajorMedia\Companies\Models\Company::class, 'key' => 'company_id'],
        'pricing_plan' => [PricingPlan::class, 'key' => 'pricing_plan_id'],
    ];

    public $hasMany = [
        'payments' => [Payment::class, 'key' => 'subscription_id'],
    ];

    const LABEL_STATUS_1 = 'Trial';
    const LABEL_STATUS_2 = 'Active';
    const LABEL_STATUS_3 = 'Past Due';
    const LABEL_STATUS_4 = 'Canceled';
    const LABEL_STATUS_5 = 'Expired';

    const LABEL_BILLING_CYCLE_1 = 'Monthly';
    const LABEL_BILLING_CYCLE_2 = 'Annual';
}
