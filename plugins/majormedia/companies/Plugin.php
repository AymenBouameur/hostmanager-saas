<?php namespace MajorMedia\Companies;

use Backend;
use System\Classes\PluginBase;
use Backend\Models\User as BackendUser;
use MajorMedia\Listings\Models\Listing;

class Plugin extends PluginBase
{
    public $require = ['MajorMedia.ToolBox', 'MajorMedia.Listings'];

    public function pluginDetails()
    {
        return [
            'name'        => 'Companies',
            'description' => 'Host management companies for SaaS',
            'author'      => 'MajorMedia',
            'icon'        => 'icon-building'
        ];
    }

    public function boot()
    {
        // Extend BackendUser model with company relation
        BackendUser::extend(function ($model) {
            $model->belongsTo['company'] = [
                \MajorMedia\Companies\Models\Company::class,
                'key' => 'company_id'
            ];
        });

        // Extend Listing model with company relation
        Listing::extend(function ($model) {
            $model->belongsTo['company'] = [
                \MajorMedia\Companies\Models\Company::class,
                'key' => 'company_id'
            ];
        });
    }

    public function registerNavigation()
    {
        return [
            'companies' => [
                'label'       => 'Companies',
                'url'         => Backend::url('majormedia/companies/companies'),
                'icon'        => 'icon-building',
                'permissions' => ['majormedia.companies::companies.*'],
                'order'       => 300,
            ],
        ];
    }

    public function registerPermissions()
    {
        return [
            'majormedia.companies::companies.manage' => [
                'tab'   => 'Companies',
                'label' => 'Manage Companies'
            ],
        ];
    }
}
