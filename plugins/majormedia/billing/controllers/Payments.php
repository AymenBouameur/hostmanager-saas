<?php namespace MajorMedia\Billing\Controllers;

use BackendMenu;
use Backend\Classes\Controller;

class Payments extends Controller
{
    public $implement = [
        \Backend\Behaviors\FormController::class,
        \Backend\Behaviors\ListController::class,
    ];

    public $requiredPermissions = ['majormedia.billing::payments.manage'];

    public $formConfig = 'config_form.yaml';
    public $listConfig = 'config_list.yaml';

    public function __construct()
    {
        parent::__construct();
        BackendMenu::setContext('MajorMedia.Billing', 'billing', 'payments');
    }
}
