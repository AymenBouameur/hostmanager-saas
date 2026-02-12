<?php namespace MajorMedia\Billing\Controllers;

use BackendMenu;
use Backend\Classes\Controller;

class Subscriptions extends Controller
{
    public $implement = [
        \Backend\Behaviors\FormController::class,
        \Backend\Behaviors\ListController::class,
        \Backend\Behaviors\RelationController::class,
    ];

    public $requiredPermissions = ['majormedia.billing::subscriptions.manage'];

    public $formConfig = 'config_form.yaml';
    public $listConfig = 'config_list.yaml';
    public $relationConfig = 'config_relation.yaml';

    public function __construct()
    {
        parent::__construct();
        BackendMenu::setContext('MajorMedia.Billing', 'billing', 'subscriptions');
    }
}
