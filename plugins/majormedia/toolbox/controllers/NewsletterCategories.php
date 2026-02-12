<?php namespace MajorMedia\ToolBox\Controllers;

use BackendMenu;
use Backend\Classes\Controller;

/**
 * Newsletter Categories Back-end Controller
 */
class NewsletterCategories extends Controller
{
    /**
     * @var array Behaviors that are implemented by this controller.
     */
    public $implement = [
        'Backend.Behaviors.FormController',
        'Backend.Behaviors.ListController'
    ];

    /**
     * @var string Configuration file for the `FormController` behavior.
     */
    public $formConfig = 'config_form.yaml';

    /**
     * @var string Configuration file for the `ListController` behavior.
     */
    public $listConfig = 'config_list.yaml';

    public function __construct()
    {
        parent::__construct();

        BackendMenu::setContext('MajorMedia.ToolBox', 'main-menu-newsletters', 'side-menu-newslettercategories');
    }
}
