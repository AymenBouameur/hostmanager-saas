<?php
namespace MajorMedia\Bookings\Http;

use Carbon\Carbon;
use Backend\Classes\Controller;
use MajorMedia\ToolBox\Traits\RetrieveUser;
use Event;
/**
 * Bookings Back-end Controller
 */
class Bookings extends Controller
{
    use RetrieveUser;
    public $implement = [
        'MajorMedia.ToolBox.Behaviors.RestController'
    ];

    public $restConfig = 'config_rest.yaml';

    public function extendModel($model, $recordId = null)
    {
        $this->retrieveUser();

        if ($recordId == null) {
            Event::fire('majormedia.bookings::hide&showBookingsAttributes', 1);
        }

        $model = $model->when(request()->has('canal'), function ($query) {
            return $query->where('canal', request('canal'));
        });

        $model = $model->when(!empty($term = request('term')), function ($query) use ($term) {
            $query->where(function ($subQuery) use ($term) {
                $subQuery->where('customer', 'like', '%' . $term . '%')
                    ->orWhereHas('listing', function ($listingQuery) use ($term) {
                        $listingQuery->where('title', 'like', '%' . $term . '%');
                    });
            });
        });

        $model = $model->when(request()->has('from') && request()->has('to'), function ($query) {
            $from = Carbon::createFromTimestamp(request('from'));
            $to = Carbon::createFromTimestamp(request('to'));
            return $query->whereBetween('check_out', [$from, $to]);
        });

        $model = $model->when(request()->has('listing'), function ($query) {
            $query->whereHas('listing', function ($query) {
                return $query->where('id', request('listing'));
            });
        });

        $model = $model->whereHas('listing', function ($query) {
            $query->whereHas('users', function ($query) {
                $query->where('user_id', $this->user->id);
            });
        });

        return $model->where('is_canceled', 0)->orderBy('check_in', 'desc');
    }


}
