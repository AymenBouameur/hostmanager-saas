<?php namespace MajorMedia\ToolBox\Traits;

/**
 * Class FilterableField
 * @package MajorMedia\ToolBox\Traits
 * @author Abdessalam ZAHRAOUI, a.zahraoui@majormedia.com, MajorMedia
 *
 * @property bool $filterable
 *
 * @method static $this filterable()
 * @method static $this notFilterable()
 */
trait FilterableField
{

  /**
   * Get filterable elements
   * @param \Illuminate\Database\Eloquent\Builder|\October\Rain\Database\Builder $obQuery
   * @return \Illuminate\Database\Eloquent\Builder|\October\Rain\Database\Builder;
   */
  public function scopeFilterable($obQuery)
  {
    return $obQuery->where($this->table . '.is_filterable', true);
  }

  /**
   * Get not filterable elements
   * @param \Illuminate\Database\Eloquent\Builder|\October\Rain\Database\Builder $obQuery
   * @return \Illuminate\Database\Eloquent\Builder|\October\Rain\Database\Builder;
   */
  public function scopeNotFilterable($obQuery)
  {
    return $obQuery->where($this->table . '.is_filterable', false);
  }
}
