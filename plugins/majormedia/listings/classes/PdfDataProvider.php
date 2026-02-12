<?php
namespace MajorMedia\Listings\Classes;

use Illuminate\Support\Facades\DB;
use Majormedia\InCore\Models\Settings;
use MajorMedia\Bookings\Models\Booking;
use MajorMedia\Listings\Models\Expense;
use MajorMedia\Listings\Models\Listing;

class PdfDataProvider
{
    public function getData($listingId, $startAt, $endAt)
    {
        if ($startAt && $endAt) {
            $startDate = $startAt;
            $endDate = $endAt;
            $startOfLastMonth = $startAt;
            $endOfLastMonth = $endAt;
        } else {
            $startDate = now()->subMonth()->startOfMonth()->format('d/m/Y');
            $endDate = now()->subMonth()->endOfMonth()->format('d/m/Y');
            $startOfLastMonth = now()->subMonth()->startOfMonth()->toDateString();
            $endOfLastMonth = now()->subMonth()->endOfMonth()->toDateString();
        }


        $listing = Listing::find($listingId);

        $bookings = Booking::where('listing_id', $listing->id)->where('status', 0)
            ->where(function ($query) use ($startOfLastMonth, $endOfLastMonth) {
                $query->whereBetween(DB::raw('DATE(check_in)'), [$startOfLastMonth, $endOfLastMonth])
                    ->orWhereBetween(DB::raw('DATE(check_out)'), [$startOfLastMonth, $endOfLastMonth]);
            })->orderBy('check_in')
            ->get();

        $expenses = Expense::where('listing_id', $listing->id)
            ->whereRaw('DATE(processed_at) >= ? AND DATE(processed_at) <= ?', [$startOfLastMonth, $endOfLastMonth])
            ->get();

        $overcome = Booking::where('listing_id', $listing->id)->where('status',0)
            ->where(function ($query) use ($startOfLastMonth, $endOfLastMonth) {
                $query->whereBetween(DB::raw('DATE(check_in)'), [$startOfLastMonth, $endOfLastMonth])
                    ->orWhereBetween(DB::raw('DATE(check_out)'), [$startOfLastMonth, $endOfLastMonth]);
            })
            ->sum('owner_profit');
        $overcome = number_format($overcome, 2, ',', ' ');

        $logoPath = asset('storage/app/media/logos/logo.png');

        return [
            'logoPath' => $logoPath,
            'listing' => $listing,
            'overcome' => $overcome,
            'bookings' => $bookings,
            'expenses' => $expenses,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'date' => now()->format('d/m/Y'),
        ];
    }

}
