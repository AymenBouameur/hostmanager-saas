<?php

namespace Majormedia\Dashboard;

use Backend;
use System\Classes\PluginBase;

/**
 * Dashboard Plugin - Statistics & Analytics for Vacaloc
 */
class Plugin extends PluginBase
{
    /**
     * Plugin dependencies
     */
    public $require = [
        'RainLab.User',
        'MajorMedia.Listings',
        'MajorMedia.Bookings',
    ];

    /**
     * Plugin details
     */
    public function pluginDetails()
    {
        return [
            'name'        => 'Dashboard Statistiques',
            'description' => 'Tableau de bord analytique pour Vacaloc',
            'author'      => 'Majormedia',
            'icon'        => 'icon-line-chart'
        ];
    }

    /**
     * Register method
     */
    public function register()
    {
        //
    }

    /**
     * Boot method
     */
    public function boot()
    {
        //
    }

    /**
     * Register backend navigation
     */
    public function registerNavigation()
    {
        return [
            'dashboard' => [
                'label'       => 'Dashboard Stats',
                'url'         => Backend::url('majormedia/dashboard/dashboard'),
                'icon'        => 'icon-line-chart',
                'permissions' => ['majormedia.dashboard.*'],
                'order'       => 100,
                'sideMenu' => [
                    'overview' => [
                        'label'       => 'Vue d\'ensemble',
                        'icon'        => 'icon-dashboard',
                        'url'         => Backend::url('majormedia/dashboard/dashboard'),
                        'permissions' => ['majormedia.dashboard.access'],
                    ],
                ]
            ]
        ];
    }

    /**
     * Register permissions
     */
    public function registerPermissions()
    {
        return [
            'majormedia.dashboard.access' => [
                'tab'   => 'Dashboard',
                'label' => 'Accès au tableau de bord statistiques'
            ],
        ];
    }
}
