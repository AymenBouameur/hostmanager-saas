<?php
namespace MajorMedia\Listings\Models;

use Model;
use Carbon\Carbon;
use System\Models\File;
use RainLab\User\Models\User;
use MajorMedia\Bookings\Models\Booking;
use MajorMedia\Listings\Models\Expense;
use MajorMedia\Listings\Models\Invoice;
use October\Rain\Database\Traits\Validation;

/**
 * Model
 */
class Listing extends Model
{
    use Validation;
    /**
     * @var string table in the database used by the model.
     */
    public $table = 'majormedia_listings_listings';

    /**
     * @var array rules for validation.
     */
    public $rules = [
    ];
    protected $appends = [
        'images_url',
        'availability'
    ];
    protected $fillable = [
        'shortName',
        'title',
        'address',
        'owner_full_name',
        'description',
        'is_active'
    ];
    protected $visible = [
        'id',
        'shortName',
        'title',
        'address',
        'owner_full_name',
        'description',
        'images_url',
        'availability'
    ];
    protected $hidden = [];

    public $hasMany = [
        'expenses' => [Expense::class],
        'statements' => [Statement::class],
        'bookings' => [Booking::class],
        'invoices' => [Invoice::class],
    ];
    public $belongsTo = [
        'owner' => [User::class, 'key' => 'user_id'],
    ];
    public function latestStatement()
    {
        return $this->hasOne(Statement::class)
            ->latestOfMany('statement_date')->active();
    }

    public $attachMany = [
        'images' => [File::class],
    ];

    /**
     * Accessors
     */


    public function getImagesUrlAttribute()
    {
        $urls = [];
        if ($this->images && $this->images->isNotEmpty()) {
            foreach ($this->images as $image) {
                $urls[] = [
                    'id' => $image->id,
                    'path' => $image->path,
                ];
            }
            return $urls;
        }
        return [];
    }

    public function getAvailabilityAttribute()
    {
        // Get today's date
        $today = Carbon::today();
        $conflictingBooking = $this->bookings()
            ->where('is_canceled', 0)
            ->where(function ($query) use ($today) {
                $query->where('check_out', '<=', $today)
                    ->where('check_out', '>=', $today);
            })
            ->exists();

        return $conflictingBooking ? 0 : 1;
    }

    /**
     * Scopes
     */
    public function scopeCommissionStatus($query, $scope)
    {
        if ($scope->value == 1) {
            \Log::info($scope->value);
            return $query->whereNull('commission');
        } elseif ($scope->value == 2) {
            return $query->whereNotNull('commission');
        }

        return $query;
    }



}
