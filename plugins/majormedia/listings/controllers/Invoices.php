<?php
namespace MajorMedia\Listings\Controllers;

use Backend;
use BackendMenu;
use Backend\Classes\Controller;
use MajorMedia\Listings\Models\Invoice;

class Invoices extends Controller
{
    public $implement = [
        \Backend\Behaviors\FormController::class,
        \Backend\Behaviors\ListController::class
    ];

    public $formConfig = 'config_form.yaml';
    public $listConfig = 'config_list.yaml';

    public function __construct()
    {
        parent::__construct();

        BackendMenu::setContext('MajorMedia.Listings', 'listings', 'invoices');
    }

    public function downloadFile($id)
    {
        $invoice = Invoice::find($id);

        if (!$invoice || !$invoice->document) {
            \Flash::error('Fichier introuvable.');
            return redirect()->back();
        }

        $file = $invoice->document;
        $filePath = $file->getLocalPath();

        if (!file_exists($filePath)) {
            \Flash::error('Fichier non trouvé sur le serveur.');
            return redirect()->back();
        }

        return $invoice->document->download();
    }

}
