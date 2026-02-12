<?php namespace MajorMedia\ToolBox\Models;

use MajorMedia\ToolBox\Traits\ActiveField;
use MajorMedia\ToolBox\Traits\CalculateVAT;
use MajorMedia\ToolBox\Traits\PinnedField;
use October\Rain\Database\Model;
use October\Rain\Database\Traits\SimpleTree;
use October\Rain\Database\Traits\Sluggable;
use October\Rain\Database\Traits\Sortable;
use October\Rain\Halcyon\Traits\Validation;

class Country extends Model
{
  use Validation;
  use Sluggable;
  use SimpleTree;
  use Sortable;
  use ActiveField;
  use PinnedField;

  // todo: Solution provisoire, it is required by MajorMedia.ShippingModes
  //use CalculateVAT;
  //public $vat = ['ht' => 'fees_ht', 'vat' => 'tva', 'ttc' => 'fees_ttc'];

  public $table = 'majormedia_toolbox_countries';

  public $implement = ['@RainLab.Translate.Behaviors.TranslatableModel'];
  public $translatable = ['name', 'slug'];

  /*
   * Start: This section id used for API
   */
  protected $appends = [];
  protected $visible = ['id', 'name', 'slug', 'code', 'lat', 'lng', 'is_active', 'is_pinned', 'sort_order', 'states'];
  /*
   * END
   */

  protected $slugs = ['slug' => 'name'];

  protected $fillable = ['name', 'is_active', 'code'];

  public $rules = [
    'name' => 'required',
    'code' => 'required',
  ];

  public $hasMany = [
    'states' => [State::class],
    'states_active' => [State::class, 'scope' => 'active'],
    'states_active_ordered' => [State::class, 'scope' => 'activeOrdered'],
  ];

  public $hasManyThrough = [
    'cities' => [City::class, 'through' => State::class],
    'cities_active' => [City::class, 'through' => State::class, 'scope' => 'active'],
    'cities_active_ordered' => [City::class, 'through' => State::class, 'scope' => 'activeOrdered'],
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
