<?php namespace Majormedia\Stripe\Models;

use Model;
use October\Rain\Database\Traits\Validation;

class Transaction extends Model
{
    use Validation;

    public $table = 'majormedia_stripe_transactions';

    public $rules = [
        'ref'    => 'required',
        'amount' => 'required|numeric',
    ];

    public $fillable = [
        'ref',
        'stripe_payment_intent_id',
        'paid',
        'amount',
        'company_id',
        'subscription_id',
        'is_active',
        'is_pinned',
        'sort_order',
    ];

    public $visible = [
        'id',
        'ref',
        'stripe_payment_intent_id',
        'paid',
        'amount',
        'company_id',
        'subscription_id',
        'is_active',
        'created_at',
        'updated_at',
        'company',
        'subscription',
    ];

    public $belongsTo = [
        'company'      => [\MajorMedia\Companies\Models\Company::class, 'key' => 'company_id'],
        'subscription' => [\MajorMedia\Billing\Models\Subscription::class, 'key' => 'subscription_id'],
    ];
}
