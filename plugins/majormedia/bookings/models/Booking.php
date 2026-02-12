<?php
namespace MajorMedia\Bookings\Models;

use App;
use October\Rain\Database\Model;
use MajorMedia\ToolBox\Traits\ModelAliases;
use MajorMedia\Listings\Models\Listing;
use October\Rain\Database\Traits\Validation;
use function PHPUnit\Framework\matches;

/**
 * Model
 */
class Booking extends Model
{
  use Validation, ModelAliases;
  const LABEL_STATUS_0 = "Confirmé";
  const LABEL_STATUS_1 = "Annulé";

  const LABEL_CANAL_1 = "Vacaloc";
  const LABEL_CANAL_2 = "Airbnb";
  const LABEL_CANAL_3 = "Bookings";
  const LABEL_CANAL_4 = "Vrbo";


  /**
   * @var string table in the database used by the model.
   */
  public $table = 'majormedia_bookings_bookings';

  protected $appends = ['statusAlias', 'canalAlias', 'listing_data'];
  protected $fillable = [
    'reference',
    'order_reference',
    'customer',
    'amount',
    'net_amount',
    'gross_amount',
    'clearning_fee',
    'tax',
    'vacaloc_commission',
    'ota_commission',
    'total_amount_order',
    'owner_profit',
    'check_in',
    'check_out',
    'status',
    'canal',
    'is_canceled',
    'listing_id',
    'created_at',
    'updated_at'
  ];
  protected $visible = [
    'id',
    'reference',
    'customer',
    'net_amount',
    'gross_amount',
    'clearning_fee',
    'tax',
    'vacaloc_commission',
    'ota_commission',
    'total_amount_order',
    'owner_profit',
    'check_in',
    'check_out',
    'status',
    'is_canceled',
    'statusAlias',
    'canal',
    'canalAlias',
    'listing_data'
  ];

  /**
   * @var array rules for validation.
   */
  public $rules = [
  ];

  public $belongsTo = [
    'listing' => [Listing::class]
  ];

  public function getIsCanceledAttribute($value)
  {
    if (App::runningInBackend()) {
      return $value == 0 ? 'Confirmé' : 'Annulé';
    }
    return (bool) $value;
  }

  public function getCanalOptions()
  {
    return $this->guessOptions('LABEL_CANAL_', true);
  }


  public function getCanalAliasAttribute()
  {
    $options = $this->getCanalOptions();
    return (isset($options[$this->canal]) ? $options[$this->canal] : '-');
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

  public static function getRecentReservationsCount($userId)
  {
    $startDate = now()->startOfMonth();
    $endDate = now()->endOfMonth();

    return self::whereHas('listing.users', function ($query) use ($userId) {
      $query->where('users.id', $userId);
    })
      ->where('is_canceled', 0)
      ->whereBetween('check_out', [$startDate, $endDate])
      ->count();
  }
  public static function getUpcomingReservationsCount($userId)
  {
    $startDate = now()->startOfMonth()->addMonth();
    $endDate = now()->startOfMonth()->addMonths(4)->subDay();

    return self::whereHas('listing.users', function ($query) use ($userId) {
      $query->where('users.id', $userId);
    })
      ->where('is_canceled', 0)
      ->whereDate('check_out', '>=', $startDate->toDateString())
      ->whereDate('check_out', '<=', $endDate->toDateString())
      ->count();
  }


  public static function getRecentRevenue($userId)
  {
    $startDate = now()->startOfMonth();
    $endDate = now()->endOfMonth();

    return self::whereHas('listing.users', function ($query) use ($userId) {
      $query->where('users.id', $userId);
    })
      ->where('is_canceled', 0)
      ->whereBetween('check_out', [$startDate, $endDate])
      ->sum('owner_profit');
  }

  public static function getProjectedRevenue($userId)
  {
    $startDate = now()->startOfMonth()->addMonth(); 
    $endDate = now()->startOfMonth()->addMonths(4)->subDay();

    return self::whereHas('listing.users', function ($query) use ($userId) {
      $query->where('users.id', $userId);
    })
      ->where('is_canceled', 0)
      ->whereBetween('check_out', [$startDate->toDateString(), $endDate->toDateString()])
      ->sum('owner_profit');
  }


  /**
   * Accessor for the NetAmount attribute.
   */
  public function getNetAmountAttribute($value)
  {
    return (float) $value;
  }
  /**
   * Accessor for the GrossAmount attribute.
   */
  public function getGrossAmountAttribute($value)
  {
    return (float) $value;
  }
  /**
   * Accessor for the ClearningFee attribute.
   */
  public function getClearningFeeAttribute($value)
  {
    return (float) $value;
  }
  /**
   * Accessor for the Tax attribute.
   */
  public function getTaxAttribute($value)
  {
    return (float) $value;
  }
  /**
   * Accessor for the VacalocCommission attribute.
   */
  public function getVacalocCommissionAttribute($value)
  {
    return (float) $value;
  }
  /**
   * Accessor for the OtaCommission attribute.
   */
  public function getOtaCommissionAttribute($value)
  {
    return (float) $value;
  }
  /**
   * Accessor for the TotalAmountOrder attribute.
   */
  public function getTotalAmountOrderAttribute($value)
  {
    return (float) $value;
  }

  /**
   * Accessor for the OwnerProfit attribute.
   */
  public function getOwnerProfitAttribute($value)
  {
    return (float) $value;
  }

}
