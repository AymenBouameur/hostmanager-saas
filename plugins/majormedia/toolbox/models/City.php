<?php
namespace MajorMedia\ToolBox\Models;

use MajorMedia\ToolBox\Traits\ActiveField;
use MajorMedia\ToolBox\Traits\PinnedField;
use October\Rain\Database\Model;
use October\Rain\Database\Traits\SimpleTree;
use October\Rain\Database\Traits\Sluggable;
use October\Rain\Database\Traits\Sortable;
use October\Rain\Database\Traits\Validation;

class City extends Model
{
  use Validation;
  use Sluggable;
  use SimpleTree;
  use Sortable;
  use ActiveField;
  use PinnedField;

  public $table = 'majormedia_toolbox_cities';

  public $implement = ['@RainLab.Translate.Behaviors.TranslatableModel'];
  public $translatable = ['name', 'slug'];

  /*
   * Start: This section id used for API
   */
  protected $appends = ['imagesPathUrl'];
  protected $visible = ['id', 'state_id', 'name', 'slug', 'lat', 'lng', 'is_active', 'is_pinned', 'sort_order', 'state', 'postal_code', 'imagesPathUrl'];
  /*
   * END
   */

  protected $slugs = ['slug' => 'name'];

  protected $fillable = ['name', 'slug', 'state_id', 'is_pinned', 'lat', 'lng'];

  public $rules = [
    'name' => 'required'
  ];

  public $belongsTo = [
    'state' => [State::class],
    'state_active' => [State::class, 'scope' => 'active'],
    'state_active_ordered' => [State::class, 'scope' => 'activeOrdered'],
  ];

  public $attachMany = [
    'images' => [\System\Models\File::class],
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

  public function getImagesPathUrlAttribute()
  {
    $urls = [];
    if ($this->images && $this->images->isNotEmpty()) {
      foreach ($this->images as $image) {
        $urls[] = [
          'id' => $image->id,
          'path' => $image->path,
        ];
      }
      return $urls;
    }
    return [];
  }

  public function getLatAttribute($value)
  {
    return (float) $value;
  }

  public function getLngAttribute($value)
  {
    return (float) $value;
  }

  public function getPostCodeAttribute($value)
  {
    return (float) $value;
  }
}
