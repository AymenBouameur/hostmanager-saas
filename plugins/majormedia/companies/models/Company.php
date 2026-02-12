<?php namespace MajorMedia\Companies\Models;

use Model;
use October\Rain\Database\Traits\Validation;

class Company extends Model
{
    use Validation;

    public $table = 'majormedia_companies_companies';

    public $rules = [
        'name' => 'required',
    ];

    public $fillable = [
        'name',
        'email',
        'phone',
        'address',
        'city_id',
        'tax_id',
        'website',
        'is_active',
        'is_pinned',
        'sort_order',
    ];

    public $visible = [
        'id',
        'name',
        'email',
        'phone',
        'address',
        'city_id',
        'tax_id',
        'website',
        'is_active',
        'created_at',
        'updated_at',
        'city',
        'listings',
        'backend_users',
    ];

    public $belongsTo = [
        'city' => [\MajorMedia\ToolBox\Models\City::class, 'key' => 'city_id'],
    ];

    public $hasMany = [
        'listings' => [\MajorMedia\Listings\Models\Listing::class, 'key' => 'company_id'],
    ];

    public $attachOne = [
        'logo' => \System\Models\File::class,
    ];
}
