<?php
namespace MajorMedia\Bookings\Controllers;

use Backend;
use BackendMenu;
use Backend\Classes\Controller;
use Illuminate\Support\Facades\Artisan;
use MajorMedia\Listings\Models\Listing;
use October\Rain\Support\Facades\Flash;
set_time_limit(300);
class Bookings extends Controller
{
    public $implement = [
        \Backend\Behaviors\FormController::class,
        \Backend\Behaviors\ListController::class
    ];
    public $formConfig = 'config_form.yaml';
    public $listConfig = 'config_list.yaml';

    public function __construct()
    {
        parent::__construct();

        BackendMenu::setContext('MajorMedia.Bookings', 'bookings', 'bookings');
    }
    public function onShowSyncBookingsModal()
    {
        $listings = Listing::all();
        $this->vars['listings'] = $listings;
        return $this->makePartial('sync_bookings_modal');
    }


    public function onSyncBookings()
    {
        try {
            $month = post('month');
            $listingId = post('listing_id');

            \Log::info('Sync bookings parameters', [
                'month' => $month,
                'listing_id' => $listingId
            ]);

            $params = [];

            if ($month) {
                $params['month'] = $month;
            }

            if ($listingId) {
                $params['listingId'] = $listingId;
            }

            if (!empty($params)) {
                Artisan::call('fetch:eviivo-bookings', $params);
            } else {
                Artisan::call('fetch:eviivo-bookings');
            }

            Flash::success('Synchronisation des réservations réussie.');

        } catch (\Exception $e) {
            \Log::error('Sync bookings error', ['error' => $e->getMessage()]);
            Flash::error('Erreur lors de la synchronisation: ' . $e->getMessage());
        }

        return \Redirect::to(\Backend::url('majormedia/bookings/bookings'));
    }



}
