<?php
namespace MajorMedia\Listings\Controllers;

use Backend;
use BackendMenu;
use Carbon\Carbon;
use Backend\Classes\Controller;
use Illuminate\Support\Facades\Artisan;
use MajorMedia\Listings\Models\Listing;
use MajorMedia\Listings\Models\Statement;
use October\Rain\Support\Facades\Flash;
use Illuminate\Support\Facades\Response;
use October\Rain\Exception\ApplicationException;
use Majormedia\Listings\Services\ExportListingCsvService;

class Listings extends Controller
{
    public $implement = [
        \Backend\Behaviors\FormController::class,
        \Backend\Behaviors\ListController::class,
        \Backend\Behaviors\RelationController::class
    ];

    public $requiredPermissions = [
        'majormedia.listings::plugin.manage',
        'majormedia.listings::listings.manage',
    ];

    public $formConfig = 'config_form.yaml';
    public $listConfig = 'config_list.yaml';
    public $relationConfig = 'config_relation.yaml';


    public function __construct()
    {
        parent::__construct();

        BackendMenu::setContext('MajorMedia.Listings', 'listings', 'listings');
    }

    public function onSyncProperties()
    {

        //     if (BackendAuth::userHasAccess('majormedia.listings::listings.sync')) {
        // // ...
        //         Flash::error('Vous n’avez pas les permissions nécessaires pour synchroniser les propriétés.');
        //         return;
        //     }
        try {
            Artisan::call('fetch:eviivo-properties');
            // Artisan::call('fetch:eviivo-properties-contact');

            Flash::success('Synchronisation des propriétés réussie.');
        } catch (\Exception $e) {
            Flash::error('Erreur lors de la synchronisation: ' . $e->getMessage());
        }

        // Optional: reload the list view after syncing
        return \Redirect::to(\Backend::url('majormedia/listings/listings'));
    }
    public function onOpenMonthModal()
    {
        $listingId = post('listing_id');
        $listing = Listing::find($listingId);

        return $this->makePartial('$/majormedia/listings/controllers/listings/_select_month_modal.htm', [
            'listing' => $listing,
        ]);
    }

    public function onChooseMonth()
    {
        $selectedIds = post('checked');

        if ($selectedIds === null) {
            $selectedIds = Listing::pluck('id')->toArray();
        }

        return $this->makePartial('$/majormedia/listings/controllers/listings/_select_month_to_export_modal.htm', [
            'selectedIds' => $selectedIds,
        ]);
    }



    public function onGenerateStatementWithMonth()
    {
        $listingId = post('listing_id');
        $month = (int) post('month');

        $listing = Listing::find($listingId);
        if (!$listing) {
            throw new \Exception("Propriété introuvable.");
        }

        \Artisan::call('listings:generatepdfviacommand', [
            'listingId' => $listing->id,
            'month' => $month
        ]);
    }

    public function onExportData()
    {
        $month = (int) post('month');
        $year = (int) date('Y');
        $ids = explode(',', post('selectedIds'));

        if (!$month) {
            \Flash::error("Données invalides pour l'export.");
            return;
        }

        try {
            $exportService = new ExportListingCsvService($year, $month, $ids);
            $csv = $exportService->generateCsv();

            $fileName = 'export_listings_bookings_' . now()->format('Y_m_d_His') . '.csv';

            return Response::streamDownload(function () use ($csv) {
                echo $csv;
            }, $fileName, [
                'Content-Type' => 'text/csv',
                'Cache-Control' => 'max-age=0, no-cache, must-revalidate, proxy-revalidate',
                'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
            ]);

        } catch (\Exception $e) {
            \Log::error('Error during export', ['exception' => $e]);
            \Flash::error("Une erreur est survenue pendant l'export.");
        }
    }


