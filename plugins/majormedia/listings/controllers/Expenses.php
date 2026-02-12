<?php
namespace MajorMedia\Listings\Controllers;

use Backend;
use BackendMenu;
use Backend\Classes\Controller;
use MajorMedia\Listings\Models\Expense;

class Expenses extends Controller
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
        BackendMenu::setContext('MajorMedia.Listings', 'listings', 'expenses');
    }

    public function regenerateStatement($expenseId)
    {
        $expense = Expense::find($expenseId);
        if (!$expense) {
            \Flash::error('Dépense introuvable.');
            return \Redirect::to(\Backend::url('majormedia/listings/expenses'));
        }
        $listing = $expense->listing;
        if (!$listing) {
            \Flash::error('Propriété associée à la dépense introuvable.');
            return \Redirect::to(\Backend::url('majormedia/listings/expenses'));
        }
        try {
            \Artisan::call('listings:generatepdfviacommand', ['listingId' => $listing->id]);
            \Flash::success('Récapitulatif des dépenses régénéré avec succès.');
        } catch (\Exception $e) {
            \Flash::error('Erreur lors de la régénération du récapitulatif des dépenses: ' . $e->getMessage());
        }

        // Optional: reload the list view after regenerating
        return \Redirect::to(\Backend::url('majormedia/listings/expenses'));
    }
}
