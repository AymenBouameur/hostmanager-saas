<?php namespace MajorMedia\Billing\Controllers;

use BackendMenu;
use Backend\Classes\Controller;

class PricingPlans extends Controller
{
    public $implement = [
        \Backend\Behaviors\FormController::class,
        \Backend\Behaviors\ListController::class,
    ];

    public $requiredPermissions = ['majormedia.billing::pricingplans.manage'];

    public $formConfig = 'config_form.yaml';
    public $listConfig = 'config_list.yaml';

    public function __construct()
    {
        parent::__construct();
        BackendMenu::setContext('MajorMedia.Billing', 'billing', 'pricingplans');
    }
}
