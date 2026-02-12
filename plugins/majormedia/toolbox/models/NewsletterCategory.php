<?php namespace MajorMedia\ToolBox\Models;

use MajorMedia\ToolBox\Traits\ActiveField;
use Model;
use MajorMedia\ToolBox\Models\Newsletter;

/**
 * NewsletterCategory Model
 */
class NewsletterCategory extends Model
{
    use \October\Rain\Database\Traits\Validation;
    use ActiveField;

    /**
     * @var string The database table used by the model.
     */
    public $table = 'majormedia_toolbox_newsletters_categories';

    /**
     * @var array Guarded fields
     */
    protected $guarded = ['*'];

    /**
     * @var array Fillable fields
     */
    protected $fillable = [];

    /**
     * @var array Validation rules for attributes
     */
    public $rules = [];

    /**
     * @var array Attributes to be cast to native types
     */
    protected $casts = [];

    /**
     * @var array Attributes to be cast to JSON
     */
    protected $jsonable = [];

    /**
     * @var array Attributes to be appended to the API representation of the model (ex. toArray())
     */
    protected $appends = [];

    /**
     * @var array Attributes to be removed from the API representation of the model (ex. toArray())
     */
    protected $hidden = [];

    /**
     * @var array Attributes to be cast to Argon (Carbon) instances
     */
    protected $dates = [
        'created_at',
        'updated_at'
    ];

    /**
     * @var array Relations
     */
    public $hasOne = [];
    public $hasMany = [];
    public $belongsTo = [];
    public $belongsToMany = [
        'newsletters' => [Newsletter::class, 'table' => 'majormedia_toolbox_newsletters_category_newsletter', 'key' => 'category_id', 'otherKey' => 'newsletter_id']
    ];
    public $morphTo = [];
    public $morphOne = [];
    public $morphMany = [];
    public $attachOne = [];
    public $attachMany = [];
}
