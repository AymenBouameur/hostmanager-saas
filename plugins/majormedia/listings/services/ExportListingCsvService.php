<?php
namespace MajorMedia\Listings\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use MajorMedia\Listings\Models\Expense;
use MajorMedia\Listings\Models\Listing;

class ExportListingCsvService
{
    protected $month;
    protected $listingIds;
    protected $year;

    public function __construct(int $year, int $month, array $listingIds)
    {
        if ($month < 1 || $month > 12) {
            throw new \InvalidArgumentException('Mois invalide.');
        }
        
        $this->year = $year;
        $this->month = $month;
        $this->listingIds = array_map('intval', $listingIds);
    }

    public function generateCsv(): string
    {
        $startDate = Carbon::create($this->year, $this->month, 1)->startOfMonth();
        $endDate = (clone $startDate)->endOfMonth();

        Log::info('Date range for export', [
            'start' => $startDate->toDateString(),
            'end' => $endDate->toDateString()
        ]);
        
        if (count($this->listingIds)) {
            $listings = Listing::with([
                'bookings' => function ($query) use ($startDate, $endDate) {
                    $query->whereBetween('check_out', [$startDate, $endDate])
                        ->where('is_canceled', 0);
                }
            ])->whereIn('id', $this->listingIds)->get();
        } else {
            $listings = Listing::with([
                'bookings' => function ($query) use ($startDate, $endDate) {
                    $query->whereBetween('check_out', [$startDate, $endDate])
                        ->where('is_canceled', 0);
                },
                'expenses' => function ($query) use ($startDate, $endDate) {
                    $query->whereBetween('date', [$startDate, $endDate]);
                }
            ])->get();
        }
        

        Log::info('Listings fetched', ['count' => $listings->count()]);

        $csv = "Shortname,Titre,Gain proprietaire,Commission Vacaloc,Commission OTA,Somme des Taxes,Somme Menages,ToTal Brut\n";

        foreach ($listings as $listing) {
            if ($listing->bookings->isEmpty()) {
                Log::info('No bookings for listing', ['shortName' => $listing->shortName]);
            }

            $totals = [
                'owner_profit' => 0,
                'total_expenses' => 0, // Assuming this is the total expenses to be calculated
                'commission_vacaloc' => 0,
                'commission_ota' => 0,
                'tax' => 0,
                'cleaning_fee' => 0,
                'gross_amount' => 0,
            ];

            $totals['total_expenses'] = Expense::where('listing_id', $listing->id)
                ->whereBetween(DB::raw('DATE(processed_at)'), [$startDate, $endDate])
                ->sum('amount') ?? 0;


            foreach ($listing->bookings as $booking) {
                $totals['owner_profit'] += $booking->owner_profit ?? 0;
                $totals['commission_vacaloc'] += $booking->vacaloc_commission ?? 0;
                $totals['commission_ota'] += $booking->ota_commission ?? 0;
                $totals['tax'] += $booking->tax ?? 0;
                $totals['cleaning_fee'] += $booking->clearning_fee ?? 0;
                $totals['gross_amount'] += $booking->gross_amount ?? 0;
            }

            $csv .= '"' . $listing->shortName . '",';
            $csv .= '"' . $listing->title . '",';
            $csv .= '"' . number_format(($totals['owner_profit'] - $totals['total_expenses']), 2) . '",';
            $csv .= '"' . number_format($totals['commission_vacaloc'], 2) . '",';
            $csv .= '"' . number_format($totals['commission_ota'], 2) . '",';
            $csv .= '"' . number_format($totals['tax'], 2) . '",';
            $csv .= '"' . number_format($totals['cleaning_fee'], 2) . '",';
            $csv .= '"' . number_format($totals['gross_amount'], 2) . "\"\n";

            Log::info('Exported listing', ['shortName' => $listing->shortName, 'totals' => $totals]);
        }

        return $csv;
    }
}
