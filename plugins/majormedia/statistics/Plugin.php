<?php
namespace MajorMedia\Statistics;

use Backend;
use System\Classes\PluginBase;

/**
 * Plugin Information File
 *
 * @link https://docs.octobercms.com/3.x/extend/system/plugins.html
 */
class Plugin extends PluginBase
{
    /**
     * pluginDetails about this plugin.
     */
    public function pluginDetails()
    {
        return [
            'name' => 'Statistics',
            'description' => 'No description provided yet...',
            'author' => 'MajorMedia',
            'icon' => 'icon-leaf'
        ];
    }

    /**
     * register method, called when the plugin is first registered.
     */
    public function register()
    {
        //
    }

    /**
     * boot method, called right before the request route.
     */
    public function boot()
    {

    }

    /**
     * registerComponents used by the frontend.
     */
    public function registerComponents()
    {
        return []; // Remove this line to activate

        return [
            'MajorMedia\Statistics\Components\MyComponent' => 'myComponent',
        ];
    }

    /**
     * registerPermissions used by the backend.
     */
    public function registerPermissions()
    {
        return []; // Remove this line to activate

        return [
            'majormedia.statistics.some_permission' => [
                'tab' => 'Statistics',
                'label' => 'Some permission'
            ],
        ];
    }

    /**
     * registerNavigation used by the backend.
     */
    public function registerNavigation()
    {
        return []; // Remove this line to activate

        return [
            'statistics' => [
                'label' => 'Statistics',
                'url' => Backend::url('majormedia/statistics/mycontroller'),
                'icon' => 'icon-leaf',
                'permissions' => ['majormedia.statistics.*'],
                'order' => 500,
            ],
        ];
    }
}
