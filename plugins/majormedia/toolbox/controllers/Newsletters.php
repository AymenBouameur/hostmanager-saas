<?php

namespace MajorMedia\ToolBox\Controllers;

use Backend\Classes\Controller;
use BackendMenu;

class Newsletters extends Controller {

    public $implement = ['Backend\Behaviors\ListController', 'Backend\Behaviors\FormController', 'Backend\Behaviors\ReorderController',
        'Backend\Behaviors\RelationController'];
    public $listConfig = 'config_list.yaml';
    public $formConfig = 'config_form.yaml';
    public $reorderConfig = 'config_reorder.yaml';
    public $relationConfig = 'config_relation.yaml';

    public function __construct() {
        parent::__construct();
        BackendMenu::setContext('MajorMedia.ToolBox', 'main-menu-newsletters', 'side-menu-newsletters');
    }

    public function formExtendFields($form)
    {
      $form->addTabFields([
        'newsletter_categories' => [
          'type' => 'partial',
          'path' => "$/majormedia/toolbox/controllers/newslettercategories/_newsletter_categories.htm",
          'span' => 'full',
          'tab' => 'Catégories'
        ],
      ]);
    }

}
