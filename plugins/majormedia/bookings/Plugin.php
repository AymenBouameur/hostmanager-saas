<?php
namespace MajorMedia\Bookings;

use System\Classes\PluginBase;
use Backend\Classes\Controller;
use MajorMedia\Listings\Models\Listing;
use MajorMedia\Listings\Controllers\Listings;
use MajorMedia\Bookings\Models\Booking;
use Event;
use Backend;
class Plugin extends PluginBase
{
  public $require = ['MajorMedia.Listings', 'MajorMedia.UserPlus'];

  public function pluginDetails()
  {
    return [
      'name' => 'Bookings',
      'description' => 'Manage event bookings.',
      'author' => 'MajorMedia',
      'icon' => 'icon-calendar'
    ];
  }

  public function registerComponents()
  {
  }

  public function registerSettings()
  {
  }

  public function boot()
  {

    $this->extendListingModel();
    $this->extendListingsConfigRelation();
    $this->extendListingsFormFields();

    Event::listen('backend.menu.extendItems', function ($manager) {
      $manager->addSideMenuItems('MajorMedia.Listings', 'listings', [
        'bookings' => [
          'label' => 'majormedia.bookings::lang.menu.bookings.label',
          'icon' => '',
          'url' => \Backend::url('majormedia/bookings/bookings'),
          'iconSvg' => '/plugins/majormedia/bookings/assets/icons/bookings.png'
        ]
      ]);
    });
  }

  private function extendListingModel()
  {
    Listing::extend(function ($model) {
      $model->hasMany['bookings'] = [Booking::class];
    });
  }

  protected function extendListingsConfigRelation()
  {
    Listings::extend(function (Controller $controller) {

      // Config_relation
      if (!in_array('Backend\Behaviors\RelationController', $controller->implement) && !in_array('Backend.Behaviors.RelationController', $controller->implement)) {
        $controller->implement[] = 'Backend\Behaviors\RelationController';
      }
      if (!isset($controller->relationConfig)) {
        $controller->addDynamicProperty('relationConfig');
      }
      $controller->relationConfig = $controller->mergeConfig(
        $controller->relationConfig,
        '$/majormedia/bookings/config/config_relation.yaml'
      );
    });
  }

  private function extendListingsFormFields()
  {
    Event::listen('backend.form.extendFields', function ($widget) {
      // Only for the Listings controller
      if (!$widget->getController() instanceof Listings) {
        return;
      }

      // Only for the Listing model
      if (!$widget->model instanceof Listing) {
        return;
      }
      $widget->addTabFields([
        'bookings' => [
          'type' => 'partial',
          'path' => "$/majormedia/bookings/controllers/bookings/_bookings.htm",
          'span' => 'full',
          'tab' => "Bookings"
        ],
      ]);
    });
  }
}
