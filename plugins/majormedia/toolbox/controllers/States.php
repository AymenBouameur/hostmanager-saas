<?php namespace MajorMedia\ToolBox\Controllers;

use Backend\Classes\Controller;
use BackendMenu;

class States extends Controller
{
    public $implement = ['Backend\Behaviors\ListController', 'Backend\Behaviors\FormController', 'Backend\Behaviors\RelationController'];

    public $listConfig = 'config_list.yaml';
    public $formConfig = 'config_form.yaml';
    public $relationConfig = 'config_relation.yaml';

    public function __construct()
    {
        parent::__construct();
        BackendMenu::setContext('MajorMedia.ToolBox', 'main-menu-dictionary', 'side-menu-states');
    }

    public function formExtendFields($form)
    {
        $form->addTabFields([
            'cities' => [
                'type' => 'partial',
                'path' => "$/majormedia/toolbox/controllers/cities/_cities.htm",
                'span' => 'full',
                'tab' => "Villes"
            ]
        ]);
    }
}
