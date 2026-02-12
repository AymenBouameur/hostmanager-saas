<?php namespace MajorMedia\ToolBox\Traits;

/**
 * Class PinnedField
 * @package MajorMedia\ToolBox\Traits
 * @author Abdessalam ZAHRAOUI, a.zahraoui@majormedia.com, MajorMedia
 *
 * @property bool $pinned
 *
 * @method static $this pinned()
 * @method static $this notPinned()
 */
trait PinnedField
{

  /**
   * Get active elements
   * @param \Illuminate\Database\Eloquent\Builder|\October\Rain\Database\Builder $obQuery
   * @return \Illuminate\Database\Eloquent\Builder|\October\Rain\Database\Builder;
   */
  public function scopePinned($obQuery)
  {
    return $obQuery->where($this->table . '.is_pinned', true);
  }

  /**
   * Get not active elements
   * @param \Illuminate\Database\Eloquent\Builder|\October\Rain\Database\Builder $obQuery
   * @return \Illuminate\Database\Eloquent\Builder|\October\Rain\Database\Builder;
   */
  public function scopeNotPinned($obQuery)
  {
    return $obQuery->where($this->table . '.is_pinned', false);
  }
}