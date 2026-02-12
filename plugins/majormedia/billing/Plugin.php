<?php namespace MajorMedia\Billing;

use Backend;
use System\Classes\PluginBase;
use MajorMedia\Companies\Models\Company;

class Plugin extends PluginBase
{
    public $require = ['MajorMedia.Companies'];

    public function pluginDetails()
    {
        return [
            'name'        => 'Billing',
            'description' => 'SaaS billing: pricing plans, subscriptions & payments',
            'author'      => 'MajorMedia',
            'icon'        => 'icon-credit-card'
        ];
    }

    public function boot()
    {
        // Extend Company model with billing relations
        Company::extend(function ($model) {
            $model->hasMany['subscriptions'] = [
                \MajorMedia\Billing\Models\Subscription::class,
                'key' => 'company_id'
            ];
            $model->hasMany['payment_methods'] = [
                \MajorMedia\Billing\Models\PaymentMethod::class,
                'key' => 'company_id'
            ];
            $model->hasMany['payments'] = [
                \MajorMedia\Billing\Models\Payment::class,
                'key' => 'company_id'
            ];
        });
    }

    public function registerNavigation()
    {
        return [
            'billing' => [
                'label'       => 'Billing',
                'url'         => Backend::url('majormedia/billing/pricingplans'),
                'icon'        => 'icon-credit-card',
                'permissions' => ['majormedia.billing::*'],
                'order'       => 310,
                'sideMenu'    => [
                    'pricingplans' => [
                        'label' => 'Pricing Plans',
                        'url'   => Backend::url('majormedia/billing/pricingplans'),
                        'icon'  => 'icon-tags',
                    ],
                    'subscriptions' => [
                        'label' => 'Subscriptions',
                        'url'   => Backend::url('majormedia/billing/subscriptions'),
                        'icon'  => 'icon-refresh',
                    ],
                    'paymentmethods' => [
                        'label' => 'Payment Methods',
                        'url'   => Backend::url('majormedia/billing/paymentmethods'),
                        'icon'  => 'icon-bank',
                    ],
                    'payments' => [
                        'label' => 'Payments',
                        'url'   => Backend::url('majormedia/billing/payments'),
                        'icon'  => 'icon-money',
                    ],
                ],
            ],
        ];
    }

    public function registerPermissions()
    {
        return [
            'majormedia.billing::pricingplans.manage' => [
                'tab'   => 'Billing',
                'label' => 'Manage Pricing Plans'
            ],
            'majormedia.billing::subscriptions.manage' => [
                'tab'   => 'Billing',
                'label' => 'Manage Subscriptions'
            ],
            'majormedia.billing::paymentmethods.manage' => [
                'tab'   => 'Billing',
                'label' => 'Manage Payment Methods'
            ],
            'majormedia.billing::payments.manage' => [
                'tab'   => 'Billing',
                'label' => 'Manage Payments'
            ],
        ];
    }
}
