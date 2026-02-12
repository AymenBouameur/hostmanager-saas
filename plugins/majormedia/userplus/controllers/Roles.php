<?php
namespace MajorMedia\UserPlus\Controllers;

use Backend\Classes\Controller;
use BackendMenu;

class Roles extends Controller
{
    public $implement = ['Backend\Behaviors\ListController', 'Backend\Behaviors\FormController'];

    public $listConfig = 'config_list.yaml';
    public $formConfig = 'config_form.yaml';

    public function __construct()
    {
        parent::__construct();
        // integrate with rainlab menu
        BackendMenu::setContext('RainLab.User', 'user', 'roles');
    }
}