    public function onExportPdf()
    {
        $month = post('month');
        $all = post('all');
        $selectedIds = post('checked');

        // Automatique avec data-list-checked-request

        if (!$month || $month < 1 || $month > 12) {
            Flash::error('Mois invalide.');
            return;
        }

        try {
            if ($all) {
                \Artisan::call('listings:generatepdfviacommand', [
                    'mode' => 'all',
                    'month' => $month
                ]);
                \Artisan::call('listings:generateinvoiceviacommand', [
                    'mode' => 'all',
                    'month' => $month
                ]);
            } elseif (is_array($selectedIds) && count($selectedIds)) {
                foreach ($selectedIds as $listingId) {
                    \Artisan::call('listings:generatepdfviacommand', [
                        'listingId' => $listingId,
                        'month' => $month
                    ]);
                    \Artisan::call('listings:generateinvoiceviacommand', [
                        'listingId' => $listingId,
                        'month' => $month
                    ]);
                }
            } else {
                Flash::error('Aucune propriété sélectionnée.');
                return;
            }

            Flash::success('PDF générés avec succès.');
        } catch (\Exception $e) {
            \Log::error('Erreur lors de la génération des PDF', ['exception' => $e]);
            Flash::error('Une erreur est survenue : ' . $e->getMessage());
        }
    }

    /**
     * Select a month for PDF generation
     * 
     */
    public function onSelectMonthModal()
    {
        $type = (int) post('type');
        if (!in_array($type, [1, 2, 3, 4])) {
            throw new \ApplicationException('Type de génération inconnu.');
        }
        return $this->makePartial('$/majormedia/listings/controllers/listings/_select_month_modal_to_generate.htm', ['type' => $type]);
    }


    /**
     * Generate PDF or invoice based on the selected month and type
     * 
     * @return \Illuminate\Http\Response
     */
    public function onGenerateStatement()
    {
        $type = (int) post('type');
        $month = (int) post('month');

        if (!$month) {
            throw new \ApplicationException('Veuillez sélectionner un mois.');
        }

        if (!is_numeric($month) || $month < 1 || $month > 12) {
            throw new \InvalidArgumentException("Invalid month value: $month");
        }
        switch ($type) {
            case 1: //Generate PDF statement
                \Artisan::call('listings:generatepdfviacommand', [
                    'month' => $month,
                    'mode' => 'all'
                ]);
                Flash::success('Relevé PDF généré avec succès.');
                return Response::make('Relevé PDF généré avec succès.', 200);

            case 2: //Generate invoice
                \Artisan::call('listings:generateinvoiceviacommand', [
                    'month' => $month,
                    'mode' => 'all'
                ]);
                Flash::success('Facture générée avec succès.');
                return Response::make('Facture générée avec succès.', 200);
            case 3://Activate statements
                $statements = Statement::whereMonth('statement_date', $month)
                    ->where('is_active', '=', 0)
                    ->get();
                \Log::info('Activating statements for month: ' . $month, ['count' => $statements->count()]);
                if ($statements->isEmpty()) {
                    throw new \ApplicationException('Aucun relevé trouvé pour le mois sélectionné.');
                }

                foreach ($statements as $statement) {
                    $statement->is_active = 1;
                    $statement->save();
                }
                Flash::success('Relevés activés avec succès.');
                return Response::make('Relevés activés avec succès.', 200);
            case 4: //Desactivate statements
                $statements = Statement::whereMonth('statement_date', $month)
                    ->where('is_active', '=', 1)
                    ->get();
                \Log::info('Deactivating statements for month: ' . $month, ['count' => $statements->count()]);
                if ($statements->isEmpty()) {
                    throw new \ApplicationException('Aucun relevé trouvé pour le mois sélectionné.');
                }

                foreach ($statements as $statement) {
                    $statement->is_active = 0;
                    $statement->save();
                }
                Flash::success('Relevés désactivés avec succès.');
                return Response::make('Relevés désactivés avec succès.', 200);

            default:
                throw new \ApplicationException('Type de génération inconnu.');
        }

    }



}
