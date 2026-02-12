<?php
namespace MajorMedia\Listings;

use Event;
use RainLab\User\Models\User;
use System\Classes\PluginBase;
use October\Rain\Database\Model;
use Illuminate\Support\Facades\Log;
use MajorMedia\Listings\Models\Listing;
use Majormedia\Listings\Console\GeneratePdfViaCommand;
use RainLab\User\Controllers\Users as UsersController;
use Majormedia\Listings\Console\GenerateInvoiceViaCommand;
use Majormedia\Listings\Console\GenerateTestingPdfCommand;
use Majormedia\Listings\Console\GenerateListingPdfViaRoute;


/**
 * Plugin class
 */
class Plugin extends PluginBase
{
    public $require = ['MajorMedia.ToolBox', 'MajorMedia.UserPlus'];
    /**
     * register method, called when the plugin is first registered.
     */
    public function register()
    {
        $this->registerConsoleCommand('generate:listing-pdf-via-route', GenerateListingPdfViaRoute::class);
        $this->registerConsoleCommand('listings:generatepdfviacommand', GeneratePdfViaCommand::class);
        $this->registerConsoleCommand('listings:generateinvoiceviacommand', GenerateInvoiceViaCommand::class);
        $this->registerConsoleCommand('listings:generateTestingPdf', GenerateTestingPdfCommand::class);

    }

    public function registerSchedule($schedule)
    {

        $schedule->command('listings:generatepdfviacommand')
            ->dailyAt('02:00')
            ->appendOutputTo(storage_path('logs/statementEachDay.log'))
            ->onFailure(fn() => Log::error('Fetch Listing generate statementOnEachDay failed.'));

        $schedule->command('listings:generatepdfviacommand')
            ->monthlyOn(5, '00:00')
            ->appendOutputTo(storage_path('logs/statementOnDay5.log'))
            ->onFailure(fn() => Log::error('Fetch Listing generate statementOnDay5 failed.'));


        $schedule->command('listings:generateinvoiceviacommand')
            ->monthlyOn(5, '00:00')
            ->appendOutputTo(storage_path('logs/invoices.log'))
            ->onFailure(fn() => Log::error('Fetch Listing generate in invoices failed.'));

    }

    public function boot()
    {
        $this->extendListingModel();
        $this->extendUserModel();
        $this->extendUserFormFields();

        Event::listen('majormedia.listings::extendListingHidden', function () {
            Listing::extend(function (Model $model) {
                $model->hidden = is_array($model->hidden) ? $model->hidden : [];
                $model->hidden = array_merge($model->hidden, ['address', 'description', 'images_url', 'availability']);
            });
        });
    }

    public function registerComponents()
    {
    }

    public function registerSettings()
    {
    }

    public function extendUserModel()
    {
        User::extend(function ($model) {
            // $model->hasMany['listings'] = [Listing::class,];
            $model->belongsToMany['listings'] = [
                Listing::class,
                'table' => 'majormedia_listings_user_listings',
                'timestamps' => true,
            ];
        });

    }
    public function extendListingModel()
    {
        Listing::extend(function ($model) {
            $model->belongsTo['user'] = [User::class];
            $model->belongsToMany['users'] = [
                User::class,
                'table' => 'majormedia_listings_user_listings',
                'timestamps' => true,
            ];
        });
    }

    private function extendUserFormFields()
    {
        Event::listen('backend.form.extendFields', function ($widget) {
            // Only for the Users controller
            if (!$widget->getController() instanceof UsersController) {
                return;
            }

            // Only for the User model
            if (!$widget->model instanceof User) {
                return;
            }

            if ($widget->model) {
                $widget->addTabFields([
                    'listings' => [
                        'label' => 'Biens Immobiliers',
                        'type' => 'partial',
                        'path' => '$/majormedia/listings/controllers/listings/_listings.htm',

                        'tab' => 'Gestion des Biens'
                    ],

                ]);
            }

        });
    }



//     public function registerPermissions()
// {
//     return [
//         'majormedia.listings.access_listings' => [
//             'tab' => 'Listings',
//             'label' => 'Accès aux biens immobiliers'
//         ],
//         'majormedia.listings.manage_invoices' => [
//             'tab' => 'Listings',
//             'label' => 'Générer et gérer les factures'
//         ],
//         'majormedia.listings.manage_statements' => [
//             'tab' => 'Listings',
//             'label' => 'Générer et gérer les relevés'
//         ],
//     ];
// }

}
