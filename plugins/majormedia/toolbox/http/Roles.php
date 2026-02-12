<?php namespace MajorMedia\ToolBox\Http;

use Backend\Classes\Controller;

/**
 * Roles Back-end Controller
 */
class Roles extends Controller
{
    public $implement = [
        'MajorMedia.ToolBox.Behaviors.RestController'
    ];

    public $restConfig = 'config_rest.yaml';

}
