<?php
namespace Majormedia\Listings\Console;

use Carbon\Carbon;
use System\Models\File;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf as PDF;
use MajorMedia\Bookings\Models\Booking;
use MajorMedia\Listings\Models\Expense;
use MajorMedia\Listings\Models\Listing;
use MajorMedia\Listings\Models\Statement;

/**
 * GeneratePdfViaCommand Command
 *
 * @link https://docs.octobercms.com/3.x/extend/console-commands.html
 */
class GeneratePdfViaCommand extends Command
{
    protected $signature = 'listings:generatepdfviacommand {mode?} {listingId?} {month?}';
    protected $description = 'Generate a PDF report for each listing, showing data for each month in the current year.';
    public function handle()
    {
        $mode = $this->argument('mode');
        $listingId = $this->argument('listingId');
        $monthArg = $this->argument('month');
        \Log::info('start generating statements');

        if (!empty($listingId)) {
            $listings = Listing::where('id', $listingId)->get();
        } else {
            $listings = Listing::all();
        }

        if ($listings->isEmpty()) {
            $this->warn('No listings found.');
            return;
        }

        $now = now();

        // Determine months to process
        if ($monthArg !== null && is_numeric($monthArg) && $monthArg >= 1 && $monthArg <= 12) {
            // If a specific month was provided and valid, generate only for that month
            $monthsToGenerate = [$monthArg];
            $this->info("Generating PDF report for month: {$monthArg}");
        } else {
            // Otherwise, use existing logic for range of months based on mode
            $startMonth = $mode === 'all' ? 1 : $now->copy()->subMonth()->month;
            $endMonth = $mode === 'all' ? $now->month : $now->copy()->subMonth()->month;
            $monthsToGenerate = range($startMonth, $endMonth);
            $this->info("Generating PDF reports for listings from month {$startMonth} to {$endMonth}...");
        }

        foreach ($listings as $listing) {
            foreach ($monthsToGenerate as $month) {
                $startOfMonth = Carbon::createFromDate($now->year, $month, 1)->startOfMonth();
                $endOfMonth = $startOfMonth->copy()->endOfMonth();

                // Bookings
                $bookings = Booking::where('listing_id', $listing->id)
                    ->where('is_canceled', 0)
                    ->whereBetween(DB::raw('DATE(check_out)'), [$startOfMonth, $endOfMonth])
                    ->orderBy('check_in')
                    ->get();

                // Expenses
                $expenses = Expense::where('listing_id', $listing->id)
                    ->whereBetween(DB::raw('DATE(processed_at)'), [$startOfMonth, $endOfMonth])
                    ->get();

                // Owner profit
                $overcome = Booking::where('listing_id', $listing->id)
                    ->where('is_canceled', 0)
                    ->whereBetween(DB::raw('DATE(check_out)'), [$startOfMonth, $endOfMonth])
                    ->sum('owner_profit');

                $overcomeFormatted = number_format($overcome, 2, ',', ' ');

                $logoPath = \Media\Classes\MediaLibrary::url('logos/logo.png');
                $data = [
                    'listing' => $listing,
                    'bookings' => $bookings,
                    'expenses' => $expenses,
                    'overcome' => $overcomeFormatted,
                    'logoPath' => $logoPath,
                    'startDate' => $startOfMonth->format('d/m/Y'),
                    'endDate' => $endOfMonth->format('d/m/Y'),
                    'monthLabel' => $startOfMonth->format('F Y'),
                ];

                $pdf = PDF::loadView('majormedia.listings::pdf.report', $data);
                $pdf->setOption([
                    'fontDir' => storage_path('fonts'),
                    'fontCache' => storage_path('fonts/cache'),
                    'defaultFont' => 'Jost',
                    'isHtml5ParserEnabled' => true,
                    'isPhpEnabled' => true,
                    'defaultPaperSize' => 'A4',
                    'defaultPaperOrientation' => 'portrait',
                    'dpi' => 96,
                ]);

                $directory = storage_path("app/public/pdfs");


                if (!file_exists($directory)) {
                    mkdir($directory, 0755, true);
                }

                $filename = "releve_reservations_{$listing->id}_{$startOfMonth->format('Y-m')}.pdf";
                $path = $directory . '/' . $filename;


                file_put_contents($path, $pdf->output());

                $statement = Statement::whereMonth('statement_date', $startOfMonth->month)
                    ->whereYear('statement_date', $startOfMonth->year)
                    ->where('listing_id', $listing->id)
                    ->first();

                if ($statement) {
                    $statement->statement_date = $startOfMonth->toDateString();
                    $statement->listing_id = $listing->id;
                    $statement->updated_at = $now;
                    $statement->is_active = false;

                    $statement->save();
                } else {
                    $statement = Statement::create([
                        'statement_date' => $startOfMonth->toDateString(),
                        'listing_id' => $listing->id,
                        'updated_at' => $now,
                        'created_at' => $now,
                        'is_active' => false,
                    ]);
                }

                $statement->document()->delete();

                $statement->document = (new File)->fromFile($path);
                $statement->save();

                $this->info("PDF generated for listing ID {$listing->id} - {$startOfMonth->format('F')}");
            }
        }
        \Log::info('end generating statements');
    }

}
