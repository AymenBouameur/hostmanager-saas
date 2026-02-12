<?php
namespace Majormedia\Listings\Console;


use Carbon\Carbon;
use System\Models\File;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf as PDF;
use Majormedia\InCore\Models\Settings;
use MajorMedia\Bookings\Models\Booking;
use MajorMedia\Listings\Models\Invoice;
use MajorMedia\Listings\Models\Listing;
/**
 * GenerateInvoiceViaCommand Command
 *
 * @link https://docs.octobercms.com/3.x/extend/console-commands.html
 */
class GenerateInvoiceViaCommand extends Command
{
    protected $signature = 'listings:generateinvoiceviacommand {mode?} {listingId?} {month?}';
    protected $description = 'Generate a PDF Invoice for each listing, showing data for each month in the current year.';

    public function handle()
    {
        $mode = $this->argument('mode');
        $listingId = $this->argument('listingId');
        $monthArg = $this->argument('month');
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
            $this->info("Generating Invoice report for month: {$monthArg}");
        } else {
            // Otherwise, use existing logic for range of months based on mode
            $startMonth = $mode === 'all' ? 1 : $now->copy()->subMonth()->month;
            $endMonth = $mode === 'all' ? $now->month : $now->copy()->subMonth()->month;
            $monthsToGenerate = range($startMonth, $endMonth);
            $this->info("Generating Invoice reports for listings from month {$startMonth} to {$endMonth}...");
        }

        foreach ($listings as $listing) {
            foreach ($monthsToGenerate as $month) {
                $startOfMonth = Carbon::createFromDate($now->year, $month, 1)->startOfMonth();
                $endOfMonth = $startOfMonth->copy()->endOfMonth();

                $totalTTC = Booking::where('listing_id', $listing->id)
                    ->where('is_canceled', 0)
                    ->where(function ($query) use ($startOfMonth, $endOfMonth) {
                        $query->whereBetween(DB::raw('DATE(check_out)'), [$startOfMonth, $endOfMonth]);
                    })
                    ->sum('vacaloc_commission');

                $tva = Settings::get('tva', 20);
                if ($tva < 0 || $tva > 100) {
                    $this->error('Invalid TVA percentage. Please set a value between 0 and 100.');
                    return;
                }
                $totalHT = round($totalTTC / 1.2, 2);
                $montantTVA = $totalHT * ($tva / 100);

                $logoPath = \Media\Classes\MediaLibrary::url('logos/logo.png');

                $data = [
                    'listing' => $listing,
                    'totalTTC' => $totalTTC,
                    'totalHT' => $totalHT,
                    'tva' => $tva,
                    'montantTVA' => $montantTVA,
                    'logoPath' => $logoPath,
                    'startDate' => $startOfMonth->format('d/m/Y'),
                    'endDate' => $endOfMonth->format('d/m/Y'),
                    'issue_date' => Carbon::now()
                ];

                $pdf = PDF::loadView('majormedia.listings::pdf.invoice', $data);
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
                $directory = storage_path("app/public/invoices");

                if (!file_exists($directory)) {
                    mkdir($directory, 0755, true);
                }
                $filename = "facture_reservations_{$listing->id}_{$startOfMonth->format('Y-m')}.pdf";
                $path = $directory . '/' . $filename;
                file_put_contents($path, $pdf->output());

                $invoice = Invoice::updateOrCreate(
                    [
                        'invoice_number' => $filename,
                        'listing_id' => $listing->id,
                    ],
                    [
                        'issued_date' => Carbon::now(),
                    ]
                );

                $invoice->document = (new File)->fromFile($path);
                $invoice->save();

                $this->info("Invoice for listing ID {$listing->id} - {$startOfMonth->format('F')}");
            }
        }
    }

}
