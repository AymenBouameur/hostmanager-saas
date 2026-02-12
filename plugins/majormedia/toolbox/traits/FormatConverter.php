<?php namespace MajorMedia\ToolBox\Traits;

use MajorMedia\ToolBox\Models\Settings;

trait FormatConverter
{
  /**
   * Format the data based on the input formatter value
   *
   * @param $value
   * @param $formatter
   * @return string
   */
  public static function format($value, $formatter)
  {
    return sprintf($formatter, $value);
  }

  /**
   * Format the input data with decimal places
   *
   * Defaults to 2 decimal places
   *
   * @param     $value
   * @param int $decimals
   * @return null|string
   */
  public static function formatToNumber($value, $decimals = 2)
  {
    if (trim($value) != null) {
      return number_format($value, $decimals, '.', '');
    }
    return null;
  }

  /**
   * Helper method to format price values with associated currency information.
   *
   * It covers the cases where certain currencies does not accept decimal values. We will be adding
   * any specific currency level rules as required here.
   *
   * @param      $value
   * @param null $currency
   * @return null|string
   */
  public static function formatToPrice($value, $currency = null)
  {
    $decimals = 2;
    $currencyDecimals = array('JPY' => 0, 'TWD' => 0, 'HUF' => 0);
    if ($currency && array_key_exists($currency, $currencyDecimals)) {
      if (strpos($value, ".") !== false && (floor($value) != $value)) {
        //throw exception if it has decimal values for JPY, TWD and HUF which does not ends with .00
        throw new \InvalidArgumentException("value cannot have decimals for $currency currency");
      }
      $decimals = $currencyDecimals[$currency];
    } elseif (strpos($value, ".") === false) {
      // Check if value has decimal values. If not no need to assign 2 decimals with .00 at the end
      $decimals = 0;
    }
    return self::formatToNumber($value, $decimals);
  }

  public static function price($price = 0, $currency_symbol = -1, $round = 2, $sep_centimes = '.', $sep_mille = ' ')
  {
    if ($currency_symbol == -1) {
      $currency_symbol = self::getCurrentCurrencySymbol();
    }
    return number_format($price, $round, $sep_centimes, $sep_mille) . ($currency_symbol ? ' ' . strtoupper($currency_symbol) : '');
  }

  public static function getCurrentCurrencySymbol () {
    $currency_symbol = '';
    switch (Settings::get('currency_symbol')) {
      case 'EUR':
        $currency_symbol = '€';
        break;
      case 'USD':
        $currency_symbol = '$';
        break;
      case 'MAD':
        $currency_symbol = 'DH';
        break;
    }
    return $currency_symbol;
  }

  public function unit($size = 0, $in = 'Mo', $out = 'Go')
  {
    if ($size < 1024)
      return $size . ' Mo';
    return round(($size / 1024)) . ' Go';
  }
}
