<?php namespace MajorMedia\ToolBox\Behaviors;

/**
 * Class SearchableField
 * @package MajorMedia\Catalog\Traits
 * @author Abdessalam ZAHRAOUI, a.zahraoui@majormedia.com, MajorMedia
 *
 * @property bool $active
 *
 * @method static $this searchable()
 * @method static $this notSearchable()
 */
class SearchableField extends \October\Rain\Extension\ExtensionBase
{
    protected $model;

    public function __construct($model)
    {
        $this->model = $model;
    }

    /**
     * Get active elements
     * @param \Illuminate\Database\Eloquent\Builder|\October\Rain\Database\Builder $obQuery
     * @return \Illuminate\Database\Eloquent\Builder|\October\Rain\Database\Builder;
     */
    public function scopeSearchable($obQuery)
    {
        return $obQuery->where($this->model->table . '.is_searchable', true);
    }

    /**
     * Get not active elements
     * @param \Illuminate\Database\Eloquent\Builder|\October\Rain\Database\Builder $obQuery
     * @return \Illuminate\Database\Eloquent\Builder|\October\Rain\Database\Builder;
     */
    public function scopeNotSearchable($obQuery)
    {
        return $obQuery->where($this->model->table . '.is_searchable', false);
    }
}
