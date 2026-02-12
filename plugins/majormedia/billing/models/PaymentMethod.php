<?php namespace MajorMedia\Billing\Models;

use Model;
use October\Rain\Database\Traits\Validation;

class PaymentMethod extends Model
{
    use Validation;

    public $table = 'majormedia_billing_payment_methods';

    public $rules = [
        'company_id' => 'required',
    ];

    public $fillable = [
        'company_id',
        'type',
        'label',
        'last_four',
        'holder_name',
        'details',
        'is_default',
        'is_active',
    ];

    public $visible = [
        'id',
        'company_id',
        'type',
        'label',
        'last_four',
        'holder_name',
        'is_default',
        'is_active',
        'created_at',
        'updated_at',
        'company',
    ];

    public $belongsTo = [
        'company' => [\MajorMedia\Companies\Models\Company::class, 'key' => 'company_id'],
    ];

    public $hasMany = [
        'payments' => [Payment::class, 'key' => 'payment_method_id'],
    ];

    const LABEL_TYPE_1 = 'Credit Card';
    const LABEL_TYPE_2 = 'Bank Transfer';
    const LABEL_TYPE_3 = 'Check';
    const LABEL_TYPE_4 = 'Other';
}
