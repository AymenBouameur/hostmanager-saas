<?php namespace MajorMedia\ToolBox\Traits;

use Event;
use Exception;

trait SoftPrice
{
  public static $MODEL_NAME = '';
  public static $HT_FIELD_NAME = '';
  public static $TTC_FIELD_NAME = '';
  public static $TOTAL_HT_FIELD_NAME = '';
  public static $TOTAL_TTC_FIELD_NAME = '';

  /**
   * @var array List of attributes to automatically generate unique URL names (slugs) for.
   *
   * protected $prices = [];
   */

  /**
   * @var bool Allow trashed slugs to be counted in the slug generation.
   *
   * protected $allowTrashedSlugs = false;
   */

  /**
   * Boot the SoftPrice trait for a model.
   * @return void
   */
  public static function bootSoftPrice()
  {
    /*if (!property_exists(get_called_class(), 'prices')) {
      throw new Exception(sprintf(
        'You must define a $prices property in %s to use the SoftPrice trait.',
        get_called_class()
      ));
    }*/

    /*
     * Set slugged attributes on new records and existing records if slug is missing.
     */
    static::extend(function ($model) {
      self::$MODEL_NAME = strtolower(class_basename(get_called_class()));
      self::$HT_FIELD_NAME = isset($model->price_fields['price_ht']) ? $model->price_fields['price_ht'] : 'price_ht';
      self::$TTC_FIELD_NAME = isset($model->price_fields['price_ttc']) ? $model->price_fields['price_ttc'] : 'price_ttc';
      self::$TOTAL_HT_FIELD_NAME = isset($model->price_fields['total_ht']) ? $model->price_fields['total_ht'] : 'total_ht';
      self::$TOTAL_TTC_FIELD_NAME = isset($model->price_fields['total_ttc']) ? $model->price_fields['total_ttc'] : 'total_ttc';
      /*foreach ($model->price_fields as $key => $field) {
        switch ($key) {
          case 'ht':
            self::$HT_FIELD_NAME = $field;
            break;
          case 'ttc':
            self::$TTC_FIELD_NAME = $field;
            break;
          case 'total_ht':
            self::$TOTAL_HT_FIELD_NAME = $field;
            break;
          case 'total_ttc':
            self::$TOTAL_TTC_FIELD_NAME = $field;
            break;
        }
      }*/
    });
  }

  public function getSoftPriceAttribute()
  {
    return Event::fire(self::$MODEL_NAME.'.softPrice', [&$this], true) ?? $this->{self::$TTC_FIELD_NAME};
  }

  public function getSoftPriceHTAttribute()
  {
    return Event::fire(self::$MODEL_NAME.'.softPriceHT', [&$this], true) ?? $this->{self::$HT_FIELD_NAME};
  }

  public function getSoftPriceWithCurrencyAttribute()
  {
    return Event::fire(self::$MODEL_NAME.'.softPriceWithCurrency', [&$this], true) ?? $this->price($this->softPrice);
  }

  public function getSoftPriceHTWithCurrencyAttribute()
  {
    return Event::fire(self::$MODEL_NAME.'.softPriceHTWithCurrency', [&$this], true) ?? $this->price($this->softPriceHT);
  }

  public function getSoftTotalAttribute()
  {
    return $this->{self::$TOTAL_TTC_FIELD_NAME};
  }

  public function getSoftTotalHTAttribute()
  {
    return $this->{self::$TOTAL_HT_FIELD_NAME};
  }

  public function getSoftTotalWithCurrencyAttribute()
  {
    return $this->price($this->softTotal);
  }

  public function getSoftTotalHTWithCurrencyAttribute()
  {
    return $this->price($this->softTotalHT);
  }
}
