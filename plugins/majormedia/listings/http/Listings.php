<?php
namespace Majormedia\Listings\Http;

use Carbon\Carbon;
use Backend\Classes\Controller;
use MajorMedia\Bookings\Models\Booking;
use MajorMedia\ToolBox\Traits\RetrieveUser;
use Event;
/**
 * Listings Back-end Controller
 */
class Listings extends Controller
{
    use RetrieveUser;
    public $implement = [
        'MajorMedia.ToolBox.Behaviors.RestController'
    ];

    public $restConfig = 'config_rest.yaml';

    public function extendModel($model)
    {
        $this->retrieveUser();
        $model = $model->whereHas('users', function ($query) {
            $query->where('user_id', $this->user->id);
        });
        if (request()->has('availability')) {
            $availability = request()->get('availability');

            if ($availability == 1) {
                $model = $model->whereDoesntHave('bookings', function ($query) {
                    $today = Carbon::today();
                    $query->where('is_canceled', 0)
                        ->where(function ($query) use ($today) {
                            $query->where('check_in', '<=', $today)
                                ->where('check_out', '>=', $today);
                        });
                });
            } elseif ($availability == 0) {
                $model = $model->whereHas('bookings', function ($query) {
                    $today = Carbon::today();
                    $query->where('is_canceled', 0)
                        ->where(function ($query) use ($today) {
                            $query->where('check_in', '<=', $today)
                                ->where('check_out', '>=', $today);
                        });
                });
            }
        }
        if (request()->has('minView')) {
            Event::fire('majormedia.listings::extendListingHidden', true);
        }
        return $model;
    }

}
