<?php namespace MajorMedia\ToolBox\Components;

use Cms\Classes\ComponentBase;
use Event;
use MajorMedia\ToolBox\Models\Country;

class Countries extends ComponentBase
{
  public function componentDetails()
  {
    return [
      'name' => 'Countries Component',
      'description' => 'No description provided yet...'
    ];
  }

  public function defineProperties()
  {
    return [
      'fieldname' => [
        'title' => "Nom du champ",
        'description' => "Nom du champ exemple 'country_id'",
        'type' => 'string',
        'default' => 'country_id',
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
      'dependsOnCities' => [
        'title' => "Dépendre des villes ?",
        'description' => "Changer les villes sur chaque changement du Pays ?",
        'type' => 'checkbox',
        'default' => Event::fire('majormedia.toolbox::changeProperty.CountriesDP.DependsOnCities', [], true) ?? true,
      ],
    ];
  }

  public function getSelectedCountryIdOptions()
  {
    return Country::active()->ordered()->get();
  }

  public function getCountries()
  {
    return Country::active()->ordered()->get();
  }

  public function onChangeCountry()
  {
    $states = [];
    if (($country_id = post('country_id')) && ($country = Country::with('states_active_ordered.cities_active_ordered')->whereId($country_id)->active()->first())) {
      $states = $country->states_active_ordered;
    }
    return [
      '#citiesDP' . post('formKey') => $this->renderPartial('citiesDP::default', ['states' => $states])
    ];
  }
}
