<?php namespace MajorMedia\ToolBox\Components;

use Cms\Classes\ComponentBase;
use MajorMedia\ToolBox\Models\Country;
use MajorMedia\ToolBox\Models\State;

class Cities extends ComponentBase
{
  public function componentDetails()
  {
    return [
      'name' => 'Cities Component',
      'description' => 'No description provided yet...'
    ];
  }

  public function defineProperties()
  {
    return [
      'fieldname' => [
        'title' => "Nom du champ",
        'description' => "Nom du champ exemple 'city_id'",
        'type' => 'string',
        'default' => 'city_id',
      ],
      'ajax' => [
        'title' => "Requête AJAX ?",
        'description' => "Type de requête AJAX ?",
        'type' => 'checkbox',
        'default' => false,
      ],
      'required' => [
        'title' => "Champ obligatoire ?",
        'description' => "Ce champ est obligatoire ?",
        'type' => 'checkbox',
        'default' => false,
      ],
      'show_zipcode' => [
        'title' => "Codes postaux ?",
        'description' => "Afficher les codes postaux ?",
        'type' => 'checkbox',
        'default' => false,
      ],
      'selected_country_id' => [
        'title' => "Pays par défaut",
        'description' => "Selection par défaut",
        'type' => 'dropdown',
        'default' => '0',
      ],
    ];
  }

  public function getSelectedCountryIdOptions()
  {
    return Country::active()->ordered()->get();
  }

  public function getStates()
  {
    if (($country_id = $this->page['selected_country_id'])) {
      $query = State::with('cities_active_ordered')->active()->ordered();
      $query->whereCountryId($country_id);
      return $query->get();
    }
    return [];
  }

  /*public function items()
  {
    return City::with('state.country')->active()->ordered()->get();
  }*/
}
