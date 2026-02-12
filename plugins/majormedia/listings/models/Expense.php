<?php
namespace MajorMedia\Listings\Models;

use Model;
use MajorMedia\Listings\Models\Listing;
use MajorMedia\ToolBox\Traits\ModelAliases;
use October\Rain\Database\Traits\Validation;

/**
 * Model
 */
class Expense extends Model
{
    use ModelAliases, Validation;

    /**
     * @var string table in the database used by the model.
     */
    public $table = 'majormedia_listings_expenses';

    /**
     * @var array rules for validation.
     */
    public $rules = [
    ];

    protected $fillable = [
        'listing_id',
        'label',
        'amount',
        'processed_at',
        'expenses_type'
    ];
    protected $visible = [
        'listing_id',
        'label',
        'amount',
        'processed_at',
        'expenses_type'
    ];

    public $belongsTo = [
        'listing' => [Listing::class]
    ];

    /**
     * Enums
     */
    const LABEL_EXPENSE_TYPE_1 = 'Remboursement';
    const LABEL_EXPENSE_TYPE_2 = 'Abonnement Wi-Fi';
    const LABEL_EXPENSE_TYPE_3 = 'Fournitures de bienvenue';
    const LABEL_EXPENSE_TYPE_4 = 'Assurance logement';
    const LABEL_EXPENSE_TYPE_5 = 'Frais d’électricité';
    const LABEL_EXPENSE_TYPE_6 = 'Abonnement TV / Streaming';
    const LABEL_EXPENSE_TYPE_7 = 'Maintenance';
    const LABEL_EXPENSE_TYPE_8 = 'Autre';


    public function getExpensesTypeOptions()
    {
        return $this->guessOptions('LABEL_EXPENSE_TYPE_', true);
    }

    public function getExpensesTypeAliasAttribute()
    {
        $options = $this->getExpensesTypeOptions();
        return (isset($options[$this->expenses_type]) ? $options[$this->expenses_type] : '-');
    }

}
