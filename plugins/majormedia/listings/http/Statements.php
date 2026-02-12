<?php
namespace Majormedia\Listings\Http;

use Carbon\Carbon;
use Backend\Classes\Controller;
use Illuminate\Support\Facades\Log;
use MajorMedia\Listings\Classes\PdfSaver;
use MajorMedia\ToolBox\Traits\RetrieveUser;
use MajorMedia\Listings\Classes\PdfGenerator;
use MajorMedia\Listings\Classes\PdfDownloader;
use MajorMedia\Listings\Classes\PdfDataProvider;
use MajorMedia\Listings\Classes\PdfGeneratorFacade;
/**
 * Statements Back-end Controller
 */
class Statements extends Controller
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

        $model = $model->when(request()->has('from'), function ($query) {
            $from = Carbon::createFromTimestamp(request('from'))->toDateString();
            return $query->whereDate('statement_date', '=', $from);
        })->when(request()->has('to'), function ($query) {
            $to = Carbon::createFromTimestamp(request('to'))->toDateString();
            return $query->whereDate('statement_date', '<=', $to);
        });

        $model = $model->when(request()->has('listing'), function ($query) {
            $query->whereHas('listing', function ($query) {
                return $query->where('id', request('listing'));
            });
        });
        $model = $model->whereHas('listing.users', function ($query) {
            $query->where('users.id', $this->user->id);
        });

        return $model->active()->orderBy('statement_date', 'desc');
    }

    public function generatePdf($listingId)
    {
        $startTimestamp = request()->input('startAt');
        $endTimestamp = request()->input('endAt');
        $start = null;
        $end = null;

        if ($startTimestamp && $endTimestamp) {
            $start = Carbon::createFromTimestamp($startTimestamp);
            $end = Carbon::createFromTimestamp($endTimestamp);


            if ($end->lt($start)) {
                Log::error("Invalid date range: end date is earlier than start date", [
                    'listing_id' => $listingId,
                    'start_timestamp' => $startTimestamp,
                    'end_timestamp' => $endTimestamp,
                ]);
                return response()->json(['error' => 'End date cannot be earlier than start date.'], 400);
            }

            $daysDifference = $start->diffInDays($end);

            if ($daysDifference > 31) {
                Log::error("Invalid date range: exceeds 31 days", [
                    'listing_id' => $listingId,
                    'start_timestamp' => $startTimestamp,
                    'end_timestamp' => $endTimestamp,
                ]);
                return response()->json(['error' => 'The date range cannot exceed 31 days.'], 400);
            }
        }

        try {
            $pdfGeneratorFacade = new PdfGeneratorFacade(
                new PdfDataProvider(),
                new PdfGenerator(),
                new PdfSaver(),
                new PdfDownloader()
            );
            if ($start && $end) {
                return $pdfGeneratorFacade->generateAndSavePdf($listingId, $start->toDateString(), $end->toDateString());

            }
            return $pdfGeneratorFacade->generateAndSavePdf($listingId, null, null);

        } catch (\Exception $e) {
            Log::error("PDF generation failed", [
                'listing_id' => $listingId,
                'error_message' => $e->getMessage(),
            ]);
            return response()->json(['error' => 'Failed to generate PDF.'], 500);
        }
    }
}
