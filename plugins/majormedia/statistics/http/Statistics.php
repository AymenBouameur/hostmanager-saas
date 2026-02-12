<?php
namespace Majormedia\Statistics\Http;

use Carbon\Carbon;
use Illuminate\Http\Request;
use RainLab\User\Models\User;
use Backend\Classes\Controller;
use Illuminate\Support\Facades\DB;
use MajorMedia\Bookings\Models\Booking;
use MajorMedia\Listings\Models\Listing;
use MajorMedia\ToolBox\Traits\JsonAbort;
/**
 * Statistics Back-end Controller
 */
class Statistics extends Controller
{
    use JsonAbort;
    public $implement = [
        'MajorMedia.ToolBox.Behaviors.RestController'
    ];

    public $restConfig = 'config_rest.yaml';

    public function overview()
    {

        $request = request();
        $user = $request->get('auth_user');

        $recentReservations = Booking::getRecentReservationsCount($user->id);
        $upcomingReservations = Booking::getUpcomingReservationsCount($user->id);
        $recentRevenue = (float) Booking::getRecentRevenue($user->id);
        $projectedRevenue = (float) Booking::getProjectedRevenue($user->id);

        return $this->JsonAbort([
            'status' => 'success',
            'data' => [
                'recent_reservations' => $recentReservations,
                'upcoming_reservations' => $upcomingReservations,
                'recent_revenue' => $recentRevenue,
                'projected_revenue' => $projectedRevenue,
            ]
        ], 200);
    }


    public function acquisitionChannels()
    {
        $request = request();
        $user = $request->get('auth_user');

        $channelLabels = [
            1 => Booking::LABEL_CANAL_1,
            2 => Booking::LABEL_CANAL_2,
            3 => Booking::LABEL_CANAL_3,
            4 => Booking::LABEL_CANAL_4,
        ];

        $startOfYear = Carbon::now()->startOfYear()->toDateString();
        $endOfYear = Carbon::now()->endOfYear()->toDateString();

        $listingIds = $user->listings()->pluck('listing_id');

        $data = Booking::query()
            ->select('canal', DB::raw('COUNT(*) as total'))
            ->where('is_canceled', 0)
            ->whereDate('check_out', '>=', $startOfYear)
            ->whereDate('check_out', '<=', $endOfYear)
            ->whereIn('listing_id', $listingIds)
            ->groupBy('canal')
            ->get()
            ->mapWithKeys(fn($item) => [$item->canal => $item->total]);

        $fullData = collect($channelLabels)->mapWithKeys(
            fn($label, $canal) => [$label => $data[$canal] ?? 0]
        );

        $total = $fullData->sum();

        return $this->JsonAbort([
            'status' => 'success',
            'data' => [
                'total_reservations' => $total,
                'channels' => $fullData,
            ]
        ], 200);
    }

    public function getLastDocumentsPerProperty()
    {
        $request = request();
        $user = $request->get('auth_user');
        $userId = $user->id;

        $listings = Listing::whereHas('users', function ($query) use ($userId) {
            $query->where('user_id', $userId);
        })->with('latestStatement')->get();
        $data = $listings->map(function ($listing) {
            $statement = $listing->latestStatement;

            return [
                'id' => $listing->id,
                'title' => $listing->title,
                'images_url' => $listing->images_url,
                'statement' => $statement ? [
                    'id' => $statement->id,
                    'statement_date' => $statement->statement_date,
                    'url' => $statement->url,
                ] : null,
            ];
        });

        return $this->JsonAbort([
            'success' => true,
            'data' => $data,
        ], 200);
    }

