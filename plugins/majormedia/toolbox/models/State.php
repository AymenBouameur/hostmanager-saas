<?php namespace MajorMedia\ToolBox\Models;

use MajorMedia\ToolBox\Traits\ActiveField;
use MajorMedia\ToolBox\Traits\PinnedField;
use October\Rain\Database\Model;
use October\Rain\Database\Traits\SimpleTree;
use October\Rain\Database\Traits\Sluggable;
use October\Rain\Database\Traits\Sortable;
use October\Rain\Halcyon\Traits\Validation;

class State extends Model
{
  use Validation;
  use Sluggable;
  use SimpleTree;
  use Sortable;
  use ActiveField;
  use PinnedField;

  public $table = 'majormedia_toolbox_states';

  public $implement = ['@RainLab.Translate.Behaviors.TranslatableModel'];
  public $translatable = ['name', 'slug'];

  /*
   * Start: This section id used for API
   */
  protected $appends = [];
  protected $visible = ['id', 'country_id', 'name', 'slug', 'code', 'lat', 'lng', 'is_active', 'is_pinned', 'sort_order', 'country', 'cities'];
  /*
   * END
   */

  protected $slugs = ['slug' => 'name'];

  public $rules = [
    'name' => 'required',
    //'code' => 'required',
  ];

  public $belongsTo = [
    'country' => [Country::class],
    'country_active' => [Country::class, 'scope' => 'active'],
    'country_active_ordered' => [Country::class, 'scope' => 'activeOrdered'],
  ];

  public $hasMany = [
    'cities' => [City::class],
    'cities_active' => [City::class, 'scope' => 'active'],
    'cities_active_ordered' => [City::class, 'scope' => 'activeOrdered'],
  ];

  public function scopeOrdered($q, $options = ['is_pinned' => 'desc', 'name' => 'asc'])
  {
    foreach ($options as $column => $direction)
      $q->orderBy($column, $direction);
    return $q;
  }

  public function scopeActiveOrdered($q)
  {
    return $q->active()->ordered(['is_pinned' => 'desc', 'name' => 'asc']);
  }
}
