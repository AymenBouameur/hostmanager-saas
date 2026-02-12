<?php
namespace Majormedia\Listings\Http;

use Carbon\Carbon;
use System\Models\File;
use Backend\Classes\Controller;
use Barryvdh\DomPDF\Facade\Pdf as PDF;
use MajorMedia\Listings\Models\Invoice;
use MajorMedia\Listings\Models\Listing;
use MajorMedia\ToolBox\Traits\RetrieveUser;

/**
 * Invoices Back-end Controller
 */
class Invoices extends Controller
{
    use RetrieveUser;
    public $implement = [
        'MajorMedia.ToolBox.Behaviors.RestController'
    ];

    public $restConfig = 'config_rest.yaml';


    public function extendModel($model)
    {
        $this->retrieveUser();

        $model = $model->when(!empty($term = request('term')), function ($query) use ($term) {
            $query->whereHas('listing', function ($listingQuery) use ($term) {
                $listingQuery->where('title', 'like', '%' . $term . '%');
            });
        });

        $model = $model->when(request()->has('from') && request()->has('to'), function ($query) {
            $from = Carbon::createFromTimestamp(request('from'));
            $to = Carbon::createFromTimestamp(request('to'));
            return $query->whereBetween('created_at', [$from, $to]);
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
        return $model;
    }

    public function generateInvoice($listingId)
    {
        $listing = Listing::find($listingId);

        $bookings = $listing->bookings()->get();

        $logoPath = asset('storage/app/uploads/public/logos/logo.png');

        $data = [
            'logoPath' => $logoPath,
            'listing' => $listing,
            'bookings' => $bookings,
            'date' => now()->format('d/m/Y'),
        ];

        $pdfForDownload = PDF::loadView('majormedia.listings::pdf.report', $data);
        $pdfForDownload->setOption([
            'fontDir' => storage_path('fonts'),
            'fontCache' => storage_path('fonts/cache'),
            'defaultFont' => 'Jost',
            'isHtml5ParserEnabled' => true,
            'isPhpEnabled' => true,
            'defaultPaperSize' => 'A4',
            'defaultPaperOrientation' => 'portrait',
            'margin-top' => 0,
            'margin-right' => 0,
            'margin-bottom' => 0,
            'margin-left' => 0,
            'dpi' => 96,
        ]);
        $pdf = PDF::loadView('majormedia.listings::pdf.report', $data);
        $pdf->setOption([
            'fontDir' => storage_path('fonts'),
            'fontCache' => storage_path('fonts/cache'),
            'defaultFont' => 'Jost',
            'isHtml5ParserEnabled' => true,
            'isPhpEnabled' => true,
            'defaultPaperSize' => 'A4',
            'defaultPaperOrientation' => 'portrait',
            'margin-top' => 0,
            'margin-right' => 0,
            'margin-bottom' => 0,
            'margin-left' => 0,
            'dpi' => 96,
        ]);

        $pdfName = "releve_reservations_{$listing->id}.pdf";

        $pdfPath = storage_path("app/public/invoices/{$pdfName}");

        file_put_contents($pdfPath, $pdf->output());

        $invoice = Invoice::create([
            'invoice_number' => "INV-" . now()->format('YmdHis'),
            'listing_id' => $listingId,
        ]);
        $invoice->document = (new File)->fromFile($pdfPath);
        $invoice->save();

        return $pdfForDownload->download("invoices_{$listing->id}.pdf");
    }

}
