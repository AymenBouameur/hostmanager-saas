<?php namespace MajorMedia\ToolBox\Models;

use October\Rain\Database\Model;
use MajorMedia\ToolBox\Models\NewsletterCategory;

/**
 * Model
 */
class Newsletter extends Model
{
  use \October\Rain\Database\Traits\Validation;


  /**
   * @var string The database table used by the model.
   */
  public $table = 'majormedia_toolbox_newsletters';

  /*
   * Start: This section id used for API
   */
  protected $appends = [];
  protected $visible = ['id', 'name', 'email', 'created_at'];
  /*
   * END
   */
  public $belongsToMany = [
      'newsletter_categories' => [NewsletterCategory::class, 'table' => 'majormedia_toolbox_newsletters_category_newsletter', 'key' => 'newsletter_id', 'otherKey' => 'category_id']
  ];
  /**
   * @var array Validation rules
   */
  public $rules = [
    'email' => 'required|email|unique:majormedia_toolbox_newsletters,email'
  ];

  public static function findByEmail($email)
  {
    return empty($email) ? false : (new self())->whereEmail($email)->first();
  }
}
