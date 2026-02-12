<?php
namespace MajorMedia\ToolBox\Models;

use October\Rain\Database\Model;
use System\Models\File;
use RainLab\User\Models\User;
use App;
use Event;
use Mail;

class Message extends Model
{
  // use \October\Rain\Database\Traits\Validation;

  /**
   * @var string The database table used by the model.
   */
  public $table = 'majormedia_toolbox_messages';

  /*
   * Start: This section id used for API
   */
  protected $appends = [];
  protected $visible = ['id', 'full_name', 'phone', 'email', 'company', 'service', 'website', 'subject', 'message', 'files'];
  /*
   * END
   */

  /**
   * @var array Guarded fields
   */
  protected $guarded = ['*'];

  /**
   * @var array Fillable fields
   */
  protected $fillable = [
    'user_id',
    'full_name',
    'phone',
    'email',
    'company',
    'service',
    'website',
    'subject',
    'message',
    'files'
  ];

  /**
   * @var array Validation rules for attributes
   */
  public $rules = [
    'full_name' => 'required',
    'email' => 'required|email',
    'subject' => 'required',
    'message' => 'required',
  ];

  public $customAttributes = [
    'full_name' => 'Nom',
    'email' => 'E-mail',
    'phone' => 'Téléphone',
  ];

  public $customMessages = [
    'full_name.required' => 'Le champ Nom est obligatoire',
    'email.required' => 'Le champ Email est obligatoire',
    'subject.required' => 'Le champ Sujet est obligatoire',
    'message.required' => 'Le champ Message est obligatoire',
  ];

  /**
   * @var array Attributes to be cast to native types
   */
  protected $casts = [];

  /**
   * @var array Attributes to be cast to JSON
   */
  protected $jsonable = [];

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

  public $attachOne = [
    'file' => [File::class],
  ];
  public $attachMany = [
  ];

  /**
   * @var array Relations
   */
  public $hasOne = [];
  public $hasMany = [];
  public $belongsTo = [
    'user' => User::class
  ];
  public $belongsToMany = [];
  public $morphTo = [];
  public $morphOne = [];
  public $morphMany = [];

  public function afterCreate()
  {
    if (App::runningInBackend()) {
      Event::fire('majormedia.toolbox::new_message_from_admin', [$this]);
    }
  }
}
