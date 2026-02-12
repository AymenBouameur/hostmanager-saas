<?php namespace MajorMedia\ToolBox\Traits;

/**
 * Class WithWhereHasScope
 * @package MajorMedia\Catalog\Traits
 * @author Abdessalam ZAHRAOUI, a.zahraoui@majormedia.com, MajorMedia
 *
 * @method static $this withWhereHas($relation, $constraint)
 */
trait WithWhereHasScope
{
  /**
   * Scope with whereHas
   * @param \Illuminate\Database\Eloquent\Builder|\October\Rain\Database\Builder $query
   * @param String $relation
   * @param \Closure $constraint
   * @return \Illuminate\Database\Eloquent\Builder|\October\Rain\Database\Builder;
   */
  public function scopeWithWhereHas($query, String $relation, \Closure $constraint)
  {
    return $query->whereHas($relation, $constraint)->with([$relation => $constraint]);
  }
}
