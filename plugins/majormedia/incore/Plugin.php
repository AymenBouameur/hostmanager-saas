<?php
namespace Majormedia\InCore;

use Event;
use System\Classes\PluginBase;
use MajorMedia\InCore\Models\Settings;
use Majormedia\InCore\Components\Extras;

class Plugin extends PluginBase
{
  public $require = ['Majormedia.ToolBox'];

  public function registerComponents()
  {
    return [
      Extras::class => 'extras',
    ];
  }

  public function boot()
  {
    Event::listen('backend.form.extendFields', function ($widget) {

      // Ensure it's the correct model
      if (!$widget->model instanceof Settings) {
        return;
      }
      
      $widget->addFields([
        'vacaloc_commission' => [
          'label' => 'Commission Vacaloc',
          'type' => 'number',
          'span' => 'left',
          'comment' => 'Indiquez le pourcentage de commission appliqué sur chaque réservation via Vacaloc.',
          'tab' => 'Commissions',
        ],
        'airbnb_commission' => [
          'label' => 'Commission Airbnb',
          'type' => 'number',
          'span' => 'right',
          'comment' => 'Indiquez le pourcentage de commission appliqué sur chaque réservation via Airbnb.',
          'tab' => 'Commissions',
        ],
        'vrbo_commission' => [
          'label' => 'Commission VRBO',
          'type' => 'number',
          'span' => 'left',
          'comment' => 'Indiquez le pourcentage de commission appliqué sur chaque réservation via VRBO.',
          'tab' => 'Commissions',
        ],
        'booking_commission' => [
          'label' => 'Commission Booking.com',
          'type' => 'number',
          'span' => 'right',
          'comment' => 'Indiquez le pourcentage de commission appliqué sur chaque réservation via Booking.com.',
          'tab' => 'Commissions',
        ],
        'tva' => [
          'label' => 'TVA',
          'type' => 'number',
          'span' => 'right',
          'comment' => 'Indiquez le pourcentage de TVA appliqué sur chaque réservation.',
          'tab' => 'Commissions',
        ],
      ]);

    });
  }

  public function registerSettings()
  {
    return [
      'settings' => [
        'label' => "Extras",
        'description' => "Extras Settings",
        'icon' => 'icon-mobile',
        'class' => Models\Settings::class,
        'order' => 500,
        'keywords' => ''
      ]
    ];
  }
  public function registerMailTemplates()
  {
    return [
      'majormedia.incore::mails.otp_verification',
    ];
  }
}
