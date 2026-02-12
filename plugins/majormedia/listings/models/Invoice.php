<?php
namespace MajorMedia\Listings\Models;

use Model;
use System\Models\File;
use MajorMedia\Listings\Models\Listing;

/**
 * Model
 */
class Invoice extends Model
{
    use \October\Rain\Database\Traits\Validation;


    /**
     * @var string table in the database used by the model.
     */
    public $table = 'majormedia_listings_invoices';

    /**
     * @var array rules for validation.
     */
    public $rules = [
    ];
    protected $appends = ['listing_data', 'url'];
    protected $visible = ['id', 'due_date', 'issued_date', 'listing_data', 'url'];

    protected $fillable = [
        'invoice_number',
        'listing_id',
        'due_date',
        'issued_date',
    ];

    public $belongsTo = [
        'listing' => [Listing::class],
    ];

    public $attachOne = [
        'document' => [File::class],
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($invoice) {
            $invoice->issued_date = $invoice->issued_date ?? now()->toDateString();
            $invoice->due_date = $invoice->due_date ?? now()->addDays(30)->toDateString();
        });
    }

    public function getListingDataAttribute()
    {
        if (!$this->relationLoaded('listing')) {
            $this->load('listing');
        }
        if ($this->listing) {
            return [
                'id' => $this->listing->id,
                'title' => $this->listing->title,
            ];
        }
        return null;
    }

    public function getUrlAttribute()
    {
        return $this->document ? $this->document->path : null;
    }

}
