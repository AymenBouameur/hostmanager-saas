<?php
namespace MajorMedia\UserPlus\Models;

use Model;
use RainLab\User\Models\User;

/**
 * Model
 */
class Role extends Model
{
    use \October\Rain\Database\Traits\Validation;


    /**
     * @var string The database table used by the model.
     */
    public $table = 'majormedia_userplus_roles';

    /**
     * @var array Validation rules
     */
    public $rules = [
    ];

    /**
     * @var array Attributes to be shown in the API representation of the model (ex. toArray())
     */
    protected $visible = ['id', 'name'];

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

    public $hasMany = [
        'users' => [User::class]

    ];

    public $belongsTo = [];
    public $belongsToMany = [];
    public $morphTo = [];
    public $morphOne = [];
    public $morphMany = [];
    public $attachOne = [];
    public $attachMany = [];
}
