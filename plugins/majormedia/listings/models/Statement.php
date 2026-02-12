<?php
namespace MajorMedia\Listings\Models;

use Model;
use Carbon\Carbon;
use System\Models\File;
use Illuminate\Support\Facades\App;
use MajorMedia\Listings\Models\Listing;
use October\Rain\Database\Traits\Validation;
/**
 * Model
 */
class Statement extends Model
{
    use Validation;

    /**
     * @var string table in the database used by the model.
     */
    public $table = 'majormedia_listings_statements';

    /**
     * @var array rules for validation.
     */
    public $rules = [
    ];
    protected $appends = [
        'listing_data',
        'url'
    ];
    protected $fillable = [
        'listing_id',
        'statement_date',
        'updated_at',
        'is_active',
    ];
    protected $visible = [
        'id',
        'statement_date',
        'listing_data',
        'url',
        'is_active',
    ];

    public $belongsTo = [
        'listing' => [Listing::class],
    ];

    public $attachOne = [
        'document' => [File::class]
    ];

    public function getStatementDateAttribute($value)
    {

        if (!$value) {
            return null;
        }
        if (App::runningInBackend()) {
            return $value;
        }

        // Use parse for non-timestamp values
        return Carbon::parse($value)->format('F Y');
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

    public function getIsActiveTextAttribute()
    {
        return $this->is_active ? 'Oui' : 'No';
    }

    /**
     * Scopes
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