    public function getBookingsRevenue()
    {
        $user_id = request()->get('auth_user')->id;

        $start = Carbon::now()->startOfMonth();
        $months = collect(range(0, 6))->map(fn($i) => $start->copy()->addMonths($i));
        $labels = $months->map(fn($date) => ucfirst($date->locale('fr_FR')->isoFormat('MMM')))->toArray();

        $listings = Listing::whereHas('users', function ($query) use ($user_id) {
            $query->where('user_id', $user_id);
        })->get();
        $colors = $this->generateListingColors($listings);
        $datasets = [];

        foreach ($listings as $listing) {
            $monthlyProfits = $months->map(function ($date) use ($listing) {
                return Booking::where('is_canceled', 0)
                    ->where('listing_id', $listing->id)
                    ->whereBetween('check_out', [$date->copy()->startOfMonth(), $date->copy()->endOfMonth()])
                    ->sum('owner_profit');
            })->toArray();

            $datasets[] = [
                'label' => $listing->title ?? 'Logement ' . $listing->id,
                'data' => $monthlyProfits,
                'backgroundColor' => $colors[$listing->id],
                'borderRadius' => 15,
                'barPercentage' => 0.5,
                'categoryPercentage' => 0.6,
            ];
        }

        return response()->json([
            'status' => 'success',
            'labels' => $labels,
            'datasets' => $datasets,
        ]);
    }

    public function getBookingsCount()
    {
        $user_id = request()->get('auth_user')->id;

        $start = Carbon::now()->startOfMonth();
        $months = collect(range(0, 6))->map(fn($i) => $start->copy()->addMonths($i));
        $labels = $months->map(fn($date) => ucfirst($date->locale('fr_FR')->isoFormat('MMM')))->toArray();

        $listings = Listing::whereHas('users', function ($query) use ($user_id) {
            $query->where('user_id', $user_id);
        })->get();
        $colors = $this->generateListingColors($listings);
        $datasets = [];

        foreach ($listings as $listing) {
            $monthlyCounts = $months->map(function ($date) use ($listing) {
                return Booking::where('is_canceled', 0)
                    ->where('listing_id', $listing->id)
                    ->whereBetween('check_out', [$date->copy()->startOfMonth(), $date->copy()->endOfMonth()])
                    ->count();
            })->toArray();

            $datasets[] = [
                'label' => $listing->title ?? 'Logement ' . $listing->id,
                'data' => $monthlyCounts,
                'backgroundColor' => $colors[$listing->id],
                'borderRadius' => 15,
                'barPercentage' => 0.5,
                'categoryPercentage' => 0.6,
            ];
        }

        return response()->json([
            'status' => 'success',
            'labels' => $labels,
            'datasets' => $datasets,
        ]);
    }

    public function getBookingRevenuByMonths(Request $request)
    {
        $request = request();
        $user = $request->get('auth_user');
        $year = $request->get('year', Carbon::now()->year);

        $listingIds = Listing::whereHas('users', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })->pluck('id');

        $monthlyRevenues = array_fill(0, 12, null);

        $revenues = Booking::whereIn('listing_id', $listingIds)
            ->where('is_canceled', 0)
            ->whereYear('check_out', (int) $year)
            ->selectRaw('MONTH(check_out) as month, SUM(owner_profit) as total_revenue')
            ->groupBy('month')
            ->get();

        foreach ($revenues as $revenue) {
            $monthlyRevenues[$revenue->month - 1] = round((float) $revenue->total_revenue, 2);
        }

        $lastYearRevenue = Booking::whereIn('listing_id', $listingIds)
            ->where('is_canceled', 0)
            ->whereYear('check_out', (int) $year - 1)
            ->sum('owner_profit');

        $lastYearRevenue = $lastYearRevenue > 0 ? round((float) $lastYearRevenue, 2) : null;

        return response()->json([
            'status' => 'success',
            'data' => $monthlyRevenues,
            'last_year_revenue' => $lastYearRevenue,
            'year' => $year,
        ], 200, [], JSON_PRESERVE_ZERO_FRACTION);
    }

    private function generateListingColors($listings)
    {
        $total = count($listings);
        $colors = [];

        foreach ($listings as $index => $listing) {
            $hue = intval(($index * 360) / max($total, 1));
            $colors[$listing->id] = "hsl($hue, 60%, 50%)";
        }
        return $colors;
    }
}
