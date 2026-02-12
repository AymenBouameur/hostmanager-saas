<?php namespace MajorMedia\ToolBox\Traits;

use Event;

/**
 * Class ActiveField
 * @package MajorMedia\Catalog\Traits
 * @author Abdessalam ZAHRAOUI, a.zahraoui@majormedia.com, MajorMedia
 *
 * @property bool $active
 *
 * @method static $this active()
 * @method static $this notActive()
 */
trait ActiveField
{

  /**
   * Get active elements
   * @param \Illuminate\Database\Eloquent\Builder|\October\Rain\Database\Builder $obQuery
   * @return \Illuminate\Database\Eloquent\Builder|\October\Rain\Database\Builder;
   */
  public function scopeActive($obQuery)
  {
    $obQuery = $obQuery->where($this->table . '.is_active', true);
    Event::fire('majormedia.toolbox::extendActiveScope', [&$obQuery]);
    return $obQuery;
  }

  /**
   * Get not active elements
   * @param \Illuminate\Database\Eloquent\Builder|\October\Rain\Database\Builder $obQuery
   * @return \Illuminate\Database\Eloquent\Builder|\October\Rain\Database\Builder;
   */
  public function scopeNotActive($obQuery)
  {
    return $obQuery->where($this->table . '.is_active', false);
  }
}
