<?php namespace MajorMedia\Billing\Models;

use Model;
use October\Rain\Database\Traits\Validation;

class Payment extends Model
{
    use Validation;

    public $table = 'majormedia_billing_payments';

    public $rules = [
        'company_id'      => 'required',
        'subscription_id' => 'required',
        'invoice_number'  => 'required|unique:majormedia_billing_payments',
        'amount'          => 'required|numeric',
        'total'           => 'required|numeric',
    ];

    public $fillable = [
        'company_id',
        'subscription_id',
        'payment_method_id',
        'invoice_number',
        'amount',
        'tax',
        'total',
        'status',
        'billing_period_start',
        'billing_period_end',
        'paid_at',
    ];

    public $visible = [
        'id',
        'company_id',
        'subscription_id',
        'payment_method_id',
        'invoice_number',
        'amount',
        'tax',
        'total',
        'status',
        'billing_period_start',
        'billing_period_end',
        'paid_at',
        'created_at',
        'updated_at',
        'company',
        'subscription',
        'payment_method',
    ];

    public $belongsTo = [
        'company'        => [\MajorMedia\Companies\Models\Company::class, 'key' => 'company_id'],
        'subscription'   => [Subscription::class, 'key' => 'subscription_id'],
        'payment_method' => [PaymentMethod::class, 'key' => 'payment_method_id'],
    ];

    const LABEL_STATUS_1 = 'Pending';
    const LABEL_STATUS_2 = 'Paid';
    const LABEL_STATUS_3 = 'Failed';
    const LABEL_STATUS_4 = 'Refunded';
}
