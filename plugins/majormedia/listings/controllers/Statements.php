<?php
namespace MajorMedia\Listings\Controllers;

use Backend;
use BackendMenu;
use Backend\Classes\Controller;
use MajorMedia\Listings\Models\Statement;

class Statements extends Controller
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
        BackendMenu::setContext('MajorMedia.Listings', 'listings', 'statements');
    }

    public function downloadFile($id)
    {
        $statement = Statement::find($id);

        if (!$statement || !$statement->document) {
            \Flash::error('Fichier introuvable.');
            return redirect()->back();
        }

        $file = $statement->document;
        $filePath = $file->getLocalPath();

        if (!file_exists($filePath)) {
            \Flash::error('Fichier non trouvé sur le serveur.');
            return redirect()->back();
        }

        return $statement->document->download();
    }

}
